<?php

namespace Tests\Unit;

use App\Models\Tpp;
use App\Support\SipdRekapBuilder;
use PHPUnit\Framework\TestCase;

class SipdRekapBuilderTest extends TestCase
{
    public function test_jumlah_transfer_sipd_mengikuti_struktur_pendapatan_dan_potongan(): void
    {
        $tpp = $this->makeTpp([
            'produktivitas' => 100,
            'kehadiran' => 100,
            'perilaku' => 100,
            'tpp_tempat_bertugas' => 100000,
            'tunjangan_pph' => 50000,
            'bpjs_kesehatan_pemberi_kerja' => 40000,
            'iuran_jkk' => 10000,
            'iuran_jkm' => 5000,
            'iuran_tapera' => 20000,
            'iuran_pensiun' => 30000,
            'tunjangan_jht' => 25000,
            'iuran_wajib' => 10000,
            'pajak' => 0,
            'zakat' => 25000,
            'bulog' => 15000,
            // Sengaja berbeda: nilai ini merupakan hasil rincian, bukan acuan SIPD.
            'total_diterima' => 965000,
        ]);

        $row = SipdRekapBuilder::rowFromTpp($tpp);

        $this->assertSame(1000000.0, $row['tpp_beban_kerja']);
        $this->assertSame(1280000.0, $row['jumlah_tpp']);
        $this->assertSame(180000.0, $row['jumlah_potongan']);
        $this->assertSame(1100000.0, $row['jumlah_di_transfer']);
        $this->assertNotSame((float) $tpp->total_diterima, $row['jumlah_di_transfer']);
    }

    public function test_total_sipd_merupakan_penjumlahan_setiap_baris(): void
    {
        $first = SipdRekapBuilder::rowFromTpp($this->makeTpp([
            'produktivitas' => 100,
            'kehadiran' => 100,
            'perilaku' => 100,
            'iuran_wajib' => 10000,
        ]));
        $second = SipdRekapBuilder::rowFromTpp($this->makeTpp([
            'produktivitas' => 50,
            'kehadiran' => 50,
            'perilaku' => 50,
            'iuran_wajib' => 5000,
        ]));

        $totals = SipdRekapBuilder::totals([$first, $second]);

        $this->assertSame($first['jumlah_tpp'] + $second['jumlah_tpp'], $totals['jumlah_tpp']);
        $this->assertSame($first['jumlah_potongan'] + $second['jumlah_potongan'], $totals['jumlah_potongan']);
        $this->assertSame($first['jumlah_di_transfer'] + $second['jumlah_di_transfer'], $totals['jumlah_di_transfer']);
    }

    private function makeTpp(array $attributes): Tpp
    {
        return (new Tpp(array_merge([
            'potongan_tpp' => 0,
            'pegawai_snapshot' => [
                'kelas_jabatan' => [
                    'beban_kerja' => 1000000,
                    'prestasi_kerja' => 0,
                    'kondisi_kerja' => 0,
                    'kelangkaan_profesi' => 0,
                ],
            ],
        ], $attributes)))->setRelation('pegawai', null);
    }
}
