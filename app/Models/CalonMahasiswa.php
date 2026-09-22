<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalonMahasiswa extends Model
{
    protected $table = 'calon_mahasiswas';
    protected $guarded = ['id'];

    public function tahunAkademik() { return $this->belongsTo(DimTahunAkademik::class, 'tahun_akademik_id'); }
    public function programStudi()  { return $this->belongsTo(DimProgramStudi::class, 'program_studi_id'); }
    public function jalurSeleksi()  { return $this->belongsTo(DimJalurSeleksi::class, 'jalur_seleksi_id'); }
    public function wilayah()       { return $this->belongsTo(DimWilayah::class, 'wilayah_id'); }
    public function kelompokUkt()   { return $this->belongsTo(DimKelompokUkt::class, 'kelompok_ukt_id'); }
    public function warganegara()   { return $this->belongsTo(DimWarganegara::class, 'warganegara_id'); }
    
    public function mahasiswa()     { return $this->hasOne(Mahasiswa::class, 'calon_mahasiswa_id'); }

    /**
     * Status bertahap berdasarkan is_lulus + status_akademik di tabel mahasiswas.
     * Urutan prioritas: Alumni > Stop Out > Drop Out > Registrasi/Aktif > Lulus > Daftar
     */
    public function getStatusLabelAttribute(): string
    {
        $mhs = $this->mahasiswa;

        if ($mhs) {
            $akademik = strtolower($mhs->status_akademik ?? '');
            return match(true) {
                str_contains($akademik, 'lulus')     => 'Alumni',
                str_contains($akademik, 'stop')      => 'Stop Out',
                str_contains($akademik, 'drop')      => 'Drop Out',
                str_contains($akademik, 'mengundur') => 'Mengundurkan Diri',
                str_contains($akademik, 'meninggal') => 'Meninggal',
                default                              => 'Registrasi',
            };
        }

        return $this->is_lulus ? 'Lulus' : 'Daftar';
    }

    /**
     * Status code singkat untuk keperluan filter.
     */
    public function getStatusCodeAttribute(): string
    {
        $mhs = $this->mahasiswa;

        if ($mhs) {
            $akademik = strtolower($mhs->status_akademik ?? '');
            return match(true) {
                str_contains($akademik, 'lulus')     => 'alumni',
                str_contains($akademik, 'stop')      => 'stop_out',
                str_contains($akademik, 'drop')      => 'drop_out',
                str_contains($akademik, 'mengundur') => 'mengundurkan_diri',
                str_contains($akademik, 'meninggal') => 'meninggal',
                default                              => 'registrasi',
            };
        }

        return $this->is_lulus ? 'lulus' : 'daftar';
    }

    /**
     * Badge warna untuk status.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status_code) {
            'alumni'            => 'indigo',
            'registrasi'        => 'green',
            'lulus'             => 'blue',
            'stop_out'          => 'yellow',
            'drop_out'          => 'red',
            'mengundurkan_diri' => 'orange',
            'meninggal'         => 'gray',
            default             => 'gray',
        };
    }
}
