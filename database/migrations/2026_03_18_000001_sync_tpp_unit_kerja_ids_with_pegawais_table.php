<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tpps')
            ->join('pegawais', 'pegawais.id', '=', 'tpps.pegawai_id')
            ->whereNotNull('pegawais.unit_kerja_id')
            ->update([
                'tpps.unit_kerja_id' => DB::raw('pegawais.unit_kerja_id'),
            ]);
    }

    public function down(): void
    {
        // Sinkronisasi data historis tidak dibalik agar perubahan unit kerja tetap konsisten.
    }
};
