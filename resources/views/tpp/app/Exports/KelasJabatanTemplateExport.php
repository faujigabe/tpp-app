<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KelasJabatanTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'Nomor Kelas',
            'Nama Kelas',
            'Beban Kerja',
            'Prestasi Kerja',
            'Kondisi Kerja',
            'Kelangkaan Profesi',
        ];
    }

    public function array(): array
    {
        // 1 baris contoh (boleh dihapus oleh user)
        return [[
            10,
            'PELAKSANA',
            1000000,
            0,
            0,
            0,
        ]];
    }
}
