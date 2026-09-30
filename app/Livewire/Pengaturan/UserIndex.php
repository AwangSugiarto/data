<?php

namespace App\Livewire\Pengaturan;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UserIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public bool $editMode = false;
    public ?int $editId = null;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        if (!auth()->user()->hasAnyRole(['super-admin', 'admin', 'admin-pmb'])) {
            session()->flash('error', 'Akses ditolak. Anda tidak memiliki izin untuk menambah pengguna.');
            return;
        }

        $this->reset(['editId', 'name', 'email', 'password', 'role']);
        $this->editMode = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        if (!auth()->user()->hasAnyRole(['super-admin', 'admin', 'admin-pmb'])) {
            session()->flash('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengedit pengguna.');
            return;
        }

        $user = User::findOrFail($id);
        $this->editId = $user->id;
        $this->name   = $user->name;
        $this->email  = $user->email;
        $this->role   = $user->roles->first()?->name ?? '';
        $this->password = ''; // Kosongkan password saat edit
        
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        \Illuminate\Support\Facades\Log::info('UserIndex save() dipanggil', [
            'name' => $this->name, 'email' => $this->email, 'role' => $this->role, 'editMode' => $this->editMode
        ]);

        if (!auth()->user()->hasAnyRole(['super-admin', 'admin', 'admin-pmb'])) {
            session()->flash('error', 'Akses ditolak. Anda tidak memiliki izin untuk menyimpan pengguna.');
            return;
        }

        $rules = [
            'name'  => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->editId)],
            'role'  => 'required|string|exists:roles,name',
        ];

        if (!$this->editMode) {
            $rules['password'] = 'required|string|min:8';
        } else {
            $rules['password'] = 'nullable|string|min:8';
        }

        $this->validate($rules);

        if ($this->editMode && $this->editId) {
            $user = User::findOrFail($this->editId);
            $user->name = $this->name;
            $user->email = $this->email;
            if (!empty($this->password)) {
                $user->password = Hash::make($this->password);
            }
            $user->save();
            $user->syncRoles([$this->role]);
            
            session()->flash('success', 'Pengguna berhasil diperbarui.');
        } else {
            $user = User::create([
                'name'     => $this->name,
                'email'    => $this->email,
                'password' => Hash::make($this->password),
            ]);
            $user->assignRole($this->role);

            session()->flash('success', 'Pengguna berhasil ditambahkan.');
        }

        $this->showModal = false;
    }

    public function deleteUser(int $id): void
    {
        if (!auth()->user()->hasAnyRole(['super-admin', 'admin', 'admin-pmb'])) {
            session()->flash('error', 'Akses ditolak. Anda tidak memiliki izin untuk menghapus pengguna.');
            return;
        }

        if ($id === auth()->id()) {
            session()->flash('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
            return;
        }

        $user = User::findOrFail($id);
        $user->delete();
        session()->flash('success', 'Pengguna berhasil dihapus.');
    }

    public function render()
    {
        $users = User::with('roles')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(10);
            
        $roles = Role::orderBy('name')->get();

        return view('livewire.pengaturan.user-index', compact('users', 'roles'))
            ->layout('layouts.app', ['title' => 'Manajemen Pengguna']);
    }
}
