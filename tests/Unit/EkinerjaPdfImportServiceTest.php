<?php

namespace Tests\Unit;

use App\Models\Pegawai;
use App\Services\EkinerjaPdfImportService;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class EkinerjaPdfImportServiceTest extends TestCase
{
    private string $pdfPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pdfPath = tempnam(sys_get_temp_dir(), 'ekinerja-test-');
        file_put_contents($this->pdfPath, '%PDF-1.4');
    }

    protected function tearDown(): void
    {
        if (isset($this->pdfPath) && is_file($this->pdfPath)) {
            unlink($this->pdfPath);
        }

        parent::tearDown();
    }

    public function test_pdf_tanpa_periode_ditolak(): void
    {
        $service = $this->serviceWithText($this->recordText(includePeriod: false));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Periode PDF tidak dapat dikenali.');

        $service->import($this->pdfPath, collect([$this->pegawai()]), 8, 2026);
    }

    public function test_pdf_tanpa_baris_nilai_ditolak(): void
    {
        $service = $this->serviceWithText("Bulan Agustus Tahun 2026\nTidak ada data pegawai");

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Tidak ada baris nilai e-Kinerja yang dapat dibaca dari PDF.');

        $service->import($this->pdfPath, collect([$this->pegawai()]), 8, 2026);
    }

    public function test_baris_ganda_untuk_pegawai_yang_sama_ditolak(): void
    {
        $service = $this->serviceWithText(
            $this->recordText() . "\n" . $this->recordText(includePeriod: false)
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('PDF memuat lebih dari satu baris untuk pegawai Fauji Gabe.');

        $service->import($this->pdfPath, collect([$this->pegawai()]), 8, 2026);
    }

    public function test_satu_baris_valid_dicocokkan_berdasarkan_nip(): void
    {
        $service = $this->serviceWithText($this->recordText());

        $result = $service->import($this->pdfPath, collect([$this->pegawai()]), 8, 2026);

        $this->assertSame(1, $result['record_count']);
        $this->assertSame(1, $result['matched_count']);
        $this->assertSame(['nip' => 1, 'nama' => 0], $result['matched_by']);
        $this->assertSame(90.0, $result['matched'][10]['kehadiran']);
        $this->assertSame(80.0, $result['matched'][10]['perilaku']);
        $this->assertSame(70.0, $result['matched'][10]['produktivitas']);
    }

    private function serviceWithText(string $text): EkinerjaPdfImportService
    {
        return new class($text) extends EkinerjaPdfImportService
        {
            public function __construct(private string $text) {}

            public function extractText(string $path): string
            {
                return $this->text;
            }
        };
    }

    private function pegawai(): Pegawai
    {
        return (new Pegawai())->forceFill([
            'id' => 10,
            'nip' => '199411012025041002',
            'nama' => 'Fauji Gabe',
        ]);
    }

    private function recordText(bool $includePeriod = true): string
    {
        return implode("\n", array_filter([
            $includePeriod ? 'Bulan Agustus Tahun 2026' : null,
            'Fauji Gabe',
            '199411012025041002',
            '90',
            '80',
            '70',
        ], fn ($line) => $line !== null));
    }
}
