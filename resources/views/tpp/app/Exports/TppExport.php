<?php

namespace App\Exports;

use App\Models\Tpp;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TppExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        protected ?int $bulan = null,
        protected ?int $tahun = null
    ) {}

    public function collection()
    {
        $q = Tpp::with('pegawai');

        if ($this->bulan) $q->where('bulan', $this->bulan);
        if ($this->tahun) $q->where('tahun', $this->tahun);

        return $q->orderBy('tahun', 'desc')
                 ->orderBy('bulan', 'desc')
                 ->get();
    }

    public function headings(): array
    {
        return [
            'Nama', 'NIP', 'Golongan', 'Jabatan',
            'Bulan', 'Tahun',
            'Produktivitas', 'Kehadiran', 'Perilaku',
            'Iuran Wajib', 'TPP Kotor', 'Pajak', 'Zakat', 'Total Diterima'
        ];
    }

    public function map($tpp): array
    {
        return [
            $tpp->pegawai->nama ?? '',
            $tpp->pegawai->nip ?? '',
            $tpp->pegawai->golongan ?? '',
            $tpp->pegawai->jabatan ?? '',
            $tpp->bulan,
            $tpp->tahun,
            (float)$tpp->produktivitas,
            (float)$tpp->kehadiran,
            (float)$tpp->perilaku,
            (float)$tpp->iuran_wajib,
            (float)$tpp->tpp_kotor,
            (float)$tpp->pajak,
            (float)$tpp->zakat,
            (float)$tpp->total_diterima,
        ];
    }
}