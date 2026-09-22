<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Propinsi;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Sekolah;

class MergeWilayah extends Command
{
    protected $signature = 'app:merge-wilayah';
    protected $description = 'Merge duplicate wilayah and remove prefixes';

    private function cleanName($name)
    {
        $name = strtoupper($name);
        $name = str_replace('PROV. ', '', $name);
        $name = str_replace('PROVINSI ', '', $name);
        $name = str_replace('KAB. ', '', $name);
        $name = str_replace('KEC. ', '', $name);
        // Sometimes they have extra spaces
        return trim($name);
    }

    public function handle()
    {
        $this->info("Memulai penggabungan Provinsi...");
        $provinsis = Propinsi::all();
        $provGroups = [];
        foreach ($provinsis as $p) {
            $clean = $this->cleanName($p->nama_prop);
            $provGroups[$clean][] = $p;
        }

        foreach ($provGroups as $cleanName => $group) {
            if (count($group) > 1) {
                // Find main (prioritize non-99 codes)
                usort($group, function($a, $b) {
                    $aIs99 = str_starts_with($a->kode_prop, '99') ? 1 : 0;
                    $bIs99 = str_starts_with($b->kode_prop, '99') ? 1 : 0;
                    return $aIs99 <=> $bIs99;
                });
                $main = $group[0];
                for ($i = 1; $i < count($group); $i++) {
                    $dup = $group[$i];
                    $this->info("Merge Provinsi: {$dup->nama_prop} -> {$main->nama_prop}");
                    Kabupaten::where('kode_prop', $dup->kode_prop)->update(['kode_prop' => $main->kode_prop]);
                    Kecamatan::where('kode_prop', $dup->kode_prop)->update(['kode_prop' => $main->kode_prop]);
                    $dup->delete();
                }
                // Update main name to clean name
                $main->update(['nama_prop' => $cleanName]);
            } else {
                // Just clean name
                $group[0]->update(['nama_prop' => $cleanName]);
            }
        }

        $this->info("Memulai penggabungan Kabupaten...");
        $kabupatens = Kabupaten::all();
        $kabGroups = [];
        foreach ($kabupatens as $k) {
            $clean = $this->cleanName($k->nama_kab);
            $key = $k->kode_prop . '_' . $clean;
            $kabGroups[$key][] = $k;
        }

        foreach ($kabGroups as $key => $group) {
            if (count($group) > 1) {
                usort($group, function($a, $b) {
                    $aIs99 = str_starts_with($a->kode_kab, '99') ? 1 : 0;
                    $bIs99 = str_starts_with($b->kode_kab, '99') ? 1 : 0;
                    return $aIs99 <=> $bIs99;
                });
                $main = $group[0];
                for ($i = 1; $i < count($group); $i++) {
                    $dup = $group[$i];
                    $this->info("Merge Kabupaten: {$dup->nama_kab} -> {$main->nama_kab}");
                    Kecamatan::where('kode_kab', $dup->kode_kab)->update(['kode_kab' => $main->kode_kab]);
                    $dup->delete();
                }
                $parts = explode('_', $key);
                $main->update(['nama_kab' => $parts[1]]);
            } else {
                $clean = $this->cleanName($group[0]->nama_kab);
                $group[0]->update(['nama_kab' => $clean]);
            }
        }

        $this->info("Memulai penggabungan Kecamatan...");
        $kecamatans = Kecamatan::all();
        $kecGroups = [];
        foreach ($kecamatans as $k) {
            $clean = $this->cleanName($k->nama_kec);
            $key = $k->kode_kab . '_' . $clean;
            $kecGroups[$key][] = $k;
        }

        foreach ($kecGroups as $key => $group) {
            if (count($group) > 1) {
                usort($group, function($a, $b) {
                    $aIs99 = str_starts_with($a->kode_kec, '99') ? 1 : 0;
                    $bIs99 = str_starts_with($b->kode_kec, '99') ? 1 : 0;
                    return $aIs99 <=> $bIs99;
                });
                $main = $group[0];
                for ($i = 1; $i < count($group); $i++) {
                    $dup = $group[$i];
                    $this->info("Merge Kecamatan: {$dup->nama_kec} -> {$main->nama_kec}");
                    Sekolah::where('kode_kec', $dup->kode_kec)->update(['kode_kec' => $main->kode_kec]);
                    $dup->delete();
                }
                $parts = explode('_', $key);
                $main->update(['nama_kec' => $parts[1]]);
            } else {
                $clean = $this->cleanName($group[0]->nama_kec);
                $group[0]->update(['nama_kec' => $clean]);
            }
        }

        $this->info("Selesai!");
        return Command::SUCCESS;
    }
}
