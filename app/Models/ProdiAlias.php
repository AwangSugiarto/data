<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProdiAlias extends Model
{
    protected $table = 'prodi_alias';
    protected $fillable = ['program_studi_id', 'nama_alias'];

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(DimProgramStudi::class, 'program_studi_id');
    }
}
