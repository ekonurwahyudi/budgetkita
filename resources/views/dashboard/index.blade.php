@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-description', 'Ringkasan data keuangan, operasional & budidaya')

@section('content')
@if(!$hasTambak)
{{-- Empty state: belum punya tambak --}}
<div class="flex flex-col items-center justify-center py-20 gap-6">
    <div class="flex items-center justify-center size-[80px] rounded-full bg-primary/10">
        <i class="ki-filled ki-geolocation text-4xl text-primary"></i>
    </div>
    <div class="flex flex-col items-center gap-2 text-center">
        <h2 class="text-xl font-semibold text-mono">Selamat Datang, {{ auth()->user()->nama ?? 'User' }} 👋</h2>
        <p class="text-sm text-secondary-foreground max-w-md">
            Anda belum memiliki tambak. Mulai dengan membuat tambak pertama Anda untuk mengakses fitur dashboard, keuangan, dan budidaya.
        </p>
    </div>
    <button type="button" class="kt-btn kt-btn-primary" onclick="openTambakModal()">
        <i class="ki-filled ki-plus-squared"></i> Buat Tambak Pertama
    </button>
</div>

<!-- Modal Tambah Tambak -->
<div class="kt-modal" data-kt-modal="true" id="tambakModal">
    <div class="kt-modal-content max-w-[600px] top-5 lg:top-[15%]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title">Buat Tambak Baru</h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true"><i class="ki-filled ki-cross"></i></button>
        </div>
        <form method="POST" action="{{ route('tambak.store') }}">
            @csrf
            <div class="kt-modal-body flex flex-col gap-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Nama Tambak <span class="text-danger">*</span></label>
                        <input type="text" name="nama_tambak" class="kt-input" required>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Lokasi <span class="text-danger">*</span></label>
                        <div class="relative">
                            <input type="text" name="lokasi" class="kt-input" autocomplete="off" required placeholder="Ketik nama kecamatan..." oninput="searchLokasi(this)">
                            <div class="lokasi-dropdown hidden absolute w-full mt-1 bg-background border border-border rounded-lg shadow-lg max-h-[200px] overflow-y-auto" style="z-index:9999;"></div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-foreground">Alamat</label>
                    <input type="text" name="alamat" class="kt-input" required>
                    <!-- <textarea name="alamat" class="kt-input" rows="2"></textarea> -->
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Total Lahan (m²)</label>
                        <input type="number" name="total_lahan" class="kt-input" step="0.01" min="0">
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Didirikan Pada <span class="text-danger">*</span></label>
                        <div class="kt-input">
                            <i class="ki-outline ki-calendar"></i>
                            <input class="grow" name="didirikan_pada" data-kt-date-picker="true" data-kt-date-picker-input-mode="true" placeholder="Pilih tanggal" readonly type="text" required/>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-foreground">Catatan</label>
                    <textarea name="catatan" class="kt-input" rows="2" style="height: 94px;"></textarea>
                </div>
            </div>
            <div class="kt-modal-footer justify-end">
                <button type="button" class="kt-btn kt-btn-outline" data-kt-modal-dismiss="true">Batal</button>
                <button type="submit" class="kt-btn kt-btn-primary">Simpan & Mulai</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openTambakModal() {
    KTModal.getInstance(document.querySelector('#tambakModal')).show();
}
</script>
@endpush

@else
{{-- Dashboard normal --}}
<div class="grid gap-5 lg:gap-7.5">
    <div class="grid lg:grid-cols-3 gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-content flex items-center gap-3.5 p-5 lg:p-7.5">
                <div class="flex items-center justify-center size-[50px] shrink-0 rounded-lg bg-success/10">
                    <i class="ki-filled ki-dollar text-xl text-success"></i>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-sm font-normal text-secondary-foreground">Total Pendapatan</span>
                    <span class="text-lg font-semibold text-mono">Rp 0</span>
                </div>
            </div>
        </div>
        <div class="kt-card">
            <div class="kt-card-content flex items-center gap-3.5 p-5 lg:p-7.5">
                <div class="flex items-center justify-center size-[50px] shrink-0 rounded-lg bg-danger/10">
                    <i class="ki-filled ki-arrow-down text-xl text-danger"></i>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-sm font-normal text-secondary-foreground">Total Pengeluaran</span>
                    <span class="text-lg font-semibold text-mono">Rp 0</span>
                </div>
            </div>
        </div>
        <div class="kt-card">
            <div class="kt-card-content flex items-center gap-3.5 p-5 lg:p-7.5">
                <div class="flex items-center justify-center size-[50px] shrink-0 rounded-lg bg-primary/10">
                    <i class="ki-filled ki-chart-line-up text-xl text-primary"></i>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-sm font-normal text-secondary-foreground">Laba / Rugi</span>
                    <span class="text-lg font-semibold text-mono">Rp 0</span>
                </div>
            </div>
        </div>
    </div>
    <div class="grid lg:grid-cols-4 gap-5 lg:gap-7.5">
        <div class="kt-card">
            <div class="kt-card-content flex flex-col items-center gap-2 p-5 lg:py-7.5">
                <i class="ki-filled ki-geolocation text-2xl text-primary"></i>
                <span class="text-2xl font-bold text-mono">0</span>
                <span class="text-sm font-normal text-secondary-foreground">Total Tambak</span>
            </div>
        </div>
        <div class="kt-card">
            <div class="kt-card-content flex flex-col items-center gap-2 p-5 lg:py-7.5">
                <i class="ki-filled ki-grid text-2xl text-info"></i>
                <span class="text-2xl font-bold text-mono">0</span>
                <span class="text-sm font-normal text-secondary-foreground">Total Blok</span>
            </div>
        </div>
        <div class="kt-card">
            <div class="kt-card-content flex flex-col items-center gap-2 p-5 lg:py-7.5">
                <i class="ki-filled ki-arrows-circle text-2xl text-success"></i>
                <span class="text-2xl font-bold text-mono">0</span>
                <span class="text-sm font-normal text-secondary-foreground">Siklus Aktif</span>
            </div>
        </div>
        <div class="kt-card">
            <div class="kt-card-content flex flex-col items-center gap-2 p-5 lg:py-7.5">
                <i class="ki-filled ki-parcel text-2xl text-warning"></i>
                <span class="text-2xl font-bold text-mono">0</span>
                <span class="text-sm font-normal text-secondary-foreground">Stok Persediaan</span>
            </div>
        </div>
    </div>
    <div class="kt-card">
        <div class="kt-card-content p-5 lg:p-7.5">
            <div class="flex flex-col gap-4">
                <h3 class="text-base font-medium text-mono">Selamat Datang, {{ auth()->user()->nama ?? 'User' }} 👋</h3>
                <p class="text-sm text-secondary-foreground leading-5.5">
                    Dashboard ini menampilkan ringkasan data keuangan, operasional, dan budidaya tambak udang Anda.
                </p>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
