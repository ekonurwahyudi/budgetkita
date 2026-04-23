@extends('layouts.app')

@section('title', 'Gaji Karyawan')
@section('page-title', 'Gaji Karyawan')
@section('page-description', 'Kelola gaji karyawan')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-16">
            <form method="GET" class="flex items-center gap-2">
                <input type="text" name="search" placeholder="Cari..." class="kt-input" style="width:200px" data-kt-datatable-search="#kt_datatable" value="{{ request('search') }}" />
            </form>
            @can('gaji-karyawan.create')
            <a href="{{ route('gaji.create') }}" class="kt-btn kt-btn-outline">
                <i class="ki-filled ki-plus-squared"></i> Tambah
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
                            <th data-kt-datatable-column="karyawan"><span class="kt-table-col"><span class="kt-table-col-label">Karyawan</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="jabatan"><span class="kt-table-col"><span class="kt-table-col-label">Jabatan</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="gaji_pokok"><span class="kt-table-col"><span class="kt-table-col-label">Gaji Pokok</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="thp"><span class="kt-table-col"><span class="kt-table-col-label">THP</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="status"><span class="kt-table-col"><span class="kt-table-col-label">Status</span><span class="kt-table-col-sort"></span></span></th>
                            <th class="w-28" data-kt-datatable-column="aksi"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="text-mono">{{ $item->nomor_transaksi }}</td>
                            <td>{{ $item->user->nama ?? '-' }}</td>
                            <td>{{ $item->user->jabatan ?? '-' }}</td>
                            <td class="text-mono">Rp {{ number_format($item->gaji_pokok, 0, ',', '.') }}</td>
                            <td class="text-mono">Rp {{ number_format($item->thp, 0, ',', '.') }}</td>
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
                                    <form method="POST" action="{{ route('gaji.approve', $item) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-success" title="Approve"><i class="ki-filled ki-check"></i></button>
                                    </form>
                                    <form method="POST" action="{{ route('gaji.reject', $item) }}" class="inline" onsubmit="return confirm('Yakin reject?')">
                                        @csrf
                                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger" title="Reject"><i class="ki-filled ki-cross"></i></button>
                                    </form>
                                    @endif
                                    <a href="{{ route('gaji.show', $item) }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" title="Lihat"><i class="ki-filled ki-eye"></i></a>
                                    @can('gaji-karyawan.edit')
                                    @if(auth()->user()->hasRole('Owner') || in_array($item->status, ['awaiting_approval','pending']))
                                    <a href="{{ route('gaji.edit', $item) }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" title="Edit"><i class="ki-filled ki-pencil"></i></a>
                                    @endif
                                    @endcan
                                    @can('gaji-karyawan.delete')
                                    @if(auth()->user()->hasRole('Owner') || $item->status === 'awaiting_approval')
                                    <form method="POST" action="{{ route('gaji.destroy', $item) }}" onsubmit="return confirm('Yakin hapus?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger"><i class="ki-filled ki-trash"></i></button>
                                    </form>
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

