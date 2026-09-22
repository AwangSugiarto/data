<?php

namespace App\Livewire\MasterData;

use App\Models\Propinsi;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use Livewire\Component;
use Livewire\WithPagination;

class WilayahIndex extends Component
{
    use WithPagination;

    public string $activeTab = 'propinsi';
    
    // Breadcrumbs/Selection
    public ?string $selectedPropinsi = null;
    public ?string $selectedKabupaten = null;
    public ?string $selectedKecamatan = null;

    // Search for current tab
    public string $search = '';

    // Add Modal State
    public bool $isAddModalOpen = false;
    public string $newWilayahName = '';
    public string $newWilayahKode = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingActiveTab(): void { $this->resetPage(); $this->search = ''; }

    public function openAddModal()
    {
        $this->newWilayahName = '';
        $this->newWilayahKode = '';
        $this->isAddModalOpen = true;
    }

    public function closeAddModal()
    {
        $this->isAddModalOpen = false;
        $this->newWilayahName = '';
        $this->newWilayahKode = '';
    }

    public function saveWilayah()
    {
        $this->validate([
            'newWilayahKode' => 'required|numeric',
            'newWilayahName' => 'required|min:3'
        ]);

        $name = strtoupper(trim($this->newWilayahName));
        $code = trim($this->newWilayahKode);

        if ($this->activeTab === 'propinsi') {
            // Cek duplikat
            if (Propinsi::where('kode_prop', $code)->exists()) {
                $this->addError('newWilayahKode', 'Kode Provinsi sudah digunakan.');
                return;
            }

            Propinsi::create([
                'kode_prop' => $code,
                'nama_prop' => $name,
                'kab' => 0, 'kec' => 0, 'kel' => 0
            ]);
        } 
        elseif ($this->activeTab === 'kabupaten') {
            if (Kabupaten::where('kode_kab', $code)->exists()) {
                $this->addError('newWilayahKode', 'Kode Kabupaten sudah digunakan.');
                return;
            }

            Kabupaten::create([
                'kode_kab' => $code,
                'kode_prop' => $this->selectedPropinsi,
                'nama_kab' => $name,
                'kec' => 0, 'kel' => 0
            ]);
        }
        elseif ($this->activeTab === 'kecamatan') {
            if (Kecamatan::where('kode_kec', $code)->exists()) {
                $this->addError('newWilayahKode', 'Kode Kecamatan sudah digunakan.');
                return;
            }

            Kecamatan::create([
                'kode_kec' => $code,
                'kode_prop' => $this->selectedPropinsi,
                'kode_kab' => $this->selectedKabupaten,
                'nama_kec' => $name,
                'kel' => 0
            ]);
        }

        $this->closeAddModal();
        session()->flash('message', 'Data wilayah berhasil ditambahkan.');
    }

    public function selectPropinsi($kode)
    {
        $this->selectedPropinsi = $kode;
        $this->activeTab = 'kabupaten';
        $this->resetPage();
        $this->search = '';
    }

    public function selectKabupaten($kode)
    {
        $this->selectedKabupaten = $kode;
        $this->activeTab = 'kecamatan';
        $this->resetPage();
        $this->search = '';
    }

    public function selectKecamatan($kode)
    {
        $this->selectedKecamatan = $kode;
        $this->activeTab = 'kelurahan';
        $this->resetPage();
        $this->search = '';
    }

    public function goBackTo($tab)
    {
        if ($tab === 'propinsi') {
            $this->selectedPropinsi = null;
            $this->selectedKabupaten = null;
            $this->selectedKecamatan = null;
        } elseif ($tab === 'kabupaten') {
            $this->selectedKabupaten = null;
            $this->selectedKecamatan = null;
        } elseif ($tab === 'kecamatan') {
            $this->selectedKecamatan = null;
        }
        $this->activeTab = $tab;
        $this->resetPage();
        $this->search = '';
    }

    public function render()
    {
        $data = null;
        $breadcrumbs = [];

        if ($this->activeTab === 'propinsi') {
            $data = Propinsi::when($this->search, fn($q) => $q->where('nama_prop', 'like', "%{$this->search}%")
                            ->orWhere('kode_prop', 'like', "%{$this->search}%"))
                ->orderBy('nama_prop')
                ->paginate(10);
        } 
        elseif ($this->activeTab === 'kabupaten') {
            $prop = Propinsi::find($this->selectedPropinsi);
            $breadcrumbs[] = ['label' => 'Provinsi: ' . ($prop->nama_prop ?? ''), 'tab' => 'propinsi'];

            $data = Kabupaten::where('kode_prop', $this->selectedPropinsi)
                ->when($this->search, fn($q) => $q->where('nama_kab', 'like', "%{$this->search}%")
                            ->orWhere('kode_kab', 'like', "%{$this->search}%"))
                ->orderBy('nama_kab')
                ->paginate(10);
        }
        elseif ($this->activeTab === 'kecamatan') {
            $prop = Propinsi::find($this->selectedPropinsi);
            $kab = Kabupaten::find($this->selectedKabupaten);
            $breadcrumbs[] = ['label' => 'Provinsi: ' . ($prop->nama_prop ?? ''), 'tab' => 'propinsi'];
            $breadcrumbs[] = ['label' => 'Kab: ' . ($kab->nama_kab ?? ''), 'tab' => 'kabupaten'];

            $data = Kecamatan::where('kode_kab', $this->selectedKabupaten)
                ->when($this->search, fn($q) => $q->where('nama_kec', 'like', "%{$this->search}%")
                            ->orWhere('kode_kec', 'like', "%{$this->search}%"))
                ->orderBy('nama_kec')
                ->paginate(10);
        }
        elseif ($this->activeTab === 'kelurahan') {
            $prop = Propinsi::find($this->selectedPropinsi);
            $kab = Kabupaten::find($this->selectedKabupaten);
            $kec = Kecamatan::find($this->selectedKecamatan);
            $breadcrumbs[] = ['label' => 'Provinsi: ' . ($prop->nama_prop ?? ''), 'tab' => 'propinsi'];
            $breadcrumbs[] = ['label' => 'Kab: ' . ($kab->nama_kab ?? ''), 'tab' => 'kabupaten'];
            $breadcrumbs[] = ['label' => 'Kec: ' . ($kec->nama_kec ?? ''), 'tab' => 'kecamatan'];

            $data = Kelurahan::where('kode_kec', $this->selectedKecamatan)
                ->when($this->search, fn($q) => $q->where('nama_kel', 'like', "%{$this->search}%")
                            ->orWhere('kode_kel', 'like', "%{$this->search}%"))
                ->orderBy('nama_kel')
                ->paginate(10);
        }

        return view('livewire.master-data.wilayah-index', compact('data', 'breadcrumbs'))
            ->layout('layouts.app', ['title' => 'Master Wilayah (Hirarkis)']);
    }
}
