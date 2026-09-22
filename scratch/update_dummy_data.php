<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FactPendaftarIndividu;
use App\Models\DimWilayah;

$records = FactPendaftarIndividu::all();
$genders = ['Laki-laki', 'Perempuan'];
$jenis = ['SMA Negeri', 'SMA Swasta', 'SMK Negeri', 'SMK Swasta', 'MA Negeri', 'MA Swasta', 'Pondok Pesantren'];

foreach($records as $rec) {
    $rec->jenis_kelamin = $genders[array_rand($genders)];
    $j = $jenis[array_rand($jenis)];
    $rec->jenis_sekolah = $j;
    $kab = DimWilayah::find($rec->wilayah_id)?->kabupaten_kota ?? 'Palembang';
    $prefix = explode(' ', $j)[0];
    $rec->asal_sekolah = $prefix . ' 1 ' . $kab;
    $rec->save();
}

echo "Successfully updated " . count($records) . " records.\n";
