<?php

namespace App\Imports;

use App\Models\KelasJabatan;
use App\Models\Pegawai;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PegawaiImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsEmptyRows
{
    use SkipsFailures;

    public function model(array $row)
    {
        $kelasValue = $row['kelas_jabatan'] ?? null;
        $kelas = null;

        if ($kelasValue !== null && $kelasValue !== '') {
            if (is_numeric($kelasValue)) {
                $kelas = KelasJabatan::where('nomor_kelas', (int) $kelasValue)->first();
            } else {
                $kelas = KelasJabatan::where('nama_kelas', trim((string) $kelasValue))->first();
            }
        }

        return new Pegawai([
            'nama' => trim((string) ($row['nama'] ?? '')),
            'nip' => trim((string) ($row['nip'] ?? '')),
            'nomor_rekening' => isset($row['nomor_rekening']) ? trim((string) $row['nomor_rekening']) : null,
            'no_hp' => isset($row['no_hp']) ? trim((string) $row['no_hp']) : null,
            'golongan' => strtoupper(trim((string) ($row['golongan'] ?? ''))),
            'agama' => trim((string) ($row['agama'] ?? '')),
            'jabatan' => trim((string) ($row['jabatan'] ?? '')),
            'kelas_jabatan_id' => $kelas?->id,
        ]);
    }

    public function rules(): array
    {
        return [
            'nama' => ['required'],
            'nip' => ['required', 'distinct', Rule::unique('pegawais', 'nip')],
            'nomor_rekening' => ['nullable'],
            'no_hp' => ['nullable'],
            'golongan' => ['required', Rule::in([
                'II/A', 'II/B', 'II/C', 'II/D',
                'III/A', 'III/B', 'III/C', 'III/D',
                'IV/A', 'IV/B', 'IV/C', 'IV/D', 'IV/E',
                '2', '3', '4',
            ])],
            'agama' => ['required'],
            'jabatan' => ['required'],
            'kelas_jabatan' => ['required', function ($attribute, $value, $fail) {
                $exists = is_numeric($value)
                    ? KelasJabatan::where('nomor_kelas', (int) $value)->exists()
                    : KelasJabatan::where('nama_kelas', trim((string) $value))->exists();

                if (!$exists) {
                    $fail('Kelas Jabatan tidak ditemukan. Pastikan master Kelas Jabatan sudah ada.');
                }
            }],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nip.unique' => 'NIP sudah ada di database.',
            'golongan.in' => 'Golongan harus salah satu dari II/A s.d IV/E.',
            'kelas_jabatan.required' => 'Kelas Jabatan wajib diisi (1-16 atau nama kelas).',
        ];
    }
}
