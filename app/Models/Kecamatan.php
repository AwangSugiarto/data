<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kecamatan extends Model
{
    protected $table = 'kecamatan';
    protected $primaryKey = 'kode_kec';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'kode_kec',
        'kode_prop',
        'kode_kab',
        'nama_kec',
        'kel',
        'kkn'
    ];

    public function kabupaten(): BelongsTo
    {
        return $this->belongsTo(Kabupaten::class, 'kode_kab', 'kode_kab');
    }

    public function propinsi(): BelongsTo
    {
        return $this->belongsTo(Propinsi::class, 'kode_prop', 'kode_prop');
    }

    public function kelurahan(): HasMany
    {
        return $this->hasMany(Kelurahan::class, 'kode_kec', 'kode_kec');
    }
    
    public function sekolah(): HasMany
    {
        return $this->hasMany(Sekolah::class, 'kode_kec', 'kode_kec');
    }
}
