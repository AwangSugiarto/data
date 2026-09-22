<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactPenerimaBeasiswa extends Model
{
    protected $table = 'fact_penerima_beasiswa';
    protected $fillable = ['tahun_akademik_id', 'dim_beasiswa_id', 'program_studi_id', 'nim', 'nama_mahasiswa'];

    public function tahunAkademik()
    {
        return $this->belongsTo(DimTahunAkademik::class, 'tahun_akademik_id');
    }

    public function beasiswa()
    {
        return $this->belongsTo(DimBeasiswa::class, 'dim_beasiswa_id');
    }

    public function programStudi()
    {
        return $this->belongsTo(DimProgramStudi::class, 'program_studi_id');
    }
}
