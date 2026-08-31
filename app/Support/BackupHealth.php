<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class BackupHealth
{
    public function summary(): array
    {
        $daily = $this->inspect(
            (string) config('backup.local_path'),
            (int) config('backup.daily_health_max_age_hours', 26)
        );
        $weekly = $this->inspect(
            (string) config('backup.weekly_path'),
            (int) config('backup.weekly_health_max_age_hours', 192)
        );

        return [
            'healthy' => $daily['healthy'] && $weekly['healthy'],
            'daily' => $daily,
            'weekly' => $weekly,
        ];
    }

    private function inspect(string $configuredPath, int $maxAgeHours): array
    {
        $path = $this->absolutePath($configuredPath);
        $files = $path && is_dir($path)
            ? glob($path . DIRECTORY_SEPARATOR . 'tpp_*.sql.gz') ?: []
            : [];

        usort($files, fn (string $a, string $b) => filemtime($b) <=> filemtime($a));
        $latest = $files[0] ?? null;
        $modifiedAt = $latest ? Carbon::createFromTimestamp(filemtime($latest), 'Asia/Jakarta') : null;
        $ageHours = $modifiedAt ? $modifiedAt->diffInHours(now('Asia/Jakarta')) : null;
        $checksumExists = $latest ? is_file($latest . '.sha256') : false;
        $checksumValid = $latest && $checksumExists ? $this->checksumIsValid($latest) : false;
        $healthy = $latest !== null && $checksumValid && $ageHours <= $maxAgeHours;

        return [
            'healthy' => $healthy,
            'path' => $path,
            'file' => $latest ? basename($latest) : null,
            'modified_at' => $modifiedAt,
            'age_hours' => $ageHours,
            'size_bytes' => $latest ? filesize($latest) : null,
            'checksum_exists' => $checksumExists,
            'checksum_valid' => $checksumValid,
            'max_age_hours' => $maxAgeHours,
        ];
    }

    private function checksumIsValid(string $file): bool
    {
        $expected = strtolower((string) strtok(trim((string) @file_get_contents($file . '.sha256')), " \t"));
        $actual = strtolower((string) @hash_file('sha256', $file));

        return $expected !== '' && $actual !== '' && hash_equals($expected, $actual);
    }

    private function absolutePath(string $path): ?string
    {
        $path = trim($path);
        if ($path === '') {
            return null;
        }

        if (preg_match('/^(?:[A-Za-z]:[\\\\\/]|\\\\\\\\|\/)/', $path)) {
            return rtrim($path, '/\\');
        }

        return base_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path));
    }
}
