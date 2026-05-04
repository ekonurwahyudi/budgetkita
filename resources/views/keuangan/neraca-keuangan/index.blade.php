@extends('layouts.app')

@section('title', 'Neraca Keuangan')
@section('page-title', 'Neraca Keuangan')
@section('page-description', 'Posisi keuangan per tahun')

@section('content')
<div class="grid w-full space-y-5">
    {{-- Filter Tahun & Export --}}
    <div class="kt-card">
        <div class="kt-card-content py-4 px-5 flex items-center justify-between flex-wrap gap-3">
            <form method="GET" class="flex items-center gap-3">
                <label class="text-sm font-medium text-foreground">Tahun:</label>
                <select name="tahun" class="kt-select" onchange="this.form.submit()">
                    @for($y = now()->year; $y >= now()->year - 10; $y--)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </form>
            <a href="{{ route('neraca-keuangan.export', ['tahun' => $tahun]) }}"
               class="kt-btn flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white border-0">
                <i class="ki-filled ki-file-sheet"></i> Export Excel
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- ASET --}}
        <div class="kt-card">
            <div class="kt-card-header min-h-12 border-b border-border">
                <h3 class="kt-card-title text-base font-bold">ASET</h3>
            </div>
            <div class="kt-card-content py-0 px-0">
                <table class="w-full text-sm">
                    <tbody>
                        <tr class="border-b border-border">
                            <td class="py-3 px-5 text-foreground font-medium">Kas & Bank</td>
                            <td class="py-3 px-5 text-right text-mono font-medium">Rp {{ number_format($kasBank, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="border-b border-border">
                            <td class="py-3 px-5 text-foreground font-medium">Piutang</td>
                            <td class="py-3 px-5 text-right text-mono font-medium">Rp {{ number_format($piutang, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="border-b border-border">
                            <td class="py-3 px-5 text-foreground font-medium">Persediaan</td>
                            <td class="py-3 px-5 text-right text-mono font-medium">Rp {{ number_format($persediaan, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="border-b border-border">
                            <td class="py-3 px-5 text-foreground font-medium">Investasi</td>
                            <td class="py-3 px-5 text-right text-mono font-medium">Rp {{ number_format($investasi, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="border-b border-border bg-gray-50/50">
                            <td class="py-2 px-5 text-muted-foreground text-xs">Aset Tetap (Bruto)</td>
                            <td class="py-2 px-5 text-right text-mono text-xs">Rp {{ number_format($asetTetapBruto, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="border-b border-border bg-gray-50/50">
                            <td class="py-2 px-5 text-muted-foreground text-xs">Akumulasi Depresiasi</td>
                            <td class="py-2 px-5 text-right text-mono text-xs">(Rp {{ number_format($akumulasiDepresiasi, 0, ',', '.') }})</td>
                        </tr>
                        <tr class="border-b border-border">
                            <td class="py-3 px-5 text-foreground font-medium">Aset Tetap (Netto)</td>
                            <td class="py-3 px-5 text-right text-mono font-medium">Rp {{ number_format($asetTetapNetto, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="bg-primary/5">
                            <td class="py-3 px-5 font-bold text-primary">TOTAL ASET</td>
                            <td class="py-3 px-5 text-right font-bold text-primary text-mono text-base">Rp {{ number_format($totalAset, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- KEWAJIBAN & EKUITAS --}}
        <div class="kt-card">
            <div class="kt-card-header min-h-12 border-b border-border">
                <h3 class="kt-card-title text-base font-bold">KEWAJIBAN & EKUITAS</h3>
            </div>
            <div class="kt-card-content py-0 px-0">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-gray-50">
                            <th colspan="2" class="py-2 px-5 text-xs font-semibold text-muted-foreground uppercase tracking-wider">Kewajiban</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-border">
                            <td class="py-3 px-5 text-foreground font-medium">Hutang</td>
                            <td class="py-3 px-5 text-right text-mono font-medium">Rp {{ number_format($hutang, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="bg-destructive/5">
                            <td class="py-3 px-5 font-bold text-destructive">TOTAL KEWAJIBAN</td>
                            <td class="py-3 px-5 text-right font-bold text-destructive text-mono text-base">Rp {{ number_format($totalKewajiban, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                    <thead>
                        <tr class="border-b border-t border-border bg-gray-50">
                            <th colspan="2" class="py-2 px-5 text-xs font-semibold text-muted-foreground uppercase tracking-wider">Ekuitas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-border bg-green-50/50">
                            <td colspan="2" class="py-2 px-4 text-xs text-muted-foreground font-medium pl-8">Pendapatan {{ $tahun }}</td>
                        </tr>
                        <tr class="border-b border-border bg-green-50/50">
                            <td class="py-1.5 px-5 text-muted-foreground text-xs pl-12">Transaksi Keuangan</td>
                            <td class="py-1.5 px-5 text-right text-mono text-xs">Rp {{ number_format($pendapatanTransaksi, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="border-b border-border bg-green-50/50">
                            <td class="py-1.5 px-5 text-muted-foreground text-xs pl-12">Panen</td>
                            <td class="py-1.5 px-5 text-right text-mono text-xs">Rp {{ number_format($pendapatanPanen, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="border-b border-border bg-green-50/50">
                            <td class="py-1.5 px-5 text-muted-foreground text-xs pl-12">Investasi</td>
                            <td class="py-1.5 px-5 text-right text-mono text-xs">Rp {{ number_format($pendapatanInvestasi, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="border-b border-border bg-green-50/50">
                            <td class="py-2 px-5 text-xs font-semibold text-success pl-8">Total Pendapatan</td>
                            <td class="py-2 px-5 text-right text-xs font-semibold text-success text-mono">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="border-b border-border bg-red-50/50">
                            <td colspan="2" class="py-2 px-4 text-xs text-muted-foreground font-medium pl-8">Pengeluaran {{ $tahun }}</td>
                        </tr>
                        <tr class="border-b border-border bg-red-50/50">
                            <td class="py-1.5 px-5 text-muted-foreground text-xs pl-12">Transaksi Keuangan</td>
                            <td class="py-1.5 px-5 text-right text-mono text-xs">(Rp {{ number_format($pengeluaranTransaksi, 0, ',', '.') }})</td>
                        </tr>
                        <tr class="border-b border-border bg-red-50/50">
                            <td class="py-1.5 px-5 text-muted-foreground text-xs pl-12">Gaji Karyawan</td>
                            <td class="py-1.5 px-5 text-right text-mono text-xs">(Rp {{ number_format($pengeluaranGaji, 0, ',', '.') }})</td>
                        </tr>
                        <tr class="border-b border-border bg-red-50/50">
                            <td class="py-1.5 px-5 text-muted-foreground text-xs pl-12">Pembelian Pakan</td>
                            <td class="py-1.5 px-5 text-right text-mono text-xs">(Rp {{ number_format($pengeluaranPersediaan, 0, ',', '.') }})</td>
                        </tr>
                        <tr class="border-b border-border bg-red-50/50">
                            <td class="py-1.5 px-5 text-muted-foreground text-xs pl-12">Pembelian Aset</td>
                            <td class="py-1.5 px-5 text-right text-mono text-xs">(Rp {{ number_format($pengeluaranAset, 0, ',', '.') }})</td>
                        </tr>
                        <tr class="border-b border-border bg-red-50/50">
                            <td class="py-2 px-5 text-xs font-semibold text-destructive pl-8">Total Pengeluaran</td>
                            <td class="py-2 px-5 text-right text-xs font-semibold text-destructive text-mono">(Rp {{ number_format($totalPengeluaran, 0, ',', '.') }})</td>
                        </tr>
                        <tr class="border-b border-border">
                            <td class="py-3 px-5 font-bold {{ $labaRugi >= 0 ? 'text-success' : 'text-destructive' }}">Laba / Rugi {{ $tahun }}</td>
                            <td class="py-3 px-5 text-right font-bold text-mono {{ $labaRugi >= 0 ? 'text-success' : 'text-destructive' }}">{{ $labaRugi >= 0 ? '' : '(' }}Rp {{ number_format(abs($labaRugi), 0, ',', '.') }}{{ $labaRugi >= 0 ? '' : ')' }}</td>
                        </tr>
                        <tr class="bg-primary/5">
                            <td class="py-3 px-5 font-bold text-primary">TOTAL KEWAJIBAN + EKUITAS</td>
                            <td class="py-3 px-5 text-right font-bold text-primary text-mono text-base">Rp {{ number_format($totalKewajiban + $ekuitas, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Neraca Check --}}
    @php $selisih = $totalAset - ($totalKewajiban + $ekuitas); @endphp
    @if(abs($selisih) > 0)
    <div class="kt-card border-amber-300 bg-amber-50">
        <div class="kt-card-content py-3 px-5 flex items-center gap-2">
            <i class="ki-filled ki-information-2 text-amber-600 text-lg"></i>
            <div>
                <p class="text-sm font-semibold text-amber-800">Neraca belum seimbang</p>
                <p class="text-xs text-amber-700">Selisih: Rp {{ number_format(abs($selisih), 0, ',', '.') }} ({{ $selisih > 0 ? 'Aset lebih besar' : 'Kewajiban + Ekuitas lebih besar' }})</p>
            </div>
        </div>
    </div>
    @else
    <div class="kt-card border-green-300 bg-green-50">
        <div class="kt-card-content py-3 px-5 flex items-center gap-2">
            <i class="ki-filled ki-shield-tick text-green-600 text-lg"></i>
            <p class="text-sm font-semibold text-green-800">Neraca seimbang &mdash; Aset = Kewajiban + Ekuitas</p>
        </div>
    </div>
    @endif
</div>
@endsection