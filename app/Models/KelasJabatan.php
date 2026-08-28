<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KelasJabatan extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'unit_kerja_id',
        'nomor_kelas',
        'nama_kelas',
        'beban_kerja',
        'prestasi_kerja',
        'kondisi_kerja',
        'kelangkaan_profesi',
    ];

    public function pegawais(): HasMany
    {
        return $this->hasMany(Pegawai::class);
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class);
    }
}
