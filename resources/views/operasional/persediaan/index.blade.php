@extends('layouts.app')

@section('title', 'Persediaan')
@section('page-title', 'Persediaan')
@section('page-description', 'Kelola stok persediaan')

@section('content')
@php
    $formatQty = fn($value) => rtrim(rtrim(number_format((float) ($value ?? 0), 2, ',', '.'), '0'), ',');
@endphp

<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-16">
            <form method="GET" class="flex items-center gap-2">
                <input type="text" name="search" placeholder="Cari..." class="kt-input" style="width:200px" data-kt-datatable-search="#persediaan_table" value="{{ request('search') }}" />
            </form>
        </div>
        <div id="persediaan_table" class="kt-card-table" data-kt-datatable="true" data-kt-datatable-page-size="10" data-kt-datatable-state-save="false" data-kt-datatable-state-namespace="persediaan">
            <div class="kt-table-wrapper kt-scrollable">
                <table class="kt-table" data-kt-datatable-table="true">
                    <thead>
                        <tr>
                            <th class="w-12" data-kt-datatable-column="no"><span class="kt-table-col"><span class="kt-table-col-label">No</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="nama"><span class="kt-table-col"><span class="kt-table-col-label">Nama Produk</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="kategori"><span class="kt-table-col"><span class="kt-table-col-label">Kategori</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="qty"><span class="kt-table-col"><span class="kt-table-col-label">Qty</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="minimum_stok"><span class="kt-table-col"><span class="kt-table-col-label">Minimum Stok</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="status_stok"><span class="kt-table-col"><span class="kt-table-col-label">Status</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="unit"><span class="kt-table-col"><span class="kt-table-col-label">Unit</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="harga"><span class="kt-table-col"><span class="kt-table-col-label">Harga/Unit</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="total"><span class="kt-table-col"><span class="kt-table-col-label">Total Harga</span><span class="kt-table-col-sort"></span></span></th>
                            <th class="w-16" data-kt-datatable-column="aksi"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $i => $item)
                        @php
                            $minimumStok = $item->minimum_stok;
                            $hasMinimumStok = $minimumStok !== null && (float) $minimumStok > 0;
                            $isLowStock = $hasMinimumStok && (float) $item->qty <= (float) $minimumStok;
                        @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                <a href="{{ route('persediaan.show', $item) }}" class="kt-link">
                                    {{ $item->itemPersediaan?->kode_item_persediaan }} - {{ $item->itemPersediaan?->deskripsi }}
                                </a>
                            </td>
                            <td>{{ $item->itemPersediaan?->kategoriPersediaan?->deskripsi ?? '-' }}</td>
                            <td class="text-mono">{{ $formatQty($item->qty) }}</td>
                            <td class="text-mono">{{ $item->minimum_stok !== null ? $formatQty($item->minimum_stok) . ' ' . ($item->unit ?? '') : '-' }}</td>
                            <td>
                                @if($isLowStock)
                                    <span class="kt-badge kt-badge-sm kt-badge-destructive">Kurang</span>
                                @elseif($hasMinimumStok)
                                    <span class="kt-badge kt-badge-sm kt-badge-success">Aman</span>
                                @else
                                    <span class="kt-badge kt-badge-sm kt-badge-outline">Belum Diatur</span>
                                @endif
                            </td>
                            <td>{{ $item->unit ?? '-' }}</td>
                            <td class="text-mono">Rp {{ number_format($item->harga_per_unit, 0, ',', '.') }}</td>
                            <td class="text-mono">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                            <td class="text-end">
                                <span class="inline-flex gap-1">
                                    @can('persediaan.edit')
                                    <button type="button"
                                        class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline"
                                        title="Atur Minimum Stok"
                                        onclick="openMinimumStokModal(@js($item->id), @js(($item->itemPersediaan?->kode_item_persediaan ?? '') . ' - ' . ($item->itemPersediaan?->deskripsi ?? '-')), @js($item->minimum_stok !== null ? (float) $item->minimum_stok : ''), @js($item->unit ?? ''))">
                                        <i class="ki-filled ki-pencil"></i>
                                    </button>
                                    @endcan
                                    <a href="{{ route('persediaan.show', $item) }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" title="Lihat"><i class="ki-filled ki-eye"></i></a>
                                </span>
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
</div>

{{-- Modal Minimum Stok --}}
<div class="kt-modal" data-kt-modal="true" id="minimumStokModal">
    <div class="kt-modal-content max-w-[420px] top-0 sm:top-5 lg:top-[18%]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title">Atur Minimum Stok</h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form id="minimumStokForm" method="POST">
            @csrf
            @method('PATCH')
            <div class="kt-modal-body flex flex-col gap-4">
                <div class="p-3 rounded-lg bg-accent/40 border border-border">
                    <p class="text-xs text-muted-foreground">Item Persediaan</p>
                    <p class="text-sm font-semibold" id="minimum_stok_item">-</p>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-foreground">Minimum Stok</label>
                    <div class="kt-input-group">
                        <input class="kt-input text-mono" type="number" name="minimum_stok" id="minimum_stok_input" min="0" step="0.01" placeholder="0">
                        <span class="kt-input-addon" id="minimum_stok_unit">unit</span>
                    </div>
                    <p class="text-xs text-muted-foreground">Kosongkan nilai untuk menonaktifkan pengecekan minimum stok.</p>
                </div>
            </div>
            <div class="kt-modal-footer justify-end">
                <button type="button" class="kt-btn kt-btn-outline" data-kt-modal-dismiss="true">Batal</button>
                <button type="submit" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-check"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openMinimumStokModal(id, itemName, minimumStok, unit) {
    document.getElementById('minimumStokForm').action = "{{ route('persediaan.minimum-stok', ['persediaan' => '__ID__']) }}".replace('__ID__', id);
    document.getElementById('minimum_stok_item').textContent = itemName || '-';
    document.getElementById('minimum_stok_input').value = minimumStok || '';
    document.getElementById('minimum_stok_unit').textContent = unit || 'unit';
    KTModal.getInstance(document.querySelector('#minimumStokModal')).show();
}
</script>
@endpush
