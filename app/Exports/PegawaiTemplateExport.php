<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PegawaiTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'Nama',
            'NIP',
            'NIK',
            'No_NPWP',
            'Tanggal_Lahir',
            'Nomor_Rekening',
            'No_HP',
            'Golongan',
            'Agama',
            'Jabatan',
            'Nama_Jabatan',
            'Tipe_Jabatan',
            'Eselon',
            'Status_ASN',
            'Masa_Kerja_Golongan',
            'Alamat',
            'Kode_Bank',
            'Nama_Bank',
            'Kelas_Jabatan',
            'Nama_Kelas_Jabatan',
        ];
    }

    public function array(): array
    {
        return [[
            'Contoh Nama',
            '199411012025041002',
            '1271020102030001',
            '09.123.456.7-890.000',
            '1994-11-01',
            '1234567890',
            '081234567890',
            'III/A',
            'Islam',
            'Penata Kelola Sistem',
            'Analis Sistem Informasi',
            2,
            'III.a',
            1,
            5,
            'Jl. Contoh Nomor 1',
            '002',
            'Bank Sumut',
            10,
            'Analis Sistem Informasi',
        ]];
    }
}
