<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tpps', function (Blueprint $table) {
            if (!Schema::hasColumn('tpps', 'bpjs_kesehatan_pemberi_kerja')) {
                $table->decimal('bpjs_kesehatan_pemberi_kerja', 15, 2)->default(0)->after('iuran_wajib');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tpps', function (Blueprint $table) {
            if (Schema::hasColumn('tpps', 'bpjs_kesehatan_pemberi_kerja')) {
                $table->dropColumn('bpjs_kesehatan_pemberi_kerja');
            }
        });
    }
};
