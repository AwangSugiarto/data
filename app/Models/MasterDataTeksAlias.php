<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterDataTeksAlias extends Model
{
    protected $fillable = [
        'tipe',
        'nilai_mentah',
        'dipetakan_ke_teks',
    ];
}
