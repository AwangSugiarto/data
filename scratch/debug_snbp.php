<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$snbpJalur = App\Models\DimJalurSeleksi::where('nama_jalur', 'like', '%SNBP%')->first();
if (!$snbpJalur) { echo "Jalur SNBP tidak ditemukan\n"; exit; }
echo "Jalur SNBP ID: {$snbpJalur->id} - {$snbpJalur->nama_jalur}\n";

$tahun = App\Models\DimTahunAkademik::where('tahun', '20261')->first();
echo "Tahun 20261 ID: " . ($tahun ? $tahun->id : 'tidak ada') . "\n";

$tId = $tahun?->id;
$jId = $snbpJalur->id;

$lulus = App\Models\CalonMahasiswa::where('jalur_seleksi_id', $jId)
    ->where('tahun_akademik_id', $tId)
    ->where(function($q) { $q->where('is_lulus', true)->orWhereHas('mahasiswa'); })
    ->count();
echo "Lulus (is_lulus=true OR punya mahasiswa): $lulus\n";

$registrasi = App\Models\CalonMahasiswa::where('jalur_seleksi_id', $jId)
    ->where('tahun_akademik_id', $tId)
    ->whereHas('mahasiswa')
    ->count();
echo "Registrasi (punya mahasiswa): $registrasi\n";

// check null jalur 
$nullJalurLulus = App\Models\CalonMahasiswa::whereNull('jalur_seleksi_id')
    ->where('tahun_akademik_id', $tId)
    ->where('is_lulus', true)
    ->count();
$nullJalurReg = App\Models\CalonMahasiswa::whereNull('jalur_seleksi_id')
    ->where('tahun_akademik_id', $tId)
    ->whereHas('mahasiswa')
    ->count();
echo "NULL jalur - Lulus: $nullJalurLulus, Registrasi: $nullJalurReg\n";
