@extends('layouts.app')

@section('title', 'Detail Pembelian Aset - ' . $pembelianAset->nama_aset)
@section('page-title', 'Detail Pembelian Aset')
@section('page-description', $pembelianAset->nama_aset)

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <div class="flex items-center gap-3">
                <h3 class="kt-card-title">{{ $pembelianAset->nama_aset }}</h3>
                @if($pembelianAset->status === 'selesai')
                    <span class="kt-badge kt-badge-sm kt-badge-success">Selesai</span>
                @elseif($pembelianAset->status === 'cancel')
                    <span class="kt-badge kt-badge-sm kt-badge-destructive">Cancel</span>
                @elseif($pembelianAset->status === 'proses')
                    <span class="kt-badge kt-badge-sm kt-badge-primary">Proses</span>
                @elseif($pembelianAset->status === 'pending')
                    <span class="kt-badge kt-badge-sm kt-badge-warning">Pending</span>
                @else
                    <span class="kt-badge kt-badge-sm kt-badge-outline">Awaiting</span>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @if(auth()->user()->hasRole('Owner') || in_array($pembelianAset->status, ['awaiting_approval','pending']))
                @can('pembelian-aset.edit')
                <a href="{{ route('pembelian-aset.edit', $pembelianAset) }}" class="kt-btn kt-btn-sm kt-btn-outline">
                    <i class="ki-filled ki-pencil"></i> Edit
                </a>
                @endcan
                @endif
                <a href="{{ route('pembelian-aset.index') }}" class="kt-btn kt-btn-sm kt-btn-outline">
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
                                <td class="text-sm text-secondary-foreground pb-3 pe-8 w-40">Nama Aset</td>
                                <td class="text-sm pb-3 font-medium">{{ $pembelianAset->nama_aset }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Kategori</td>
                                <td class="text-sm pb-3">{{ $pembelianAset->kategoriAset?->deskripsi ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Tanggal Pembelian</td>
                                <td class="text-sm text-mono pb-3">{{ $pembelianAset->tgl_pembelian?->format('d/m/Y') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Nominal Pembelian</td>
                                <td class="text-sm text-mono pb-3 font-semibold">Rp {{ number_format($pembelianAset->nominal_pembelian, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Umur Manfaat</td>
                                <td class="text-sm pb-3">{{ $pembelianAset->umur_manfaat }} Tahun</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div>
                    <table class="kt-table-auto">
                        <tbody>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8 w-40">Nilai Residu</td>
                                <td class="text-sm text-mono pb-3">Rp {{ number_format($pembelianAset->nilai_residu, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Jenis Pembayaran</td>
                                <td class="text-sm pb-3 capitalize">{{ $pembelianAset->jenis_pembayaran }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Account Bank</td>
                                <td class="text-sm pb-3">
                                    @if($pembelianAset->accountBank)
                                        {{ $pembelianAset->accountBank->nama_bank }} - {{ $pembelianAset->accountBank->nama_pemilik }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Catatan</td>
                                <td class="text-sm pb-3">{{ $pembelianAset->catatan ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Depresiasi Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="kt-card">
            <div class="kt-card-content py-4 flex items-center gap-3">
                <div class="size-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                    <i class="ki-filled ki-calculator text-primary text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Depresiasi / Tahun</p>
                    <p class="text-base font-semibold text-mono">Rp {{ number_format($pembelianAset->depresiasi_per_tahun, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="kt-card">
            <div class="kt-card-content py-4 flex items-center gap-3">
                <div class="size-10 rounded-full bg-info/10 flex items-center justify-center shrink-0">
                    <i class="ki-filled ki-chart-line text-info text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Umur Berjalan</p>
                    <p class="text-base font-semibold">{{ $pembelianAset->umur_berjalan }} Tahun</p>
                </div>
            </div>
        </div>
        <div class="kt-card">
            <div class="kt-card-content py-4 flex items-center gap-3">
                <div class="size-10 rounded-full bg-warning/10 flex items-center justify-center shrink-0">
                    <i class="ki-filled ki-graph-up text-warning text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Akumulasi Depresiasi</p>
                    <p class="text-base font-semibold text-mono">Rp {{ number_format($pembelianAset->akumulasi_depresiasi, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="kt-card">
            <div class="kt-card-content py-4 flex items-center gap-3">
                <div class="size-10 rounded-full bg-success/10 flex items-center justify-center shrink-0">
                    <i class="ki-filled ki-dollar text-success text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Nilai Buku</p>
                    <p class="text-base font-semibold text-success text-mono">Rp {{ number_format($pembelianAset->nilai_buku_aset, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Eviden --}}
    @if(!empty($pembelianAset->eviden))
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">Eviden</h3>
            <span class="text-sm text-muted-foreground">{{ count($pembelianAset->eviden) }} file</span>
        </div>
        <div class="kt-card-content py-4">
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
                @foreach($pembelianAset->eviden as $idx => $ev)
                @php
                    $isPdf = \Illuminate\Support\Str::endsWith(strtolower($ev), ['.pdf']);
                    $url = \Illuminate\Support\Facades\Storage::url($ev);
                @endphp
                @if($isPdf)
                <a href="{{ $url }}" target="_blank" class="group relative aspect-square rounded-xl border border-border overflow-hidden bg-muted flex flex-col items-center justify-center p-3 hover:shadow-md hover:border-primary/50 transition-all">
                    <i class="ki-filled ki-document text-3xl text-primary mb-2"></i>
                    <span class="text-[10px] text-muted-foreground text-center truncate w-full">PDF</span>
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