<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('penilaians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained()->onDelete('cascade');
            $table->foreignId('periode_id')->constrained()->onDelete('cascade');

            $table->decimal('produktivitas', 5, 2);
            $table->decimal('kehadiran', 5, 2);
            $table->decimal('perilaku', 5, 2);

            $table->decimal('iuran_wajib', 15, 2);

            $table->decimal('tpp_bersih', 15, 2)->nullable();
            $table->decimal('pajak', 15, 2)->nullable();
            $table->decimal('zakat', 15, 2)->nullable();
            $table->decimal('total_final', 15, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaians');
    }
};
