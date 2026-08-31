<?php

namespace Tests\Feature;

use App\Models\UnitKerja;
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
            ->assertDontSee('Distribusi User Aktif');
    }

    private function unitKerja(): UnitKerja
    {
        return UnitKerja::query()->firstOrCreate(
            ['kode_unit' => 'DASHBOARD-TEST'],
            ['nama_unit' => 'Unit Pengujian Dashboard']
        );
    }
}
