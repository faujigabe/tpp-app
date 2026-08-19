<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tpps', function (Blueprint $table) {
            if (! Schema::hasColumn('tpps', 'pegawai_snapshot')) {
                $table->json('pegawai_snapshot')->nullable()->after('pegawai_id');
            }
        });

        DB::table('tpps')
            ->leftJoin('pegawais', 'pegawais.id', '=', 'tpps.pegawai_id')
            ->leftJoin('kelas_jabatans', 'kelas_jabatans.id', '=', 'pegawais.kelas_jabatan_id')
            ->leftJoin('unit_kerjas', 'unit_kerjas.id', '=', 'tpps.unit_kerja_id')
            ->select([
                'tpps.id', 'tpps.pegawai_snapshot',
                'pegawais.nama', 'pegawais.nip', 'pegawais.nik', 'pegawais.no_npwp', 'pegawais.tanggal_lahir',
                'pegawais.nomor_rekening', 'pegawais.no_hp', 'pegawais.golongan', 'pegawais.jabatan', 'pegawais.nama_jabatan',
                'pegawais.tipe_jabatan', 'pegawais.eselon', 'pegawais.status_asn', 'pegawais.masa_kerja_golongan',
                'pegawais.alamat', 'pegawais.kode_bank', 'pegawais.nama_bank', 'pegawais.agama',
                'kelas_jabatans.nomor_kelas', 'kelas_jabatans.beban_kerja', 'kelas_jabatans.prestasi_kerja',
                'kelas_jabatans.kondisi_kerja', 'kelas_jabatans.kelangkaan_profesi',
                'unit_kerjas.nama_unit as unit_kerja_nama',
            ])
            ->orderBy('tpps.id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    if (! empty($row->pegawai_snapshot)) {
                        continue;
                    }

                    DB::table('tpps')->where('id', $row->id)->update([
                        'pegawai_snapshot' => json_encode([
                            'nama' => $row->nama,
                            'nip' => $row->nip,
                            'nik' => $row->nik,
                            'no_npwp' => $row->no_npwp,
                            'tanggal_lahir' => $row->tanggal_lahir,
                            'nomor_rekening' => $row->nomor_rekening,
                            'no_hp' => $row->no_hp,
                            'golongan' => $row->golongan,
                            'jabatan' => $row->jabatan,
                            'nama_jabatan' => $row->nama_jabatan,
                            'tipe_jabatan' => $row->tipe_jabatan,
                            'eselon' => $row->eselon,
                            'status_asn' => $row->status_asn,
                            'masa_kerja_golongan' => $row->masa_kerja_golongan,
                            'alamat' => $row->alamat,
                            'kode_bank' => $row->kode_bank,
                            'nama_bank' => $row->nama_bank,
                            'agama' => $row->agama,
                            'unit_kerja' => [
                                'nama_unit' => $row->unit_kerja_nama,
                            ],
                            'kelas_jabatan' => [
                                'nomor_kelas' => $row->nomor_kelas,
                                'beban_kerja' => (float) ($row->beban_kerja ?? 0),
                                'prestasi_kerja' => (float) ($row->prestasi_kerja ?? 0),
                                'kondisi_kerja' => (float) ($row->kondisi_kerja ?? 0),
                                'kelangkaan_profesi' => (float) ($row->kelangkaan_profesi ?? 0),
                            ],
                        ], JSON_UNESCAPED_UNICODE),
                    ]);
                }
            }, 'tpps.id', 'id');

        DB::statement('ALTER TABLE tpps DROP FOREIGN KEY tpps_pegawai_id_foreign');
        DB::statement('ALTER TABLE tpps MODIFY pegawai_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE tpps ADD CONSTRAINT tpps_pegawai_id_foreign FOREIGN KEY (pegawai_id) REFERENCES pegawais(id) ON DELETE SET NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE tpps DROP FOREIGN KEY tpps_pegawai_id_foreign');
        DB::statement('ALTER TABLE tpps MODIFY pegawai_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE tpps ADD CONSTRAINT tpps_pegawai_id_foreign FOREIGN KEY (pegawai_id) REFERENCES pegawais(id) ON DELETE CASCADE');

        Schema::table('tpps', function (Blueprint $table) {
            if (Schema::hasColumn('tpps', 'pegawai_snapshot')) {
                $table->dropColumn('pegawai_snapshot');
            }
        });
    }
};
