<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Tpp extends Model
{
    use Auditable;

        protected $fillable = [
        'pegawai_id',
        'unit_kerja_id',
        'bulan',
        'tahun',
        'produktivitas',
        'kehadiran',
        'perilaku',
        'iuran_wajib',
        'bpjs_kesehatan_pemberi_kerja',
        'tpp_tempat_bertugas',
        'tunjangan_pph',
        'iuran_jkk',
        'iuran_jkm',
        'iuran_tapera',
        'iuran_pensiun',
        'tunjangan_jht',
        'bulog',
        'potongan_iwp',
        'tambahan_tpp',
        'potongan_tpp',
        'tpp_kotor',
        'hitung_pajak',
        'pajak',
        'zakat',
        'total_diterima',
        'pegawai_snapshot',
    ];


    protected $casts = [
        'hitung_pajak' => 'boolean',
        'pegawai_snapshot' => 'array',
    ];


    public function snapshot(string $key, $default = null)
    {
        return Arr::get($this->pegawai_snapshot ?? [], $key, $default);
    }

    public function scopeForUnit($query, ?int $unitKerjaId)
    {
        if (!$unitKerjaId) {
            return $query;
        }

        return $query->where(function ($inner) use ($unitKerjaId) {
            $inner->where($this->qualifyColumn('unit_kerja_id'), $unitKerjaId)
                ->orWhere(function ($fallback) use ($unitKerjaId) {
                    $fallback->whereNull($this->qualifyColumn('unit_kerja_id'))
                        ->whereHas('pegawai', fn ($pegawaiQuery) => $pegawaiQuery->where('unit_kerja_id', $unitKerjaId));
                });
        });
    }


    private function snapshotPreferred(string $key, $liveValue = null, $default = null)
    {
        $snapshotValue = $this->snapshot($key, null);

        return $snapshotValue !== null && $snapshotValue !== '' ? $snapshotValue : ($liveValue ?? $default);
    }


    public function getReferensiNamaAttribute(): string
    {
        return (string) $this->snapshotPreferred('nama', $this->pegawai?->nama, '');
    }

    public function getReferensiNipAttribute(): string
    {
        return (string) $this->snapshotPreferred('nip', $this->pegawai?->nip, '');
    }

    public function getReferensiNikAttribute(): string
    {
        return (string) $this->snapshotPreferred('nik', $this->pegawai?->nik, '');
    }

    public function getReferensiNoHpAttribute(): string
    {
        return (string) $this->snapshotPreferred('no_hp', $this->pegawai?->no_hp, '');
    }

    public function getReferensiNomorRekeningAttribute(): string
    {
        return (string) $this->snapshotPreferred('nomor_rekening', $this->pegawai?->nomor_rekening, '');
    }

    public function getReferensiGolonganAttribute(): string
    {
        return (string) $this->snapshotPreferred('golongan', $this->pegawai?->golongan, '');
    }

    public function getReferensiJabatanAttribute(): string
    {
        return (string) $this->snapshotPreferred('jabatan', $this->pegawai?->jabatan, '');
    }

    public function getReferensiNamaJabatanAttribute(): string
    {
        return (string) $this->snapshotPreferred('nama_jabatan', $this->pegawai?->nama_jabatan, '');
    }

    public function getReferensiAgamaAttribute(): string
    {
        return (string) $this->snapshotPreferred('agama', $this->pegawai?->agama, '');
    }

    public function getReferensiTanggalLahirAttribute(): ?string
    {
        return $this->snapshotPreferred('tanggal_lahir', $this->pegawai?->tanggal_lahir);
    }

    public function getReferensiTipeJabatanAttribute(): string
    {
        return (string) $this->snapshotPreferred('tipe_jabatan', $this->pegawai?->tipe_jabatan, '');
    }

    public function getReferensiEselonAttribute(): string
    {
        return (string) $this->snapshotPreferred('eselon', $this->pegawai?->eselon, '');
    }

    public function getReferensiStatusAsnAttribute(): string
    {
        return (string) $this->snapshotPreferred('status_asn', $this->pegawai?->status_asn, '');
    }

    public function getReferensiMasaKerjaGolonganAttribute(): string
    {
        return (string) $this->snapshotPreferred('masa_kerja_golongan', $this->pegawai?->masa_kerja_golongan, '');
    }

    public function getReferensiAlamatAttribute(): string
    {
        return (string) $this->snapshotPreferred('alamat', $this->pegawai?->alamat, '');
    }

    public function getReferensiKodeBankAttribute(): string
    {
        return (string) $this->snapshotPreferred('kode_bank', $this->pegawai?->kode_bank, '');
    }

    public function getReferensiNamaBankAttribute(): string
    {
        return (string) $this->snapshotPreferred('nama_bank', $this->pegawai?->nama_bank, '');
    }

    public function getReferensiNomorKelasAttribute(): string
    {
        return (string) $this->snapshotPreferred('kelas_jabatan.nomor_kelas', $this->pegawai?->kelasJabatan?->nomor_kelas, '');
    }

    public function getReferensiBebanKerjaAttribute(): float
    {
        return (float) $this->snapshotPreferred('kelas_jabatan.beban_kerja', $this->pegawai?->kelasJabatan?->beban_kerja, 0);
    }

    public function getReferensiPrestasiKerjaAttribute(): float
    {
        return (float) $this->snapshotPreferred('kelas_jabatan.prestasi_kerja', $this->pegawai?->kelasJabatan?->prestasi_kerja, 0);
    }

    public function getReferensiKondisiKerjaAttribute(): float
    {
        return (float) $this->snapshotPreferred('kelas_jabatan.kondisi_kerja', $this->pegawai?->kelasJabatan?->kondisi_kerja, 0);
    }

    public function getReferensiKelangkaanProfesiAttribute(): float
    {
        return (float) $this->snapshotPreferred('kelas_jabatan.kelangkaan_profesi', $this->pegawai?->kelasJabatan?->kelangkaan_profesi, 0);
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class);
    }
}
