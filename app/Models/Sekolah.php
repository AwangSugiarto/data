<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sekolah extends Model
{
    protected $table = 'sekolah';
    protected $primaryKey = 'npsn';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'npsn',
        'kode_kec',
        'nama_sekolah',
        'alamat_sekolah',
        'kelompok',
        'status',
        'kkn',
        'akreditasi',
        'kode_pos',
        'telepon',
        'fax',
        'email',
        'web',
        'jumlah_siswa',
        'kurikulum',
        'nama_kepsek',
        'hp_kepsek',
        'email_kepsek'
    ];

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kode_kec', 'kode_kec');
    }
}
