<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JalurAlias extends Model
{
    protected $table = 'jalur_alias';
    protected $fillable = ['jalur_seleksi_id', 'nama_alias'];

    public function jalurSeleksi(): BelongsTo
    {
        return $this->belongsTo(DimJalurSeleksi::class, 'jalur_seleksi_id');
    }
}
