<?php

namespace Tests\Unit;

use App\Models\KelasJabatan;
use App\Models\Pegawai;
use App\Models\Tpp;
use App\Models\UnitKerja;
use App\Support\PegawaiSnapshotFactory;
use PHPUnit\Framework\TestCase;

class PegawaiSnapshotFactoryTest extends TestCase
{
    public function test_snapshot_historis_dipertahankan_saat_data_pegawai_berubah(): void
    {
        $snapshotLama = [
            'nama' => 'Pegawai Lama',
            'jabatan' => 'Jabatan Lama',
            'golongan' => 'III/A',
            'kelas_jabatan' => ['nomor_kelas' => '8', 'beban_kerja' => 1000000],
        ];
        $pegawaiSekarang = new Pegawai([
            'nama' => 'Pegawai Baru',
            'jabatan' => 'Jabatan Baru',
            'golongan' => 'IV/A',
        ]);
        $tpp = (new Tpp(['pegawai_snapshot' => $snapshotLama]))
            ->setRelation('pegawai', $pegawaiSekarang);

        $hasil = PegawaiSnapshotFactory::fromTpp($tpp);

        $this->assertSame($snapshotLama, $hasil);
        $this->assertSame('Jabatan Lama', $hasil['jabatan']);
        $this->assertSame('III/A', $hasil['golongan']);
    }

    public function test_data_pegawai_dipakai_sebagai_fallback_jika_snapshot_belum_ada(): void
    {
        $kelas = new KelasJabatan([
            'nomor_kelas' => '9',
            'beban_kerja' => 1500000,
            'prestasi_kerja' => 500000,
            'kondisi_kerja' => 0,
            'kelangkaan_profesi' => 0,
        ]);
        $unitKerja = new UnitKerja(['nama_unit' => 'Biro Administrasi Pembangunan']);
        $pegawai = (new Pegawai([
            'nama' => 'Pegawai Aktif',
            'jabatan' => 'Analis',
            'golongan' => 'III/B',
            'agama' => 'Kristen',
        ]))
            ->setRelation('kelasJabatan', $kelas)
            ->setRelation('unitKerja', $unitKerja);
        $tpp = (new Tpp(['pegawai_snapshot' => null]))
            ->setRelation('pegawai', $pegawai);

        $hasil = PegawaiSnapshotFactory::fromTpp($tpp);

        $this->assertSame('Pegawai Aktif', $hasil['nama']);
        $this->assertSame('Analis', $hasil['jabatan']);
        $this->assertSame('III/B', $hasil['golongan']);
        $this->assertSame('9', $hasil['kelas_jabatan']['nomor_kelas']);
        $this->assertSame(1500000.0, $hasil['kelas_jabatan']['beban_kerja']);
        $this->assertSame('Biro Administrasi Pembangunan', $hasil['unit_kerja']['nama_unit']);
    }
}
