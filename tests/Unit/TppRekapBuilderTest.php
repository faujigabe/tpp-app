<?php

namespace Tests\Unit;

use App\Models\Tpp;
use App\Services\TppCalculator;
use App\Support\TppRekapBuilder;
use PHPUnit\Framework\TestCase;

class TppRekapBuilderTest extends TestCase
{
    public function test_rekap_memakai_breakdown_yang_konsisten_dengan_kalkulator(): void
    {
        $snapshot = [
            'agama' => 'Kristen',
            'golongan' => 'III/A',
            'kelas_jabatan' => [
                'beban_kerja' => 1000000,
                'prestasi_kerja' => 500000,
                'kondisi_kerja' => 250000,
                'kelangkaan_profesi' => 125000,
            ],
        ];

        $hasil = (new TppCalculator())->calculateFromSnapshot(
            $snapshot,
            90,
            80,
            70,
            100000,
            50000,
            10,
            false
        );

        $tpp = (new Tpp(array_merge($hasil, [
            'bpjs_kesehatan_pemberi_kerja' => 400000,
            'pegawai_snapshot' => $snapshot,
        ])))->setRelation('pegawai', null);

        $rekap = TppRekapBuilder::rowFromTpp($tpp);

        $jumlahBreakdown = $rekap['beban_jml']
            + $rekap['pres_jml']
            + $rekap['kond_jml']
            + $rekap['lang_jml'];

        $this->assertSame($hasil['tpp_kotor'], $jumlahBreakdown);
        $this->assertSame($hasil['tpp_kotor'] - 100000, $rekap['setelah_bpjs']);
        $this->assertSame($hasil['total_diterima'], $rekap['diterima']);
    }

    public function test_rekap_tidak_menampilkan_nilai_setelah_bpjs_negatif(): void
    {
        $tpp = (new Tpp([
            'produktivitas' => 100,
            'kehadiran' => 100,
            'perilaku' => 100,
            'iuran_wajib' => 200000,
            'tpp_kotor' => 100000,
            'pajak' => 0,
            'zakat' => 0,
            'total_diterima' => 0,
            'pegawai_snapshot' => ['kelas_jabatan' => []],
        ]))->setRelation('pegawai', null);

        $rekap = TppRekapBuilder::rowFromTpp($tpp);

        $this->assertSame(0.0, $rekap['setelah_bpjs']);
        $this->assertSame(0.0, $rekap['setelah_pajak']);
    }

    public function test_total_rekap_merupakan_penjumlahan_baris_yang_sama(): void
    {
        $first = (new Tpp([
            'tpp_kotor' => 100000,
            'iuran_wajib' => 10000,
            'pajak' => 0,
            'zakat' => 0,
            'total_diterima' => 90000,
            'pegawai_snapshot' => ['kelas_jabatan' => []],
        ]))->setRelation('pegawai', null);
        $second = (new Tpp([
            'tpp_kotor' => 200000,
            'iuran_wajib' => 20000,
            'pajak' => 0,
            'zakat' => 0,
            'total_diterima' => 180000,
            'pegawai_snapshot' => ['kelas_jabatan' => []],
        ]))->setRelation('pegawai', null);

        $totals = TppRekapBuilder::totals([$first, $second]);

        $this->assertSame(300000.0, $totals['tpp_kotor']);
        $this->assertSame(270000.0, $totals['setelah_bpjs']);
        $this->assertSame(270000.0, $totals['diterima']);
    }
}
