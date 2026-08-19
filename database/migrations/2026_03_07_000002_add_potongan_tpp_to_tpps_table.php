<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tpps', function (Blueprint $table) {
            if (!Schema::hasColumn('tpps', 'potongan_tpp')) {
                $table->decimal('potongan_tpp', 8, 2)->default(0)->after('tambahan_tpp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tpps', function (Blueprint $table) {
            if (Schema::hasColumn('tpps', 'potongan_tpp')) {
                $table->dropColumn('potongan_tpp');
            }
        });
    }
};
