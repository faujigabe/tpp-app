<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Konversi format lama (2/3/4) -> format baru (II/A, III/A, IV/A)
        DB::table('pegawais')->where('golongan', '2')->update(['golongan' => 'II/A']);
        DB::table('pegawais')->where('golongan', '3')->update(['golongan' => 'III/A']);
        DB::table('pegawais')->where('golongan', '4')->update(['golongan' => 'IV/A']);
    }

    public function down(): void
    {
        // Tidak dibalik otomatis.
    }
};
