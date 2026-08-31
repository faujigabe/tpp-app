<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class TppMassalViewStructureTest extends TestCase
{
    private string $view;

    protected function setUp(): void
    {
        parent::setUp();
        $this->view = (string) file_get_contents(
            dirname(__DIR__, 2) . '/resources/views/tpp/create.blade.php'
        );
    }

    public function test_input_massal_memiliki_kelompok_kolom_yang_lengkap(): void
    {
        $this->assertStringContainsString('data-tpp-group="kinerja"', $this->view);
        $this->assertStringContainsString('data-tpp-group="potongan"', $this->view);
        $this->assertStringContainsString('data-tpp-group="sipd"', $this->view);
        $this->assertStringContainsString('data-tpp-group="all"', $this->view);
        $this->assertSame(6, substr_count($this->view, 'data-column-group="kinerja"'));
        $this->assertSame(8, substr_count($this->view, 'data-column-group="potongan"'));
        $this->assertSame(18, substr_count($this->view, 'data-column-group="sipd"'));
        $this->assertStringContainsString("querySelector(':invalid')", $this->view);
        $this->assertStringContainsString('aria-live="polite"', $this->view);
    }

    public function test_seluruh_field_perhitungan_tetap_tersedia(): void
    {
        $fields = [
            'produktivitas',
            'kehadiran',
            'perilaku',
            'tambahan_tpp',
            'potongan_tpp',
            'bpjs_kesehatan',
            'bpjs_kesehatan_pemberi_kerja',
            'tpp_tempat_bertugas',
            'tunjangan_pph',
            'hitung_pajak',
            'iuran_jkk',
            'iuran_jkm',
            'iuran_tapera',
            'iuran_pensiun',
            'tunjangan_jht',
            'bulog',
        ];

        foreach ($fields as $field) {
            $this->assertStringContainsString('name="' . $field . '[{{ $pid }}]"', $this->view);
        }

        $this->assertStringContainsString("route('tpp.store')", $this->view);
        $this->assertStringContainsString('id="tppMassalForm"', $this->view);
    }
}
