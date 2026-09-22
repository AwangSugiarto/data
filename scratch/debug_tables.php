<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "-- calon_mahasiswas --\n";
$cols = DB::select('SHOW COLUMNS FROM calon_mahasiswas');
foreach ($cols as $c) echo "  {$c->Field} ({$c->Type})\n";

echo "\n-- dim_wilayah --\n";
$cols = DB::select('SHOW COLUMNS FROM dim_wilayah');
foreach ($cols as $c) echo "  {$c->Field} ({$c->Type})\n";

echo "\nSample dim_wilayah:\n";
$rows = DB::table('dim_wilayah')->limit(2)->get();
foreach ($rows as $r) echo "  " . implode(', ', (array)$r) . "\n";

echo "\n-- dim_sekolah --\n";
try {
    $cols = DB::select('SHOW COLUMNS FROM dim_sekolah');
    foreach ($cols as $c) echo "  {$c->Field} ({$c->Type})\n";
    $rows = DB::table('dim_sekolah')->limit(2)->get();
    foreach ($rows as $r) echo "  " . implode(', ', (array)$r) . "\n";
} catch (Exception $e) {
    echo "  Tabel tidak ada\n";
}

echo "\n-- Tabel yang mengandung 'sekolah' atau 'negara' atau 'kecamatan' --\n";
$tables = DB::select("SHOW TABLES LIKE '%sekolah%'");
foreach ($tables as $t) echo "  " . current((array)$t) . "\n";
$tables = DB::select("SHOW TABLES LIKE '%negara%'");
foreach ($tables as $t) echo "  " . current((array)$t) . "\n";
$tables = DB::select("SHOW TABLES LIKE '%kecamatan%'");
foreach ($tables as $t) echo "  " . current((array)$t) . "\n";
