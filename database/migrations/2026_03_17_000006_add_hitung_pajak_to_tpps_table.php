<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tpps', function (Blueprint $table) {
            $table->boolean('hitung_pajak')->default(true)->after('tpp_kotor');
        });
    }

    public function down(): void
    {
        Schema::table('tpps', function (Blueprint $table) {
            $table->dropColumn('hitung_pajak');
        });
    }
};
