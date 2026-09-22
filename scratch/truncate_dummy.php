<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tablesToTruncate = [
    'fact_kelulusan',
    'fact_kuota',
    'fact_pendaftar_individu',
    'fact_pendaftaran',
    'fact_penerima_beasiswa',
    'fact_registrasi',
    'import_batches',
    'import_staging_rows',
    'master_mahasiswas',
    'activity_log'
];

Schema::disableForeignKeyConstraints();

foreach ($tablesToTruncate as $table) {
    if (Schema::hasTable($table)) {
        DB::table($table)->truncate();
        echo "Truncated table: $table\n";
    }
}

Schema::enableForeignKeyConstraints();
echo "Done.\n";
