@extends('layouts.app')

@section('title', 'Detail Pembelian Persediaan - ' . $pembelianPersediaan->nomor_transaksi)
@section('page-title', 'Detail Pembelian Persediaan')
@section('page-description', $pembelianPersediaan->nomor_transaksi)

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">{{ $pembelianPersediaan->nomor_transaksi }}</h3>
            <div class="flex items-center gap-2">
                @if($pembelianPersediaan->status === 'awaiting_approval' && auth()->user()->hasRole('Owner'))
                <form method="POST" action="{{ route('pembelian-persediaan.approve', $pembelianPersediaan) }}" class="inline">@csrf<button type="submit" class="kt-btn kt-btn-primary kt-btn-sm"><i class="ki-filled ki-check"></i> Approve</button></form>
                <button type="button" class="kt-btn kt-btn-destructive kt-btn-sm" onclick="openRejectModal('{{ route('pembelian-persediaan.reject', $pembelianPersediaan) }}', '{{ e($pembelianPersediaan->nomor_transaksi) }}')"><i class="ki-filled ki-cross"></i> Reject</button>
                @endif
                @if(auth()->user()->hasRole('Owner') || in_array($pembelianPersediaan->status, ['awaiting_approval','pending']))
                @can('pembelian-persediaan.edit')
                <a href="{{ route('pembelian-persediaan.edit', $pembelianPersediaan) }}" class="kt-btn kt-btn-sm kt-btn-outline">
                    <i class="ki-filled ki-pencil"></i> Edit
                </a>
                @endcan
                @endif
                <a href="{{ route('pembelian-persediaan.index') }}" class="kt-btn kt-btn-sm kt-btn-outline">
                    <i class="ki-filled ki-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
        <div class="kt-card-content py-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <table class="kt-table-auto">
                    <tbody>
                        <tr>
                            <td class="text-sm text-secondary-foreground pb-3 pe-8 w-40">No. Transaksi</td>
                            <td class="text-sm text-mono pb-3 font-medium">{{ $pembelianPersediaan->nomor_transaksi }}</td>
                        </tr>
                        <tr>
                            <td class="text-sm text-secondary-foreground pb-3 pe-8">Tanggal Pembelian</td>
                            <td class="text-sm text-mono pb-3">{{ $pembelianPersediaan->tgl_pembelian?->format('d/m/Y') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-sm text-secondary-foreground pb-3 pe-8">Status</td>
                            <td class="text-sm pb-3">
                                @if($pembelianPersediaan->status === 'selesai')
                                    <span class="kt-badge kt-badge-sm kt-badge-success">Selesai</span>
                                @elseif($pembelianPersediaan->status === 'cancel')
                                    <span class="kt-badge kt-badge-sm kt-badge-destructive">Cancel</span>
                                @elseif($pembelianPersediaan->status === 'proses')
                                    <span class="kt-badge kt-badge-sm kt-badge-primary">Proses</span>
                                @elseif($pembelianPersediaan->status === 'pending')
                                    <span class="kt-badge kt-badge-sm kt-badge-warning">Pending</span>
                                @else
                                    <span class="kt-badge kt-badge-sm kt-badge-outline">Awaiting</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table class="kt-table-auto">
                    <tbody>
                        <tr>
                            <td class="text-sm text-secondary-foreground pb-3 pe-8 w-40">Jenis Pembayaran</td>
                            <td class="text-sm pb-3 capitalize">{{ $pembelianPersediaan->jenis_pembayaran }}</td>
                        </tr>
                        <tr>
                            <td class="text-sm text-secondary-foreground pb-3 pe-8">Account Bank</td>
                            <td class="text-sm pb-3">
                                @if($pembelianPersediaan->accountBank)
                                    {{ $pembelianPersediaan->accountBank->nama_bank }} - {{ $pembelianPersediaan->accountBank->nama_pemilik }}
                                @else - @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-sm text-secondary-foreground pb-3 pe-8">Catatan</td>
                            <td class="text-sm pb-3">{{ $pembelianPersediaan->catatan ?? '-' }}</td>
                        </tr>
                        @if($pembelianPersediaan->status === 'cancel' && $pembelianPersediaan->reject_reason)
                        <tr>
                            <td class="text-sm text-secondary-foreground pb-3 pe-8">Alasan Reject</td>
                            <td class="text-sm pb-3">
                                <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium" style="background:rgba(241,65,108,0.12); color:#f1416c;">
                                    {{ $pembelianPersediaan->reject_reason }}
                                </span>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if(!empty($pembelianPersediaan->eviden))
            <div class="mt-4">
                <p class="text-sm font-medium text-foreground mb-2">Eviden</p>
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
                    @foreach($pembelianPersediaan->eviden as $idx => $ev)
                    @php
                        $isPdf = \Illuminate\Support\Str::endsWith(strtolower($ev), ['.pdf']);
                        $isExcel = \Illuminate\Support\Str::endsWith(strtolower($ev), ['.xlsx', '.xls']);
                        $url = \Illuminate\Support\Facades\Storage::url($ev);
                    @endphp
                    @if($isPdf)
                    <a href="{{ $url }}" target="_blank" class="group relative aspect-square rounded-xl border border-border overflow-hidden bg-muted flex flex-col items-center justify-center p-3 hover:shadow-md hover:border-primary/50 transition-all">
                        <i class="ki-filled ki-document text-3xl text-primary mb-2"></i>
                        <span class="text-[10px] text-muted-foreground text-center truncate w-full">PDF</span>
                    </a>
                    @elseif($isExcel)
                    <a href="{{ $url }}" target="_blank" class="group relative aspect-square rounded-xl border border-border overflow-hidden bg-muted flex flex-col items-center justify-center p-3 hover:shadow-md hover:border-primary/50 transition-all">
                        <i class="ki-filled ki-excel text-3xl text-green-600 mb-2"></i>
                        <span class="text-[10px] text-muted-foreground text-center truncate w-full">Excel</span>
                    </a>
                    @else
                    <div class="lb-thumb group relative aspect-square rounded-xl border border-border overflow-hidden bg-muted cursor-pointer hover:ring-2 hover:ring-primary hover:shadow-md transition-all"
                         data-src="{{ $url }}">
                        <img src="{{ $url }}" class="w-full h-full object-cover" alt="Eviden {{ $idx + 1 }}">
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors flex items-center justify-center pointer-events-none">
                            <i class="ki-filled ki-eye text-white text-2xl drop-shadow-lg opacity-0 group-hover:opacity-100 transition-opacity"></i>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Items Table --}}
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">Item Pembelian</h3>
        </div>
        <div class="kt-card-table">
            <div class="kt-table-wrapper kt-scrollable">
                <table class="kt-table">
                    <thead>
                        <tr>
                            <th class="w-12">No</th>
                            <th>Item Persediaan</th>
                            <th>Qty</th>
                            <th>Satuan</th>
                            <th>Harga Satuan</th>
                            <th>Harga Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pembelianPersediaan->items as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $item->itemPersediaan?->kode_item_persediaan }} - {{ $item->itemPersediaan?->deskripsi }}</td>
                            <td class="text-mono">{{ number_format($item->qty, 0, ',', '.') }}</td>
                            <td>{{ $item->satuan }}</td>
                            <td class="text-mono">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                            <td class="text-mono">Rp {{ number_format($item->harga_total, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-end font-semibold">Grand Total</td>
                            <td class="text-mono font-semibold text-primary">Rp {{ number_format($pembelianPersediaan->items->sum('harga_total'), 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
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

{{-- Lightbox Modal --}}
<div id="lb-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.85);align-items:center;justify-content:center;padding:1rem;">
    <button id="lb-close" style="position:absolute;top:1rem;right:1rem;color:#fff;font-size:1.5rem;background:none;border:none;cursor:pointer;">
        <i class="ki-filled ki-cross" style="font-size:1.75rem;"></i>
    </button>
    <img id="lb-img" src="" style="max-width:100%;max-height:90vh;object-fit:contain;border-radius:0.5rem;box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);">
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

document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('lb-modal');
    if (!modal) return;
    var img = document.getElementById('lb-img');
    var closeBtn = document.getElementById('lb-close');

    function open(src) {
        img.src = src;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    function close() {
        modal.style.display = 'none';
        img.src = '';
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.lb-thumb').forEach(function(el) {
        el.addEventListener('click', function() {
            open(this.dataset.src);
        });
    });

    closeBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        close();
    });

    modal.addEventListener('click', function(e) {
        if (e.target === modal || e.target === img) {
            close();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') close();
    });
});
</script>
@endpush
