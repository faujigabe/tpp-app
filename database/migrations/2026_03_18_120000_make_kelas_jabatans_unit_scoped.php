<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function indexExists(string $table, string $indexName): bool
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }

    public function up(): void
    {
        if (!Schema::hasColumn('kelas_jabatans', 'unit_kerja_id')) {
            Schema::table('kelas_jabatans', function (Blueprint $table) {
                $table->foreignId('unit_kerja_id')->nullable()->after('id')->constrained('unit_kerjas')->nullOnDelete();
            });
        }

        // Lepas unique global lama dulu agar nomor_kelas boleh diduplikasi per unit.
        if ($this->indexExists('kelas_jabatans', 'kelas_jabatans_nomor_kelas_unique')) {
            Schema::table('kelas_jabatans', function (Blueprint $table) {
                $table->dropUnique('kelas_jabatans_nomor_kelas_unique');
            });
        }

        $unitIds = DB::table('unit_kerjas')->orderBy('id')->pluck('id');
        $oldKelasRows = DB::table('kelas_jabatans')->orderBy('id')->get();

        if ($unitIds->isNotEmpty() && $oldKelasRows->isNotEmpty()) {
            $defaultUnitId = DB::table('unit_kerjas')
                ->where('nama_unit', 'like', '%Biro Administrasi Pembangunan%')
                ->value('id') ?: $unitIds->first();

            $oldByUnitAndNomor = [];

            // Pastikan data lama tanpa unit diarahkan ke unit default.
            foreach ($oldKelasRows->whereNull('unit_kerja_id') as $row) {
                DB::table('kelas_jabatans')->where('id', $row->id)->update(['unit_kerja_id' => $defaultUnitId]);
            }

            $currentRows = DB::table('kelas_jabatans')->orderBy('id')->get();

            // Petakan data yang sudah ada berdasarkan unit + nomor_kelas.
            foreach ($currentRows as $row) {
                if ($row->unit_kerja_id === null) {
                    continue;
                }

                $oldByUnitAndNomor[(int) $row->unit_kerja_id][(int) $row->nomor_kelas] = $row->id;
            }

            // Gandakan master kelas jabatan ke semua unit yang belum punya nomor_kelas tersebut.
            foreach ($unitIds as $unitId) {
                foreach ($currentRows as $row) {
                    $nomorKelas = (int) $row->nomor_kelas;

                    $existingId = $oldByUnitAndNomor[(int) $unitId][$nomorKelas] ?? null;
                    if ($existingId) {
                        continue;
                    }

                    $newId = DB::table('kelas_jabatans')->insertGetId([
                        'unit_kerja_id' => $unitId,
                        'nomor_kelas' => $row->nomor_kelas,
                        'nama_kelas' => $row->nama_kelas,
                        'beban_kerja' => $row->beban_kerja,
                        'prestasi_kerja' => $row->prestasi_kerja,
                        'kondisi_kerja' => $row->kondisi_kerja,
                        'kelangkaan_profesi' => $row->kelangkaan_profesi,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $oldByUnitAndNomor[(int) $unitId][$nomorKelas] = $newId;
                }
            }

            // Sinkronkan relasi pegawai ke kelas jabatan milik unitnya.
            $pegawais = DB::table('pegawais')
                ->select('id', 'unit_kerja_id', 'kelas_jabatan_id')
                ->whereNotNull('kelas_jabatan_id')
                ->whereNotNull('unit_kerja_id')
                ->get();

            foreach ($pegawais as $pegawai) {
                $oldNomorKelas = DB::table('kelas_jabatans')
                    ->where('id', $pegawai->kelas_jabatan_id)
                    ->value('nomor_kelas');

                if ($oldNomorKelas === null) {
                    continue;
                }

                $newKelasId = $oldByUnitAndNomor[(int) $pegawai->unit_kerja_id][(int) $oldNomorKelas] ?? null;
                if ($newKelasId) {
                    DB::table('pegawais')
                        ->where('id', $pegawai->id)
                        ->update(['kelas_jabatan_id' => $newKelasId]);
                }
            }
        }

        if (!$this->indexExists('kelas_jabatans', 'kelas_jabatans_unit_kerja_id_nomor_kelas_unique')) {
            Schema::table('kelas_jabatans', function (Blueprint $table) {
                $table->unique(['unit_kerja_id', 'nomor_kelas'], 'kelas_jabatans_unit_kerja_id_nomor_kelas_unique');
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists('kelas_jabatans', 'kelas_jabatans_unit_kerja_id_nomor_kelas_unique')) {
            Schema::table('kelas_jabatans', function (Blueprint $table) {
                $table->dropUnique('kelas_jabatans_unit_kerja_id_nomor_kelas_unique');
            });
        }

        if (!$this->indexExists('kelas_jabatans', 'kelas_jabatans_nomor_kelas_unique')) {
            Schema::table('kelas_jabatans', function (Blueprint $table) {
                $table->unique('nomor_kelas');
            });
        }

        if (Schema::hasColumn('kelas_jabatans', 'unit_kerja_id')) {
            Schema::table('kelas_jabatans', function (Blueprint $table) {
                $table->dropConstrainedForeignId('unit_kerja_id');
            });
        }
    }
};
