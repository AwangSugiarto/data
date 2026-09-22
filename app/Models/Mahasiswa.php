<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    protected $table = 'mahasiswas';
    protected $guarded = ['id'];

    public function calonMahasiswa() { return $this->belongsTo(CalonMahasiswa::class, 'calon_mahasiswa_id'); }
    public function tahunMasuk()     { return $this->belongsTo(DimTahunAkademik::class, 'tahun_masuk_id'); }
    public function kelompokUkt()    { return $this->belongsTo(DimKelompokUkt::class, 'kelompok_ukt_id'); }
}
