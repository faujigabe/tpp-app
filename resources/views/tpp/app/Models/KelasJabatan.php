<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KelasJabatan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_kelas',
        'nama_kelas',
        'beban_kerja',
        'prestasi_kerja',
        'kondisi_kerja',
        'kelangkaan_profesi'
    ];

    public function pegawais()
    {
        return $this->hasMany(Pegawai::class);
    }
}