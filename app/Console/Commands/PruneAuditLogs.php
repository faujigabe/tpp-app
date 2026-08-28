<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

class PruneAuditLogs extends Command
{
    protected $signature = 'audit:prune';
    protected $description = 'Menghapus audit log yang melewati masa retensi';

    public function handle(): int
    {
        $years = max(1, (int) config('backup.audit_retention_years', 5));
        $deleted = AuditLog::query()->where('created_at', '<', now()->subYears($years))->delete();
        $this->info("Pembersihan audit selesai. {$deleted} catatan lebih lama dari {$years} tahun dihapus.");

        return self::SUCCESS;
    }
}
