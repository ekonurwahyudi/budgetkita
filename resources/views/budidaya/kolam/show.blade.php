@extends('layouts.app')

@section('title', 'Detail Kolam - ' . $kolam->nama_kolam)
@section('page-title', 'Detail Kolam')
@section('page-description', $kolam->nama_kolam . ' · ' . $kolam->siklus?->nama_siklus)

@push('styles')
<style>
.excel-card .kt-card-content { padding: 0 !important; }
.excel-wrap {
    border: 1px solid #c0c8d4; border-radius: 6px;
    max-height: 75vh; overflow: auto; position: relative;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    width: 0; min-width: 100%;
}
.excel-table { border-collapse: separate; border-spacing: 0; }
.excel-table thead th {
    position: sticky; top: 0; z-index: 30;
    background: #3b82f6; color: #fff;
    font-size: 0.68rem; font-weight: 600;
    padding: 0.4rem 0.35rem; text-align: center; white-space: nowrap;
    border-bottom: 1px solid #2563eb; border-right: 1px solid #2563eb;
    box-shadow: 0 1px 2px rgba(37,99,235,0.3);
}
.excel-table thead th.th-corner { position: sticky; left: 0; z-index: 40; background: #2563eb; }
.excel-table thead th.th-corner-action { position: sticky; right: 0; z-index: 40; background: #2563eb; }
.excel-table thead tr.th-group th {
    background: #2563eb; font-size: 0.65rem; padding: 0.25rem 0.35rem;
    border-bottom: 1px solid #1d4ed8; border-right: 1px solid #1d4ed8;
}
.excel-table thead tr.th-sub th {
    background: #3b82f6; font-size: 0.6rem;
    border-bottom: 2px solid #1d4ed8; border-right: 1px solid #2563eb;
}
.excel-table tbody td {
    padding: 0; border-bottom: 1px solid #d1d5db; border-right: 1px solid #d1d5db;
}
.excel-table tbody tr:nth-child(even) td:not(.td-date):not(.td-action) { background: #f9fafb; }
.excel-table tbody tr:hover td { background: #eff6ff !important; }
.excel-table tbody tr.row-today td.td-date { background: #dbeafe !important; }
.excel-table tbody tr.row-today td.td-action { background: #dbeafe !important; }
.excel-table tbody tr.row-today td:not(.td-date):not(.td-action):not(.td-oleh) { background: #eff6ff !important; }
.excel-table tbody td.td-date {
    position: sticky; left: 0; z-index: 10;
    background: #fff; padding: 0.35rem 0.5rem;
    font-size: 0.73rem; font-weight: 600; white-space: nowrap;
    font-variant-numeric: tabular-nums; min-width: 105px;
    border-right: 2px solid #93c5fd;
}
.excel-table tbody td.td-action {
    position: sticky; right: 0; z-index: 10;
    background: #fff; padding: 0.35rem; text-align: center; min-width: 36px;
    border-left: 2px solid #93c5fd;
}
.excel-table tbody td.td-num { min-width: 72px; }
.excel-table tbody td.td-text { min-width: 110px; }
.excel-table tbody td.td-status { min-width: 88px; }
.excel-table tbody td.td-oleh { min-width: 72px; padding: 0 0.35rem; font-size: 0.68rem; color: #6b7280; white-space: nowrap; text-align: center; }
.excel-input {
    width: 100%; border: none; outline: none; background: transparent;
    padding: 0.35rem 0.4rem; font-size: 0.73rem; font-variant-numeric: tabular-nums;
    height: 30px; box-sizing: border-box;
}
.excel-input:focus { background: #dbeafe; box-shadow: inset 0 0 0 2px #3b82f6; position: relative; z-index: 5; }
.excel-input::-webkit-outer-spin-button,
.excel-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.excel-input[type=number] { -moz-appearance: textfield; }
.excel-input:hover:not(:focus) { background: #f0f4ff; }
.excel-select {
    width: 100%; border: none; outline: none; background: transparent;
    padding: 0.35rem 0.4rem; font-size: 0.68rem; cursor: pointer;
    -webkit-appearance: none; appearance: none; height: 30px; box-sizing: border-box;
}
.excel-select:focus { background: #dbeafe; box-shadow: inset 0 0 0 2px #3b82f6; }
.excel-select:hover:not(:focus) { background: #f0f4ff; }
.excel-input.save-ok { background: #bbf7d0 !important; }
.excel-input.save-err { background: #fecaca !important; }
</style>
@endpush

@section('content')
<div class="grid w-full space-y-5">
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

    {{-- Parameter Harian --}}
    <div class="kt-card excel-card">
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
var saveTimeout = null;

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
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: data
    }).then(function(r) {
        if (!r.ok) throw new Error('Create failed: ' + r.status + ' ' + r.statusText);
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

var PARAM_UPDATE_URL = '{{ route("kolam.parameter.update", ["parameter" => "__ID__"]) }}'.replace('__ID__', '');
var PARAM_DELETE_URL = '{{ route("kolam.parameter.destroy", ["parameter" => "__ID__"]) }}'.replace('__ID__', '');

function saveField(input) {
    var field = input.dataset.field;
    if (!field) return;

    ensureParameterExists(input, function(id) {
        var data = new FormData();
        data.append('_method', 'PUT');
        data.append('_token', '{{ csrf_token() }}');
        data.append(field, input.value);

        fetch(PARAM_UPDATE_URL + id, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: data
        }).then(function(r) {
            if (r.ok) {
                input.classList.add('save-ok');
                setTimeout(function() { input.classList.remove('save-ok'); }, 800);
            } else {
                input.classList.add('save-err');
                setTimeout(function() { input.classList.remove('save-err'); }, 1500);
            }
        }).catch(function() {
            input.classList.add('save-err');
            setTimeout(function() { input.classList.remove('save-err'); }, 1500);
        });
    });
}

function deleteParam(btn, id) {
    if (!confirm('Hapus parameter ini?')) return;
    var row = btn.closest('tr');

    var data = new FormData();
    data.append('_method', 'DELETE');
    data.append('_token', '{{ csrf_token() }}');

    fetch(PARAM_DELETE_URL + id, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: data
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
        } else {
            alert('Gagal menghapus parameter.');
        }
    }).catch(function() {
        alert('Gagal menghapus parameter.');
    });
}

function handleImport(input) {
    if (!input.files.length) return;
    var dialog = document.getElementById('importDialog');
    var form = document.getElementById('importForm');
    form.querySelector('input[name="file"]').files = input.files;
    dialog.showModal();
}

document.addEventListener('DOMContentLoaded', function() {
    var todayRow = document.querySelector('tr[data-date="{{ now()->format('Y-m-d') }}"]');
    if (todayRow) {
        setTimeout(function() {
            todayRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 300);
    }

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('param-field') && e.target.tagName === 'SELECT') {
            saveField(e.target);
        }
    });

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('param-field') && e.target.tagName !== 'SELECT') {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(function() { saveField(e.target); }, 800);
        }
    });
});
</script>
@endpush
@endsection