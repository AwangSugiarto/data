<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CalonMahasiswa;
use App\Models\Mahasiswa;
use App\Models\DimFakultas;

$fahum = DimFakultas::where('kode_fakultas', 'FAHUM')->orWhere('nama_fakultas', 'like', '%Adab%')->first();
echo "Fakultas FAHUM: " . ($fahum ? $fahum->nama_fakultas . " (ID: {$fahum->id})" : "TIDAK DITEMUKAN") . "\n\n";

// Total mahasiswas di DB
$totalMhs = Mahasiswa::count();
echo "Total record di tabel mahasiswas: $totalMhs\n";

// Sample data mahasiswas
$sample = Mahasiswa::with('calonMahasiswa')->take(3)->get();
foreach ($sample as $m) {
    $cama = $m->calonMahasiswa;
    echo "  Mahasiswa id={$m->id}, nim={$m->nim}, status_akademik={$m->status_akademik}, cama_id={$m->calon_mahasiswa_id}";
    if ($cama) {
        echo ", is_lulus={$cama->is_lulus}";
    } else {
        echo " [ORPHAN - CAMA TIDAK ADA!]";
    }
    echo "\n";
}

echo "\n";

// Cama lulus FAHUM
if ($fahum) {
    $tahun = App\Models\DimTahunAkademik::where('tahun', '20261')->first();
    $tId = $tahun?->id;
    echo "Cama FAHUM tahun 20261:\n";
    $totalLulus = CalonMahasiswa::where('is_lulus', true)
        ->whereHas('programStudi', fn($q) => $q->where('fakultas_id', $fahum->id))
        ->when($tId, fn($q) => $q->where('tahun_akademik_id', $tId))
        ->count();
    echo "  is_lulus=true: $totalLulus\n";

    $totalReg = CalonMahasiswa::where('is_lulus', true)
        ->whereHas('programStudi', fn($q) => $q->where('fakultas_id', $fahum->id))
        ->when($tId, fn($q) => $q->where('tahun_akademik_id', $tId))
        ->whereHas('mahasiswa')
        ->count();
    echo "  is_lulus=true AND punya mahasiswa: $totalReg\n";
}

// Cek apakah relasi CalonMahasiswa->mahasiswa berfungsi
$camaWithMhs = CalonMahasiswa::whereHas('mahasiswa')->count();
echo "\nTotal CalonMahasiswa yang punya mahasiswa (semua tahun): $camaWithMhs\n";

// Cek apakah relasi mahasiswa di model benar
$firstMhs = Mahasiswa::first();
if ($firstMhs) {
    $cama = $firstMhs->calonMahasiswa;
    echo "Test relasi Mahasiswa->calonMahasiswa: " . ($cama ? "OK (id={$cama->id})" : "GAGAL") . "\n";
}

// Cek struktur tabel mahasiswas
$cols = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM mahasiswas");
echo "\nKolom tabel mahasiswas:\n";
foreach ($cols as $c) {
    echo "  {$c->Field} ({$c->Type})\n";
}
