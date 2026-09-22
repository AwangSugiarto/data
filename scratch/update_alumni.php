<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FactPendaftarIndividu;

$count = FactPendaftarIndividu::where('status', 'REGISTRASI')->inRandomOrder()->limit(500)->update(['status' => 'ALUMNI']);

echo "Updated {$count} records to ALUMNI.\n";
