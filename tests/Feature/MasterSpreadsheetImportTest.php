<?php

namespace Tests\Feature;

use App\Imports\KelasJabatanImport;
use App\Imports\PegawaiImport;
use App\Models\KelasJabatan;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MasterSpreadsheetImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_tidak_dapat_memindahkan_pegawai_dari_unit_lain_melalui_import(): void
    {
        [$unitPertama, $kelasPertama] = $this->unitDanKelas('UNIT-A');
        [$unitKedua] = $this->unitDanKelas('UNIT-B');
        Pegawai::query()->create($this->pegawaiData($unitPertama->id, $kelasPertama->id));

        $import = new PegawaiImport($unitKedua->id);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('sudah terdaftar pada unit kerja lain');

        $import->model($this->pegawaiRow());
    }

    public function test_nik_yang_dipakai_pegawai_lain_ditolak(): void
    {
        [$unit, $kelas] = $this->unitDanKelas('UNIT-NIK');
        Pegawai::query()->create($this->pegawaiData($unit->id, $kelas->id));
        $row = $this->pegawaiRow();
        $row['nip'] = '199411012025041003';

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('sudah digunakan oleh pegawai lain');

        (new PegawaiImport($unit->id))->model($row);
    }

    public function test_nomor_kelas_ambigu_ditolak_dan_harus_memakai_nama_kelas(): void
    {
        [$unit] = $this->unitDanKelas('UNIT-AMBIGU', 5, 'Analis');
        KelasJabatan::query()->create($this->kelasData($unit->id, 5, 'Pelaksana'));
        $row = $this->pegawaiRow();
        $row['kelas_jabatan'] = 5;

        $validator = Validator::make($row, (new PegawaiImport($unit->id))->rules());

        $this->assertTrue($validator->fails());
        $this->assertStringContainsString('beberapa nama kelas', $validator->errors()->first('kelas_jabatan'));
    }

    public function test_import_kelas_yang_sama_memperbarui_baris_dan_menghitung_hasil(): void
    {
        [$unit, $kelas] = $this->unitDanKelas('UNIT-KELAS');
        $import = new KelasJabatanImport($unit->id);

        $import->model([
            'nomor_kelas' => $kelas->nomor_kelas,
            'nama_kelas' => $kelas->nama_kelas,
            'beban_kerja' => 2000000,
            'prestasi_kerja' => 500000,
            'kondisi_kerja' => 0,
            'kelangkaan_profesi' => 0,
        ]);

        $this->assertSame(0, $import->createdCount);
        $this->assertSame(1, $import->updatedCount);
        $this->assertSame(2000000.0, (float) $kelas->fresh()->beban_kerja);
        $this->assertDatabaseCount('kelas_jabatans', 1);
    }

    private function unitDanKelas(string $kode, int $nomor = 1, string $nama = 'Analis'): array
    {
        $unit = UnitKerja::query()->create([
            'kode_unit' => $kode,
            'nama_unit' => 'Unit ' . $kode,
        ]);
        $kelas = KelasJabatan::query()->create($this->kelasData($unit->id, $nomor, $nama));

        return [$unit, $kelas];
    }

    private function kelasData(int $unitKerjaId, int $nomor, string $nama): array
    {
        return [
            'unit_kerja_id' => $unitKerjaId,
            'nomor_kelas' => $nomor,
            'nama_kelas' => $nama,
            'beban_kerja' => 1000000,
            'prestasi_kerja' => 0,
            'kondisi_kerja' => 0,
            'kelangkaan_profesi' => 0,
        ];
    }

    private function pegawaiData(int $unitKerjaId, int $kelasId): array
    {
        return [
            'nama' => 'Fauji Gabe',
            'nip' => '199411012025041002',
            'nik' => '1271020102030001',
            'no_hp' => '081234567890',
            'golongan' => 'III/A',
            'agama' => 'Kristen',
            'jabatan' => 'Analis',
            'kelas_jabatan_id' => $kelasId,
            'unit_kerja_id' => $unitKerjaId,
        ];
    }

    private function pegawaiRow(): array
    {
        return [
            'nama' => 'Fauji Gabe',
            'nip' => '199411012025041002',
            'nik' => '1271020102030001',
            'no_hp' => '081234567890',
            'golongan' => 'III/A',
            'agama' => 'Kristen',
            'jabatan' => 'Analis',
            'kelas_jabatan' => 1,
        ];
    }
}
