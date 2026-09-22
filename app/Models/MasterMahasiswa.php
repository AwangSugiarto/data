<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterMahasiswa extends Model
{
    protected $table = 'master_mahasiswas';

    protected $fillable = [
        'fact_pendaftar_individu_id',
        'nim',
        'nama',
        'program_studi_id',
        'tahun_masuk_id',
        'status_akademik',
        'tanggal_lulus',
        'tahun_akademik_lulus_id'
    ];

    public function pendaftar()
    {
        return $this->belongsTo(FactPendaftarIndividu::class, 'fact_pendaftar_individu_id');
    }

    public function programStudi()
    {
        return $this->belongsTo(DimProgramStudi::class, 'program_studi_id');
    }

    public function tahunMasuk()
    {
        return $this->belongsTo(DimTahunAkademik::class, 'tahun_masuk_id');
    }
}
