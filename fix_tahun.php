<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tahuns = \App\Models\DimTahunAkademik::all();
foreach($tahuns as $t) {
    if (strpos($t->keterangan, 'Ganjil') === false && strpos($t->keterangan, 'Genap') === false) {
        $t->keterangan = 'Tahun ' . $t->tahun . ' Ganjil';
        $t->save();
    }
}
echo "Done\n";
