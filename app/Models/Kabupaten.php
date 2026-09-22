<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kabupaten extends Model
{
    protected $table = 'kabupaten';
    protected $primaryKey = 'kode_kab';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'kode_kab',
        'kode_prop',
        'nama_kab',
        'kec',
        'kel',
        'kkn'
    ];

    public function propinsi(): BelongsTo
    {
        return $this->belongsTo(Propinsi::class, 'kode_prop', 'kode_prop');
    }

    public function kecamatan(): HasMany
    {
        return $this->hasMany(Kecamatan::class, 'kode_kab', 'kode_kab');
    }
}
