<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PmbPeminatAgregat extends Model
{
    protected $table = 'pmb_peminat_agregat';
    protected $guarded = ['id'];

    public function tahunAkademik() { return $this->belongsTo(DimTahunAkademik::class, 'tahun_akademik_id'); }
    public function programStudi()  { return $this->belongsTo(DimProgramStudi::class, 'program_studi_id'); }
    public function jalurSeleksi()  { return $this->belongsTo(DimJalurSeleksi::class, 'jalur_seleksi_id'); }
}
