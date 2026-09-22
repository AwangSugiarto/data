<?php

namespace App\Livewire\MasterData;

use App\Models\Sekolah;
use App\Models\Propinsi;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use Livewire\Component;
use Livewire\WithPagination;

class SekolahIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public string $filterKelompok = '';
    
    public string $filterProvinsi = '';
    public string $filterKabupaten = '';
    public string $filterKecamatan = '';

    public bool $isDetailModalOpen = false;
    public ?Sekolah $selectedSekolah = null;

    // Add Modal State
    public bool $isAddSekolahModalOpen = false;
    public $newNpsn, $newNama, $newAlamat, $newKelompok, $newStatus;
    public $newProvinsi, $newKabupaten, $newKecamatan;
    public $newAkreditasi, $newKodePos, $newTelepon, $newFax, $newEmail, $newWeb;
    public $newJumlahSiswa, $newKurikulum, $newNamaKepsek, $newHpKepsek, $newEmailKepsek;

    public function openDetailModal($npsn): void
    {
        $this->selectedSekolah = Sekolah::with(['kecamatan.kabupaten.propinsi'])->find($npsn);
        $this->isDetailModalOpen = true;
    }

    public function closeDetailModal(): void
    {
        $this->isDetailModalOpen = false;
        $this->selectedSekolah = null;
    }

    public function openAddSekolahModal()
    {
        $this->reset([
            'newNpsn', 'newNama', 'newAlamat', 'newKelompok', 'newStatus', 
            'newProvinsi', 'newKabupaten', 'newKecamatan',
            'newAkreditasi', 'newKodePos', 'newTelepon', 'newFax', 'newEmail', 'newWeb',
            'newJumlahSiswa', 'newKurikulum', 'newNamaKepsek', 'newHpKepsek', 'newEmailKepsek'
        ]);
        $this->isAddSekolahModalOpen = true;
    }

    public function closeAddSekolahModal()
    {
        $this->isAddSekolahModalOpen = false;
    }

    public function saveSekolah()
    {
        $this->validate([
            'newNpsn' => 'required|unique:sekolah,npsn|max:8',
            'newNama' => 'required|max:100',
            'newKecamatan' => 'required',
            'newAlamat' => 'required',
            'newStatus' => 'required',
            'newKelompok' => 'required',
        ]);

        Sekolah::create([
            'npsn' => $this->newNpsn,
            'nama_sekolah' => strtoupper($this->newNama),
            'alamat_sekolah' => $this->newAlamat,
            'kode_kec' => $this->newKecamatan,
            'status' => $this->newStatus,
            'kelompok' => $this->newKelompok,
            'akreditasi' => $this->newAkreditasi,
            'kode_pos' => $this->newKodePos,
            'telepon' => $this->newTelepon,
            'fax' => $this->newFax,
            'email' => $this->newEmail,
            'web' => $this->newWeb,
            'jumlah_siswa' => $this->newJumlahSiswa ? (int) $this->newJumlahSiswa : null,
            'kurikulum' => $this->newKurikulum,
            'nama_kepsek' => $this->newNamaKepsek,
            'hp_kepsek' => $this->newHpKepsek,
            'email_kepsek' => $this->newEmailKepsek,
            'kkn' => 'T'
        ]);

        $this->closeAddSekolahModal();
        session()->flash('message', 'Data sekolah berhasil ditambahkan.');
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }
    public function updatingFilterKelompok(): void { $this->resetPage(); }
    
    public function updatedFilterProvinsi(): void { 
        $this->filterKabupaten = ''; 
        $this->filterKecamatan = ''; 
        $this->resetPage(); 
    }
    public function updatedFilterKabupaten(): void { 
        $this->filterKecamatan = ''; 
        $this->resetPage(); 
    }
    public function updatingFilterKecamatan(): void { $this->resetPage(); }

    public function render()
    {
        $sekolahList = Sekolah::with(['kecamatan.kabupaten.propinsi'])
            ->when($this->search, fn($q) => $q->where('nama_sekolah', 'like', "%{$this->search}%")
                                                ->orWhere('npsn', 'like', "%{$this->search}%")
                                                ->orWhere('alamat_sekolah', 'like', "%{$this->search}%"))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterKelompok, fn($q) => $q->where('kelompok', $this->filterKelompok))
            ->when($this->filterProvinsi, function($q) {
                $q->whereHas('kecamatan.kabupaten', function($q) {
                    $q->where('kode_prop', $this->filterProvinsi);
                });
            })
            ->when($this->filterKabupaten, function($q) {
                $q->whereHas('kecamatan', function($q) {
                    $q->where('kode_kab', $this->filterKabupaten);
                });
            })
            ->when($this->filterKecamatan, fn($q) => $q->where('kode_kec', $this->filterKecamatan))
            ->orderBy('nama_sekolah')
            ->paginate(10);

        $statusList = Sekolah::select('status')->distinct()->whereNotNull('status')->pluck('status');
        $kelompokList = Sekolah::select('kelompok')->distinct()->whereNotNull('kelompok')->pluck('kelompok');
        
        $provinsiList = Propinsi::orderBy('nama_prop')->get();
        $kabupatenList = $this->filterProvinsi ? Kabupaten::where('kode_prop', $this->filterProvinsi)->orderBy('nama_kab')->get() : [];
        $kecamatanList = $this->filterKabupaten ? Kecamatan::where('kode_kab', $this->filterKabupaten)->orderBy('nama_kec')->get() : [];

        $formKabupatenList = $this->newProvinsi ? Kabupaten::where('kode_prop', $this->newProvinsi)->orderBy('nama_kab')->get() : [];
        $formKecamatanList = $this->newKabupaten ? Kecamatan::where('kode_kab', $this->newKabupaten)->orderBy('nama_kec')->get() : [];

        return view('livewire.master-data.sekolah-index', compact('sekolahList', 'statusList', 'kelompokList', 'provinsiList', 'kabupatenList', 'kecamatanList', 'formKabupatenList', 'formKecamatanList'))
            ->layout('layouts.app', ['title' => 'Master Sekolah']);
    }
}
