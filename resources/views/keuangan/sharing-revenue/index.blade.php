@extends('layouts.app')

@section('title', 'Sharing Revenue')
@section('page-title', 'Sharing Revenue')
@section('page-description', 'Kelola pembagian keuntungan per blok dan siklus')

@section('content')
<div class="grid w-full space-y-5">
    @if($siklusCards->count())
    <div class="kt-card">
        <div class="kt-card-header">
            <div>
                <h3 class="kt-card-title">Siklus</h3>
                <p class="text-xs text-secondary-foreground mt-0.5">{{ $siklusCards->count() }} siklus tercatat</p>
            </div>
            @can('sharing-revenue.create')
            <a href="{{ route('sharing-revenue.create') }}" class="kt-btn kt-btn-sm kt-btn-primary">
                <i class="ki-filled ki-plus-squared"></i> Tambah Sharing
            </a>
            @endcan
        </div>
        <div class="kt-card-content p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($siklusCards as $siklusItem)
                @php
                    $isProfit = $siklusItem['keuntungan_kerugian'] >= 0;
                    $statusBadge = match($siklusItem['status']) {
                        'aktif' => 'kt-badge-warning',
                        'selesai' => 'kt-badge-success',
                        default => 'kt-badge-destructive',
                    };
                    $statusLabel = match($siklusItem['status']) {
                        'aktif' => 'Aktif',
                        'selesai' => 'Selesai',
                        default => 'Gagal',
                    };
                @endphp
                <div class="relative overflow-hidden rounded-lg border border-border bg-card p-4 hover:ring-2 hover:ring-primary/20 transition-all group">
                    <div class="absolute inset-x-0 top-0 h-1 {{ $siklusItem['status'] === 'aktif' ? 'bg-warning' : ($siklusItem['status'] === 'selesai' ? 'bg-success' : 'bg-muted') }}"></div>
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex items-center justify-center size-10 rounded-lg shrink-0 bg-primary/10 text-primary">
                                <i class="ki-filled ki-arrows-circle text-lg"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold truncate">{{ $siklusItem['nama_siklus'] }}</p>
                                <p class="text-xs text-muted-foreground truncate">{{ $siklusItem['blok_nama'] }} &bull; {{ $siklusItem['total_kolam'] }} kolam</p>
                            </div>
                        </div>
                        <span class="kt-badge kt-badge-sm {{ $statusBadge }} shrink-0">{{ $statusLabel }}</span>
                    </div>
                    <div class="flex flex-col gap-2.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-secondary-foreground">Tanggal Siklus</span>
                            <span class="text-mono font-medium">{{ $siklusItem['tgl_siklus']?->format('d/m/Y') ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-secondary-foreground flex items-center gap-1.5">
                                <span class="size-2 rounded-full" style="background:#17c653;"></span> Uang Masuk
                            </span>
                            <span class="text-mono font-medium text-success">Rp {{ number_format($siklusItem['uang_masuk'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-secondary-foreground flex items-center gap-1.5">
                                <span class="size-2 rounded-full" style="background:#f1416c;"></span> Uang Keluar
                            </span>
                            <span class="text-mono font-medium text-destructive">Rp {{ number_format($siklusItem['uang_keluar'], 0, ',', '.') }}</span>
                        </div>
                        <div class="border-t border-border pt-2.5 flex items-center justify-between text-xs">
                            <span class="text-secondary-foreground font-medium">Estimasi {{ $isProfit ? 'Keuntungan' : 'Kerugian' }}</span>
                            <span class="text-mono font-bold {{ $isProfit ? 'text-success' : 'text-destructive' }}">
                                {{ $isProfit ? '+' : '-' }}Rp {{ number_format(abs($siklusItem['keuntungan_kerugian']), 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="rounded-lg bg-accent/40 border border-border p-2.5">
                            <div class="flex items-center justify-between text-xs mb-2">
                                <span class="text-secondary-foreground font-medium">Sisa Sharing</span>
                                <span class="text-mono font-bold text-primary">{{ number_format($siklusItem['sharing_sisa'], 2, ',', '.') }}%</span>
                            </div>
                            <div class="h-1.5 rounded-full bg-muted overflow-hidden">
                                <div class="h-full rounded-full bg-primary" style="width: {{ min(100, $siklusItem['sharing_terpakai']) }}%;"></div>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-muted-foreground mt-1.5">
                                <span>Terpakai {{ number_format($siklusItem['sharing_terpakai'], 2, ',', '.') }}%</span>
                                <span>Total 100%</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-between gap-2">
                        <a href="{{ route('siklus.show', $siklusItem['id']) }}" class="kt-btn kt-btn-sm kt-btn-outline">
                            <i class="ki-filled ki-eye"></i> Detail
                        </a>
                        @if($siklusItem['status'] === 'selesai' && $siklusItem['sharing_sisa'] > 0)
                        <a href="{{ route('sharing-revenue.create', ['blok_id' => $siklusItem['blok_id'], 'siklus_id' => $siklusItem['id']]) }}" class="kt-btn kt-btn-sm kt-btn-primary">
                            <i class="ki-filled ki-percentage"></i> Sharing Revenue
                        </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <div class="kt-card">
        <div class="kt-card-header min-h-16">
            <form method="GET" class="flex items-center gap-2">
                <input type="text" name="search" placeholder="Cari..." class="kt-input" style="width:200px" data-kt-datatable-search="#sharing_revenue_table" value="{{ request('search') }}" />
            </form>
            @can('sharing-revenue.create')
            <a href="{{ route('sharing-revenue.create') }}" class="kt-btn kt-btn-primary">
                <i class="ki-filled ki-plus-squared"></i> Tambah
            </a>
            @endcan
        </div>
        <div id="sharing_revenue_table" class="kt-card-table" data-kt-datatable="true" data-kt-datatable-page-size="10" data-kt-datatable-state-save="false" data-kt-datatable-state-namespace="sharing_revenue">
            <div class="kt-table-wrapper kt-scrollable">
                <table class="kt-table" data-kt-datatable-table="true">
                    <thead>
                        <tr>
                            <th class="w-12" data-kt-datatable-column="no"><span class="kt-table-col"><span class="kt-table-col-label">No</span></span></th>
                            <th data-kt-datatable-column="nomor"><span class="kt-table-col"><span class="kt-table-col-label">No. Transaksi</span></span></th>
                            <th data-kt-datatable-column="penerima"><span class="kt-table-col"><span class="kt-table-col-label">Penerima</span></span></th>
                            <th data-kt-datatable-column="blok"><span class="kt-table-col"><span class="kt-table-col-label">Blok/Siklus</span></span></th>
                            <th data-kt-datatable-column="persen"><span class="kt-table-col"><span class="kt-table-col-label">Persen</span></span></th>
                            <th data-kt-datatable-column="nominal"><span class="kt-table-col"><span class="kt-table-col-label">Nominal</span></span></th>
                            <th data-kt-datatable-column="status"><span class="kt-table-col"><span class="kt-table-col-label">Status</span></span></th>
                            <th class="w-28" data-kt-datatable-column="aksi"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="text-mono whitespace-nowrap">{{ $item->nomor_transaksi }}</td>
                            <td>{{ $item->nama_penerima }}</td>
                            <td>
                                <div class="flex flex-col">
                                    <span>{{ $item->blok?->nama_blok ?? '-' }}</span>
                                    <span class="text-xs text-muted-foreground">{{ $item->siklus?->nama_siklus ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="text-mono">{{ number_format($item->persentase, 2, ',', '.') }}%</td>
                            <td class="text-mono whitespace-nowrap">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
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
                                    <form method="POST" action="{{ route('sharing-revenue.approve', $item) }}" class="inline">@csrf<button type="submit" class="kt-btn kt-btn-primary kt-btn-sm kt-btn-icon" title="Approve"><i class="ki-filled ki-check"></i></button></form>
                                    <button type="button" class="kt-btn kt-btn-destructive kt-btn-sm kt-btn-icon" title="Reject" onclick="openRejectModal('{{ route('sharing-revenue.reject', $item) }}', '{{ e($item->nomor_transaksi) }}')"><i class="ki-filled ki-cross"></i></button>
                                    @endif
                                    <a href="{{ route('sharing-revenue.show', $item) }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" title="Lihat"><i class="ki-filled ki-eye"></i></a>
                                    @can('sharing-revenue.edit')
                                    @if(auth()->user()->hasRole('Owner') || in_array($item->status, ['awaiting_approval','pending']))
                                    <a href="{{ route('sharing-revenue.edit', $item) }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" title="Edit"><i class="ki-filled ki-pencil"></i></a>
                                    @endif
                                    @endcan
                                    @can('sharing-revenue.delete')
                                    @if(auth()->user()->hasRole('Owner') || $item->status === 'awaiting_approval')
                                    <form method="POST" action="{{ route('sharing-revenue.destroy', $item) }}" onsubmit="return confirm('Yakin hapus?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger" title="Hapus"><i class="ki-filled ki-trash"></i></button>
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

<div id="rejectModal" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; padding:1rem;">
    <div class="kt-card w-full max-w-[380px] shadow-2xl">
        <div class="kt-card-header min-h-14">
            <div>
                <h3 class="kt-card-title">Reject Sharing Revenue</h3>
                <p class="text-xs text-muted-foreground mt-1" id="rejectNomor">-</p>
            </div>
            <button type="button" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" onclick="closeRejectModal()"><i class="ki-filled ki-cross"></i></button>
        </div>
        <form method="POST" id="rejectForm">
            @csrf
            <div class="kt-card-content py-4">
                <label class="text-sm font-medium text-foreground" for="alasan_reject">Alasan Reject <span class="text-danger">*</span></label>
                <textarea id="alasan_reject" name="alasan_reject" class="kt-input mt-2" rows="4" style="height:120px;" placeholder="Tuliskan alasan sharing revenue ditolak..." required minlength="5"></textarea>
            </div>
            <div class="kt-card-footer justify-end gap-2">
                <button type="button" class="kt-btn kt-btn-outline" onclick="closeRejectModal()">Batal</button>
                <button type="submit" class="kt-btn kt-btn-destructive"><i class="ki-filled ki-cross"></i> Reject</button>
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
