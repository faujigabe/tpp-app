<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_kerjas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_unit');
            $table->string('kode_unit')->nullable()->unique();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('unit_kerja_id')->nullable()->after('pegawai_id')->constrained('unit_kerjas')->nullOnDelete();
        });

        Schema::table('pegawais', function (Blueprint $table) {
            $table->foreignId('unit_kerja_id')->nullable()->after('kelas_jabatan_id')->constrained('unit_kerjas')->nullOnDelete();
        });

        Schema::table('tpps', function (Blueprint $table) {
            $table->foreignId('unit_kerja_id')->nullable()->after('pegawai_id')->constrained('unit_kerjas')->nullOnDelete();
        });

        $unitId = DB::table('unit_kerjas')->insertGetId([
            'nama_unit' => 'Biro Administrasi Pembangunan Setdaprovsu',
            'kode_unit' => 'BAP',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->update(['unit_kerja_id' => $unitId]);
        DB::table('pegawais')->update(['unit_kerja_id' => $unitId]);
        DB::table('tpps')->update(['unit_kerja_id' => $unitId]);

        DB::table('tpps')
            ->join('pegawais', 'pegawais.id', '=', 'tpps.pegawai_id')
            ->update(['tpps.unit_kerja_id' => DB::raw('pegawais.unit_kerja_id')]);

        DB::table('users')->where('role', 'admin')->update(['role' => 'super_admin']);
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'super_admin')->update(['role' => 'admin']);

        Schema::table('tpps', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_kerja_id');
        });

        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_kerja_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_kerja_id');
        });

        Schema::dropIfExists('unit_kerjas');
    }
};
