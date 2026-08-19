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
            'Nomor Rekening',
            'No HP',
            'Golongan',
            'Agama',
            'Jabatan',
            'Kelas Jabatan',
        ];
    }

    public function array(): array
    {
        return [[
            'Contoh Nama',
            '199411012025041002',
            '1234567890',
            '081234567890',
            'III/A',
            'Kristen',
            'Penata Kelola Sistem',
            10,
        ]];
    }
}
