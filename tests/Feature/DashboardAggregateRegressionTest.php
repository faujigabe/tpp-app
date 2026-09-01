<?php

namespace Tests\Feature;

use App\Models\KelasJabatan;
use App\Models\Pegawai;
use App\Models\Tpp;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAggregateRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_agregasi_dashboard_tetap_menghasilkan_total_periode_yang_sama(): void
    {
        $unit = UnitKerja::query()->create(['kode_unit' => 'AGG', 'nama_unit' => 'Unit Agregasi']);
        $kelas = KelasJabatan::query()->create([
            'unit_kerja_id' => $unit->id,
            'nomor_kelas' => 7,
            'nama_kelas' => 'Kelas Agregasi',
            'beban_kerja' => 1000000,
            'prestasi_kerja' => 0,
            'kondisi_kerja' => 0,
            'kelangkaan_profesi' => 0,
        ]);
        $admin = User::factory()->create(['role' => 'admin', 'unit_kerja_id' => $unit->id]);
        $pegawaiSatu = $this->pegawai($unit, $kelas, '001');
        $pegawaiDua = $this->pegawai($unit, $kelas, '002');
        $this->tpp($pegawaiSatu, $unit, 1000000, 10000, 5000, 25000, 960000);
        $this->tpp($pegawaiDua, $unit, 2000000, 20000, 10000, 50000, 1920000);

        $this->actingAs($admin)
            ->get(route('dashboard', ['bulan' => 8, 'tahun' => 2026]))
            ->assertOk()
            ->assertViewHas('jumlahPerhitungan', 2)
            ->assertViewHas('totalTppKotor', 3000000.0)
            ->assertViewHas('totalBpjs', 30000.0)
            ->assertViewHas('totalPajak', 15000.0)
            ->assertViewHas('totalZakat', 75000.0)
            ->assertViewHas('totalDiterima', 2880000.0);
    }

    private function pegawai(UnitKerja $unit, KelasJabatan $kelas, string $suffix): Pegawai
    {
        return Pegawai::query()->create([
            'nama' => 'Pegawai ' . $suffix,
            'nip' => 'NIP-AGG-' . $suffix,
            'golongan' => 'III/A',
            'jabatan' => 'Pelaksana',
            'agama' => 'Islam',
            'kelas_jabatan_id' => $kelas->id,
            'unit_kerja_id' => $unit->id,
            'status_pegawai' => Pegawai::STATUS_AKTIF,
        ]);
    }

    private function tpp(Pegawai $pegawai, UnitKerja $unit, float $kotor, float $bpjs, float $pajak, float $zakat, float $diterima): Tpp
    {
        return Tpp::query()->create([
            'pegawai_id' => $pegawai->id,
            'unit_kerja_id' => $unit->id,
            'bulan' => 8,
            'tahun' => 2026,
            'produktivitas' => 100,
            'kehadiran' => 100,
            'perilaku' => 100,
            'iuran_wajib' => $bpjs,
            'tpp_kotor' => $kotor,
            'pajak' => $pajak,
            'zakat' => $zakat,
            'total_diterima' => $diterima,
        ]);
    }
}
