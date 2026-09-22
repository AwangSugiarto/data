<?php
require 'C:/laragon/www/InformasiData/vendor/autoload.php';
$app = require_once 'C:/laragon/www/InformasiData/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Prodi ID 21 ===\n";
$p = DB::table('dim_program_studi')->where('id', 21)->first();
print_r((array)$p);

echo "\n=== Tarif untuk prodi_id=21 ===\n";
$t = DB::table('dim_tarif_ukt')->where('program_studi_id', 21)->get();
echo "Count: " . count($t) . "\n";
foreach ($t as $r) { echo json_encode((array)$r) . "\n"; }

echo "\n=== Prodi S1 Psikologi Islam (cari by nama) ===\n";
$prodis = DB::table('dim_program_studi')
    ->where('nama_prodi', 'like', '%Psikologi%')
    ->get(['id', 'nama_prodi', 'kode_prodi', 'fakultas_id']);
foreach ($prodis as $p) { echo json_encode((array)$p) . "\n"; }

echo "\n=== Tarif by prodi kode 73201 (cari ID dulu) ===\n";
$prodi73201 = DB::table('dim_program_studi')->where('kode_prodi', '73201')->first();
if ($prodi73201) {
    echo "ID: {$prodi73201->id}, Nama: {$prodi73201->nama_prodi}\n";
    $tarifs = DB::table('dim_tarif_ukt')->where('program_studi_id', $prodi73201->id)->get();
    echo "Tarif count: " . count($tarifs) . "\n";
    foreach ($tarifs as $r) { echo json_encode((array)$r) . "\n"; }
}

echo "\n=== Semua prodi_id di dim_tarif_ukt ===\n";
$prodiIds = DB::table('dim_tarif_ukt')->distinct()->pluck('program_studi_id');
echo "Distinct prodi IDs with tarif: " . implode(', ', $prodiIds->toArray()) . "\n";

echo "\n=== Check cama prodi distribution (top 10) ===\n";
$dist = DB::table('calon_mahasiswas as c')
    ->join('mahasiswas as m', 'm.calon_mahasiswa_id', '=', 'c.id')
    ->join('dim_program_studi as ps', 'ps.id', '=', 'c.program_studi_id')
    ->selectRaw('c.program_studi_id, ps.nama_prodi, ps.kode_prodi, COUNT(*) as total')
    ->groupBy('c.program_studi_id', 'ps.nama_prodi', 'ps.kode_prodi')
    ->orderByDesc('total')
    ->limit(10)
    ->get();
foreach ($dist as $r) {
    $hasTarif = DB::table('dim_tarif_ukt')->where('program_studi_id', $r->program_studi_id)->exists();
    echo "  [{$r->program_studi_id}] {$r->kode_prodi} {$r->nama_prodi}: {$r->total} mhs | tarif: " . ($hasTarif ? "ADA" : "TIDAK ADA") . "\n";
}
