@extends('layouts.app')

@section('title', 'Persediaan')
@section('page-title', 'Persediaan')
@section('page-description', 'Kelola stok persediaan')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-16">
            <form method="GET" class="flex items-center gap-2">
                <input type="text" name="search" placeholder="Cari..." class="kt-input" style="width:200px" data-kt-datatable-search="#kt_datatable" value="{{ request('search') }}" />
            </form>
        </div>
        <div id="kt_datatable" class="kt-card-table" data-kt-datatable="true" data-kt-datatable-page-size="10" data-kt-datatable-state-save="true">
            <div class="kt-table-wrapper kt-scrollable">
                <table class="kt-table" data-kt-datatable-table="true">
                    <thead>
                        <tr>
                            <th class="w-12" data-kt-datatable-column="no"><span class="kt-table-col"><span class="kt-table-col-label">No</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="nama"><span class="kt-table-col"><span class="kt-table-col-label">Nama Produk</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="kategori"><span class="kt-table-col"><span class="kt-table-col-label">Kategori</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="qty"><span class="kt-table-col"><span class="kt-table-col-label">Qty</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="unit"><span class="kt-table-col"><span class="kt-table-col-label">Unit</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="harga"><span class="kt-table-col"><span class="kt-table-col-label">Harga/Unit</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="total"><span class="kt-table-col"><span class="kt-table-col-label">Total Harga</span><span class="kt-table-col-sort"></span></span></th>
                            <th class="w-16" data-kt-datatable-column="aksi"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                <a href="{{ route('persediaan.show', $item) }}" class="kt-link">
                                    {{ $item->itemPersediaan?->kode_item_persediaan }} - {{ $item->itemPersediaan?->deskripsi }}
                                </a>
                            </td>
                            <td>{{ $item->itemPersediaan?->kategoriPersediaan?->deskripsi ?? '-' }}</td>
                            <td class="text-mono">{{ number_format($item->qty, 2, ',', '.') }}</td>
                            <td>{{ $item->unit ?? '-' }}</td>
                            <td class="text-mono">Rp {{ number_format($item->harga_per_unit, 0, ',', '.') }}</td>
                            <td class="text-mono">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                            <td class="text-end">
                                <a href="{{ route('persediaan.show', $item) }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" title="Lihat"><i class="ki-filled ki-eye"></i></a>
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
@endsection
