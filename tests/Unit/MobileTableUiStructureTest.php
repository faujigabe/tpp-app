<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class MobileTableUiStructureTest extends TestCase
{
    private string $viewsPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->viewsPath = dirname(__DIR__, 2) . '/resources/views';
    }

    public function test_layout_menyediakan_transformasi_tabel_menjadi_kartu_mobile(): void
    {
        $layout = (string) file_get_contents($this->viewsPath . '/layouts/main.blade.php');

        $this->assertStringContainsString('@media (max-width: 767.98px)', $layout);
        $this->assertStringContainsString('.mobile-card-table tbody tr', $layout);
        $this->assertStringContainsString('content: attr(data-mobile-label)', $layout);
        $this->assertStringContainsString("table.mobile-card-table", $layout);
        $this->assertStringContainsString("header.dataset.mobileLabel", $layout);
        $this->assertStringContainsString("cell.setAttribute('data-mobile-label'", $layout);
        $this->assertStringContainsString("row.classList.add('mobile-card-empty')", $layout);
    }

    public function test_empat_tabel_operasional_mengaktifkan_mode_kartu_mobile(): void
    {
        $files = [
            'tpp/index.blade.php',
            'pegawai/index.blade.php',
            'audit-logs/index.blade.php',
            'backup-monitor/index.blade.php',
        ];

        foreach ($files as $file) {
            $view = (string) file_get_contents($this->viewsPath . '/' . $file);
            $this->assertStringContainsString('mobile-card-table-wrap', $view, $file);
            $this->assertStringContainsString('mobile-card-table', $view, $file);
        }
    }

    public function test_header_khusus_memiliki_label_mobile_yang_jelas(): void
    {
        $tpp = (string) file_get_contents($this->viewsPath . '/tpp/index.blade.php');
        $pegawai = (string) file_get_contents($this->viewsPath . '/pegawai/index.blade.php');

        $this->assertStringContainsString('data-mobile-label="Nomor"', $tpp);
        $this->assertStringContainsString('data-mobile-label="Pilih"', $pegawai);
    }
}
