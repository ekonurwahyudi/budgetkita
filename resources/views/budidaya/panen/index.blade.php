@extends('layouts.app')

@section('title', 'Data Panen')
@section('page-title', 'Data Panen')
@section('page-description', 'Kelola data panen udang')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-16">
            <form method="GET" class="flex items-center gap-2">
                <input type="text" name="search" placeholder="Cari..." class="kt-input" style="width:200px" data-kt-datatable-search="#kt_datatable" value="{{ request('search') }}" />
                <select name="status" class="kt-select sm:w-40" onchange="this.form.submit()">
                    <option value="">-- Status --</option>
                    <option value="awaiting_approval" {{ request('status')=='awaiting_approval'?'selected':'' }}>Awaiting</option>
                    <option value="proses" {{ request('status')=='proses'?'selected':'' }}>Proses</option>
                    <option value="selesai" {{ request('status')=='selesai'?'selected':'' }}>Selesai</option>
                    <option value="cancel" {{ request('status')=='cancel'?'selected':'' }}>Cancel</option>
                    <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                </select>
            </form>
            @can('panen.create')
            <a href="{{ route('panen.create') }}" class="kt-btn kt-btn-primary">
                <i class="ki-filled ki-plus-squared"></i> Tambah Panen
            </a>
            @endcan
        </div>
        <div id="kt_datatable" class="kt-card-table" data-kt-datatable="true" data-kt-datatable-page-size="10" data-kt-datatable-state-save="true">
            <div class="kt-table-wrapper kt-scrollable">
                <table class="kt-table" data-kt-datatable-table="true">
                    <thead>
                        <tr>
                            <th class="w-12" data-kt-datatable-column="no"><span class="kt-table-col"><span class="kt-table-col-label">No</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="siklus"><span class="kt-table-col"><span class="kt-table-col-label">Siklus</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="tgl"><span class="kt-table-col"><span class="kt-table-col-label">Tgl Panen</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="tipe"><span class="kt-table-col"><span class="kt-table-col-label">Tipe</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="berat"><span class="kt-table-col"><span class="kt-table-col-label">Total Berat</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="harga"><span class="kt-table-col"><span class="kt-table-col-label">Harga Jual</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="total"><span class="kt-table-col"><span class="kt-table-col-label">Total Penjualan</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="pembeli"><span class="kt-table-col"><span class="kt-table-col-label">Pembeli</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="status"><span class="kt-table-col"><span class="kt-table-col-label">Status</span><span class="kt-table-col-sort"></span></span></th>
                            <th class="w-28" data-kt-datatable-column="aksi"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-mono">{{ $item->siklus?->nama_siklus ?? '-' }}</span>
                                    <span class="text-xs text-secondary-foreground">{{ $item->siklus?->blok?->tambak?->nama_tambak ?? '' }} &rsaquo; {{ $item->siklus?->blok?->nama_blok ?? '' }}</span>
                                </div>
                            </td>
                            <td>{{ $item->tgl_panen?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                @if($item->tipe_panen === 'parsial')
                                    <span class="kt-badge kt-badge-sm kt-badge-warning kt-badge-outline">Parsial</span>
                                @elseif($item->tipe_panen === 'gagal')
                                    <span class="kt-badge kt-badge-sm kt-badge-destructive kt-badge-outline">Gagal</span>
                                @else
                                    <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">Full</span>
                                @endif
                            </td>
                            <td class="text-mono">{{ number_format($item->total_berat ?? 0, 0, ',', '.') }} kg</td>
                            <td class="text-mono">Rp {{ number_format($item->harga_jual ?? 0, 0, ',', '.') }}</td>
                            <td class="text-mono">Rp {{ number_format($item->total_penjualan ?? 0, 0, ',', '.') }}</td>
                            <td>{{ $item->pembeli ?? '-' }}</td>
                            <td>
                                @if($item->status === 'selesai')
                                    <span class="kt-badge kt-badge-sm kt-badge-success">Selesai</span>
                                @elseif($item->status === 'cancel')
                                    <span class="kt-badge kt-badge-sm kt-badge-destructive">Cancel</span>
                                @elseif($item->status === 'proses')
                                    <span class="kt-badge kt-badge-sm kt-badge-primary">Proses</span>
                                @elseif($item->status === 'pending')
                                    <span class="kt-badge kt-badge-sm kt-badge-warning">Pending</span>
                                @else
                                    <span class="kt-badge kt-badge-sm kt-badge-outline">Awaiting</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="inline-flex gap-2.5">
                                    @if($item->status === 'awaiting_approval' && auth()->user()->hasRole('Owner'))
                                    <form method="POST" action="{{ route('panen.approve', $item) }}" class="inline">@csrf<button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-success" title="Approve"><i class="ki-filled ki-check"></i></button></form>
                                    <form method="POST" action="{{ route('panen.reject', $item) }}" class="inline" onsubmit="return confirm('Yakin reject?')">@csrf<button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger" title="Reject"><i class="ki-filled ki-cross"></i></button></form>
                                    @endif
                                    <a href="{{ route('panen.show', $item) }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" title="Lihat"><i class="ki-filled ki-eye"></i></a>
                                    @can('panen.edit')
                                    @if(auth()->user()->hasRole('Owner') || in_array($item->status, ['awaiting_approval','pending']))
                                    <a href="{{ route('panen.edit', $item) }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" title="Edit"><i class="ki-filled ki-pencil"></i></a>
                                    @endif
                                    @endcan
                                    @can('panen.delete')
                                    @if(auth()->user()->hasRole('Owner') || $item->status === 'awaiting_approval')
                                    <form method="POST" action="{{ route('panen.destroy', $item) }}" onsubmit="return confirm('Yakin hapus?')">@csrf @method('DELETE')<button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger" title="Hapus"><i class="ki-filled ki-trash"></i></button></form>
                                    @endif
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