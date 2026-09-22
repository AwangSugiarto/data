{{--
    Partial: ukt-table
    $title $rows $labelKey $cols $colPrefix
--}}
@php
    $normRows = collect($rows)->map(function($r) use ($labelKey) {
        return [
            'label' => $r[$labelKey] ?? '-',
            'cols'  => $r['cols'] ?? $r['ukts'] ?? [],
            'total' => $r['total'] ?? 0,
        ];
    })->filter(fn($r) => $r['total'] > 0)->values();
@endphp

<div class="p-3 border-b border-gray-100 bg-gray-50">
    <p class="text-xs font-semibold text-gray-700">{{ $title }}</p>
    @if(count($normRows))
    <p class="text-xs text-gray-400 mt-0.5">{{ count($normRows) }} baris &middot; Total: {{ number_format($normRows->sum('total')) }}</p>
    @endif
</div>

<div class="overflow-x-auto">
    <table class="w-full text-xs text-gray-600 whitespace-nowrap">
        <thead class="bg-gray-50 text-gray-500 uppercase border-b border-gray-200 sticky top-0">
            <tr>
                <th class="py-2.5 px-2 text-center font-semibold w-8">#</th>
                <th class="py-2.5 px-4 text-left font-semibold">{{ $labelKey === 'jalur' ? 'Jalur Masuk' : 'Nama' }}</th>
                @foreach($cols as $col)
                <th class="py-2.5 px-3 text-center font-semibold">{{ $colPrefix }}{{ $col }}</th>
                @endforeach
                <th class="py-2.5 px-4 text-right font-semibold">Total</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($normRows as $i => $row)
            <tr class="hover:bg-indigo-50/40 transition">
                <td class="py-2 px-2 text-center text-gray-400 font-medium">{{ $i + 1 }}</td>
                <td class="py-2 px-4 font-medium text-gray-800 max-w-xs truncate" title="{{ $row['label'] }}">{{ $row['label'] }}</td>
                @foreach($cols as $col)
                @php $val = (int)($row['cols'][$col] ?? 0); @endphp
                <td class="py-2 px-3 text-center {{ $val > 0 ? 'text-gray-800' : 'text-gray-300' }}">
                    {{ $val > 0 ? number_format($val) : '-' }}
                </td>
                @endforeach
                <td class="py-2 px-4 text-right font-semibold text-indigo-700">{{ number_format($row['total']) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ count($cols) + 3 }}" class="py-8 text-center text-gray-400">Tidak ada data untuk filter yang dipilih.</td>
            </tr>
            @endforelse
        </tbody>
        @if(count($normRows) > 1)
        <tfoot class="bg-gray-50 font-bold border-t-2 border-gray-200 text-gray-700">
            <tr>
                <td class="py-2.5 px-2"></td>
                <td class="py-2.5 px-4">TOTAL</td>
                @foreach($cols as $col)
                @php $colSum = $normRows->sum(fn($r) => (int)($r['cols'][$col] ?? 0)); @endphp
                <td class="py-2.5 px-3 text-center">{{ $colSum > 0 ? number_format($colSum) : '-' }}</td>
                @endforeach
                <td class="py-2.5 px-4 text-right text-blue-700">{{ number_format($normRows->sum('total')) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>
