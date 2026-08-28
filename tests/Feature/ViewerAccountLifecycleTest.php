<?php

namespace Tests\Feature;

use App\Models\KelasJabatan;
use App\Models\Pegawai;
use App\Models\Tpp;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ViewerAccountLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_akun_viewer_otomatis_hanya_dibuat_untuk_pegawai_aktif(): void
    {
        [$unit, $kelas] = $this->makeUnitAndClass('AKTIF');
        $pegawai = $this->makePegawai($unit, $kelas, '1985-04-17');

        $viewer = User::query()->where('pegawai_id', $pegawai->id)->first();

        $this->assertNotNull($viewer);
        $this->assertSame('viewer', $viewer->role);
        $this->assertTrue(Hash::check('17041985', $viewer->password));
    }

    public function test_pegawai_nonaktif_tidak_mendapat_akun_viewer_baru(): void
    {
        [$unit, $kelas] = $this->makeUnitAndClass('NONAKTIF');
        $pegawai = $this->makePegawai($unit, $kelas, '1985-04-17', Pegawai::STATUS_PENSIUN);

        $this->assertDatabaseMissing('users', ['pegawai_id' => $pegawai->id]);
    }

    public function test_pegawai_nonaktif_tidak_dapat_login_dengan_akun_lama(): void
    {
        [$unit, $kelas] = $this->makeUnitAndClass('LOGIN');
        $pegawai = $this->makePegawai($unit, $kelas, '1985-04-17');
        $pegawai->update([
            'status_pegawai' => Pegawai::STATUS_PENSIUN,
            'nonaktif_sejak' => now()->toDateString(),
        ]);

        $this->post('/login', [
            'login_as' => 'pegawai',
            'nip' => $pegawai->nip,
            'password' => '17041985',
        ]);

        $this->assertGuest();
    }

    public function test_pegawai_aktif_dapat_login_dengan_akun_viewer(): void
    {
        [$unit, $kelas] = $this->makeUnitAndClass('MASUK');
        $pegawai = $this->makePegawai($unit, $kelas, '1985-04-17');

        $this->post('/login', [
            'login_as' => 'pegawai',
            'nip' => $pegawai->nip,
            'password' => '17041985',
        ]);

        $this->assertAuthenticatedAs(User::query()->where('pegawai_id', $pegawai->id)->first());
    }

    public function test_sesi_viewer_dihentikan_segera_setelah_pegawai_nonaktif(): void
    {
        [$unit, $kelas] = $this->makeUnitAndClass('SESI');
        $pegawai = $this->makePegawai($unit, $kelas, '1985-04-17');
        $viewer = User::query()->where('pegawai_id', $pegawai->id)->firstOrFail();
        $pegawai->update([
            'status_pegawai' => Pegawai::STATUS_MUTASI,
            'nonaktif_sejak' => now()->toDateString(),
        ]);

        $this->actingAs($viewer)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('nip');

        $this->assertGuest();
    }

    public function test_benturan_email_internal_tidak_mengambil_alih_akun_viewer_lain(): void
    {
        [$unit, $kelas] = $this->makeUnitAndClass('EMAIL');
        $pegawaiPertama = $this->makePegawai($unit, $kelas, '1985-04-17', Pegawai::STATUS_AKTIF, '12-34');
        $viewerPertama = User::query()->where('pegawai_id', $pegawaiPertama->id)->firstOrFail();

        $pegawaiKedua = $this->makePegawai($unit, $kelas, '1990-06-21', Pegawai::STATUS_AKTIF, '1234');
        $viewerKedua = User::query()->where('pegawai_id', $pegawaiKedua->id)->firstOrFail();

        $this->assertSame($pegawaiPertama->id, $viewerPertama->fresh()->pegawai_id);
        $this->assertNotSame($viewerPertama->id, $viewerKedua->id);
        $this->assertNotSame($viewerPertama->email, $viewerKedua->email);
    }

    public function test_viewer_mutasi_melihat_seluruh_riwayat_pribadi_tetapi_pengelola_tetap_dibatasi_per_unit(): void
    {
        [$unitLama, $kelasLama] = $this->makeUnitAndClass('RIWAYAT-LAMA');
        [$unitBaru, $kelasBaru] = $this->makeUnitAndClass('RIWAYAT-BARU');
        $pegawai = $this->makePegawai($unitLama, $kelasLama, '1985-04-17');
        $viewer = User::query()->where('pegawai_id', $pegawai->id)->firstOrFail();
        $tppLama = $this->makeTpp($pegawai, $unitLama, 1);

        $pegawai->update([
            'unit_kerja_id' => $unitBaru->id,
            'kelas_jabatan_id' => $kelasBaru->id,
        ]);
        $tppBaru = $this->makeTpp($pegawai, $unitBaru, 2);

        $this->actingAs($viewer)
            ->get(route('tpp.index'))
            ->assertOk()
            ->assertViewHas('tpps', function ($rows) use ($tppLama, $tppBaru) {
                return collect($rows->items())->pluck('id')->sort()->values()->all()
                    === collect([$tppLama->id, $tppBaru->id])->sort()->values()->all();
            });

        $operatorLama = User::factory()->create([
            'role' => 'operator',
            'unit_kerja_id' => $unitLama->id,
        ]);
        $this->actingAs($operatorLama)
            ->get(route('tpp.index', ['bulan' => 1, 'tahun' => 2026]))
            ->assertViewHas('tpps', fn ($rows) => collect($rows->items())->pluck('id')->all() === [$tppLama->id]);

        $operatorBaru = User::factory()->create([
            'role' => 'operator',
            'unit_kerja_id' => $unitBaru->id,
        ]);
        $this->actingAs($operatorBaru)
            ->get(route('tpp.index', ['bulan' => 1, 'tahun' => 2026]))
            ->assertViewHas('tpps', fn ($rows) => collect($rows->items())->isEmpty());
        $this->actingAs($operatorBaru)
            ->get(route('tpp.index', ['bulan' => 2, 'tahun' => 2026]))
            ->assertViewHas('tpps', fn ($rows) => collect($rows->items())->pluck('id')->all() === [$tppBaru->id]);
    }

    public function test_password_manual_kurang_dari_delapan_karakter_ditolak(): void
    {
        [$unit] = $this->makeUnitAndClass('PASSWORD');
        $admin = User::factory()->create([
            'role' => 'admin',
            'unit_kerja_id' => $unit->id,
        ]);

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Operator Baru',
            'email' => 'operator-baru@example.com',
            'password' => '1234567',
            'password_confirmation' => '1234567',
            'role' => 'operator',
            'unit_kerja_id' => $unit->id,
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'operator-baru@example.com']);
    }

    private function makeUnitAndClass(string $suffix): array
    {
        $unit = UnitKerja::query()->create([
            'nama_unit' => 'Unit ' . $suffix,
            'kode_unit' => $suffix,
        ]);
        $kelas = KelasJabatan::query()->create([
            'unit_kerja_id' => $unit->id,
            'nomor_kelas' => 7,
            'nama_kelas' => 'Kelas ' . $suffix,
            'beban_kerja' => 1000000,
            'prestasi_kerja' => 500000,
            'kondisi_kerja' => 0,
            'kelangkaan_profesi' => 0,
        ]);

        return [$unit, $kelas];
    }

    private function makePegawai(
        UnitKerja $unit,
        KelasJabatan $kelas,
        string $tanggalLahir,
        string $status = Pegawai::STATUS_AKTIF,
        ?string $nip = null
    ): Pegawai {
        return Pegawai::query()->create([
            'nama' => 'Pegawai ' . ($nip ?? $unit->kode_unit),
            'nip' => $nip ?? 'NIP-' . $unit->kode_unit,
            'tanggal_lahir' => $tanggalLahir,
            'no_hp' => '081234567890',
            'golongan' => 'III/A',
            'jabatan' => 'Pelaksana',
            'agama' => 'Islam',
            'kelas_jabatan_id' => $kelas->id,
            'unit_kerja_id' => $unit->id,
            'status_pegawai' => $status,
            'nonaktif_sejak' => $status === Pegawai::STATUS_AKTIF ? null : now()->toDateString(),
        ]);
    }

    private function makeTpp(Pegawai $pegawai, UnitKerja $unit, int $bulan): Tpp
    {
        return Tpp::query()->create([
            'pegawai_id' => $pegawai->id,
            'unit_kerja_id' => $unit->id,
            'bulan' => $bulan,
            'tahun' => 2026,
            'produktivitas' => 100,
            'kehadiran' => 100,
            'perilaku' => 100,
            'iuran_wajib' => 0,
            'tpp_kotor' => 1500000,
            'pajak' => 0,
            'zakat' => 0,
            'total_diterima' => 1500000,
            'pegawai_snapshot' => ['nama' => $pegawai->nama, 'nip' => $pegawai->nip],
        ]);
    }
}
