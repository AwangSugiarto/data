<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DimKelompokJalur extends Model
{
    use HasFactory;
    
    protected $table = 'dim_kelompok_jalurs';
    
    protected $fillable = [
        'nama_kelompok'
    ];
}
