<?php

namespace App\Imports;

use App\Models\KelasJabatan;
use App\Models\Pegawai;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class PegawaiImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsEmptyRows
{
    use SkipsFailures;

    public int $createdCount = 0;
    public int $updatedCount = 0;

    public function __construct(
        private ?int $defaultUnitKerjaId = null,
        private ?int $overrideUnitKerjaId = null,
    ) {}

    public function model(array $row)
    {
        $targetUnitKerjaId = $this->targetUnitKerjaId();
        $nip = trim((string) ($row['nip'] ?? ''));
        $pegawai = Pegawai::firstOrNew(['nip' => $nip]);
        $isNew = !$pegawai->exists;
        $unitKerjaId = $targetUnitKerjaId ?: $pegawai->unit_kerja_id;

        $kelas = $this->resolveKelasJabatan($row, $unitKerjaId, $pegawai);

        $pegawai->fill([
            'nama' => trim((string) ($row['nama'] ?? '')),
            'nik' => $this->nullableString($row['nik'] ?? null),
            'no_npwp' => $this->nullableString($row['no_npwp'] ?? null),
            'tanggal_lahir' => $this->parseDate($row['tanggal_lahir'] ?? null),
            'nomor_rekening' => $this->nullableString($row['nomor_rekening'] ?? null),
            'no_hp' => $this->nullableString($row['no_hp'] ?? null),
            'golongan' => strtoupper(trim((string) ($row['golongan'] ?? ''))),
            'agama' => trim((string) ($row['agama'] ?? '')),
            'jabatan' => trim((string) ($row['jabatan'] ?? '')),
            'nama_jabatan' => $this->nullableString($row['nama_jabatan'] ?? null),
            'tipe_jabatan' => $this->nullableInt($row['tipe_jabatan'] ?? null),
            'eselon' => $this->nullableString($row['eselon'] ?? null),
            'status_asn' => $this->nullableInt($row['status_asn'] ?? null),
            'masa_kerja_golongan' => $this->nullableInt($row['masa_kerja_golongan'] ?? null),
            'alamat' => $this->nullableString($row['alamat'] ?? null),
            'kode_bank' => $this->nullableString($row['kode_bank'] ?? null),
            'nama_bank' => $this->nullableString($row['nama_bank'] ?? null),
            'kelas_jabatan_id' => $kelas?->id,
            'unit_kerja_id' => $unitKerjaId,
        ]);

        if ($isNew) {
            $this->createdCount++;
        } else {
            $this->updatedCount++;
        }

        return $pegawai;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required'],
            'nip' => ['required', 'distinct'],
            'nik' => ['nullable', 'distinct'],
            'tanggal_lahir' => ['nullable'],
            'nomor_rekening' => ['nullable'],
            'no_hp' => ['required'],
            'golongan' => ['required', Rule::in(['II/A','II/B','II/C','II/D','III/A','III/B','III/C','III/D','IV/A','IV/B','IV/C','IV/D','IV/E','2','3','4'])],
            'agama' => ['required'],
            'jabatan' => ['required'],
            'kelas_jabatan' => ['required', function ($attribute, $value, $fail) {
                $unitKerjaId = $this->targetUnitKerjaId();
                $query = KelasJabatan::query()->where('unit_kerja_id', $unitKerjaId);

                if (is_numeric($value)) {
                    if (!(clone $query)->where('nomor_kelas', (int) $value)->exists()) {
                        $fail('Kelas Jabatan tidak ditemukan pada unit kerja tujuan. Pastikan master Kelas Jabatan unit ini sudah dibuat terlebih dahulu.');
                    }
                    return;
                }

                if (!(clone $query)->where('nama_kelas', trim((string) $value))->exists()) {
                    $fail('Kelas Jabatan tidak ditemukan pada unit kerja tujuan. Pastikan master Kelas Jabatan unit ini sudah dibuat terlebih dahulu.');
                }
            }],
            'nama_kelas_jabatan' => ['nullable', 'string'],
            'nama_jabatan' => ['nullable', 'string'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'golongan.in' => 'Golongan harus salah satu dari II/A s.d IV/E.',
            'kelas_jabatan.required' => 'Kelas Jabatan wajib diisi (1-16 atau nama kelas).',
            'nama_kelas_jabatan.string' => 'Nama_Kelas_Jabatan harus berupa teks.',
            'nama_jabatan.string' => 'Nama_Jabatan harus berupa teks.',
        ];
    }

    private function resolveKelasJabatan(array $row, ?int $unitKerjaId, Pegawai $existingPegawai = null): ?KelasJabatan
    {
        $kelasValue = $row['kelas_jabatan'] ?? null;
        if ($kelasValue === null || $kelasValue === '' || !$unitKerjaId) {
            return null;
        }

        $query = KelasJabatan::query()->where('unit_kerja_id', $unitKerjaId);
        $namaKelasJabatan = $this->normalizeName($row['nama_kelas_jabatan'] ?? null);
        $namaJabatan = $this->normalizeName($row['nama_jabatan'] ?? null);
        $jabatan = $this->normalizeName($row['jabatan'] ?? null);

        if (is_numeric($kelasValue)) {
            $matches = (clone $query)->where('nomor_kelas', (int) $kelasValue)->orderBy('nama_kelas')->get();

            if ($matches->count() <= 1) {
                return $matches->first();
            }

            foreach ([$namaKelasJabatan, $namaJabatan, $jabatan] as $candidateName) {
                if ($candidateName === '') {
                    continue;
                }

                $exact = $matches->first(fn ($item) => $this->normalizeName($item->nama_kelas) === $candidateName);
                if ($exact) {
                    return $exact;
                }

                $contains = $matches->first(function ($item) use ($candidateName) {
                    $itemName = $this->normalizeName($item->nama_kelas);
                    return str_contains($itemName, $candidateName) || str_contains($candidateName, $itemName);
                });
                if ($contains) {
                    return $contains;
                }
            }

            if ($existingPegawai && $existingPegawai->kelasJabatan && (int) $existingPegawai->kelasJabatan->unit_kerja_id === (int) $unitKerjaId && (int) $existingPegawai->kelasJabatan->nomor_kelas === (int) $kelasValue) {
                return $existingPegawai->kelasJabatan;
            }

            return $matches->first();
        }

        $normalized = $this->normalizeName($kelasValue);
        if ($normalized === '') {
            return null;
        }

        return (clone $query)
            ->get()
            ->first(function ($item) use ($normalized) {
                return $this->normalizeName($item->nama_kelas) === $normalized;
            });
    }

    private function normalizeName($value): string
    {
        $text = trim((string) ($value ?? ''));
        if ($text === '') {
            return '';
        }

        $text = Str::of($text)
            ->replaceMatches('/\s+/', ' ')
            ->replace('/', ' / ')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->upper()
            ->value();

        return $text;
    }

    private function targetUnitKerjaId(): ?int
    {
        return $this->overrideUnitKerjaId ?: $this->defaultUnitKerjaId;
    }

    private function nullableString($value): ?string
    {
        if ($value === null) return null;
        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
    }

    private function nullableInt($value): ?int
    {
        if ($value === null || $value === '') return null;
        return (int) $value;
    }

    private function parseDate($value): ?string
    {
        if ($value === null || $value === '') return null;
        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }
        $timestamp = strtotime((string) $value);
        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }
}
