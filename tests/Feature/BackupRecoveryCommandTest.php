<?php

namespace Tests\Feature;

use App\Support\MySqlBackupProcess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Mockery;
use Tests\TestCase;

class BackupRecoveryCommandTest extends TestCase
{
    use RefreshDatabase;

    private string $backupRoot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->backupRoot = storage_path('framework/testing-backup-' . uniqid());
        config([
            'backup.local_path' => $this->backupRoot . DIRECTORY_SEPARATOR . 'daily',
            'backup.weekly_path' => $this->backupRoot . DIRECTORY_SEPARATOR . 'weekly',
            'backup.local_retention_days' => 14,
            'backup.weekly_retention_days' => 365,
        ]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->backupRoot);
        parent::tearDown();
    }

    public function test_backup_membuat_arsip_checksum_dan_salinan_mingguan(): void
    {
        $process = Mockery::mock(MySqlBackupProcess::class);
        $process->shouldReceive('backup')->once()->andReturnUsing(function ($path) {
            file_put_contents($path, "CREATE TABLE example (id INT);\n");
        });
        $this->app->instance(MySqlBackupProcess::class, $process);

        $this->artisan('database:backup', ['--weekly' => true])->assertSuccessful();

        $daily = glob($this->backupRoot . '/daily/*.sql.gz');
        $weekly = glob($this->backupRoot . '/weekly/*.sql.gz');
        $this->assertCount(1, $daily);
        $this->assertCount(1, $weekly);
        $this->assertFileExists($daily[0] . '.sha256');
        $this->assertSame(hash_file('sha256', $daily[0]), strtok(trim(file_get_contents($daily[0] . '.sha256')), " \t"));
    }

    public function test_restore_ditolak_tanpa_konfirmasi_eksplisit(): void
    {
        $this->artisan('database:restore', ['file' => 'tidak-ada.sql.gz'])->assertFailed();
    }

    public function test_restore_memverifikasi_checksum_sebelum_memanggil_mysql(): void
    {
        File::ensureDirectoryExists($this->backupRoot);
        $archive = $this->backupRoot . DIRECTORY_SEPARATOR . 'valid.sql.gz';
        $gzip = gzopen($archive, 'wb9');
        gzwrite($gzip, "CREATE TABLE example (id INT);\n");
        gzclose($gzip);
        file_put_contents($archive . '.sha256', hash_file('sha256', $archive) . '  ' . basename($archive));

        $process = Mockery::mock(MySqlBackupProcess::class);
        $process->shouldReceive('restore')->once()->with(Mockery::on(function ($path) {
            return is_file($path) && str_contains(file_get_contents($path), 'CREATE TABLE example');
        }));
        $this->app->instance(MySqlBackupProcess::class, $process);

        $this->artisan('database:restore', ['file' => $archive, '--confirm' => 'RESTORE'])->assertSuccessful();
    }
}
