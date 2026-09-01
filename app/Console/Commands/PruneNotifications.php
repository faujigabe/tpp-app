<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneNotifications extends Command
{
    protected $signature = 'notifications:prune {--days=365 : Hapus notifikasi terbaca yang lebih lama dari jumlah hari ini}';

    protected $description = 'Menghapus notifikasi terbaca yang sudah melewati masa retensi';

    public function handle(): int
    {
        $days = max((int) $this->option('days'), 30);
        $deleted = DB::table('notifications')
            ->whereNotNull('read_at')
            ->where('created_at', '<', now()->subDays($days))
            ->delete();

        $this->info("{$deleted} notifikasi terbaca telah dibersihkan.");

        return self::SUCCESS;
    }
}
