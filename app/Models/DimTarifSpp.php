<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DimTarifSpp extends Model
{
    protected $table = 'dim_tarif_spp';

    protected $fillable = [
        'tahun_akademik_id',
        'program_studi_id',
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
}
