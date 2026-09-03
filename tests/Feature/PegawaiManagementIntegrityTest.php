<?php

namespace Tests\Feature;

use App\Models\KelasJabatan;
use App\Models\Pegawai;
use App\Models\Tpp;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PegawaiManagementIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_riwayat_tpp_tetap_mengikuti_unit_saat_tpp_dibuat(): void
    {
        [$unitLama, $kelasLama] = $this->makeUnitAndClass('LAMA');
        [$unitBaru, $kelasBaru] = $this->makeUnitAndClass('BARU');
        $pegawai = $this->makePegawai($unitLama, $kelasLama);

        $riwayat = $this->makeTpp($pegawai, $unitLama, 1);
        $pegawai->update([
            'unit_kerja_id' => $unitBaru->id,
            'kelas_jabatan_id' => $kelasBaru->id,
        ]);

        $this->assertTrue(Tpp::query()->forUnit($unitLama->id)->whereKey($riwayat->id)->exists());
        $this->assertFalse(Tpp::query()->forUnit($unitBaru->id)->whereKey($riwayat->id)->exists());
    }

    public function test_tpp_lama_tanpa_unit_menggunakan_unit_pegawai_sebagai_fallback(): void
    {
        [$unit, $kelas] = $this->makeUnitAndClass('FALLBACK');
        $pegawai = $this->makePegawai($unit, $kelas);
        $tpp = $this->makeTpp($pegawai, null, 2);

        $this->assertTrue(Tpp::query()->forUnit($unit->id)->whereKey($tpp->id)->exists());
    }

    public function test_akun_terkait_mengikuti_perpindahan_unit_pegawai(): void
    {
        [$unitLama, $kelasLama] = $this->makeUnitAndClass('AKUN-LAMA');
        [$unitBaru, $kelasBaru] = $this->makeUnitAndClass('AKUN-BARU');
        $pegawai = $this->makePegawai($unitLama, $kelasLama);
        $user = User::factory()->create([
            'role' => 'viewer',
            'pegawai_id' => $pegawai->id,
            'unit_kerja_id' => $unitLama->id,
        ]);

        $pegawai->update([
            'unit_kerja_id' => $unitBaru->id,
            'kelas_jabatan_id' => $kelasBaru->id,
        ]);

        $this->assertSame($unitBaru->id, $user->fresh()->unit_kerja_id);
    }

    public function test_tombol_whatsapp_memakai_nomor_pegawai_terbaru(): void
    {
        [$unit, $kelas] = $this->makeUnitAndClass('WA-TERBARU');
        $pegawai = $this->makePegawai($unit, $kelas);
        $riwayat = $this->makeTpp($pegawai, $unit, 1);
        $riwayat->update([
            'pegawai_snapshot' => array_merge($riwayat->pegawai_snapshot ?? [], [
                'no_hp' => '081234567890',
            ]),
        ]);
        $operator = User::factory()->create([
            'role' => 'operator',
            'unit_kerja_id' => $unit->id,
        ]);

        $pegawai->update(['no_hp' => '087654321098']);
        $riwayat->refresh()->load('pegawai');

        $this->assertSame('081234567890', $riwayat->referensi_no_hp);
        $this->assertSame('087654321098', $riwayat->nomor_whatsapp);

        $this->actingAs($operator)
            ->get(route('tpp.index', ['bulan' => 1, 'tahun' => 2026]))
            ->assertOk()
            ->assertSee('https://wa.me/6287654321098', false);
    }

    public function test_kelas_jabatan_yang_dipakai_pegawai_tidak_dapat_dihapus(): void
    {
        [$unit, $kelas] = $this->makeUnitAndClass('KELAS');
        $this->makePegawai($unit, $kelas);
        $operator = User::factory()->create([
            'role' => 'operator',
            'unit_kerja_id' => $unit->id,
        ]);

        $this->actingAs($operator)
            ->delete(route('kelas-jabatan.destroy', $kelas))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('kelas_jabatans', ['id' => $kelas->id]);
        $this->assertDatabaseCount('pegawais', 1);
    }

    public function test_pegawai_yang_terhubung_akun_tidak_dapat_dihapus(): void
    {
        [$unit, $kelas] = $this->makeUnitAndClass('HAPUS');
        $pegawai = $this->makePegawai($unit, $kelas);
        $operator = User::factory()->create([
            'role' => 'operator',
            'unit_kerja_id' => $unit->id,
        ]);
        User::factory()->create([
            'role' => 'viewer',
            'pegawai_id' => $pegawai->id,
            'unit_kerja_id' => $unit->id,
        ]);

        $this->actingAs($operator)
            ->delete(route('pegawai.destroy', $pegawai))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('pegawais', ['id' => $pegawai->id]);
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

    private function makePegawai(UnitKerja $unit, KelasJabatan $kelas): Pegawai
    {
        return Pegawai::query()->create([
            'nama' => 'Pegawai ' . $unit->kode_unit,
            'nip' => 'NIP-' . $unit->kode_unit,
            'no_hp' => '081234567890',
            'golongan' => 'III/A',
            'jabatan' => 'Pelaksana',
            'agama' => 'Islam',
            'kelas_jabatan_id' => $kelas->id,
            'unit_kerja_id' => $unit->id,
            'status_pegawai' => Pegawai::STATUS_AKTIF,
        ]);
    }

    private function makeTpp(Pegawai $pegawai, ?UnitKerja $unit, int $bulan): Tpp
    {
        return Tpp::query()->create([
            'pegawai_id' => $pegawai->id,
            'unit_kerja_id' => $unit?->id,
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
