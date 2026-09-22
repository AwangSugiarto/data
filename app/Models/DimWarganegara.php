<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DimWarganegara extends Model
{
    protected $table = 'dim_warganegara';

    protected $fillable = [
        'nama_negara',
        'kode_negara',
    ];
}
