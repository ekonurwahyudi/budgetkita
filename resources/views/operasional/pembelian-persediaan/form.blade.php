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
                <input type="hidden" name="nominal_dibayar" id="nominal_dibayar_val" value="{{ old('nominal_dibayar', (int)($pembelianPersediaan?->nominal_dibayar ?? 0)) ?: '0' }}">
                <input type="hidden" name="konfirmasi_hutang" id="konfirmasi_hutang" value="0">

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
                    </div>

                    {{-- Tambak, Blok, Siklus --}}
                    <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1rem;">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Tambak <span class="text-danger">*</span></label>
                            <select name="tambak_id" id="tambak_id" class="kt-select" required onchange="loadBlokByTambak()">
                                <option value="">-- Pilih Tambak --</option>
                                @foreach($tambaks as $tambak)
                                <option value="{{ $tambak->id }}" {{ $selectedTambakId === $tambak->id ? 'selected' : '' }}>
                                    {{ $tambak->nama_tambak }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Blok</label>
                            <select name="blok_id" id="blok_id" class="kt-select" onchange="loadSiklusByBlok()">
                                <option value="">-- Pilih Blok --</option>
                                @foreach($bloks as $blok)
                                <option value="{{ $blok->id }}" {{ $selectedBlokId === $blok->id ? 'selected' : '' }}>
                                    {{ $blok->nama_blok }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Siklus</label>
                            <select name="siklus_id" id="siklus_id" class="kt-select">
                                <option value="">-- Pilih Siklus --</option>
                                @foreach($sikluses as $siklus)
                                <option value="{{ $siklus->id }}" {{ $selectedSiklusId === $siklus->id ? 'selected' : '' }}>
                                    {{ $siklus->nama_siklus }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Pembayaran --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Status Pembayaran <span class="text-danger">*</span></label>
                            <select name="status_pembayaran" id="status_pembayaran" class="kt-select" required onchange="onStatusPembayaranChange()">
                                <option value="lunas" {{ old('status_pembayaran', $pembelianPersediaan?->status_pembayaran ?? 'lunas') === 'lunas' ? 'selected' : '' }}>Lunas</option>
                                <option value="hutang" {{ old('status_pembayaran', $pembelianPersediaan?->status_pembayaran) === 'hutang' ? 'selected' : '' }}>Hutang</option>
                                <option value="sebagian" {{ old('status_pembayaran', $pembelianPersediaan?->status_pembayaran) === 'sebagian' ? 'selected' : '' }}>Bayar Sebagian</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1.5" id="pembayaran_bank_wrap">
                            <label class="text-sm font-medium text-foreground">Account Pembayaran <span class="text-danger">*</span></label>
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

                        <div class="flex flex-col gap-1.5" id="nominal_dibayar_wrap" style="display:none;">
                            <label class="text-sm font-medium text-foreground">Dibayar Sekarang <span class="text-danger">*</span></label>
                            <div class="kt-input-group">
                                <span class="kt-input-addon">Rp</span>
                                <input class="kt-input" type="text" id="nominal_dibayar_display" placeholder="0" oninput="onNominalDibayarInput()"/>
                            </div>
                            <span class="text-xs text-muted-foreground">Sisa akan otomatis tercatat sebagai hutang.</span>
                        </div>
                    </div>

                    <div id="hutang_preview" class="hidden rounded-xl border border-warning/30 bg-warning/10 p-4">
                        <p class="text-sm font-medium text-foreground mb-2">Ringkasan Hutang</p>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-muted-foreground">Dibayar:</span>
                                <span class="font-semibold text-mono" id="preview_dibayar">Rp 0</span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Dicatat Hutang:</span>
                                <span class="font-semibold text-mono text-warning" id="preview_hutang">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    {{-- Eviden & Catatan --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Eviden (bisa pilih banyak file)</label>
                            <input type="file" name="eviden[]" id="evidenInput" class="kt-input" accept=".jpg,.jpeg,.png,.gif,.bmp,.webp,.pdf,.xlsx,.xls" multiple onchange="previewEviden(this)">
                            <p class="text-xs text-muted-foreground">Max 5MB per file. Format: JPG, PNG, PDF, Excel</p>
                            <div id="previewContainer" class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-2"></div>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Catatan</label>
                            <textarea name="catatan" class="kt-input" rows="3" style="height: 72px;">{{ old('catatan', $pembelianPersediaan?->catatan) }}</textarea>
                        </div>
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

                    {{-- Items Table --}}
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-medium text-foreground">Item Pembelian</label>
                            <button type="button" class="kt-btn kt-btn-sm kt-btn-outline" onclick="addItem()">
                                <i class="ki-filled ki-plus"></i> Tambah Item
                            </button>
                        </div>
                        <div class="kt-table-wrapper kt-scrollable rounded-lg border border-border">
                            <table class="kt-table align-middle" id="itemsTable">
                                <thead>
                                    <tr class="bg-muted/40">
                                        <th style="width:16%;min-width:150px">Kategori</th>
                                        <th style="width:22%;min-width:190px">Item Persediaan</th>
                                        <th style="width:9%;min-width:90px">Qty</th>
                                        <th style="width:10%;min-width:110px">Satuan</th>
                                        <th style="width:20%;min-width:170px">Harga Satuan</th>
                                        <th style="width:20%;min-width:170px">Harga Total</th>
                                        <th style="width:3%;min-width:48px"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody"></tbody>
                                <tfoot>
                                    <tr class="bg-muted/30 border-t border-border">
                                        <td colspan="4" class="py-5"></td>
                                        <td class="text-end text-sm font-medium text-muted-foreground py-5 pe-4">Grand Total</td>
                                        <td class="py-5">
                                            <div class="flex items-center justify-end gap-2 text-base font-semibold text-primary">
                                                <span class="text-sm font-medium">Rp</span>
                                                <span class="text-mono" id="grandTotalDisplay">0</span>
                                            </div>
                                        </td>
                                        <td class="py-5"></td>
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
var selectedTambakId = @json($selectedTambakId);
var selectedBlokId = @json($selectedBlokId);
var selectedSiklusId = @json($selectedSiklusId);

function formatMoney(val) {
    var n = parseInt(String(val).replace(/\D/g, '')) || 0;
    return n.toLocaleString('id-ID');
}
function parseMoney(val) {
    return parseInt(String(val).replace(/\D/g, '')) || 0;
}
function formatRp(num) {
    return 'Rp ' + Math.round(num).toLocaleString('id-ID');
}

function loadBlokByTambak(keepVal) {
    var tambakId = document.getElementById('tambak_id').value;
    var blokSelect = document.getElementById('blok_id');
    var siklusSelect = document.getElementById('siklus_id');

    blokSelect.innerHTML = '<option value="">-- Pilih Blok --</option>';
    siklusSelect.innerHTML = '<option value="">-- Pilih Siklus --</option>';
    if (!tambakId) return;

    fetch('/budidaya/blok/by-tambak/' + tambakId)
        .then(function(response) { return response.json(); })
        .then(function(bloks) {
            blokSelect.innerHTML = '<option value="">-- Pilih Blok --</option>' + bloks.map(function(blok) {
                var selected = keepVal && blok.id === keepVal ? ' selected' : '';
                return '<option value="' + blok.id + '"' + selected + '>' + blok.nama_blok + '</option>';
            }).join('');

            if (keepVal) {
                loadSiklusByBlok(selectedSiklusId);
            }
        });
}

function loadSiklusByBlok(keepVal) {
    var blokId = document.getElementById('blok_id').value;
    var siklusSelect = document.getElementById('siklus_id');

    siklusSelect.innerHTML = '<option value="">-- Pilih Siklus --</option>';
    if (!blokId) return;

    fetch('/budidaya/siklus/by-blok/' + blokId)
        .then(function(response) { return response.json(); })
        .then(function(sikluses) {
            siklusSelect.innerHTML = '<option value="">-- Pilih Siklus --</option>' + sikluses.map(function(siklus) {
                var selected = keepVal && siklus.id === keepVal ? ' selected' : '';
                return '<option value="' + siklus.id + '"' + selected + '>' + siklus.nama_siklus + '</option>';
            }).join('');
        });
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
        '<td class="py-3"><select class="kt-select w-full" id="kat_'+idx+'" onchange="filterItems('+idx+')">'+katHtml+'</select></td>' +
        '<td class="py-3"><select name="items['+idx+'][item_persediaan_id]" id="item_'+idx+'" class="kt-select w-full" required><option value="">-- Pilih Item --</option></select></td>' +
        '<td class="py-3"><input type="number" name="items['+idx+'][qty]" class="kt-input w-full text-mono" min="0.01" step="any" required value="'+(data.qty||'')+'" onchange="calcItemTotal('+idx+')" oninput="calcItemTotal('+idx+')"/></td>' +
        '<td class="py-3"><select name="items['+idx+'][satuan]" class="kt-select w-full" required><option value="kg"'+(data.satuan==='kg'?' selected':'')+'>kg</option><option value="gram"'+(data.satuan==='gram'?' selected':'')+'>gram</option><option value="liter"'+(data.satuan==='liter'?' selected':'')+'>liter</option><option value="ml"'+(data.satuan==='ml'?' selected':'')+'>ml</option><option value="pcs"'+(data.satuan==='pcs'?' selected':'')+'>pcs</option><option value="karung"'+(data.satuan==='karung'?' selected':'')+'>karung</option><option value="botol"'+(data.satuan==='botol'?' selected':'')+'>botol</option></select></td>' +
        '<td class="py-3"><div class="kt-input-group w-full"><span class="kt-input-addon">Rp</span><input type="text" class="kt-input money-item text-mono" id="harga_satuan_display_'+idx+'" data-idx="'+idx+'" placeholder="0" oninput="onHargaSatuanInput('+idx+')"/></div><input type="hidden" name="items['+idx+'][harga_satuan]" id="harga_satuan_val_'+idx+'" value="'+(data.harga_satuan||0)+'"/></td>' +
        '<td class="py-3"><div class="kt-input-group w-full"><span class="kt-input-addon">Rp</span><input type="text" class="kt-input text-mono font-semibold" id="harga_total_display_'+idx+'" readonly style="background:var(--muted);" value="'+(data.harga_total ? formatMoney(data.harga_total) : '0')+'"/></div></td>' +
        '<td class="py-3 text-center"><button type="button" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger" onclick="removeItem('+idx+')" title="Hapus item"><i class="ki-filled ki-trash"></i></button></td>';
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
    document.getElementById('grandTotalDisplay').textContent = formatMoney(total);
    syncPaymentAmount(total);
    updateHutangPreview();
}

function onPembayaranChange() {
    var sel = document.getElementById('pembayaran_combo');
    document.getElementById('jenis_pembayaran').value = 'bank';
    document.getElementById('account_bank_id').value = document.getElementById('status_pembayaran').value === 'hutang' ? '' : sel.value;
    var saldo = sel.options[sel.selectedIndex]?.getAttribute('data-saldo');
    if (saldo) {
        document.getElementById('saldoValue').textContent = 'Rp ' + Number(saldo).toLocaleString('id-ID');
        document.getElementById('saldoInfo').style.display = '';
    } else {
        document.getElementById('saldoInfo').style.display = 'none';
    }
}

function getGrandTotal() {
    var total = 0;
    document.querySelectorAll('[id^="harga_total_display_"]').forEach(function(el) {
        total += parseMoney(el.value);
    });
    return total;
}

function syncPaymentAmount(total) {
    total = total ?? getGrandTotal();
    var status = document.getElementById('status_pembayaran').value;
    var val = document.getElementById('nominal_dibayar_val');
    var display = document.getElementById('nominal_dibayar_display');

    if (status === 'lunas') {
        val.value = total;
        display.value = formatMoney(total);
    } else if (status === 'hutang') {
        val.value = 0;
        display.value = '0';
    } else {
        var dibayar = Math.min(parseMoney(display.value || val.value), total);
        val.value = dibayar;
        display.value = dibayar > 0 ? formatMoney(dibayar) : '0';
    }
}

function onNominalDibayarInput() {
    var display = document.getElementById('nominal_dibayar_display');
    var raw = parseMoney(display.value);
    var total = getGrandTotal();
    raw = Math.min(raw, total);
    display.value = raw > 0 ? formatMoney(raw) : '0';
    document.getElementById('nominal_dibayar_val').value = raw;
    updateHutangPreview();
}

function onStatusPembayaranChange() {
    var status = document.getElementById('status_pembayaran').value;
    var bankWrap = document.getElementById('pembayaran_bank_wrap');
    var dibayarWrap = document.getElementById('nominal_dibayar_wrap');
    var bankSelect = document.getElementById('pembayaran_combo');

    bankWrap.style.display = status === 'hutang' ? 'none' : '';
    dibayarWrap.style.display = status === 'sebagian' ? '' : 'none';
    bankSelect.required = status !== 'hutang';
    document.getElementById('konfirmasi_hutang').value = '0';

    syncPaymentAmount();
    onPembayaranChange();
    updateHutangPreview();
}

function updateHutangPreview() {
    var status = document.getElementById('status_pembayaran').value;
    var total = getGrandTotal();
    var dibayar = parseInt(document.getElementById('nominal_dibayar_val').value) || 0;
    var hutang = Math.max(0, total - dibayar);
    var preview = document.getElementById('hutang_preview');

    if (!preview || !['hutang', 'sebagian'].includes(status) || hutang <= 0) {
        preview?.classList.add('hidden');
        return;
    }

    document.getElementById('preview_dibayar').textContent = formatRp(dibayar);
    document.getElementById('preview_hutang').textContent = formatRp(hutang);
    preview.classList.remove('hidden');
}

document.addEventListener('DOMContentLoaded', function() {
    if (selectedTambakId) {
        loadBlokByTambak(selectedBlokId);
    }

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

    document.getElementById('nominal_dibayar_display').value = formatMoney(document.getElementById('nominal_dibayar_val').value);
    onStatusPembayaranChange();

    document.getElementById('formPembelian').addEventListener('submit', function(e) {
        var status = document.getElementById('status_pembayaran').value;
        var total = getGrandTotal();
        var dibayar = parseInt(document.getElementById('nominal_dibayar_val').value) || 0;
        var sisa = Math.max(0, total - dibayar);

        if (status === 'sebagian' && sisa > 0 && document.getElementById('konfirmasi_hutang').value !== '1') {
            if (!confirm('Sisa pembayaran sebesar ' + formatRp(sisa) + ' akan dicatat sebagai hutang. Lanjutkan?')) {
                e.preventDefault();
                return;
            }
            document.getElementById('konfirmasi_hutang').value = '1';
        }
    });
});
</script>
@endpush
