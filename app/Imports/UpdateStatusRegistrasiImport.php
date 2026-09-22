<?php

namespace App\Imports;

use App\Models\CalonMahasiswa;
use App\Models\DimKelompokUkt;
use App\Models\Mahasiswa;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;

/**
 * Import Penetapan Registrasi Cama.
 *
 * Template: nomor_pendaftaran | nim | kelompok_ukt
 *
 * Semua baris diproses sebagai "Registrasi" →
 *   - CalonMahasiswa.is_lulus = true
 *   - Mahasiswa dibuat/diupdate dengan status_akademik = 'Aktif'
 */
class UpdateStatusRegistrasiImport implements OnEachRow, WithHeadingRow
{
    private int $created = 0;
    private int $updated = 0;
    private int $skipped = 0;

    public function onRow(Row $row): void
    {
        $data = $row->toArray();

        $nomorPendaftaran = trim($data['nomor_pendaftaran'] ?? $data['nomor_tes'] ?? $data['nomor_registrasi'] ?? '');
        $nim              = trim($data['nim'] ?? '') ?: null;
        $kelompokUkt      = trim($data['kelompok_ukt'] ?? $data['ukt'] ?? '') ?: null;

        if (!$nomorPendaftaran) {
            $this->skipped++;
            return;
        }

        /** @var CalonMahasiswa|null $cama */
        $cama = CalonMahasiswa::where('nomor_pendaftaran', $nomorPendaftaran)->first();
        if (!$cama) {
            $this->skipped++;
            return;
        }

        // Resolusi kelompok UKT
        $uktId = null;
        if ($kelompokUkt !== null) {
            $uktId = DimKelompokUkt::where('kelompok', $kelompokUkt)->value('id');
        }
        // Fallback: gunakan UKT yang sudah ada di Cama
        if (!$uktId) {
            $uktId = $cama->kelompok_ukt_id;
        }

        DB::transaction(function () use ($cama, $nim, $uktId) {
            // Pastikan Cama ditandai lulus
            $cama->update(['is_lulus' => true, 'kelompok_ukt_id' => $uktId ?? $cama->kelompok_ukt_id]);

            $isNew = !$cama->mahasiswa()->exists();

            // Buat atau update record Mahasiswa — selalu Aktif
            Mahasiswa::updateOrCreate(
                ['calon_mahasiswa_id' => $cama->id],
                [
                    'nim'             => $nim ?? $cama->mahasiswa?->nim ?? 'REG-' . $cama->id,
                    'tahun_masuk_id'  => $cama->tahun_akademik_id,
                    'status_akademik' => 'Aktif',   // ← otomatis Aktif saat registrasi
                    'kelompok_ukt_id' => $uktId,
                ]
            );

            if ($isNew) {
                $this->created++;
            } else {
                $this->updated++;
            }
        });
    }

    public function getCreated(): int { return $this->created; }
    public function getUpdated(): int { return $this->updated; }
    public function getSkipped(): int { return $this->skipped; }
}
