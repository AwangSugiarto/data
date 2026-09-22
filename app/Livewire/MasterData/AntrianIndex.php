<?php

namespace App\Livewire\MasterData;

use App\Models\DimFakultas;
use App\Models\DimJalurSeleksi;
use App\Models\DimProgramStudi;
use App\Models\FakultasAlias;
use App\Models\JalurAlias;
use App\Models\MasterDataAntrian;
use App\Models\ProdiAlias;
use Livewire\Component;
use Livewire\WithPagination;

class AntrianIndex extends Component
{
    use WithPagination;

    public string $filterTipe = '';
    public string $filterStatus = 'menunggu';
    public bool $showTinjauModal = false;

    public ?int $antrianId = null;
    public string $tinjauTipe = '';
    public string $nilaiMentah = '';
    public string $aksi = 'baru'; // 'baru' | 'alias'
    public string $namaBaru = '';
    public string $kodeBaruFakultas = '';
    public string $kelompokJalurBaru = 'lainnya';
    public string $wilayahProv = '';
    public string $wilayahKab = '';
    public string $wilayahKec = '';
    public string $warganegaraKode = '';
    public int|string $pemetaanKeId = '';
    
    public string $aliasProv = '';
    public string $aliasKab = '';
    public string $aliasKec = '';

    public function updatingFilterTipe(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    public function openTinjau(int $id): void
    {
        $a = MasterDataAntrian::findOrFail($id);
        $this->antrianId    = $a->id;
        $this->tinjauTipe   = $a->tipe;
        $this->nilaiMentah  = $a->nilai_mentah;
        $this->namaBaru     = $a->nilai_mentah; // prefill
        $this->aksi         = 'baru';
        $this->pemetaanKeId = '';
        $this->aliasProv    = '';
        $this->aliasKab     = '';
        $this->aliasKec     = '';
        
        if ($this->tinjauTipe === 'wilayah') {
            $parts = explode(' | ', $a->nilai_mentah);
            $this->wilayahProv = $parts[0] ?? '';
            $this->wilayahKab  = $parts[1] ?? '';
            $this->wilayahKec  = $parts[2] ?? '';
        } elseif ($this->tinjauTipe === 'warganegara') {
            $parts = explode(' | ', $a->nilai_mentah);
            $this->namaBaru = $parts[0] ?? '';
            $this->warganegaraKode = $parts[1] ?? '';
        }

        $this->showTinjauModal = true;
    }

    public function konfirmasi(): void
    {
        $antrian = MasterDataAntrian::findOrFail($this->antrianId);
        
        $wilayahRecord = null; // untuk menangkap data DimWilayah hasil konfirmasi (baru atau alias)

        if ($this->aksi === 'baru') {
            // Buat entitas baru di tabel dim_*
            if ($this->tinjauTipe !== 'wilayah') {
                $this->validate(['namaBaru' => 'required|string|max:255']);
            }

            $entity = match ($this->tinjauTipe) {
                'fakultas' => \App\Models\DimFakultas::create([
                    'nama_fakultas'  => $this->namaBaru,
                    'kode_fakultas'  => $this->kodeBaruFakultas ?: null,
                    'status_terkini' => true,
                ]),
                'jalur' => \App\Models\DimJalurSeleksi::create([
                    'nama_jalur'    => $this->namaBaru,
                    'kelompok_jalur'=> $this->kelompokJalurBaru,
                    'status_aktif'  => true,
                ]),
                'prodi' => \App\Models\DimProgramStudi::create([
                    'nama_prodi'  => $this->namaBaru,
                    'fakultas_id' => $this->kodeBaruFakultas ?: null,
                ]),
                'wilayah' => \App\Models\DimWilayah::create([
                    'provinsi'       => $this->wilayahProv ?: 'TIDAK DIKETAHUI',
                    'kabupaten_kota' => $this->wilayahKab ?: 'TIDAK DIKETAHUI',
                    'kecamatan'      => $this->wilayahKec ?: null,
                ]),
                'ukt' => \App\Models\DimKelompokUkt::create([
                    'tahun_akademik_id' => \App\Models\DimTahunAkademik::where('status', 1)->value('id') ?? \App\Models\DimTahunAkademik::first()->id,
                    'kelompok'          => $this->namaBaru,
                ]),
                'warganegara' => \App\Models\DimWarganegara::create([
                    'nama_negara' => $this->namaBaru,
                    'kode_negara' => $this->warganegaraKode ?: null,
                ]),
                default => null,
            };

            if ($this->tinjauTipe === 'wilayah') {
                $wilayahRecord = $entity;
                $this->pemetaanKeId = $entity->id; // set id untuk referensi
            }

            $antrian->update([
                'status'       => 'dikonfirmasi_baru',
                'ditinjau_oleh'=> auth()->id(),
            ]);
            session()->flash('success', 'Entitas baru berhasil dibuat.');
        } else {
            // Jadikan alias
            if ($this->tinjauTipe === 'wilayah') {
                $this->validate([
                    'aliasProv' => 'required',
                    'aliasKab'  => 'required',
                ], [
                    'aliasProv.required' => 'Provinsi harus dipilih.',
                    'aliasKab.required'  => 'Kabupaten harus dipilih.',
                ]);
                
                $wilayah = \App\Models\DimWilayah::firstOrCreate([
                    'provinsi' => $this->aliasProv,
                    'kabupaten_kota' => $this->aliasKab,
                    'kecamatan' => $this->aliasKec ?: null,
                ]);
                    
                $wilayahRecord = $wilayah;
                $this->pemetaanKeId = $wilayah->id;
            } else {
                $this->validate(['pemetaanKeId' => 'required|integer']);
            }

            match ($this->tinjauTipe) {
                'fakultas' => FakultasAlias::firstOrCreate([
                    'fakultas_id' => $this->pemetaanKeId,
                    'nama_alias'  => $this->nilaiMentah,
                ]),
                'jalur' => JalurAlias::firstOrCreate([
                    'jalur_seleksi_id' => $this->pemetaanKeId,
                    'nama_alias'       => $this->nilaiMentah,
                ]),
                'prodi' => ProdiAlias::firstOrCreate([
                    'program_studi_id' => $this->pemetaanKeId,
                    'nama_alias'       => $this->nilaiMentah,
                ]),
                default => null, // wilayah dan ukt akan menggunakan tabel master_data_antrian sebagai referensi alias
            };

            $antrian->update([
                'status'         => 'dijadikan_alias',
                'dipetakan_ke_id'=> $this->pemetaanKeId,
                'ditinjau_oleh'  => auth()->id(),
            ]);
            session()->flash('success', 'Alias berhasil disimpan.');
        }

        // AUTO-FIX: Simpan Substring Teks Alias jika tipenya wilayah
        if ($this->tinjauTipe === 'wilayah' && $wilayahRecord) {
            $parts = explode(' | ', $this->nilaiMentah);
            $rawProv = trim($parts[0] ?? '');
            $rawKab  = trim($parts[1] ?? '');
            $rawKec  = trim($parts[2] ?? '');
            
            $aliasAdded = false;

            // Bandingkan dan simpan jika ada perbedaan (berdasarkan lowercase)
            if ($rawProv && strtolower($rawProv) !== strtolower($wilayahRecord->provinsi)) {
                \App\Models\MasterDataTeksAlias::firstOrCreate([
                    'tipe' => 'wilayah_prov',
                    'nilai_mentah' => $rawProv,
                ], ['dipetakan_ke_teks' => $wilayahRecord->provinsi]);
                $aliasAdded = true;
            }
            if ($rawKab && strtolower($rawKab) !== strtolower($wilayahRecord->kabupaten_kota)) {
                \App\Models\MasterDataTeksAlias::firstOrCreate([
                    'tipe' => 'wilayah_kab',
                    'nilai_mentah' => $rawKab,
                ], ['dipetakan_ke_teks' => $wilayahRecord->kabupaten_kota]);
                $aliasAdded = true;
            }
            if ($rawKec && $wilayahRecord->kecamatan && strtolower($rawKec) !== strtolower($wilayahRecord->kecamatan)) {
                \App\Models\MasterDataTeksAlias::firstOrCreate([
                    'tipe' => 'wilayah_kec',
                    'nilai_mentah' => $rawKec,
                ], ['dipetakan_ke_teks' => $wilayahRecord->kecamatan]);
                $aliasAdded = true;
            }
            
            // Jika ada alias teks yang ditambahkan, coba auto-resolve antrian wilayah lain yang menunggu
            if ($aliasAdded) {
                $resolver = app(\App\Services\Import\MasterDataResolverService::class);
                $autoFixedCount = 0;
                
                \App\Models\MasterDataAntrian::where('tipe', 'wilayah')
                    ->where('status', 'menunggu')
                    ->where('id', '!=', $this->antrianId)
                    ->get()
                    ->each(function($a) use ($resolver, &$autoFixedCount) {
                        $p = explode(' | ', $a->nilai_mentah);
                        $wId = $resolver->resolveWilayah($p[0] ?? '', $p[1] ?? '', $p[2] ?? '');
                        if ($wId) {
                            $a->update([
                                'status' => 'dijadikan_alias',
                                'dipetakan_ke_id' => $wId,
                                'ditinjau_oleh' => auth()->id(),
                            ]);
                            $autoFixedCount++;
                        }
                    });
                    
                if ($autoFixedCount > 0) {
                    session()->flash('success', "Alias berhasil disimpan dan $autoFixedCount antrian lain berhasil diselesaikan secara otomatis!");
                }
            }
        }

        $this->showTinjauModal = false;
    }

    public function render()
    {
        $antrian = MasterDataAntrian::with(['importBatch', 'penanggungJawab'])
            ->when($this->filterTipe,   fn($q) => $q->where('tipe', $this->filterTipe))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->paginate(10);

        $fakultasList = DimFakultas::orderBy('nama_fakultas')->get();
        $jalurList    = DimJalurSeleksi::orderBy('nama_jalur')->get();
        $prodiList    = DimProgramStudi::with('fakultas')->orderBy('nama_prodi')->get();
        $wilayahList  = \App\Models\DimWilayah::orderBy('provinsi')->orderBy('kabupaten_kota')->get();
        
        $masterProvinsi = \App\Models\Propinsi::orderBy('nama_prop')->get()->unique('nama_prop');
        $selectedProv = $this->aksi === 'baru' ? $this->wilayahProv : $this->aliasProv;
        
        $masterKabupaten = \App\Models\Kabupaten::when($selectedProv, function($q) use ($masterProvinsi, $selectedProv) {
            $prop = $masterProvinsi->firstWhere('nama_prop', $selectedProv);
            if ($prop) {
                $q->where('kode_prop', $prop->kode_prop);
            }
        })->orderBy('nama_kab')->get()->unique('nama_kab');

        $selectedKab = $this->aksi === 'baru' ? $this->wilayahKab : $this->aliasKab;
        $masterKecamatan = \App\Models\Kecamatan::when($selectedKab, function($q) use ($masterKabupaten, $selectedKab) {
            $kab = $masterKabupaten->firstWhere('nama_kab', $selectedKab);
            if ($kab) {
                $q->where('kode_kab', $kab->kode_kab);
            }
        })->orderBy('nama_kec')->get()->unique('nama_kec');

        $uktList      = \App\Models\DimKelompokUkt::orderBy('kelompok')->get();
        $warganegaraList = \App\Models\DimWarganegara::orderBy('nama_negara')->get();

        return view('livewire.master-data.antrian-index', compact('antrian','fakultasList','jalurList','prodiList','wilayahList','masterProvinsi','masterKabupaten','masterKecamatan','uktList','warganegaraList'))
            ->layout('layouts.app', ['title' => 'Antrian Master Data']);
    }
}
