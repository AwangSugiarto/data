<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DimWilayah extends Model
{
    protected $table = 'dim_wilayah';

    protected $fillable = [
        'kabupaten_kota',
        'kecamatan',
        'provinsi',
        'kode_kemendagri',
        'pulau_region',
    ];

    protected $appends = ['nama_wilayah'];

    /** Accessor: gabungan kabupaten + provinsi untuk kemudahan tampil */
    public function getNamaWilayahAttribute(): string
    {
        $parts = [];
        if ($this->provinsi) $parts[] = $this->provinsi;
        if ($this->kabupaten_kota) $parts[] = $this->kabupaten_kota;
        if ($this->kecamatan) $parts[] = $this->kecamatan;
        
        return implode(' - ', $parts);
    }

    public function pendaftarIndividu(): HasMany
    {
        return $this->hasMany(FactPendaftarIndividu::class, 'wilayah_id');
    }
}
