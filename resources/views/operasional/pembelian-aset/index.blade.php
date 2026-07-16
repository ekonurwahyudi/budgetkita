@extends('layouts.app')

@section('title', 'Pembelian Aset')
@section('page-title', 'Pembelian Aset')
@section('page-description', 'Kelola pembelian aset')

@section('content')
@php
    $totalAset = $data->count();
    $totalNilaiBuku = $data->sum('nilai_buku_aset');
    $totalPembelian = $data->sum('nominal_pembelian');
@endphp
<div class="grid w-full space-y-5">
    <div class="grid grid-cols-3 gap-4">
        <div class="kt-card">
            <div class="kt-card-content py-4 px-4 flex items-center gap-3 min-w-0">
                <div class="size-10 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                    <i class="ki-filled ki-briefcase text-primary"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-muted-foreground truncate">Total Aset</p>
                    <p class="text-base font-bold text-mono truncate">{{ number_format($totalAset, 0, ',', '.') }}</p>
                    <p class="text-xs text-muted-foreground truncate">data pembelian</p>
                </div>
            </div>
        </div>
        <div class="kt-card">
            <div class="kt-card-content py-4 px-4 flex items-center gap-3 min-w-0">
                <div class="size-10 rounded-xl bg-success/10 flex items-center justify-center shrink-0">
                    <i class="ki-filled ki-wallet text-success"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-muted-foreground truncate">Nilai Buku</p>
                    <p class="text-base font-bold text-success text-mono truncate">Rp {{ number_format($totalNilaiBuku, 0, ',', '.') }}</p>
                    <p class="text-xs text-muted-foreground truncate">total nilai saat ini</p>
                </div>
            </div>
        </div>
        <div class="kt-card">
            <div class="kt-card-content py-4 px-4 flex items-center gap-3 min-w-0">
                <div class="size-10 rounded-xl bg-warning/10 flex items-center justify-center shrink-0">
                    <i class="ki-filled ki-cheque text-warning"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-muted-foreground truncate">Total Pembelian Aset</p>
                    <p class="text-base font-bold text-warning text-mono truncate">Rp {{ number_format($totalPembelian, 0, ',', '.') }}</p>
                    <p class="text-xs text-muted-foreground truncate">nominal pembelian</p>
                </div>
            </div>
        </div>
    </div>

    <div class="kt-card">
        <div class="kt-card-header min-h-16">
            <form method="GET" class="flex items-center gap-2">
                <input type="text" name="search" placeholder="Cari..." class="kt-input" style="width:200px" data-kt-datatable-search="#pembelian_aset_table" value="{{ request('search') }}" />
            </form>
            @can('pembelian-aset.create')
            <a href="{{ route('pembelian-aset.create') }}" class="kt-btn kt-btn-primary">
                <i class="ki-filled ki-plus-squared"></i> Tambah Aset
            </a>
            @endcan
        </div>
        <div id="pembelian_aset_table" class="kt-card-table" data-kt-datatable="true" data-kt-datatable-page-size="10" data-kt-datatable-state-save="false" data-kt-datatable-state-namespace="pembelian_aset">
            <div class="kt-table-wrapper kt-scrollable">
                <table class="kt-table" data-kt-datatable-table="true">
                    <thead>
                        <tr>
                            <th class="w-12" data-kt-datatable-column="no"><span class="kt-table-col"><span class="kt-table-col-label">No</span><span class="kt-table-col-sort"></span></span></th>
                            <th class="w-14" data-kt-datatable-column="foto"><span class="kt-table-col"><span class="kt-table-col-label">Foto</span></span></th>
                            <th data-kt-datatable-column="nomor"><span class="kt-table-col"><span class="kt-table-col-label">No. Transaksi</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="nama"><span class="kt-table-col"><span class="kt-table-col-label">Nama Aset</span><span class="kt-table-col-sort"></span></span></th>
                            <!-- <th data-kt-datatable-column="kategori"><span class="kt-table-col"><span class="kt-table-col-label">Kategori</span><span class="kt-table-col-sort"></span></span></th> -->
                            <th data-kt-datatable-column="tgl"><span class="kt-table-col"><span class="kt-table-col-label">Tgl Pembelian</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="stok"><span class="kt-table-col"><span class="kt-table-col-label">Qty On Hand</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="nominal"><span class="kt-table-col"><span class="kt-table-col-label">Nominal</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="pembayaran"><span class="kt-table-col"><span class="kt-table-col-label">Pembayaran</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="nilai_buku"><span class="kt-table-col"><span class="kt-table-col-label">Nilai Buku</span><span class="kt-table-col-sort"></span></span></th>
                            <!-- <th data-kt-datatable-column="depresiasi"><span class="kt-table-col"><span class="kt-table-col-label">Depresiasi/Thn</span><span class="kt-table-col-sort"></span></span></th> -->
                            <!-- <th data-kt-datatable-column="metode"><span class="kt-table-col"><span class="kt-table-col-label">Metode</span></span></th> -->
                            <!-- <th data-kt-datatable-column="status"><span class="kt-table-col"><span class="kt-table-col-label">Status</span><span class="kt-table-col-sort"></span></span></th> -->
                            <th class="w-28" data-kt-datatable-column="aksi"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                @if(!empty($item->foto_aset) && $item->foto_aset[0])
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($item->foto_aset[0]) }}" class="size-10 rounded-lg object-cover" alt="Foto">
                                @else
                                <div class="size-10 rounded-lg bg-muted flex items-center justify-center">
                                    <i class="ki-filled ki-image text-muted-foreground text-sm"></i>
                                </div>
                                @endif
                            </td>
                            <td class="text-mono text-sm whitespace-nowrap">{{ $item->nomor_transaksi }}</td>
                            <td>
                                <div class="font-medium">{{ $item->nama_aset }}</div>
                                @if($item->siklus || $item->blok)
                                <div class="text-xs text-muted-foreground">
                                    {{ $item->blok?->nama_blok ?? '-' }}{{ $item->siklus ? ' · ' . $item->siklus->nama_siklus : '' }}
                                </div>
                                @endif
                            </td>
                            <!-- <td>{{ $item->kategoriAset?->deskripsi ?? '-' }}</td> -->
                            <td>{{ $item->tgl_pembelian?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                @php
                                    $qtyTerjual = $item->penjualanAsets->sum('qty');
                                    $qtyTersedia = $item->qty_tersedia ?? $item->qty;
                                    $qtyRusak = $item->qty_rusak ?? 0;
                                    $qtyOnHand = $qtyTersedia + $qtyRusak;
                                @endphp
                                <div class="text-sm text-mono font-semibold">{{ number_format($qtyOnHand, 0, ',', '.') }}</div>
                                <div class="text-xs text-muted-foreground text-mono">
                                    {{ number_format($qtyTersedia, 0, ',', '.') }} baik · {{ number_format($qtyRusak, 0, ',', '.') }} rusak · {{ number_format($qtyTerjual, 0, ',', '.') }} terjual
                                </div>
                            </td>
                            <td class="text-mono whitespace-nowrap">Rp {{ number_format($item->nominal_pembelian, 0, ',', '.') }}</td>
                            <td>
                                @if($item->status_pembayaran === 'hutang')
                                    <span class="kt-badge kt-badge-sm kt-badge-warning">Hutang</span>
                                @elseif($item->status_pembayaran === 'sebagian')
                                    <span class="kt-badge kt-badge-sm kt-badge-primary">Sebagian</span>
                                    <div class="text-xs text-muted-foreground text-mono mt-1">
                                        Sisa Rp {{ number_format(max(0, $item->nominal_pembelian - ($item->nominal_dibayar ?? 0)), 0, ',', '.') }}
                                    </div>
                                @else
                                    <span class="kt-badge kt-badge-sm kt-badge-success">Lunas</span>
                                @endif
                            </td>
                            <td class="text-mono whitespace-nowrap">Rp {{ number_format($item->nilai_buku_aset, 0, ',', '.') }}</td>
                            <!-- <td class="text-mono">Rp {{ number_format($item->depresiasi_per_tahun, 0, ',', '.') }}</td> -->
                            <!-- <td>
                                @if($item->metode_depresiasi === 'persen')
                                    <span class="kt-badge kt-badge-sm kt-badge-primary kt-badge-outline">{{ $item->persen_depresiasi }}%</span>
                                @elseif($item->metode_depresiasi === 'tanpa')
                                    <span class="kt-badge kt-badge-sm kt-badge-warning kt-badge-outline">Tanpa</span>
                                @else
                                    <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">Garis Lurus</span>
                                @endif
                            </td> -->
                            <!-- <td>
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
                            </td> -->
                            <td class="text-end">
                                <span class="inline-flex gap-2.5">
                                    @if($item->status === 'awaiting_approval' && auth()->user()->hasRole('Owner'))
                                    <form method="POST" action="{{ route('pembelian-aset.approve', $item) }}" class="inline">@csrf<button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-success" title="Approve"><i class="ki-filled ki-check"></i></button></form>
                                    <button type="button" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger" title="Reject" onclick="openRejectModal('{{ route('pembelian-aset.reject', $item) }}', '{{ e($item->nomor_transaksi) }}')"><i class="ki-filled ki-cross"></i></button>
                                    @endif
                                    <a href="{{ route('pembelian-aset.show', $item) }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" title="Lihat"><i class="ki-filled ki-eye"></i></a>
                                    @can('pembelian-aset.edit')
                                    @if(auth()->user()->hasRole('Owner') || in_array($item->status, ['awaiting_approval','pending']))
                                    <a href="{{ route('pembelian-aset.edit', $item) }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" title="Edit"><i class="ki-filled ki-pencil"></i></a>
                                    @endif
                                    @endcan
                                    @can('pembelian-aset.delete')
                                    @if(auth()->user()->hasRole('Owner') || $item->status === 'awaiting_approval')
                                    <form method="POST" action="{{ route('pembelian-aset.destroy', $item) }}" onsubmit="return confirm('Yakin hapus?')">@csrf @method('DELETE')<button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger" title="Hapus"><i class="ki-filled ki-trash"></i></button></form>
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
                <h3 class="kt-card-title">Reject Pembelian Aset</h3>
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
                <textarea id="alasan_reject" name="alasan_reject" class="kt-input mt-2" rows="4" style="height:120px;" placeholder="Tuliskan alasan pembelian aset ditolak..." required minlength="5"></textarea>
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
