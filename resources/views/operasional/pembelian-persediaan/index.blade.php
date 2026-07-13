@extends('layouts.app')

@section('title', 'Pembelian Persediaan')
@section('page-title', 'Pembelian Persediaan')
@section('page-description', 'Kelola pembelian persediaan')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-16">
            <form method="GET" class="flex items-center gap-2">
                <input type="text" name="search" placeholder="Cari..." class="kt-input" style="width:200px" data-kt-datatable-search="#pembelian_persediaan_table" value="{{ request('search') }}" />
            </form>
            @can('pembelian-persediaan.create')
            <a href="{{ route('pembelian-persediaan.create') }}" class="kt-btn kt-btn-primary">
                <i class="ki-filled ki-plus-squared"></i> Tambah Pembelian
            </a>
            @endcan
        </div>
        <div id="pembelian_persediaan_table" class="kt-card-table" data-kt-datatable="true" data-kt-datatable-page-size="10" data-kt-datatable-state-save="false" data-kt-datatable-state-namespace="pembelian_persediaan">
            <div class="kt-table-wrapper kt-scrollable">
                <table class="kt-table" data-kt-datatable-table="true">
                    <thead>
                        <tr>
                            <th class="w-12" data-kt-datatable-column="no"><span class="kt-table-col"><span class="kt-table-col-label">No</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="nomor"><span class="kt-table-col"><span class="kt-table-col-label">No. Transaksi</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="tgl"><span class="kt-table-col"><span class="kt-table-col-label">Tanggal</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="siklus"><span class="kt-table-col"><span class="kt-table-col-label">Blok/Siklus</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="jumlah"><span class="kt-table-col"><span class="kt-table-col-label">Jumlah Item</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="total"><span class="kt-table-col"><span class="kt-table-col-label">Grand Total</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="pembayaran"><span class="kt-table-col"><span class="kt-table-col-label">Pembayaran</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="status"><span class="kt-table-col"><span class="kt-table-col-label">Status</span><span class="kt-table-col-sort"></span></span></th>
                            <th class="w-28" data-kt-datatable-column="aksi"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="text-mono">{{ $item->nomor_transaksi }}</td>
                            <td>{{ $item->tgl_pembelian?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                <div class="text-sm">{{ $item->blok?->nama_blok ?? '-' }}</div>
                                <div class="text-xs text-muted-foreground">{{ $item->siklus?->nama_siklus ?? '-' }}</div>
                            </td>
                            <td class="text-mono">{{ $item->items->count() }}</td>
                            <td class="text-mono">Rp {{ number_format($item->items->sum('harga_total'), 0, ',', '.') }}</td>
                            <td>
                                @if($item->status_pembayaran === 'hutang')
                                    <span class="kt-badge kt-badge-sm kt-badge-warning">Hutang</span>
                                @elseif($item->status_pembayaran === 'sebagian')
                                    <span class="kt-badge kt-badge-sm kt-badge-primary">Sebagian</span>
                                    <div class="text-xs text-muted-foreground text-mono mt-1">
                                        Sisa Rp {{ number_format(max(0, $item->items->sum('harga_total') - ($item->nominal_dibayar ?? 0)), 0, ',', '.') }}
                                    </div>
                                @else
                                    <span class="kt-badge kt-badge-sm kt-badge-success">Lunas</span>
                                @endif
                            </td>
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
                                    <form method="POST" action="{{ route('pembelian-persediaan.approve', $item) }}" class="inline">@csrf<button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-success" title="Approve"><i class="ki-filled ki-check"></i></button></form>
                                    <button type="button" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger" title="Reject" onclick="openRejectModal('{{ route('pembelian-persediaan.reject', $item) }}', '{{ e($item->nomor_transaksi) }}')"><i class="ki-filled ki-cross"></i></button>
                                    @endif
                                    <a href="{{ route('pembelian-persediaan.show', $item) }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" title="Lihat"><i class="ki-filled ki-eye"></i></a>
                                    @can('pembelian-persediaan.edit')
                                    @if(auth()->user()->hasRole('Owner') || in_array($item->status, ['awaiting_approval','pending']))
                                    <a href="{{ route('pembelian-persediaan.edit', $item) }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" title="Edit"><i class="ki-filled ki-pencil"></i></a>
                                    @endif
                                    @endcan
                                    @can('pembelian-persediaan.delete')
                                    @if(auth()->user()->hasRole('Owner') || $item->status === 'awaiting_approval')
                                    <form method="POST" action="{{ route('pembelian-persediaan.destroy', $item) }}" onsubmit="return confirm('Yakin hapus?')">@csrf @method('DELETE')<button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger" title="Hapus"><i class="ki-filled ki-trash"></i></button></form>
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

<div id="rejectModal" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; padding:1rem;">
    <div class="kt-card w-full max-w-[460px] shadow-2xl">
        <div class="kt-card-header min-h-14">
            <div>
                <h3 class="kt-card-title">Reject Pembelian Persediaan</h3>
                <p class="text-xs text-muted-foreground mt-1" id="rejectNomor">-</p>
            </div>
            <button type="button" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" onclick="closeRejectModal()">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form method="POST" id="rejectForm">
            @csrf
            <div class="kt-card-content py-4">
                <label class="text-sm font-medium text-foreground" for="alasan_reject">Alasan Reject <span class="text-danger">*</span></label>
                <textarea id="alasan_reject" name="alasan_reject" class="kt-input mt-2" rows="4" style="height:120px;" placeholder="Tuliskan alasan pembelian persediaan ditolak..." required minlength="5"></textarea>
            </div>
            <div class="kt-card-footer justify-end gap-2">
                <button type="button" class="kt-btn kt-btn-outline" onclick="closeRejectModal()">Batal</button>
                <button type="submit" class="kt-btn kt-btn-destructive">
                    <i class="ki-filled ki-cross"></i> Reject
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openRejectModal(action, nomor) {
    var modal = document.getElementById('rejectModal');
    var form = document.getElementById('rejectForm');
    var label = document.getElementById('rejectNomor');
    var textarea = document.getElementById('alasan_reject');

    form.action = action;
    label.textContent = nomor;
    textarea.value = '';
    modal.style.display = 'flex';
    textarea.focus();
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}
</script>
@endpush
