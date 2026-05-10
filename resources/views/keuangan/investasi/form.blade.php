@extends('layouts.app')

@section('title', $investasi ? 'Edit Investasi' : 'Tambah Investasi')
@section('page-title', $investasi ? 'Edit Investasi' : 'Tambah Investasi')
@section('page-description', $investasi ? $investasi->nomor_transaksi : 'Input data investasi baru')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">{{ $investasi ? 'Edit Investasi' : 'Tambah Investasi' }}</h3>
            <a href="{{ route('investasi.index') }}" class="kt-btn kt-btn-outline kt-btn-sm">
                <i class="ki-filled ki-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="kt-card-content py-6">
            <form method="POST"
                  action="{{ $investasi ? route('investasi.update', $investasi) : route('investasi.store') }}"
                  enctype="multipart/form-data">
                @csrf
                @if($investasi) @method('PUT') @endif
                <input type="hidden" name="jenis_pembayaran" id="jenis_pembayaran" value="bank">
                <input type="hidden" name="account_bank_id" id="account_bank_id" value="{{ old('account_bank_id', $investasi?->account_bank_id) }}">

                <div class="flex flex-col gap-5 max-w-2xl">
                    {{-- Tanggal --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Tanggal <span class="text-danger">*</span></label>
                        <div class="kt-input">
                            <i class="ki-outline ki-calendar"></i>
                            <input class="grow" name="created_at" id="created_at"
                                   data-kt-date-picker="true" data-kt-date-picker-input-mode="true"
                                   placeholder="Pilih tanggal" readonly type="text" required
                                   value="{{ old('created_at', ($investasi?->created_at ?? now())->format('Y-m-d')) }}"/>
                        </div>
                        @error('created_at')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Deskripsi --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Deskripsi <span class="text-danger">*</span></label>
                        <textarea name="deskripsi" id="deskripsi" class="kt-input" rows="3" style="height: 60px;" required>{{ old('deskripsi', $investasi?->deskripsi) }}</textarea>
                        @error('deskripsi')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Nominal & Kategori --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Nominal <span class="text-danger">*</span></label>
                            <div class="kt-input-group">
                                <span class="kt-input-addon">Rp.</span>
                                <input class="kt-input" type="text" id="nominal_display" placeholder="0" required/>
                                <input type="hidden" name="nominal" id="nominal_val" value="{{ old('nominal', (int)($investasi?->nominal ?? 0)) }}"/>
                            </div>
                            @error('nominal')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Kategori Investasi <span class="text-danger">*</span></label>
                            <select name="kategori_investasi_id" id="kategori_investasi_id" class="kt-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($kategoriInvestasis as $kat)
                                <option value="{{ $kat->id }}" {{ old('kategori_investasi_id', $investasi?->kategori_investasi_id) === $kat->id ? 'selected' : '' }}>
                                    {{ $kat->deskripsi }}
                                </option>
                                @endforeach
                            </select>
                            @error('kategori_investasi_id')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Pembayaran --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Pembayaran <span class="text-danger">*</span></label>
                        <select id="pembayaran_combo" class="kt-select" onchange="onPembayaranChange()">
                            @foreach($accountBanks as $bank)
                            <option value="{{ $bank->id }}" data-saldo="{{ $bank->saldo }}"
                                {{ old('account_bank_id', $investasi?->account_bank_id) === $bank->id ? 'selected' : '' }}>
                                {{ $bank->nama_bank }} - {{ $bank->nama_pemilik }}
                            </option>
                            @endforeach
                        </select>
                        <span class="text-xs text-muted-foreground mt-1" id="saldoInfo">
                            Saldo: <span class="text-mono font-medium text-primary" id="saldoValue"></span>
                        </span>
                    </div>

                    {{-- Eviden --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Eviden</label>
                        <input type="file" name="eviden[]" id="evidenInput" class="kt-input" multiple accept="image/*,.pdf,.xlsx,.xls" onchange="previewEviden(this)">
                        <p class="text-xs text-muted-foreground">Maksimal 5MB per file. Format: JPG, PNG, PDF, Excel.</p>

                        <div id="previewContainer" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3 mt-2"></div>

                        @if($investasi && !empty($investasi->eviden))
                        <div id="existingEviden" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3 mt-2">
                            @foreach($investasi->eviden as $idx => $ev)
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

                    {{-- Catatan --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Catatan</label>
                        <textarea name="catatan" id="catatan" class="kt-input" rows="3" style="height: 94px;">{{ old('catatan', $investasi?->catatan) }}</textarea>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 mt-8 pt-5 border-t border-border">
                    <a href="{{ route('investasi.index') }}" class="kt-btn kt-btn-outline">Batal</a>
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check"></i>
                        {{ $investasi ? 'Simpan Perubahan' : 'Simpan' }}
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
function onPembayaranChange() {
    var sel = document.getElementById('pembayaran_combo');
    document.getElementById('jenis_pembayaran').value = 'bank';
    document.getElementById('account_bank_id').value = sel.value;
    var saldo = sel.options[sel.selectedIndex]?.getAttribute('data-saldo');
    var saldoEl = document.getElementById('saldoValue');
    if (saldo !== null && saldo !== '') {
        saldoEl.textContent = 'Rp ' + Number(saldo || 0).toLocaleString('id-ID');
        document.getElementById('saldoInfo').style.display = '';
    } else {
        document.getElementById('saldoInfo').style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var disp = document.getElementById('nominal_display');
    var val = document.getElementById('nominal_val');
    var init = parseInt(val.value) || 0;
    disp.value = init > 0 ? init.toLocaleString('id-ID') : '';
    disp.addEventListener('input', function() {
        var raw = parseInt(this.value.replace(/\D/g,'')) || 0;
        this.value = raw > 0 ? raw.toLocaleString('id-ID') : '';
        val.value = raw;
    });
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
