<?php

namespace Tests\Feature;

use App\Models\TppApproval;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class EkinerjaPdfImportAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdf_tidak_dapat_diimpor_ke_periode_yang_sudah_dikunci(): void
    {
        $unitKerja = UnitKerja::query()->firstOrCreate(
            ['kode_unit' => 'TEST-IMPORT-PDF'],
            ['nama_unit' => 'Unit Pengujian Import PDF']
        );
        $operator = User::factory()->create([
            'role' => 'operator',
            'unit_kerja_id' => $unitKerja->id,
        ]);
        TppApproval::query()->create([
            'unit_kerja_id' => $unitKerja->id,
            'bulan' => 8,
            'tahun' => 2026,
            'status' => TppApproval::STATUS_LOCKED,
        ]);

        $this->actingAs($operator)
            ->post(route('tpp.import-ekinerja-pdf'), [
                'bulan' => 8,
                'tahun' => 2026,
                'ekinerja_pdf' => UploadedFile::fake()->createWithContent(
                    'ekinerja.pdf',
                    "%PDF-1.4\n%%EOF"
                ),
            ])
            ->assertForbidden();
    }
}
