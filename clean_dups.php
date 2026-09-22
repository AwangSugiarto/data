<?php
$dups = DB::select("SELECT tahun_akademik_id, program_studi_id, jalur_seleksi_id, nomor_pilihan, count(*) as c, MIN(id) as old_id FROM fact_pendaftaran GROUP BY tahun_akademik_id, program_studi_id, jalur_seleksi_id, nomor_pilihan HAVING count(*) > 1");
foreach($dups as $dup) { 
    App\Models\FactPendaftaran::where("id", $dup->old_id)->delete(); 
}
echo "Cleaned " . count($dups) . " duplicates.\n";
