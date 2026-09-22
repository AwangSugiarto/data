<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class DimJalurSeleksi extends Model
{
    protected $table = 'dim_jalur_seleksi';

    protected $fillable = ['nama_jalur', 'lembaga', 'kelompok_jalur', 'kelompok_jalur_id', 'status_aktif'];

    protected $casts = ['status_aktif' => 'boolean'];

    public function kelompokJalur()
    {
        return $this->belongsTo(DimKelompokJalur::class, 'kelompok_jalur_id');
    }

    public function alias(): HasMany
    {
        return $this->hasMany(JalurAlias::class, 'jalur_seleksi_id');
    }

    public function factKuota(): HasMany
    {
        return $this->hasMany(FactKuota::class, 'jalur_seleksi_id');
    }

    public function factPendaftaran(): HasMany
    {
        return $this->hasMany(FactPendaftaran::class, 'jalur_seleksi_id');
    }

    public function factKelulusan(): HasMany
    {
        return $this->hasMany(FactKelulusan::class, 'jalur_seleksi_id');
    }

    public function factRegistrasi(): HasMany
    {
        return $this->hasMany(FactRegistrasi::class, 'jalur_seleksi_id');
    }

    public function programStudi(): BelongsToMany
    {
        return $this->belongsToMany(DimProgramStudi::class, 'dim_jalur_prodi', 'jalur_seleksi_id', 'program_studi_id')->withTimestamps();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }
}
