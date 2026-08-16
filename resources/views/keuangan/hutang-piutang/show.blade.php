@extends('layouts.app')

@section('title', 'Detail Hutang/Piutang - ' . $hutangPiutang->nomor_transaksi)
@section('page-title', 'Detail Hutang/Piutang')
@section('page-description', $hutangPiutang->nomor_transaksi)

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <div class="flex items-center gap-3">
                <h3 class="kt-card-title">{{ $hutangPiutang->nomor_transaksi }}</h3>
                @if($hutangPiutang->jenis === 'hutang')
                    <span class="kt-badge kt-badge-sm kt-badge-destructive kt-badge-outline">Hutang</span>
                @else
                    <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">Piutang</span>
                @endif
                @if($hutangPiutang->status === 'selesai')
                    <span class="kt-badge kt-badge-sm kt-badge-success">Selesai</span>
                @elseif($hutangPiutang->status === 'cancel')
                    <span class="kt-badge kt-badge-sm kt-badge-destructive">Cancel</span>
                @elseif($hutangPiutang->status === 'proses')
                    <span class="kt-badge kt-badge-sm kt-badge-primary">Proses</span>
                @elseif($hutangPiutang->status === 'pending')
                    <span class="kt-badge kt-badge-sm kt-badge-warning">Pending</span>
                @else
                    <span class="kt-badge kt-badge-sm kt-badge-outline">Awaiting</span>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @if($hutangPiutang->status === 'awaiting_approval' && auth()->user()->hasRole('Owner'))
                <form method="POST" action="{{ route('hutang-piutang.approve', $hutangPiutang) }}" class="inline">@csrf<button type="submit" class="kt-btn kt-btn-primary kt-btn-sm"><i class="ki-filled ki-check"></i> Approve</button></form>
                <button type="button" class="kt-btn kt-btn-destructive kt-btn-sm" onclick="openRejectModal('{{ route('hutang-piutang.reject', $hutangPiutang) }}', '{{ e($hutangPiutang->nomor_transaksi) }}')"><i class="ki-filled ki-cross"></i> Reject</button>
                @endif
                @if(auth()->user()->hasRole('Owner') || in_array($hutangPiutang->status, ['awaiting_approval','pending']))
                @can('hutang-piutang.edit')
                <a href="{{ route('hutang-piutang.edit', $hutangPiutang) }}" class="kt-btn kt-btn-sm kt-btn-outline">
                    <i class="ki-filled ki-pencil"></i> Edit
                </a>
                @endcan
                @endif
                <a href="{{ route('hutang-piutang.index') }}" class="kt-btn kt-btn-sm kt-btn-outline">
                    <i class="ki-filled ki-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
        <div class="kt-card-content py-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <table class="kt-table-auto">
                        <tbody>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8 w-40">No. Transaksi</td>
                                <td class="text-sm text-mono pb-3 font-medium">{{ $hutangPiutang->nomor_transaksi }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Kategori</td>
                                <td class="text-sm pb-3">{{ $hutangPiutang->kategoriHutangPiutang?->deskripsi ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">{{ $hutangPiutang->jenis === 'piutang' ? 'Nama Penerima' : 'Nama Pemberi Hutang' }}</td>
                                <td class="text-sm pb-3">{{ $hutangPiutang->nama_pemberi_hutang ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Aktivitas</td>
                                <td class="text-sm pb-3">{{ $hutangPiutang->aktivitas }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Nominal</td>
                                <td class="text-sm text-mono pb-3 font-semibold">Rp {{ number_format($hutangPiutang->nominal, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Jatuh Tempo</td>
                                <td class="text-sm text-mono pb-3">{{ $hutangPiutang->jatuh_tempo?->format('d/m/Y') ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div>
                    <table class="kt-table-auto">
                        <tbody>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8 w-40">Total Bayar</td>
                                <td class="text-sm text-mono pb-3">Rp {{ number_format($hutangPiutang->total_bayar ?? $hutangPiutang->nominal, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Sudah Dibayar</td>
                                <td class="text-sm text-mono pb-3">Rp {{ number_format($hutangPiutang->nominal_bayar ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Sisa Pembayaran</td>
                                <td class="text-sm text-mono pb-3 font-semibold text-primary">Rp {{ number_format($hutangPiutang->sisa_pembayaran, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Pembayaran</td>
                                <td class="text-sm pb-3 capitalize">{{ $hutangPiutang->jenis_pembayaran }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Account Bank</td>
                                <td class="text-sm pb-3">
                                    @if($hutangPiutang->accountBank)
                                        {{ $hutangPiutang->accountBank->nama_bank }} - {{ $hutangPiutang->accountBank->nama_pemilik }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Catatan</td>
                                <td class="text-sm pb-3">{{ $hutangPiutang->catatan ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Tanggal</td>
                                <td class="text-sm text-mono pb-3">{{ $hutangPiutang->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            </tr>
                            @if($hutangPiutang->status === 'cancel' && $hutangPiutang->reject_reason)
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Alasan Reject</td>
                                <td class="text-sm pb-3">
                                    <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium" style="background:rgba(241,65,108,0.12); color:#f1416c;">
                                        {{ $hutangPiutang->reject_reason }}
                                    </span>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Eviden --}}
    @if(!empty($hutangPiutang->eviden))
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">Eviden</h3>
            <span class="text-sm text-muted-foreground">{{ count($hutangPiutang->eviden) }} file</span>
        </div>
        <div class="kt-card-content py-4">
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
                @foreach($hutangPiutang->eviden as $idx => $ev)
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
    </div>
    @endif
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
