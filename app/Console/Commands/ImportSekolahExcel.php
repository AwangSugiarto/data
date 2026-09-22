<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Propinsi;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Sekolah;
use Illuminate\Support\Str;

class ImportSekolahExcel extends Command
{
    protected $signature = 'import:sekolah-excel {file=DATA_SEKOLAH_2025.xlsx}';
    protected $description = 'Import data sekolah dan wilayah dari file Excel';

    // Mapping cache to prevent hitting DB in loop
    protected $provinsiCache = [];
    protected $kabupatenCache = [];
    protected $kecamatanCache = [];
    protected $kelurahanCache = [];

    // Latest max IDs for dummy generation
    protected $maxProvinsiCode = 990000;
    protected $maxKabupatenCode = 990000;
    protected $maxKecamatanCode = 990000;
    protected $maxKelurahanCode = 99000000;

    private function cleanName($name)
    {
        $name = strtoupper($name);
        $name = str_replace('PROV. ', '', $name);
        $name = str_replace('PROVINSI ', '', $name);
        $name = str_replace('KAB. ', '', $name);
        $name = str_replace('KEC. ', '', $name);
        return trim($name);
    }

    public function handle()
    {
        $file = $this->argument('file');
        
        if (!file_exists($file)) {
            $this->error("File {$file} tidak ditemukan!");
            return Command::FAILURE;
        }

        $this->info("Membaca file Excel...");
        $data = Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray {
            public function array(array $array): void {}
        }, $file);

        $rows = $data[0] ?? [];
        $totalRows = count($rows);
        $this->info("Ditemukan {$totalRows} baris. Memulai impor...");

        // Pre-load existing data for fast lookup
        $this->provinsiCache = Propinsi::pluck('kode_prop', 'nama_prop')->toArray();
        // Since names might overlap across regions, we map by "Name_ParentCode"
        foreach(Kabupaten::all() as $k) {
            $this->kabupatenCache[strtolower($k->nama_kab) . '_' . $k->kode_prop] = $k->kode_kab;
        }
        foreach(Kecamatan::all() as $k) {
            $this->kecamatanCache[strtolower($k->nama_kec) . '_' . $k->kode_kab] = $k->kode_kec;
        }
        foreach(Kelurahan::all() as $k) {
            $this->kelurahanCache[strtolower($k->nama_kel) . '_' . $k->kode_kec] = $k->kode_kel;
        }

        // Initialize max codes from DB to prevent collisions
        $this->maxProvinsiCode = max((int) Propinsi::where('kode_prop', 'like', '99%')->max('kode_prop'), 990000);
        $this->maxKabupatenCode = max((int) Kabupaten::where('kode_kab', 'like', '99%')->max('kode_kab'), 990000);
        $this->maxKecamatanCode = max((int) Kecamatan::where('kode_kec', 'like', '99%')->max('kode_kec'), 990000);

        $bar = $this->output->createProgressBar($totalRows - 3);
        $bar->start();

        // Columns mapped to index based on earlier observation
        // 0: NPSN, 1: NAMA SEKOLAH, 2: JENIS (Kelompok), 3: AKREDITASI (Status), 4: ALAMAT, 5: DESA, 6: KECAMATAN, 7: KABUPATEN, 8: PROVINSI
        
        $insertedSchools = 0;
        
        for ($i = 3; $i < $totalRows; $i++) {
            $row = $rows[$i];
            
            // Skip empty rows (no NPSN or Name)
            if (empty($row[0]) && empty($row[1])) {
                $bar->advance();
                continue;
            }

            $npsn = (string) $row[0];
            $namaSekolah = $row[1];
            $jenis = $row[2];
            $akreditasi = $row[3];
            $alamat = $row[4];
            $namaDesa = trim($row[5] ?? '');
            $namaKecamatan = trim($row[6] ?? '');
            $namaKabupaten = trim($row[7] ?? '');
            $namaProvinsi = trim($row[8] ?? '');
            $kodePos = $row[9] ?? null;
            $telepon = $row[10] ?? null;
            $fax = $row[11] ?? null;
            $email = $row[12] ?? null;
            $web = $row[13] ?? null;
            $jumlahSiswa = (int) ($row[14] ?? 0);
            $kurikulum = $row[15] ?? null;
            $namaKepsek = $row[16] ?? null;
            $hpKepsek = $row[17] ?? null;
            $emailKepsek = $row[18] ?? null;

            // Fallback for missing territory names so they don't break logic
            if (empty($namaProvinsi)) $namaProvinsi = "TIDAK DIKETAHUI";
            if (empty($namaKabupaten)) $namaKabupaten = "TIDAK DIKETAHUI";
            if (empty($namaKecamatan)) $namaKecamatan = "TIDAK DIKETAHUI";

            $cleanProv = $this->cleanName($namaProvinsi);
            $cleanKab = $this->cleanName($namaKabupaten);
            $cleanKec = $this->cleanName($namaKecamatan);

            // 1. Resolve Provinsi
            $keyProv = strtolower($cleanProv);
            if (!isset($this->provinsiCache[$keyProv])) {
                $this->maxProvinsiCode++;
                $kodeProv = (string) $this->maxProvinsiCode;
                Propinsi::create([
                    'kode_prop' => $kodeProv,
                    'nama_prop' => strtoupper($cleanProv),
                    'kab' => 0,
                    'kec' => 0,
                    'kel' => 0
                ]);
                $this->provinsiCache[$keyProv] = $kodeProv;
            }
            $kodeProv = $this->provinsiCache[$keyProv];

            // 2. Resolve Kabupaten
            $keyKab = strtolower($cleanKab) . '_' . $kodeProv;
            if (!isset($this->kabupatenCache[$keyKab])) {
                $this->maxKabupatenCode++;
                $kodeKab = (string) $this->maxKabupatenCode;
                Kabupaten::create([
                    'kode_kab' => $kodeKab,
                    'kode_prop' => $kodeProv,
                    'nama_kab' => strtoupper($cleanKab),
                    'kec' => 0,
                    'kel' => 0
                ]);
                $this->kabupatenCache[$keyKab] = $kodeKab;
            }
            $kodeKab = $this->kabupatenCache[$keyKab];

            // 3. Resolve Kecamatan
            $keyKec = strtolower($cleanKec) . '_' . $kodeKab;
            if (!isset($this->kecamatanCache[$keyKec])) {
                $this->maxKecamatanCode++;
                $kodeKec = (string) $this->maxKecamatanCode;
                Kecamatan::create([
                    'kode_kec' => $kodeKec,
                    'kode_prop' => $kodeProv,
                    'kode_kab' => $kodeKab,
                    'nama_kec' => strtoupper($cleanKec),
                    'kel' => 0
                ]);
                $this->kecamatanCache[$keyKec] = $kodeKec;
            }
            $kodeKec = $this->kecamatanCache[$keyKec];

            // 4. Insert/Update Sekolah
            // If NPSN is empty, we must generate a temporary one just in case, but it's risky. Let's just use uniqid if empty.
            if (empty($npsn)) {
                $npsn = 'TMP-' . strtoupper(Str::random(6));
            }
            
            // Map status. Usually 'N' in name means Negeri.
            $status = 'SWASTA';
            if (str_contains(strtoupper($namaSekolah), 'NEGERI') || str_starts_with(strtoupper($namaSekolah), 'SMAN') || str_starts_with(strtoupper($namaSekolah), 'SMKN') || str_starts_with(strtoupper($namaSekolah), 'MAN') || str_starts_with(strtoupper($namaSekolah), 'MTsN')) {
                $status = 'NEGERI';
            }

            Sekolah::updateOrCreate(
                ['npsn' => $npsn],
                [
                    'kode_kec' => $kodeKec,
                    'nama_sekolah' => $namaSekolah,
                    'alamat_sekolah' => substr($alamat ?? '-', 0, 250),
                    'kelompok' => $jenis ?? '-',
                    'status' => $status,
                    'akreditasi' => $akreditasi,
                    'kode_pos' => $kodePos,
                    'telepon' => $telepon,
                    'fax' => $fax,
                    'email' => $email,
                    'web' => $web,
                    'jumlah_siswa' => $jumlahSiswa ?: null,
                    'kurikulum' => $kurikulum,
                    'nama_kepsek' => $namaKepsek,
                    'hp_kepsek' => $hpKepsek,
                    'email_kepsek' => $emailKepsek,
                ]
            );
            $insertedSchools++;

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Selesai! $insertedSchools data sekolah berhasil diproses.");

        return Command::SUCCESS;
    }
}
