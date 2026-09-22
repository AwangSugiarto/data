<div class="py-8 px-4 max-w-6xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Antrian Master Data</h1>
        <p class="text-sm text-gray-500 mt-1">Entitas baru yang terdeteksi saat import Excel dan belum dipetakan</p>
    </div>
    @if(session('success'))<div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>@endif

    {{-- Filter --}}
    <div class="flex flex-wrap gap-3 mb-4">
        <select wire:model.live="filterTipe" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Tipe</option>
            <option value="fakultas">Fakultas</option>
            <option value="prodi">Program Studi</option>
            <option value="jalur">Jalur Seleksi</option>
        </select>
        <select wire:model.live="filterStatus" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="menunggu">Menunggu Tinjauan</option>
            <option value="dikonfirmasi_baru">Dikonfirmasi Baru</option>
            <option value="dijadikan_alias">Dijadikan Alias</option>
            <option value="">Semua Status</option>
        </select>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Nilai Mentah dari Excel</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Tipe</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Batch Import</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Status</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-700">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @php
                    $provCache = collect($wilayahList)->pluck('provinsi')->filter()->map(fn($v) => strtolower(trim($v)))->unique()->flip()->toArray();
                    $kabCache = collect($wilayahList)->pluck('kabupaten_kota')->filter()->map(fn($v) => strtolower(trim($v)))->unique()->flip()->toArray();
                    $kecCache = collect($wilayahList)->pluck('kecamatan')->filter()->map(fn($v) => strtolower(trim($v)))->unique()->flip()->toArray();
                @endphp
                @forelse($antrian as $a)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-gray-900">
                        @if($a->tipe === 'wilayah')
                            @php
                                $parts = explode(' | ', $a->nilai_mentah);
                                $p = trim($parts[0] ?? '');
                                $k = trim($parts[1] ?? '');
                                $c = trim($parts[2] ?? '');
                            @endphp
                            @if($p)<span class="{{ isset($provCache[strtolower($p)]) ? 'font-medium' : 'text-red-600 font-bold' }}" title="{{ isset($provCache[strtolower($p)]) ? '' : 'Provinsi ini tidak ditemukan di database' }}">{{ $p }}{!! isset($provCache[strtolower($p)]) ? '' : ' ⚠️' !!}</span>@endif
                            @if($k) | <span class="{{ isset($kabCache[strtolower($k)]) ? 'font-medium' : 'text-red-600 font-bold' }}" title="{{ isset($kabCache[strtolower($k)]) ? '' : 'Kabupaten ini tidak ditemukan di database' }}">{{ $k }}{!! isset($kabCache[strtolower($k)]) ? '' : ' ⚠️' !!}</span>@endif
                            @if($c) | <span class="{{ isset($kecCache[strtolower($c)]) ? 'font-medium' : 'text-red-600 font-bold' }}" title="{{ isset($kecCache[strtolower($c)]) ? '' : 'Kecamatan ini tidak ditemukan di database' }}">{{ $c }}{!! isset($kecCache[strtolower($c)]) ? '' : ' ⚠️' !!}</span>@endif
                        @else
                            <span class="font-medium">{{ $a->nilai_mentah }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @php $tipeColors = ['fakultas'=>'blue','prodi'=>'purple','jalur'=>'orange']; $tc = $tipeColors[$a->tipe] ?? 'gray'; @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $tc }}-100 text-{{ $tc }}-800">{{ ucfirst($a->tipe) }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $a->importBatch?->nama_file_asal ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($a->status === 'menunggu')
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">⏳ Menunggu</span>
                        @elseif($a->status === 'dikonfirmasi_baru')
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">✅ Entitas Baru</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">🔗 Alias</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($a->status === 'menunggu')
                        <button wire:click="openTinjau({{ $a->id }})"
                            class="text-blue-600 hover:text-blue-800 text-xs border border-blue-200 hover:border-blue-400 px-2 py-1 rounded transition font-medium">
                            Tinjau
                        </button>
                        @else
                        <span class="text-gray-400 text-xs">{{ $a->penanggungJawab?->name ?? 'Sistem' }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">
                    @if($filterStatus === 'menunggu')
                        🎉 Tidak ada antrian yang perlu ditinjau.
                    @else
                        Tidak ada data.
                    @endif
                </td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $antrian->links() }}</div>
    </div>

    {{-- Modal Tinjau --}}
    @if($showTinjauModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6" wire:click.stop>
            <h2 class="text-lg font-bold text-gray-900 mb-1">Tinjau Antrian</h2>
            <p class="text-sm text-gray-500 mb-4">
                Tipe: <strong>{{ ucfirst($tinjauTipe) }}</strong> •
                Nilai dari Excel: <strong class="text-blue-700">{{ $nilaiMentah }}</strong>
            </p>

            {{-- Toggle aksi --}}
            <div class="flex gap-3 mb-4">
                <button wire:click="$set('aksi', 'baru')"
                    class="flex-1 py-2 rounded-lg text-sm font-medium border transition {{ $aksi === 'baru' ? 'bg-blue-700 text-white border-blue-700' : 'text-gray-600 border-gray-300 hover:bg-gray-50' }}">
                    ➕ Buat Entitas Baru
                </button>
                <button wire:click="$set('aksi', 'alias')"
                    class="flex-1 py-2 rounded-lg text-sm font-medium border transition {{ $aksi === 'alias' ? 'bg-purple-600 text-white border-purple-600' : 'text-gray-600 border-gray-300 hover:bg-gray-50' }}">
                    🔗 Jadikan Alias
                </button>
            </div>

            @if($aksi === 'baru')
            <div class="space-y-3">
                @if($tinjauTipe !== 'wilayah')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Resmi</label>
                    <input wire:model="namaBaru" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
                @else
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Provinsi</label>
                        <input wire:model.live="wilayahProv" list="list-provinsi" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kabupaten/Kota</label>
                        <input wire:model.live="wilayahKab" list="list-kabupaten" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kecamatan</label>
                        <input wire:model="wilayahKec" list="list-kecamatan" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>

                <datalist id="list-provinsi">
                    @foreach($masterProvinsi as $prov)
                        <option value="{{ $prov->nama_prop }}">
                    @endforeach
                </datalist>
                <datalist id="list-kabupaten">
                    @foreach($masterKabupaten as $kab)
                        <option value="{{ $kab->nama_kab }}">
                    @endforeach
                </datalist>
                <datalist id="list-kecamatan">
                    @foreach($masterKecamatan as $kec)
                        <option value="{{ $kec->nama_kec }}">
                    @endforeach
                </datalist>
                @endif
                @if($tinjauTipe === 'fakultas')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Fakultas</label>
                    <input wire:model="kodeBaruFakultas" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="FSH">
                </div>
                @elseif($tinjauTipe === 'jalur')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kelompok Jalur</label>
                    <select wire:model="kelompokJalurBaru" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="nasional">Nasional</option>
                        <option value="mandiri">Mandiri</option>
                        <option value="beasiswa">Beasiswa</option>
                        <option value="afirmasi">Afirmasi</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                @elseif($tinjauTipe === 'warganegara')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Negara</label>
                    <input wire:model="warganegaraKode" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="ID">
                </div>
                @endif
            </div>
            @else
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Petakan ke {{ ucfirst($tinjauTipe) }} yang sudah ada:</label>
                
                @if($tinjauTipe === 'wilayah')
                <div class="mb-3 p-3 bg-blue-50 text-blue-800 text-xs rounded-lg border border-blue-200">
                    <strong>💡 Tips:</strong> Jika data dari Excel hanya berupa nama Provinsi, sebaiknya gunakan opsi <strong>Buat Entitas Baru</strong> di atas (biarkan kolom Kabupaten kosong) agar tidak salah memetakan ke Kabupaten tertentu.
                </div>
                @endif
                    @if($tinjauTipe === 'wilayah')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Pilih Provinsi <span class="text-red-500">*</span></label>
                                <select wire:model.live="aliasProv" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                                    <option value="">-- Provinsi --</option>
                                    @foreach($masterProvinsi as $prov)
                                        <option value="{{ $prov->nama_prop }}">{{ $prov->nama_prop }}</option>
                                    @endforeach
                                </select>
                                @error('aliasProv') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Pilih Kabupaten <span class="text-red-500">*</span></label>
                                <select wire:model.live="aliasKab" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none" @if(!$aliasProv) disabled @endif>
                                    <option value="">-- Kabupaten/Kota --</option>
                                    @if($aliasProv)
                                        @foreach($masterKabupaten as $kab)
                                            <option value="{{ $kab->nama_kab }}">{{ $kab->nama_kab }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('aliasKab') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Pilih Kecamatan</label>
                                <select wire:model="aliasKec" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none" @if(!$aliasKab) disabled @endif>
                                    <option value="">-- Kecamatan (Opsional) --</option>
                                    @if($aliasKab)
                                        @foreach($masterKecamatan as $kec)
                                            <option value="{{ $kec->nama_kec }}">{{ $kec->nama_kec }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    @else
                        <select wire:model="pemetaanKeId" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none mt-2">
                            <option value="">-- Pilih --</option>
                            @if($tinjauTipe === 'fakultas')
                                @foreach($fakultasList as $item)
                                <option value="{{ $item->id }}">{{ $item->nama_fakultas }}</option>
                                @endforeach
                            @elseif($tinjauTipe === 'jalur')
                                @foreach($jalurList as $item)
                                <option value="{{ $item->id }}">{{ $item->nama_jalur }}</option>
                                @endforeach
                            @elseif($tinjauTipe === 'prodi')
                                @foreach($prodiList as $item)
                                <option value="{{ $item->id }}">{{ $item->nama_prodi }} ({{ $item->fakultas?->kode_fakultas }})</option>
                                @endforeach
                            @elseif($tinjauTipe === 'ukt')
                                @foreach($uktList as $item)
                                <option value="{{ $item->id }}">{{ $item->kelompok }}</option>
                                @endforeach
                            @elseif($tinjauTipe === 'warganegara')
                                @foreach($warganegaraList as $item)
                                <option value="{{ $item->id }}">{{ $item->nama_negara }} ({{ $item->kode_negara ?: '-' }})</option>
                                @endforeach
                            @endif
                        </select>
                    @endif
            </div>
            @endif

            <div class="flex justify-end gap-3 mt-6">
                <button wire:click="$set('showTinjauModal', false)" class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition">Batal</button>
                <button wire:click="konfirmasi" class="px-4 py-2 text-sm text-white {{ $aksi === 'baru' ? 'bg-blue-700 hover:bg-blue-800' : 'bg-purple-600 hover:bg-purple-700' }} rounded-lg transition">
                    {{ $aksi === 'baru' ? 'Konfirmasi sebagai Baru' : 'Simpan sebagai Alias' }}
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
