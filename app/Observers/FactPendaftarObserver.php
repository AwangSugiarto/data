<?php

namespace App\Observers;

use App\Models\FactPendaftarIndividu;

class FactPendaftarObserver
{
    /**
     * Handle the FactPendaftarIndividu "created" event.
     */
    public function created(FactPendaftarIndividu $factPendaftarIndividu): void
    {
        //
    }

    /**
     * Handle the FactPendaftarIndividu "updated" event.
     */
    public function updated(FactPendaftarIndividu $factPendaftarIndividu): void
    {
        if ($factPendaftarIndividu->isDirty('status') && $factPendaftarIndividu->status === 'registrasi') {
            $exists = \App\Models\MasterMahasiswa::where('fact_pendaftar_individu_id', $factPendaftarIndividu->id)->exists();
            
            if (!$exists) {
                // Auto-generate NIM sederhana
                $nim = date('Y') . str_pad($factPendaftarIndividu->program_studi_id, 2, '0', STR_PAD_LEFT) . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                
                \App\Models\MasterMahasiswa::create([
                    'fact_pendaftar_individu_id' => $factPendaftarIndividu->id,
                    'nim' => $nim,
                    'nama' => $factPendaftarIndividu->nama ?: 'Tanpa Nama',
                    'program_studi_id' => $factPendaftarIndividu->program_studi_id,
                    'tahun_masuk_id' => $factPendaftarIndividu->tahun_akademik_id,
                    'status_akademik' => 'aktif'
                ]);
            }
        }
    }

    /**
     * Handle the FactPendaftarIndividu "deleted" event.
     */
    public function deleted(FactPendaftarIndividu $factPendaftarIndividu): void
    {
        //
    }

    /**
     * Handle the FactPendaftarIndividu "restored" event.
     */
    public function restored(FactPendaftarIndividu $factPendaftarIndividu): void
    {
        //
    }

    /**
     * Handle the FactPendaftarIndividu "force deleted" event.
     */
    public function forceDeleted(FactPendaftarIndividu $factPendaftarIndividu): void
    {
        //
    }
}
