@extends('layouts.app')

@section('title', 'Detail Pemberian Pakan')
@section('page-title', 'Detail Pemberian Pakan')
@section('page-description', ($pemberianPakan->kolam?->nama_kolam ?? 'Semua Kolam') . ' - ' . ($pemberianPakan->tgl_pakan?->format('d/m/Y') ?? '-'))

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <div class="flex items-center gap-3">
                <h3 class="kt-card-title">{{ $pemberianPakan->kolam?->nama_kolam ?? 'Semua Kolam' }}</h3>
                @if($pemberianPakan->puasa)
                    <span class="kt-badge kt-badge-sm kt-badge-warning">Puasa</span>
                @endif
            </div>
            <a href="{{ route('pemberian-pakan.index') }}" class="kt-btn kt-btn-sm kt-btn-outline">
                <i class="ki-filled ki-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="kt-card-content py-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <table class="kt-table-auto">
                        <tbody>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8 w-40">Tambak</td>
                                <td class="text-sm pb-3 font-medium">{{ $pemberianPakan->blok?->tambak?->nama_tambak ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Blok</td>
                                <td class="text-sm text-mono pb-3">{{ $pemberianPakan->blok?->nama_blok ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Siklus</td>
                                <td class="text-sm text-mono pb-3">{{ $pemberianPakan->siklus?->nama_siklus ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Kolam</td>
                                <td class="text-sm text-mono pb-3">{{ $pemberianPakan->kolam?->nama_kolam ?? 'Semua Kolam' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div>
                    <table class="kt-table-auto">
                        <tbody>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8 w-40">Tanggal & Jam</td>
                                <td class="text-sm text-mono pb-3">{{ $pemberianPakan->tgl_pakan?->format('d/m/Y H:i') ?? '-' }}</td>
                            </tr>
                            @if($groupItems->count() === 1 && !$groupItems->first()->puasa)
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Kategori</td>
                                <td class="text-sm pb-3"><span class="kt-badge kt-badge-sm kt-badge-outline">{{ $groupItems->first()->itemPersediaan?->kategoriPersediaan?->deskripsi ?? '-' }}</span></td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Item Pakan</td>
                                <td class="text-sm pb-3">{{ $groupItems->first()->itemPersediaan?->deskripsi ?? $groupItems->first()->itemPersediaan?->kode_item_persediaan ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Jumlah</td>
                                <td class="text-sm text-mono pb-3 font-semibold">{{ number_format($groupItems->first()->jumlah_pakan ?? 0, 2) }} {{ $groupItems->first()->unit ?? 'kg' }}</td>
                            </tr>
                            @else
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Total Item</td>
                                <td class="text-sm text-mono pb-3 font-semibold">{{ $groupItems->count() }} item pakan</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Item Pakan Table --}}
    @if($groupItems->count() > 1)
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">Item Pakan</h3>
            <span class="text-sm text-muted-foreground">{{ $groupItems->count() }} item · Total {{ number_format($groupItems->where('puasa', false)->sum('jumlah_pakan'), 2) }} {{ $pemberianPakan->unit ?? 'kg' }}</span>
        </div>
        <div id="pemberian_pakan_items_table" class="kt-card-table" data-kt-datatable="true" data-kt-datatable-page-size="10" data-kt-datatable-state-save="false" data-kt-datatable-state-namespace="pemberian_pakan_items">
            <div class="kt-table-wrapper kt-scrollable">
                <table class="kt-table" data-kt-datatable-table="true">
                    <thead>
                        <tr>
                            <th class="w-12" data-kt-datatable-column="no"><span class="kt-table-col"><span class="kt-table-col-label">No</span></span></th>
                            <th data-kt-datatable-column="item"><span class="kt-table-col"><span class="kt-table-col-label">Item Pakan</span></span></th>
                            <th data-kt-datatable-column="kategori"><span class="kt-table-col"><span class="kt-table-col-label">Kategori</span></span></th>
                            <th data-kt-datatable-column="jumlah"><span class="kt-table-col"><span class="kt-table-col-label">Jumlah</span></span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($groupItems as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                @if($item->puasa)
                                    <span class="kt-badge kt-badge-sm kt-badge-warning">Puasa</span>
                                @else
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium">{{ $item->itemPersediaan?->deskripsi ?? '-' }}</span>
                                        <span class="text-xs text-muted-foreground">{{ $item->itemPersediaan?->kode_item_persediaan ?? '' }}</span>
                                    </div>
                                @endif
                            </td>
                            <td><span class="kt-badge kt-badge-sm kt-badge-outline">{{ $item->itemPersediaan?->kategoriPersediaan?->deskripsi ?? '-' }}</span></td>
                            <td>
                                @if($item->puasa)
                                    -
                                @else
                                    <span class="text-mono">{{ number_format($item->jumlah_pakan ?? 0, 2) }} {{ $item->unit ?? 'kg' }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="kt-datatable-toolbar">
                <div class="kt-datatable-length">Show <select class="kt-select kt-select-sm w-16" name="perpage" data-kt-datatable-size="true"></select> per page</div>
                <div class="kt-datatable-info"><span data-kt-datatable-info="true"></span><div class="kt-datatable-pagination" data-kt-datatable-pagination="true"></div></div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection