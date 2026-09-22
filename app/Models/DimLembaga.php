<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DimLembaga extends Model
{
    protected $table = 'dim_lembaga';

    protected $fillable = [
        'nama_lembaga',
        'singkatan',
        'keterangan',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
    ];

    /** Jalur-jalur seleksi yang diselenggarakan oleh lembaga ini */
    public function jalurSeleksi(): HasMany
    {
        return $this->hasMany(DimJalurSeleksi::class, 'lembaga_id');
    }
}
