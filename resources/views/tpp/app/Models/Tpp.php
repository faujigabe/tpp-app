<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tpp extends Model
{
        protected $fillable = [
        'pegawai_id',
        'bulan',
        'tahun',
        'produktivitas',
        'kehadiran',
        'perilaku',
        'iuran_wajib',
        'tpp_kotor',
        'pajak',
        'zakat',
        'total_diterima'
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }
}