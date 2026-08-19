<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            if (! Schema::hasColumn('pegawais', 'no_npwp')) {
                $table->string('no_npwp', 50)->nullable()->after('nik');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            if (Schema::hasColumn('pegawais', 'no_npwp')) {
                $table->dropColumn('no_npwp');
            }
        });
    }
};
