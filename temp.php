<?php foreach(["KIP", "MAHASISWA ASING", "KERJASAMA"] as $k) { App\Models\DimKelompokUkt::firstOrCreate(["tahun_akademik_id" => 13, "kelompok" => $k]); }
