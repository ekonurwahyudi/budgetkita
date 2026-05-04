@extends('layouts.app')

@section('title', 'Detail Kolam - ' . $kolam->nama_kolam)
@section('page-title', 'Detail Kolam')
@section('page-description', $kolam->nama_kolam . ' · ' . $kolam->siklus?->nama_siklus)

@push('styles')
<style>
.excel-wrap {
    border: 1px solid #c0c8d4; border-radius: 6px;
    max-height: 70vh; overflow: auto; position: relative;
    min-width: 0;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}
.excel-wrap .excel-table { min-width: max-content; }
.excel-table { border-collapse: separate; border-spacing: 0; }
.excel-table thead th {
    position: sticky; top: 0; z-index: 30;
    background: #3b82f6; color: #fff;
    font-size: 0.62rem; font-weight: 600;
    padding: 0.25rem 0.2rem; text-align: center; white-space: nowrap;
    border-bottom: 1px solid #2563eb; border-right: 1px solid #2563eb;
    box-shadow: 0 1px 2px rgba(37,99,235,0.3);
}
.excel-table thead th.th-corner { position: sticky; left: 0; z-index: 40; background: #2563eb; }
.excel-table thead th.th-corner-action { position: sticky; right: 0; z-index: 40; background: #2563eb; }
.excel-table thead tr.th-group th {
    background: #2563eb; font-size: 0.6rem; padding: 0.18rem 0.2rem;
    border-bottom: 1px solid #1d4ed8; border-right: 1px solid #1d4ed8;
}
.excel-table thead tr.th-sub th {
    background: #3b82f6; font-size: 0.56rem;
    border-bottom: 2px solid #1d4ed8; border-right: 1px solid #2563eb;
    width: 48px;
}
.excel-table tbody td {
    padding: 0; border-bottom: 1px solid #d1d5db; border-right: 1px solid #d1d5db;
}
.excel-table tbody td.td-date {
    position: sticky; left: 0; z-index: 10;
    background: #fff; padding: 0.2rem 0.3rem;
    font-size: 0.68rem; font-weight: 600; white-space: nowrap;
    font-variant-numeric: tabular-nums; width: 75px;
    border-right: 2px solid #93c5fd;
}
.excel-table tbody td.td-action {
    position: sticky; right: 0; z-index: 10;
    background: #fff; padding: 0.2rem; text-align: center; width: 28px;
    border-left: 2px solid #93c5fd;
}
.excel-table tbody tr:nth-child(even) td:not(.td-date):not(.td-action) { background: #f9fafb; }
.excel-table tbody tr:hover td { background: #eff6ff !important; }
.excel-table tbody tr.row-today td.td-date { background: #dbeafe !important; }
.excel-table tbody tr.row-today td.td-action { background: #dbeafe !important; }
.excel-table tbody tr.row-today td:not(.td-date):not(.td-action):not(.td-oleh) { background: #eff6ff !important; }
.excel-table tbody td.td-date {
    position: sticky; left: 0; z-index: 10;
    background: #fff; padding: 0.25rem 0.35rem;
    font-size: 0.7rem; font-weight: 600; white-space: nowrap;
    font-variant-numeric: tabular-nums; min-width: 80px;
    border-right: 2px solid #93c5fd;
}
.excel-table tbody td.td-action {
    position: sticky; right: 0; z-index: 10;
    background: #fff; padding: 0.25rem; text-align: center; min-width: 30px;
    border-left: 2px solid #93c5fd;
}
.excel-table tbody td.td-num { width: 48px; }
.excel-table tbody td.td-text { width: 55px; }
.excel-table tbody td.td-status { width: 50px; }
.excel-table tbody td.td-oleh { width: 48px; padding: 0 0.2rem; font-size: 0.65rem; color: #6b7280; white-space: nowrap; text-align: center; }
.excel-input {
    width: 100%; border: none; outline: none; background: transparent;
    padding: 0.2rem 0.15rem; font-size: 0.68rem; font-variant-numeric: tabular-nums;
    height: 26px; box-sizing: border-box;
}
.excel-input:focus { background: #dbeafe; box-shadow: inset 0 0 0 2px #3b82f6; position: relative; z-index: 5; }
.excel-input::-webkit-outer-spin-button,
.excel-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.excel-input[type=number] { -moz-appearance: textfield; }
.excel-input:hover:not(:focus) { background: #f0f4ff; }
.excel-select {
    width: 100%; border: none; outline: none; background: transparent;
    padding: 0.2rem 0.15rem; font-size: 0.63rem; cursor: pointer;
    -webkit-appearance: none; appearance: none; height: 26px; box-sizing: border-box;
}
.excel-select:focus { background: #dbeafe; box-shadow: inset 0 0 0 2px #3b82f6; }
.excel-select:hover:not(:focus) { background: #f0f4ff; }
.excel-input.save-ok { background: #bbf7d0 !important; }
.excel-input.save-err { background: #fecaca !important; }
.tab-btn { padding: 0.5rem 1rem; font-size: 0.8rem; font-weight: 600; border-bottom: 2px solid transparent; color: #6b7280; cursor: pointer; transition: all 0.15s; }
.tab-btn:hover { color: #3b82f6; }
.tab-btn.active { color: #3b82f6; border-bottom-color: #3b82f6; }
</style>
@endpush

@section('content')
<div class="grid w-full space-y-5 min-w-0">
    {{-- Header Info --}}
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <div class="flex items-center gap-3">
                <h3 class="kt-card-title">{{ $kolam->nama_kolam }}</h3>
                @if($kolam->status === 'aktif')
                    <span class="kt-badge kt-badge-success">Aktif</span>
                @elseif($kolam->status === 'selesai')
                    <span class="kt-badge kt-badge-primary">Selesai</span>
                @else
                    <span class="kt-badge kt-badge-destructive">Batal</span>
                @endif
            </div>
            <a href="{{ route('siklus.show', $kolam->siklus_id) }}" class="kt-btn kt-btn-sm kt-btn-outline">
                <i class="ki-filled ki-arrow-left"></i> Kembali ke Siklus
            </a>
        </div>
        <div class="kt-card-content py-4">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-muted-foreground">Siklus</p>
                    <p class="text-sm font-medium">{{ $kolam->siklus?->nama_siklus ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Blok</p>
                    <p class="text-sm font-medium">{{ $kolam->blok?->nama_blok ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Total Tebar</p>
                    <p class="text-sm font-medium">{{ $kolam->total_tebar ? number_format($kolam->total_tebar) . ' ekor' : '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Tgl Berdiri</p>
                    <p class="text-sm font-medium">{{ $kolam->tgl_berdiri?->format('d/m/Y') ?? '-' }}</p>
                </div>
            </div>
            @if($kolam->users->count())
            <div class="mt-3">
                <p class="text-xs text-muted-foreground mb-1">Akses User</p>
                <div class="flex flex-wrap gap-1">
                    @foreach($kolam->users as $u)
                        <span class="kt-badge kt-badge-sm kt-badge-outline">{{ $u->nama }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Pakan Analytics --}}
    @if($pakanByJenis->count())
    @php
        $chartLabels = $pakanByJenis->map(fn($item) => $item['name'] . ' (' . number_format($item['total'], 1) . ' kg)')->toJson();
        $chartSeries = $pakanByJenis->pluck('total')->map(fn($v) => (float) $v)->toJson();
    @endphp
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="kt-card min-w-0">
            <div class="kt-card-header min-h-14">
                <h3 class="kt-card-title">Stok Persediaan Pakan</h3>
            </div>
            <div class="kt-card-content py-4">
                <div class="overflow-x-auto">
                    <table class="kt-table kt-table-border w-full">
                        <thead>
                            <tr>
                                <th class="text-center text-xs font-semibold w-10">No.</th>
                                <th class="text-left text-xs font-semibold">Jenis Pakan</th>
                                <th class="text-right text-xs font-semibold">Penggunaan (kg)</th>
                                <th class="text-right text-xs font-semibold">Sisa (kg)</th>
                                <th class="text-center text-xs font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pakanByJenis as $item)
                            <tr>
                                <td class="text-center text-sm">{{ $loop->iteration }}</td>
                                <td class="text-sm">{{ $item['name'] }}</td>
                                <td class="text-right text-sm text-mono">{{ number_format($item['total'], 2) }}</td>
                                <td class="text-right text-sm text-mono @if($item['sisa'] < 5) text-red-500 font-semibold @endif">{{ number_format($item['sisa'], 2) }}</td>
                                <td class="text-center">
                                    @if($item['persediaan_id'])
                                    <a href="{{ route('persediaan.show', $item['persediaan_id']) }}" class="kt-btn kt-btn-sm kt-btn-outline" title="Lihat Detail">
                                        <i class="ki-filled ki-eye text-xs"></i>
                                    </a>
                                    @else
                                    <span class="text-xs text-muted-foreground">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="kt-card min-w-0">
            <div class="kt-card-header min-h-14">
                <h3 class="kt-card-title">Komposisi Jenis Pakan</h3>
            </div>
            <div class="kt-card-content py-4 flex flex-col items-center justify-center">
                <div id="pakanDonutChart" style="width:100%;max-width:320px;height:280px"></div>
            </div>
        </div>
    </div>
    @endif

    {{-- Tabs --}}
    <div class="flex border-b border-gray-200 mb-0">
        <button class="tab-btn active" data-tab="parameter" onclick="switchTab('parameter')">Parameter Harian</button>
        <button class="tab-btn" data-tab="pakan" onclick="switchTab('pakan')">Pemberian Pakan</button>
    </div>

    {{-- Tab: Parameter Harian --}}
    <div class="kt-card overflow-hidden min-w-0" id="tab-parameter">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">Parameter Harian</h3>
            <div class="flex items-center gap-2">
                <button type="button" class="kt-btn kt-btn-sm kt-btn-outline" onclick="document.getElementById('importFile').click()">
                    <i class="ki-filled ki-upload-file"></i> Import
                </button>
                <a href="{{ route('kolam.parameter.export', $kolam) }}" class="kt-btn kt-btn-sm kt-btn-outline">
                    <i class="ki-filled ki-download"></i> Export
                </a>
            </div>
            <input type="file" id="importFile" accept=".xlsx,.xls,.csv" class="hidden" onchange="handleImport(this)">
        </div>
        <div class="excel-wrap">
            <table class="excel-table" id="paramTable">
                <thead>
                    <tr class="th-group">
                        <th class="th-corner" rowspan="2">Tanggal</th>
                        <th rowspan="2">Status</th>
                        <th colspan="2">pH</th>
                        <th colspan="2">DO</th>
                        <th colspan="2">Suhu</th>
                        <th colspan="2">Kecerahan</th>
                        <th rowspan="2">Salinitas</th>
                        <th rowspan="2">Tinggi Air</th>
                        <th rowspan="2">Warna Air</th>
                        <th rowspan="2">ALK</th>
                        <th rowspan="2">CA</th>
                        <th rowspan="2">MG</th>
                        <th rowspan="2">MBW</th>
                        <th rowspan="2">MASA</th>
                        <th rowspan="2">SR</th>
                        <th rowspan="2">PCR</th>
                        <th rowspan="2">Perlakuan</th>
                        <th rowspan="2">Oleh</th>
                        <th class="th-corner-action" rowspan="2"></th>
                    </tr>
                    <tr class="th-sub">
                        <th>Pagi</th><th>Sore</th>
                        <th>Pagi</th><th>Sore</th>
                        <th>Pagi</th><th>Sore</th>
                        <th>Pagi</th><th>Sore</th>
                    </tr>
                </thead>
                <tbody id="paramBody">
                    @foreach($dates as $dateStr)
                        @php
                            $p = $parametersByKeyed->get($dateStr);
                            $isToday = $dateStr === now()->format('Y-m-d');
                            $rowClass = $isToday ? 'row-today' : '';
                        @endphp
                        <tr data-date="{{ $dateStr }}" @if($p) data-id="{{ $p->id }}" @endif class="{{ $rowClass }}">
                            <td class="td-date">
                                {{ \Carbon\Carbon::parse($dateStr)->format('d/m') }}
                                <span class="text-[0.58rem] text-blue-400 font-normal">{{ \Carbon\Carbon::parse($dateStr)->translatedFormat('D') }}</span>
                                @if($isToday)<span class="ml-1 inline-block bg-blue-600 text-white text-[0.5rem] px-1 py-0.5 rounded font-semibold leading-none">Hari ini</span>@endif
                            </td>
                            <td class="td-status">
                                <select class="excel-select param-field" data-field="status" data-date="{{ $dateStr }}" @if($p) data-id="{{ $p->id }}" @endif>
                                    <option value="normal" {{ ($p ? $p->status === 'normal' : true) ? 'selected' : '' }}>Normal</option>
                                    <option value="perhatian" {{ $p && $p->status === 'perhatian' ? 'selected' : '' }}>Perhatian</option>
                                    <option value="kritis" {{ $p && $p->status === 'kritis' ? 'selected' : '' }}>Kritis</option>
                                </select>
                            </td>
                            @foreach(['ph_pagi','ph_sore','do_pagi','do_sore','suhu_pagi','suhu_sore','kecerahan_pagi','kecerahan_sore','salinitas','tinggi_air'] as $f)
                            <td class="td-num"><input type="number" step="0.01" class="excel-input param-field" data-field="{{ $f }}" data-date="{{ $dateStr }}" @if($p) data-id="{{ $p->id }}" @endif value="{{ $p ? ($p->$f ?? '') : '' }}" placeholder="·"></td>
                            @endforeach
                            <td class="td-text"><input type="text" class="excel-input param-field" data-field="warna_air" data-date="{{ $dateStr }}" @if($p) data-id="{{ $p->id }}" @endif value="{{ $p ? ($p->warna_air ?? '') : '' }}" placeholder="·"></td>
                            @foreach(['alk','ca','mg','mbw','masa','sr','pcr'] as $f)
                            <td class="td-num"><input type="number" step="0.01" class="excel-input param-field" data-field="{{ $f }}" data-date="{{ $dateStr }}" @if($p) data-id="{{ $p->id }}" @endif value="{{ $p ? ($p->$f ?? '') : '' }}" placeholder="·"></td>
                            @endforeach
                            <td class="td-text"><input type="text" class="excel-input param-field" data-field="perlakuan_harian" data-date="{{ $dateStr }}" @if($p) data-id="{{ $p->id }}" @endif value="{{ $p ? ($p->perlakuan_harian ?? '') : '' }}" placeholder="·"></td>
                            <td class="td-oleh">{{ $p?->user?->nama ?? '-' }}</td>
                            <td class="td-action">
                                @if($p)
                                <button type="button" class="text-gray-400 hover:text-red-500 transition-colors" onclick="deleteParam(this, '{{ $p->id }}')" title="Hapus">
                                    <i class="ki-filled ki-trash text-xs"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tab: Pemberian Pakan --}}
    <div class="kt-card hidden overflow-hidden min-w-0" id="tab-pakan">
        <div class="kt-card-header min-h-16">
            <h3 class="kt-card-title">Pemberian Pakan</h3>
            <div class="flex items-center gap-2">
                <select id="perPagePakan" class="kt-select kt-select-sm w-20" onchange="changePakanPerPage()">
                    <option value="5" {{ request('per_page', 10) == 5 ? 'selected' : '' }}>5</option>
                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                </select>
                <span class="text-sm text-muted-foreground">dari {{ $pakanGroups->count() }} hari</span>
            </div>
        </div>
        <div class="kt-card-content min-w-0">
            <div class="overflow-auto" style="max-height:65vh">
                <table class="kt-table kt-table-border min-w-max" id="pakanGroupTable">
                    <thead>
                        <tr>
                            <th class="w-10" rowspan="2">No</th>
                            <th rowspan="2">Tanggal</th>
                            <th rowspan="2">Jenis Pakan</th>
                            <th colspan="4" class="!text-center" style="background:rgba(59,130,246,0.08)">Pemberian Pakan</th>
                            <th rowspan="2" class="text-right">Jumlah Pakan</th>
                            <th rowspan="2">Puasa</th>
                            <th rowspan="2" class="text-right">Pakan Kumulatif</th>
                        </tr>
                        <tr>
                            <th class="!text-center text-xs" style="background:rgba(59,130,246,0.08)">06:00</th>
                            <th class="!text-center text-xs" style="background:rgba(59,130,246,0.08)">10:00</th>
                            <th class="!text-center text-xs" style="background:rgba(59,130,246,0.08)">14:00</th>
                            <th class="!text-center text-xs" style="background:rgba(59,130,246,0.08)">18:00</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pagedPakan as $gi => $group)
                            @php
                                $rowNo = $pagedPakan->firstItem() + $gi;
                                $kumulatif = $cumulativeByDate[$group['date']] ?? 0;
                                $hasMultiItems = $group['items']->count() > 1;
                            @endphp
                            @if($hasMultiItems)
                            <tr class="bg-muted/40 cursor-pointer" onclick="togglePakanGroup('{{ $loop->index }}')">
                                <td class="font-medium">{{ $rowNo }}</td>
                                <td>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium">{{ \Carbon\Carbon::parse($group['date'])->format('d/m/y') }}</span>
                                        <span class="text-[0.65rem] text-muted-foreground">{{ \Carbon\Carbon::parse($group['date'])->translatedFormat('D') }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-sm">{{ $group['jenis_pakan'] ?: '-' }}</span>
                                        <i class="ki-filled ki-down text-xs ms-1 transition-transform" id="pakan-icon-{{ $loop->index }}"></i>
                                    </div>
                                </td>
                                @foreach(['06:00','10:00','14:00','18:00'] as $slot)
                                @php $slotItems = $group['by_time'][$slot]; @endphp
                                <td class="!text-center text-mono text-xs">
                                    @if($slotItems->count())
                                        @if($slotItems->count() === 1)
                                            @php $si = $slotItems->first(); @endphp
                                            @if($si->puasa)
                                                <span class="kt-badge kt-badge-sm kt-badge-warning">Puasa</span>
                                            @else
                                                {{ number_format($si->jumlah_pakan, 2) }}
                                            @endif
                                        @else
                                            <span class="text-xs font-medium">{{ number_format($slotItems->where('puasa', false)->sum('jumlah_pakan'), 2) }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted-foreground">·</span>
                                    @endif
                                </td>
                                @endforeach
                                <td class="text-right text-mono text-sm font-medium">{{ number_format($group['total_jumlah'], 2) }} {{ $group['unit'] }}</td>
                                <td>
                                    @if($group['is_puasa'])
                                        <span class="kt-badge kt-badge-sm kt-badge-warning">Ya</span>
                                    @else
                                        <span class="text-xs text-muted-foreground">Tidak</span>
                                    @endif
                                </td>
                                <td class="text-right text-mono text-sm font-semibold text-primary">{{ number_format($kumulatif, 2) }}</td>
                            </tr>
                            @foreach($group['items']->sortBy(fn($i) => $i->tgl_pakan?->format('H:i') ?? '00:00') as $item)
                            <tr class="pakan-child-{{ $loop->parent->index }} hidden" style="border-left:3px solid #3b82f6">
                                <td></td>
                                <td class="text-xs text-muted-foreground">{{ $item->tgl_pakan?->format('H:i') ?? '-' }}</td>
                                <td class="ps-6">
                                    @if($item->puasa)
                                        <span class="kt-badge kt-badge-sm kt-badge-warning" style="width:fit-content">Puasa</span>
                                    @else
                                        <span class="text-sm">{{ $item->itemPersediaan?->deskripsi ?? '-' }}</span>
                                    @endif
                                </td>
                                <td class="!text-center text-mono text-xs">{{ $item->tgl_pakan?->format('H') === '06' ? number_format($item->jumlah_pakan, 2) : '' }}</td>
                                <td class="!text-center text-mono text-xs">{{ $item->tgl_pakan?->format('H') === '10' ? number_format($item->jumlah_pakan, 2) : '' }}</td>
                                <td class="!text-center text-mono text-xs">{{ $item->tgl_pakan?->format('H') === '14' ? number_format($item->jumlah_pakan, 2) : '' }}</td>
                                <td class="!text-center text-mono text-xs">{{ $item->tgl_pakan?->format('H') === '18' ? number_format($item->jumlah_pakan, 2) : '' }}</td>
                                <td class="text-right text-mono text-sm">{{ number_format($item->jumlah_pakan, 2) }} {{ $item->unit ?? 'kg' }}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            @endforeach
                            @else
                            @php $item = $group['items']->first(); @endphp
                            <tr>
                                <td>{{ $rowNo }}</td>
                                <td>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium">{{ \Carbon\Carbon::parse($group['date'])->format('d/m/y') }}</span>
                                        <span class="text-[0.65rem] text-muted-foreground">{{ \Carbon\Carbon::parse($group['date'])->translatedFormat('D') }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($item->puasa)
                                        <span class="kt-badge kt-badge-sm kt-badge-warning">Puasa</span>
                                    @else
                                        <span class="text-sm">{{ $item->itemPersediaan?->deskripsi ?? '-' }}</span>
                                    @endif
                                </td>
                                @foreach(['06:00','10:00','14:00','18:00'] as $slot)
                                @php $slotItems = $group['by_time'][$slot]; @endphp
                                <td class="!text-center text-mono text-xs">
                                    @if($slotItems->count())
                                        @php $si = $slotItems->first(); @endphp
                                        @if($si->puasa)
                                            <span class="kt-badge kt-badge-sm kt-badge-warning">Puasa</span>
                                        @else
                                            {{ number_format($si->jumlah_pakan, 2) }}
                                        @endif
                                    @else
                                        <span class="text-muted-foreground">·</span>
                                    @endif
                                </td>
                                @endforeach
                                <td class="text-right text-mono text-sm">{{ number_format($group['total_jumlah'], 2) }} {{ $group['unit'] }}</td>
                                <td>
                                    @if($item->puasa)
                                        <span class="kt-badge kt-badge-sm kt-badge-warning">Ya</span>
                                    @else
                                        <span class="text-xs text-muted-foreground">Tidak</span>
                                    @endif
                                </td>
                                <td class="text-right text-mono text-sm font-semibold text-primary">{{ number_format($kumulatif, 2) }}</td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($pakanGroups->isEmpty())
            <div class="py-8 text-center text-muted-foreground text-sm">Belum ada data pemberian pakan</div>
            @endif
        </div>
        <div class="kt-card-footer flex items-center justify-between py-3 px-5 border-t">
            <span class="text-sm text-muted-foreground">Menampilkan {{ $pagedPakan->firstItem() }}–{{ $pagedPakan->lastItem() }} dari {{ $pagedPakan->total() }} hari</span>
            <div class="flex items-center gap-1">
                {{ $pagedPakan->onEachSide(1)->appends(['per_page' => request('per_page', 10)])->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
</div>

{{-- Import Dialog --}}
<dialog id="importDialog" class="kt-modal">
    <div class="kt-modal-content sm:max-w-md">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title">Import Parameter dari Excel</h3>
            <button type="button" class="kt-modal-close" onclick="document.getElementById('importDialog').close()">&times;</button>
        </div>
        <div class="kt-modal-body">
            <p class="text-sm text-muted-foreground mb-3">Upload file Excel (.xlsx/.xls) yang berisi data parameter harian. Format harus sesuai template export.</p>
            <form id="importForm" method="POST" action="{{ route('kolam.parameter.import', $kolam) }}" enctype="multipart/form-data">
                @csrf
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="kt-input w-full mb-3">
                <p class="text-xs text-muted-foreground">Kolom wajib di Excel: <strong>Tanggal</strong> (format d/m/Y). Kolom lain bersifat opsional.</p>
            </form>
        </div>
        <div class="kt-modal-footer">
            <button type="button" class="kt-btn kt-btn-outline" onclick="document.getElementById('importDialog').close()">Batal</button>
            <button type="button" class="kt-btn kt-btn-primary" onclick="document.getElementById('importForm').submit()">Import</button>
        </div>
    </div>
</dialog>

@push('scripts')
<script>
function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.toggle('active', b.dataset.tab === tab); });
    document.getElementById('tab-parameter').classList.toggle('hidden', tab !== 'parameter');
    document.getElementById('tab-pakan').classList.toggle('hidden', tab !== 'pakan');
}

function togglePakanGroup(id) {
    var children = document.querySelectorAll('.pakan-child-' + id);
    var icon = document.getElementById('pakan-icon-' + id);
    if (!children.length) return;
    var isHidden = children[0].classList.contains('hidden');
    children.forEach(function(el) {
        if (isHidden) el.classList.remove('hidden');
        else el.classList.add('hidden');
    });
    if (icon) {
        if (isHidden) icon.classList.add('rotate-180');
        else icon.classList.remove('rotate-180');
    }
}

function changePakanPerPage() {
    var val = document.getElementById('perPagePakan').value;
    var url = new URL(window.location.href);
    url.searchParams.set('per_page', val);
    url.searchParams.set('pakan_page', 1);
    window.location.href = url.toString();
}

var saveTimeout = null;
var PARAM_UPDATE_URL = '{{ route("kolam.parameter.update", ["parameter" => "__ID__"]) }}'.replace('__ID__', '');
var PARAM_DELETE_URL = '{{ route("kolam.parameter.destroy", ["parameter" => "__ID__"]) }}'.replace('__ID__', '');

function ensureParameterExists(input, callback) {
    var id = input.dataset.id;
    if (id) { callback(id); return; }
    var dateStr = input.dataset.date;
    var row = input.closest('tr');
    var statusEl = row.querySelector('select[data-field="status"]');
    var data = new FormData();
    data.append('_token', '{{ csrf_token() }}');
    data.append('tgl_parameter', dateStr);
    data.append('status', statusEl ? statusEl.value : 'normal');
    var fields = ['ph_pagi','ph_sore','do_pagi','do_sore','suhu_pagi','suhu_sore','kecerahan_pagi','kecerahan_sore','salinitas','tinggi_air','warna_air','alk','ca','mg','mbw','masa','sr','pcr','perlakuan_harian'];
    fields.forEach(function(f) {
        var el = row.querySelector('[data-field="' + f + '"]');
        if (el && el.value) data.append(f, el.value);
    });
    fetch('{{ route('kolam.parameter.store', $kolam) }}', {
        method: 'POST', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: data
    }).then(function(r) {
        if (!r.ok) throw new Error('Create failed: ' + r.status);
        return r.json();
    }).then(function(json) {
        var newId = json.parameter.id;
        row.dataset.id = newId;
        row.querySelectorAll('.param-field').forEach(function(el) { el.dataset.id = newId; });
        var olehTd = row.querySelector('td.td-oleh');
        if (olehTd) olehTd.textContent = json.parameter.user?.nama || '{{ auth()->user()->nama ?? "-" }}';
        var actionTd = row.querySelector('td.td-action');
        if (actionTd && !actionTd.querySelector('button')) {
            actionTd.innerHTML = '<button type="button" class="text-gray-400 hover:text-red-500 transition-colors" onclick="deleteParam(this, \'' + newId + '\')" title="Hapus"><i class="ki-filled ki-trash text-xs"></i></button>';
        }
        callback(newId);
    }).catch(function() {
        input.classList.add('save-err');
        setTimeout(function() { input.classList.remove('save-err'); }, 1500);
    });
}

function saveField(input) {
    var field = input.dataset.field;
    if (!field) return;
    ensureParameterExists(input, function(id) {
        var data = new FormData();
        data.append('_method', 'PUT');
        data.append('_token', '{{ csrf_token() }}');
        data.append(field, input.value);
        fetch(PARAM_UPDATE_URL + id, {
            method: 'POST', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: data
        }).then(function(r) {
            if (r.ok) { input.classList.add('save-ok'); setTimeout(function() { input.classList.remove('save-ok'); }, 800); }
            else { input.classList.add('save-err'); setTimeout(function() { input.classList.remove('save-err'); }, 1500); }
        }).catch(function() { input.classList.add('save-err'); setTimeout(function() { input.classList.remove('save-err'); }, 1500); });
    });
}

function deleteParam(btn, id) {
    if (!confirm('Hapus parameter ini?')) return;
    var row = btn.closest('tr');
    var data = new FormData();
    data.append('_method', 'DELETE');
    data.append('_token', '{{ csrf_token() }}');
    fetch(PARAM_DELETE_URL + id, {
        method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, body: data
    }).then(function(r) {
        if (r.ok) {
            delete row.dataset.id;
            row.querySelectorAll('.param-field').forEach(function(el) {
                el.dataset.id = '';
                if (el.tagName === 'INPUT') el.value = '';
                if (el.tagName === 'SELECT') el.selectedIndex = 0;
            });
            var olehTd = row.querySelector('td.td-oleh');
            if (olehTd) olehTd.textContent = '-';
            btn.closest('td.td-action').innerHTML = '';
        } else { alert('Gagal menghapus parameter.'); }
    }).catch(function() { alert('Gagal menghapus parameter.'); });
}

function handleImport(input) {
    if (!input.files.length) return;
    var dialog = document.getElementById('importDialog');
    var form = document.getElementById('importForm');
    form.querySelector('input[name="file"]').files = input.files;
    dialog.showModal();
}

function initPakanDonutChart() {
    var donutEl = document.getElementById('pakanDonutChart');
    if (!donutEl) return;
    if (typeof ApexCharts === 'undefined') { setTimeout(initPakanDonutChart, 300); return; }
    var chartLabels = {!! $pakanByJenis->count() ? $chartLabels : '[]' !!};
    var chartSeries = {!! $pakanByJenis->count() ? $chartSeries : '[]' !!};
    if (!chartLabels.length || !chartSeries.length) return;
    var colors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#14b8a6','#f97316','#6366f1','#84cc16'];
    try {
        new ApexCharts(donutEl, {
            chart: { type: 'donut', height: 280, fontFamily: 'Onest, sans-serif', toolbar: { show: false } },
            series: chartSeries,
            labels: chartLabels,
            colors: colors.slice(0, chartLabels.length),
            legend: { position: 'bottom', fontSize: '11px', itemMargin: { horizontal: 6, vertical: 4 } },
            dataLabels: { enabled: false },
            plotOptions: { pie: { donut: { size: '55%', labels: { show: true, name: { show: true, fontSize: '12px' }, value: { show: true, fontSize: '14px', fontWeight: 600, formatter: function(val) { return parseFloat(val).toFixed(1) + ' kg'; } }, total: { show: true, label: 'Total', formatter: function(w) { return w.globals.seriesTotals.reduce(function(a,b){return a+b;},0).toFixed(1) + ' kg'; } } } } } },
            tooltip: { y: { formatter: function(v) { return v.toFixed(2) + ' kg'; } } },
            stroke: { width: 2 },
            states: { hover: { filter: { type: 'none' } } }
        }).render();
    } catch(e) { console.error('ApexCharts render error:', e); }
}

document.addEventListener('DOMContentLoaded', function() {
    var todayRow = document.querySelector('tr[data-date="{{ now()->format('Y-m-d') }}"]');
    if (todayRow) { setTimeout(function() { todayRow.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 300); }
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('param-field') && e.target.tagName === 'SELECT') { saveField(e.target); }
    });
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('param-field') && e.target.tagName !== 'SELECT') {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(function() { saveField(e.target); }, 800);
        }
    });
    initPakanDonutChart();
});
</script>
@endpush
@endsection