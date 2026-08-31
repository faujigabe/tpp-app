<?php

namespace Tests\Feature;

use App\Models\BackupRun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BackupMonitorTest extends TestCase
{
    use RefreshDatabase;

    private string $backupRoot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->backupRoot = storage_path('framework/testing-monitor-' . uniqid());
        config([
            'backup.local_path' => $this->backupRoot . DIRECTORY_SEPARATOR . 'daily',
            'backup.weekly_path' => $this->backupRoot . DIRECTORY_SEPARATOR . 'weekly',
            'backup.daily_health_max_age_hours' => 26,
            'backup.weekly_health_max_age_hours' => 192,
        ]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->backupRoot);
        parent::tearDown();
    }

    public function test_hanya_super_admin_dapat_membuka_monitoring_backup(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($admin)->get(route('backup-monitor.index'))->assertForbidden();
        $this->actingAs($superAdmin)->get(route('backup-monitor.index'))->assertOk();
    }

    public function test_monitor_menampilkan_backup_sehat_dan_riwayat_proses(): void
    {
        foreach (['daily', 'weekly'] as $type) {
            $path = $this->backupRoot . DIRECTORY_SEPARATOR . $type;
            File::ensureDirectoryExists($path);
            $file = $path . DIRECTORY_SEPARATOR . 'tpp_20260831_010000.sql.gz';
            file_put_contents($file, 'backup-valid');
            file_put_contents($file . '.sha256', hash_file('sha256', $file));
        }

        BackupRun::query()->create([
            'type' => 'daily',
            'status' => 'success',
            'file_path' => 'storage/app/backups/daily/tpp_20260831_010000.sql.gz',
            'size_bytes' => 12,
            'checksum' => str_repeat('a', 64),
            'started_at' => now(),
            'finished_at' => now(),
        ]);

        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($superAdmin)
            ->get(route('backup-monitor.index'))
            ->assertOk()
            ->assertSee('Backup Sehat')
            ->assertSee('tpp_20260831_010000.sql.gz')
            ->assertSee('Berhasil');
    }

    public function test_monitor_memberi_peringatan_saat_backup_tidak_ada(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($superAdmin)
            ->get(route('backup-monitor.index'))
            ->assertOk()
            ->assertSee('Perlu Perhatian')
            ->assertSee('Belum ditemukan');
    }
}
