<?php

namespace Tests\Feature;

use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TppReportExportTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_periode_ekspor_yang_tidak_valid_ditolak(): void
    {
        $operator = User::factory()->create(['role' => 'operator']);

        $this->actingAs($operator)
            ->from('/tpp')
            ->get(route('tpp.export', ['bulan' => 13, 'tahun' => 2026]))
            ->assertRedirect('/tpp')
            ->assertSessionHasErrors('bulan');
    }

    public function test_pdf_tanpa_parameter_memakai_periode_bulan_sebelumnya_dalam_nama_file(): void
    {
        Carbon::setTestNow('2026-08-28 09:00:00');
        $unitKerja = UnitKerja::query()->firstOrCreate(
            ['kode_unit' => 'TEST-REPORT'],
            ['nama_unit' => 'Unit Pengujian Laporan']
        );
        $operator = User::factory()->create([
            'role' => 'operator',
            'unit_kerja_id' => $unitKerja->id,
        ]);

        $this->actingAs($operator)
            ->get(route('tpp.cetak'))
            ->assertOk()
            ->assertDownload('Laporan_TPP_7_2026.pdf');
    }
}
