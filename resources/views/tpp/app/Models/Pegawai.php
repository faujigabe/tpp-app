<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'nip',
        'nomor_rekening',
        'no_hp',
        'golongan',
        'jabatan',
        'agama',
        'kelas_jabatan_id'
    ];

    public function kelasJabatan()
    {
         return $this->belongsTo(KelasJabatan::class, 'kelas_jabatan_id');
     }

    public function tpps()
    {
        return $this->hasMany(Tpp::class);
    }
}