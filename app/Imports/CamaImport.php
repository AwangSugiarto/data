<?php

namespace App\Imports;

use App\Models\CalonMahasiswa;
use App\Models\DimJalurSeleksi;
use App\Models\DimKelompokUkt;
use App\Models\DimProgramStudi;
use App\Models\DimWarganegara;
use App\Models\DimWilayah;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CamaImport implements ToCollection, WithHeadingRow
{
    private int  $tahunId;
    private ?int $jalurId;
    private int  $inserted = 0;
    private int  $updated  = 0;
    private int  $skipped  = 0;

    // ── In-memory lookup caches (diisi sekali sebelum proses baris) ────────────
    private array $jalurCache     = []; // 'nama_like' => id
    private array $prodiByKode    = []; // kode_prodi  => id
    private array $prodiByNama    = []; // nama_lower  => id
    private array $wilayahCache   = []; // "kab|prov|kec" => id
    private array $uktCache       = []; // kelompok (string) => id
    private array $warganCache    = []; // kode_negara  => id
    private array $existingNomor  = []; // "nomor|tahunId" => CalonMahasiswa

    public function __construct(int $tahunId, ?int $jalurId = null)
    {
        $this->tahunId = $tahunId;
        $this->jalurId = $jalurId;
    }

    // ── Pre-load semua lookup ke memory ───────────────────────────────────────
    private function preloadLookups(Collection $rows): void
    {
        // 1. Jalur seleksi
        DimJalurSeleksi::with('alias')->get()->each(function ($j) {
            $key = strtolower($j->nama_jalur);
            $this->jalurCache[$key] = $j->id;
            foreach ($j->alias as $a) {
                $this->jalurCache[strtolower($a->nama_alias)] = $j->id;
            }
        });

        // 2. Program studi
        DimProgramStudi::all()->each(function ($p) {
            $this->prodiByKode[trim($p->kode_prodi)] = $p->id;
            $this->prodiByNama[strtolower(trim($p->nama_prodi))] = $p->id;
        });

        // 3. Wilayah — load semua, index by "kab|prov|kec" lowercase
        DimWilayah::all()->each(function ($w) {
            $key = strtolower(trim($w->kabupaten_kota)) . '|'
                 . strtolower(trim($w->provinsi)) . '|'
                 . strtolower(trim($w->kecamatan ?? ''));
            $this->wilayahCache[$key] = $w->id;
        });

        // 4. UKT
        DimKelompokUkt::all()->each(function ($u) {
            $this->uktCache[(string) $u->kelompok] = $u->id;
        });

        // 5. Warganegara
        DimWarganegara::all()->each(function ($w) {
            $this->warganCache[strtoupper($w->kode_negara)] = $w->id;
        });

        // 6. Existing records untuk tahun ini — load sekaligus, index by nomor
        $nomors = $rows->map(fn($r) =>
            trim($r['nomor_pendaftaran'] ?? $r['nomor_registrasi'] ?? $r['nomor_tes'] ?? '')
        )->filter()->unique()->values()->all();

        if (!empty($nomors)) {
            CalonMahasiswa::where('tahun_akademik_id', $this->tahunId)
                ->whereIn('nomor_pendaftaran', $nomors)
                ->get()
                ->each(function ($c) {
                    $this->existingNomor[$c->nomor_pendaftaran] = $c;
                });
        }
    }

    // ── Resolusi jalur dari cache (fuzzy contains) ─────────────────────────────
    private function resolveJalur(?string $namaJalur): ?int
    {
        if (!$namaJalur) return null;
        $needle = strtolower(trim($namaJalur));

        // Exact match dulu
        if (isset($this->jalurCache[$needle])) {
            return $this->jalurCache[$needle];
        }

        // Contains match
        foreach ($this->jalurCache as $key => $id) {
            if (str_contains($key, $needle) || str_contains($needle, $key)) {
                return $id;
            }
        }
        return null;
    }

    // ── Resolusi wilayah dari cache (fuzzy kabupaten + provinsi + kecamatan) ───
    private function resolveWilayah(?string $kab, ?string $prov, ?string $kec): ?int
    {
        if (!$kab) return null;

        $kabN  = strtolower(trim($kab));
        $provN = strtolower(trim($prov ?? ''));
        $kecN  = strtolower(trim($kec ?? ''));

        // Coba exact key
        $key = "{$kabN}|{$provN}|{$kecN}";
        if (isset($this->wilayahCache[$key])) {
            return $this->wilayahCache[$key];
        }

        // Fuzzy: cari yang kabupaten & provinsi & kecamatan semuanya contains
        foreach ($this->wilayahCache as $k => $id) {
            [$wKab, $wProv, $wKec] = explode('|', $k, 3);
            $kabMatch  = str_contains($wKab, $kabN)  || str_contains($kabN, $wKab);
            $provMatch = !$provN || str_contains($wProv, $provN) || str_contains($provN, $wProv);
            $kecMatch  = !$kecN  || str_contains($wKec, $kecN)  || str_contains($kecN, $wKec);
            if ($kabMatch && $provMatch && $kecMatch) {
                return $id;
            }
        }
        return null;
    }

    // ── Proses koleksi baris ──────────────────────────────────────────────────
    public function collection(Collection $rows): void
    {
        // Pre-load semua lookup sekali saja
        $this->preloadLookups($rows);

        $toInsert = [];

        foreach ($rows as $row) {
            $row = $row->toArray();

            $nomorPendaftaran = trim($row['nomor_pendaftaran'] ?? $row['nomor_registrasi'] ?? $row['nomor_tes'] ?? '');
            $nama             = trim($row['nama'] ?? '');

            if (!$nomorPendaftaran || !$nama) {
                $this->skipped++;
                continue;
            }

            // ── Resolusi FK dari cache (TANPA DB query) ───────────────────────
            $jalurId = $this->jalurId;
            if (!$jalurId && !empty($row['jalur_seleksi'])) {
                $jalurId = $this->resolveJalur($row['jalur_seleksi']);
            }

            $prodiId = null;
            if (!empty($row['kode_prodi'])) {
                $prodiId = $this->prodiByKode[trim($row['kode_prodi'])] ?? null;
            }
            if (!$prodiId && !empty($row['program_studi'])) {
                $needle = strtolower(trim($row['program_studi']));
                $prodiId = $this->prodiByNama[$needle] ?? null;
                if (!$prodiId) {
                    foreach ($this->prodiByNama as $key => $id) {
                        if (str_contains($key, $needle) || str_contains($needle, $key)) {
                            $prodiId = $id;
                            break;
                        }
                    }
                }
            }

            $wilayahId = $this->resolveWilayah(
                $row['kabupaten_kota'] ?? $row['kabupaten'] ?? null,
                $row['provinsi'] ?? null,
                $row['kecamatan'] ?? null
            );

            $uktId = !empty($row['ukt'])
                ? ($this->uktCache[(string) trim($row['ukt'])] ?? null)
                : null;

            $kodeNegara  = strtoupper(trim($row['kode_negara'] ?? 'ID')) ?: 'ID';
            $warganegaraId = $this->warganCache[$kodeNegara] ?? ($this->warganCache['ID'] ?? null);

            // ── Data non-FK ───────────────────────────────────────────────────
            $nisn         = trim($row['nisn'] ?? '') ?: null;
            $npsn         = trim($row['npsn'] ?? '') ?: null;
            $jenisKel     = strtoupper(trim($row['jenis_kelamin'] ?? ''));
            $jenisKel     = in_array($jenisKel, ['L', 'P']) ? $jenisKel : null;
            $asalSekolah  = trim($row['asal_sekolah'] ?? '') ?: null;
            $jenisSekolah = trim($row['jenis_sekolah'] ?? '') ?: null;

            // ── Update existing atau siapkan insert ────────────────────────────
            if (isset($this->existingNomor[$nomorPendaftaran])) {
                $existing = $this->existingNomor[$nomorPendaftaran];
                $changed  = false;

                if ($prodiId      && $existing->program_studi_id  !== $prodiId)      { $existing->program_studi_id  = $prodiId;       $changed = true; }
                if ($jalurId      && $existing->jalur_seleksi_id  !== $jalurId)      { $existing->jalur_seleksi_id  = $jalurId;       $changed = true; }
                if ($wilayahId    && $existing->wilayah_id        !== $wilayahId)    { $existing->wilayah_id        = $wilayahId;     $changed = true; }
                if ($uktId        && $existing->kelompok_ukt_id   !== $uktId)        { $existing->kelompok_ukt_id   = $uktId;         $changed = true; }
                if ($warganegaraId && $existing->warganegara_id   !== $warganegaraId){ $existing->warganegara_id    = $warganegaraId; $changed = true; }
                if ($nama         && $existing->nama              !== $nama)         { $existing->nama              = $nama;          $changed = true; }
                if ($nisn !== null && $existing->nisn             !== $nisn)         { $existing->nisn              = $nisn;          $changed = true; }
                if ($npsn !== null && $existing->npsn             !== $npsn)         { $existing->npsn              = $npsn;          $changed = true; }
                if ($jenisKel !== null && $existing->jenis_kelamin !== $jenisKel)    { $existing->jenis_kelamin     = $jenisKel;      $changed = true; }
                if ($asalSekolah  && $existing->asal_sekolah     !== $asalSekolah)  { $existing->asal_sekolah      = $asalSekolah;   $changed = true; }
                if ($jenisSekolah && $existing->jenis_sekolah    !== $jenisSekolah) { $existing->jenis_sekolah     = $jenisSekolah;  $changed = true; }
                if (!$existing->is_lulus)                                            { $existing->is_lulus          = true;           $changed = true; }

                if ($changed) {
                    $existing->save();
                    $this->updated++;
                } else {
                    $this->skipped++;
                }
                continue;
            }

            // ── Siapkan batch insert ──────────────────────────────────────────
            $now = now()->toDateTimeString();
            $toInsert[] = [
                'nomor_pendaftaran' => $nomorPendaftaran,
                'tahun_akademik_id' => $this->tahunId,
                'jalur_seleksi_id'  => $jalurId,
                'program_studi_id'  => $prodiId,
                'wilayah_id'        => $wilayahId,
                'kelompok_ukt_id'   => $uktId,
                'warganegara_id'    => $warganegaraId,
                'nama'              => $nama,
                'nisn'              => $nisn,
                'npsn'              => $npsn,
                'jenis_kelamin'     => $jenisKel,
                'asal_sekolah'      => $asalSekolah,
                'jenis_sekolah'     => $jenisSekolah,
                'is_lulus'          => 1,
                'created_at'        => $now,
                'updated_at'        => $now,
            ];
        }

        // ── Batch insert dalam chunk 200 ──────────────────────────────────────
        foreach (array_chunk($toInsert, 200) as $chunk) {
            CalonMahasiswa::insert($chunk);
            $this->inserted += count($chunk);
        }
    }

    public function getInserted(): int { return $this->inserted; }
    public function getUpdated():  int { return $this->updated; }
    public function getSkipped():  int { return $this->skipped; }
}
