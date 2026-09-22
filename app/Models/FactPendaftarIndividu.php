<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactPendaftarIndividu extends Model
{
    protected $table = 'fact_pendaftar_individu';
    protected $fillable = [
        'tahun_akademik_id', 'program_studi_id', 'jalur_seleksi_id',
        'wilayah_id', 'kelompok_ukt_id', 'nomor_tes', 'nama', 'status',
        'jenis_kelamin', 'asal_sekolah', 'jenis_sekolah', 'nisn', 'npsn',
        'warganegara_id'
    ];

    public function tahunAkademik(): BelongsTo { return $this->belongsTo(DimTahunAkademik::class, 'tahun_akademik_id'); }
    public function programStudi(): BelongsTo  { return $this->belongsTo(DimProgramStudi::class, 'program_studi_id'); }
    public function jalurSeleksi(): BelongsTo  { return $this->belongsTo(DimJalurSeleksi::class, 'jalur_seleksi_id'); }
    public function wilayah(): BelongsTo       { return $this->belongsTo(DimWilayah::class, 'wilayah_id'); }
    public function kelompokUkt(): BelongsTo   { return $this->belongsTo(DimKelompokUkt::class, 'kelompok_ukt_id'); }
    public function warganegara(): BelongsTo   { return $this->belongsTo(DimWarganegara::class, 'warganegara_id'); }

    public function mahasiswa()
    {
        return $this->hasOne(MasterMahasiswa::class, 'fact_pendaftar_individu_id');
    }
}
