<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DimTarifUkt extends Model
{
    protected $table = 'dim_tarif_ukt';

    protected $fillable = [
        'tahun_akademik_id',
        'program_studi_id',
        'kelompok_ukt_id',
        'nominal'
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(DimTahunAkademik::class, 'tahun_akademik_id');
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(DimProgramStudi::class, 'program_studi_id');
    }

    public function kelompokUkt(): BelongsTo
    {
        return $this->belongsTo(DimKelompokUkt::class, 'kelompok_ukt_id');
    }
}
