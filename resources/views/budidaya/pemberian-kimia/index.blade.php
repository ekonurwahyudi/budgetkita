@extends('layouts.app')

@section('title', 'Pemberian Kimia/Antibiotik')
@section('page-title', 'Pemberian Kimia/Antibiotik')
@section('page-description', 'Kelola data Pemberian Kimia/Antibiotik')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-16">
            <form method="GET" class="flex items-center gap-2">
                <input type="text" name="search" placeholder="Cari..." class="kt-input" style="width:200px" data-kt-datatable-search="#kt_datatable" value="{{ request('search') }}" />
            </form>
            @can('pemberian-pakan.create')
            <a href="{{ route('pemberian-kimia.create') }}" class="kt-btn kt-btn-primary">
                <i class="ki-filled ki-plus-squared"></i> Tambah Pemberian
            </a>
            @endcan
        </div>
        <div id="kt_datatable" class="kt-card-table" data-kt-datatable="true" data-kt-datatable-page-size="10" data-kt-datatable-state-save="true">
            <div class="kt-table-wrapper kt-scrollable">
                <table class="kt-table" data-kt-datatable-table="true">
                    <thead>
                        <tr>
                            <th class="w-12" data-kt-datatable-column="no"><span class="kt-table-col"><span class="kt-table-col-label">No</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="blok"><span class="kt-table-col"><span class="kt-table-col-label">Blok</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="siklus"><span class="kt-table-col"><span class="kt-table-col-label">Siklus</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="tgl"><span class="kt-table-col"><span class="kt-table-col-label">Tanggal</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="item"><span class="kt-table-col"><span class="kt-table-col-label">Kategori</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="nama"><span class="kt-table-col"><span class="kt-table-col-label">Item</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="jumlah"><span class="kt-table-col"><span class="kt-table-col-label">Jumlah (kg)</span><span class="kt-table-col-sort"></span></span></th>
                            <th class="w-24" data-kt-datatable-column="aksi"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-mono">{{ $item->blok?->nama_blok ?? '-' }}</span>
                                    <span class="text-xs text-secondary-foreground">{{ $item->blok?->tambak?->nama_tambak ?? '' }}</span>
                                </div>
                            </td>
                            <td>{{ $item->siklus?->nama_siklus ?? '-' }}</td>
                            <td>{{ $item->tgl_pakan?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td><span class="kt-badge kt-badge-sm kt-badge-outline">{{ $item->itemPersediaan?->kategoriPersediaan?->deskripsi ?? '-' }}</span></td>
                            <td>{{ $item->itemPersediaan?->deskripsi ?? $item->itemPersediaan?->kode_item_persediaan ?? '-' }}</td>
                            <td class="text-mono">{{ number_format($item->jumlah_pakan ?? 0, 2) }} {{ $item->unit ?? 'kg' }}</td>
                            <td class="text-end">
                                <span class="inline-flex gap-2.5">
                                    <a href="{{ route('pemberian-kimia.show', $item) }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" title="Lihat"><i class="ki-filled ki-eye"></i></a>
                                    @can('pemberian-pakan.delete')
                                    <form method="POST" action="{{ route('pemberian-kimia.destroy', $item) }}" onsubmit="return confirm('Yakin hapus? Stok akan dikembalikan.')">@csrf @method('DELETE')<button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger" title="Hapus"><i class="ki-filled ki-trash"></i></button></form>
                                    @endcan
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
@endsection
