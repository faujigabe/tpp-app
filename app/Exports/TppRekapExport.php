<?php

namespace App\Exports;

use App\Models\Tpp;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Support\TppRekapBuilder;

class TppRekapExport extends SafeExcelValueBinder implements FromCollection, WithMapping, ShouldAutoSize, WithEvents, WithCustomValueBinder
{
    protected Collection $rows;
    protected string $bulanLabel;
    protected int $tahun;
    protected int $headingRow = 5;
    protected int $rowNumber = 0;
    protected ?User $user = null;
    protected ?int $selectedUnitKerjaId = null;

    public function __construct(?int $bulan = null, ?int $tahun = null, ?User $user = null, ?int $selectedUnitKerjaId = null)
    {
        $bulanNama = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
        $this->user = $user;
        $this->tahun = $tahun ?: (int) now()->year;
        $this->selectedUnitKerjaId = $selectedUnitKerjaId;
        $this->bulanLabel = $bulanNama[(int) $bulan] ?? (string) $bulan;

        $targetUnitKerjaId = $this->user
            ? ($this->user->isSuperAdmin() ? $this->selectedUnitKerjaId : (int) $this->user->unit_kerja_id)
            : $this->selectedUnitKerjaId;

        $query = Tpp::with(['pegawai.kelasJabatan', 'pegawai.unitKerja', 'unitKerja'])
            ->forUnit($targetUnitKerjaId);
        if ($bulan) {
            $query->where('bulan', $bulan);
        }
        if ($tahun) {
            $query->where('tahun', $tahun);
        }

        $this->rows = $query->orderBy('pegawai_id')->get();
    }

    public function collection()
    {
        $collections = collect();
        $collections->push((object) ['_meta_title' => 'RINCIAN PERHITUNGAN TAMBAHAN PENGHASILAN PEGAWAI (TPP)']);
        $subtitle = $this->user?->isSuperAdmin()
            ? ($this->selectedUnitKerjaId ? optional($this->rows->first())->unitKerja?->nama_unit ?? 'UNIT KERJA TERPILIH' : 'SEMUA UNIT KERJA')
            : ($this->user?->unitKerja?->nama_unit ?: 'UNIT KERJA');
        $collections->push((object) ['_meta_subtitle' => strtoupper($subtitle)]);
        $collections->push((object) ['_meta_period' => 'Bulan ' . $this->bulanLabel . ' Tahun ' . $this->tahun]);
        $collections->push((object) ['_meta_spacer' => '']);
        $collections->push((object) ['_headings' => true]);
        foreach ($this->rows as $row) {
            $collections->push($row);
        }

        $totals = TppRekapBuilder::totals($this->rows);

        $collections->push((object) array_merge(['_total' => true], $totals));

        return $collections;
    }

    public function headingsRow(): array
    {
        return [
            'No', 'Nama', 'NIP', 'Rekening', 'Gol', 'Jabatan', 'Kelas',
            'Produktivitas (%)', 'Kehadiran (%)', 'Perilaku (%)',
            'Beban PK', 'Beban DK', 'Beban Per', 'Beban Jumlah',
            'Prestasi PK', 'Prestasi DK', 'Prestasi Per', 'Prestasi Jumlah',
            'Kondisi PK', 'Kondisi DK', 'Kondisi Per', 'Kondisi Jumlah',
            'Kelangkaan PK', 'Kelangkaan DK', 'Kelangkaan Per', 'Kelangkaan Jumlah',
            'Jumlah Besaran', 'TPP Kotor', 'BPJS Kesehatan 1%', 'BPJS Kesehatan 4%', 'TPP Setelah BPJS',
            'Pajak', 'TPP Setelah Potong Pajak', 'Zakat', 'TPP Diterima',
        ];
    }

    public function map($row): array
    {
        if (isset($row->_meta_title)) {
            return [$row->_meta_title];
        }
        if (isset($row->_meta_subtitle)) {
            return [$row->_meta_subtitle];
        }
        if (isset($row->_meta_period)) {
            return [$row->_meta_period];
        }
        if (isset($row->_meta_spacer)) {
            return [''];
        }
        if (isset($row->_headings)) {
            return $this->headingsRow();
        }
        if (isset($row->_total)) {
            return [
                'TOTAL', '', '', '', '', '', '', '', '', '',
                $row->beban_pk, $row->beban_dk, $row->beban_pr, $row->beban_jml,
                $row->pres_pk, $row->pres_dk, $row->pres_pr, $row->pres_jml,
                $row->kond_pk, $row->kond_dk, $row->kond_pr, $row->kond_jml,
                $row->lang_pk, $row->lang_dk, $row->lang_pr, $row->lang_jml,
                $row->jumlah_besaran, $row->tpp_kotor, $row->bpjs1, $row->bpjs4, $row->setelah_bpjs,
                $row->pajak, $row->setelah_pajak, $row->zakat, $row->diterima,
            ];
        }

        $calc = TppRekapBuilder::rowFromTpp($row);

        $this->rowNumber++;

        return [
            $this->rowNumber,
            $row->referensi_nama,
            (string) $row->referensi_nip,
            (string) $row->referensi_nomor_rekening,
            $row->referensi_golongan,
            $row->referensi_jabatan,
            $row->referensi_nomor_kelas,
            (float) $row->produktivitas,
            (float) $row->kehadiran,
            (float) $row->perilaku,
            $calc['beban_pk'], $calc['beban_dk'], $calc['beban_pr'], $calc['beban_jml'],
            $calc['pres_pk'], $calc['pres_dk'], $calc['pres_pr'], $calc['pres_jml'],
            $calc['kond_pk'], $calc['kond_dk'], $calc['kond_pr'], $calc['kond_jml'],
            $calc['lang_pk'], $calc['lang_dk'], $calc['lang_pr'], $calc['lang_jml'],
            $calc['jumlah_besaran'], $calc['tpp_kotor'], $calc['bpjs1'], $calc['bpjs4'], $calc['setelah_bpjs'],
            $calc['pajak'], $calc['setelah_pajak'], $calc['zakat'], $calc['diterima'],
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        if (in_array($cell->getColumn(), ['C', 'D'], true)) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $headingCount = count($this->headingsRow());
                $lastColumn = Coordinate::stringFromColumnIndex($headingCount);
                $lastRow = $sheet->getHighestRow();
                $dataStartRow = $this->headingRow + 1;
                $totalRow = $lastRow;

                foreach ([1, 2, 3] as $row) {
                    $sheet->mergeCells("A{$row}:{$lastColumn}{$row}");
                }

                $sheet->getStyle("A1:{$lastColumn}3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A1:{$lastColumn}3")->getFont()->setBold(true);
                $sheet->getStyle('A1')->getFont()->setSize(14);
                $sheet->getStyle('A2')->getFont()->setSize(12);
                $sheet->getStyle('A3')->getFont()->setSize(11);

                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(18);
                $sheet->freezePane('A6');

                $sheet->getStyle("A{$this->headingRow}:{$lastColumn}{$this->headingRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E9ECEF'],
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);
                $sheet->getRowDimension($this->headingRow)->setRowHeight(36);

                if ($lastRow >= $dataStartRow) {
                    $sheet->getStyle("A{$dataStartRow}:{$lastColumn}{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    $sheet->getStyle("A{$dataStartRow}:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("C{$dataStartRow}:D{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle("E{$dataStartRow}:J{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                $moneyColumns = ['K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE'];
                foreach ($moneyColumns as $column) {
                    $sheet->getStyle("{$column}{$dataStartRow}:{$column}{$lastRow}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0.00');
                }

                foreach (['H','I','J'] as $percentColumn) {
                    $sheet->getStyle("{$percentColumn}{$dataStartRow}:{$percentColumn}{$lastRow}")
                        ->getNumberFormat()
                        ->setFormatCode('0.00');
                }

                $sheet->getStyle("A{$totalRow}:{$lastColumn}{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFF3CD'],
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);

                foreach (range(1, $headingCount) as $index) {
                    $column = Coordinate::stringFromColumnIndex($index);
                    $currentWidth = $sheet->getColumnDimension($column)->getWidth();
                    if ($currentWidth < 12) {
                        $sheet->getColumnDimension($column)->setWidth(12);
                    }
                }
                $sheet->getColumnDimension('B')->setWidth(28);
                $sheet->getColumnDimension('C')->setWidth(24);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getStyle("B{$dataStartRow}:B{$lastRow}")->getAlignment()->setWrapText(true);
            },
        ];
    }

}
