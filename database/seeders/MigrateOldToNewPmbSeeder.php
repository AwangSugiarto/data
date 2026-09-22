<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\FactKuota;
use App\Models\FactPendaftaran;
use App\Models\FactPendaftarIndividu;
use App\Models\MasterMahasiswa;
use App\Models\PmbDayaTampung;
use App\Models\PmbPeminatAgregat;
use App\Models\CalonMahasiswa;
use App\Models\Mahasiswa;

class MigrateOldToNewPmbSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function() {
            // 1. Migrate FactKuota -> PmbDayaTampung
            $kuotas = FactKuota::all();
            foreach($kuotas as $k) {
                PmbDayaTampung::updateOrCreate(
                    [
                        'tahun_akademik_id' => $k->tahun_akademik_id,
                        'program_studi_id' => $k->program_studi_id,
                        'jalur_seleksi_id' => $k->jalur_seleksi_id,
                    ],
                    [
                        'daya_tampung' => $k->kuota_akhir,
                    ]
                );
            }

            // 2. Migrate FactPendaftaran -> PmbPeminatAgregat (summing them up per program studi)
            $pendaftars = FactPendaftaran::select('tahun_akademik_id', 'program_studi_id', 'jalur_seleksi_id', DB::raw('SUM(jumlah_daftar) as total'))
                ->groupBy('tahun_akademik_id', 'program_studi_id', 'jalur_seleksi_id')
                ->get();
            foreach($pendaftars as $p) {
                PmbPeminatAgregat::updateOrCreate(
                    [
                        'tahun_akademik_id' => $p->tahun_akademik_id,
                        'program_studi_id' => $p->program_studi_id,
                        'jalur_seleksi_id' => $p->jalur_seleksi_id,
                    ],
                    [
                        'jumlah_peminat' => $p->total,
                    ]
                );
            }

            // 3. Migrate FactPendaftarIndividu -> CalonMahasiswa & Mahasiswa
            $individus = FactPendaftarIndividu::all();
            foreach($individus as $ind) {
                $status = strtolower($ind->status);
                $isLulus = $status !== 'daftar';

                $cama = CalonMahasiswa::updateOrCreate(
                    [
                        'nomor_pendaftaran' => $ind->nomor_tes ?? 'TEST-'.$ind->id,
                    ],
                    [
                        'nama' => $ind->nama,
                        'tahun_akademik_id' => $ind->tahun_akademik_id,
                        'program_studi_id' => $ind->program_studi_id,
                        'jalur_seleksi_id' => $ind->jalur_seleksi_id,
                        'wilayah_id' => $ind->wilayah_id,
                        'kelompok_ukt_id' => $ind->kelompok_ukt_id,
                        'warganegara_id' => $ind->warganegara_id,
                        'jenis_kelamin' => $ind->jenis_kelamin,
                        'asal_sekolah' => $ind->asal_sekolah,
                        'jenis_sekolah' => $ind->jenis_sekolah,
                        'nisn' => $ind->nisn,
                        'npsn' => $ind->npsn,
                        'is_lulus' => $isLulus,
                    ]
                );

                if (in_array($status, ['registrasi', 'alumni'])) {
                    $master = MasterMahasiswa::where('fact_pendaftar_individu_id', $ind->id)->first();
                    Mahasiswa::updateOrCreate(
                        [
                            'calon_mahasiswa_id' => $cama->id,
                        ],
                        [
                            'nim' => $master ? $master->nim : 'NIM-TMP-'.$cama->id,
                            'tahun_masuk_id' => $cama->tahun_akademik_id,
                            'status_akademik' => $status === 'alumni' ? 'Lulus' : 'Aktif',
                            'tanggal_lulus' => $master ? $master->tanggal_lulus : null,
                        ]
                    );
                }
            }
        });
    }
}
