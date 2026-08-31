<?php

namespace Tests\Feature;

use App\Models\UnitKerja;
use App\Models\TppApproval;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRoleExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_melihat_pusat_pengawasan_dan_aksi_sistem(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($superAdmin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pusat Pengawasan TPP Pemerintah Daerah')
            ->assertSee('Jejak Perubahan')
            ->assertSee('Monitoring Backup');
    }

    public function test_admin_melihat_pengelolaan_dan_laporan_unit_kerja(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'unit_kerja_id' => $this->unitKerja()->id,
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pengelolaan TPP Unit Kerja')
            ->assertSee('Input TPP')
            ->assertSee('Rekap TPP');
    }

    public function test_operator_melihat_prioritas_input_bukan_distribusi_user(): void
    {
        $operator = User::factory()->create([
            'role' => 'operator',
            'unit_kerja_id' => $this->unitKerja()->id,
        ]);

        $this->actingAs($operator)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Penyelesaian Input TPP Periode Berjalan')
            ->assertSee('Fokus Operator')
            ->assertSee('Periksa dan ajukan periode')
            ->assertDontSee('Distribusi User Aktif');
    }

    public function test_admin_melihat_status_pengajuan_dan_aktivitas_periode(): void
    {
        $unit = $this->unitKerja();
        $admin = User::factory()->create([
            'name' => 'Admin Penguji',
            'role' => 'admin',
            'unit_kerja_id' => $unit->id,
        ]);
        TppApproval::query()->create([
            'unit_kerja_id' => $unit->id,
            'bulan' => 8,
            'tahun' => 2026,
            'status' => TppApproval::STATUS_SUBMITTED,
            'submitted_by' => $admin->id,
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard', ['bulan' => 8, 'tahun' => 2026]))
            ->assertOk()
            ->assertSee('Menunggu Validasi')
            ->assertSee('Menunggu validasi')
            ->assertSee('Aktivitas Alur Periode Terbaru')
            ->assertSee('Admin Penguji');
    }

    public function test_super_admin_melihat_ringkasan_status_seluruh_opd(): void
    {
        $unitSatu = $this->unitKerja();
        $unitDua = UnitKerja::query()->create([
            'kode_unit' => 'DASHBOARD-TEST-2',
            'nama_unit' => 'Unit Pengujian Dashboard Dua',
        ]);
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        TppApproval::query()->create([
            'unit_kerja_id' => $unitSatu->id,
            'bulan' => 8,
            'tahun' => 2026,
            'status' => TppApproval::STATUS_SUBMITTED,
        ]);
        TppApproval::query()->create([
            'unit_kerja_id' => $unitDua->id,
            'bulan' => 8,
            'tahun' => 2026,
            'status' => TppApproval::STATUS_LOCKED,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('dashboard', ['bulan' => 8, 'tahun' => 2026]))
            ->assertOk()
            ->assertSee('Status seluruh OPD')
            ->assertSee('1 pengajuan menunggu')
            ->assertSee('Unit Pengujian Dashboard Dua');
    }

    private function unitKerja(): UnitKerja
    {
        return UnitKerja::query()->firstOrCreate(
            ['kode_unit' => 'DASHBOARD-TEST'],
            ['nama_unit' => 'Unit Pengujian Dashboard']
        );
    }
}
