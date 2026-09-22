<div class="py-6 px-4 max-w-6xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Import Data Calon Mahasiswa (Lulus)</h1>
        <p class="text-sm text-gray-500 mt-1">Unggah file Excel/CSV pendaftar yang lulus seleksi.</p>
    </div>

    @if (session()->has('success'))
        <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form wire:submit="import" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Akademik</label>
                    <select wire:model="tahunId" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Pilih Tahun...</option>
                        @foreach($tahunList as $t)
                            <option value="{{ $t->id }}">{{ $t->tahun }}</option>
                        @endforeach
                    </select>
                    @error('tahunId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jalur Seleksi</label>
                    <select wire:model="jalurId" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Semua Jalur / Tidak Spesifik</option>
                        @foreach($jalurList as $j)
                            <option value="{{ $j->id }}">{{ $j->nama_jalur }}</option>
                        @endforeach
                    </select>
                    @error('jalurId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">File Upload (Excel/CSV)</label>
                <input type="file" wire:model="file" class="block w-full text-sm text-gray-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-md file:border-0
                    file:text-sm file:font-semibold
                    file:bg-blue-50 file:text-blue-700
                    hover:file:bg-blue-100" />
                @error('file') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg shadow-sm" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="import">Upload & Import Data</span>
                    <span wire:loading wire:target="import">Memproses...</span>
                </button>
            </div>
        </form>
        
        <div class="mt-8 pt-6 border-t border-gray-100">
            <h3 class="text-sm font-bold text-gray-800 mb-2">Format Excel yang didukung:</h3>
            <p class="text-xs text-gray-500 mb-2">Pastikan baris pertama adalah header dengan nama kolom berikut (huruf kecil):</p>
            <ul class="text-xs text-gray-600 list-disc list-inside bg-gray-50 p-4 rounded-lg">
                <li><code>nomor_registrasi</code> atau <code>nomor_tes</code> (Wajib)</li>
                <li><code>kode_prodi</code> atau <code>program_studi</code> (Wajib jika pendaftar belum ada di database)</li>
                <li><code>nama</code> (Opsional jika pendaftar sudah ada)</li>
                <li><code>kabupaten_kota</code> (Opsional)</li>
                <li><code>ukt</code> (Opsional)</li>
                <li><code>nim</code> (Opsional, jika sudah memiliki NIM)</li>
            </ul>
        </div>
    </div>
</div>
