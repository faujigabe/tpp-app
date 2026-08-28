<?php

namespace App\Console\Commands;

use App\Support\MySqlBackupProcess;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RuntimeException;

class BackupDatabase extends Command
{
    protected $signature = 'database:backup {--weekly : Salin hasil backup ke lokasi mingguan terpisah}';
    protected $description = 'Membuat backup MySQL terkompresi beserta checksum integritas';

    public function handle(MySqlBackupProcess $backupProcess): int
    {
        if (config('database.default') !== 'mysql') {
            $this->error('Perintah backup hanya mendukung koneksi MySQL.');
            return self::FAILURE;
        }

        $localPath = (string) config('backup.local_path');
        File::ensureDirectoryExists($localPath);
        $baseName = 'tpp_' . now('Asia/Jakarta')->format('Ymd_His') . '.sql';
        $sqlPath = $localPath . DIRECTORY_SEPARATOR . $baseName;
        $gzipPath = $sqlPath . '.gz';

        try {
            $backupProcess->backup($sqlPath);
            if (!is_file($sqlPath) || filesize($sqlPath) === 0) {
                throw new RuntimeException('Hasil mysqldump kosong. Backup dihentikan.');
            }
            $this->gzip($sqlPath, $gzipPath);
            @unlink($sqlPath);
            $this->writeChecksum($gzipPath);

            if ($this->option('weekly')) {
                $this->copyWeekly($gzipPath);
            }

            $this->prune($localPath, (int) config('backup.local_retention_days'));
            $this->info('Backup berhasil: ' . $gzipPath);
            return self::SUCCESS;
        } catch (\Throwable $e) {
            @unlink($sqlPath);
            if (!is_file($gzipPath . '.sha256')) {
                @unlink($gzipPath);
            }
            report($e);
            $this->error('Backup gagal: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function gzip(string $source, string $destination): void
    {
        $input = fopen($source, 'rb');
        $output = gzopen($destination, 'wb9');
        if (!$input || !$output) {
            throw new RuntimeException('Tidak dapat membuat arsip backup terkompresi.');
        }

        while (!feof($input)) {
            gzwrite($output, (string) fread($input, 1024 * 1024));
        }
        fclose($input);
        gzclose($output);
    }

    private function writeChecksum(string $path): void
    {
        file_put_contents($path . '.sha256', hash_file('sha256', $path) . '  ' . basename($path) . PHP_EOL, LOCK_EX);
    }

    private function copyWeekly(string $source): void
    {
        $weeklyPath = trim((string) config('backup.weekly_path'));
        if ($weeklyPath === '') {
            throw new RuntimeException('BACKUP_WEEKLY_PATH belum dikonfigurasi.');
        }

        $normalize = fn ($path) => strtolower(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, rtrim($path, '/\\')));
        if ($normalize($weeklyPath) === $normalize((string) config('backup.local_path'))) {
            throw new RuntimeException('Lokasi backup mingguan harus berbeda dari lokasi backup harian.');
        }

        File::ensureDirectoryExists($weeklyPath);
        $target = $weeklyPath . DIRECTORY_SEPARATOR . basename($source);
        if (!copy($source, $target) || !copy($source . '.sha256', $target . '.sha256')) {
            throw new RuntimeException('Gagal menyalin backup ke lokasi mingguan.');
        }

        $this->prune($weeklyPath, (int) config('backup.weekly_retention_days'));
    }

    private function prune(string $path, int $days): void
    {
        if ($days < 1 || !is_dir($path)) {
            return;
        }

        $cutoff = now()->subDays($days)->getTimestamp();
        foreach (glob($path . DIRECTORY_SEPARATOR . 'tpp_*.sql.gz*') ?: [] as $file) {
            if (is_file($file) && filemtime($file) < $cutoff) {
                @unlink($file);
            }
        }
    }
}
