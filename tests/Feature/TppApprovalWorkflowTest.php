<?php

namespace Tests\Feature;

use App\Models\TppApproval;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TppApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_periode_yang_sudah_dikirim_tidak_dapat_dikirim_ulang(): void
    {
        $unitKerja = UnitKerja::query()->firstOrCreate(
            ['kode_unit' => 'TEST-APPROVAL'],
            ['nama_unit' => 'Unit Pengujian Persetujuan']
        );
        $operatorPertama = User::factory()->create([
            'role' => 'operator',
            'unit_kerja_id' => $unitKerja->id,
        ]);
        $operatorKedua = User::factory()->create([
            'role' => 'operator',
            'unit_kerja_id' => $unitKerja->id,
        ]);
        $submittedAt = now()->subHour()->startOfSecond();
        $catatanAwal = '[27-08-2026 09:00] Periode dikirim untuk validasi';

        $approval = TppApproval::query()->create([
            'unit_kerja_id' => $unitKerja->id,
            'bulan' => 8,
            'tahun' => 2026,
            'status' => TppApproval::STATUS_SUBMITTED,
            'submitted_by' => $operatorPertama->id,
            'submitted_at' => $submittedAt,
            'catatan' => $catatanAwal,
        ]);

        $response = $this->actingAs($operatorKedua)->post(route('tpp.submit-period'), [
            'bulan' => 8,
            'tahun' => 2026,
        ]);

        $response->assertSessionHas('error', 'Hanya periode berstatus draft yang dapat dikirim untuk validasi.');

        $approval->refresh();
        $this->assertSame(TppApproval::STATUS_SUBMITTED, $approval->status);
        $this->assertSame($operatorPertama->id, $approval->submitted_by);
        $this->assertTrue($approval->submitted_at->equalTo($submittedAt));
        $this->assertSame($catatanAwal, $approval->catatan);
    }
}
