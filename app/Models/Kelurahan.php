<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kelurahan extends Model
{
    protected $table = 'kelurahan';
    protected $primaryKey = 'kode_kel';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'kode_kel',
        'kode_kec',
        'kode_kab',
        'kode_prop',
        'nama_kel',
        'kkn'
    ];

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kode_kec', 'kode_kec');
    }

    public function kabupaten(): BelongsTo
    {
        return $this->belongsTo(Kabupaten::class, 'kode_kab', 'kode_kab');
    }

    public function propinsi(): BelongsTo
    {
        return $this->belongsTo(Propinsi::class, 'kode_prop', 'kode_prop');
    }
}
