@extends('layouts.app')

@section('title', 'Neraca Keuangan')
@section('page-title', 'Neraca Keuangan')
@section('page-description', 'Posisi keuangan per periode')

@section('content')
@php
    $fmtRp = function ($val) {
        $sign = $val < 0 ? '-' : '';
        return $sign . 'Rp ' . number_format(abs($val), 0, ',', '.');
    };
    $selisih = $totalAset - ($totalKewajiban + $totalEkuitas);
    $balanced = abs($selisih) === 0;
@endphp

<div class="grid w-full space-y-5">
    @if(session('success'))
    <div class="kt-card border-green-300 bg-green-50">
        <div class="kt-card-content py-3 px-5 flex items-center gap-2">
            <i class="ki-filled ki-shield-tick text-green-600 text-lg"></i>
            <p class="text-sm font-semibold text-green-800">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    {{-- Filter & Actions --}}
    <div class="kt-card">
        <div class="kt-card-content py-4 px-5 flex items-center justify-between flex-wrap gap-3">
            <form method="GET" class="flex items-center gap-3" action="{{ route('neraca-keuangan.index') }}">
                <label class="text-sm font-medium text-foreground shrink-0">Pilih tanggal:</label>
                <select name="tanggal_cutoff" id="filterCutoff" class="kt-select" onchange="this.form.submit()">
                    @foreach($cutoffs as $cutoff)
                        <option value="{{ $cutoff->tanggal_cutoff->format('Y-m-d') }}" {{ $tanggalCutoff === $cutoff->tanggal_cutoff->format('Y-m-d') ? 'selected' : '' }}>
                            {{ $cutoff->label ? $cutoff->label . ' — ' : '' }}{{ $cutoff->tanggal_cutoff->format('d M Y') }}
                        </option>
                    @endforeach
                    @if($cutoffs->isEmpty())
                        <option value="{{ $tanggalCutoff }}">{{ \Carbon\Carbon::parse($tanggalCutoff)->format('d M Y') }}</option>
                    @endif
                </select>
                <button type="submit" class="kt-btn kt-btn-primary shrink-0">
                    <i class="ki-filled ki-tablet-text-up"></i> Tampilkan
                </button>
            </form>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <button type="button" class="kt-btn bg-yellow-500 hover:bg-yellow-600 text-white border-0" onclick="toggleCutoffDropdown()">
                        <i class="ki-filled ki-calculator"></i>Cut Off Data
                    </button>
                    <div id="cutoffDropdown" class="hidden absolute top-full right-0 mt-2 z-50 bg-white rounded-lg shadow-xl border border-border w-80">
                        <div class="px-4 py-3 border-b border-border flex items-center justify-between">
                            <h3 class="text-sm font-bold text-foreground">Simpan Cut Off</h3>
                            <button type="button" onclick="document.getElementById('cutoffDropdown').classList.add('hidden')" class="text-muted-foreground hover:text-foreground">
                                <i class="ki-filled ki-cross text-base"></i>
                            </button>
                        </div>
                        <form method="POST" action="{{ route('neraca-keuangan.cutoff.store') }}">
                            @csrf
                            <div class="px-4 py-3 space-y-3">
                                <div>
                                    <label class="block text-xs font-medium text-foreground mb-1">Tahun</label>
                                    <select name="tahun" id="cutoffTahun" class="kt-select w-full" onchange="updateCutoffDateRange()">
                                        @for($y = now()->year; $y >= now()->year - 10; $y--)
                                            <option value="{{ $y }}" {{ $y == $tahun ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-foreground mb-1">Tanggal Cut Off</label>
                                    <div class="kt-input">
                                        <i class="ki-outline ki-calendar"></i>
                                        <input class="grow" name="tanggal_cutoff" id="cutoffDate"
                                               data-kt-date-picker="true" data-kt-date-picker-input-mode="true"
                                               placeholder="Pilih tanggal" readonly type="text" required/>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-foreground mb-1">Label <span class="text-muted-foreground">(opsional)</span></label>
                                    <input type="text" name="label" class="kt-input w-full" placeholder="Contoh: Akhir Q1 2026">
                                </div>
                            </div>
                            <div class="px-4 py-3 border-t border-border flex justify-end gap-2">
<button type="button" onclick="document.getElementById('cutoffDropdown').classList.add('hidden')" class="kt-btn kt-btn-secondary">Batal</button>
                            <button type="submit" class="kt-btn kt-btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
                <a href="{{ route('neraca-keuangan.export', ['tahun' => $tahun, 'tanggal_cutoff' => $tanggalCutoff]) }}"
                   class="kt-btn flex items-center gap-2 kt-badge-success text-white border-0">
                    <i class="ki-filled ki-file-sheet"></i> Export Excel
                </a>
            </div>
        </div>
        <div class="px-5 pb-3">
            <p class="text-xs text-secondary-foreground">Periode: <strong>1 Januari {{ $tahun }}</strong> &mdash; <strong>{{ \Carbon\Carbon::parse($tanggalCutoff)->format('d F Y') }}</strong></p>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="kt-card bg-blue-50 border-blue-200">
            <div class="kt-card-content py-4 px-5">
                <div class="flex items-center gap-2 mb-1">
                    <i class="ki-filled ki-wallet text-blue-600"></i>
                    <span class="text-xs font-semibold text-blue-600 uppercase tracking-wide">Total Aset</span>
                </div>
                <p class="text-xl font-bold text-blue-800">{{ $fmtRp($totalAset) }}</p>
            </div>
        </div>
        <div class="kt-card bg-red-50 border-red-200">
            <div class="kt-card-content py-4 px-5">
                <div class="flex items-center gap-2 mb-1">
                    <i class="ki-filled ki-hand-down text-red-600"></i>
                    <span class="text-xs font-semibold text-red-600 uppercase tracking-wide">Total Hutang</span>
                </div>
                <p class="text-xl font-bold text-red-800">{{ $fmtRp($totalKewajiban) }}</p>
            </div>
        </div>
        <div class="kt-card bg-green-50 border-green-200">
            <div class="kt-card-content py-4 px-5">
                <div class="flex items-center gap-2 mb-1">
                    <i class="ki-filled ki-shield-tick text-green-600"></i>
                    <span class="text-xs font-semibold text-green-600 uppercase tracking-wide">Total Ekuitas</span>
                </div>
                <p class="text-xl font-bold text-green-800">{{ $fmtRp($totalEkuitas) }}</p>
            </div>
        </div>
        <div class="kt-card {{ $labaBerjalan >= 0 ? 'bg-emerald-50 border-emerald-200' : 'bg-rose-50 border-rose-200' }}">
            <div class="kt-card-content py-4 px-5">
                <div class="flex items-center gap-2 mb-1">
                    <i class="ki-filled {{ $labaBerjalan >= 0 ? 'ki-graph-up text-emerald-600' : 'ki-graph-down text-rose-600' }}"></i>
                    <span class="text-xs font-semibold {{ $labaBerjalan >= 0 ? 'text-emerald-600' : 'text-rose-600' }} uppercase tracking-wide">Laba Bersih</span>
                </div>
                <p class="text-xl font-bold {{ $labaBerjalan >= 0 ? 'text-emerald-800' : 'text-rose-800' }}">{{ $fmtRp($labaBerjalan) }}</p>
            </div>
        </div>
    </div>

    {{-- Balance Status --}}
    @if($balanced)
    <div class="kt-card border-green-300 bg-green-50">
        <div class="kt-card-content py-3 px-5 flex items-center gap-2">
            <i class="ki-filled ki-shield-tick text-green-600 text-lg"></i>
            <p class="text-sm font-semibold text-green-800">Neraca seimbang &mdash; Aset = Kewajiban + Ekuitas</p>
        </div>
    </div>
    @else
    <div class="kt-card border-amber-300 bg-amber-50">
        <div class="kt-card-content py-3 px-5 flex items-center gap-2">
            <i class="ki-filled ki-information-2 text-amber-600 text-lg"></i>
            <div>
                <p class="text-sm font-semibold text-amber-800">Neraca belum seimbang</p>
                <p class="text-xs text-amber-700">Selisih: {{ $fmtRp(abs($selisih)) }} ({{ $selisih > 0 ? 'Aset lebih besar' : 'Kewajiban + Ekuitas lebih besar' }})</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Neraca Body --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- ASET --}}
        <div class="kt-card">
            <div class="kt-card-header min-h-12 border-b border-blue-200 bg-blue-50">
                <h3 class="kt-card-title text-base font-bold text-blue-800">
                    <i class="ki-filled ki-wallet mr-1"></i> ASET
                </h3>
            </div>
            <div class="kt-card-content py-0 px-0">
                {{-- Aset Lancar --}}
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-blue-50/50">
                            <th colspan="2" class="py-2 px-5 text-xs font-semibold text-blue-700 uppercase tracking-wider">Aset Lancar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-border">
                            <td class="py-2.5 px-5 pl-8 text-foreground">Kas & Bank</td>
                            <td class="py-2.5 px-5 text-right text-mono">{{ $fmtRp($kasBank) }}</td>
                        </tr>
                        <tr class="border-b border-border">
                            <td class="py-2.5 px-5 pl-8 text-foreground">Piutang</td>
                            <td class="py-2.5 px-5 text-right text-mono">{{ $fmtRp($piutang) }}</td>
                        </tr>
                        <tr class="border-b border-border">
                            <td class="py-2.5 px-5 pl-8 text-foreground">Persediaan</td>
                            <td class="py-2.5 px-5 text-right text-mono">{{ $fmtRp($persediaan) }}</td>
                        </tr>
                        <tr class="border-b border-border">
                            <td class="py-2.5 px-5 pl-8 text-foreground">Investasi</td>
                            <td class="py-2.5 px-5 text-right text-mono">{{ $fmtRp($investasi) }}</td>
                        </tr>
                        <tr class="border-b border-border bg-blue-50/30">
                            <td class="py-2 px-5 pl-6 text-xs font-semibold text-blue-700">Subtotal Aset Lancar</td>
                            <td class="py-2 px-5 text-right text-xs font-semibold text-blue-700 text-mono">{{ $fmtRp($asetLancar) }}</td>
                        </tr>
                    </tbody>
                    {{-- Aset Tetap --}}
                    <thead>
                        <tr class="border-b border-border bg-blue-50/50">
                            <th colspan="2" class="py-2 px-5 text-xs font-semibold text-blue-700 uppercase tracking-wider">Aset Tetap</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-border">
                            <td class="py-2.5 px-5 pl-8 text-foreground">Aset Tetap (Bruto)</td>
                            <td class="py-2.5 px-5 text-right text-mono">{{ $fmtRp($asetTetapBruto) }}</td>
                        </tr>
                        <tr class="border-b border-border">
                            <td class="py-2.5 px-5 pl-8 text-muted-foreground">Akumulasi Penyusutan</td>
                            <td class="py-2.5 px-5 text-right text-mono text-red-600">({{ $fmtRp($akumulasiDepresiasi) }})</td>
                        </tr>
                        <tr class="border-b border-border bg-blue-50/30">
                            <td class="py-2 px-5 pl-6 text-xs font-semibold text-blue-700">Subtotal Aset Tetap (Netto)</td>
                            <td class="py-2 px-5 text-right text-xs font-semibold text-blue-700 text-mono">{{ $fmtRp($asetTetapNetto) }}</td>
                        </tr>
                    </tbody>
                    {{-- Total Aset --}}
                    <tfoot>
                        <tr class="bg-blue-600/10">
                            <td class="py-3 px-5 font-bold text-blue-900">TOTAL ASET</td>
                            <td class="py-3 px-5 text-right font-bold text-blue-900 text-mono text-base">{{ $fmtRp($totalAset) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- KEWAJIBAN & EKUITAS --}}
        <div class="kt-card">
            <div class="kt-card-header min-h-12 border-b border-border bg-gray-50">
                <h3 class="kt-card-title text-base font-bold text-foreground">
                    <i class="ki-filled ki-shield-tick mr-1"></i> KEWAJIBAN & EKUITAS
                </h3>
            </div>
            <div class="kt-card-content py-0 px-0">
                {{-- Kewajiban --}}
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-red-200 bg-red-50">
                            <th colspan="2" class="py-2 px-5 text-xs font-semibold text-red-700 uppercase tracking-wider">Kewajiban</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-border">
                            <td class="py-2.5 px-5 pl-8 text-foreground">Hutang Usaha</td>
                            <td class="py-2.5 px-5 text-right text-mono">{{ $fmtRp($hutang) }}</td>
                        </tr>
                        <tr class="bg-red-600/10">
                            <td class="py-3 px-5 font-bold text-red-800">TOTAL KEWAJIBAN</td>
                            <td class="py-3 px-5 text-right font-bold text-red-800 text-mono text-base">{{ $fmtRp($totalKewajiban) }}</td>
                        </tr>
                    </tbody>
                    {{-- Ekuitas --}}
                    <thead>
                        <tr class="border-b border-green-200 bg-green-50">
                            <th colspan="2" class="py-2 px-5 text-xs font-semibold text-green-700 uppercase tracking-wider">Ekuitas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-border">
                            <td class="py-2.5 px-5 pl-8 text-foreground">Modal Pemilik</td>
                            <td class="py-2.5 px-5 text-right text-mono">{{ $fmtRp($modalPemilik) }}</td>
                        </tr>
                        <tr class="border-b border-border">
                            <td class="py-2.5 px-5 pl-8 {{ $labaBerjalan >= 0 ? 'text-foreground' : 'text-rose-700' }}">Laba Tahun Berjalan</td>
                            <td class="py-2.5 px-5 text-right text-mono {{ $labaBerjalan >= 0 ? 'text-green-600' : 'text-rose-600' }}">{{ $fmtRp($labaBerjalan) }}</td>
                        </tr>
                        <tr class="bg-green-600/10">
                            <td class="py-3 px-5 font-bold text-green-800">TOTAL EKUITAS</td>
                            <td class="py-3 px-5 text-right font-bold text-green-800 text-mono text-base">{{ $fmtRp($totalEkuitas) }}</td>
                        </tr>
                    </tbody>
                    {{-- Total Kewajiban + Ekuitas --}}
                    <tfoot>
                        <tr class="bg-primary/10 border-t-2 border-primary">
                            <td class="py-3 px-5 font-bold text-primary">TOTAL KEWAJIBAN + EKUITAS</td>
                            <td class="py-3 px-5 text-right font-bold text-primary text-mono text-base">{{ $fmtRp($totalKewajiban + $totalEkuitas) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleCutoffDropdown() {
    var dd = document.getElementById('cutoffDropdown');
    dd.classList.toggle('hidden');
    if (!dd.classList.contains('hidden')) {
        updateCutoffDateRange();
    }
}

function updateCutoffDateRange() {
    var tahun = document.getElementById('cutoffTahun').value;
    var dateInput = document.getElementById('cutoffDate');
    dateInput.value = '';
    dateInput.setAttribute('data-kt-date-picker-min', tahun + '-01-01');
    dateInput.setAttribute('data-kt-date-picker-max', tahun + '-12-31');

    var wrapper = dateInput.closest('[data-kt-date-picker]');
    if (wrapper && window.KTDatePicker) {
        var inst = KTDatePicker.getInstance(wrapper);
        if (inst) {
            inst.destroy();
        }
        new KTDatePicker(wrapper);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    updateCutoffDateRange();
    document.addEventListener('click', function(e) {
        var dd = document.getElementById('cutoffDropdown');
        if (!dd) return;
        if (dd.classList.contains('hidden')) return;
        if (!dd.contains(e.target) && !e.target.closest('[onclick*="toggleCutoffDropdown"]')) {
            dd.classList.add('hidden');
        }
    });
});
</script>
@endpush