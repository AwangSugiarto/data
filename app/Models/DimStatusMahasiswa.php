<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DimStatusMahasiswa extends Model
{
    protected $table    = 'dim_status_mahasiswas';
    protected $fillable = ['nama_status', 'kode_status', 'warna', 'deskripsi', 'is_aktif_akademik', 'urutan'];
    protected $casts    = ['is_aktif_akademik' => 'boolean'];

    public static function ordered()
    {
        return static::orderBy('urutan')->orderBy('nama_status');
    }
}
