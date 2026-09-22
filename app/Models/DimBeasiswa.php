<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DimBeasiswa extends Model
{
    protected $table = 'dim_beasiswa';
    protected $fillable = ['kode_beasiswa', 'nama_beasiswa', 'keterangan'];

    public function penerima()
    {
        return $this->hasMany(FactPenerimaBeasiswa::class, 'dim_beasiswa_id');
    }
}
