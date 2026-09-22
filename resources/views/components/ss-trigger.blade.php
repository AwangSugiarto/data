{{-- Trigger button for searchable select --}}
<button type="button" x-ref="trigger" @click="tog()"
    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-left flex items-center justify-between bg-white hover:border-blue-400 focus:ring-2 focus:ring-blue-300 focus:outline-none transition min-h-[38px]"
    :class="open ? 'border-blue-400 ring-2 ring-blue-100' : ''">
    <span x-text="lbl" :class="sel ? 'text-gray-900' : 'text-gray-400'" class="truncate pr-2 flex-1"></span>
    <svg :class="open ? 'rotate-180 text-blue-500' : 'text-gray-400'"
        class="w-4 h-4 shrink-0 transition-transform duration-150"
        fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
    </svg>
</button>
