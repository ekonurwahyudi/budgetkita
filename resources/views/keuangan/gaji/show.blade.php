@extends('layouts.app')

@section('title', 'Detail Gaji - ' . $gaji->nomor_transaksi)
@section('page-title', 'Detail Gaji Karyawan')
@section('page-description', $gaji->nomor_transaksi)

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <div class="flex items-center gap-3">
                <h3 class="kt-card-title">{{ $gaji->nomor_transaksi }}</h3>
                @if($gaji->status === 'selesai')
                    <span class="kt-badge kt-badge-sm kt-badge-success">Selesai</span>
                @elseif($gaji->status === 'cancel')
                    <span class="kt-badge kt-badge-sm kt-badge-destructive">Cancel</span>
                @elseif($gaji->status === 'proses')
                    <span class="kt-badge kt-badge-sm kt-badge-primary">Proses</span>
                @elseif($gaji->status === 'pending')
                    <span class="kt-badge kt-badge-sm kt-badge-warning">Pending</span>
                @else
                    <span class="kt-badge kt-badge-sm kt-badge-outline">Awaiting</span>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @if(auth()->user()->hasRole('Owner') || in_array($gaji->status, ['awaiting_approval','pending']))
                @can('gaji-karyawan.edit')
                <a href="{{ route('gaji.edit', $gaji) }}" class="kt-btn kt-btn-sm kt-btn-outline">
                    <i class="ki-filled ki-pencil"></i> Edit
                </a>
                @endcan
                @endif
                <a href="{{ route('gaji.index') }}" class="kt-btn kt-btn-sm kt-btn-outline">
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
                                <td class="text-sm text-mono pb-3 font-medium">{{ $gaji->nomor_transaksi }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Karyawan</td>
                                <td class="text-sm pb-3">{{ $gaji->user?->nama ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Jabatan</td>
                                <td class="text-sm pb-3">{{ $gaji->user?->jabatan ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Gaji Pokok</td>
                                <td class="text-sm text-mono pb-3 font-semibold">Rp {{ number_format($gaji->gaji_pokok, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Upah Lembur</td>
                                <td class="text-sm text-mono pb-3">Rp {{ number_format($gaji->upah_lembur ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Bonus</td>
                                <td class="text-sm text-mono pb-3">Rp {{ number_format($gaji->bonus ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div>
                    <table class="kt-table-auto">
                        <tbody>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8 w-40">Pajak</td>
                                <td class="text-sm text-mono pb-3">Rp {{ number_format($gaji->pajak ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">BPJS</td>
                                <td class="text-sm text-mono pb-3">Rp {{ number_format($gaji->bpjs ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Potongan</td>
                                <td class="text-sm text-mono pb-3">Rp {{ number_format($gaji->potongan ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">THP</td>
                                <td class="text-sm text-mono pb-3 font-semibold text-primary">Rp {{ number_format($gaji->thp, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Pembayaran</td>
                                <td class="text-sm pb-3 capitalize">{{ $gaji->jenis_pembayaran }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Account Bank</td>
                                <td class="text-sm pb-3">
                                    @if($gaji->accountBank)
                                        {{ $gaji->accountBank->nama_bank }} - {{ $gaji->accountBank->nama_pemilik }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Tanggal</td>
                                <td class="text-sm text-mono pb-3">{{ $gaji->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Eviden --}}
    @if(!empty($gaji->eviden))
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">Eviden</h3>
            <span class="text-sm text-muted-foreground">{{ count($gaji->eviden) }} file</span>
        </div>
        <div class="kt-card-content py-4">
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
                @foreach($gaji->eviden as $idx => $ev)
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