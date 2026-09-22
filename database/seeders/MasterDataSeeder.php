<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Data Fakultas (Berdasarkan inferensi dari nama prodi di gambar)
        $fakultas = [
            1  => ['nama' => 'Syariah dan Hukum', 'kode' => 'FSH'],
            2  => ['nama' => 'Ilmu Tarbiyah dan Keguruan', 'kode' => 'FITK'],
            3  => ['nama' => 'Ushuluddin dan Pemikiran Islam', 'kode' => 'FUSHPI'],
            4  => ['nama' => 'Adab dan Humaniora', 'kode' => 'FAHUM'],
            5  => ['nama' => 'Dakwah dan Komunikasi', 'kode' => 'FDK'],
            6  => ['nama' => 'Ekonomi dan Bisnis Islam', 'kode' => 'FEBI'],
            7  => ['nama' => 'Ilmu Sosial dan Ilmu Politik', 'kode' => 'FISIP'],
            8  => ['nama' => 'Sains dan Teknologi', 'kode' => 'FST'],
            9  => ['nama' => 'Psikologi', 'kode' => 'FPSI'],
            10 => ['nama' => 'Pascasarjana', 'kode' => 'PPs'],
            13 => ['nama' => 'Lembaga / Lainnya', 'kode' => 'Lainnya'],
        ];

        foreach ($fakultas as $id => $f) {
            DB::table('dim_fakultas')->updateOrInsert(
                ['id' => $id],
                ['nama_fakultas' => $f['nama'], 'kode_fakultas' => $f['kode'], 'status_terkini' => 1]
            );
        }

        // 2. Data Program Studi (Transkripsi dari gambar yang dikirim)
        $prodis = [
            // Fakultas 13 (Lainnya)
            ['86902', 13, 'Profesi Pendidikan Profesi Guru Keagamaan', 'S1', 'PPG', 'Baik Sekali'],
            ['80030', 13, 'S3 Peradaban Islam', 'S3', 'Dr.', 'Unggul'],
            
            // Fakultas 10 (Pasca)
            ['61405', 10, 'S3 Perbankan Syariah', 'S3', 'Dr.', 'B'],
            ['86006', 10, 'S3 Pendidikan Agama Islam', 'S3', 'Dr.', 'Unggul'],
            ['80008', 10, 'S3 Ilmu Syariah', 'S3', 'Dr.', 'Unggul'],
            ['74125', 10, 'S2 Hukum Tatanegara (Siyasah)', 'S2', 'M.H.', 'Unggul'],
            ['86130', 10, 'S2 Pendidikan Agama Islam', 'S2', 'M.Pd.', 'Unggul'],
            ['86104', 10, 'S2 Manajemen Pendidikan Islam', 'S2', 'M.Pd.', 'Unggul'],
            ['76124', 10, 'S2 Ilmu Al-Quran dan Tafsir', 'S2', 'M.Ag.', 'Unggul'],
            ['80101', 10, 'S2 Sejarah Peradaban Islam', 'S2', 'M.Hum.', 'Unggul'],
            ['74134', 10, 'S2 Ekonomi Syariah', 'S2', 'M.E.', 'Unggul'],
            ['76102', 10, 'S2 Studi Islam', 'S2', 'M.Ag.', 'Unggul'],

            // Fakultas 1 (FSH)
            ['74230', 1, 'S1 Hukum Keluarga Islam', 'S1', 'S.H.', 'Unggul'],
            ['74223', 1, 'S1 Perbandingan Mazhab', 'S1', 'S.H.', 'Unggul'],
            ['74231', 1, 'S1 Hukum Pidana Islam', 'S1', 'S.H.', 'Unggul'],
            ['74234', 1, 'S1 Hukum Ekonomi Syariah', 'S1', 'S.H.', 'Unggul'],

            // Fakultas 2 (FITK)
            ['86232', 2, 'S1 Pendidikan Guru Madrasah Ibtidaiyah', 'S1', 'S.Pd.', 'Unggul'],
            ['86208', 2, 'S1 Pendidikan Agama Islam', 'S1', 'S.Pd.', 'Unggul'],
            ['86231', 2, 'S1 Manajemen Pendidikan Islam', 'S1', 'S.Pd.', 'Unggul'],
            ['88204', 2, 'S1 Pendidikan Bahasa Arab', 'S1', 'S.Pd.', 'Unggul'],
            ['88203', 2, 'S1 Pendidikan Bahasa Inggris', 'S1', 'S.Pd.', 'Unggul'],
            ['84202', 2, 'S1 Pendidikan Matematika', 'S1', 'S.Pd.', 'Unggul'],
            ['84205', 2, 'S1 Pendidikan Biologi', 'S1', 'S.Pd.', 'Unggul'],
            ['84204', 2, 'S1 Pendidikan Kimia', 'S1', 'S.Pd.', 'Unggul'],
            ['84203', 2, 'S1 Pendidikan Fisika', 'S1', 'S.Pd.', 'Baik Sekali'],
            ['86233', 2, 'S1 Pendidikan Islam Anak Usia Dini', 'S1', 'S.Pd.', 'Unggul'],

            // Fakultas 3 (FUSHPI)
            ['76234', 3, 'S1 Studi Agama-Agama', 'S1', 'S.Ag.', 'Unggul'],
            ['76232', 3, 'S1 Aqidah dan Filsafat Islam', 'S1', 'S.Ag.', 'Unggul'],
            ['76235', 3, 'S1 Ilmu Hadis', 'S1', 'S.Ag.', 'Unggul'],
            ['76231', 3, 'S1 Ilmu Al-Quran dan Tafsir', 'S1', 'S.Ag.', 'Unggul'],
            ['99999', 3, 'S1 Tasawuf dan Psikoterapi', 'S1', 'S.Ag.', 'Unggul'],

            // Fakultas 4 (FAHUM)
            ['76203', 4, 'S1 Bahasa dan Sastra Arab', 'S1', 'S.Hum.', 'A'],
            ['80220', 4, 'S1 Sejarah Peradaban Islam', 'S1', 'S.Hum.', 'Unggul'],
            ['71401', 4, 'S1 Ilmu Perpustakaan', 'S1', 'S.IP.', 'Unggul'],
            ['74227', 4, 'S1 Politik Islam', 'S1', 'S.Sos.', 'Unggul'],

            // Fakultas 5 (FDK)
            ['70233', 5, 'S1 Komunikasi dan Penyiaran Islam', 'S1', 'S.Sos.', 'A'],
            ['70232', 5, 'S1 Bimbingan Penyuluhan Islam', 'S1', 'S.Sos.', 'A'],
            ['70220', 5, 'S1 Jurnalistik', 'S1', 'S.I.Kom.', 'Unggul'],
            ['70230', 5, 'S1 Manajemen Dakwah', 'S1', 'S.Sos.', 'Unggul'],
            ['70231', 5, 'S1 Pengembangan Masyarakat Islam', 'S1', 'S.Sos.', 'Baik Sekali'],

            // Fakultas 6 (FEBI)
            ['60202', 6, 'S1 Ekonomi Syariah', 'S1', 'S.E.', 'Unggul'],
            ['61206', 6, 'S1 Perbankan Syariah', 'S1', 'S.E.', 'Unggul'],
            ['74226', 6, 'S1 Manajemen Zakat dan Wakaf', 'S1', 'S.E.', 'Unggul'],

            // Fakultas 7 (FISIP)
            ['70201', 7, 'S1 Ilmu Komunikasi', 'S1', 'S.I.Kom.', 'B'],
            ['67201', 7, 'S1 Ilmu Politik', 'S1', 'S.IP.', 'Unggul'],

            // Fakultas 8 (FST)
            ['46201', 8, 'S1 Biologi', 'S1', 'S.Si.', 'Baik Sekali'],
            ['47201', 8, 'S1 Kimia', 'S1', 'S.Si.', 'Baik Sekali'],
            ['57201', 8, 'S1 Sistem Informasi', 'S1', 'S.Kom.', 'Baik Sekali'],

            // Fakultas 9 (FPSI)
            ['73201', 9, 'S1 Psikologi Islam', 'S1', 'S.Psi.', 'Baik Sekali'],
        ];

        foreach ($prodis as $idx => $p) {
            DB::table('dim_program_studi')->updateOrInsert(
                ['kode_dikti' => $p[0]],
                [
                    'fakultas_id' => $p[1],
                    'nama_prodi'  => $p[2],
                    'jenjang'     => $p[3], 
                    'nama_jenjang_pendidikan' => $p[3],
                    'gelar'       => $p[4],
                    'akreditasi'  => $p[5],
                    'status_terkini' => 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            );
        }
    }
}
