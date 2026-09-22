{{--
    Dropdown panel — position:fixed agar keluar dari overflow:auto modal.
    Posisi dihitung JS dari getBoundingClientRect() trigger button.
--}}

{{-- Backdrop transparan untuk tutup dropdown saat klik luar --}}
<div x-show="open" @click="cls()"
    class="fixed inset-0 z-[9998]" style="display:none;"></div>

{{-- Dropdown panel --}}
<div x-show="open"
    :style="{
        position: 'fixed',
        zIndex:   9999,
        width:    _width + 'px',
        left:     _left + 'px',
        top:      openUp ? (_top - 4) + 'px' : _top + 'px',
        transform: openUp ? 'translateY(-100%)' : 'none',
    }"
    x-transition:enter="transition ease-out duration-100"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-75"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="bg-white border border-gray-200 rounded-xl shadow-2xl overflow-hidden"
    style="display:none; min-width:180px;">

    {{-- Search input — tanpa icon agar tidak menutupi area ketik --}}
    <div class="p-2 border-b border-gray-100">
        <input x-ref="si" x-model="search" type="text"
            placeholder="🔍 Ketik untuk mencari..."
            @keydown.escape="cls()"
            @keydown.tab="cls()"
            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2
                   outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 bg-gray-50">
    </div>

    {{-- Options list --}}
    <div class="max-h-52 overflow-y-auto">
        {{-- Clear option --}}
        <template x-if="clr !== false">
            <button type="button" @click="pick('')"
                class="w-full text-left px-3 py-2 text-xs text-gray-400
                       hover:bg-gray-50 italic border-b border-gray-100">
                — Kosongkan pilihan
            </button>
        </template>

        {{-- Options --}}
        <template x-for="opt in fil" :key="opt.v">
            <button type="button" @click="pick(opt.v)"
                class="w-full text-left px-3 py-2 text-sm transition-colors"
                :class="String(sel) === String(opt.v)
                    ? 'bg-blue-50 text-blue-700 font-semibold'
                    : 'text-gray-700 hover:bg-gray-50'"
                x-text="opt.l">
            </button>
        </template>

        {{-- Empty state --}}
        <p x-show="fil.length === 0"
            class="px-3 py-4 text-sm text-gray-400 text-center italic">
            Tidak ada yang cocok
        </p>
    </div>
</div>
