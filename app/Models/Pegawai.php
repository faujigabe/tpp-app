<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Pegawai extends Model
{
    use HasFactory;

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_MUTASI = 'mutasi';
    public const STATUS_PENSIUN = 'pensiun';
    public const STATUS_KELUAR = 'keluar';
    public const STATUS_MENINGGAL = 'meninggal';

    protected $fillable = [
        'nama',
        'nip',
        'nik',
        'no_npwp',
        'tanggal_lahir',
        'nomor_rekening',
        'no_hp',
        'golongan',
        'jabatan',
        'nama_jabatan',
        'tipe_jabatan',
        'eselon',
        'status_asn',
        'masa_kerja_golongan',
        'alamat',
        'kode_bank',
        'nama_bank',
        'agama',
        'foto_profil',
        'kelas_jabatan_id',
        'unit_kerja_id',
        'status_pegawai',
        'nonaktif_sejak',
        'catatan_status',
    ];



    protected $casts = [
        'nonaktif_sejak' => 'date',
    ];

    protected static function booted(): void
    {
        static::updated(function (Pegawai $pegawai) {
            if ($pegawai->wasChanged('unit_kerja_id')) {
                $pegawai->userAccounts()->update([
                    'unit_kerja_id' => $pegawai->unit_kerja_id,
                ]);
            }
        });
    }

    public function kelasJabatan()
    {
        return $this->belongsTo(KelasJabatan::class, 'kelas_jabatan_id');
    }

    public function tpps()
    {
        return $this->hasMany(Tpp::class);
    }

    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class);
    }

    public static function availableStatuses(): array
    {
        return [
            self::STATUS_AKTIF => 'Aktif',
            self::STATUS_MUTASI => 'Mutasi',
            self::STATUS_PENSIUN => 'Pensiun',
            self::STATUS_KELUAR => 'Keluar',
            self::STATUS_MENINGGAL => 'Meninggal',
        ];
    }

    public static function inactiveStatuses(): array
    {
        return [
            'nonaktif',
            self::STATUS_MUTASI,
            self::STATUS_PENSIUN,
            self::STATUS_KELUAR,
            self::STATUS_MENINGGAL,
        ];
    }

    public function scopeAktif($query)
    {
        return $query->where('status_pegawai', self::STATUS_AKTIF);
    }

    public function scopeNonaktif($query)
    {
        return $query->whereIn('status_pegawai', self::inactiveStatuses());
    }

    public function scopeActiveForPeriod($query, int $bulan, int $tahun)
    {
        $periodEnd = Carbon::create($tahun, $bulan, 1)->endOfMonth()->toDateString();

        return $query->where(function ($inner) use ($periodEnd) {
            $inner->where('status_pegawai', self::STATUS_AKTIF)
                ->orWhere(function ($inactiveQuery) use ($periodEnd) {
                    $inactiveQuery->whereIn('status_pegawai', self::inactiveStatuses())
                        ->whereNotNull('nonaktif_sejak')
                        ->whereDate('nonaktif_sejak', '>', $periodEnd);
                });
        });
    }

    public function isAktif(): bool
    {
        return ($this->status_pegawai ?? self::STATUS_AKTIF) === self::STATUS_AKTIF;
    }

    public function isActiveForPeriod(int $bulan, int $tahun): bool
    {
        if ($this->isAktif()) {
            return true;
        }

        if (! $this->nonaktif_sejak) {
            return false;
        }

        return $this->nonaktif_sejak->gt(Carbon::create($tahun, $bulan, 1)->endOfMonth());
    }

    public function getStatusLabelAttribute(): string
    {
        $status = $this->status_pegawai ?? self::STATUS_AKTIF;

        if ($status === 'nonaktif') {
            return 'Nonaktif';
        }

        return self::availableStatuses()[$status] ?? ucfirst((string) $status);
    }

    public function userAccount()
    {
        return $this->hasOne(User::class);
    }

    public function userAccounts()
    {
        return $this->hasMany(User::class);
    }
}
