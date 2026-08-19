<?php

namespace App\Support;

use App\Models\Pegawai;

class PegawaiSnapshotFactory
{
    public static function fromPegawai(?Pegawai $pegawai): array
    {
        if (! $pegawai) {
            return [];
        }

        $pegawai->loadMissing(['kelasJabatan', 'unitKerja']);

        return [
            'nama' => $pegawai->nama,
            'nip' => $pegawai->nip,
            'nik' => $pegawai->nik,
            'no_npwp' => $pegawai->no_npwp,
            'tanggal_lahir' => $pegawai->tanggal_lahir,
            'nomor_rekening' => $pegawai->nomor_rekening,
            'no_hp' => $pegawai->no_hp,
            'golongan' => $pegawai->golongan,
            'jabatan' => $pegawai->jabatan,
            'nama_jabatan' => $pegawai->nama_jabatan,
            'tipe_jabatan' => $pegawai->tipe_jabatan,
            'eselon' => $pegawai->eselon,
            'status_asn' => $pegawai->status_asn,
            'masa_kerja_golongan' => $pegawai->masa_kerja_golongan,
            'alamat' => $pegawai->alamat,
            'kode_bank' => $pegawai->kode_bank,
            'nama_bank' => $pegawai->nama_bank,
            'agama' => $pegawai->agama,
            'unit_kerja' => [
                'nama_unit' => $pegawai->unitKerja?->nama_unit,
            ],
            'kelas_jabatan' => [
                'nomor_kelas' => $pegawai->kelasJabatan?->nomor_kelas,
                'beban_kerja' => (float) ($pegawai->kelasJabatan?->beban_kerja ?? 0),
                'prestasi_kerja' => (float) ($pegawai->kelasJabatan?->prestasi_kerja ?? 0),
                'kondisi_kerja' => (float) ($pegawai->kelasJabatan?->kondisi_kerja ?? 0),
                'kelangkaan_profesi' => (float) ($pegawai->kelasJabatan?->kelangkaan_profesi ?? 0),
            ],
        ];
    }
}
