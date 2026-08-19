<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->string('nik')->nullable()->after('nip');
            $table->date('tanggal_lahir')->nullable()->after('nik');
            $table->string('nama_jabatan')->nullable()->after('jabatan');
            $table->integer('tipe_jabatan')->nullable()->after('nama_jabatan');
            $table->string('eselon')->nullable()->after('tipe_jabatan');
            $table->integer('status_asn')->nullable()->after('eselon');
            $table->integer('masa_kerja_golongan')->nullable()->after('status_asn');
            $table->text('alamat')->nullable()->after('masa_kerja_golongan');
            $table->string('kode_bank')->nullable()->after('alamat');
            $table->string('nama_bank')->nullable()->after('kode_bank');
        });
    }

    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropColumn([
                'nik',
                'tanggal_lahir',
                'nama_jabatan',
                'tipe_jabatan',
                'eselon',
                'status_asn',
                'masa_kerja_golongan',
                'alamat',
                'kode_bank',
                'nama_bank',
            ]);
        });
    }
};
