@extends('layouts.app')

@section('title', $pembelianPersediaan ? 'Edit Pembelian Persediaan' : 'Tambah Pembelian Persediaan')
@section('page-title', $pembelianPersediaan ? 'Edit Pembelian Persediaan' : 'Tambah Pembelian Persediaan')
@section('page-description', $pembelianPersediaan ? $pembelianPersediaan->nomor_transaksi : 'Input pembelian persediaan baru')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">{{ $pembelianPersediaan ? 'Edit Pembelian Persediaan' : 'Tambah Pembelian Persediaan' }}</h3>
            <a href="{{ route('pembelian-persediaan.index') }}" class="kt-btn kt-btn-outline kt-btn-sm">
                <i class="ki-filled ki-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="kt-card-content py-6">
            <form method="POST"
                  action="{{ $pembelianPersediaan ? route('pembelian-persediaan.update', $pembelianPersediaan) : route('pembelian-persediaan.store') }}"
                  enctype="multipart/form-data" id="formPembelian">
                @csrf
                @if($pembelianPersediaan) @method('PUT') @endif
                <input type="hidden" name="jenis_pembayaran" id="jenis_pembayaran" value="bank">
                <input type="hidden" name="account_bank_id" id="account_bank_id" value="{{ old('account_bank_id', $pembelianPersediaan?->account_bank_id) }}">

                <div class="flex flex-col gap-5 max-w-4xl">
                    {{-- Header Fields --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Tanggal Pembelian <span class="text-danger">*</span></label>
                            <div class="kt-input">
                                <i class="ki-outline ki-calendar"></i>
                                <input class="grow" name="tgl_pembelian" data-kt-date-picker="true" data-kt-date-picker-input-mode="true"
                                       placeholder="Pilih tanggal" readonly type="text" required
                                       value="{{ old('tgl_pembelian', $pembelianPersediaan?->tgl_pembelian?->format('Y-m-d')) }}"/>
                            </div>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Pembayaran <span class="text-danger">*</span></label>
                            <select id="pembayaran_combo" class="kt-select" onchange="onPembayaranChange()">
                                @foreach($accountBanks as $bank)
                                <option value="{{ $bank->id }}" data-saldo="{{ $bank->saldo }}"
                                    {{ old('account_bank_id', $pembelianPersediaan?->account_bank_id) === $bank->id ? 'selected' : '' }}>
                                    {{ $bank->nama_bank }} - {{ $bank->nama_pemilik }}
                                </option>
                                @endforeach
                            </select>
                            <span class="text-xs text-muted-foreground mt-1" id="saldoInfo">
                                Saldo: <span class="text-mono font-medium text-primary" id="saldoValue"></span>
                            </span>
                        </div>
                    </div>

                    {{-- Eviden --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Eviden (bisa pilih banyak file)</label>
                        <input type="file" name="eviden[]" id="evidenInput" class="kt-input" accept=".jpg,.jpeg,.png,.gif,.bmp,.webp,.pdf,.xlsx,.xls" multiple onchange="previewEviden(this)">
                        <p class="text-xs text-muted-foreground">Max 5MB per file. Format: JPG, PNG, PDF, Excel</p>
                        <div id="previewContainer" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3 mt-2"></div>
                    </div>
                    @if($pembelianPersediaan && !empty($pembelianPersediaan->eviden))
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-foreground">Eviden Tersimpan</label>
                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
                            @foreach($pembelianPersediaan->eviden as $idx => $path)
                            @php
                                $isPdf = \Illuminate\Support\Str::endsWith(strtolower($path), ['.pdf']);
                                $isExcel = \Illuminate\Support\Str::endsWith(strtolower($path), ['.xlsx', '.xls']);
                                $url = \Illuminate\Support\Facades\Storage::url($path);
                            @endphp
                            <div class="relative group rounded-xl border border-border overflow-hidden bg-muted hover:ring-2 hover:ring-primary hover:shadow-md transition-all">
                                @if($isPdf)
                                    <a href="{{ $url }}" target="_blank" class="flex flex-col items-center justify-center w-full h-24 p-3">
                                        <i class="ki-filled ki-document text-3xl text-primary mb-2"></i>
                                        <span class="text-[10px] text-muted-foreground text-center truncate w-full">PDF</span>
                                    </a>
                                @elseif($isExcel)
                                    <a href="{{ $url }}" target="_blank" class="flex flex-col items-center justify-center w-full h-24 p-3">
                                        <i class="ki-filled ki-excel text-3xl text-green-600 mb-2"></i>
                                        <span class="text-[10px] text-muted-foreground text-center truncate w-full">Excel</span>
                                    </a>
                                @else
                                    <img src="{{ $url }}" class="w-full h-24 object-cover" alt="Eviden {{ $idx + 1 }}">
                                @endif
                                <div class="flex items-center justify-end px-2 py-1.5 border-t border-border">
                                    <label class="flex items-center gap-1.5 text-xs text-danger cursor-pointer">
                                        <input type="checkbox" name="hapus_eviden[]" value="{{ $path }}" class="size-3"> Hapus
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Catatan --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Catatan</label>
                        <textarea name="catatan" class="kt-input" rows="2">{{ old('catatan', $pembelianPersediaan?->catatan) }}</textarea>
                    </div>

                    {{-- Items Table --}}
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-medium text-foreground">Item Pembelian</label>
                            <button type="button" class="kt-btn kt-btn-sm kt-btn-outline" onclick="addItem()">
                                <i class="ki-filled ki-plus"></i> Tambah Item
                            </button>
                        </div>
                        <div class="kt-table-wrapper kt-scrollable">
                            <table class="kt-table" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th style="min-width:160px">Kategori</th>
                                        <th style="min-width:200px">Item Persediaan</th>
                                        <th style="width:100px">Qty</th>
                                        <th style="width:120px">Satuan</th>
                                        <th style="min-width:160px">Harga Satuan</th>
                                        <th style="min-width:160px">Harga Total</th>
                                        <th style="width:50px"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody"></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5" class="text-end font-semibold">Grand Total</td>
                                        <td class="text-mono font-semibold" id="grandTotalDisplay">Rp 0</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 mt-8 pt-5 border-t border-border">
                    <a href="{{ route('pembelian-persediaan.index') }}" class="kt-btn kt-btn-outline">Batal</a>
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check"></i>
                        {{ $pembelianPersediaan ? 'Simpan Perubahan' : 'Simpan Pembelian' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewEviden(input) {
    var container = document.getElementById('previewContainer');
    container.innerHTML = '';
    if (input.files) {
        Array.from(input.files).forEach(function(file, index) {
            var isPdf = file.type === 'application/pdf';
            var isExcel = file.type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || file.type === 'application/vnd.ms-excel';
            var div = document.createElement('div');
            div.className = 'relative group rounded-xl border border-border overflow-hidden bg-muted hover:ring-2 hover:ring-primary hover:shadow-md transition-all';
            div.id = 'preview-' + index;
            if (isPdf) {
                div.innerHTML =
                    '<div class="flex flex-col items-center justify-center w-full h-24 p-3">' +
                        '<i class="ki-filled ki-document text-3xl text-primary mb-2"></i>' +
                        '<span class="text-[10px] text-muted-foreground text-center truncate w-full">' + file.name + '</span>' +
                    '</div>' +
                    '<div class="flex items-center justify-end px-2 py-1.5 border-t border-border">' +
                        '<button type="button" onclick="removePreview(' + index + ')" class="size-5 rounded-full bg-destructive text-white flex items-center justify-center hover:bg-destructive/80 transition-colors" title="Hapus">' +
                            '<i class="ki-filled ki-cross text-[10px]"></i>' +
                        '</button>' +
                    '</div>';
            } else if (isExcel) {
                div.innerHTML =
                    '<div class="flex flex-col items-center justify-center w-full h-24 p-3">' +
                        '<i class="ki-filled ki-excel text-3xl text-green-600 mb-2"></i>' +
                        '<span class="text-[10px] text-muted-foreground text-center truncate w-full">' + file.name + '</span>' +
                    '</div>' +
                    '<div class="flex items-center justify-end px-2 py-1.5 border-t border-border">' +
                        '<button type="button" onclick="removePreview(' + index + ')" class="size-5 rounded-full bg-destructive text-white flex items-center justify-center hover:bg-destructive/80 transition-colors" title="Hapus">' +
                            '<i class="ki-filled ki-cross text-[10px]"></i>' +
                        '</button>' +
                    '</div>';
            } else {
                var reader = new FileReader();
                reader.onload = (function(d, i) {
                    return function(e) {
                        d.innerHTML =
                            '<img src="' + e.target.result + '" class="w-full h-24 object-cover" alt="Preview">' +
                            '<div class="flex items-center justify-end px-2 py-1.5 border-t border-border">' +
                                '<button type="button" onclick="removePreview(' + i + ')" class="size-5 rounded-full bg-destructive text-white flex items-center justify-center hover:bg-destructive/80 transition-colors" title="Hapus">' +
                                    '<i class="ki-filled ki-cross text-[10px]"></i>' +
                                '</button>' +
                            '</div>';
                    };
                })(div, index);
                reader.readAsDataURL(file);
                container.appendChild(div);
                return;
            }
            container.appendChild(div);
        });
    }
}
function removePreview(index) {
    var el = document.getElementById('preview-' + index);
    if (el) el.remove();
}

var itemIndex = 0;
var itemOptions = @json($itemPersediaans->map(fn($ip) => ['id' => $ip->id, 'kategori_id' => $ip->kategori_persediaan_id, 'label' => $ip->kode_item_persediaan . ' - ' . $ip->deskripsi]));
var kategoriOptions = @json($kategoriPersediaans->map(fn($k) => ['id' => $k->id, 'label' => $k->deskripsi]));

function formatMoney(val) {
    var n = parseInt(String(val).replace(/\D/g, '')) || 0;
    return n.toLocaleString('id-ID');
}
function parseMoney(val) {
    return parseInt(String(val).replace(/\D/g, '')) || 0;
}

function addItem(data) {
    data = data || {};
    var idx = itemIndex++;

    var katHtml = '<option value="">-- Kategori --</option>' +
        kategoriOptions.map(function(k) {
            var sel = (data.kategori_id && data.kategori_id === k.id) ? ' selected' : '';
            return '<option value="'+k.id+'"'+sel+'>'+k.label+'</option>';
        }).join('');

    var row = document.createElement('tr');
    row.id = 'item-row-' + idx;
    row.innerHTML =
        '<td><select class="kt-select" id="kat_'+idx+'" onchange="filterItems('+idx+')">'+katHtml+'</select></td>' +
        '<td><select name="items['+idx+'][item_persediaan_id]" id="item_'+idx+'" class="kt-select" required><option value="">-- Pilih Item --</option></select></td>' +
        '<td><input type="number" name="items['+idx+'][qty]" class="kt-input" min="0.01" step="any" required value="'+(data.qty||'')+'" onchange="calcItemTotal('+idx+')" oninput="calcItemTotal('+idx+')"/></td>' +
        '<td><select name="items['+idx+'][satuan]" class="kt-select" required><option value="kg"'+(data.satuan==='kg'?' selected':'')+'>kg</option><option value="gram"'+(data.satuan==='gram'?' selected':'')+'>gram</option><option value="liter"'+(data.satuan==='liter'?' selected':'')+'>liter</option><option value="ml"'+(data.satuan==='ml'?' selected':'')+'>ml</option><option value="pcs"'+(data.satuan==='pcs'?' selected':'')+'>pcs</option><option value="karung"'+(data.satuan==='karung'?' selected':'')+'>karung</option><option value="botol"'+(data.satuan==='botol'?' selected':'')+'>botol</option></select></td>' +
        '<td><div class="kt-input-group"><span class="kt-input-addon">Rp</span><input type="text" class="kt-input money-item" id="harga_satuan_display_'+idx+'" data-idx="'+idx+'" placeholder="0" oninput="onHargaSatuanInput('+idx+')"/></div><input type="hidden" name="items['+idx+'][harga_satuan]" id="harga_satuan_val_'+idx+'" value="'+(data.harga_satuan||0)+'"/></td>' +
        '<td><div class="kt-input-group"><span class="kt-input-addon">Rp</span><input type="text" class="kt-input" id="harga_total_display_'+idx+'" readonly style="background:var(--muted);" value="'+(data.harga_total ? formatMoney(data.harga_total) : '0')+'"/></div></td>' +
        '<td><button type="button" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger" onclick="removeItem('+idx+')"><i class="ki-filled ki-trash"></i></button></td>';
    document.getElementById('itemsBody').appendChild(row);

    // Set kategori and filter items if editing
    if (data.kategori_id) {
        filterItems(idx, data.item_persediaan_id);
    }
    if (data.harga_satuan) {
        document.getElementById('harga_satuan_display_'+idx).value = formatMoney(data.harga_satuan);
    }
    calcItemTotal(idx);
}

function filterItems(idx, keepVal) {
    var katId = document.getElementById('kat_' + idx).value;
    var sel = document.getElementById('item_' + idx);
    var filtered = katId ? itemOptions.filter(function(o) { return o.kategori_id === katId; }) : itemOptions;
    sel.innerHTML = '<option value="">-- Pilih Item --</option>' +
        filtered.map(function(o) {
            var s = (keepVal && keepVal === o.id) ? ' selected' : '';
            return '<option value="'+o.id+'"'+s+'>'+o.label+'</option>';
        }).join('');
}

function removeItem(idx) {
    var row = document.getElementById('item-row-' + idx);
    if (row) row.remove();
    calcGrandTotal();
}

function onHargaSatuanInput(idx) {
    var display = document.getElementById('harga_satuan_display_' + idx);
    var raw = parseMoney(display.value);
    display.value = raw > 0 ? formatMoney(raw) : '';
    document.getElementById('harga_satuan_val_' + idx).value = raw;
    calcItemTotal(idx);
}

function calcItemTotal(idx) {
    var qty = parseFloat(document.querySelector('[name="items['+idx+'][qty]"]')?.value) || 0;
    var harga = parseMoney(document.getElementById('harga_satuan_display_' + idx)?.value);
    var total = qty * harga;
    var el = document.getElementById('harga_total_display_' + idx);
    if (el) el.value = formatMoney(total);
    calcGrandTotal();
}

function calcGrandTotal() {
    var total = 0;
    document.querySelectorAll('[id^="harga_total_display_"]').forEach(function(el) {
        total += parseMoney(el.value);
    });
    document.getElementById('grandTotalDisplay').textContent = 'Rp ' + formatMoney(total);
}

function onPembayaranChange() {
    var sel = document.getElementById('pembayaran_combo');
    document.getElementById('jenis_pembayaran').value = 'bank';
    document.getElementById('account_bank_id').value = sel.value;
    var saldo = sel.options[sel.selectedIndex]?.getAttribute('data-saldo');
    if (saldo) {
        document.getElementById('saldoValue').textContent = 'Rp ' + Number(saldo).toLocaleString('id-ID');
        document.getElementById('saldoInfo').style.display = '';
    } else {
        document.getElementById('saldoInfo').style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    onPembayaranChange();
    @if($pembelianPersediaan && $pembelianPersediaan->items->count())
        @foreach($pembelianPersediaan->items as $existingItem)
        addItem({
            item_persediaan_id: '{{ $existingItem->item_persediaan_id }}',
            kategori_id: '{{ $existingItem->itemPersediaan?->kategori_persediaan_id }}',
            qty: {{ $existingItem->qty }},
            satuan: '{{ $existingItem->satuan }}',
            harga_satuan: {{ (int)$existingItem->harga_satuan }},
            harga_total: {{ (int)$existingItem->harga_total }}
        });
        @endforeach
    @else
        addItem();
    @endif
});
</script>
@endpush
