<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class DimFakultas extends Model
{
    protected $table = 'dim_fakultas';

    protected $fillable = [
        'nama_fakultas', 'kode_fakultas', 'status_terkini',
        'berlaku_dari', 'berlaku_sampai', 'fakultas_induk_id',
        'pejabat', 'nip_pejabat', 'telepon', 'email', 'keterangan',
    ];

    protected $casts = [
        'status_terkini' => 'boolean',
        'berlaku_dari'   => 'date',
        'berlaku_sampai' => 'date',
    ];

    public function fakultasInduk(): BelongsTo
    {
        return $this->belongsTo(DimFakultas::class, 'fakultas_induk_id');
    }

    public function cabangFakultas(): HasMany
    {
        return $this->hasMany(DimFakultas::class, 'fakultas_induk_id');
    }

    public function alias(): HasMany
    {
        return $this->hasMany(FakultasAlias::class, 'fakultas_id');
    }

    public function programStudi(): HasMany
    {
        return $this->hasMany(DimProgramStudi::class, 'fakultas_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }
}
