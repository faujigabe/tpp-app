<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function indexExists(string $table, string $indexName): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }

    public function up(): void
    {
        // Buat index unik baru lebih dulu agar foreign key unit_kerja_id tetap punya index penopang.
        if (!$this->indexExists('kelas_jabatans', 'kelas_jabatans_unit_kerja_id_nomor_kelas_nama_kelas_unique')) {
            Schema::table('kelas_jabatans', function (Blueprint $table) {
                $table->unique(
                    ['unit_kerja_id', 'nomor_kelas', 'nama_kelas'],
                    'kelas_jabatans_unit_kerja_id_nomor_kelas_nama_kelas_unique'
                );
            });
        }

        // Setelah ada index baru yang masih diawali unit_kerja_id, baru aman melepas unique lama.
        if ($this->indexExists('kelas_jabatans', 'kelas_jabatans_unit_kerja_id_nomor_kelas_unique')) {
            Schema::table('kelas_jabatans', function (Blueprint $table) {
                $table->dropUnique('kelas_jabatans_unit_kerja_id_nomor_kelas_unique');
            });
        }
    }

    public function down(): void
    {
        // Kembalikan unique lama lebih dulu agar FK unit_kerja_id tetap punya index penopang.
        if (!$this->indexExists('kelas_jabatans', 'kelas_jabatans_unit_kerja_id_nomor_kelas_unique')) {
            Schema::table('kelas_jabatans', function (Blueprint $table) {
                $table->unique(['unit_kerja_id', 'nomor_kelas'], 'kelas_jabatans_unit_kerja_id_nomor_kelas_unique');
            });
        }

        if ($this->indexExists('kelas_jabatans', 'kelas_jabatans_unit_kerja_id_nomor_kelas_nama_kelas_unique')) {
            Schema::table('kelas_jabatans', function (Blueprint $table) {
                $table->dropUnique('kelas_jabatans_unit_kerja_id_nomor_kelas_nama_kelas_unique');
            });
        }
    }
};
