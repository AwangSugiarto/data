<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FakultasAlias extends Model
{
    protected $table = 'fakultas_alias';
    protected $fillable = ['fakultas_id', 'nama_alias'];

    public function fakultas(): BelongsTo
    {
        return $this->belongsTo(DimFakultas::class, 'fakultas_id');
    }
}

