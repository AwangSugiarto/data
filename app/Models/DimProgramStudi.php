<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class DimProgramStudi extends Model
{
    protected $table = 'dim_program_studi';

    protected $fillable = [
        'fakultas_id', 'nama_prodi', 'kode_prodi', 'jenjang',
        'status_terkini', 'berlaku_dari', 'berlaku_sampai',
        'kode_dikti', 'nama_jenjang_pendidikan', 'gelar', 'akreditasi',
        'no_sk_dikti', 'tgl_sk_dikti', 'tgl_akhir_sk_dikti',
        'no_sk_ban', 'tgl_sk_ban', 'tgl_akhir_sk_ban',
        'tanggal_pendirian', 'telepon', 'email', 'pejabat', 'jabatan',
        'nama_sekprodi', 'nip_sekprodi', 'keterangan'
    ];

    protected $casts = [
        'status_terkini' => 'boolean',
        'berlaku_dari'   => 'date',
        'berlaku_sampai' => 'date',
        'tgl_sk_dikti'       => 'date',
        'tgl_akhir_sk_dikti' => 'date',
        'tgl_sk_ban'         => 'date',
        'tgl_akhir_sk_ban'   => 'date',
        'tanggal_pendirian'  => 'date',
    ];

    public function fakultas(): BelongsTo
    {
        return $this->belongsTo(DimFakultas::class, 'fakultas_id');
    }

    public function alias(): HasMany
    {
        return $this->hasMany(ProdiAlias::class, 'program_studi_id');
    }

    public function factKuota(): HasMany
    {
        return $this->hasMany(FactKuota::class, 'program_studi_id');
    }

    public function factPendaftaran(): HasMany
    {
        return $this->hasMany(FactPendaftaran::class, 'program_studi_id');
    }

    public function factKelulusan(): HasMany
    {
        return $this->hasMany(FactKelulusan::class, 'program_studi_id');
    }

    public function factRegistrasi(): HasMany
    {
        return $this->hasMany(FactRegistrasi::class, 'program_studi_id');
    }

    public function jalurSeleksi(): BelongsToMany
    {
        return $this->belongsToMany(DimJalurSeleksi::class, 'dim_jalur_prodi', 'program_studi_id', 'jalur_seleksi_id')->withTimestamps();
    }

    public function tarifUkt(): HasMany
    {
        return $this->hasMany(DimTarifUkt::class, 'program_studi_id');
    }

    public function tarifSpp(): HasMany
    {
        return $this->hasMany(DimTarifSpp::class, 'program_studi_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }
}
