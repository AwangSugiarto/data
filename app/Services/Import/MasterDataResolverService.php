<?php

namespace App\Services\Import;

use App\Models\DimFakultas;
use App\Models\DimJalurSeleksi;
use App\Models\DimProgramStudi;
use App\Models\DimTahunAkademik;
use App\Models\DimWilayah;
use App\Models\DimKelompokUkt;
use App\Models\FakultasAlias;
use App\Models\JalurAlias;
use App\Models\MasterDataAntrian;
use App\Models\MasterDataTeksAlias;
use App\Models\ProdiAlias;
use App\Models\DimWarganegara;

/**
 * Memetakan nilai string dari Excel ke ID master data.
 * Semua pencarian bersifat case-insensitive dengan alias support.
 */
class MasterDataResolverService
{
    // Cache untuk mempercepat resolusi berulang
    private array $cacheProdi    = [];
    private array $cacheFakultas = [];
    private array $cacheJalur    = [];
    private array $cacheTahun    = [];
    private array $cacheWilayah  = [];
    private array $cacheUkt      = [];
    private array $cacheWarganegara = [];

    public function __construct()
    {
        $this->warmCache();
    }

    private function warmCache(): void
    {
        // Tahun akademik
        DimTahunAkademik::all()->each(function ($t) {
            $this->cacheTahun[(string) $t->tahun] = $t->id;
        });

        // Jalur + alias
        DimJalurSeleksi::with('alias')->get()->each(function ($j) {
            $key = strtolower($j->nama_jalur);
            $this->cacheJalur[$key] = $j->id;
            foreach ($j->alias as $a) {
                $this->cacheJalur[strtolower($a->nama_alias)] = $j->id;
            }
        });

        // Fakultas + alias
        DimFakultas::with('alias')->get()->each(function ($f) {
            $key = strtolower($f->nama_fakultas);
            $this->cacheFakultas[$key] = $f->id;
            if ($f->kode_fakultas) {
                $this->cacheFakultas[strtolower($f->kode_fakultas)] = $f->id;
            }
            foreach ($f->alias as $a) {
                $this->cacheFakultas[strtolower($a->nama_alias)] = $f->id;
            }
        });

        // Prodi + alias
        DimProgramStudi::with('alias')->get()->each(function ($p) {
            $key = strtolower($p->nama_prodi);
            $this->cacheProdi[$key] = $p->id;
            
            if ($p->kode_prodi) {
                $this->cacheProdi[strtolower(trim($p->kode_prodi))] = $p->id;
            }

            foreach ($p->alias as $a) {
                $this->cacheProdi[strtolower($a->nama_alias)] = $p->id;
            }
        });

        // Wilayah
        DimWilayah::all()->each(function ($w) {
            $prov = strtolower(trim($w->provinsi));
            $kab  = strtolower(trim($w->kabupaten_kota));
            $kec  = strtolower(trim($w->kecamatan ?? ''));
            
            $gabungan = implode(' | ', array_filter([$prov, $kab, $kec]));
            $this->cacheWilayah[$gabungan] = $w->id;
            
            if ($kab && !isset($this->cacheWilayah[$kab])) {
                $this->cacheWilayah[$kab] = $w->id;
            }
            if ($prov && !isset($this->cacheWilayah[$prov])) {
                $this->cacheWilayah[$prov] = $w->id;
            }
        });

        // 6. UKT
        DimKelompokUkt::all()->each(function ($u) {
            $this->cacheUkt[strtolower(trim($u->kelompok))] = $u->id;
        });
        
        // 7. Warganegara
        DimWarganegara::all()->each(function ($w) {
            $nama = strtolower(trim($w->nama_negara));
            $this->cacheWarganegara[$nama] = $w->id;
            if ($w->kode_negara) {
                $this->cacheWarganegara[strtolower(trim($w->kode_negara))] = $w->id;
            }
        });

        // Mapping dinamis dari antrian
        MasterDataAntrian::where('status', 'dijadikan_alias')
            ->whereNotNull('dipetakan_ke_id')
            ->get()
            ->each(function ($a) {
                $key = strtolower(trim($a->nilai_mentah));
                if ($a->tipe === 'wilayah') $this->cacheWilayah[$key] = $a->dipetakan_ke_id;
                if ($a->tipe === 'ukt') $this->cacheUkt[$key] = $a->dipetakan_ke_id;
                if ($a->tipe === 'warganegara') $this->cacheWarganegara[$key] = $a->dipetakan_ke_id;
            });
            
        // Load Teks Alias (Auto-fix typo massal)
        MasterDataTeksAlias::all()->each(function($t) {
            $this->cacheTeksAlias[$t->tipe][strtolower(trim($t->nilai_mentah))] = strtolower(trim($t->dipetakan_ke_teks));
        });
    }

    public function applyTeksAlias(string $tipe, ?string $value): ?string
    {
        if (!$value) return $value;
        $key = strtolower(trim($value));
        return $this->cacheTeksAlias[$tipe][$key] ?? $value;
    }

    public function resolveTahun(?string $value): ?int
    {
        $v = trim($value ?? '');
        return $this->cacheTahun[strtolower($v)] ?? null;
    }

    public function resolveJalur(?string $value): ?int
    {
        return $this->cacheJalur[strtolower(trim($value ?? ''))] ?? null;
    }

    public function resolveFakultas(?string $value): ?int
    {
        return $this->cacheFakultas[strtolower(trim($value ?? ''))] ?? null;
    }

    public function resolveProdi(?string $value): ?int
    {
        return $this->cacheProdi[strtolower(trim($value ?? ''))] ?? null;
    }

    public function resolveWilayah(?string $prov, ?string $kab, ?string $kec): ?int
    {
        $prov = $this->applyTeksAlias('wilayah_prov', $prov);
        $kab  = $this->applyTeksAlias('wilayah_kab', $kab);
        $kec  = $this->applyTeksAlias('wilayah_kec', $kec);
        
        $gabungan = implode(' | ', array_filter([strtolower(trim($prov ?? '')), strtolower(trim($kab ?? '')), strtolower(trim($kec ?? ''))]));
        
        // Coba cari gabungan lengkap terlebih dahulu
        if (isset($this->cacheWilayah[$gabungan])) {
            return $this->cacheWilayah[$gabungan];
        }
        
        // Fallback: cari by kabupaten saja jika gabungan tidak ada, atau by provinsi saja
        $kabKey = strtolower(trim($kab ?? ''));
        if ($kabKey && isset($this->cacheWilayah[$kabKey])) return $this->cacheWilayah[$kabKey];
        
        $provKey = strtolower(trim($prov ?? ''));
        if ($provKey && isset($this->cacheWilayah[$provKey])) return $this->cacheWilayah[$provKey];
        
        return null;
    }

    public function resolveUkt(?string $value): ?int
    {
        return $this->cacheUkt[strtolower(trim($value ?? ''))] ?? null;
    }

    public function resolveWarganegara(?string $nama, ?string $kode): ?int
    {
        $gabungan = implode(' | ', array_filter([strtolower(trim($nama ?? '')), strtolower(trim($kode ?? ''))]));
        
        if (isset($this->cacheWarganegara[$gabungan])) {
            return $this->cacheWarganegara[$gabungan];
        }

        $namaKey = strtolower(trim($nama ?? ''));
        if ($namaKey && isset($this->cacheWarganegara[$namaKey])) return $this->cacheWarganegara[$namaKey];

        $kodeKey = strtolower(trim($kode ?? ''));
        if ($kodeKey && isset($this->cacheWarganegara[$kodeKey])) return $this->cacheWarganegara[$kodeKey];

        return null;
    }

    /**
     * Daftarkan ke tabel antrian bila tidak berhasil diresolve
     */
    public function enqueueAntrian(string $tipe, string $nilaiMentah, int $batchId): void
    {
        MasterDataAntrian::updateOrCreate([
            'tipe'        => $tipe,
            'nilai_mentah'=> $nilaiMentah,
        ], [
            'import_batch_id' => $batchId,
            'status'          => 'menunggu',
        ]);
    }
}
