<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$jalurs = \App\Models\DimJalurSeleksi::pluck('id')->toArray();
foreach(\App\Models\DimProgramStudi::all() as $prodi) {
    $prodi->jalurSeleksi()->sync($jalurs);
}
echo "Seeded jalur_prodi successfully.\n";
