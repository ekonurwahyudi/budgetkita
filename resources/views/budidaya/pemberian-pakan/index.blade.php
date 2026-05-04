@extends('layouts.app')

@section('title', 'Pemberian Pakan')
@section('page-title', 'Pemberian Pakan')
@section('page-description', 'Kelola data pemberian pakan')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-16">
            <div class="flex items-center gap-2">
                <select id="perPageSelect" class="kt-select kt-select-sm w-20" onchange="changePerPage()">
                    <option value="5" {{ request('per_page', 10) == 5 ? 'selected' : '' }}>5</option>
                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                </select>
                <span class="text-sm text-muted-foreground">dari {{ $paged->total() }} grup</span>
            </div>
            @can('pemberian-pakan.create')
            <a href="{{ route('pemberian-pakan.create') }}" class="kt-btn kt-btn-primary">
                <i class="ki-filled ki-plus-squared"></i> Tambah
            </a>
            @endcan
        </div>
        <div class="kt-card-content">
            <div class="kt-scrollable" style="max-height:75vh">
                <table class="kt-table kt-table-border" id="pakanTable">
                    <thead>
                        <tr>
                            <th class="w-10">No</th>
                            <th>Tanggal</th>
                            <th>Kolam</th>
                            <th>Item Pakan</th>
                            <th>Jumlah</th>
                            <th class="w-28 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paged as $gi => $group)
                            @php $rowNo = $paged->firstItem() + $gi; @endphp
                            @if($group['items']->count() > 1)
                            <tr class="bg-muted/40 cursor-pointer" onclick="toggleGroup('{{ $loop->index }}')">
                                <td class="font-medium">{{ $rowNo }}</td>
                                <td>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium">{{ $group['tgl_pakan']?->format('d/m/Y') ?? '-' }}</span>
                                        <span class="text-xs text-muted-foreground">{{ $group['tgl_pakan']?->format('H:i') ?? '' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium">{{ $group['kolam'] }}</span>
                                        <span class="text-xs text-muted-foreground">{{ $group['blok'] }} · {{ $group['siklus'] }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-1.5">
                                        @if($group['is_puasa'])
                                            <span class="kt-badge kt-badge-sm kt-badge-warning">Puasa</span>
                                        @endif
                                        <span class="text-sm">{{ $group['items']->where('puasa', false)->count() }} jenis pakan</span>
                                        <i class="ki-filled ki-down text-xs ms-1 transition-transform" id="icon-{{ $loop->index }}"></i>
                                    </div>
                                </td>
                                <td class="text-mono text-sm font-medium">
                                    @if($group['is_puasa'] && $group['total_jumlah'] == 0)
                                        -
                                    @else
                                        {{ number_format($group['total_jumlah'], 2) }} {{ $group['unit'] }}
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('pemberian-pakan.show', $group['items']->first()) }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" title="Lihat detail"><i class="ki-filled ki-eye"></i></a>
                                </td>
                            </tr>
                            @foreach($group['items'] as $item)
                            <tr class="group-child-{{ $loop->parent->index }} hidden bg-muted/5" style="border-left:3px solid var(--tw-border-opacity, #3b82f6)">
                                <td class="ps-5 text-muted-foreground">•</td>
                                <td><span class="text-xs text-muted-foreground">{{ $item->tgl_pakan?->format('H:i') ?? '-' }}</span></td>
                                <td><span class="text-xs">{{ $item->kolam?->nama_kolam ?? '-' }}</span></td>
                                <td>
                                    @if($item->puasa)
                                        <span class="kt-badge kt-badge-sm kt-badge-warning">Puasa</span>
                                    @else
                                        <span class="text-sm">{{ $item->itemPersediaan?->deskripsi ?? $item->itemPersediaan?->kode_item_persediaan ?? '-' }}</span>
                                    @endif
                                </td>
                                <td class="text-mono text-sm">
                                    @if($item->puasa) - @else {{ number_format($item->jumlah_pakan ?? 0, 2) }} {{ $item->unit ?? 'kg' }} @endif
                                </td>
                                <td class="text-end">
                                    <span class="inline-flex gap-1.5">
                                        <a href="{{ route('pemberian-pakan.show', $item) }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" title="Lihat"><i class="ki-filled ki-eye"></i></a>
                                        @can('pemberian-pakan.delete')
                                        <form method="POST" action="{{ route('pemberian-pakan.destroy', $item) }}" onsubmit="return confirm('Yakin hapus? Stok akan dikembalikan.')">@csrf @method('DELETE')<button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger" title="Hapus"><i class="ki-filled ki-trash"></i></button></form>
                                        @endcan
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                            @else
                            @php $item = $group['items']->first(); @endphp
                            <tr>
                                <td>{{ $rowNo }}</td>
                                <td>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium">{{ $item->tgl_pakan?->format('d/m/Y') ?? '-' }}</span>
                                        <span class="text-xs text-muted-foreground">{{ $item->tgl_pakan?->format('H:i') ?? '' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium">{{ $item->kolam?->nama_kolam ?? '-' }}</span>
                                        <span class="text-xs text-muted-foreground">{{ $item->blok?->nama_blok ?? '' }} · {{ $item->siklus?->nama_siklus ?? '' }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($item->puasa)
                                        <span class="kt-badge kt-badge-sm kt-badge-warning">Puasa</span>
                                    @else
                                        <span class="text-sm">{{ $item->itemPersediaan?->deskripsi ?? $item->itemPersediaan?->kode_item_persediaan ?? '-' }}</span>
                                    @endif
                                </td>
                                <td class="text-mono text-sm">
                                    @if($item->puasa) - @else {{ number_format($item->jumlah_pakan ?? 0, 2) }} {{ $item->unit ?? 'kg' }} @endif
                                </td>
                                <td class="text-end">
                                    <span class="inline-flex gap-1.5">
                                        <a href="{{ route('pemberian-pakan.show', $item) }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" title="Lihat"><i class="ki-filled ki-eye"></i></a>
                                        @can('pemberian-pakan.delete')
                                        <form method="POST" action="{{ route('pemberian-pakan.destroy', $item) }}" onsubmit="return confirm('Yakin hapus? Stok akan dikembalikan.')">@csrf @method('DELETE')<button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger" title="Hapus"><i class="ki-filled ki-trash"></i></button></form>
                                        @endcan
                                    </span>
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="kt-card-footer flex items-center justify-between py-3 px-5 border-t">
            <span class="text-sm text-muted-foreground">Menampilkan {{ $paged->firstItem() }}–{{ $paged->lastItem() }} dari {{ $paged->total() }} grup</span>
            <div class="flex items-center gap-1">
                {{ $paged->onEachSide(1)->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleGroup(id) {
    const children = document.querySelectorAll('.group-child-' + id);
    const icon = document.getElementById('icon-' + id);
    if (!children.length) return;
    const isHidden = children[0].classList.contains('hidden');
    children.forEach(el => {
        if (isHidden) el.classList.remove('hidden');
        else el.classList.add('hidden');
    });
    if (icon) {
        if (isHidden) icon.classList.add('rotate-180');
        else icon.classList.remove('rotate-180');
    }
}
function changePerPage() {
    const val = document.getElementById('perPageSelect').value;
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', val);
    url.searchParams.delete('page');
    window.location.href = url.toString();
}
</script>
@endpush
@endsection