<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tpps', function (Blueprint $table) {
            $table->decimal('tpp_tempat_bertugas', 15, 2)->default(0)->after('bpjs_kesehatan_pemberi_kerja');
            $table->decimal('tunjangan_pph', 15, 2)->default(0)->after('tpp_tempat_bertugas');
            $table->decimal('iuran_jkk', 15, 2)->default(0)->after('tunjangan_pph');
            $table->decimal('iuran_jkm', 15, 2)->default(0)->after('iuran_jkk');
            $table->decimal('iuran_tapera', 15, 2)->default(0)->after('iuran_jkm');
            $table->decimal('iuran_pensiun', 15, 2)->default(0)->after('iuran_tapera');
            $table->decimal('tunjangan_jht', 15, 2)->default(0)->after('iuran_pensiun');
            $table->decimal('bulog', 15, 2)->default(0)->after('tunjangan_jht');
            $table->decimal('potongan_iwp', 15, 2)->default(0)->after('bulog');
        });
    }

    public function down(): void
    {
        Schema::table('tpps', function (Blueprint $table) {
            $table->dropColumn([
                'tpp_tempat_bertugas',
                'tunjangan_pph',
                'iuran_jkk',
                'iuran_jkm',
                'iuran_tapera',
                'iuran_pensiun',
                'tunjangan_jht',
                'bulog',
                'potongan_iwp',
            ]);
        });
    }
};
