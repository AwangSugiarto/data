<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\DimKelompokJalur;

$jalurs = DB::table('dim_jalur_seleksi')->get();

foreach ($jalurs as $jalur) {
    if (!empty($jalur->kelompok_jalur)) {
        // Find or create Kelompok
        $kelompok = DimKelompokJalur::firstOrCreate([
            'nama_kelompok' => ucfirst($jalur->kelompok_jalur)
        ]);

        // Update jalur
        DB::table('dim_jalur_seleksi')
            ->where('id', $jalur->id)
            ->update(['kelompok_jalur_id' => $kelompok->id]);
        
        echo "Updated jalur {$jalur->nama_jalur} with kelompok_id {$kelompok->id}\n";
    }
}
echo "Migration complete.\n";
