<?php

namespace App\Imports;

use App\Models\CalonMahasiswa;
use App\Models\DimProgramStudi;
use App\Models\DimWilayah;
use App\Models\DimKelompokUkt;
use App\Models\DimJalurSeleksi;
use App\Models\DimWarganegara;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Database\Eloquent\Model;

class CamaImport implements ToModel, WithHeadingRow
{
    private int $tahunId;
    private ?int $jalurId;
    private int $inserted = 0;
    private int $updated  = 0;
    private int $skipped  = 0;

    public function __construct(int $tahunId, ?int $jalurId = null)
    {
        $this->tahunId = $tahunId;
        $this->jalurId = $jalurId;
    }

    public function model(array $row): Model|array|null
    {
        $nomorPendaftaran = trim($row['nomor_pendaftaran'] ?? $row['nomor_registrasi'] ?? $row['nomor_tes'] ?? '');
        $nama             = trim($row['nama'] ?? '');

        if (!$nomorPendaftaran || !$nama) {
            $this->skipped++;
            return null;
        }

        // ── Resolusi FK: Jalur ────────────────────────────────────────────────
        $jalurId = $this->jalurId;
        if (!$jalurId && !empty($row['jalur_seleksi'])) {
            $jalurId = DimJalurSeleksi::where('nama_jalur', 'like', '%' . trim($row['jalur_seleksi']) . '%')
                ->orWhereHas('alias', fn($q) => $q->where('nama_alias', 'like', '%' . trim($row['jalur_seleksi']) . '%'))
                ->value('id');
        }

        // ── Resolusi FK: Prodi ────────────────────────────────────────────────
        $prodi = null;
        if (!empty($row['kode_prodi'])) {
            $prodi = DimProgramStudi::where('kode_prodi', trim($row['kode_prodi']))->first();
        }
        if (!$prodi && !empty($row['program_studi'])) {
            $prodi = DimProgramStudi::where('nama_prodi', 'like', '%' . trim($row['program_studi']) . '%')->first();
        }

        // ── Resolusi FK: Wilayah (provinsi + kabupaten + kecamatan) ──────────
        $wilayah = null;
        $kabupaten = trim($row['kabupaten_kota'] ?? $row['kabupaten'] ?? '');
        $provinsi  = trim($row['provinsi'] ?? '');
        $kecamatan = trim($row['kecamatan'] ?? '');

        if ($kabupaten) {
            $query = DimWilayah::where('kabupaten_kota', 'like', "%{$kabupaten}%");
            if ($provinsi) $query->where('provinsi', 'like', "%{$provinsi}%");
            if ($kecamatan) $query->where('kecamatan', 'like', "%{$kecamatan}%");
            $wilayah = $query->first();
        } elseif ($provinsi) {
            $wilayah = DimWilayah::where('provinsi', 'like', "%{$provinsi}%")->first();
        }

        // ── Resolusi FK: UKT ──────────────────────────────────────────────────
        $ukt = null;
        if (!empty($row['ukt'])) {
            $ukt = DimKelompokUkt::where('kelompok', trim($row['ukt']))->first();
        }

        // ── Resolusi FK: Warganegara (kode_negara) ────────────────────────────
        $warganegara = null;
        $kodeNegara = trim($row['kode_negara'] ?? '');
        if (!$kodeNegara) $kodeNegara = 'ID'; // default Indonesia
        $warganegara = DimWarganegara::where('kode_negara', strtoupper($kodeNegara))->first();

        // ── Data non-FK ───────────────────────────────────────────────────────
        $nisn       = trim($row['nisn'] ?? '') ?: null;
        $npsn       = trim($row['npsn'] ?? '') ?: null;
        $jenisKel   = strtoupper(trim($row['jenis_kelamin'] ?? ''));
        $jenisKel   = in_array($jenisKel, ['L', 'P']) ? $jenisKel : null;
        $asalSekolah = trim($row['asal_sekolah'] ?? '') ?: null;
        $jenisSekolah = trim($row['jenis_sekolah'] ?? '') ?: null;

        // ── Cek data existing ─────────────────────────────────────────────────
        $existing = CalonMahasiswa::where('nomor_pendaftaran', $nomorPendaftaran)
            ->where('tahun_akademik_id', $this->tahunId)
            ->first();

        if ($existing) {
            $changed = false;
            if ($prodi   && $existing->program_studi_id  !== $prodi->id)     { $existing->program_studi_id  = $prodi->id;     $changed = true; }
            if ($jalurId && $existing->jalur_seleksi_id  !== $jalurId)        { $existing->jalur_seleksi_id  = $jalurId;       $changed = true; }
            if ($wilayah && $existing->wilayah_id        !== $wilayah->id)    { $existing->wilayah_id        = $wilayah->id;   $changed = true; }
            if ($ukt     && $existing->kelompok_ukt_id   !== $ukt->id)        { $existing->kelompok_ukt_id   = $ukt->id;       $changed = true; }
            if ($warganegara && $existing->warganegara_id !== $warganegara->id){ $existing->warganegara_id   = $warganegara->id;$changed = true; }
            if (!empty($row['nama'])  && $existing->nama !== $nama)            { $existing->nama              = $nama;          $changed = true; }
            if ($nisn     !== null    && $existing->nisn !== $nisn)            { $existing->nisn              = $nisn;          $changed = true; }
            if ($npsn     !== null    && $existing->npsn !== $npsn)            { $existing->npsn              = $npsn;          $changed = true; }
            if ($jenisKel !== null    && $existing->jenis_kelamin !== $jenisKel){ $existing->jenis_kelamin    = $jenisKel;      $changed = true; }
            if ($asalSekolah          && $existing->asal_sekolah !== $asalSekolah){ $existing->asal_sekolah  = $asalSekolah;   $changed = true; }
            if ($jenisSekolah         && $existing->jenis_sekolah !== $jenisSekolah){ $existing->jenis_sekolah = $jenisSekolah; $changed = true; }
            if (!$existing->is_lulus) { $existing->is_lulus = true; $changed = true; }

            if ($changed) { $existing->save(); $this->updated++; } else { $this->skipped++; }
            return null;
        }

        // ── Buat record baru (HANYA is_lulus=true, tidak auto-buat Mahasiswa) ─
        $this->inserted++;
        return new CalonMahasiswa([
            'nomor_pendaftaran' => $nomorPendaftaran,
            'tahun_akademik_id' => $this->tahunId,
            'jalur_seleksi_id'  => $jalurId,
            'program_studi_id'  => $prodi?->id,
            'wilayah_id'        => $wilayah?->id,
            'kelompok_ukt_id'   => $ukt?->id,
            'warganegara_id'    => $warganegara?->id,
            'nama'              => $nama,
            'nisn'              => $nisn,
            'npsn'              => $npsn,
            'jenis_kelamin'     => $jenisKel,
            'asal_sekolah'      => $asalSekolah,
            'jenis_sekolah'     => $jenisSekolah,
            'is_lulus'          => true,
        ]);
    }


    public function getInserted(): int { return $this->inserted; }
    public function getUpdated():  int { return $this->updated; }
    public function getSkipped():  int { return $this->skipped; }
}
