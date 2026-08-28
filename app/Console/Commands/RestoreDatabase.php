<?php

namespace App\Console\Commands;

use App\Support\MySqlBackupProcess;
use Illuminate\Console\Command;
use RuntimeException;

class RestoreDatabase extends Command
{
    protected $signature = 'database:restore {file : Path lengkap file .sql.gz} {--confirm= : Wajib diisi RESTORE}';
    protected $description = 'Memulihkan database MySQL dari backup terkompresi yang valid';

    public function handle(MySqlBackupProcess $backupProcess): int
    {
        if ($this->option('confirm') !== 'RESTORE') {
            $this->error('Restore dibatalkan. Gunakan --confirm=RESTORE setelah memastikan backup database saat ini tersedia.');
            return self::FAILURE;
        }

        $source = realpath((string) $this->argument('file'));
        if (!$source || !is_file($source) || !str_ends_with(strtolower($source), '.sql.gz')) {
            $this->error('File backup .sql.gz tidak ditemukan atau formatnya tidak sesuai.');
            return self::FAILURE;
        }

        try {
            $this->verifyChecksum($source);
            $sqlPath = tempnam(sys_get_temp_dir(), 'tpp_restore_');
            if ($sqlPath === false) {
                throw new RuntimeException('Tidak dapat membuat file restore sementara.');
            }

            try {
                $this->gunzip($source, $sqlPath);
                $backupProcess->restore($sqlPath);
            } finally {
                @unlink($sqlPath);
            }

            $this->info('Restore database berhasil. Jalankan php artisan optimize:clear sebelum membuka aplikasi.');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            report($e);
            $this->error('Restore gagal: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function verifyChecksum(string $source): void
    {
        $checksumPath = $source . '.sha256';
        if (!is_file($checksumPath)) {
            throw new RuntimeException('File checksum .sha256 tidak ditemukan. Restore dihentikan.');
        }

        $expected = strtolower((string) strtok(trim((string) file_get_contents($checksumPath)), " \t"));
        $actual = strtolower(hash_file('sha256', $source));
        if ($expected === '' || !hash_equals($expected, $actual)) {
            throw new RuntimeException('Checksum backup tidak cocok. File mungkin rusak atau berubah.');
        }
    }

    private function gunzip(string $source, string $destination): void
    {
        $input = gzopen($source, 'rb');
        $output = fopen($destination, 'wb');
        if (!$input || !$output) {
            throw new RuntimeException('Tidak dapat mengekstrak file backup.');
        }

        while (!gzeof($input)) {
            fwrite($output, (string) gzread($input, 1024 * 1024));
        }
        gzclose($input);
        fclose($output);
    }
}
