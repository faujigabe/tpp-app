<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            if (! Schema::hasColumn('pegawais', 'status_pegawai')) {
                $table->string('status_pegawai')->default('aktif')->after('unit_kerja_id');
            }
            if (! Schema::hasColumn('pegawais', 'nonaktif_sejak')) {
                $table->date('nonaktif_sejak')->nullable()->after('status_pegawai');
            }
            if (! Schema::hasColumn('pegawais', 'catatan_status')) {
                $table->string('catatan_status')->nullable()->after('nonaktif_sejak');
            }
        });

        DB::table('pegawais')->whereNull('status_pegawai')->update(['status_pegawai' => 'aktif']);
    }

    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            foreach (['catatan_status', 'nonaktif_sejak', 'status_pegawai'] as $column) {
                if (Schema::hasColumn('pegawais', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
