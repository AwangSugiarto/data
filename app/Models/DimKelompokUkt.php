<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DimKelompokUkt extends Model
{
    protected $table = 'dim_kelompok_ukt';

    protected $fillable = ['tahun_akademik_id', 'kelompok', 'nominal_min', 'nominal_max'];

    protected $casts = [
        'nominal_min' => 'decimal:2',
        'nominal_max' => 'decimal:2',
    ];

    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(DimTahunAkademik::class, 'tahun_akademik_id');
    }

    public function pendaftarIndividu(): HasMany
    {
        return $this->hasMany(FactPendaftarIndividu::class, 'kelompok_ukt_id');
    }
}
