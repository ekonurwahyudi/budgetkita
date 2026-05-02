@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-description', 'Ringkasan data keuangan, operasional & budidaya')

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

    {{-- Row 1: 4 mini stat cards (kiri) + chart earnings (kanan) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-7.5">
        {{-- 4 mini cards 2x2 --}}
        <div class="grid grid-cols-2 gap-5">
            {{-- Pendapatan --}}
            <div class="kt-card">
                <div class="kt-card-content p-5 lg:p-6">
                    <div class="flex items-center justify-center size-9 rounded-lg mb-3" style="background:rgba(23,198,83,0.12);">
                        <i class="ki-filled ki-dollar text-base" style="color:#17c653;"></i>
                    </div>
                    <p class="text-2xl font-bold text-mono leading-none mb-1">
                        @if($pendapatan >= 1000000)
                            {{ number_format($pendapatan/1000000, 1, ',', '.') }}jt
                        @else
                            {{ number_format($pendapatan/1000, 0, ',', '.') }}rb
                        @endif
                    </p>
                    <p class="text-xs text-secondary-foreground">Total Pendapatan</p>
                </div>
            </div>
            {{-- Pengeluaran --}}
            <div class="kt-card">
                <div class="kt-card-content p-5 lg:p-6">
                    <div class="flex items-center justify-center size-9 rounded-lg mb-3" style="background:rgba(241,65,108,0.12);">
                        <i class="ki-filled ki-minus-circle text-base" style="color:#f1416c;"></i>
                    </div>
                    <p class="text-2xl font-bold text-mono leading-none mb-1">
                        @if($pengeluaran >= 1000000)
                            {{ number_format($pengeluaran/1000000, 1, ',', '.') }}jt
                        @else
                            {{ number_format($pengeluaran/1000, 0, ',', '.') }}rb
                        @endif
                    </p>
                    <p class="text-xs text-secondary-foreground">Total Pengeluaran</p>
                </div>
            </div>
            {{-- Hutang --}}
            <div class="kt-card">
                <div class="kt-card-content p-5 lg:p-6">
                    <div class="flex items-center justify-center size-9 rounded-lg mb-3" style="background:rgba(255,199,0,0.12);">
                        <i class="ki-filled ki-bill text-base" style="color:#ffc700;"></i>
                    </div>
                    <p class="text-2xl font-bold text-mono leading-none mb-1">
                        @if($totalHutang >= 1000000)
                            {{ number_format($totalHutang/1000000, 1, ',', '.') }}jt
                        @else
                            {{ number_format($totalHutang/1000, 0, ',', '.') }}rb
                        @endif
                    </p>
                    <p class="text-xs text-secondary-foreground">Total Hutang</p>
                </div>
            </div>
            {{-- Piutang --}}
            <div class="kt-card">
                <div class="kt-card-content p-5 lg:p-6">
                    <div class="flex items-center justify-center size-9 rounded-lg mb-3" style="background:rgba(0,158,247,0.12);">
                        <i class="ki-filled ki-handshake text-base" style="color:#009ef7;"></i>
                    </div>
                    <p class="text-2xl font-bold text-mono leading-none mb-1">
                        @if($totalPiutang >= 1000000)
                            {{ number_format($totalPiutang/1000000, 1, ',', '.') }}jt
                        @else
                            {{ number_format($totalPiutang/1000, 0, ',', '.') }}rb
                        @endif
                    </p>
                    <p class="text-xs text-secondary-foreground">Total Piutang</p>
                </div>
            </div>
        </div>

        {{-- Chart Penjualan (Earnings style) --}}
        <div class="kt-card lg:col-span-2">
            <div class="kt-card-header border-b border-border pb-4">
                <div class="flex flex-col gap-0.5">
                    <h3 class="text-base font-semibold text-foreground">Penjualan Bulanan</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-bold text-mono">Rp {{ number_format($penjualanChart->sum()/1000000, 1, ',', '.') }}jt</span>
                        @php $totalPenjualan = $penjualanChart->sum(); @endphp
                        @if($totalPenjualan > 0)
                        <span class="text-xs font-medium text-[#17c653] bg-[#17c653]/10 px-2 py-0.5 rounded-full">Total 12 Bln</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="kt-card-content p-4">
                <div id="chart_penjualan"></div>
            </div>
        </div>
    </div>

    {{-- Row 2: Highlights (Laba/Rugi breakdown) + Pendapatan vs Pengeluaran --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-7.5">
        {{-- Highlights panel --}}
        <div class="kt-card">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Ringkasan Keuangan</h3>
            </div>
            <div class="kt-card-content p-5 flex flex-col gap-4">
                <div>
                    <p class="text-xs text-secondary-foreground mb-1">Laba / Rugi Bersih</p>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-bold text-mono {{ $labaRugi >= 0 ? 'text-[#17c653]' : 'text-[#f1416c]' }}">
                            {{ $labaRugi >= 0 ? '+' : '-' }} Rp {{ number_format(abs($labaRugi)/1000000, 1, ',', '.') }}jt
                        </span>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $labaRugi >= 0 ? 'text-[#17c653] bg-[#17c653]/10' : 'text-[#f1416c] bg-[#f1416c]/10' }}">
                            {{ $labaRugi >= 0 ? 'Laba' : 'Rugi' }}
                        </span>
                    </div>
                </div>
                {{-- Progress bar pendapatan vs pengeluaran --}}
                @php
                    $total = $pendapatan + $pengeluaran;
                    $pctPendapatan = $total > 0 ? round($pendapatan / $total * 100) : 0;
                    $pctPengeluaran = $total > 0 ? round($pengeluaran / $total * 100) : 0;
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
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm"><i class="ki-filled ki-geolocation text-primary"></i> Tambak</div>
                        <span class="text-sm font-semibold text-mono">{{ $totalTambak }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm"><i class="ki-filled ki-grid text-[#009ef7]"></i> Blok/Kolam</div>
                        <span class="text-sm font-semibold text-mono">{{ $totalBlok }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm"><i class="ki-filled ki-arrows-circle text-[#17c653]"></i> Siklus Aktif</div>
                        <span class="text-sm font-semibold text-mono">{{ $siklusAktif }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm"><i class="ki-filled ki-parcel text-[#ffc700]"></i> Stok Item</div>
                        <span class="text-sm font-semibold text-mono">{{ $stokPersediaan }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm"><i class="ki-filled ki-bank text-[#7239ea]"></i> Nilai Aset</div>
                        <span class="text-sm font-semibold text-mono">Rp {{ number_format($nilaiAset/1000000, 1, ',', '.') }}jt</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pendapatan vs Pengeluaran chart --}}
        <div class="kt-card lg:col-span-2">
            <div class="kt-card-header">
                <h3 class="kt-card-title">Pendapatan vs Pengeluaran</h3>
                <div class="flex items-center gap-3 text-xs text-secondary-foreground">
                    <span class="flex items-center gap-1.5"><span class="size-2 rounded-full inline-block" style="background:#17c653;"></span> Pendapatan</span>
                    <span class="flex items-center gap-1.5"><span class="size-2 rounded-full inline-block" style="background:#f1416c;"></span> Pengeluaran</span>
                </div>
            </div>
            <div class="kt-card-content p-4">
                <div id="chart_pendapatan_pengeluaran"></div>
            </div>
        </div>
    </div>

    {{-- Row 3: Top Stok (full width horizontal bar) --}}
    <div class="kt-card">
        <div class="kt-card-header">
            <div>
                <h3 class="kt-card-title">Top 10 Stok Persediaan</h3>
                <p class="text-xs text-secondary-foreground mt-0.5">Item dengan stok terbanyak</p>
            </div>
        </div>
        <div class="kt-card-content p-4">
            <div id="chart_stok"></div>
        </div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var months = @json(array_values($allMonths->toArray()));
    var shortMonths = months.map(function(m) {
        var p = m.split('-');
        var n = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        return n[parseInt(p[1])-1] + " '" + p[0].slice(2);
    });

    var baseGrid = { borderColor: 'rgba(0,0,0,0.06)', strokeDashArray: 4, padding: { left: 0, right: 0 } };
    var baseFont = { fontFamily: 'Onest, sans-serif' };

    // Chart 1: Penjualan (Area)
    new ApexCharts(document.querySelector('#chart_penjualan'), {
        chart: Object.assign({ type: 'area', height: 200, toolbar: { show: false }, sparkline: { enabled: false } }, baseFont),
        series: [{ name: 'Penjualan', data: @json(array_values($penjualanChart->toArray())) }],
        xaxis: { categories: shortMonths, labels: { style: { fontSize: '10px', colors: '#99a1b7' } }, axisBorder: { show: false }, axisTicks: { show: false } },
        yaxis: { labels: { formatter: function(v) { return v >= 1000000 ? (v/1000000).toFixed(0)+'jt' : (v/1000).toFixed(0)+'rb'; }, style: { fontSize: '10px', colors: '#99a1b7' } } },
        tooltip: { y: { formatter: function(v) { return 'Rp ' + Number(v).toLocaleString('id-ID'); } } },
        colors: ['#17c653'],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.02, stops: [0, 100] } },
        stroke: { curve: 'smooth', width: 2 },
        dataLabels: { enabled: false },
        grid: baseGrid,
        markers: { size: 0 },
    }).render();

    // Chart 2: Pendapatan vs Pengeluaran (Bar)
    new ApexCharts(document.querySelector('#chart_pendapatan_pengeluaran'), {
        chart: Object.assign({ type: 'bar', height: 260, toolbar: { show: false } }, baseFont),
        series: [
            { name: 'Pendapatan', data: @json(array_values($pendapatanChart->toArray())) },
            { name: 'Pengeluaran', data: @json(array_values($pengeluaranChart->toArray())) }
        ],
        xaxis: { categories: shortMonths, labels: { style: { fontSize: '10px', colors: '#99a1b7' } }, axisBorder: { show: false }, axisTicks: { show: false } },
        yaxis: { labels: { formatter: function(v) { return v >= 1000000 ? (v/1000000).toFixed(0)+'jt' : (v/1000).toFixed(0)+'rb'; }, style: { fontSize: '10px', colors: '#99a1b7' } } },
        tooltip: { y: { formatter: function(v) { return 'Rp ' + Number(v).toLocaleString('id-ID'); } } },
        colors: ['#17c653', '#f1416c'],
        plotOptions: { bar: { columnWidth: '50%', borderRadius: 3, borderRadiusApplication: 'end' } },
        dataLabels: { enabled: false },
        legend: { show: false },
        grid: baseGrid,
    }).render();

    // Chart 3: Top Stok
    var stokData = @json($topStok->map(fn($s) => ['name' => \Illuminate\Support\Str::limit($s->itemPersediaan?->deskripsi ?? '-', 22), 'qty' => (float)$s->qty])->values());
    if (stokData.length > 0) {
        new ApexCharts(document.querySelector('#chart_stok'), {
            chart: Object.assign({ type: 'bar', height: Math.max(180, stokData.length * 30), toolbar: { show: false } }, baseFont),
            series: [{ name: 'Stok', data: stokData.map(function(s) { return s.qty; }) }],
            xaxis: { categories: stokData.map(function(s) { return s.name; }), labels: { style: { fontSize: '11px', colors: '#99a1b7' } } },
            yaxis: { labels: { style: { fontSize: '11px', colors: '#99a1b7' } } },
            plotOptions: { bar: { horizontal: true, borderRadius: 3, barHeight: '50%', borderRadiusApplication: 'end' } },
            colors: ['#7239ea'],
            dataLabels: { enabled: true, style: { fontSize: '11px', fontWeight: 500, colors: ['#fff'] }, formatter: function(v) { return Number(v).toLocaleString('id-ID'); } },
            tooltip: { y: { formatter: function(v) { return Number(v).toLocaleString('id-ID'); } } },
            grid: baseGrid,
        }).render();
    }
});
</script>
@endpush

@endif
@endsection
