<?php

namespace App\Exports;

use App\Models\Tpp;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TppExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        protected ?int $bulan = null,
        protected ?int $tahun = null,
        protected ?User $user = null,
        protected ?int $selectedUnitKerjaId = null
    ) {}

    public function collection()
    {
        $targetUnitKerjaId = $this->user
            ? ($this->user->isSuperAdmin() ? $this->selectedUnitKerjaId : (int) $this->user->unit_kerja_id)
            : $this->selectedUnitKerjaId;

        $q = Tpp::with(['pegawai', 'pegawai.unitKerja'])
            ->when($targetUnitKerjaId, function ($query) use ($targetUnitKerjaId) {
                $query->where(function ($inner) use ($targetUnitKerjaId) {
                    $inner->whereHas('pegawai', fn ($pegawaiQuery) => $pegawaiQuery->where('unit_kerja_id', $targetUnitKerjaId))
                        ->orWhere(function ($fallbackQuery) use ($targetUnitKerjaId) {
                            $fallbackQuery->whereNull('pegawai_id')
                                ->where('unit_kerja_id', $targetUnitKerjaId);
                        });
                });
            });

        if ($this->bulan) {
            $q->where('bulan', $this->bulan);
        }
        if ($this->tahun) {
            $q->where('tahun', $this->tahun);
        }

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
            'Tambahan TPP', 'Potongan TPP (%)', 'BPJS Kesehatan 1% (Peserta)', 'BPJS Kesehatan 4% (Pemberi Kerja)', 'TPP Kotor', 'Pajak', 'Zakat', 'Total Diterima',
        ];
    }

    public function map($tpp): array
    {
        return [
            $tpp->referensi_nama,
            $tpp->referensi_nip,
            $tpp->referensi_golongan,
            $tpp->referensi_jabatan,
            $tpp->bulan,
            $tpp->tahun,
            (float) $tpp->produktivitas,
            (float) $tpp->kehadiran,
            (float) $tpp->perilaku,
            (float) ($tpp->tambahan_tpp ?? 0),
            (float) ($tpp->potongan_tpp ?? 0),
            (float) $tpp->iuran_wajib,
            (float) ($tpp->bpjs_kesehatan_pemberi_kerja ?? 0),
            (float) $tpp->tpp_kotor,
            (float) $tpp->pajak,
            (float) $tpp->zakat,
            (float) $tpp->total_diterima,
        ];
    }
}
