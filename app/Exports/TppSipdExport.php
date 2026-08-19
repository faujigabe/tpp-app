<?php

namespace App\Exports;

use App\Models\Tpp;
use App\Models\User;
use App\Models\Pegawai;
use App\Support\SipdRekapBuilder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class TppSipdExport extends StringValueBinder implements FromCollection, WithHeadings, WithMapping, WithEvents, ShouldAutoSize, WithCustomValueBinder
{
    use Exportable;

    private ?User $user;
    private ?int $selectedUnitKerjaId;

    public function __construct(private int $bulan, private int $tahun, ?User $user = null, ?int $selectedUnitKerjaId = null)
    {
        $this->user = $user;
        $this->selectedUnitKerjaId = $selectedUnitKerjaId;
    }

    public function collection(): Collection
    {
        $targetUnitKerjaId = $this->user
            ? ($this->user->isSuperAdmin() ? $this->selectedUnitKerjaId : (int) $this->user->unit_kerja_id)
            : $this->selectedUnitKerjaId;

        $rows = Tpp::with(['pegawai.kelasJabatan', 'pegawai.unitKerja'])
            ->when($targetUnitKerjaId, function ($query) use ($targetUnitKerjaId) {
                $query->where(function ($inner) use ($targetUnitKerjaId) {
                    $inner->whereHas('pegawai', fn ($pegawaiQuery) => $pegawaiQuery->where('unit_kerja_id', $targetUnitKerjaId))
                        ->orWhere(function ($fallbackQuery) use ($targetUnitKerjaId) {
                            $fallbackQuery->whereNull('pegawai_id')
                                ->where('unit_kerja_id', $targetUnitKerjaId);
                        });
                });
            })
            ->where('bulan', $this->bulan)
            ->where('tahun', $this->tahun)
            ->orderByRaw("COALESCE((SELECT nama FROM pegawais WHERE pegawais.id = tpps.pegawai_id), JSON_UNQUOTE(JSON_EXTRACT(pegawai_snapshot, '$.nama'))) asc")
            ->get()
            ->map(fn ($tpp, $index) => array_merge(['no' => $index + 1], SipdRekapBuilder::rowFromTpp($tpp)));

        $totals = SipdRekapBuilder::totals($rows->all());
        $rows->push(array_merge(['no' => 'TOTAL'], $totals));

        return $rows;
    }

    private function money($value): string
    {
        return number_format((float) ($value ?? 0), 2, ",", ".");
    }

    public function headings(): array
    {
        return [
            'No.', 'Nama Pegawai', 'NIP Pegawai', 'NIK Pegawai', 'Tanggal Lahir Pegawai', 'Tipe Jabatan', 'Nama Jabatan',
            'Eselon', 'Status ASN', 'Golongan', 'Masa Kerja Golongan', 'Alamat', 'Kode Bank', 'Nama Bank',
            'Nomor Rekening Pegawai', 'TPP Beban Kerja', 'TPP Tempat Bertugas', 'TPP Kondisi Kerja', 'TPP Kelangkaan Profesi',
            'TPP Prestasi Kerja', 'Tunjangan PPH', 'Iuran Pemberi Kerja', 'Iuran Jaminan Kecelakaan Kerja',
            'Iuran Jaminan Kematian', 'Iuran Simpanan Tapera', 'Iuran Pensiun', 'Tunjangan Jaminan Hari Tua',
            'BPJS Kesehatan 1% (Peserta)', 'Potongan PPh 21', 'Zakat', 'Bulog', 'Jumlah TPP', 'Jumlah Potongan', 'Jumlah Di Transfer'
        ];
    }

    public function map($row): array
    {
        return [
            $row['no'] ?? '',
            $row['nama_pegawai'] ?? '',
            (string) ($row['nip_pegawai'] ?? ''),
            (string) ($row['nik_pegawai'] ?? ''),
            $row['tanggal_lahir_pegawai'] ?? '',
            $row['tipe_jabatan'] ?? '',
            $row['nama_jabatan'] ?? '',
            $row['eselon'] ?? '',
            $row['status_asn'] ?? '',
            $row['golongan'] ?? '',
            $row['masa_kerja_golongan'] ?? '',
            $row['alamat'] ?? '',
            (string) ($row['kode_bank'] ?? ''),
            $row['nama_bank'] ?? '',
            (string) ($row['nomor_rekening_pegawai'] ?? ''),
            $this->money($row['tpp_beban_kerja'] ?? 0),
            $this->money($row['tpp_tempat_bertugas'] ?? 0),
            $this->money($row['tpp_kondisi_kerja'] ?? 0),
            $this->money($row['tpp_kelangkaan_profesi'] ?? 0),
            $this->money($row['tpp_prestasi_kerja'] ?? 0),
            $this->money($row['tunjangan_pph'] ?? 0),
            $this->money($row['iuran_pemberi_kerja'] ?? 0),
            $this->money($row['iuran_jaminan_kecelakaan_kerja'] ?? 0),
            $this->money($row['iuran_jaminan_kematian'] ?? 0),
            $this->money($row['iuran_simpanan_tapera'] ?? 0),
            $this->money($row['iuran_pensiun'] ?? 0),
            $this->money($row['tunjangan_jaminan_hari_tua'] ?? 0),
            $this->money($row['potongan_iwp'] ?? 0),
            $this->money($row['potongan_pph_21'] ?? 0),
            $this->money($row['zakat'] ?? 0),
            $this->money($row['bulog'] ?? 0),
            $this->money($row['jumlah_tpp'] ?? 0),
            $this->money($row['jumlah_potongan'] ?? 0),
            $this->money($row['jumlah_di_transfer'] ?? 0),
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        if (in_array($cell->getColumn(), ['C', 'D', 'M', 'O'], true) || (Coordinate::columnIndexFromString($cell->getColumn()) >= 16 && Coordinate::columnIndexFromString($cell->getColumn()) <= 34)) {
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
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                $sheet->freezePane('A2');
                $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
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
                $sheet->getRowDimension(1)->setRowHeight(38);
                $sheet->getStyle("A2:{$highestColumn}{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("A2:O{$highestRow}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                $sheet->getStyle("A2:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("P2:AH{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);


                $sheet->getStyle("A{$highestRow}:{$highestColumn}{$highestRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFF3CD'],
                    ],
                ]);

                $sheet->getColumnDimension('B')->setWidth(28);
                $sheet->getColumnDimension('C')->setWidth(22);
                $sheet->getColumnDimension('D')->setWidth(22);
                $sheet->getColumnDimension('E')->setWidth(16);
                $sheet->getColumnDimension('G')->setWidth(28);
                $sheet->getColumnDimension('L')->setWidth(30);
                $sheet->getColumnDimension('O')->setWidth(20);
            },
        ];
    }
}
