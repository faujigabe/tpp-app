<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('pegawais', function (Blueprint $table) {
        if (!Schema::hasColumn('pegawais', 'no_hp')) {
            $table->string('no_hp', 20)->nullable()->after('nomor_rekening');
        }
    });
}

public function down(): void
{
    Schema::table('pegawais', function (Blueprint $table) {
        if (Schema::hasColumn('pegawais', 'no_hp')) {
            $table->dropColumn('no_hp');
        }
    });
}
};
