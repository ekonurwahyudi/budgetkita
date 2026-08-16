@extends('layouts.app')

@section('title', 'Tambah Pemberian Pakan')
@section('page-title', 'Tambah Pemberian Pakan')
@section('page-description', 'Catat pemberian pakan baru')

@push('styles')
<style>
.pakan-table { border-collapse: separate; border-spacing: 0; width: 100%; }
.pakan-table thead th {
    background: #3b82f6; color: #fff;
    font-size: 0.72rem; font-weight: 600; text-transform: uppercase;
    padding: 0.5rem 0.6rem; text-align: left; white-space: nowrap;
    border-bottom: 2px solid #2563eb;
}
.pakan-table thead th:first-child { border-radius: 0.5rem 0 0 0; }
.pakan-table thead th:last-child { border-radius: 0 0.5rem 0 0; }
.pakan-table tbody td {
    padding: 0.3rem 0.35rem; border-bottom: 1px solid #e5e7eb;
}
.pakan-table tbody tr:last-child td { border-bottom: none; }
.pakan-table tbody tr:hover td { background: #f8fafc; }
.pakan-table .kt-select, .pakan-table .kt-input {
    width: 100%; font-size: 0.78rem; height: 32px;
}
.pakan-table .kt-select:focus, .pakan-table .kt-input:focus {
    background: #eff6ff; box-shadow: inset 0 0 0 2px #3b82f6;
}
</style>
@endpush

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">Tambah Pemberian Pakan</h3>
            <a href="{{ route('pemberian-pakan.index') }}" class="kt-btn kt-btn-outline kt-btn-sm">
                <i class="ki-filled ki-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="kt-card-content py-6">
            <form method="POST" action="{{ route('pemberian-pakan.store') }}" id="formPakan">
                @csrf
                <div class="flex flex-col gap-5 max-w-4xl">

                    {{-- Tanggal + Jam --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Tanggal <span class="text-danger">*</span></label>
                            <div class="kt-input">
                                <i class="ki-outline ki-calendar"></i>
                                <input class="grow" name="tgl_pakan_date" id="tgl_pakan_date"
                                       data-kt-date-picker="true"
                                       data-kt-date-picker-input-mode="true"
                                       placeholder="Pilih tanggal"
                                       readonly type="text" required/>
                            </div>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Jam Pemberian <span class="text-danger">*</span></label>
                            <select name="tgl_pakan_time" class="kt-select" required>
                                <option value="06:00">06:00 - Pagi</option>
                                <option value="10:00">10:00 - Siang</option>
                                <option value="14:00">14:00 - Sore</option>
                                <option value="18:00" selected>18:00 - Petang</option>
                            </select>
                        </div>
                    </div>

                    <input type="hidden" name="tgl_pakan" id="tgl_pakan_hidden">

                    {{-- Tambak / Blok / Siklus / Kolam --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Tambak <span class="text-danger">*</span></label>
                            <select id="tambak_id" class="kt-select" required onchange="loadBlokByTambak()">
                                <option value="">-- Pilih Tambak --</option>
                                @foreach($tambaks as $t)
                                <option value="{{ $t->id }}" {{ $autoTambak?->id === $t->id ? 'selected' : '' }}>{{ $t->nama_tambak }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Blok <span class="text-danger">*</span></label>
                            <select name="blok_id" id="blok_id" class="kt-select" required onchange="loadSiklusByBlok()">
                                <option value="">-- Pilih Blok --</option>
                                @foreach($bloks as $b)
                                <option value="{{ $b->id }}" {{ $bloks->count() === 1 ? 'selected' : '' }}>{{ $b->nama_blok }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Siklus <span class="text-danger">*</span></label>
                            <select name="siklus_id" id="siklus_id" class="kt-select" required onchange="loadKolamBySiklus()">
                                <option value="">-- Pilih Siklus --</option>
                                @foreach($sikluses as $s)
                                <option value="{{ $s->id }}" {{ $sikluses->count() === 1 ? 'selected' : '' }}>{{ $s->nama_siklus }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Kolam</label>
                            <select name="kolam_id" id="kolam_id" class="kt-select">
                                <option value="">-- Semua Kolam --</option>
                                @foreach($kolams as $k)
                                <option value="{{ $k->id }}" {{ $kolams->count() === 1 ? 'selected' : '' }}>{{ $k->nama_kolam }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Puasa --}}
                    <div class="flex items-center gap-3 py-2 px-4 rounded-lg border border-border bg-muted/30">
                        <input type="checkbox" name="puasa" id="puasa" value="1" class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary" onchange="togglePuasa()">
                        <label for="puasa" class="text-sm font-medium text-foreground cursor-pointer select-none">
                            Puasa (Tidak memberi pakan pada waktu ini)
                        </label>
                    </div>

                    {{-- Tabel Item Pakan --}}
                    <div id="pakanFields">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-semibold text-foreground">Item Pakan</label>
                            <button type="button" class="kt-btn kt-btn-sm kt-btn-outline" onclick="addItemRow()">
                                <i class="ki-filled ki-plus"></i> Tambah Baris
                            </button>
                        </div>
                        <div class="border rounded-lg overflow-hidden">
                            <table class="pakan-table">
                                <thead>
                                    <tr>
                                        <th style="width:4%">#</th>
                                        <th style="width:44%">Item Pakan</th>
                                        <th style="width:12%">Stok</th>
                                        <th style="width:18%">Jumlah</th>
                                        <th style="width:14%">Satuan</th>
                                        <th style="width:8%"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemTableBody">
                                    <tr class="item-row" data-index="0">
                                        <td class="text-center text-xs text-muted-foreground font-medium row-num">1</td>
                                        <td>
                                            <select name="items[0][item_persediaan_id]" class="kt-select item-select" required>
                                                <option value="">-- Pilih Item --</option>
                                            </select>
                                        </td>
                                        <td class="text-xs text-muted-foreground stok-cell">-</td>
                                        <td><input class="kt-input" type="number" name="items[0][jumlah_pakan]" step="0.01" min="0.01" placeholder="0" required></td>
                                        <td>
                                            <select name="items[0][unit]" class="kt-select" required>
                                                <option value="kg">kg</option>
                                                <option value="gram">gram</option>
                                                <option value="liter">liter</option>
                                                <option value="ml">ml</option>
                                            </select>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="text-gray-300 hover:text-red-500 transition-colors" onclick="removeItemRow(this)" title="Hapus">
                                                <i class="ki-filled ki-trash text-sm"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-8 pt-5 border-t border-border">
                    <a href="{{ route('pemberian-pakan.index') }}" class="kt-btn kt-btn-outline">Batal</a>
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var allItems = @json($itemPakans->values());
var itemIndex = 0;

function togglePuasa() {
    var puasa = document.getElementById('puasa');
    var pakanFields = document.getElementById('pakanFields');
    if (puasa.checked) {
        pakanFields.style.opacity = '0.4';
        pakanFields.style.pointerEvents = 'none';
        pakanFields.querySelectorAll('[required]').forEach(function(el) { el.removeAttribute('required'); });
    } else {
        pakanFields.style.opacity = '1';
        pakanFields.style.pointerEvents = 'auto';
        pakanFields.querySelectorAll('.item-select, .item-row input[type=number]').forEach(function(el) { el.setAttribute('required', 'required'); });
    }
}

function filterItem() {
    var filtered = allItems;
    document.querySelectorAll('.item-select').forEach(function(sel) {
        var currentVal = sel.value;
        sel.innerHTML = '<option value="">-- Pilih Item --</option>' +
            filtered.map(function(i) {
                return '<option value="'+i.id+'" data-stok="'+i.stok+'" data-unit="'+i.unit+'">'+i.nama+'</option>';
            }).join('');
        if (currentVal) sel.value = currentVal;
        sel.onchange = function() { showStokForRow(sel); };
    });
}

function showStokForRow(sel) {
    var row = sel.closest('tr');
    var opt = sel.options[sel.selectedIndex];
    var cell = row.querySelector('.stok-cell');
    if (opt && opt.value) {
        var stok = Number(opt.getAttribute('data-stok')).toLocaleString('id-ID');
        var unit = opt.getAttribute('data-unit');
        cell.textContent = stok + ' ' + unit;
        cell.classList.remove('text-muted-foreground');
        cell.classList.add('text-primary', 'font-medium');
    } else {
        cell.textContent = '-';
        cell.classList.add('text-muted-foreground');
        cell.classList.remove('text-primary', 'font-medium');
    }
}

function addItemRow() {
    itemIndex++;
    var rowCount = document.querySelectorAll('.item-row').length + 1;
    var html = '<tr class="item-row" data-index="'+itemIndex+'">' +
        '<td class="text-center text-xs text-muted-foreground font-medium row-num">'+rowCount+'</td>' +
        '<td><select name="items['+itemIndex+'][item_persediaan_id]" class="kt-select item-select" required onchange="showStokForRow(this)">' +
            '<option value="">-- Pilih Item --</option>' +
            allItems.map(function(i) { return '<option value="'+i.id+'" data-stok="'+i.stok+'" data-unit="'+i.unit+'">'+i.nama+'</option>'; }).join('') +
        '</select></td>' +
        '<td class="text-xs stok-cell">-</td>' +
        '<td><input class="kt-input" type="number" name="items['+itemIndex+'][jumlah_pakan]" step="0.01" min="0.01" placeholder="0" required></td>' +
        '<td><select name="items['+itemIndex+'][unit]" class="kt-select" required><option value="kg">kg</option><option value="gram">gram</option><option value="liter">liter</option><option value="ml">ml</option></select></td>' +
        '<td class="text-center"><button type="button" class="text-gray-300 hover:text-red-500 transition-colors" onclick="removeItemRow(this)" title="Hapus"><i class="ki-filled ki-trash text-sm"></i></button></td>' +
    '</tr>';
    document.getElementById('itemTableBody').insertAdjacentHTML('beforeend', html);
    var puasa = document.getElementById('puasa');
    if (puasa.checked) {
        var lastRow = document.querySelector('.item-row:last-child');
        lastRow.querySelectorAll('[required]').forEach(function(el) { el.removeAttribute('required'); });
    }
}

function removeItemRow(btn) {
    var rows = document.querySelectorAll('.item-row');
    if (rows.length <= 1) return;
    btn.closest('tr').remove();
    renumberRows();
}

function renumberRows() {
    document.querySelectorAll('.item-row').forEach(function(row, i) {
        row.querySelector('.row-num').textContent = i + 1;
    });
}

document.getElementById('formPakan').addEventListener('submit', function(e) {
    var dateEl = document.getElementById('tgl_pakan_date');
    var dateVal = dateEl.value;
    var timeVal = document.querySelector('select[name="tgl_pakan_time"]').value;
    if (dateVal) {
        var parsed = dateEl._flatpickr ? dateEl._flatpickr.selectedDates[0] : null;
        var dateStr = parsed ? parsed.getFullYear() + '-' + String(parsed.getMonth()+1).padStart(2,'0') + '-' + String(parsed.getDate()).padStart(2,'0') : dateVal;
        document.getElementById('tgl_pakan_hidden').value = dateStr + ' ' + timeVal;
    }
    if (document.getElementById('puasa').checked) {
        document.getElementById('pakanFields').querySelectorAll('[required]').forEach(function(el) { el.removeAttribute('required'); });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    filterItem();

    @if($autoTambak)
    loadBlokByTambak();
    @endif
    @if($bloks->count() === 1)
    setTimeout(function() { loadSiklusByBlok(); }, 100);
    @endif
    @if($sikluses->count() === 1)
    setTimeout(function() { loadKolamBySiklus(); }, 200);
    @endif

    var dateEl = document.getElementById('tgl_pakan_date');
    if (dateEl && !dateEl.value) {
        setTimeout(function() {
            if (dateEl._flatpickr) dateEl._flatpickr.setDate(new Date(), true);
        }, 300);
    }
});

function loadBlokByTambak() {
    var tambakId = document.getElementById('tambak_id').value;
    var sel = document.getElementById('blok_id');
    document.getElementById('siklus_id').innerHTML = '<option value="">-- Pilih Siklus --</option>';
    document.getElementById('kolam_id').innerHTML = '<option value="">-- Semua Kolam --</option>';
    if (!tambakId) { sel.innerHTML = '<option value="">-- Pilih Blok --</option>'; return; }
    fetch('/budidaya/blok/by-tambak/' + tambakId).then(r => r.json()).then(bloks => {
        sel.innerHTML = '<option value="">-- Pilih Blok --</option>' + bloks.map(b => '<option value="'+b.id+'">'+b.nama_blok+'</option>').join('');
        if (bloks.length === 1) { sel.value = bloks[0].id; loadSiklusByBlok(); }
    });
}

function loadSiklusByBlok() {
    var blokId = document.getElementById('blok_id').value;
    var sel = document.getElementById('siklus_id');
    document.getElementById('kolam_id').innerHTML = '<option value="">-- Semua Kolam --</option>';
    if (!blokId) { sel.innerHTML = '<option value="">-- Pilih Siklus --</option>'; return; }
    fetch('/budidaya/siklus/by-blok/' + blokId).then(r => r.json()).then(sikluses => {
        sel.innerHTML = '<option value="">-- Pilih Siklus --</option>' + sikluses.map(s => '<option value="'+s.id+'">'+s.nama_siklus+'</option>').join('');
        if (sikluses.length === 1) { sel.value = sikluses[0].id; loadKolamBySiklus(); }
    });
}

function loadKolamBySiklus() {
    var siklusId = document.getElementById('siklus_id').value;
    var sel = document.getElementById('kolam_id');
    if (!siklusId) { sel.innerHTML = '<option value="">-- Semua Kolam --</option>'; return; }
    fetch('/budidaya/kolam/by-siklus/' + siklusId).then(r => r.json()).then(kolams => {
        sel.innerHTML = '<option value="">-- Semua Kolam --</option>' + kolams.map(k => '<option value="'+k.id+'">'+k.nama_kolam+'</option>').join('');
        if (kolams.length === 1) { sel.value = kolams[0].id; }
    });
}
</script>
@endpush