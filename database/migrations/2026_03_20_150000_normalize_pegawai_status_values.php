<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('pegawais')
            ->where('status_pegawai', 'nonaktif')
            ->update(['status_pegawai' => 'mutasi']);
    }

    public function down(): void
    {
        DB::table('pegawais')
            ->where('status_pegawai', 'mutasi')
            ->where(function ($query) {
                $query->whereNotNull('nonaktif_sejak')
                    ->orWhere('catatan_status', 'like', '%nonaktif%')
                    ->orWhere('catatan_status', 'like', '%mutasi%');
            })
            ->update(['status_pegawai' => 'nonaktif']);
    }
};
