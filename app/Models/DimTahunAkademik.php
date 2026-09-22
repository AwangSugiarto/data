<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class DimTahunAkademik extends Model
{
    protected $table = 'dim_tahun_akademik';

    protected $fillable = ['tahun', 'keterangan'];

    protected $casts = ['tahun' => 'integer'];

    public function factKuota(): HasMany
    {
        return $this->hasMany(FactKuota::class, 'tahun_akademik_id');
    }

    public function factPendaftaran(): HasMany
    {
        return $this->hasMany(FactPendaftaran::class, 'tahun_akademik_id');
    }

    public function factKelulusan(): HasMany
    {
        return $this->hasMany(FactKelulusan::class, 'tahun_akademik_id');
    }

    public function factRegistrasi(): HasMany
    {
        return $this->hasMany(FactRegistrasi::class, 'tahun_akademik_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }
}
