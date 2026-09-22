<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Propinsi extends Model
{
    protected $table = 'propinsi';
    protected $primaryKey = 'kode_prop';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'kode_prop',
        'nama_prop',
        'kab',
        'kec',
        'kel'
    ];

    public function kabupaten(): HasMany
    {
        return $this->hasMany(Kabupaten::class, 'kode_prop', 'kode_prop');
    }
}
