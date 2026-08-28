<?php

namespace App\Support;

use RuntimeException;

class MySqlBackupProcess
{
    public function backup(string $sqlPath): void
    {
        $configPath = $this->temporaryClientConfig();

        try {
            $command = [
                (string) config('backup.mysqldump_binary'),
                '--defaults-extra-file=' . $configPath,
                '--host=' . config('database.connections.mysql.host'),
                '--port=' . config('database.connections.mysql.port'),
                '--single-transaction',
                '--routines',
                '--triggers',
                '--events',
                '--hex-blob',
                '--default-character-set=utf8mb4',
                (string) config('database.connections.mysql.database'),
            ];

            $this->run($command, [['file', $sqlPath, 'w'], ['pipe', 'w']]);
        } finally {
            @unlink($configPath);
        }
    }

    public function restore(string $sqlPath): void
    {
        $configPath = $this->temporaryClientConfig();

        try {
            $command = [
                (string) config('backup.mysql_binary'),
                '--defaults-extra-file=' . $configPath,
                '--host=' . config('database.connections.mysql.host'),
                '--port=' . config('database.connections.mysql.port'),
                '--default-character-set=utf8mb4',
                (string) config('database.connections.mysql.database'),
            ];

            $this->run($command, [['file', $sqlPath, 'r'], ['pipe', 'w']], true);
        } finally {
            @unlink($configPath);
        }
    }

    private function run(array $command, array $io, bool $inputFile = false): void
    {
        $descriptors = [
            0 => $inputFile ? $io[0] : ['pipe', 'r'],
            1 => $inputFile ? ['file', PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null', 'w'] : $io[0],
            2 => $io[1],
        ];
        $pipes = [];
        $process = proc_open($command, $descriptors, $pipes, base_path());

        if (! is_resource($process)) {
            throw new RuntimeException('Tidak dapat menjalankan utilitas MySQL. Periksa konfigurasi binary.');
        }

        if (! $inputFile && isset($pipes[0]) && is_resource($pipes[0])) {
            fclose($pipes[0]);
        }

        $stderr = isset($pipes[2]) && is_resource($pipes[2]) ? stream_get_contents($pipes[2]) : '';

        foreach ($pipes as $pipe) {
            if (is_resource($pipe)) {
                fclose($pipe);
            }
        }

        $exitCode = proc_close($process);
        if ($exitCode !== 0) {
            $message = trim((string) $stderr) ?: 'Utilitas MySQL berhenti dengan kode ' . $exitCode;
            throw new RuntimeException($message);
        }
    }

    private function temporaryClientConfig(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'tpp_mysql_');
        if ($path === false) {
            throw new RuntimeException('Tidak dapat membuat file kredensial MySQL sementara.');
        }

        $escape = fn ($value) => str_replace(['\\', '"'], ['\\\\', '\\"'], (string) $value);
        $contents = "[client]\n"
            . 'user="' . $escape(config('database.connections.mysql.username')) . "\"\n"
            . 'password="' . $escape(config('database.connections.mysql.password')) . "\"\n";

        file_put_contents($path, $contents, LOCK_EX);
        @chmod($path, 0600);

        return $path;
    }
}
