<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Hyperlink;
use PhpOffice\PhpSpreadsheet\Style\Color;

class TppWhatsappExport implements FromCollection, WithHeadings, WithEvents, ShouldAutoSize
{
    public function __construct(protected Collection $rows)
    {
    }

    public function collection()
    {
        return $this->rows->map(function ($tpp) {
            return [
                'nama' => $tpp->referensi_nama,
                'nip' => $tpp->referensi_nip,
                'no_hp' => $tpp->referensi_no_hp,
                'periode' => $tpp->periode_label ?? '',
                'produktivitas' => $this->formatPercent($tpp->produktivitas),
                'kehadiran' => $this->formatPercent($tpp->kehadiran),
                'perilaku' => $this->formatPercent($tpp->perilaku),
                'tpp_kotor' => $this->formatRupiah($tpp->tpp_kotor),
                'beban_kerja' => $this->formatRupiah($tpp->beban_jml ?? 0),
                'prestasi_kerja' => $this->formatRupiah($tpp->pres_jml ?? 0),
                'kondisi_kerja' => $this->formatRupiah($tpp->kond_jml ?? 0),
                'kelangkaan_profesi' => $this->formatRupiah($tpp->lang_jml ?? 0),
                'total_diterima' => $this->formatRupiah($tpp->total_diterima),
                'bpjs_1' => $this->formatRupiah($tpp->iuran_wajib),
                'pajak_label' => $tpp->wa_tax_label ?? 'Pajak',
                'pajak' => $this->formatRupiah($tpp->pajak),
                'zakat' => $this->formatRupiah($tpp->zakat),
                'isi_pesan_wa' => $tpp->wa_message ?? '',
                'status' => $tpp->wa_link ? 'Siap Kirim' : 'No. HP belum tersedia',
                'link' => $tpp->wa_link ? 'Kirim WA' : '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nama Pegawai',
            'NIP',
            'No. HP',
            'Periode',
            'Produktivitas',
            'Kehadiran',
            'Perilaku',
            'TPP Kotor',
            'Beban Kerja',
            'Prestasi Kerja',
            'Kondisi Kerja',
            'Kelangkaan Profesi',
            'TPP Diterima',
            'Potongan BPJS 1%',
            'Caption Pajak',
            'Nominal Pajak',
            'Zakat 2,5%',
            'Isi Pesan WA',
            'Status WA',
            'Link WhatsApp',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $this->rows->count() + 1;

                for ($row = 2; $row <= $lastRow; $row++) {
                    $item = $this->rows[$row - 2] ?? null;
                    if (!$item || empty($item->wa_link)) {
                        continue;
                    }

                    $cell = 'T' . $row;
                    $sheet->setCellValue($cell, 'Kirim WA');
                    $sheet->getCell($cell)->setHyperlink(new Hyperlink($item->wa_link, 'Kirim WA'));
                    $sheet->getStyle($cell)->getFont()
                        ->setUnderline(true)
                        ->getColor()->setARGB(Color::COLOR_BLUE);
                }

                $sheet->freezePane('A2');
                $sheet->setAutoFilter('A1:T1');
            },
        ];
    }

    private function formatRupiah($angka): string
    {
        return 'Rp. ' . number_format((float) $angka, 0, ',', '.');
    }

    private function formatPercent($angka): string
    {
        $formatted = rtrim(rtrim(number_format((float) $angka, 2, '.', ''), '0'), '.');
        return $formatted . '%';
    }
}
