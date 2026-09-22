<?php

namespace Database\Seeders;

use App\Models\DimFakultas;
use App\Models\DimJalurSeleksi;
use App\Models\DimKelompokUkt;
use App\Models\DimProgramStudi;
use App\Models\DimTahunAkademik;
use App\Models\FactKelulusan;
use App\Models\FactKuota;
use App\Models\FactPendaftaran;
use App\Models\FactRegistrasi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── 1. ROLES ──────────────────────────────────────────────────────────
        $roles = ['super-admin', 'admin-pmb', 'pimpinan', 'viewer-fakultas'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // ─── 2. USERS ──────────────────────────────────────────────────────────
        $superAdmin = User::firstOrCreate(['email' => 'superadmin@uinrf.ac.id'], [
            'name'              => 'Super Admin',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('super-admin');

        $adminPmb = User::firstOrCreate(['email' => 'adminpmb@uinrf.ac.id'], [
            'name'              => 'Admin PMB',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $adminPmb->assignRole('admin-pmb');

        $pimpinan = User::firstOrCreate(['email' => 'rektor@uinrf.ac.id'], [
            'name'              => 'Rektor UIN RF',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $pimpinan->assignRole('pimpinan');

        $viewer = User::firstOrCreate(['email' => 'dekan.fsh@uinrf.ac.id'], [
            'name'              => 'Dekan FSH',
            'password'          => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $viewer->assignRole('viewer-fakultas');

        // ─── 3. TAHUN AKADEMIK ─────────────────────────────────────────────────
        $tahunList = [2015, 2016, 2017, 2018, 2019, 2020, 2021, 2022, 2023, 2024];
        $tahunModels = [];
        foreach ($tahunList as $tahun) {
            $tahunModels[$tahun] = DimTahunAkademik::firstOrCreate(
                ['tahun' => $tahun],
                ['keterangan' => 'Tahun Akademik ' . $tahun . '/' . ($tahun + 1)]
            );
        }

        // ─── 4. KELOMPOK UKT ───────────────────────────────────────────────────
        $uktData = [
            [1, 400000, 999999],   [2, 1000000, 1499999],
            [3, 1500000, 1999999], [4, 2000000, 2499999],
            [5, 2500000, 2999999], [6, 3000000, 3999999],
            [7, 4000000, 5999999], [8, 6000000, null],
        ];
        foreach ($uktData as [$kelompok, $min, $max]) {
            DimKelompokUkt::firstOrCreate(
                ['kelompok' => $kelompok],
                ['nominal_min' => $min, 'nominal_max' => $max]
            );
        }

        // ─── 5. JALUR SELEKSI ──────────────────────────────────────────────────
        $jalurData = [
            ['SNBP',          'nasional'],
            ['SPAN-PTKIN',    'nasional'],
            ['SNBT',          'nasional'],
            ['UM-PTKIN',      'nasional'],
            ['Mandiri',       'mandiri'],
            ['Golden Ticket', 'beasiswa'],
            ['Beasiswa',      'beasiswa'],
            ['RPL',           'afirmasi'],
        ];
        $jalurModels = [];
        foreach ($jalurData as [$nama, $kelompok]) {
            $jalurModels[$nama] = DimJalurSeleksi::firstOrCreate(
                ['nama_jalur' => $nama],
                ['kelompok_jalur' => $kelompok, 'status_aktif' => true]
            );
        }

        // ─── 6. FAKULTAS & PRODI ───────────────────────────────────────────────
        $strukturData = [
            ['Fakultas Syariah dan Hukum', 'FSH', [
                ['Hukum Ekonomi Syariah', 'HES', 'S1'],
                ['Hukum Keluarga Islam', 'HKI', 'S1'],
                ['Hukum Tata Negara', 'HTN', 'S1'],
                ['Perbandingan Mazhab', 'PM', 'S1'],
            ]],
            ['Fakultas Tarbiyah dan Keguruan', 'FTK', [
                ['Pendidikan Agama Islam', 'PAI', 'S1'],
                ['Pendidikan Bahasa Arab', 'PBA', 'S1'],
                ['Pendidikan Guru Madrasah Ibtidaiyah', 'PGMI', 'S1'],
                ['Manajemen Pendidikan Islam', 'MPI', 'S1'],
                ['Pendidikan Biologi', 'PBIO', 'S1'],
                ['Pendidikan Matematika', 'PMAT', 'S1'],
            ]],
            ['Fakultas Ushuluddin dan Pemikiran Islam', 'FUPI', [
                ['Aqidah dan Filsafat Islam', 'AFI', 'S1'],
                ['Ilmu Al-Quran dan Tafsir', 'IAT', 'S1'],
                ['Ilmu Hadits', 'IH', 'S1'],
            ]],
            ['Fakultas Dakwah dan Komunikasi', 'FDK', [
                ['Komunikasi dan Penyiaran Islam', 'KPI', 'S1'],
                ['Bimbingan dan Penyuluhan Islam', 'BPI', 'S1'],
                ['Manajemen Dakwah', 'MD', 'S1'],
                ['Pengembangan Masyarakat Islam', 'PMI', 'S1'],
            ]],
            ['Fakultas Ekonomi dan Bisnis Islam', 'FEBI', [
                ['Perbankan Syariah', 'PS', 'S1'],
                ['Ekonomi Syariah', 'ES', 'S1'],
                ['Manajemen Zakat dan Wakaf', 'MZW', 'S1'],
                ['Akuntansi Syariah', 'AS', 'S1'],
            ]],
            ['Fakultas Sains dan Teknologi', 'FST', [
                ['Sistem Informasi', 'SI', 'S1'],
                ['Teknik Informatika', 'TI', 'S1'],
                ['Biologi', 'BIO', 'S1'],
                ['Fisika', 'FIS', 'S1'],
            ]],
            ['Fakultas Psikologi', 'FPSI', [
                ['Psikologi Islam', 'PSI', 'S1'],
            ]],
            ['Pascasarjana', 'SPS', [
                ['Pendidikan Agama Islam', 'PAI-S2', 'S2'],
                ['Hukum Keluarga Islam', 'HKI-S2', 'S2'],
                ['Ekonomi Syariah', 'ES-S2', 'S2'],
            ]],
        ];

        $prodiModels = [];
        foreach ($strukturData as [$namaFakultas, $kode, $prodiList]) {
            $fakultas = DimFakultas::firstOrCreate(
                ['nama_fakultas' => $namaFakultas],
                ['kode_fakultas' => $kode, 'status_terkini' => true]
            );
            foreach ($prodiList as [$namaProdi, $kodeProdi, $jenjang]) {
                $prodi = DimProgramStudi::firstOrCreate(
                    ['nama_prodi' => $namaProdi, 'fakultas_id' => $fakultas->id],
                    ['kode_prodi' => $kodeProdi, 'jenjang' => $jenjang, 'status_terkini' => true]
                );
                $prodiModels[] = $prodi;
            }
        }

        // ─── 7. FAKTA DUMMY (5 tahun, semua prodi, 4 jalur utama) ─────────────
        $jalurUtama = ['SNBP', 'SPAN-PTKIN', 'SNBT', 'Mandiri'];
        $tahunDummy = [2020, 2021, 2022, 2023, 2024];

        foreach ($tahunDummy as $tahun) {
            $tahunModel = $tahunModels[$tahun];
            foreach ($prodiModels as $prodi) {
                foreach ($jalurUtama as $namaJalur) {
                    $jalur = $jalurModels[$namaJalur];

                    $kuota  = rand(20, 80);
                    $daftar = rand($kuota * 3, $kuota * 10);
                    $lulus  = rand((int)($kuota * 0.9), $kuota);
                    $reg    = rand((int)($lulus * 0.75), $lulus);

                    FactKuota::firstOrCreate(
                        [
                            'tahun_akademik_id' => $tahunModel->id,
                            'program_studi_id'  => $prodi->id,
                            'jalur_seleksi_id'  => $jalur->id,
                        ],
                        ['kuota_awal' => $kuota, 'kuota_akhir' => $kuota]
                    );

                    FactPendaftaran::create([
                        'tahun_akademik_id' => $tahunModel->id,
                        'program_studi_id'  => $prodi->id,
                        'jalur_seleksi_id'  => $jalur->id,
                        'jumlah_daftar'     => $daftar,
                    ]);

                    FactKelulusan::firstOrCreate(
                        [
                            'tahun_akademik_id' => $tahunModel->id,
                            'program_studi_id'  => $prodi->id,
                            'jalur_seleksi_id'  => $jalur->id,
                        ],
                        ['jumlah_lulus' => $lulus]
                    );

                    FactRegistrasi::firstOrCreate(
                        [
                            'tahun_akademik_id' => $tahunModel->id,
                            'program_studi_id'  => $prodi->id,
                            'jalur_seleksi_id'  => $jalur->id,
                        ],
                        ['jumlah_registrasi' => $reg]
                    );
                }
            }
        }

        $this->command->info('✅ Seeder selesai: Roles, Users, Master Data, dan Fakta Dummy berhasil diisi.');
    }
}
