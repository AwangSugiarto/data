<div class="max-w-4xl mx-auto pb-12">
    <!-- Progress Bar -->
    <div class="mb-8 relative">
        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded-full bg-gray-200">
            <div style="width: {{ ($step / 4) * 100 }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-purple-600 transition-all duration-500"></div>
        </div>
        <div class="flex justify-between text-xs font-medium text-gray-500 px-1">
            <span class="{{ $step >= 1 ? 'text-purple-600' : '' }}">1. Upload</span>
            <span class="{{ $step >= 2 ? 'text-purple-600' : '' }}">2. Mapping Kolom</span>
            <span class="{{ $step >= 3 ? 'text-purple-600' : '' }}">3. Konfirmasi</span>
            <span class="{{ $step == 4 ? 'text-purple-600' : '' }}">4. Hasil</span>
        </div>
    </div>

    <!-- Error/Success Messages -->
    @if (session()->has('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg shadow-sm" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    {{-- STEP 1: UPLOAD --}}
    @if($step === 1)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-10 text-center">
        <div class="mx-auto w-16 h-16 bg-purple-50 text-purple-600 rounded-full flex items-center justify-center mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
        </div>
        <h2 class="text-xl font-semibold text-gray-800 mb-2">Upload File Excel (Data Mahasiswa Aktif)</h2>
        <p class="text-gray-500 text-sm mb-4">Format yang didukung: .xlsx, .xls, .csv. Pastikan file berisi data mahasiswa yang akan diupdate.</p>
        
        <div class="mb-8">
            <a href="{{ asset('template_import_mahasiswa_aktif.csv') }}" download class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-green-700 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download Template CSV
            </a>
        </div>

        <form wire:submit.prevent="uploadFile">
            <div class="flex items-center justify-center w-full mb-6">
                <label for="dropzone-file-aktif" class="flex flex-col items-center justify-center w-full h-48 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <svg class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Klik untuk memilih file</span> atau seret file ke sini</p>
                        <p class="text-xs text-gray-400">Maks. 10MB</p>
                    </div>
                    <input id="dropzone-file-aktif" type="file" wire:model="file" class="hidden" accept=".xlsx,.xls,.csv" />
                </label>
            </div>
            
            @if($file)
                <div class="flex items-center justify-between p-3 bg-purple-50 text-purple-700 rounded-lg text-sm mb-6 text-left border border-purple-100">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="font-medium truncate max-w-xs">{{ $file->getClientOriginalName() }}</span>
                    </div>
                </div>
            @endif

            @error('file') <span class="text-red-500 text-sm block mb-4">{{ $message }}</span> @enderror

            <button type="submit" class="w-full bg-purple-700 hover:bg-purple-800 text-white font-medium py-3 px-4 rounded-xl transition flex items-center justify-center gap-2" wire:loading.attr="disabled">
                <span wire:loading wire:target="uploadFile" class="animate-spin">⏳</span>
                Upload & Lanjut →
            </button>
        </form>
    </div>
    @endif

    {{-- STEP 2: MAPPING KOLOM --}}
    @if($step === 2)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex justify-between items-center mb-4 border-b pb-3">
            <h2 class="text-lg font-semibold text-gray-800">Mapping Kolom Data Mahasiswa Aktif</h2>
            <span class="text-xs bg-purple-100 text-purple-800 py-1 px-2 rounded font-medium">{{ number_format($totalRows) }} baris terdeteksi</span>
        </div>

        <div class="mb-6 overflow-x-auto border border-gray-200 rounded-lg max-h-[500px] overflow-y-auto">
            <table class="w-full text-xs text-left whitespace-nowrap">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-3 py-2 text-center font-medium border-b border-r w-12 text-gray-500">No</th>
                        @foreach(array_slice($headers, 0, 8) as $h)
                        <th class="px-3 py-2 font-medium border-b border-r">{{ $h }}</th>
                        @endforeach
                        @if(count($headers) > 8)
                        <th class="px-3 py-2 font-medium border-b text-gray-400 italic">... ({{ count($headers) - 8 }} kolom lain)</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach(array_slice($previewRows, 0, 100) as $index => $row)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-3 py-2 border-r text-center text-gray-500">{{ $index + 1 }}</td>
                        @foreach(array_slice($row, 0, 8) as $cell)
                        <td class="px-3 py-2 border-r truncate max-w-[150px]" title="{{ $cell }}">{{ $cell }}</td>
                        @endforeach
                        @if(count($headers) > 8)
                        <td class="px-3 py-2 text-gray-400">...</td>
                        @endif
                    </tr>
                    @endforeach
                    @if(count($previewRows) > 0)
                    <tr class="bg-gray-100 font-bold border-t-2 border-gray-200">
                        <td class="px-3 py-2 text-center text-gray-700 uppercase tracking-wider border-r">Total</td>
                        @foreach(array_slice($headers, 0, 8) as $colIndex => $h)
                            @php
                                $colTotal = 0;
                                $isNumeric = true;
                                foreach($previewRows as $r) {
                                    $val = isset($r[$colIndex]) ? trim((string)$r[$colIndex]) : '';
                                    if($val === '') continue;
                                    
                                    $cleanVal = str_replace(['.', ','], '', $val);
                                    if(!is_numeric($cleanVal)) {
                                        $isNumeric = false;
                                        break;
                                    }
                                    $colTotal += (float) $cleanVal;
                                }
                                
                                if ($isNumeric && $colTotal > 0) {
                                    if (preg_match('/(tahun|kode|jalur|nim|nik|nomor|no)/i', $h)) {
                                        $isNumeric = false;
                                    }
                                }
                            @endphp
                            <td class="px-3 py-2 text-gray-800 border-r">{{ $isNumeric ? number_format($colTotal, 0, ',', '.') : '-' }}</td>
                        @endforeach
                        @if(count($headers) > 8)
                        <td class="px-3 py-2 text-gray-400"></td>
                        @endif
                    </tr>
                    @endif
                </tbody>
            </table>
            @if(count($previewRows) > 100)
                <div class="px-3 py-2 text-xs text-center text-gray-500 bg-gray-50 border-t border-gray-200 sticky bottom-0">
                    Menampilkan 100 dari {{ number_format($totalRows) }} baris data
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Kolom Nomor Tes / Registrasi <span class="text-red-500">*</span></label>
                    <p class="text-xs text-gray-500 mb-2">Digunakan untuk mencari pendaftar</p>
                    <select wire:model="col_nomor_tes" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                        <option value="">-- Pilih Kolom --</option>
                        @foreach($headers as $h)
                        <option value="{{ $h }}">{{ $h }}</option>
                        @endforeach
                    </select>
                    @error('col_nomor_tes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Kolom NIM <span class="text-red-500">*</span></label>
                    <p class="text-xs text-gray-500 mb-2">Nomor Induk Mahasiswa untuk Master Data</p>
                    <select wire:model="col_nim" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                        <option value="">-- Pilih Kolom --</option>
                        @foreach($headers as $h)
                        <option value="{{ $h }}">{{ $h }}</option>
                        @endforeach
                    </select>
                    @error('col_nim') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <hr class="border-gray-100 border-dashed">

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Kolom Status Mahasiswa <span class="text-red-500">*</span></label>
                    <p class="text-xs text-gray-500 mb-2">Contoh: Registrasi, Alumni</p>
                    <select wire:model="col_status" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                        <option value="">-- Pilih Kolom --</option>
                        @foreach($headers as $h)
                        <option value="{{ $h }}">{{ $h }}</option>
                        @endforeach
                    </select>
                    @error('col_status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Kolom Nominal UKT <span class="text-red-500">*</span></label>
                    <p class="text-xs text-gray-500 mb-2">Sistem akan mencocokkan nominal ini dengan Kelompok UKT</p>
                    <select wire:model="col_ukt_nominal" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                        <option value="">-- Pilih Kolom --</option>
                        @foreach($headers as $h)
                        <option value="{{ $h }}">{{ $h }}</option>
                        @endforeach
                    </select>
                    @error('col_ukt_nominal') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <hr class="border-gray-100 border-dashed">

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Kolom Tanggal Lulus (Hanya Alumni)</label>
                    <p class="text-xs text-gray-500 mb-2">Opsional: Format disarankan YYYY-MM-DD</p>
                    <select wire:model="col_tanggal_lulus" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                        <option value="">-- Pilih Kolom (Opsional) --</option>
                        @foreach($headers as $h)
                        <option value="{{ $h }}">{{ $h }}</option>
                        @endforeach
                    </select>
                    @error('col_tanggal_lulus') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Kolom Tahun Akademik Lulus (Hanya Alumni)</label>
                    <p class="text-xs text-gray-500 mb-2">Opsional: Contoh "20261"</p>
                    <select wire:model="col_tahun_lulus" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                        <option value="">-- Pilih Kolom (Opsional) --</option>
                        @foreach($headers as $h)
                        <option value="{{ $h }}">{{ $h }}</option>
                        @endforeach
                    </select>
                    @error('col_tahun_lulus') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="flex justify-between mt-6 border-t border-gray-100 pt-6">
            <button wire:click="goBack" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">← Kembali</button>
            <button wire:click="goToPreview" class="bg-purple-700 hover:bg-purple-800 text-white px-6 py-2 rounded-lg text-sm font-medium transition">Lanjut →</button>
        </div>
    </div>
    @endif

    {{-- STEP 3: KONFIRMASI --}}
    @if($step === 3)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Konfirmasi Import Data Mahasiswa Aktif</h2>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-500 mb-1">File</p>
                <p class="text-sm font-semibold text-gray-800">{{ $uploadedName }}</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-500 mb-1">Total Baris di File</p>
                <p class="text-sm font-semibold text-gray-800">{{ number_format($totalRows) }} baris</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg col-span-2">
                <p class="text-xs text-gray-500 mb-1">Key Pencarian</p>
                <p class="text-sm font-semibold text-gray-800">Nomor Tes ({{ $col_nomor_tes }})</p>
            </div>
        </div>

        <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg mb-6 flex gap-3 items-start">
            <div class="text-amber-500 mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-amber-800 mb-1">Perhatian</p>
                <ul class="text-sm text-amber-700 space-y-1 list-disc list-inside">
                    <li>Pendaftar dengan <strong>Nomor Tes</strong> yang cocok akan diupdate statusnya.</li>
                    <li>Sistem akan meng-insert / update NIM pada tabel <strong>Master Mahasiswa</strong>.</li>
                    <li>Nominal UKT akan dicocokkan dengan range UKT di Master Data untuk menentukan Kelompok UKT.</li>
                    <li>Jika Nomor Tes tidak ditemukan di tabel pendaftar, data tersebut akan dilewati (skipped).</li>
                </ul>
            </div>
        </div>

        <div class="flex justify-between">
            <button wire:click="goBack" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">← Kembali</button>
            <button wire:click="runImport" wire:loading.attr="disabled"
                class="bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white px-6 py-2 rounded-lg text-sm font-bold transition flex items-center gap-2">
                <span wire:loading wire:target="runImport" class="animate-spin text-lg">⏳</span>
                🚀 Mulai Update Data
            </button>
        </div>
    </div>
    @endif

    {{-- STEP 4: HASIL --}}
    @if($step === 4 && $importResult)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="text-center mb-6">
            <div class="text-5xl mb-3">
                {{ $importResult['errors'] > 0 ? '⚠️' : '🎉' }}
            </div>
            <h2 class="text-xl font-bold text-gray-900">Update Selesai!</h2>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="text-center p-4 bg-green-50 rounded-xl border border-green-200">
                <p class="text-3xl font-bold text-green-700">{{ number_format($importResult['updated']) }}</p>
                <p class="text-sm text-green-600 mt-1">Mahasiswa berhasil di-update / di-insert NIM</p>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-xl border border-gray-200">
                <p class="text-3xl font-bold text-gray-600">{{ number_format($importResult['skipped']) }}</p>
                <p class="text-sm text-gray-500 mt-1">Baris dilewati (Nomor Tes tidak ditemukan)</p>
            </div>
        </div>

        @if($importResult['errors'] > 0)
        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg mb-6">
            <p class="text-sm font-semibold text-yellow-800">⚠️ Terdapat {{ $importResult['errors'] }} error selama pemrosesan.</p>
        </div>
        @endif

        <div class="flex justify-center gap-3">
            <button wire:click="restart" class="bg-purple-700 hover:bg-purple-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Import File Lain</button>
        </div>
    </div>
    @endif
</div>
