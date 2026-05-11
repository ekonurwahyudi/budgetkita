@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-description', 'Ringkasan data keuangan, operasional & budidaya')

@push('styles')
<style>
    .dashboard-finance-grid {
        display: grid;
        grid-template-columns: 1fr;
    }

    .dashboard-finance-counting,
    .dashboard-finance-chart {
        min-width: 0;
    }

    @media (min-width: 1024px) {
        .dashboard-finance-grid {
            grid-template-columns: repeat(12, minmax(0, 1fr));
        }

        .dashboard-finance-counting {
            grid-column: span 4 / span 4;
        }

        .dashboard-finance-chart {
            grid-column: span 8 / span 8;
        }
    }
</style>
@endpush

@section('page-actions')
<div class="relative" id="filterWrapper">
    <button type="button" onclick="toggleFilter()" class="kt-btn kt-btn-outline kt-btn-sm">
        <i class="ki-filled ki-filter-search"></i> Filter
    </button>
    <div id="filterPanel" class="hidden absolute right-0 top-full mt-2 w-80 bg-background border border-border rounded-xl shadow-lg p-4" style="z-index:9999;">
        <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col gap-3">
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-medium text-muted-foreground">Blok</label>
                <select name="blok_id" id="filterBlok" class="kt-select kt-select-sm" onchange="loadSiklusForBlok()">
                    <option value="">Semua Blok</option>
                    @foreach($bloks as $b)
                    <option value="{{ $b->id }}" {{ $filterBlok == $b->id ? 'selected' : '' }}>{{ $b->nama_blok }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-medium text-muted-foreground">Siklus</label>
                <select name="siklus_id" id="filterSiklus" class="kt-select kt-select-sm">
                    <option value="">Semua Siklus</option>
                    @foreach($sikluses as $s)
                    <option value="{{ $s->id }}" {{ $filterSiklus == $s->id ? 'selected' : '' }}>{{ $s->nama_siklus }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-medium text-muted-foreground">Dari Tanggal</label>
                <div class="kt-input kt-input-sm">
                    <i class="ki-outline ki-calendar"></i>
                    <input class="grow" name="date_from" data-kt-date-picker="true" data-kt-date-picker-input-mode="true"
                           placeholder="Pilih tanggal" readonly type="text" value="{{ $filterDateFrom ?? '' }}"/>
                </div>
            </div>
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-medium text-muted-foreground">Sampai Tanggal</label>
                <div class="kt-input kt-input-sm">
                    <i class="ki-outline ki-calendar"></i>
                    <input class="grow" name="date_to" data-kt-date-picker="true" data-kt-date-picker-input-mode="true"
                           placeholder="Pilih tanggal" readonly type="text" value="{{ $filterDateTo ?? '' }}"/>
                </div>
            </div>
            <div class="flex items-center gap-2 pt-2 border-t border-border">
                <button type="submit" class="kt-btn kt-btn-primary kt-btn-sm flex-1">Terapkan</button>
                @if($filterBlok || $filterSiklus || $filterDateFrom || $filterDateTo)
                <a href="{{ route('dashboard') }}" class="kt-btn kt-btn-outline kt-btn-sm">Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection

@section('content')
@if(!$hasTambak)
<div class="flex flex-col items-center justify-center py-20 gap-6">
    <div class="flex items-center justify-center size-[80px] rounded-full bg-primary/10">
        <i class="ki-filled ki-geolocation text-4xl text-primary"></i>
    </div>
    <div class="flex flex-col items-center gap-2 text-center">
        <h2 class="text-xl font-semibold text-mono">Selamat Datang, {{ auth()->user()->nama ?? 'User' }} 👋</h2>
        <p class="text-sm text-secondary-foreground max-w-md">Anda belum memiliki tambak. Mulai dengan membuat tambak pertama Anda.</p>
    </div>
    <button type="button" class="kt-btn kt-btn-primary" onclick="KTModal.getInstance(document.querySelector('#tambakModal')).show()">
        <i class="ki-filled ki-plus-squared"></i> Buat Tambak Pertama
    </button>
</div>
<div class="kt-modal" data-kt-modal="true" id="tambakModal">
    <div class="kt-modal-content max-w-[600px] top-5 lg:top-[15%]">
        <div class="kt-modal-header"><h3 class="kt-modal-title">Buat Tambak Baru</h3><button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true"><i class="ki-filled ki-cross"></i></button></div>
        <form method="POST" action="{{ route('tambak.store') }}">@csrf
            <div class="kt-modal-body flex flex-col gap-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1"><label class="text-sm font-medium">Nama Tambak <span class="text-danger">*</span></label><input type="text" name="nama_tambak" class="kt-input" required></div>
                    <div class="flex flex-col gap-1"><label class="text-sm font-medium">Lokasi <span class="text-danger">*</span></label><div class="relative"><input type="text" name="lokasi" class="kt-input" autocomplete="off" required placeholder="Ketik nama kecamatan..." oninput="searchLokasi(this)"><div class="lokasi-dropdown hidden absolute w-full mt-1 bg-background border border-border rounded-lg shadow-lg max-h-[200px] overflow-y-auto" style="z-index:9999;"></div></div></div>
                </div>
                <div class="flex flex-col gap-1"><label class="text-sm font-medium">Alamat</label><input type="text" name="alamat" class="kt-input" required></div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1"><label class="text-sm font-medium">Total Lahan (m²)</label><input type="number" name="total_lahan" class="kt-input" step="0.01" min="0"></div>
                    <div class="flex flex-col gap-1"><label class="text-sm font-medium">Didirikan Pada <span class="text-danger">*</span></label><div class="kt-input"><i class="ki-outline ki-calendar"></i><input class="grow" name="didirikan_pada" data-kt-date-picker="true" data-kt-date-picker-input-mode="true" placeholder="Pilih tanggal" readonly type="text" required/></div></div>
                </div>
                <div class="flex flex-col gap-1"><label class="text-sm font-medium">Catatan</label><textarea name="catatan" class="kt-input" rows="2" style="height:94px;"></textarea></div>
            </div>
            <div class="kt-modal-footer justify-end"><button type="button" class="kt-btn kt-btn-outline" data-kt-modal-dismiss="true">Batal</button><button type="submit" class="kt-btn kt-btn-primary">Simpan & Mulai</button></div>
        </form>
    </div>
</div>

@else
<div class="flex flex-col gap-5 lg:gap-7.5">

    {{-- Row 1: 4 counting + 8 chart --}}
    <div class="dashboard-finance-grid gap-5 lg:gap-7.5">
        <div class="dashboard-finance-counting grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-4">
            {{-- Total Investasi --}}
            <a href="{{ route('investasi.index') }}" class="kt-card group block hover:ring-2 hover:ring-primary/30 transition-all">
                <div class="kt-card-content p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex items-center justify-center size-10 rounded-xl shrink-0" style="background:rgba(114,57,234,0.12);">
                                <i class="ki-filled ki-graph-up text-lg" style="color:#7239ea;"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-lg font-bold text-mono leading-tight truncate">Rp {{ number_format($totalInvestasi, 0, ',', '.') }}</p>
                                <p class="text-xs text-secondary-foreground">Total Investasi</p>
                            </div>
                        </div>
                        <span class="text-xs font-medium text-primary opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">Lihat Data <i class="ki-filled ki-arrow-right text-[10px]"></i></span>
                    </div>
                </div>
            </a>
            {{-- Total Pendapatan --}}
            <div class="kt-card hover:ring-2 hover:ring-green-500/30 transition-all cursor-pointer">
                <div class="kt-card-content p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex items-center justify-center size-10 rounded-xl shrink-0" style="background:rgba(23,198,83,0.12);">
                                <i class="ki-filled ki-dollar text-lg" style="color:#17c653;"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-lg font-bold text-mono text-green-600 leading-tight truncate">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
                                <p class="text-xs text-secondary-foreground">Total Pendapatan</p>
                            </div>
                        </div>
                        <button type="button" onclick="event.stopPropagation(); openTransactionModal('pendapatan');" class="text-xs font-medium text-green-600 hover:underline whitespace-nowrap">Lihat Data <i class="ki-filled ki-arrow-right text-[10px]"></i></button>
                    </div>
                </div>
            </div>
            {{-- Total Pengeluaran --}}
            <div class="kt-card hover:ring-2 hover:ring-red-500/30 transition-all cursor-pointer">
                <div class="kt-card-content p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex items-center justify-center size-10 rounded-xl shrink-0" style="background:rgba(241,65,108,0.12);">
                                <i class="ki-filled ki-minus-circle text-lg" style="color:#f1416c;"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-lg font-bold text-mono text-red-600 leading-tight truncate">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</p>
                                <p class="text-xs text-secondary-foreground">Total Pengeluaran</p>
                            </div>
                        </div>
                        <button type="button" onclick="event.stopPropagation(); openTransactionModal('pengeluaran');" class="text-xs font-medium text-red-600 hover:underline whitespace-nowrap">Lihat Data <i class="ki-filled ki-arrow-right text-[10px]"></i></button>
                    </div>
                </div>
            </div>
            {{-- Total Revenue --}}
            <div class="kt-card">
                <div class="kt-card-content p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex items-center justify-center size-10 rounded-xl shrink-0" style="background:rgba(0,158,247,0.12);">
                                <i class="ki-filled ki-chart-line-up text-lg" style="color:#009ef7;"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-lg font-bold text-mono {{ $labaRugi >= 0 ? 'text-green-600' : 'text-red-600' }} leading-tight truncate">
                                    {{ $labaRugi >= 0 ? '+' : '' }}Rp {{ number_format($labaRugi, 0, ',', '.') }}
                                </p>
                                <p class="text-xs text-secondary-foreground">Total Revenue</p>
                            </div>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full shrink-0 {{ $labaRugi >= 0 ? 'text-[#17c653] bg-[#17c653]/10' : 'text-[#f1416c] bg-[#f1416c]/10' }}">
                            {{ $labaRugi >= 0 ? 'Laba' : 'Rugi' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pendapatan vs Pengeluaran chart --}}
        <div class="dashboard-finance-chart kt-card">
            <div class="kt-card-header border-b border-border flex items-center justify-between gap-4">
                <div>
                    <h3 class="kt-card-title">Selama 1 Tahun</h3>
                    <p class="text-xs text-secondary-foreground mt-1">Pemasukan vs Pengeluaran</p>
                </div>
                <div class="flex items-center gap-4 text-sm text-[#536477]">
                    <span class="flex items-center gap-2 whitespace-nowrap">
                        <span class="size-3.5 rounded-full inline-block" style="background:#2563eb;"></span>
                        Pemasukan
                    </span>
                    <span class="flex items-center gap-2 whitespace-nowrap">
                        <span class="size-3.5 rounded-full inline-block" style="background:#f45b11;"></span>
                        Pengeluaran
                    </span>
                </div>
            </div>
            <div class="kt-card-content p-4">
                <div id="chart_pendapatan_pengeluaran"></div>
            </div>
        </div>
    </div>
    {{-- Row 2: Financial summaries --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-7.5">
        {{-- Highlights panel --}}
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Ringkasan Keuangan</h3>
            </div>
            <div class="kt-card-content p-5 flex flex-col gap-4">
                <div>
                    <!-- <p class="text-xs text-secondary-foreground mb-1">Laba / Rugi Bersih</p> -->
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-bold text-mono {{ $labaRugi >= 0 ? 'text-[#17c653]' : 'text-[#f1416c]' }}">
                            {{ $labaRugi >= 0 ? '+' : '-' }} Rp {{ number_format(abs($labaRugi)/1000000, 1, ',', '.') }}jt
                        </span>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $labaRugi >= 0 ? 'text-[#17c653] bg-[#17c653]/10' : 'text-[#f1416c] bg-[#f1416c]/10' }}">
                            {{ $labaRugi >= 0 ? 'Laba' : 'Rugi' }}
                        </span>
                    </div>
                </div>
                @php
                    $total = $totalPendapatan + $totalPengeluaran;
                    $pctPendapatan = $total > 0 ? round($totalPendapatan / $total * 100) : 0;
                    $pctPengeluaran = $total > 0 ? round($totalPengeluaran / $total * 100) : 0;
                @endphp
                <div class="flex gap-1 h-2 rounded-full overflow-hidden">
                    <div class="rounded-full" style="width:{{ $pctPendapatan }}%; background:#17c653;"></div>
                    <div class="rounded-full" style="width:{{ $pctPengeluaran }}%; background:#f1416c;"></div>
                </div>
                <div class="flex items-center gap-4 text-xs">
                    <span class="flex items-center gap-1.5"><span class="size-2 rounded-full inline-block" style="background:#17c653;"></span> Pendapatan {{ $pctPendapatan }}%</span>
                    <span class="flex items-center gap-1.5"><span class="size-2 rounded-full inline-block" style="background:#f1416c;"></span> Pengeluaran {{ $pctPengeluaran }}%</span>
                </div>
                <div class="border-t border-border pt-4 flex flex-col gap-3">
                    <!-- <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm"><i class="ki-filled ki-geolocation text-primary"></i> Tambak</div>
                        <span class="text-sm font-semibold text-mono">{{ $totalTambak }}</span>
                    </div> -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm"><i class="ki-filled ki-grid text-[#009ef7]"></i> Blok</div>
                        <span class="text-sm font-semibold text-mono">{{ $totalBlok }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm"><i class="ki-filled ki-abstract-28 text-[#009ef7]"></i> Kolam Aktif</div>
                        <span class="text-sm font-semibold text-mono">{{ $kolamAktif }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm"><i class="ki-filled ki-arrows-circle text-[#17c653]"></i> Siklus Aktif</div>
                        <span class="text-sm font-semibold text-mono">{{ $siklusAktif }}</span>
                    </div>
                    <!-- <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm"><i class="ki-filled ki-parcel text-[#ffc700]"></i> Stok Item</div>
                        <span class="text-sm font-semibold text-mono">{{ $stokPersediaanCount }}</span>
                    </div> -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm"><i class="ki-filled ki-bank text-[#7239ea]"></i> Nilai Aset</div>
                        <span class="text-sm font-semibold text-mono">Rp {{ number_format($nilaiAset/1000000, 1, ',', '.') }}jt</span>
                    </div>
                    <div class="border-t border-border pt-3 mt-1">
                        <p class="text-xs text-muted-foreground mb-1">Total Revenue</p>
                        <p class="text-lg font-bold text-mono {{ $labaRugi >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $labaRugi >= 0 ? '+' : '' }}Rp {{ number_format($labaRugi, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Hutang & Piutang --}}
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Hutang & Piutang</h3>
            </div>
            <div class="kt-card-content p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2 gap-4">
                <div class="rounded-lg border border-border p-4">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <span class="text-xs text-secondary-foreground">Total Hutang</span>
                        <span class="flex items-center justify-center size-8 rounded-lg" style="background:rgba(241,65,108,0.12);">
                            <i class="ki-filled ki-arrow-down text-sm" style="color:#f1416c;"></i>
                        </span>
                    </div>
                    <p class="text-sm 2xl:text-base font-bold text-mono text-red-600 leading-tight whitespace-nowrap">Rp {{ number_format($totalHutang, 0, ',', '.') }}</p>
                </div>
                <div class="rounded-lg border border-border p-4">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <span class="text-xs text-secondary-foreground">Sisa Hutang</span>
                        <span class="flex items-center justify-center size-8 rounded-lg" style="background:rgba(245,91,17,0.12);">
                            <i class="ki-filled ki-time text-sm" style="color:#f45b11;"></i>
                        </span>
                    </div>
                    <p class="text-sm 2xl:text-base font-bold text-mono leading-tight whitespace-nowrap">Rp {{ number_format($sisaHutang, 0, ',', '.') }}</p>
                </div>
                <div class="rounded-lg border border-border p-4">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <span class="text-xs text-secondary-foreground">Total Piutang</span>
                        <span class="flex items-center justify-center size-8 rounded-lg" style="background:rgba(23,198,83,0.12);">
                            <i class="ki-filled ki-arrow-up text-sm" style="color:#17c653;"></i>
                        </span>
                    </div>
                    <p class="text-sm 2xl:text-base font-bold text-mono text-green-600 leading-tight whitespace-nowrap">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</p>
                </div>
                <div class="rounded-lg border border-border p-4">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <span class="text-xs text-secondary-foreground">Sisa Piutang</span>
                        <span class="flex items-center justify-center size-8 rounded-lg" style="background:rgba(37,99,235,0.12);">
                            <i class="ki-filled ki-calendar-tick text-sm" style="color:#2563eb;"></i>
                        </span>
                    </div>
                    <p class="text-sm 2xl:text-base font-bold text-mono leading-tight whitespace-nowrap">Rp {{ number_format($sisaPiutang, 0, ',', '.') }}</p>
                </div>
                <div class="rounded-lg border border-border p-4">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <span class="text-xs text-secondary-foreground">Telat Bayar</span>
                        <span class="flex items-center justify-center size-8 rounded-lg" style="background:rgba(241,65,108,0.12);">
                            <i class="ki-filled ki-time text-sm" style="color:#f1416c;"></i>
                        </span>
                    </div>
                    <p class="text-sm 2xl:text-base font-bold text-mono text-red-600 leading-tight whitespace-nowrap">Rp {{ number_format($hutangPiutangTelatNominal, 0, ',', '.') }}</p>
                </div>
                <div class="rounded-lg border border-border p-4">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <span class="text-xs text-secondary-foreground">Deadline</span>
                        <span class="flex items-center justify-center size-8 rounded-lg" style="background:rgba(255,199,0,0.16);">
                            <i class="ki-filled ki-calendar-tick text-sm" style="color:#f6b100;"></i>
                        </span>
                    </div>
                    <p class="text-sm 2xl:text-base font-bold text-mono text-warning leading-tight whitespace-nowrap">Rp {{ number_format($hutangPiutangDeadlineNominal, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        {{-- Transaction categories --}}
        <div class="kt-card">
            <div class="kt-card-header">
                <div>
                    <h3 class="kt-card-title">Pengeluaran per Kategori</h3>
                    <p class="text-xs text-secondary-foreground mt-0.5">Pengeluaran berdasarkan kategori</p>
                </div>
                <a href="{{ route('laporan-keuangan.index') }}" class="kt-btn kt-btn-sm kt-btn-outline">Lihat Semua</a>
            </div>
            <div class="kt-card-content p-5">
                @if($pengeluaranKategori->count())
                @php
                    $maxKategoriTotal = max((float) $pengeluaranKategori->max('total'), 1);
                    $kategoriColors = ['#2563eb', '#3b82f6', '#4f67c7', '#7db7f0', '#9ecdf5', '#6b7a8a'];
                @endphp
                <div class="flex flex-col gap-4">
                    @foreach($pengeluaranKategori->take(6) as $kategori)
                    @php
                        $kategoriPct = round(((float) $kategori['total'] / $maxKategoriTotal) * 100);
                        $kategoriColor = $kategoriColors[$loop->index % count($kategoriColors)];
                    @endphp
                    <div class="grid items-center gap-3" style="grid-template-columns:minmax(90px,1fr) minmax(90px,1.6fr) auto;">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="size-2 rounded-sm shrink-0" style="background:{{ $kategoriColor }};"></span>
                            <span class="text-xs text-secondary-foreground truncate">{{ $kategori['kategori'] }}</span>
                        </div>
                        <div class="h-1.5 rounded-full bg-muted overflow-hidden">
                            <div class="h-full rounded-full" style="width:{{ $kategoriPct }}%; background:{{ $kategoriColor }};"></div>
                        </div>
                        <span class="text-xs font-semibold text-mono whitespace-nowrap">Rp {{ number_format($kategori['total'], 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="py-8 text-center text-muted-foreground text-sm">Belum ada data kategori transaksi</div>
                @endif
            </div>
        </div>

    </div>

    {{-- Row 3: Stok Persediaan Table --}}
    @php
        $formatStok = fn($value) => rtrim(rtrim(number_format((float) ($value ?? 0), 2, ',', '.'), '0'), ',');
    @endphp
    <div class="kt-card">
        <div class="kt-card-header">
            <div>
                <h3 class="kt-card-title">Stok Persediaan</h3>
                <p class="text-xs text-secondary-foreground mt-0.5">Prioritas item yang mendekati minimum stok</p>
            </div>
            <a href="{{ route('persediaan.index') }}" class="kt-btn kt-btn-sm kt-btn-outline">
                Lihat Semua <i class="ki-filled ki-arrow-right text-xs"></i>
            </a>
        </div>
        <div class="kt-card-content p-4">
            @if($stokPersediaan->count())
            <div class="overflow-x-auto">
                <table class="kt-table kt-table-border w-full">
                    <thead>
                        <tr>
                            <th class="text-center text-xs font-semibold w-10">No.</th>
                            <th class="text-left text-xs font-semibold">Kategori</th>
                            <th class="text-left text-xs font-semibold">Item</th>
                            <th class="text-right text-xs font-semibold">Stok</th>
                            <th class="text-right text-xs font-semibold">Minimum</th>
                            <th class="text-center text-xs font-semibold">Satuan</th>
                            <th class="text-center text-xs font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stokPersediaan as $item)
                        @php
                            $minimumStok = $item->minimum_stok;
                            $hasMinimumStok = $minimumStok !== null && (float) $minimumStok > 0;
                            $isLow = $hasMinimumStok && (float) $item->qty <= (float) $minimumStok;
                        @endphp
                        <tr>
                            <td class="text-center text-sm">{{ $loop->iteration }}</td>
                            <td class="text-sm">{{ $item->itemPersediaan?->kategoriPersediaan?->deskripsi ?? '-' }}</td>
                            <td class="text-sm font-medium">{{ $item->itemPersediaan?->deskripsi ?? '-' }}</td>
                            <td class="text-right text-sm text-mono font-semibold">{{ $formatStok($item->qty) }}</td>
                            <td class="text-right text-sm text-mono">{{ $item->minimum_stok !== null ? $formatStok($item->minimum_stok) : '-' }}</td>
                            <td class="text-center text-sm">{{ $item->unit ?? '-' }}</td>
                            <td class="text-center">
                                @if($isLow)
                                    <span class="kt-badge kt-badge-sm kt-badge-destructive">Kurang</span>
                                @elseif($hasMinimumStok)
                                    <span class="kt-badge kt-badge-sm kt-badge-success">Aman</span>
                                @else
                                    <span class="kt-badge kt-badge-sm kt-badge-outline">Belum Diatur</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="py-8 text-center text-muted-foreground text-sm">Belum ada stok persediaan</div>
            @endif
        </div>
    </div>

    {{-- Row 4: Account Bank Cards --}}
    <div class="kt-card">
        <div class="kt-card-header">
            <div>
                <h3 class="kt-card-title">Saldo Rekening</h3>
                <p class="text-xs text-secondary-foreground mt-0.5">Total saldo aktif Rp {{ number_format($totalSaldoBank, 0, ',', '.') }}</p>
            </div>
            <a href="{{ route('account-bank.index') }}" class="kt-btn kt-btn-sm kt-btn-outline">
                Kelola Rekening <i class="ki-filled ki-arrow-right text-xs"></i>
            </a>
        </div>
        <div class="kt-card-content p-4">
            @if($accountBanks->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($accountBanks as $bank)
                @php
                    $bankName = strtoupper($bank->nama_bank ?? 'BANK');
                    $palette = str_contains($bankName, 'BCA') ? ['#1d4ed8', '#dbeafe', '#ffffff'] :
                        (str_contains($bankName, 'BRI') ? ['#00529c', '#dbeafe', '#ffffff'] :
                        (str_contains($bankName, 'MANDIRI') ? ['#f6b100', '#fff7d6', '#111827'] :
                        (str_contains($bankName, 'BNI') ? ['#f97316', '#ffedd5', '#ffffff'] :
                        (str_contains($bankName, 'BSI') ? ['#00a884', '#dcfce7', '#ffffff'] :
                        (str_contains($bankName, 'CASH') ? ['#6b7280', '#f3f4f6', '#ffffff'] : ['#2563eb', '#eff6ff', '#ffffff'])))));
                    $bankWords = collect(explode(' ', preg_replace('/[^A-Za-z0-9 ]/', '', $bank->nama_bank ?? 'Bank')))->filter();
                    $bankLogo = $bankWords->count() > 1
                        ? $bankWords->map(fn($word) => substr($word, 0, 1))->take(3)->implode('')
                        : strtoupper(substr($bank->nama_bank ?? 'B', 0, 3));
                    $bankLogoFile = str_contains($bankName, 'BCA DIGITAL') ? 'BCA Digital logo.svg' :
                        (str_contains($bankName, 'BCA SYARIAH') ? 'BCA Syariah.svg' :
                        (str_contains($bankName, 'BCA') ? 'Bank Central Asia.svg' :
                        (str_contains($bankName, 'BRI') ? 'BRI 2020.svg' :
                        (str_contains($bankName, 'BNI') ? 'Bank Negara Indonesia logo (2004).svg' :
                        (str_contains($bankName, 'MANDIRI') ? 'Bank Mandiri logo 2016.svg' :
                        (str_contains($bankName, 'BSI') || str_contains($bankName, 'SYARIAH INDONESIA') ? 'Bank Syariah Indonesia.svg' :
                        (str_contains($bankName, 'BTN') ? 'Bank BTN logo.svg' :
                        (str_contains($bankName, 'CIMB') ? 'CIMB Niaga logo.svg' :
                        (str_contains($bankName, 'DANAMON') ? 'Danamon.svg' :
                        (str_contains($bankName, 'MEGA') ? 'Bank Mega 2013.svg' :
                        (str_contains($bankName, 'PERMATA') ? 'Permata Bank (2024).svg' :
                        (str_contains($bankName, 'PANIN') ? 'Logo Panin Bank.svg' :
                        (str_contains($bankName, 'JAGO') ? 'Logo-jago.svg' :
                        (str_contains($bankName, 'SEABANK') || str_contains($bankName, 'SEA BANK') ? 'SeaBank.svg' :
                        (str_contains($bankName, 'UOB') ? 'UOB Logo (2022).svg' :
                        (str_contains($bankName, 'DKI') ? 'Bank DKI.svg' : null))))))))))))))));
                    $bankLogoUrl = $bankLogoFile
                        ? 'https://commons.wikimedia.org/wiki/Special:FilePath/' . rawurlencode($bankLogoFile) . '?width=160'
                        : (str_contains($bankName, 'CASH') ? asset('assets/media/brand-logos/favicon.png') : null);
                    $nomorRekening = preg_replace('/\s+/', '', (string) ($bank->nomor_rekening ?? ''));
                    $maskedRekening = $nomorRekening ? '**** ' . substr($nomorRekening, -4) : 'Nomor belum diisi';
                @endphp
                <a href="{{ route('account-bank.show', $bank) }}" class="relative overflow-hidden rounded-lg border border-border bg-card p-4 hover:ring-2 hover:ring-primary/20 transition-all cursor-pointer group">
                    <div class="absolute inset-x-0 top-0 h-1" style="background:{{ $palette[0] }};"></div>
                    <div class="flex items-start justify-between gap-3 mb-5">
                        <div class="flex items-center gap-3 min-w-0">
                            @if($bankLogoUrl)
                            <div class="flex items-center justify-center size-12 rounded-lg shrink-0 border border-border bg-white p-2">
                                <img src="{{ $bankLogoUrl }}" alt="{{ $bank->nama_bank }} logo" class="max-h-7 max-w-full object-contain" loading="lazy">
                            </div>
                            @else
                            <div class="flex items-center justify-center size-12 rounded-lg shrink-0 text-sm font-bold" style="background:{{ $palette[0] }}; color:{{ $palette[2] }};">
                                {{ $bankLogo }}
                            </div>
                            @endif
                            <div class="min-w-0">
                                <p class="text-sm font-semibold truncate">{{ $bank->nama_bank }}</p>
                                <p class="text-xs text-muted-foreground truncate">{{ $bank->nama_pemilik ?? '-' }}</p>
                            </div>
                        </div>
                        <span class="flex items-center justify-center size-8 rounded-lg shrink-0" style="background:{{ $palette[1] }}; color:{{ $palette[0] }};">
                            <i class="ki-filled ki-bank text-sm"></i>
                        </span>
                    </div>
                    <div class="flex items-end justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[10px] uppercase text-muted-foreground mb-1">Saldo tersedia</p>
                            <p class="text-lg font-bold text-mono leading-tight truncate" style="color:{{ $palette[0] }};">Rp {{ number_format($bank->saldo, 0, ',', '.') }}</p>
                            <p class="text-xs text-muted-foreground mt-2">{{ $maskedRekening }}</p>
                        </div>
                        <span class="text-xs font-medium opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap" style="color:{{ $palette[0] }};">
                            Mutasi <i class="ki-filled ki-arrow-right text-[10px]"></i>
                        </span>
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div class="py-8 text-center text-muted-foreground text-sm">Belum ada rekening bank</div>
            @endif
        </div>
    </div>

</div>

{{-- Modal Transaksi --}}
<div id="transactionModal" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; padding:1rem;">
    <div style="background:var(--card); border:1px solid var(--border); border-radius:0.75rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); width:100%; max-width:80rem; max-height:90vh; display:flex; flex-direction:column; overflow:hidden;" onclick="event.stopPropagation();">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem; border-bottom:1px solid var(--border);">
            <h3 class="text-base font-semibold text-foreground" id="modalTitle">Data Transaksi</h3>
            <div style="display:flex; align-items:center; gap:0.5rem;">
                <button type="button" onclick="exportModalToExcel()" style="background:var(--primary); color:#fff; border:none; cursor:pointer; padding:0.35rem 0.75rem; border-radius:0.375rem; font-size:0.75rem; font-weight:500; display:flex; align-items:center; gap:0.25rem;">
                    <i class="ki-filled ki-tablet-text-up"></i> Export Excel
                </button>
                <button type="button" onclick="closeTransactionModal()" style="background:none; border:none; cursor:pointer; padding:0.25rem; color:var(--muted-foreground);">
                    <i class="ki-filled ki-cross" style="font-size:1.25rem;"></i>
                </button>
            </div>
        </div>
        <div style="overflow:auto; padding:1rem; flex:1;">
            <table class="kt-table kt-table-border w-full" id="modalTable" style="font-size:0.75rem;">
                <thead>
                    <tr>
                        <th class="text-center text-xs font-semibold w-10">No.</th>
                        <th class="text-left text-xs font-semibold">No. Transaksi</th>
                        <th class="text-center text-xs font-semibold">Tipe</th>
                        <th class="text-center text-xs font-semibold">Sumber</th>
                        <th class="text-left text-xs font-semibold">Jenis</th>
                        <th class="text-center text-xs font-semibold">Tanggal</th>
                        <th class="text-left text-xs font-semibold">Aktivitas</th>
                        <th class="text-left text-xs font-semibold">Kategori</th>
                        <th class="text-right text-xs font-semibold">Nominal</th>
                        <th class="text-center text-xs font-semibold">Status</th>
                        <th class="text-left text-xs font-semibold">Bank</th>
                    </tr>
                </thead>
                <tbody id="modalTableBody">
                    <tr><td colspan="11" class="text-center py-8 text-muted-foreground">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleFilter() {
    document.getElementById('filterPanel').classList.toggle('hidden');
}
function loadSiklusForBlok() {
    var blokId = document.getElementById('filterBlok').value;
    var sel = document.getElementById('filterSiklus');
    sel.innerHTML = '<option value="">Semua Siklus</option>';
    if (!blokId) return;
    fetch('/budidaya/siklus/by-blok/' + blokId)
        .then(r => r.json())
        .then(sikluses => {
            sikluses.filter(s => s.status === 'aktif').forEach(function(s) {
                var opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = s.nama_siklus;
                sel.appendChild(opt);
            });
        });
}
function openTransactionModal(jenis) {
    console.log('Opening modal for:', jenis);
    var modal = document.getElementById('transactionModal');
    var title = document.getElementById('modalTitle');
    var tbody = document.getElementById('modalTableBody');
    title.textContent = jenis === 'pendapatan' ? 'Data Pendapatan' : 'Data Pengeluaran';
    tbody.innerHTML = '<tr><td colspan="10" class="text-center py-8 text-muted-foreground">Memuat data...</td></tr>';
    modal.style.display = 'flex';
    modal.style.opacity = '1';
    document.body.style.overflow = 'hidden';

    var params = new URLSearchParams({
        jenis: jenis,
        blok_id: document.getElementById('filterBlok').value,
        siklus_id: document.getElementById('filterSiklus').value,
        date_from: document.querySelector('input[name="date_from"]').value,
        date_to: document.querySelector('input[name="date_to"]').value
    });

    fetch('{{ route("dashboard.transactions") }}?' + params.toString())
        .then(r => r.json())
        .then(data => {
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="11" class="text-center py-8 text-muted-foreground">Tidak ada data</td></tr>';
                return;
            }
            tbody.innerHTML = data.map(function(d) {
                var sumberBadge = '';
                if (d.sumber === 'Transaksi') sumberBadge = '<span class="kt-badge kt-badge-sm kt-badge-outline">Transaksi</span>';
                else if (d.sumber === 'Panen') sumberBadge = '<span class="kt-badge kt-badge-sm kt-badge-info">Panen</span>';
                else if (d.sumber === 'Gaji') sumberBadge = '<span class="kt-badge kt-badge-sm kt-badge-primary">Gaji</span>';
                else if (d.sumber === 'Pembelian Aset') sumberBadge = '<span class="kt-badge kt-badge-sm kt-badge-warning">Aset</span>';
                else if (d.sumber === 'Pembelian Persediaan') sumberBadge = '<span class="kt-badge kt-badge-sm kt-badge-success">Persediaan</span>';
                else if (d.sumber === 'Pembayaran Hutang') sumberBadge = '<span class="kt-badge kt-badge-sm kt-badge-destructive">Hutang</span>';
                else sumberBadge = '<span class="kt-badge kt-badge-sm kt-badge-outline">' + d.sumber + '</span>';

                return '<tr>' +
                    '<td class="text-center text-sm">' + d.no + '</td>' +
                    '<td class="text-sm text-mono">' + d.nomor_transaksi + '</td>' +
                    '<td class="text-center"><span class="kt-badge kt-badge-sm ' + (d.tipe === 'Pendapatan' ? 'kt-badge-success' : 'kt-badge-destructive') + '">' + d.tipe + '</span></td>' +
                    '<td class="text-center">' + sumberBadge + '</td>' +
                    '<td class="text-sm">' + d.jenis + '</td>' +
                    '<td class="text-center text-sm">' + d.tanggal + '</td>' +
                    '<td class="text-sm">' + d.aktivitas + '</td>' +
                    '<td class="text-sm">' + d.kategori + '</td>' +
                    '<td class="text-right text-sm text-mono font-semibold">Rp ' + Number(d.nominal).toLocaleString('id-ID') + '</td>' +
                    '<td class="text-center"><span class="kt-badge kt-badge-sm kt-badge-outline">' + d.status + '</span></td>' +
                    '<td class="text-sm">' + d.bank + '</td>' +
                '</tr>';
            }).join('');
        })
        .catch(function() {
            tbody.innerHTML = '<tr><td colspan="11" class="text-center py-8 text-danger">Gagal memuat data</td></tr>';
        });
}
function closeTransactionModal() {
    var modal = document.getElementById('transactionModal');
    modal.style.display = 'none';
    document.body.style.overflow = '';
}
function exportModalToExcel() {
    var table = document.getElementById('modalTable');
    var rows = table.querySelectorAll('tr');
    var csv = [];
    rows.forEach(function(row) {
        var cols = row.querySelectorAll('td, th');
        var rowData = [];
        cols.forEach(function(col) {
            rowData.push('"' + col.innerText.replace(/"/g, '""') + '"');
        });
        csv.push(rowData.join(','));
    });
    var csvString = csv.join('\n');
    var blob = new Blob(['\ufeff' + csvString], { type: 'text/csv;charset=utf-8;' });
    var link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'transaksi_' + (document.getElementById('modalTitle').textContent || 'data') + '.csv';
    link.click();
}
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        var panel = document.getElementById('filterPanel');
        var wrapper = document.getElementById('filterWrapper');
        if (panel && wrapper && !wrapper.contains(e.target)) {
            panel.classList.add('hidden');
        }
    });

    var months = @json(array_values($allMonths->toArray()));
    var shortMonths = months.map(function(m) {
        var p = m.split('-');
        var n = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        return n[parseInt(p[1])-1];
    });

    var baseGrid = { borderColor: '#e6edf5', strokeDashArray: 0, padding: { left: 8, right: 12 } };
    var baseFont = { fontFamily: 'Onest, sans-serif' };

    new ApexCharts(document.querySelector('#chart_pendapatan_pengeluaran'), {
        chart: Object.assign({ type: 'area', height: 320, toolbar: { show: false }, zoom: { enabled: false } }, baseFont),
        series: [
            { name: 'Pemasukan', data: @json(array_values($pendapatanChart->toArray())) },
            { name: 'Pengeluaran', data: @json(array_values($pengeluaranChart->toArray())) }
        ],
        xaxis: {
            categories: shortMonths,
            labels: { style: { fontSize: '12px', colors: '#536477', fontWeight: 500 } },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            min: 0,
            labels: {
                formatter: function(v) {
                    return 'Rp ' + (v / 1000000).toFixed(0) + 'jt';
                },
                style: { fontSize: '12px', colors: '#536477', fontWeight: 500 }
            }
        },
        tooltip: {
            shared: true,
            intersect: false,
            y: { formatter: function(v) { return 'Rp ' + Number(v).toLocaleString('id-ID'); } }
        },
        colors: ['#2563eb', '#f45b11'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 0.4,
                opacityFrom: 0.16,
                opacityTo: 0.04,
                stops: [0, 100]
            }
        },
        stroke: { curve: 'smooth', width: 3 },
        dataLabels: { enabled: false },
        legend: { show: false },
        grid: baseGrid,
        markers: {
            size: 5,
            strokeWidth: 0,
            hover: { size: 7 }
        },
    }).render();
});
</script>
@endpush

@endif
@endsection
