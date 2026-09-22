<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPmb extends Model
{
    use HasFactory;

    protected $fillable = [
        'tahun_akademik_id',
        'jalur_seleksi_id',
        'fakultas_id',
        'program_studi_id',
        'daya_tampung',
        'pendaftar',
    ];

    public function tahunAkademik()
    {
        return $this->belongsTo(DimTahunAkademik::class, 'tahun_akademik_id');
    }

    public function jalurSeleksi()
    {
        return $this->belongsTo(DimJalurSeleksi::class, 'jalur_seleksi_id');
    }

    public function fakultas()
    {
        return $this->belongsTo(DimFakultas::class, 'fakultas_id');
    }

    public function programStudi()
    {
        return $this->belongsTo(DimProgramStudi::class, 'program_studi_id');
    }
}
