<?php

namespace Tests\Unit;

use App\Services\TppCalculator;
use PHPUnit\Framework\TestCase;

class TppCalculatorTaxTest extends TestCase
{
    private array $snapshot = [
        'golongan' => 'III/A',
        'agama' => 'Kristen',
        'kelas_jabatan' => [
            'beban_kerja' => 1000000,
            'prestasi_kerja' => 0,
            'kondisi_kerja' => 0,
            'kelangkaan_profesi' => 0,
        ],
    ];

    public function test_pajak_nonaktif_secara_bawaan(): void
    {
        $hasil = (new TppCalculator())->calculateFromSnapshot(
            $this->snapshot,
            100,
            100,
            100,
            0
        );

        $this->assertFalse($hasil['hitung_pajak']);
        $this->assertSame(0.0, $hasil['pajak']);
        $this->assertSame(1000000.0, $hasil['total_diterima']);
    }

    public function test_rumus_lama_tetap_tersedia_jika_diaktifkan(): void
    {
        $hasil = (new TppCalculator())->calculateFromSnapshot(
            $this->snapshot,
            100,
            100,
            100,
            0,
            0,
            0,
            true
        );

        $this->assertTrue($hasil['hitung_pajak']);
        $this->assertSame(50000.0, $hasil['pajak']);
        $this->assertSame(950000.0, $hasil['total_diterima']);
    }
}
