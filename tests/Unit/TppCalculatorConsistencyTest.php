<?php

namespace Tests\Unit;

use App\Models\KelasJabatan;
use App\Models\Pegawai;
use App\Services\TppCalculator;
use PHPUnit\Framework\TestCase;

class TppCalculatorConsistencyTest extends TestCase
{
    public function test_model_pegawai_dan_snapshot_menghasilkan_nilai_yang_sama(): void
    {
        $kelas = new KelasJabatan([
            'beban_kerja' => 1000000,
            'prestasi_kerja' => 500000,
            'kondisi_kerja' => 250000,
            'kelangkaan_profesi' => 125000,
        ]);
        $pegawai = (new Pegawai(['golongan' => 'IV/A', 'agama' => 'Islam']))
            ->setRelation('kelasJabatan', $kelas);
        $snapshot = [
            'golongan' => 'IV/A',
            'agama' => 'Islam',
            'kelas_jabatan' => $kelas->only([
                'beban_kerja', 'prestasi_kerja', 'kondisi_kerja', 'kelangkaan_profesi',
            ]),
        ];

        $calculator = new TppCalculator();
        $dariModel = $calculator->calculate($pegawai, 90, 80, 70, 100000, 50000, 10, true);
        $dariSnapshot = $calculator->calculateFromSnapshot($snapshot, 90, 80, 70, 100000, 50000, 10, true);

        $this->assertSame($dariSnapshot, $dariModel);
    }

    public function test_tarif_lama_golongan_dua_tiga_dan_empat_tetap_konsisten(): void
    {
        $calculator = new TppCalculator();

        $golonganDua = $calculator->calculateFromSnapshot($this->snapshot('II/A'), 100, 100, 100, 0, 0, 0, true);
        $golonganTiga = $calculator->calculateFromSnapshot($this->snapshot('III/A'), 100, 100, 100, 0, 0, 0, true);
        $golonganEmpat = $calculator->calculateFromSnapshot($this->snapshot('IV/A'), 100, 100, 100, 0, 0, 0, true);

        $this->assertSame(50000.0, $golonganDua['pajak']);
        $this->assertSame(50000.0, $golonganTiga['pajak']);
        $this->assertSame(150000.0, $golonganEmpat['pajak']);
    }

    public function test_zakat_hanya_diterapkan_untuk_pegawai_islam(): void
    {
        $calculator = new TppCalculator();
        $islam = $calculator->calculateFromSnapshot($this->snapshot('III/A', 'Islam'), 100, 100, 100, 0);
        $nonIslam = $calculator->calculateFromSnapshot($this->snapshot('III/A', 'Kristen'), 100, 100, 100, 0);

        $this->assertSame(25000.0, $islam['zakat']);
        $this->assertSame(975000.0, $islam['total_diterima']);
        $this->assertSame(0.0, $nonIslam['zakat']);
        $this->assertSame(1000000.0, $nonIslam['total_diterima']);
    }

    public function test_potongan_seratus_persen_menghasilkan_tpp_nol(): void
    {
        $hasil = (new TppCalculator())->calculateFromSnapshot(
            $this->snapshot('III/A', 'Islam'),
            100,
            100,
            100,
            0,
            0,
            100,
            true
        );

        $this->assertSame(0.0, $hasil['tpp_kotor']);
        $this->assertSame(0.0, $hasil['pajak']);
        $this->assertSame(0.0, $hasil['zakat']);
        $this->assertSame(0.0, $hasil['total_diterima']);
    }

    public function test_iuran_melebihi_tpp_tidak_menghasilkan_nilai_negatif(): void
    {
        $hasil = (new TppCalculator())->calculateFromSnapshot(
            $this->snapshot('IV/A', 'Islam'),
            100,
            100,
            100,
            2000000,
            0,
            0,
            true
        );

        $this->assertSame(0.0, $hasil['pajak']);
        $this->assertSame(0.0, $hasil['zakat']);
        $this->assertSame(0.0, $hasil['total_diterima']);
    }

    private function snapshot(string $golongan, string $agama = 'Kristen'): array
    {
        return [
            'golongan' => $golongan,
            'agama' => $agama,
            'kelas_jabatan' => [
                'beban_kerja' => 1000000,
                'prestasi_kerja' => 0,
                'kondisi_kerja' => 0,
                'kelangkaan_profesi' => 0,
            ],
        ];
    }
}
