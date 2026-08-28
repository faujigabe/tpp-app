<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitKerja extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'nama_unit',
        'kode_unit',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function pegawais()
    {
        return $this->hasMany(Pegawai::class);
    }

    public function tpps()
    {
        return $this->hasMany(Tpp::class);
    }
}
