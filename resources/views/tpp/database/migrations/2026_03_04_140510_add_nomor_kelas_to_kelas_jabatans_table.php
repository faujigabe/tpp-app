<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelas_jabatans', function (Blueprint $table) {
            // 1 - 16 (atau lebih kalau nanti berkembang)
            $table->unsignedSmallInteger('nomor_kelas')->nullable()->after('id');
            $table->unique('nomor_kelas');
        });
    }

    public function down(): void
    {
        Schema::table('kelas_jabatans', function (Blueprint $table) {
            $table->dropUnique(['nomor_kelas']);
            $table->dropColumn('nomor_kelas');
        });
    }
};
