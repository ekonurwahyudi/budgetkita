@extends('layouts.app')

@section('title', $hutangPiutang ? 'Edit Hutang/Piutang' : 'Tambah Hutang/Piutang')
@section('page-title', $hutangPiutang ? 'Edit Hutang/Piutang' : 'Tambah Hutang/Piutang')
@section('page-description', $hutangPiutang ? $hutangPiutang->nomor_transaksi : 'Input data hutang/piutang baru')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">{{ $hutangPiutang ? 'Edit Hutang/Piutang' : 'Tambah Hutang/Piutang' }}</h3>
            <a href="{{ route('hutang-piutang.index') }}" class="kt-btn kt-btn-outline kt-btn-sm">
                <i class="ki-filled ki-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="kt-card-content py-6">
            <form method="POST"
                  action="{{ $hutangPiutang ? route('hutang-piutang.update', $hutangPiutang) : route('hutang-piutang.store') }}"
                  enctype="multipart/form-data">
                @csrf
                @if($hutangPiutang) @method('PUT') @endif
                <input type="hidden" name="jenis_pembayaran" id="jenis_pembayaran" value="bank">
                <input type="hidden" name="account_bank_id" id="account_bank_id" value="{{ old('account_bank_id', $hutangPiutang?->account_bank_id) }}">

                <div class="flex flex-col gap-5 max-w-2xl">
                    {{-- Tanggal & Jatuh Tempo --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Tanggal <span class="text-danger">*</span></label>
                            <div class="kt-input">
                                <i class="ki-outline ki-calendar"></i>
                                <input class="grow" name="created_at" id="created_at"
                                       data-kt-date-picker="true" data-kt-date-picker-input-mode="true"
                                       placeholder="Pilih tanggal" readonly type="text" required
                                       value="{{ old('created_at', ($hutangPiutang?->created_at ?? now())->format('Y-m-d')) }}"/>
                            </div>
                            @error('created_at')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Jatuh Tempo <span class="text-danger">*</span></label>
                            <div class="kt-input">
                                <i class="ki-outline ki-calendar"></i>
                                <input class="grow" name="jatuh_tempo" id="jatuh_tempo"
                                       data-kt-date-picker="true" data-kt-date-picker-input-mode="true"
                                       placeholder="Pilih tanggal" readonly type="text" required
                                       value="{{ old('jatuh_tempo', $hutangPiutang?->jatuh_tempo?->format('Y-m-d')) }}"/>
                            </div>
                            @error('jatuh_tempo')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Jenis & Kategori --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Jenis <span class="text-danger">*</span></label>
                            <select name="jenis" id="jenis" class="kt-select" required onchange="filterKategori()">
                                <option value="hutang" {{ old('jenis', $hutangPiutang?->jenis) === 'hutang' ? 'selected' : '' }}>Hutang</option>
                                <option value="piutang" {{ old('jenis', $hutangPiutang?->jenis) === 'piutang' ? 'selected' : '' }}>Piutang</option>
                            </select>
                            @error('jenis')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Kategori <span class="text-danger">*</span></label>
                            <select name="kategori_hutang_piutang_id" id="kategori_hutang_piutang_id" class="kt-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($kategoriHutangPiutangs as $kat)
                                <option value="{{ $kat->id }}"
                                    data-jenis="{{ stripos($kat->deskripsi, 'piutang') !== false ? 'piutang' : 'hutang' }}"
                                    {{ old('kategori_hutang_piutang_id', $hutangPiutang?->kategori_hutang_piutang_id) === $kat->id ? 'selected' : '' }}>
                                    {{ $kat->deskripsi }}
                                </option>
                                @endforeach
                            </select>
                            @error('kategori_hutang_piutang_id')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Pemberi Hutang --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground" id="nama_pemberi_hutang_label">Nama Pemberi Hutang</label>
                        <input type="text" name="nama_pemberi_hutang" id="nama_pemberi_hutang" class="kt-input" value="{{ old('nama_pemberi_hutang', $hutangPiutang?->nama_pemberi_hutang) }}" placeholder="Nama pihak pemberi hutang" />
                        @error('nama_pemberi_hutang')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Aktivitas --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Aktivitas/Kegiatan <span class="text-danger">*</span></label>
                        <textarea name="aktivitas" id="aktivitas" class="kt-input" rows="6" style="height: 94px;" required>{{ old('aktivitas', $hutangPiutang?->aktivitas) }}</textarea>
                        @error('aktivitas')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Nominal & Total Bayar --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Nominal (Pinjaman) <span class="text-danger">*</span></label>
                            <div class="kt-input-group">
                                <span class="kt-input-addon">Rp.</span>
                                <input class="kt-input" type="text" id="nominal_display" placeholder="0" required/>
                                <input type="hidden" name="nominal" id="nominal_val" value="{{ old('nominal', (int)($hutangPiutang?->nominal ?? 0)) }}"/>
                            </div>
                            @error('nominal')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div id="row_total_bayar" class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">
                                Total Bayar
                                <span class="text-xs text-muted-foreground font-normal">(termasuk bunga/biaya lain)</span>
                            </label>
                            <div id="total_bayar_input_group" class="kt-input-group">
                                <span class="kt-input-addon">Rp.</span>
                                <input class="kt-input" type="text" id="total_bayar_display" placeholder="Kosongkan jika sama dengan nominal"/>
                                <input type="hidden" name="total_bayar" id="total_bayar_val" value="{{ old('total_bayar', (int)($hutangPiutang?->total_bayar ?? 0)) }}"/>
                            </div>
                            <p id="total_bayar_hint" class="text-xs text-muted-foreground">Contoh: pinjam Rp 2.000.000 tapi total harus bayar Rp 2.500.000 karena bunga</p>
                        </div>
                    </div>

                    {{-- Sudah Dibayar & Sisa --}}
                    <div id="row_nominal_bayar" class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Sudah Dibayar</label>
                            <div class="kt-input-group">
                                <span class="kt-input-addon">Rp.</span>
                                <input class="kt-input" type="text" id="nominal_bayar_display" placeholder="0"/>
                                <input type="hidden" name="nominal_bayar" id="nominal_bayar_val" value="{{ old('nominal_bayar', (int)($hutangPiutang?->nominal_bayar ?? 0)) }}"/>
                            </div>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Sisa Pembayaran</label>
                            <div class="kt-input-group">
                                <span class="kt-input-addon">Rp.</span>
                                <input class="kt-input" type="text" id="sisa_display" readonly style="background:var(--muted);"/>
                            </div>
                        </div>
                    </div>

                    {{-- Pembayaran --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Account Bank</label>
                        <select id="pembayaran_combo" class="kt-select" onchange="onPembayaranChange()">
                            <option value="">-- Pilih Bank --</option>
                            @foreach($accountBanks as $bank)
                            <option value="{{ $bank->id }}" data-saldo="{{ $bank->saldo }}"
                                {{ old('account_bank_id', $hutangPiutang?->account_bank_id) === $bank->id ? 'selected' : '' }}>
                                {{ $bank->nama_bank }} - {{ $bank->nama_pemilik }}
                            </option>
                            @endforeach
                        </select>
                        <span class="text-xs text-muted-foreground mt-1" id="saldoInfo" style="display:none;">
                            Saldo: <span class="text-mono font-medium text-primary" id="saldoValue"></span>
                        </span>
                    </div>

                    {{-- Catatan --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Catatan</label>
                        <textarea name="catatan" id="catatan" class="kt-input" rows="6" style="height: 94px;">{{ old('catatan', $hutangPiutang?->catatan) }}</textarea>
                    </div>

                    {{-- Eviden --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Eviden</label>
                        <input type="file" name="eviden[]" id="evidenInput" class="kt-input" multiple accept="image/*,.pdf,.xlsx,.xls" onchange="previewEviden(this)">
                        <p class="text-xs text-muted-foreground">Maksimal 5MB per file. Format: JPG, PNG, PDF, Excel.</p>

                        <div id="previewContainer" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3 mt-2"></div>

                        @if($hutangPiutang && !empty($hutangPiutang->eviden))
                        <div id="existingEviden" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3 mt-2">
                            @foreach($hutangPiutang->eviden as $idx => $ev)
                            @php
                                $isPdf = \Illuminate\Support\Str::endsWith(strtolower($ev), ['.pdf']);
                                $isExcel = \Illuminate\Support\Str::endsWith(strtolower($ev), ['.xlsx', '.xls']);
                                $url = \Illuminate\Support\Facades\Storage::url($ev);
                            @endphp
                            <div class="relative group rounded-xl border border-border overflow-hidden bg-muted hover:ring-2 hover:ring-primary hover:shadow-md transition-all" id="existing-ev-{{ $idx }}">
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
                                    <img src="{{ $url }}" class="w-full h-24 object-cover cursor-pointer lb-thumb" alt="Eviden {{ $idx + 1 }}" data-src="{{ $url }}">
                                @endif
                                <div class="flex items-center justify-end px-2 py-1.5 border-t border-border">
                                    <button type="button" onclick="hapusExistingEviden('{{ $ev }}', 'existing-ev-{{ $idx }}')" class="size-5 rounded-full bg-destructive text-white flex items-center justify-center hover:bg-destructive/80 transition-colors" title="Hapus">
                                        <i class="ki-filled ki-cross text-[10px]"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 mt-8 pt-5 border-t border-border">
                    <a href="{{ route('hutang-piutang.index') }}" class="kt-btn kt-btn-outline">Batal</a>
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check"></i>
                        {{ $hutangPiutang ? 'Simpan Perubahan' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- Lightbox Modal --}}
<div id="lb-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.85);align-items:center;justify-content:center;padding:1rem;">
    <button id="lb-close" style="position:absolute;top:1rem;right:1rem;color:#fff;font-size:1.5rem;background:none;border:none;cursor:pointer;">
        <i class="ki-filled ki-cross" style="font-size:1.75rem;"></i>
    </button>
    <img id="lb-img" src="" style="max-width:100%;max-height:90vh;object-fit:contain;border-radius:0.5rem;box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);">
</div>
@endsection

@push('scripts')
<script>
function filterKategori() {
    var jenis = document.getElementById('jenis').value;
    var sel = document.getElementById('kategori_hutang_piutang_id');
    var namaLabel = document.getElementById('nama_pemberi_hutang_label');
    var namaInput = document.getElementById('nama_pemberi_hutang');
    if (namaLabel) namaLabel.textContent = jenis === 'piutang' ? 'Nama Penerima' : 'Nama Pemberi Hutang';
    if (namaInput) namaInput.placeholder = jenis === 'piutang' ? 'Nama pihak penerima piutang' : 'Nama pihak pemberi hutang';
    Array.from(sel.options).forEach(function(opt) {
        if (!opt.value) return;
        opt.hidden = opt.getAttribute('data-jenis') !== jenis;
    });
    if (sel.options[sel.selectedIndex] && sel.options[sel.selectedIndex].hidden) {
        sel.value = '';
    }
    // Show/hide Total Bayar & Sudah Dibayar rows
    var isHutang = jenis === 'hutang';
    document.getElementById('total_bayar_input_group').style.display = isHutang ? '' : 'none';
    document.getElementById('total_bayar_hint').style.display = isHutang ? '' : 'none';
    document.getElementById('row_nominal_bayar').style.display = isHutang ? '' : 'none';
    if (!isHutang) {
        document.getElementById('total_bayar_val').value = 0;
        document.getElementById('total_bayar_display').value = '';
        document.getElementById('nominal_bayar_val').value = 0;
        document.getElementById('nominal_bayar_display').value = '';
    }
    calcSisa();
}

function calcSisa() {
    var jenis = document.getElementById('jenis').value;
    var nominal = parseInt(document.getElementById('nominal_val').value) || 0;
    var totalBayar = parseInt(document.getElementById('total_bayar_val').value) || 0;
    var sudahBayar = parseInt(document.getElementById('nominal_bayar_val').value) || 0;
    // Basis sisa: untuk hutang pakai total_bayar (jika diisi), fallback ke nominal
    var basis = (jenis === 'hutang' && totalBayar > 0) ? totalBayar : nominal;
    var sisa = basis - sudahBayar;
    document.getElementById('sisa_display').value = sisa.toLocaleString('id-ID');
}

function onPembayaranChange() {
    var sel = document.getElementById('pembayaran_combo');
    var val = sel.value;
    document.getElementById('jenis_pembayaran').value = val ? 'bank' : 'cash';
    document.getElementById('account_bank_id').value = val;
    var saldo = val ? sel.options[sel.selectedIndex]?.getAttribute('data-saldo') : null;
    if (saldo && val) {
        document.getElementById('saldoValue').textContent = 'Rp ' + Number(saldo).toLocaleString('id-ID');
        document.getElementById('saldoInfo').style.display = '';
    } else {
        document.getElementById('saldoInfo').style.display = 'none';
    }
}

function initMoney(displayId, hiddenId, cb) {
    var disp = document.getElementById(displayId);
    var hid = document.getElementById(hiddenId);
    var init = parseInt(hid.value) || 0;
    disp.value = init > 0 ? init.toLocaleString('id-ID') : '';
    disp.addEventListener('input', function() {
        var raw = parseInt(this.value.replace(/\D/g,'')) || 0;
        this.value = raw > 0 ? raw.toLocaleString('id-ID') : '';
        hid.value = raw;
        if (cb) cb();
    });
}

document.addEventListener('DOMContentLoaded', function() {
    filterKategori();
    initMoney('nominal_display', 'nominal_val', calcSisa);
    initMoney('total_bayar_display', 'total_bayar_val', calcSisa);
    initMoney('nominal_bayar_display', 'nominal_bayar_val', calcSisa);
    calcSisa();
    onPembayaranChange();

    // Lightbox
    var modal = document.getElementById('lb-modal');
    var img = document.getElementById('lb-img');
    var closeBtn = document.getElementById('lb-close');
    if (modal) {
        function openLb(src) {
            img.src = src;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        function closeLb() {
            modal.style.display = 'none';
            img.src = '';
            document.body.style.overflow = '';
        }
        document.addEventListener('click', function(e) {
            var thumb = e.target.closest('.lb-thumb');
            if (thumb) { e.preventDefault(); openLb(thumb.dataset.src); return; }
            var preview = e.target.closest('.lb-preview');
            if (preview) { e.preventDefault(); openLb(preview.src); return; }
        });
        closeBtn.addEventListener('click', function(e) { e.stopPropagation(); closeLb(); });
        modal.addEventListener('click', function(e) { if (e.target === modal || e.target === img) closeLb(); });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeLb(); });
    }
});

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
                            '<img src="' + e.target.result + '" class="w-full h-24 object-cover cursor-pointer lb-preview" alt="Preview">' +
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

var hapusEvidenList = [];
function hapusExistingEviden(path, elementId) {
    if (!confirm('Yakin hapus eviden ini?')) return;
    hapusEvidenList.push(path);
    var form = document.querySelector('form[enctype="multipart/form-data"]');
    document.querySelectorAll('input[name^="hapus_eviden["]').forEach(function(el) { el.remove(); });
    hapusEvidenList.forEach(function(p, i) {
        var inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'hapus_eviden[' + i + ']';
        inp.value = p;
        form.appendChild(inp);
    });
    document.getElementById(elementId).remove();
}
</script>
@endpush
