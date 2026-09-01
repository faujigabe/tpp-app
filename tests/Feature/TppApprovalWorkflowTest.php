<?php

namespace Tests\Feature;

use App\Models\KelasJabatan;
use App\Models\Pegawai;
use App\Models\Tpp;
use App\Models\TppApproval;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TppApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_periode_yang_sudah_dikirim_tidak_dapat_dikirim_ulang(): void
    {
        $unitKerja = UnitKerja::query()->firstOrCreate(
            ['kode_unit' => 'TEST-APPROVAL'],
            ['nama_unit' => 'Unit Pengujian Persetujuan']
        );
        $operatorPertama = User::factory()->create([
            'role' => 'operator',
            'unit_kerja_id' => $unitKerja->id,
        ]);
        $operatorKedua = User::factory()->create([
            'role' => 'operator',
            'unit_kerja_id' => $unitKerja->id,
        ]);
        $submittedAt = now()->subHour()->startOfSecond();
        $catatanAwal = '[27-08-2026 09:00] Periode dikirim untuk validasi';

        $approval = TppApproval::query()->create([
            'unit_kerja_id' => $unitKerja->id,
            'bulan' => 8,
            'tahun' => 2026,
            'status' => TppApproval::STATUS_SUBMITTED,
            'submitted_by' => $operatorPertama->id,
            'submitted_at' => $submittedAt,
            'catatan' => $catatanAwal,
        ]);

        $response = $this->actingAs($operatorKedua)->post(route('tpp.submit-period'), [
            'bulan' => 8,
            'tahun' => 2026,
        ]);

        $response->assertSessionHas('error', 'Hanya periode berstatus draft yang dapat dikirim untuk validasi.');

        $approval->refresh();
        $this->assertSame(TppApproval::STATUS_SUBMITTED, $approval->status);
        $this->assertSame($operatorPertama->id, $approval->submitted_by);
        $this->assertTrue($approval->submitted_at->equalTo($submittedAt));
        $this->assertSame($catatanAwal, $approval->catatan);
    }

    public function test_periode_tidak_dapat_diajukan_jika_masih_ada_pegawai_belum_dihitung(): void
    {
        [$unit, $kelas] = $this->makeUnitAndClass();
        $operator = $this->makeUser($unit, 'operator');
        $pegawaiSelesai = $this->makePegawai($unit, $kelas, '001');
        $pegawaiBelum = $this->makePegawai($unit, $kelas, '002');
        $this->makeTpp($pegawaiSelesai, $unit, 8, 0);

        $this->actingAs($operator)
            ->post(route('tpp.submit-period'), ['bulan' => 8, 'tahun' => 2026])
            ->assertSessionHas('error', 'Periode belum siap: 1 pegawai belum memiliki rincian TPP.');

        $this->assertDatabaseMissing('tpp_approvals', [
            'unit_kerja_id' => $unit->id,
            'bulan' => 8,
            'tahun' => 2026,
            'status' => TppApproval::STATUS_SUBMITTED,
        ]);
        $this->assertSame('Pegawai 002', $pegawaiBelum->nama);
    }

    public function test_tpp_nihil_tetap_dianggap_lengkap_dan_dapat_diajukan(): void
    {
        [$unit, $kelas] = $this->makeUnitAndClass();
        $operator = $this->makeUser($unit, 'operator');
        $pegawai = $this->makePegawai($unit, $kelas, 'NIHIL');
        $this->makeTpp($pegawai, $unit, 8, 0);

        $this->actingAs($operator)
            ->post(route('tpp.submit-period'), ['bulan' => 8, 'tahun' => 2026])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tpp_approvals', [
            'unit_kerja_id' => $unit->id,
            'bulan' => 8,
            'tahun' => 2026,
            'status' => TppApproval::STATUS_SUBMITTED,
        ]);
    }

    public function test_super_admin_tidak_dapat_mengunci_pengajuan_yang_tidak_lengkap(): void
    {
        [$unit, $kelas] = $this->makeUnitAndClass();
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $pegawaiSelesai = $this->makePegawai($unit, $kelas, '001');
        $this->makePegawai($unit, $kelas, '002');
        $this->makeTpp($pegawaiSelesai, $unit, 8, 1000000);
        TppApproval::query()->create([
            'unit_kerja_id' => $unit->id,
            'bulan' => 8,
            'tahun' => 2026,
            'status' => TppApproval::STATUS_SUBMITTED,
        ]);

        $this->actingAs($superAdmin)
            ->post(route('tpp.lock-period'), [
                'unit_kerja_id' => $unit->id,
                'bulan' => 8,
                'tahun' => 2026,
            ])
            ->assertSessionHas('error', 'Periode tidak dapat dikunci. Periode belum siap: 1 pegawai belum memiliki rincian TPP.');

        $this->assertDatabaseHas('tpp_approvals', [
            'unit_kerja_id' => $unit->id,
            'status' => TppApproval::STATUS_SUBMITTED,
        ]);
    }

    public function test_alasan_wajib_diisi_dan_dicatat_saat_periode_dibuka(): void
    {
        [$unit] = $this->makeUnitAndClass();
        $superAdmin = User::factory()->create(['name' => 'Super Penguji', 'role' => 'super_admin']);
        $approval = TppApproval::query()->create([
            'unit_kerja_id' => $unit->id,
            'bulan' => 8,
            'tahun' => 2026,
            'status' => TppApproval::STATUS_LOCKED,
        ]);

        $this->actingAs($superAdmin)
            ->post(route('tpp.unlock-period'), [
                'unit_kerja_id' => $unit->id,
                'bulan' => 8,
                'tahun' => 2026,
            ])
            ->assertSessionHasErrors('alasan');

        $alasan = 'Perbaikan data kehadiran hasil verifikasi ulang.';
        $this->actingAs($superAdmin)
            ->post(route('tpp.unlock-period'), [
                'unit_kerja_id' => $unit->id,
                'bulan' => 8,
                'tahun' => 2026,
                'alasan' => $alasan,
            ])
            ->assertSessionHas('success');

        $approval->refresh();
        $this->assertSame(TppApproval::STATUS_DRAFT, $approval->status);
        $this->assertStringContainsString($alasan, (string) $approval->catatan);
        $this->assertStringContainsString('Super Penguji', (string) $approval->catatan);
    }

    public function test_halaman_tpp_menampilkan_nama_pegawai_yang_belum_dihitung(): void
    {
        [$unit, $kelas] = $this->makeUnitAndClass();
        $operator = $this->makeUser($unit, 'operator');
        $this->makePegawai($unit, $kelas, 'BELUM');

        $this->actingAs($operator)
            ->get(route('tpp.index', ['bulan' => 8, 'tahun' => 2026]))
            ->assertOk()
            ->assertSee('Periode belum siap')
            ->assertSee('Pegawai BELUM')
            ->assertSee('Lengkapi Input TPP');
    }

    public function test_kekurangan_unit_lain_tidak_menghambat_pengajuan_unit_pengguna(): void
    {
        [$unitPengguna, $kelasPengguna] = $this->makeUnitAndClass('PENGGUNA');
        [$unitLain, $kelasLain] = $this->makeUnitAndClass('LAIN');
        $operator = $this->makeUser($unitPengguna, 'operator');
        $pegawaiPengguna = $this->makePegawai($unitPengguna, $kelasPengguna, 'PENGGUNA');
        $this->makePegawai($unitLain, $kelasLain, 'LAIN');
        $this->makeTpp($pegawaiPengguna, $unitPengguna, 8, 1000000);

        $this->actingAs($operator)
            ->post(route('tpp.submit-period'), ['bulan' => 8, 'tahun' => 2026])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tpp_approvals', [
            'unit_kerja_id' => $unitPengguna->id,
            'status' => TppApproval::STATUS_SUBMITTED,
        ]);
        $this->assertDatabaseMissing('tpp_approvals', [
            'unit_kerja_id' => $unitLain->id,
            'status' => TppApproval::STATUS_SUBMITTED,
        ]);
    }

    private function makeUnitAndClass(string $suffix = 'READINESS'): array
    {
        $unit = UnitKerja::query()->create([
            'kode_unit' => 'READINESS-' . $suffix,
            'nama_unit' => 'Unit Kesiapan ' . $suffix,
        ]);
        $kelas = KelasJabatan::query()->create([
            'unit_kerja_id' => $unit->id,
            'nomor_kelas' => 7,
            'nama_kelas' => 'Kelas Kesiapan ' . $suffix,
            'beban_kerja' => 1000000,
            'prestasi_kerja' => 0,
            'kondisi_kerja' => 0,
            'kelangkaan_profesi' => 0,
        ]);

        return [$unit, $kelas];
    }

    private function makeUser(UnitKerja $unit, string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'unit_kerja_id' => $unit->id,
        ]);
    }

    private function makePegawai(UnitKerja $unit, KelasJabatan $kelas, string $suffix): Pegawai
    {
        return Pegawai::query()->create([
            'nama' => 'Pegawai ' . $suffix,
            'nip' => 'NIP-' . $suffix,
            'golongan' => 'III/A',
            'jabatan' => 'Pelaksana',
            'agama' => 'Islam',
            'kelas_jabatan_id' => $kelas->id,
            'unit_kerja_id' => $unit->id,
            'status_pegawai' => Pegawai::STATUS_AKTIF,
        ]);
    }

    private function makeTpp(Pegawai $pegawai, UnitKerja $unit, int $bulan, float $total): Tpp
    {
        return Tpp::query()->create([
            'pegawai_id' => $pegawai->id,
            'unit_kerja_id' => $unit->id,
            'bulan' => $bulan,
            'tahun' => 2026,
            'produktivitas' => 0,
            'kehadiran' => 0,
            'perilaku' => 0,
            'iuran_wajib' => 0,
            'tpp_kotor' => $total,
            'pajak' => 0,
            'zakat' => 0,
            'total_diterima' => $total,
        ]);
    }
}
