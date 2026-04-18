@extends('layouts.app')

@section('title', 'Transaksi Keuangan')
@section('page-title', 'Transaksi Keuangan')
@section('page-description', 'Kelola transaksi keuangan')

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
                <select name="jenis_transaksi" class="kt-select sm:w-40" onchange="this.form.submit()">
                    <option value="">-- Jenis --</option>
                    <option value="uang_masuk" {{ request('jenis_transaksi')=='uang_masuk'?'selected':'' }}>Uang Masuk</option>
                    <option value="uang_keluar" {{ request('jenis_transaksi')=='uang_keluar'?'selected':'' }}>Uang Keluar</option>
                </select>
            </form>
            @can('transaksi-keuangan.create')
            <a href="{{ route('transaksi.create') }}" class="kt-btn kt-btn-primary">
                <i class="ki-filled ki-plus-squared"></i> Tambah Transaksi
            </a>
            @endcan
        </div>
        <div id="kt_datatable" class="kt-card-table" data-kt-datatable="true" data-kt-datatable-page-size="10" data-kt-datatable-state-save="true">
            <div class="kt-table-wrapper kt-scrollable">
                <table class="kt-table" data-kt-datatable-table="true">
                    <thead>
                        <tr>
                            <th class="w-12" data-kt-datatable-column="no"><span class="kt-table-col"><span class="kt-table-col-label">No</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="nomor"><span class="kt-table-col"><span class="kt-table-col-label">No. Transaksi</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="jenis"><span class="kt-table-col"><span class="kt-table-col-label">Jenis</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="tgl"><span class="kt-table-col"><span class="kt-table-col-label">Tanggal</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="aktivitas"><span class="kt-table-col"><span class="kt-table-col-label">Aktivitas/Kegiatan</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="nominal"><span class="kt-table-col"><span class="kt-table-col-label">Nominal</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="status"><span class="kt-table-col"><span class="kt-table-col-label">Status</span><span class="kt-table-col-sort"></span></span></th>
                            <th class="w-28" data-kt-datatable-column="aksi"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="text-mono">{{ $item->nomor_transaksi }}</td>
                            <td>
                                @if($item->jenis_transaksi === 'uang_masuk')
                                    <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">Uang Masuk</span>
                                @elseif($item->jenis_transaksi === 'uang_keluar')
                                    <span class="kt-badge kt-badge-sm kt-badge-destructive kt-badge-outline">Uang Keluar</span>
                                @endif
                            </td>
                            <td>{{ $item->tgl_kwitansi?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ Str::limit($item->aktivitas, 40) }}</td>
                            <td class="text-mono">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
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
                                    <form method="POST" action="{{ route('transaksi.approve', $item) }}" class="inline">@csrf<button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-success" title="Approve"><i class="ki-filled ki-check"></i></button></form>
                                    <form method="POST" action="{{ route('transaksi.reject', $item) }}" class="inline" onsubmit="return confirm('Yakin reject?')">@csrf<button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger" title="Reject"><i class="ki-filled ki-cross"></i></button></form>
                                    @endif
                                    <a href="{{ route('transaksi.show', $item) }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" title="Lihat"><i class="ki-filled ki-eye"></i></a>
                                    @can('transaksi-keuangan.edit')
                                    @if(auth()->user()->hasRole('Owner') || in_array($item->status, ['awaiting_approval','pending']))
                                    <a href="{{ route('transaksi.edit', $item) }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" title="Edit"><i class="ki-filled ki-pencil"></i></a>
                                    @endif
                                    @endcan
                                    @can('transaksi-keuangan.delete')
                                    @if(auth()->user()->hasRole('Owner') || $item->status === 'awaiting_approval')
                                    <form method="POST" action="{{ route('transaksi.destroy', $item) }}" onsubmit="return confirm('Yakin hapus?')">@csrf @method('DELETE')<button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger" title="Hapus"><i class="ki-filled ki-trash"></i></button></form>
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
