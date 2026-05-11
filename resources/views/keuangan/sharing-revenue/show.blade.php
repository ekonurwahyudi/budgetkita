@extends('layouts.app')

@section('title', 'Detail Sharing Revenue - ' . $sharingRevenue->nomor_transaksi)
@section('page-title', 'Detail Sharing Revenue')
@section('page-description', $sharingRevenue->nomor_transaksi)

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <div class="flex items-center gap-3">
                <h3 class="kt-card-title">{{ $sharingRevenue->nomor_transaksi }}</h3>
                @if($sharingRevenue->status === 'selesai')
                    <span class="kt-badge kt-badge-sm kt-badge-success">Selesai</span>
                @elseif($sharingRevenue->status === 'cancel')
                    <span class="kt-badge kt-badge-sm kt-badge-destructive">Cancel</span>
                @elseif($sharingRevenue->status === 'proses')
                    <span class="kt-badge kt-badge-sm kt-badge-primary">Proses</span>
                @elseif($sharingRevenue->status === 'pending')
                    <span class="kt-badge kt-badge-sm kt-badge-warning">Pending</span>
                @else
                    <span class="kt-badge kt-badge-sm kt-badge-outline">Awaiting</span>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @if($sharingRevenue->status === 'awaiting_approval' && auth()->user()->hasRole('Owner'))
                <form method="POST" action="{{ route('sharing-revenue.approve', $sharingRevenue) }}" class="inline">@csrf<button type="submit" class="kt-btn kt-btn-primary kt-btn-sm"><i class="ki-filled ki-check"></i> Approve</button></form>
                <button type="button" class="kt-btn kt-btn-destructive kt-btn-sm" onclick="openRejectModal('{{ route('sharing-revenue.reject', $sharingRevenue) }}', '{{ e($sharingRevenue->nomor_transaksi) }}')"><i class="ki-filled ki-cross"></i> Reject</button>
                @endif
                @if(auth()->user()->hasRole('Owner') || in_array($sharingRevenue->status, ['awaiting_approval','pending']))
                @can('sharing-revenue.edit')
                <a href="{{ route('sharing-revenue.edit', $sharingRevenue) }}" class="kt-btn kt-btn-sm kt-btn-outline">
                    <i class="ki-filled ki-pencil"></i> Edit
                </a>
                @endcan
                @endif
                <a href="{{ route('sharing-revenue.index') }}" class="kt-btn kt-btn-sm kt-btn-outline">
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
                                <td class="text-sm text-mono pb-3 font-medium">{{ $sharingRevenue->nomor_transaksi }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Nama Penerima</td>
                                <td class="text-sm pb-3 font-medium">{{ $sharingRevenue->nama_penerima }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Blok</td>
                                <td class="text-sm pb-3">{{ $sharingRevenue->blok?->nama_blok ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Siklus</td>
                                <td class="text-sm pb-3">{{ $sharingRevenue->siklus?->nama_siklus ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Keuntungan Siklus</td>
                                <td class="text-sm text-mono pb-3 font-semibold {{ $sharingRevenue->total_keuntungan >= 0 ? 'text-success' : 'text-danger' }}">
                                    Rp {{ number_format($sharingRevenue->total_keuntungan, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div>
                    <table class="kt-table-auto">
                        <tbody>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8 w-40">Persentase</td>
                                <td class="text-sm text-mono pb-3">{{ number_format($sharingRevenue->persentase, 2, ',', '.') }}%</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Nominal Sharing</td>
                                <td class="text-sm text-mono pb-3 font-semibold text-primary">Rp {{ number_format($sharingRevenue->nominal, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Pembayaran</td>
                                <td class="text-sm pb-3 capitalize">{{ $sharingRevenue->jenis_pembayaran }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Account Bank</td>
                                <td class="text-sm pb-3">
                                    @if($sharingRevenue->accountBank)
                                        {{ $sharingRevenue->accountBank->nama_bank }} - {{ $sharingRevenue->accountBank->nama_pemilik }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Catatan</td>
                                <td class="text-sm pb-3">{{ $sharingRevenue->catatan ?? '-' }}</td>
                            </tr>
                            @if($sharingRevenue->status === 'cancel' && $sharingRevenue->reject_reason)
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Alasan Reject</td>
                                <td class="text-sm pb-3">
                                    <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium" style="background:rgba(241,65,108,0.12); color:#f1416c;">
                                        {{ $sharingRevenue->reject_reason }}
                                    </span>
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Tanggal Input</td>
                                <td class="text-sm text-mono pb-3">{{ $sharingRevenue->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($sharingRevenue->eviden))
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">Eviden</h3>
            <span class="text-sm text-muted-foreground">{{ count($sharingRevenue->eviden) }} file</span>
        </div>
        <div class="kt-card-content py-4">
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
                @foreach($sharingRevenue->eviden as $idx => $ev)
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
        if (e.target === modal || e.target === img) close();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') close();
    });
});
</script>
@endpush
