<?php

namespace App\Imports;

use App\Models\KelasJabatan;
use App\Rules\SafeSpreadsheetText;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class KelasJabatanImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsEmptyRows
{
    use SkipsFailures;

    public int $createdCount = 0;
    public int $updatedCount = 0;

    public function __construct(private int $unitKerjaId)
    {
    }

    public function model(array $row)
    {
        $nomorKelas = isset($row['nomor_kelas']) ? (int) $row['nomor_kelas'] : null;

        $kelas = KelasJabatan::firstOrNew(
            [
                'unit_kerja_id' => $this->unitKerjaId,
                'nomor_kelas' => $nomorKelas,
                'nama_kelas' => trim((string) Arr::get($row, 'nama_kelas', '')),
            ]
        );
        $isNew = !$kelas->exists;
        $kelas->fill([
            'beban_kerja' => (float) Arr::get($row, 'beban_kerja', 0),
            'prestasi_kerja' => (float) Arr::get($row, 'prestasi_kerja', 0),
            'kondisi_kerja' => (float) Arr::get($row, 'kondisi_kerja', 0),
            'kelangkaan_profesi' => Arr::get($row, 'kelangkaan_profesi') === null || Arr::get($row, 'kelangkaan_profesi') === ''
                ? null
                : (float) Arr::get($row, 'kelangkaan_profesi'),
        ])->save();

        $isNew ? $this->createdCount++ : $this->updatedCount++;

        return null;
    }

    public function rules(): array
    {
        return [
            'nomor_kelas' => ['required', 'integer', 'min:1', 'max:16'],
            'nama_kelas' => ['required', 'string', 'max:255', new SafeSpreadsheetText()],
            'beban_kerja' => ['required', 'numeric', 'min:0'],
            'prestasi_kerja' => ['required', 'numeric', 'min:0'],
            'kondisi_kerja' => ['required', 'numeric', 'min:0'],
            'kelangkaan_profesi' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nomor_kelas.required' => 'Nomor Kelas wajib diisi (1-16).',
            'nomor_kelas.integer' => 'Nomor Kelas harus angka (1-16).',
            'nomor_kelas.min' => 'Nomor Kelas minimal 1.',
            'nomor_kelas.max' => 'Nomor Kelas maksimal 16.',
            'nama_kelas.required' => 'Nama Kelas wajib diisi.',
            'beban_kerja.required' => 'Beban Kerja wajib diisi.',
            'prestasi_kerja.required' => 'Prestasi Kerja wajib diisi.',
            'kondisi_kerja.required' => 'Kondisi Kerja wajib diisi.',
        ];
    }
}
