<?php

namespace App\Console\Commands;

use App\Models\BackupRun;
use App\Support\MySqlBackupProcess;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class BackupDatabase extends Command
{
    protected $signature = 'database:backup
    {--weekly : Salin hasil backup ke lokasi mingguan terpisah}
    {--monthly : Salin backup harian terbaru ke lokasi bulanan}';
    protected $description = 'Membuat backup MySQL terkompresi beserta checksum integritas';

    public function handle(MySqlBackupProcess $backupProcess): int
    {
        $type = $this->option('monthly')
            ? 'monthly'
            : ($this->option('weekly') ? 'weekly' : 'daily');

        $run = $this->startRun($type);

        if ($this->option('monthly')) {
            return $this->handleMonthly($run);
        }

        if (config('database.default') !== 'mysql') {
            $message = 'Perintah backup hanya mendukung koneksi MySQL.';
            $this->recordFailure($run, $type, $message);
            $this->error($message);
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
            $checksum = hash_file('sha256', $gzipPath);
            $this->recordSuccess($run, $type, $gzipPath, (int) filesize($gzipPath), $checksum);
            $this->info('Backup berhasil: ' . $gzipPath);
            return self::SUCCESS;
        } catch (\Throwable $e) {
            @unlink($sqlPath);
            if (!is_file($gzipPath . '.sha256')) {
                @unlink($gzipPath);
            }
            report($e);
            $this->recordFailure($run, $type, $e->getMessage());
            $this->error('Backup gagal: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function handleMonthly(?BackupRun $run): int
    {
        $localPath = (string) config('backup.local_path');
        $monthlyPath = trim((string) config('backup.monthly_path'));

        if ($monthlyPath === '') {
            $message = 'BACKUP_MONTHLY_PATH belum dikonfigurasi.';
            $this->recordFailure($run, 'monthly', $message);
            $this->error($message);
            return self::FAILURE;
        }

        try {
            File::ensureDirectoryExists($monthlyPath);

            $files = glob($localPath . DIRECTORY_SEPARATOR . 'tpp_*.sql.gz') ?: [];
            $files = array_filter($files, 'is_file');

            if ($files === []) {
                throw new RuntimeException('Tidak ditemukan backup harian untuk dijadikan backup bulanan.');
            }

            usort($files, fn($a, $b) => filemtime($b) <=> filemtime($a));
            $source = $files[0];

            $sourceChecksum = $source . '.sha256';
            if (!is_file($sourceChecksum)) {
                throw new RuntimeException('Checksum backup harian tidak ditemukan.');
            }

            $target = $monthlyPath . DIRECTORY_SEPARATOR . basename($source);

            if (!copy($source, $target)) {
                throw new RuntimeException('Gagal menyalin backup harian ke lokasi bulanan.');
            }

            if (!copy($sourceChecksum, $target . '.sha256')) {
                @unlink($target);
                throw new RuntimeException('Gagal menyalin checksum backup bulanan.');
            }

            $checksum = hash_file('sha256', $target);
            $size = (int) filesize($target);

            $this->prune($monthlyPath, (int) config('backup.monthly_retention_days'));

            $this->recordSuccess(
                $run,
                'monthly',
                $target,
                $size,
                $checksum
            );

            $this->info('Backup bulanan berhasil: ' . $target);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            report($e);
            $this->recordFailure($run, 'monthly', $e->getMessage());
            $this->error('Backup bulanan gagal: ' . $e->getMessage());

            return self::FAILURE;
        }
    }

    private function startRun(string $type): ?BackupRun
    {
        try {
            return BackupRun::query()->create([
                'type' => $type,
                'status' => 'running',
                'started_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Riwayat proses backup tidak dapat dibuat.', [
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function recordSuccess(?BackupRun $run, string $type, string $path, int $size, string $checksum): void
    {
        $context = compact('type', 'path', 'size', 'checksum');
        Log::info('Backup database berhasil.', $context);

        if (!$run) {
            return;
        }

        try {
            $run->update([
                'status' => 'success',
                'file_path' => $path,
                'size_bytes' => $size,
                'checksum' => $checksum,
                'finished_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Riwayat keberhasilan backup tidak dapat diperbarui.', ['error' => $e->getMessage()] + $context);
        }
    }

    private function recordFailure(?BackupRun $run, string $type, string $message): void
    {
        Log::error('Backup database gagal.', ['type' => $type, 'error' => $message]);

        if (!$run) {
            return;
        }

        try {
            $run->update([
                'status' => 'failed',
                'error_message' => mb_substr($message, 0, 65000),
                'finished_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Riwayat kegagalan backup tidak dapat diperbarui.', ['error' => $e->getMessage()]);
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

        $normalize = fn($path) => strtolower(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, rtrim($path, '/\\')));
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
