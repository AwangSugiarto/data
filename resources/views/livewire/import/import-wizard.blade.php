<div class="py-8 px-4 max-w-5xl mx-auto">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Import Data PMB dari Excel</h1>
        <p class="text-sm text-gray-500 mt-1">Wizard 4 langkah untuk memuat data dari file Excel ke database</p>
    </div>

    {{-- Flash messages --}}
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    {{-- Stepper --}}
    <div class="flex items-center mb-8">
        @foreach([1=>'Upload File', 2=>'Konfigurasi', 3=>'Mapping Kolom', 4=>'Konfirmasi'] as $s => $label)
        <div class="flex items-center {{ !$loop->last ? 'flex-1' : '' }}">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold border-2 transition
                    {{ $step > $s ? 'bg-green-500 border-green-500 text-white' : ($step === $s ? 'bg-blue-700 border-blue-700 text-white' : 'bg-white border-gray-300 text-gray-400') }}">
                    {{ $step > $s ? '✓' : $s }}
                </div>
                <span class="text-sm font-medium {{ $step === $s ? 'text-blue-700' : ($step > $s ? 'text-green-600' : 'text-gray-400') }} hidden sm:inline">
                    {{ $label }}
                </span>
            </div>
            @if(!$loop->last)
            <div class="flex-1 h-0.5 mx-3 {{ $step > $s ? 'bg-green-400' : 'bg-gray-200' }}"></div>
            @endif
        </div>
        @endforeach
    </div>

    {{-- STEP 1: UPLOAD --}}
    @if($step === 1)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Pilih File Excel</h2>

        <div class="border-2 border-dashed border-gray-300 rounded-xl p-10 text-center hover:border-blue-400 transition"
            x-data="{ dragging: false }"
            @dragover.prevent="dragging = true"
            @dragleave="dragging = false"
            @drop.prevent="dragging = false">
            <div class="text-4xl mb-3">📂</div>
            <p class="text-gray-600 mb-2">Seret file ke sini atau</p>
            <label class="cursor-pointer bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                Pilih File
                <input type="file" wire:model="file" accept=".xlsx,.xls,.csv" class="hidden">
            </label>
            <p class="text-xs text-gray-400 mt-2">Format: .xlsx, .xls, .csv — Maks 10 MB</p>

            @if($file)
                <div class="mt-4 inline-flex items-center gap-2 bg-blue-50 px-3 py-2 rounded-lg">
                    <span class="text-sm text-blue-800 font-medium">{{ $file->getClientOriginalName() }}</span>
                    <span class="text-xs text-blue-500">({{ number_format($file->getSize()/1024, 1) }} KB)</span>
                </div>
            @endif
        </div>

        @error('file') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror

        <div class="mt-6 p-5 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-sm font-bold text-blue-900 mb-2">💡 Panduan & Template Import</h3>
                    <ol class="list-decimal list-inside text-sm text-blue-800 space-y-1 mb-4">
                        <li>Unduh salah satu template CSV di bawah ini.</li>
                        <li>Isi data pendaftar menggunakan Microsoft Excel atau Google Sheets.</li>
                        <li>Gunakan <strong>Kode Prodi 5 Digit</strong> standar PDDIKTI (misal: 57201, 86208) agar data sangat presisi. Sistem akan melacak nama Prodi & Fakultas secara otomatis.</li>
                        <li>Unggah file CSV/Excel yang telah diisi ke area di atas.</li>
                        <li>Lanjutkan ke tahap Mapping Kolom untuk mencocokkan judul kolom.</li>
                    </ol>
                    <div class="flex gap-3 mt-4">
                        <a href="{{ asset('templates/Template_Import_Format_Long.csv') }}" download class="inline-flex items-center justify-center px-4 py-2 bg-white border border-blue-300 rounded-md text-sm font-medium text-blue-700 hover:bg-blue-50 transition shadow-sm">
                            📄 Download Template LONG
                        </a>
                        <a href="{{ asset('templates/Template_Import_Format_Wide.csv') }}" download class="inline-flex items-center justify-center px-4 py-2 bg-white border border-blue-300 rounded-md text-sm font-medium text-blue-700 hover:bg-blue-50 transition shadow-sm">
                            📄 Download Template WIDE
                        </a>
                    </div>
                </div>
                <div class="hidden md:block bg-white/60 p-3 rounded-lg text-xs text-blue-800 border border-blue-100 max-w-xs">
                    <strong>Pilih Format Mana?</strong>
                    <ul class="mt-2 space-y-1 list-disc list-inside">
                        <li><strong>Format LONG:</strong> 1 baris = 1 Prodi & 1 Jalur. Sangat disarankan karena paling stabil.</li>
                        <li><strong>Format WIDE:</strong> 1 baris = 1 Prodi, tapi memanjang ke kanan (kolom untuk tiap jalur). Cocok jika data mentah Anda sudah seperti ini.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="flex justify-end mt-6">
            <button wire:click="uploadFile" wire:loading.attr="disabled"
                class="bg-blue-700 hover:bg-blue-800 disabled:opacity-50 text-white px-6 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                <span wire:loading wire:target="uploadFile" class="animate-spin">⏳</span>
                Lanjut →
            </button>
        </div>
    </div>
    @endif

    {{-- STEP 2: KONFIGURASI --}}
    @if($step === 2)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Konfigurasi Import</h2>

        {{-- Info file --}}
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3">
            <span class="text-2xl">📄</span>
            <div>
                <p class="text-sm font-semibold text-green-800">{{ $uploadedName }}</p>
                <p class="text-xs text-green-600">{{ $totalRows }} baris data terdeteksi, {{ count($headers) }} kolom</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Akademik & Semester <span class="text-red-500">*</span></label>
                <select wire:model="tahun" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">-- Pilih Tahun Akademik --</option>
                    @foreach($tahunOptions as $t => $label)
                    <option value="{{ $t }}">{{ $label }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">Pilih 5-digit kode (Contoh: 20261)</p>
                @error('tahun') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Format Data <span class="text-red-500">*</span></label>
                <div class="space-y-2 mt-1">
                    <label class="flex items-start gap-2 p-3 border rounded-lg cursor-pointer {{ $format === 'long' ? 'border-blue-400 bg-blue-50' : 'border-gray-200' }}">
                        <input type="radio" wire:model="format" value="long" class="mt-0.5">
                        <div>
                            <p class="text-sm font-medium text-gray-800">Long (1 baris = 1 jalur)</p>
                            <p class="text-xs text-gray-500">Prodi | Jalur | Kuota | ...</p>
                        </div>
                    </label>
                    <label class="flex items-start gap-2 p-3 border rounded-lg cursor-pointer {{ $format === 'wide' ? 'border-blue-400 bg-blue-50' : 'border-gray-200' }}">
                        <input type="radio" wire:model="format" value="wide" class="mt-0.5">
                        <div>
                            <p class="text-sm font-medium text-gray-800">Wide (1 baris = semua jalur)</p>
                            <p class="text-xs text-gray-500">Prodi | Kuota_SNBP | Daftar_SNBP | ...</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        {{-- Preview Tabel --}}
        <div class="mb-4">
            <p class="text-sm font-medium text-gray-700 mb-2">Preview Data (Maksimal 100 baris):</p>
            <div class="overflow-x-auto rounded-lg border border-gray-200 max-h-[500px] overflow-y-auto">
                <table class="text-xs w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-center font-medium text-gray-600 whitespace-nowrap w-12 border-r">No</th>
                            @foreach($headers as $h)
                            <th class="px-3 py-2 text-left font-medium text-gray-600 whitespace-nowrap">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($previewRows as $index => $row)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-3 py-1.5 text-center text-gray-500 font-medium whitespace-nowrap border-r bg-gray-50">{{ $index + 1 }}</td>
                            @foreach($row as $cell)
                            <td class="px-3 py-1.5 text-gray-700 whitespace-nowrap">{{ $cell }}</td>
                            @endforeach
                        </tr>
                        @endforeach
                        @if(count($previewRows) > 0)
                        <tr class="bg-gray-100 font-bold border-t-2 border-gray-200">
                            <td class="px-3 py-2 text-center text-gray-700 uppercase tracking-wider border-r">Total</td>
                            @foreach($headers as $colIndex => $h)
                                @php
                                    $colTotal = 0;
                                    $isNumeric = true;
                                    foreach($previewRows as $r) {
                                        $val = isset($r[$colIndex]) ? trim((string)$r[$colIndex]) : '';
                                        // Skip empty strings
                                        if($val === '') continue;
                                        
                                        // Check if it's a number (allow dots/commas)
                                        $cleanVal = str_replace(['.', ','], '', $val);
                                        if(!is_numeric($cleanVal)) {
                                            $isNumeric = false;
                                            break;
                                        }
                                        $colTotal += (float) $cleanVal;
                                    }
                                    
                                    // Don't sum up things that look like codes or years (e.g., 20261 or 74234)
                                    // If all values are exactly 5 digits, it's probably a code
                                    if ($isNumeric && $colTotal > 0) {
                                        $allFiveDigits = true;
                                        foreach($previewRows as $r) {
                                            $val = isset($r[$colIndex]) ? trim((string)$r[$colIndex]) : '';
                                            if ($val !== '' && strlen($val) >= 4 && $val > 2000) {
                                                // likely a year or code, but let's just use a heuristic: if header contains 'tahun' or 'kode', don't sum
                                            }
                                        }
                                        if (stripos($h, 'tahun') !== false || stripos($h, 'kode') !== false || stripos($h, 'jalur') !== false) {
                                            $isNumeric = false;
                                        }
                                    }
                                @endphp
                                <td class="px-3 py-2 text-gray-800 whitespace-nowrap">{{ $isNumeric ? number_format($colTotal, 0, ',', '.') : '-' }}</td>
                            @endforeach
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-between items-center mt-6">
            <button wire:click="goBack" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">← Kembali</button>
            <div class="flex items-center gap-4">
                @error('tahun') <span class="text-sm font-medium text-red-600">Peringatan: {{ $message }}</span> @enderror
                <button wire:click="goToMapping" class="bg-blue-700 hover:bg-blue-800 text-white px-6 py-2 rounded-lg text-sm font-medium transition">Lanjut →</button>
            </div>
        </div>
    </div>
    @endif

    {{-- STEP 3: MAPPING KOLOM --}}
    @if($step === 3)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-1">Mapping Kolom</h2>
        <p class="text-sm text-gray-500 mb-5">Hubungkan kolom di file Excel Anda ke field yang sesuai</p>

        @if($format === 'long')
        {{-- ── FORMAT LONG ─────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-2 gap-4">
            @php
                $fieldMap = [
                    'col_prodi'     => ['label' => 'Kolom Program Studi', 'required' => true,  'hint' => 'Nama prodi harus bisa ditemukan di master data'],
                    'col_jalur'     => ['label' => 'Kolom Jalur Seleksi', 'required' => true,  'hint' => 'Nama jalur harus bisa ditemukan di master data'],
                    'col_kuota'     => ['label' => 'Kolom Daya Tampung',  'required' => false, 'hint' => 'Opsional'],
                    'col_pil1'      => ['label' => 'Kolom Pilihan 1',     'required' => false, 'hint' => 'Opsional'],
                    'col_pil2'      => ['label' => 'Kolom Pilihan 2',     'required' => false, 'hint' => 'Opsional'],
                    'col_pil3'      => ['label' => 'Kolom Pilihan 3',     'required' => false, 'hint' => 'Opsional'],
                    'col_pil4'      => ['label' => 'Kolom Pilihan 4',     'required' => false, 'hint' => 'Opsional'],
                    'col_lulus'     => ['label' => 'Kolom Jumlah Lulus',  'required' => false, 'hint' => 'Opsional'],
                    'col_registrasi'=> ['label' => 'Kolom Registrasi',    'required' => false, 'hint' => 'Opsional'],
                    'col_fakultas'  => ['label' => 'Kolom Fakultas',      'required' => false, 'hint' => 'Opsional, sebagai validasi'],
                ];
            @endphp
            @foreach($fieldMap as $prop => $cfg)
            <div class="p-4 border border-gray-200 rounded-lg">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ $cfg['label'] }}
                    @if($cfg['required']) <span class="text-red-500">*</span> @endif
                </label>
                <select wire:model="{{ $prop }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">-- Tidak dipetakan --</option>
                    @foreach($headers as $h)
                    <option value="{{ $h }}">{{ $h }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">{{ $cfg['hint'] }}</p>
                @error($prop) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            @endforeach
        </div>

        @else
        {{-- ── FORMAT WIDE ──────────────────────────────────────────────────── --}}
        <div class="mb-4 p-4 border border-gray-200 rounded-lg">
            <label class="block text-sm font-medium text-gray-700 mb-1">Kolom Program Studi <span class="text-red-500">*</span></label>
            <select wire:model="col_prodi" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">-- Pilih kolom prodi --</option>
                @foreach($headers as $h)
                <option value="{{ $h }}">{{ $h }}</option>
                @endforeach
            </select>
            @error('col_prodi') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-700">Mapping per Jalur Seleksi</p>
                <button wire:click="addJalurKolom" class="text-sm text-blue-700 hover:text-blue-900 border border-blue-200 px-3 py-1 rounded-lg transition">+ Tambah Jalur</button>
            </div>

            @foreach($jalurKolom as $i => $jk)
            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-600">Jalur {{ $i + 1 }}</span>
                    @if(count($jalurKolom) > 1)
                    <button wire:click="removeJalurKolom({{ $i }})" class="text-red-500 hover:text-red-700 text-xs">✕ Hapus</button>
                    @endif
                </div>
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Jalur (untuk resolusi ke master data)</label>
                        <input wire:model="jalurKolom.{{ $i }}.jalur" type="text" placeholder="SNBP / SPAN-PTKIN / Mandiri..."
                            class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    @foreach(['kuota'=>'Kuota','pil1'=>'Pilihan 1','pil2'=>'Pilihan 2','pil3'=>'Pilihan 3','pil4'=>'Pilihan 4','lulus'=>'Lulus','registrasi'=>'Registrasi'] as $field => $lbl)
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kolom {{ $lbl }}</label>
                        <select wire:model="jalurKolom.{{ $i }}.{{ $field }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">-- Opsional --</option>
                            @foreach($headers as $h)
                            <option value="{{ $h }}">{{ $h }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <div class="flex justify-between mt-6">
            <button wire:click="goBack" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">← Kembali</button>
            <button wire:click="goToPreview" class="bg-blue-700 hover:bg-blue-800 text-white px-6 py-2 rounded-lg text-sm font-medium transition">Lanjut →</button>
        </div>
    </div>
    @endif

    {{-- STEP 4: KONFIRMASI --}}
    @if($step === 4)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Konfirmasi Import</h2>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-500 mb-1">File</p>
                <p class="text-sm font-semibold text-gray-800">{{ $uploadedName }}</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-500 mb-1">Tahun & Format</p>
                <p class="text-sm font-semibold text-gray-800">{{ $tahun }}/{{ (int)$tahun + 1 }} — {{ strtoupper($format) }}</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-500 mb-1">Total Baris di File</p>
                <p class="text-sm font-semibold text-gray-800">{{ number_format($totalRows) }} baris</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-xs text-gray-500 mb-1">Mapping</p>
                @if($format === 'long')
                <p class="text-sm text-gray-700">Prodi: <strong>{{ $col_prodi }}</strong></p>
                <p class="text-sm text-gray-700">Jalur: <strong>{{ $col_jalur }}</strong></p>
                @else
                <p class="text-sm text-gray-700">Prodi: <strong>{{ $col_prodi }}</strong></p>
                <p class="text-sm text-gray-700">{{ count($jalurKolom) }} jalur dipetakan</p>
                @endif
            </div>
        </div>

        <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg mb-6">
            <p class="text-sm font-semibold text-amber-800 mb-1">⚠️ Perhatian</p>
            <ul class="text-sm text-amber-700 space-y-1 list-disc list-inside">
                <li>Data yang sudah ada akan di-<em>update</em> (bukan duplikat)</li>
                <li>Nama prodi/jalur yang tidak dikenali akan masuk ke <strong>Antrian Review</strong></li>
                <li>Proses tidak dapat dibatalkan setelah dimulai</li>
            </ul>
        </div>

        <div class="flex justify-between">
            <button wire:click="goBack" class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">← Kembali</button>
            <button wire:click="runImport" wire:loading.attr="disabled"
                class="bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white px-6 py-2 rounded-lg text-sm font-bold transition flex items-center gap-2">
                <span wire:loading wire:target="runImport" class="animate-spin text-lg">⏳</span>
                🚀 Mulai Import
            </button>
        </div>
    </div>
    @endif

    {{-- STEP 5: HASIL --}}
    @if($step === 5 && $importResult)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="text-center mb-6">
            <div class="text-5xl mb-3">
                {{ $importResult['antrian'] > 0 ? '⚠️' : '🎉' }}
            </div>
            <h2 class="text-xl font-bold text-gray-900">Import Selesai!</h2>
        </div>

        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="text-center p-4 bg-green-50 rounded-xl border border-green-200">
                <p class="text-3xl font-bold text-green-700">{{ number_format($importResult['inserted']) }}</p>
                <p class="text-sm text-green-600 mt-1">Baris berhasil diimpor</p>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-xl border border-gray-200">
                <p class="text-3xl font-bold text-gray-600">{{ number_format($importResult['skipped']) }}</p>
                <p class="text-sm text-gray-500 mt-1">Baris dilewati</p>
            </div>
            <div class="text-center p-4 {{ $importResult['antrian'] > 0 ? 'bg-yellow-50 border-yellow-200' : 'bg-gray-50 border-gray-200' }} rounded-xl border">
                <p class="text-3xl font-bold {{ $importResult['antrian'] > 0 ? 'text-yellow-700' : 'text-gray-400' }}">{{ number_format($importResult['antrian']) }}</p>
                <p class="text-sm {{ $importResult['antrian'] > 0 ? 'text-yellow-600' : 'text-gray-400' }} mt-1">Masuk antrian review</p>
            </div>
        </div>

        @if($importResult['antrian'] > 0)
        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg mb-6">
            <p class="text-sm font-semibold text-yellow-800">⏳ Ada {{ $importResult['antrian'] }} entitas yang belum dikenali</p>
            <p class="text-sm text-yellow-700 mt-1">Buka halaman <strong>Antrian Master Data</strong> untuk memetakan nama-nama tersebut ke data resmi.</p>
            <a href="{{ route('master.antrian') }}" class="inline-block mt-2 bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                Tinjau Antrian →
            </a>
        </div>
        @endif

        <div class="flex justify-center gap-3">
            <a href="{{ route('dashboard') }}" class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition">Ke Dashboard</a>
            <button wire:click="restart" class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Import File Lain</button>
        </div>
    </div>
    @endif
</div>
