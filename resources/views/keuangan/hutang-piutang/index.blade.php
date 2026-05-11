@extends('layouts.app')

@section('title', 'Hutang/Piutang')
@section('page-title', 'Hutang/Piutang')
@section('page-description', 'Kelola data hutang dan piutang')

@section('content')
@php
    $activeData = $data->where('status', '!=', 'cancel');
    $totalHutang = $activeData->where('jenis', 'hutang')->sum(fn($item) => $item->total_bayar ?? $item->nominal ?? 0);
    $sisaHutang = $activeData->where('jenis', 'hutang')->sum(fn($item) => $item->sisa_pembayaran ?? ($item->total_bayar ?? $item->nominal ?? 0));
    $totalPiutang = $activeData->where('jenis', 'piutang')->sum('nominal');
    $sisaPiutang = $activeData->where('jenis', 'piutang')->sum(fn($item) => $item->sisa_pembayaran ?? $item->nominal ?? 0);
    $jumlahHutang = $activeData->where('jenis', 'hutang')->count();
    $jumlahPiutang = $activeData->where('jenis', 'piutang')->count();
@endphp

<div class="grid w-full space-y-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        <div class="kt-card overflow-hidden border-destructive/20">
            <div class="kt-card-content p-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex flex-col gap-2 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="size-2 rounded-full bg-destructive"></span>
                            <span class="text-xs font-medium uppercase tracking-wide text-muted-foreground">Jumlah Hutang</span>
                        </div>
                        <span class="text-2xl font-semibold text-mono text-foreground leading-none truncate">Rp {{ number_format($totalHutang, 0, ',', '.') }}</span>
                        <span class="text-xs text-muted-foreground">{{ number_format($jumlahHutang, 0, ',', '.') }} transaksi hutang aktif</span>
                    </div>
                    <span class="size-11 rounded-lg bg-destructive/10 text-destructive flex items-center justify-center shrink-0">
                        <i class="ki-filled ki-arrow-up text-xl"></i>
                    </span>
                </div>
            </div>
        </div>

        <div class="kt-card overflow-hidden border-warning/20">
            <div class="kt-card-content p-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex flex-col gap-2 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="size-2 rounded-full bg-warning"></span>
                            <span class="text-xs font-medium uppercase tracking-wide text-muted-foreground">Sisa Hutang</span>
                        </div>
                        <span class="text-2xl font-semibold text-mono text-warning leading-none truncate">Rp {{ number_format($sisaHutang, 0, ',', '.') }}</span>
                        <span class="text-xs text-muted-foreground">Belum dibayarkan</span>
                    </div>
                    <span class="size-11 rounded-lg bg-warning/10 text-warning flex items-center justify-center shrink-0">
                        <i class="ki-filled ki-time text-xl"></i>
                    </span>
                </div>
            </div>
        </div>

        <div class="kt-card overflow-hidden border-success/20">
            <div class="kt-card-content p-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex flex-col gap-2 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="size-2 rounded-full bg-success"></span>
                            <span class="text-xs font-medium uppercase tracking-wide text-muted-foreground">Jumlah Piutang</span>
                        </div>
                        <span class="text-2xl font-semibold text-mono text-foreground leading-none truncate">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</span>
                        <span class="text-xs text-muted-foreground">{{ number_format($jumlahPiutang, 0, ',', '.') }} transaksi piutang aktif</span>
                    </div>
                    <span class="size-11 rounded-lg bg-success/10 text-success flex items-center justify-center shrink-0">
                        <i class="ki-filled ki-arrow-down text-xl"></i>
                    </span>
                </div>
            </div>
        </div>

        <div class="kt-card overflow-hidden border-primary/20">
            <div class="kt-card-content p-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex flex-col gap-2 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="size-2 rounded-full bg-primary"></span>
                            <span class="text-xs font-medium uppercase tracking-wide text-muted-foreground">Sisa Piutang</span>
                        </div>
                        <span class="text-2xl font-semibold text-mono text-primary leading-none truncate">Rp {{ number_format($sisaPiutang, 0, ',', '.') }}</span>
                        <span class="text-xs text-muted-foreground">Belum diterima</span>
                    </div>
                    <span class="size-11 rounded-lg bg-primary/10 text-primary flex items-center justify-center shrink-0">
                        <i class="ki-filled ki-wallet text-xl"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="kt-card">
        <div class="kt-card-header min-h-16">
            <input type="text" placeholder="Cari..." class="kt-input" style="width:200px" data-kt-datatable-search="#hutang_piutang_table" />
            @can('hutang-piutang.create')
            <a href="{{ route('hutang-piutang.create') }}" class="kt-btn kt-btn-outline">
                <i class="ki-filled ki-plus-squared"></i> Tambah
            </a>
            @endcan
        </div>
        <div id="hutang_piutang_table" class="kt-card-table" data-kt-datatable="true" data-kt-datatable-page-size="10" data-kt-datatable-state-save="true" data-kt-datatable-state-namespace="hutang_piutang">
            <div class="kt-table-wrapper kt-scrollable">
                <table class="kt-table" data-kt-datatable-table="true">
                    <thead>
                        <tr>
                            <th class="w-12" data-kt-datatable-column="no"><span class="kt-table-col"><span class="kt-table-col-label">No</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="nomor"><span class="kt-table-col"><span class="kt-table-col-label">No. Transaksi</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="jenis"><span class="kt-table-col"><span class="kt-table-col-label">Jenis</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="aktivitas"><span class="kt-table-col"><span class="kt-table-col-label">Aktivitas</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="nominal"><span class="kt-table-col"><span class="kt-table-col-label">Nominal</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="jatuh_tempo"><span class="kt-table-col"><span class="kt-table-col-label">Jatuh Tempo</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="sisa"><span class="kt-table-col"><span class="kt-table-col-label">Sisa</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="status_bayar"><span class="kt-table-col"><span class="kt-table-col-label">Status Bayar</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="status"><span class="kt-table-col"><span class="kt-table-col-label">Status</span><span class="kt-table-col-sort"></span></span></th>
                            <th class="w-36" data-kt-datatable-column="aksi"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $i => $item)
                        @php
                            $sisaBayar = $item->sisa_pembayaran ?? $item->nominal;
                            $lunas = $sisaBayar <= 0;
                            $telat = !$lunas && $item->jatuh_tempo && $item->jatuh_tempo->isPast();
                        @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="text-mono">{{ $item->nomor_transaksi }}</td>
                            <td>
                                @if($item->jenis === 'hutang')<span class="kt-badge kt-badge-sm kt-badge-destructive kt-badge-outline">Hutang</span>
                                @else<span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">Piutang</span>@endif
                            </td>
                            <td>{{ Str::limit($item->aktivitas, 30) }}</td>
                            <td class="text-mono">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                            <td>
                                {{ $item->jatuh_tempo?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td class="text-mono {{ $lunas ? 'text-success' : ($telat ? 'text-danger' : '') }}">
                                Rp {{ number_format($sisaBayar, 0, ',', '.') }}
                            </td>
                            <td>
                                @if($lunas)
                                    <span class="kt-badge kt-badge-sm kt-badge-success">Lunas</span>
                                @elseif($telat)
                                    <span class="kt-badge kt-badge-sm kt-badge-destructive">Telat</span>
                                @else
                                    <span class="kt-badge kt-badge-sm kt-badge-warning">Belum Lunas</span>
                                @endif
                            </td>
                            <td>
                                @if($item->status === 'selesai')<span class="kt-badge kt-badge-sm kt-badge-success">Selesai</span>
                                @elseif($item->status === 'cancel')<span class="kt-badge kt-badge-sm kt-badge-destructive">Cancel</span>
                                @elseif($item->status === 'proses')<span class="kt-badge kt-badge-sm kt-badge-primary">Proses</span>
                                @elseif($item->status === 'pending')<span class="kt-badge kt-badge-sm kt-badge-warning">Pending</span>
                                @else<span class="kt-badge kt-badge-sm kt-badge-outline">Awaiting</span>@endif
                            </td>
                            <td class="text-end">
                                <span class="inline-flex gap-2.5">
                                    @if($item->status === 'awaiting_approval' && auth()->user()->hasRole('Owner'))
                                    <form method="POST" action="{{ route('hutang-piutang.approve', $item) }}" class="inline">@csrf<button type="submit" class="kt-btn kt-btn-primary kt-btn-sm kt-btn-icon" title="Approve"><i class="ki-filled ki-check"></i></button></form>
                                    <button type="button" class="kt-btn kt-btn-destructive kt-btn-sm kt-btn-icon" title="Reject" onclick="openRejectModal('{{ route('hutang-piutang.reject', $item) }}', '{{ e($item->nomor_transaksi) }}')"><i class="ki-filled ki-cross"></i></button>
                                    @endif

                                    {{-- Tombol Bayar: tampil jika belum lunas dan status selesai --}}
                                    @if(!$lunas && $item->status === 'selesai')
                                    <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-primary" title="Bayar"
                                        onclick="openBayarModal('{{ $item->id }}', '{{ $item->nomor_transaksi }}', {{ $sisaBayar }}, '{{ $item->jenis }}')">
                                        <i class="ki-filled ki-dollar"></i>
                                    </button>
                                    @endif

                                    <a href="{{ route('hutang-piutang.show', $item) }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" title="Lihat"><i class="ki-filled ki-eye"></i></a>
                                    @can('hutang-piutang.edit')@if(auth()->user()->hasRole('Owner') || in_array($item->status, ['awaiting_approval','pending']))
                                    <a href="{{ route('hutang-piutang.edit', $item) }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" title="Edit"><i class="ki-filled ki-pencil"></i></a>
                                    @endif @endcan
                                    @can('hutang-piutang.delete')@if(auth()->user()->hasRole('Owner') || $item->status === 'awaiting_approval')
                                    <form method="POST" action="{{ route('hutang-piutang.destroy', $item) }}" onsubmit="return confirm('Yakin hapus?')">@csrf @method('DELETE')<button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger"><i class="ki-filled ki-trash"></i></button></form>
                                    @endif @endcan
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
                <h3 class="kt-card-title">Reject Hutang/Piutang</h3>
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
                <textarea id="alasan_reject" name="alasan_reject" class="kt-input mt-2" rows="4" style="height:120px;" placeholder="Tuliskan alasan data ditolak..." required minlength="5"></textarea>
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

<!-- Modal Bayar Hutang/Piutang -->
<div class="kt-modal" data-kt-modal="true" id="bayarModal">
    <div class="kt-modal-content max-w-[420px] top-5 lg:top-[20%]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title">Bayar Hutang/Piutang</h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true"><i class="ki-filled ki-cross"></i></button>
        </div>
        <form id="bayarForm" method="POST">
            @csrf
            @method('PATCH')
            <div class="kt-modal-body flex flex-col gap-4">
                <div class="p-3 rounded-lg bg-accent/40 border border-border">
                    <p class="text-xs text-muted-foreground">No. Transaksi</p>
                    <p class="text-sm font-semibold text-mono" id="bayar_nomor">-</p>
                    <p class="text-xs text-muted-foreground mt-2">Sisa Pembayaran</p>
                    <p class="text-sm font-semibold text-danger" id="bayar_sisa_label">-</p>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-foreground">Jumlah Bayar <span class="text-danger">*</span></label>
                    <div class="kt-input-group">
                        <span class="kt-input-addon">Rp.</span>
                        <input class="kt-input" type="text" id="bayar_jumlah_display" placeholder="0" required/>
                        <input type="hidden" name="jumlah_bayar" id="bayar_jumlah_val" value="0"/>
                    </div>
                    <input type="hidden" name="sisa_sekarang" id="bayar_sisa_val"/>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-foreground">Pembayaran Via</label>
                    <select name="account_bank_id" id="bayar_bank" class="kt-select">
                        <option value="">-- Cash --</option>
                        @foreach(\App\Models\AccountBank::where('status','aktif')->orderBy('nama_bank')->get() as $bank)
                        <option value="{{ $bank->id }}" data-saldo="{{ $bank->saldo }}">
                            {{ $bank->nama_bank }} - {{ $bank->nama_pemilik }} (Rp {{ number_format($bank->saldo, 0, ',', '.') }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-foreground">Catatan</label>
                    <textarea name="catatan_bayar" class="kt-input" rows="2" style="height: 94px;"></textarea>
                </div>
            </div>
            <div class="kt-modal-footer justify-end">
                <button type="button" class="kt-btn kt-btn-outline" data-kt-modal-dismiss="true">Batal</button>
                <button type="submit" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-dollar"></i> Bayar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openBayarModal(id, nomor, sisa, jenis) {
    document.getElementById('bayarForm').action = '/keuangan/hutang-piutang/' + id + '/bayar';
    document.getElementById('bayar_nomor').textContent = nomor;
    document.getElementById('bayar_sisa_label').textContent = 'Rp ' + Number(sisa).toLocaleString('id-ID');
    document.getElementById('bayar_sisa_val').value = sisa;
    document.getElementById('bayar_jumlah_display').value = '';
    document.getElementById('bayar_jumlah_val').value = 0;
    document.getElementById('bayar_bank').value = '';
    KTModal.getInstance(document.querySelector('#bayarModal')).show();
}

document.getElementById('bayar_jumlah_display').addEventListener('input', function() {
    var raw = parseInt(this.value.replace(/\D/g,'')) || 0;
    this.value = raw > 0 ? raw.toLocaleString('id-ID') : '';
    document.getElementById('bayar_jumlah_val').value = raw;
});

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
