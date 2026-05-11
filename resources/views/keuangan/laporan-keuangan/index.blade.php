@extends('layouts.app')

@section('title', 'Laporan Keuangan')
@section('page-title', 'Laporan Keuangan')
@section('page-description', 'Rekap seluruh transaksi keuangan')

@section('content')
<div class="grid w-full space-y-5">
    {{-- Row 1: Total Masuk, Total Keluar, Transaksi Keuangan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="kt-card">
            <div class="kt-card-content py-4 px-5 flex items-center gap-3">
                <div class="size-11 rounded-xl bg-success/10 flex items-center justify-center shrink-0">
                    <i class="ki-filled ki-arrow-down text-success text-lg"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm text-muted-foreground">Total Masuk</p>
                    <p class="text-lg font-bold text-success text-mono">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="kt-card">
            <div class="kt-card-content py-4 px-5 flex items-center gap-3">
                <div class="size-11 rounded-xl bg-destructive/10 flex items-center justify-center shrink-0">
                    <i class="ki-filled ki-arrow-up text-destructive text-lg"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm text-muted-foreground">Total Keluar</p>
                    <p class="text-lg font-bold text-destructive text-mono">Rp {{ number_format($totalKeluar, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="kt-card">
            <div class="kt-card-content py-4 px-5 flex items-center gap-3">
                <div class="size-11 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                    <i class="ki-filled ki-cheque text-blue-600 text-lg"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm text-muted-foreground">Transaksi Keuangan</p>
                    <p class="text-lg font-bold text-foreground text-mono">Rp {{ number_format($totals['transaksi'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 2: Investasi, Gaji Karyawan, Pembelian Pakan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="kt-card">
            <div class="kt-card-content py-4 px-5 flex items-center gap-3">
                <div class="size-11 rounded-xl bg-purple-50 flex items-center justify-center shrink-0">
                    <i class="ki-filled ki-chart-line-up-2 text-purple-600 text-lg"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm text-muted-foreground">Investasi</p>
                    <p class="text-lg font-bold text-foreground text-mono">Rp {{ number_format($totals['investasi'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="kt-card">
            <div class="kt-card-content py-4 px-5 flex items-center gap-3">
                <div class="size-11 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                    <i class="ki-filled ki-people text-amber-600 text-lg"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm text-muted-foreground">Gaji Karyawan</p>
                    <p class="text-lg font-bold text-foreground text-mono">Rp {{ number_format($totals['gaji'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="kt-card">
            <div class="kt-card-content py-4 px-5 flex items-center gap-3">
                <div class="size-11 rounded-xl bg-cyan-50 flex items-center justify-center shrink-0">
                    <i class="ki-filled ki-handcart text-cyan-600 text-lg"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm text-muted-foreground">Pembelian Pakan</p>
                    <p class="text-lg font-bold text-foreground text-mono">Rp {{ number_format($totals['persediaan'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 3: Pembelian Aset, Hutang/Piutang, Panen --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="kt-card">
            <div class="kt-card-content py-4 px-5 flex items-center gap-3">
                <div class="size-11 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
                    <i class="ki-filled ki-home-2 text-green-600 text-lg"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm text-muted-foreground">Pembelian Aset</p>
                    <p class="text-lg font-bold text-foreground text-mono">Rp {{ number_format($totals['aset'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="kt-card">
            <div class="kt-card-content py-4 px-5 flex items-center gap-3">
                <div class="size-11 rounded-xl bg-rose-50 flex items-center justify-center shrink-0">
                    <i class="ki-filled ki-bill text-rose-600 text-lg"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm text-muted-foreground">Hutang/Piutang</p>
                    <p class="text-lg font-bold text-foreground text-mono">Rp {{ number_format($totals['hutang_piutang'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="kt-card">
            <div class="kt-card-content py-4 px-5 flex items-center gap-3">
                <div class="size-11 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                    <i class="ki-filled ki-basket text-emerald-600 text-lg"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm text-muted-foreground">Panen</p>
                    <p class="text-lg font-bold text-foreground text-mono">Rp {{ number_format($totals['panen'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="kt-card">
        {{-- Header: Filter & Search --}}
        <div class="kt-card-header min-h-16 flex-wrap gap-3">
            {{-- Tabs kiri --}}
            <div class="flex items-center rounded-lg p-1 overflow-x-auto" style="background-color: #f1f5f9;">
                @php
                    $activeJenis = $jenis;
                    $jenisTabs = [
                        'semua' => 'Semua',
                        'uang_masuk' => 'Uang Masuk',
                        'uang_keluar' => 'Uang Keluar',
                    ];
                @endphp
                @foreach($jenisTabs as $val => $label)
                    <a href="{{ route('laporan-keuangan.index', array_filter(array_merge(request()->query(), ['jenis' => $val, 'page' => null]))) }}"
                       class="px-4 py-1.5 rounded-md text-sm whitespace-nowrap transition-all
                              {{ $activeJenis === $val
                                  ? 'text-gray-900 font-semibold'
                                  : 'text-gray-500 hover:text-gray-700' }}"
                       @if($activeJenis === $val) style="background-color: #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.06);" @endif>
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- Kanan: Search, Filter, Export --}}
            <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                <input type="text" placeholder="Cari kegiatan..." class="kt-input w-full sm:w-48"
                       data-kt-datatable-search="#laporan_table" value="{{ $search }}" />

                <button type="button" id="filter-btn" class="kt-btn kt-btn-outline flex items-center gap-2">
                    <i class="ki-filled ki-filter"></i> Filter
                    @if($tgl_dari || $tgl_sampai || $status)
                        <span class="w-2 h-2 rounded-full bg-primary inline-block"></span>
                    @endif
                </button>

                <a href="{{ route('neraca-keuangan.index') }}"
                   class="kt-btn flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white border-0">
                    <i class="ki-filled ki-calculator"></i> Neraca
                </a>

                <a href="{{ route('laporan-keuangan.export', request()->query()) }}"
                   class="kt-btn flex items-center gap-2 text-white border-0" style="background-color:#16a34a;">
                    <i class="ki-filled ki-tablet-text-up"></i> Export Excel
                </a>
            </div>
        </div>

        {{-- Table --}}
        <div id="laporan_table" class="kt-card-table" data-kt-datatable="true" data-kt-datatable-page-size="25" data-kt-datatable-state-save="true" data-kt-datatable-state-namespace="laporan_keuangan_v3">
            <div class="kt-table-wrapper kt-scrollable">
                <table class="kt-table" data-kt-datatable-table="true">
                    <thead>
                        <tr>
                            <th class="w-12" data-kt-datatable-column="no"><span class="kt-table-col"><span class="kt-table-col-label">No</span></span></th>
                            <th data-kt-datatable-column="nomor"><span class="kt-table-col"><span class="kt-table-col-label">No. Transaksi</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="tipe"><span class="kt-table-col"><span class="kt-table-col-label">Tipe</span></span></th>
                            <th data-kt-datatable-column="jenis"><span class="kt-table-col"><span class="kt-table-col-label">Jenis</span></span></th>
                            <th data-kt-datatable-column="tgl"><span class="kt-table-col"><span class="kt-table-col-label">Tanggal</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="aktivitas"><span class="kt-table-col"><span class="kt-table-col-label">Aktivitas/Kegiatan</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="kategori"><span class="kt-table-col"><span class="kt-table-col-label">Kategori</span></span></th>
                            <th data-kt-datatable-column="nominal"><span class="kt-table-col"><span class="kt-table-col-label">Nominal</span><span class="kt-table-col-sort"></span></span></th>
                            <th data-kt-datatable-column="status"><span class="kt-table-col"><span class="kt-table-col-label">Status</span></span></th>
                            <th data-kt-datatable-column="bank"><span class="kt-table-col"><span class="kt-table-col-label">Bank</span></span></th>
                            <th class="w-16" data-kt-datatable-column="aksi"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $i => $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="text-mono text-sm">{{ $row['nomor'] }}</td>
                            <td>
                                @if($row['type'] === 'Transaksi Keuangan')
                                    <span class="kt-badge kt-badge-sm kt-badge-outline">Transaksi</span>
                                @elseif($row['type'] === 'Gaji Karyawan')
                                    <span class="kt-badge kt-badge-sm kt-badge-warning kt-badge-outline">Gaji</span>
                                @elseif($row['type'] === 'Investasi')
                                    <span class="kt-badge kt-badge-sm kt-badge-primary kt-badge-outline">Investasi</span>
                                @elseif($row['type'] === 'Pembelian Persediaan')
                                    <span class="kt-badge kt-badge-sm kt-badge-info kt-badge-outline">Pakan</span>
                                @elseif($row['type'] === 'Pembelian Aset')
                                    <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">Aset</span>
                                @elseif($row['type'] === 'Hutang/Piutang')
                                    <span class="kt-badge kt-badge-sm kt-badge-destructive kt-badge-outline">Hutang/Piutang</span>
                                @elseif($row['type'] === 'Panen')
                                    <span class="kt-badge kt-badge-sm kt-badge-outline" style="color:#059669;border-color:#059669;">Panen</span>
                                @endif
                            </td>
                            <td>
                                @if($row['jenis_raw'] === 'uang_masuk')
                                    <span class="kt-badge kt-badge-sm kt-badge-success">Masuk</span>
                                @else
                                    <span class="kt-badge kt-badge-sm kt-badge-destructive">Keluar</span>
                                @endif
                            </td>
                            <td>{{ $row['tanggal']?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                <div>{{ Str::limit($row['aktivitas'], 40) }}</div>
                                @if($row['blok'] || $row['siklus'])
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ $row['blok'] }}{{ $row['blok'] && $row['siklus'] ? ' · ' : '' }}{{ $row['siklus'] }}
                                </div>
                                @endif
                            </td>
                            <td class="text-sm text-gray-500">{{ $row['kategori'] ?? '-' }}</td>
                            <td class="text-mono whitespace-nowrap {{ $row['jenis_raw'] === 'uang_masuk' ? 'text-success' : 'text-destructive' }}">
                                {{ $row['jenis_raw'] === 'uang_masuk' ? '+' : '-' }}Rp {{ number_format($row['nominal'], 0, ',', '.') }}
                            </td>
                            <td>
                                @if($row['status'] === 'selesai')
                                    <span class="kt-badge kt-badge-sm kt-badge-success">Selesai</span>
                                @elseif($row['status'] === 'cancel')
                                    <span class="kt-badge kt-badge-sm kt-badge-destructive">Cancel</span>
                                @elseif($row['status'] === 'proses')
                                    <span class="kt-badge kt-badge-sm kt-badge-primary">Proses</span>
                                @elseif($row['status'] === 'pending')
                                    <span class="kt-badge kt-badge-sm kt-badge-warning">Pending</span>
                                @else
                                    <span class="kt-badge kt-badge-sm kt-badge-outline">Awaiting</span>
                                @endif
                            </td>
                            <td class="text-sm text-gray-500">{{ $row['bank'] ?? '-' }}</td>
                            <td class="text-end">
                                <a href="{{ $row['route'] }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" title="Lihat Detail">
                                    <i class="ki-filled ki-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="kt-datatable-toolbar">
                <div class="kt-datatable-length">Show <select class="kt-select kt-select-sm w-16" name="perpage" data-kt-datatable-size="true"></select> per page</div>
                <div class="kt-datatable-info"><span data-kt-datatable-info="true"></span><div class="kt-datatable-pagination" data-kt-datatable-pagination="true"></div></div>
            </div>
        </div>
    </div>
</div>

{{-- Filter Panel --}}
<div id="filter-panel" class="hidden bg-white border border-gray-200 rounded-xl shadow-2xl p-5 w-[calc(100vw-2rem)] sm:w-80"
     style="position:fixed; z-index:100;">
    <form method="GET" id="filter-form">
        <input type="hidden" name="jenis" value="{{ $jenis }}">

        <p class="font-semibold text-gray-800 mb-4">Filter Laporan</p>

        <div class="space-y-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal</label>
                <div class="kt-input w-full">
                    <i class="ki-outline ki-calendar"></i>
                    <input
                        id="tgl-range-picker"
                        class="grow"
                        type="text"
                        placeholder="Pilih rentang tanggal"
                        readonly
                        data-kt-date-picker="true"
                        data-kt-date-picker-action-buttons="true"
                        data-kt-date-picker-display-months-count="2"
                        data-kt-date-picker-input-mode="true"
                        data-kt-date-picker-months-to-switch="1"
                        data-kt-date-picker-position-to-input="left"
                        data-kt-date-picker-preset-last-month="true"
                        data-kt-date-picker-preset-last30-days="true"
                        data-kt-date-picker-preset-last7-days="true"
                        data-kt-date-picker-preset-this-month="true"
                        data-kt-date-picker-preset-this-week="true"
                        data-kt-date-picker-presets="true"
                        data-kt-date-picker-selection-dates-mode="multiple-ranged"
                        data-kt-date-picker-type="multiple"
                        @if($tgl_dari && $tgl_sampai)
                            value="{{ $tgl_dari }} - {{ $tgl_sampai }}"
                        @endif
                    />
                </div>
                <input type="hidden" name="tgl_dari" id="input-tgl-dari" value="{{ $tgl_dari }}">
                <input type="hidden" name="tgl_sampai" id="input-tgl-sampai" value="{{ $tgl_sampai }}">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="kt-select w-full">
                    <option value="">Semua Status</option>
                    <option value="awaiting_approval" {{ $status === 'awaiting_approval' ? 'selected' : '' }}>Awaiting</option>
                    <option value="proses" {{ $status === 'proses' ? 'selected' : '' }}>Proses</option>
                    <option value="selesai" {{ $status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancel" {{ $status === 'cancel' ? 'selected' : '' }}>Cancel</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
        </div>

        <div class="flex gap-2 mt-5">
            <button type="submit" class="kt-btn kt-btn-primary flex-1">Terapkan</button>
            <a href="{{ route('laporan-keuangan.index') }}" class="kt-btn kt-btn-outline flex-1 text-center">Reset</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<style>
    [data-popper-placement] { z-index: 9999 !important; }
</style>
<script>
(function() {
    var filterBtn = document.getElementById('filter-btn');
    var panel = document.getElementById('filter-panel');
    var isOpen = false;
    var datePickerActive = false;

    function positionPanel() {
        var rect = filterBtn.getBoundingClientRect();
        var isMobile = window.innerWidth < 640;
        if (isMobile) {
            panel.style.top = (rect.bottom + 8) + 'px';
            panel.style.left = '1rem';
            panel.style.right = '1rem';
        } else {
            panel.style.top = (rect.bottom + 8) + 'px';
            panel.style.left = Math.max(0, rect.right - 320) + 'px';
            panel.style.right = '';
        }
    }

    function openFilter() { positionPanel(); panel.classList.remove('hidden'); isOpen = true; }
    function closeFilter() { panel.classList.add('hidden'); isOpen = false; }

    filterBtn.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); if (isOpen) closeFilter(); else openFilter(); });

    var dpObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) { if (node.nodeType === 1 && node.hasAttribute && node.hasAttribute('data-popper-placement')) datePickerActive = true; });
            mutation.removedNodes.forEach(function(node) { if (node.nodeType === 1 && node.hasAttribute && node.hasAttribute('data-popper-placement')) setTimeout(function() { datePickerActive = false; }, 200); });
        });
    });
    dpObserver.observe(document.body, { childList: true, subtree: true });

    document.addEventListener('mousedown', function(e) {
        if (!isOpen) return;
        if (datePickerActive) return;
        if (filterBtn.contains(e.target)) return;
        if (panel.contains(e.target)) return;
        if (e.target.closest('[data-popper-placement]')) return;
        closeFilter();
    });

    window.addEventListener('scroll', function() { if (isOpen) positionPanel(); }, true);
    window.addEventListener('resize', function() { if (isOpen) positionPanel(); });

    document.getElementById('filter-form').addEventListener('submit', function() {
        var pickerVal = (document.getElementById('tgl-range-picker').value || '').trim();
        if (pickerVal && pickerVal.indexOf(' - ') !== -1) {
            var parts = pickerVal.split(' - ');
            document.getElementById('input-tgl-dari').value = parts[0].trim();
            document.getElementById('input-tgl-sampai').value = parts[1].trim();
        } else {
            document.getElementById('input-tgl-dari').value = '';
            document.getElementById('input-tgl-sampai').value = '';
        }
    });
})();
</script>
@endpush