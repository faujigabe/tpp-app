<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ConfirmationUiStructureTest extends TestCase
{
    private string $viewsPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->viewsPath = dirname(__DIR__, 2) . '/resources/views';
    }

    public function test_layout_menyediakan_modal_konfirmasi_dan_notifikasi_global(): void
    {
        $layout = (string) file_get_contents($this->viewsPath . '/layouts/main.blade.php');

        $this->assertStringContainsString('id="appConfirmModal"', $layout);
        $this->assertStringContainsString('id="appConfirmModalSubmit"', $layout);
        $this->assertStringContainsString("form.hasAttribute('data-confirm')", $layout);
        $this->assertStringContainsString('form.requestSubmit(submitter || undefined)', $layout);
        $this->assertStringContainsString('data-auto-dismiss="6000"', $layout);
        $this->assertStringContainsString('role="status"', $layout);
    }

    public function test_tindakan_penting_memakai_modal_konfirmasi_standar(): void
    {
        $files = [
            'tpp/create.blade.php',
            'tpp/index.blade.php',
            'pegawai/index.blade.php',
            'kelas_jabatan/index.blade.php',
            'unit-kerja/index.blade.php',
            'users/index.blade.php',
        ];

        foreach ($files as $file) {
            $view = (string) file_get_contents($this->viewsPath . '/' . $file);
            $this->assertStringContainsString('data-confirm', $view, $file);
            $this->assertStringContainsString('data-confirm-title=', $view, $file);
            $this->assertStringContainsString('data-confirm-message=', $view, $file);
            $this->assertStringNotContainsString('return confirm(', $view, $file);
        }
    }

    public function test_pesan_session_tidak_dirender_ganda_di_halaman_fitur(): void
    {
        $files = [
            'tpp/create.blade.php',
            'tpp/index.blade.php',
            'pegawai/import.blade.php',
            'kelas_jabatan/import.blade.php',
            'kelas_jabatan/index.blade.php',
        ];

        foreach ($files as $file) {
            $view = (string) file_get_contents($this->viewsPath . '/' . $file);
            $this->assertStringNotContainsString("session('success')", $view, $file);
            $this->assertStringNotContainsString("session('error')", $view, $file);
        }
    }
}
