<?php
// Merge Propinsi
$duplicates = App\Models\Propinsi::select("nama_prop", \DB::raw("count(*) as total"))->groupBy("nama_prop")->having("total", ">", 1)->get();
foreach ($duplicates as $dup) {
    $props = App\Models\Propinsi::where("nama_prop", $dup->nama_prop)->orderBy("kode_prop", "asc")->get();
    $main = $props->first();
    foreach ($props as $i => $p) {
        if ($i == 0) continue;
        // Move kabupatens
        App\Models\Kabupaten::where("kode_prop", $p->kode_prop)->update(["kode_prop" => $main->kode_prop]);
        // Move kecamatans
        App\Models\Kecamatan::where("kode_prop", $p->kode_prop)->update(["kode_prop" => $main->kode_prop]);
        // Delete duplicate propinsi
        $p->delete();
    }
}
echo "Propinsi merged.\n";

// Merge Kabupaten
$duplicates = App\Models\Kabupaten::select("kode_prop", "nama_kab", \DB::raw("count(*) as total"))->groupBy("kode_prop", "nama_kab")->having("total", ">", 1)->get();
foreach ($duplicates as $dup) {
    $kabs = App\Models\Kabupaten::where("kode_prop", $dup->kode_prop)->where("nama_kab", $dup->nama_kab)->orderBy("kode_kab", "asc")->get();
    $main = $kabs->first();
    foreach ($kabs as $i => $k) {
        if ($i == 0) continue;
        // Move kecamatans
        App\Models\Kecamatan::where("kode_kab", $k->kode_kab)->update(["kode_kab" => $main->kode_kab]);
        // Delete duplicate kabupaten
        $k->delete();
    }
}
echo "Kabupaten merged.\n";

// Merge Kecamatan
$duplicates = App\Models\Kecamatan::select("kode_kab", "nama_kec", \DB::raw("count(*) as total"))->groupBy("kode_kab", "nama_kec")->having("total", ">", 1)->get();
foreach ($duplicates as $dup) {
    $kecs = App\Models\Kecamatan::where("kode_kab", $dup->kode_kab)->where("nama_kec", $dup->nama_kec)->orderBy("kode_kec", "asc")->get();
    $main = $kecs->first();
    foreach ($kecs as $i => $k) {
        if ($i == 0) continue;
        // Move sekolah
        App\Models\Sekolah::where("kode_kec", $k->kode_kec)->update(["kode_kec" => $main->kode_kec]);
        // Delete duplicate kecamatan
        $k->delete();
    }
}
echo "Kecamatan merged.\n";

