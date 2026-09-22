<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactKelulusan extends Model
{
    protected $table = 'fact_kelulusan';
    protected $fillable = ['tahun_akademik_id', 'program_studi_id', 'jalur_seleksi_id', 'jumlah_lulus'];

    public function tahunAkademik(): BelongsTo { return $this->belongsTo(DimTahunAkademik::class, 'tahun_akademik_id'); }
    public function programStudi(): BelongsTo  { return $this->belongsTo(DimProgramStudi::class, 'program_studi_id'); }
    public function jalurSeleksi(): BelongsTo  { return $this->belongsTo(DimJalurSeleksi::class, 'jalur_seleksi_id'); }
}
