<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\DimWilayah;
use App\Models\DimKelompokUkt;
use App\Models\DimProgramStudi;
use App\Models\DimJalurSeleksi;
use App\Models\DimTahunAkademik;
use App\Models\FactPendaftarIndividu;

class WilayahDanIndividuSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed DimWilayah
        $provinces = [
            'SUMATERA SELATAN' => ['Palembang', 'Ogan Ilir', 'Banyuasin', 'Muara Enim', 'Lahat', 'Prabumulih', 'Lubuklinggau', 'Pagar Alam', 'Ogan Komering Ilir', 'Ogan Komering Ulu'],
            'JAMBI' => ['Kota Jambi', 'Muaro Jambi', 'Batanghari', 'Merangin', 'Bungo'],
            'LAMPUNG' => ['Bandar Lampung', 'Metro', 'Lampung Selatan', 'Lampung Tengah', 'Lampung Utara'],
            'BENGKULU' => ['Kota Bengkulu', 'Rejang Lebong', 'Bengkulu Utara', 'Bengkulu Selatan'],
            'BANGKA BELITUNG' => ['Pangkal Pinang', 'Bangka', 'Belitung'],
            'RIAU' => ['Pekanbaru', 'Kampar', 'Bengkalis'],
            'SUMATERA BARAT' => ['Padang', 'Bukittinggi', 'Pariaman'],
            'SUMATERA UTARA' => ['Medan', 'Deli Serdang', 'Binjai'],
            'DKI JAKARTA' => ['Jakarta Selatan', 'Jakarta Timur', 'Jakarta Pusat'],
            'JAWA BARAT' => ['Bandung', 'Bekasi', 'Depok'],
            'JAWA TENGAH' => ['Semarang', 'Surakarta'],
            'JAWA TIMUR' => ['Surabaya', 'Malang']
        ];

        $wilayahIds = [];
        $wilayahWeights = [];

        foreach ($provinces as $prov => $kabs) {
            foreach ($kabs as $kab) {
                $w = DimWilayah::firstOrCreate([
                    'provinsi' => $prov,
                    'kabupaten_kota' => $kab,
                ]);
                
                $wilayahIds[] = $w->id;

                // Weight generation (bias towards Sumsel)
                if ($prov === 'SUMATERA SELATAN') {
                    if ($kab === 'Palembang') $wilayahWeights = array_merge($wilayahWeights, array_fill(0, 40, $w->id));
                    else $wilayahWeights = array_merge($wilayahWeights, array_fill(0, 10, $w->id));
                } else if (in_array($prov, ['JAMBI', 'LAMPUNG', 'BENGKULU', 'BANGKA BELITUNG'])) {
                    $wilayahWeights = array_merge($wilayahWeights, array_fill(0, 3, $w->id));
                } else {
                    $wilayahWeights[] = $w->id;
                }
            }
        }

        // 2. Ensure DimKelompokUkt has data
        if (DimKelompokUkt::count() == 0) {
            for ($i = 1; $i <= 8; $i++) {
                DimKelompokUkt::create([
                    'kelompok' => $i,
                    'nominal_min' => ($i - 1) * 1000000,
                    'nominal_max' => $i * 1000000 + 500000,
                ]);
            }
        }
        $uktIds = DimKelompokUkt::pluck('id')->toArray();
        // Weights for UKT: Bell curve, mostly in group 3, 4, 5
        $uktWeights = array_merge(
            array_fill(0, 5, $uktIds[0] ?? 1),
            array_fill(0, 10, $uktIds[1] ?? 2),
            array_fill(0, 25, $uktIds[2] ?? 3),
            array_fill(0, 30, $uktIds[3] ?? 4),
            array_fill(0, 15, $uktIds[4] ?? 5),
            array_fill(0, 8, $uktIds[5] ?? 6),
            array_fill(0, 5, $uktIds[6] ?? 7),
            array_fill(0, 2, $uktIds[7] ?? 8)
        );

        // 3. Seed FactPendaftarIndividu
        $tahunList = DimTahunAkademik::pluck('id')->toArray();
        $prodiList = DimProgramStudi::pluck('id')->toArray();
        $jalurList = DimJalurSeleksi::pluck('id')->toArray();

        if (empty($tahunList) || empty($prodiList) || empty($jalurList) || empty($wilayahIds) || empty($uktIds)) {
            $this->command->warn('Missing reference data. Please seed Tahun, Prodi, and Jalur first.');
            return;
        }

        $faker = \Faker\Factory::create('id_ID');
        $statuses = ['daftar', 'lulus', 'registrasi', 'tidak_registrasi'];
        $statusWeights = array_merge(
            array_fill(0, 50, 'daftar'),
            array_fill(0, 10, 'tidak_registrasi'),
            array_fill(0, 40, 'lulus')
        ); // Wait, registered must be a subset of lulus, but for simplicity, we'll just randomize independent status for these dummy data, but bias towards 'registrasi' if they are 'lulus' in real life. Let's just use 'registrasi' heavily.
        $statusWeights = array_merge(
            array_fill(0, 40, 'daftar'),
            array_fill(0, 15, 'lulus'),
            array_fill(0, 40, 'registrasi'),
            array_fill(0, 5, 'tidak_registrasi')
        );

        // Batch insert for performance
        $data = [];
        $batchSize = 500;
        $totalRecords = 3000;

        for ($i = 0; $i < $totalRecords; $i++) {
            $data[] = [
                'tahun_akademik_id' => $tahunList[array_rand($tahunList)],
                'program_studi_id' => $prodiList[array_rand($prodiList)],
                'jalur_seleksi_id' => $jalurList[array_rand($jalurList)],
                'wilayah_id' => $wilayahWeights[array_rand($wilayahWeights)],
                'kelompok_ukt_id' => $uktWeights[array_rand($uktWeights)],
                'nomor_tes' => $faker->unique()->numerify('24######'),
                'nama' => $faker->name,
                'status' => $statusWeights[array_rand($statusWeights)],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($data) >= $batchSize) {
                FactPendaftarIndividu::insert($data);
                $data = [];
            }
        }
        
        if (count($data) > 0) {
            FactPendaftarIndividu::insert($data);
        }

        $this->command->info("Seeded $totalRecords pendaftar individu.");
    }
}
