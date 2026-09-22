<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tahun = App\Models\DimTahunAkademik::where('tahun', '20261')->first();
$tId = $tahun?->id;
echo "Tahun 20261 ID: $tId\n\n";

$jalurIds = [5 => 'Mandiri-I', 8 => 'Mandiri-RPL', 17 => 'Mandiri-II', 18 => 'Mandiri'];

foreach ($jalurIds as $jId => $jNama) {
    $cntCama = App\Models\CalonMahasiswa::where('jalur_seleksi_id', $jId)->where('tahun_akademik_id', $tId)->count();
    $lulus = App\Models\CalonMahasiswa::where('jalur_seleksi_id', $jId)->where('tahun_akademik_id', $tId)
        ->where(function($q) { $q->where('is_lulus', true)->orWhereHas('mahasiswa'); })->count();
    $registrasi = App\Models\CalonMahasiswa::where('jalur_seleksi_id', $jId)->where('tahun_akademik_id', $tId)
        ->whereHas('mahasiswa')->count();
    
    // Cek dari FactPendaftarIndividu lama
    $oldAll = App\Models\FactPendaftarIndividu::where('jalur_seleksi_id', $jId)->where('tahun_akademik_id', $tId)->count();
    $oldLulus = App\Models\FactPendaftarIndividu::where('jalur_seleksi_id', $jId)->where('tahun_akademik_id', $tId)
        ->whereIn('status', ['lulus', 'registrasi', 'alumni'])->count();
    
    echo "=== $jNama (ID: $jId) ===\n";
    echo "  CalonMahasiswa total: $cntCama\n";
    echo "  NEW -> Lulus: $lulus | Registrasi: $registrasi\n";
    echo "  OLD FactIndividu total: $oldAll | Lulus+Reg: $oldLulus\n\n";
}

// Check Mandiri-I yang BENAR harusnya berapa dari old
echo "=== Mandiri-I dari FactPendaftarIndividu (semua status) ===\n";
$statusList = App\Models\FactPendaftarIndividu::where('jalur_seleksi_id', 5)->where('tahun_akademik_id', 13)
    ->select('status', \Illuminate\Support\Facades\DB::raw('COUNT(*) as cnt'))
    ->groupBy('status')
    ->get();
foreach($statusList as $s) {
    echo "  Status '{$s->status}': {$s->cnt}\n";
}
