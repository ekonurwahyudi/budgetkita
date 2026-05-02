@extends('layouts.app')

@section('title', $gaji ? 'Edit Gaji Karyawan' : 'Tambah Gaji Karyawan')
@section('page-title', $gaji ? 'Edit Gaji Karyawan' : 'Tambah Gaji Karyawan')
@section('page-description', $gaji ? $gaji->nomor_transaksi : 'Input gaji karyawan baru')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">{{ $gaji ? 'Edit Gaji Karyawan' : 'Tambah Gaji Karyawan' }}</h3>
            <a href="{{ route('gaji.index') }}" class="kt-btn kt-btn-outline kt-btn-sm">
                <i class="ki-filled ki-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="kt-card-content py-6">
            <form method="POST"
                  action="{{ $gaji ? route('gaji.update', $gaji) : route('gaji.store') }}"
                  enctype="multipart/form-data" id="gajiForm">
                @csrf
                @if($gaji) @method('PUT') @endif
                <input type="hidden" name="jenis_pembayaran" id="jenis_pembayaran" value="bank">
                <input type="hidden" name="account_bank_id" id="account_bank_id" value="{{ old('account_bank_id', $gaji?->account_bank_id) }}">
                {{-- Hidden fields untuk nilai numerik bersih --}}
                <input type="hidden" name="gaji_pokok" id="gaji_pokok_val" value="{{ old('gaji_pokok', (int)($gaji?->gaji_pokok ?? 0)) }}">
                <input type="hidden" name="upah_lembur" id="upah_lembur_val" value="{{ old('upah_lembur', (int)($gaji?->upah_lembur ?? 0)) }}">
                <input type="hidden" name="bonus" id="bonus_val" value="{{ old('bonus', (int)($gaji?->bonus ?? 0)) }}">
                <input type="hidden" name="pajak" id="pajak_val" value="{{ old('pajak', (int)($gaji?->pajak ?? 0)) }}">
                <input type="hidden" name="bpjs" id="bpjs_val" value="{{ old('bpjs', (int)($gaji?->bpjs ?? 0)) }}">
                <input type="hidden" name="potongan" id="potongan_val" value="{{ old('potongan', (int)($gaji?->potongan ?? 0)) }}">
                <input type="hidden" name="thp" id="thp_val" value="{{ old('thp', (int)($gaji?->thp ?? 0)) }}">

                <div class="flex flex-col gap-5 max-w-2xl">
                    {{-- Karyawan --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Karyawan <span class="text-danger">*</span></label>
                        <select name="user_id" id="user_id" class="kt-select" required>
                            <option value="">-- Pilih Karyawan --</option>
                            @foreach($karyawans as $karyawan)
                            <option value="{{ $karyawan->id }}" {{ old('user_id', $gaji?->user_id) === $karyawan->id ? 'selected' : '' }}>
                                {{ $karyawan->nama }}{{ $karyawan->jabatan ? ' - '.$karyawan->jabatan : '' }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Gaji Pokok & Upah Lembur --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Gaji Pokok <span class="text-danger">*</span></label>
                            <div class="kt-input-group">
                                <span class="kt-input-addon">Rp.</span>
                                <input class="kt-input money-input" type="text" id="gaji_pokok_display" placeholder="0" data-target="gaji_pokok_val" required/>
                            </div>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Upah Lembur</label>
                            <div class="kt-input-group">
                                <span class="kt-input-addon">Rp.</span>
                                <input class="kt-input money-input" type="text" id="upah_lembur_display" placeholder="0" data-target="upah_lembur_val"/>
                            </div>
                        </div>
                    </div>

                    {{-- Bonus & Pajak --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Bonus</label>
                            <div class="kt-input-group">
                                <span class="kt-input-addon">Rp.</span>
                                <input class="kt-input money-input" type="text" id="bonus_display" placeholder="0" data-target="bonus_val"/>
                            </div>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Pajak</label>
                            <div class="kt-input-group">
                                <span class="kt-input-addon">Rp.</span>
                                <input class="kt-input money-input" type="text" id="pajak_display" placeholder="0" data-target="pajak_val"/>
                            </div>
                        </div>
                    </div>

                    {{-- BPJS & Potongan --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">BPJS</label>
                            <div class="kt-input-group">
                                <span class="kt-input-addon">Rp.</span>
                                <input class="kt-input money-input" type="text" id="bpjs_display" placeholder="0" data-target="bpjs_val"/>
                            </div>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Potongan</label>
                            <div class="kt-input-group">
                                <span class="kt-input-addon">Rp.</span>
                                <input class="kt-input money-input" type="text" id="potongan_display" placeholder="0" data-target="potongan_val"/>
                            </div>
                        </div>
                    </div>

                    {{-- THP --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">THP (Take Home Pay)</label>
                        <div class="kt-input-group">
                            <span class="kt-input-addon">Rp.</span>
                            <input class="kt-input" type="text" id="thp_display" placeholder="0" readonly style="background:var(--muted);"/>
                        </div>
                    </div>

                    {{-- Pembayaran --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Pembayaran <span class="text-danger">*</span></label>
                        <select id="pembayaran_combo" class="kt-select" onchange="onPembayaranChange()">
                            @foreach($accountBanks as $bank)
                            <option value="{{ $bank->id }}" data-saldo="{{ $bank->saldo }}"
                                {{ old('account_bank_id', $gaji?->account_bank_id) === $bank->id ? 'selected' : '' }}>
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
                <div class="flex flex-col gap-5 max-w-2xl mt-5">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Eviden</label>
                        <input type="file" name="eviden[]" id="evidenInput" class="kt-input" multiple accept="image/*,.pdf,.xlsx,.xls" onchange="previewEviden(this)">
                        <p class="text-xs text-muted-foreground">Maksimal 5MB per file. Format: JPG, PNG, PDF, Excel.</p>

                        <div id="previewContainer" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3 mt-2"></div>

                        @if($gaji && !empty($gaji->eviden))
                        <div id="existingEviden" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3 mt-2">
                            @foreach($gaji->eviden as $idx => $ev)
                            @php
                                $isPdf = \Illuminate\Support\Str::endsWith(strtolower($ev), ['.pdf']);
                                $isExcel = \Illuminate\Support\Str::endsWith(strtolower($ev), ['.xlsx', '.xls']);
                                $url = \Illuminate\Support\Facades\Storage::url($ev);
                            @endphp
                            <div class="relative group aspect-square rounded-xl border border-border overflow-hidden bg-muted hover:ring-2 hover:ring-primary hover:shadow-md transition-all" id="existing-ev-{{ $idx }}">
                                @if($isPdf)
                                    <a href="{{ $url }}" target="_blank" class="flex flex-col items-center justify-center w-full h-full p-3">
                                        <i class="ki-filled ki-document text-3xl text-primary mb-2"></i>
                                        <span class="text-[10px] text-muted-foreground text-center truncate w-full">PDF</span>
                                    </a>
                                @elseif($isExcel)
                                    <a href="{{ $url }}" target="_blank" class="flex flex-col items-center justify-center w-full h-full p-3">
                                        <i class="ki-filled ki-excel text-3xl text-green-600 mb-2"></i>
                                        <span class="text-[10px] text-muted-foreground text-center truncate w-full">Excel</span>
                                    </a>
                                @else
                                    <img src="{{ $url }}" class="w-full h-full object-cover" alt="Eviden {{ $idx + 1 }}">
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors pointer-events-none"></div>
                                @endif
                                <button type="button" onclick="hapusExistingEviden('{{ $ev }}', 'existing-ev-{{ $idx }}')" class="absolute top-1.5 right-1.5 size-6 rounded-full bg-destructive/90 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-sm" title="Hapus">
                                    <i class="ki-filled ki-cross text-xs"></i>
                                </button>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 mt-8 pt-5 border-t border-border">
                    <a href="{{ route('gaji.index') }}" class="kt-btn kt-btn-outline">Batal</a>
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check"></i>
                        {{ $gaji ? 'Simpan Perubahan' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function formatMoney(val) {
    var n = parseInt(String(val).replace(/\D/g, '')) || 0;
    return n.toLocaleString('id-ID');
}
function parseMoney(val) {
    return parseInt(String(val).replace(/\D/g, '')) || 0;
}

function initMoneyInput(el) {
    var target = document.getElementById(el.dataset.target);
    var initVal = parseInt(target.value) || 0;
    el.value = initVal > 0 ? formatMoney(initVal) : '';
    el.addEventListener('input', function() {
        var raw = parseMoney(this.value);
        this.value = raw > 0 ? formatMoney(raw) : '';
        target.value = raw;
        calcTHP();
    });
}

function calcTHP() {
    var gaji   = parseMoney(document.getElementById('gaji_pokok_display').value);
    var lembur = parseMoney(document.getElementById('upah_lembur_display').value);
    var bonus  = parseMoney(document.getElementById('bonus_display').value);
    var pajak  = parseMoney(document.getElementById('pajak_display').value);
    var bpjs   = parseMoney(document.getElementById('bpjs_display').value);
    var pot    = parseMoney(document.getElementById('potongan_display').value);
    var thp    = gaji + lembur + bonus - pajak - bpjs - pot;
    document.getElementById('thp_display').value = formatMoney(thp);
    document.getElementById('thp_val').value = thp;
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
    document.querySelectorAll('.money-input').forEach(initMoneyInput);
    calcTHP();
    onPembayaranChange();
});

function previewEviden(input) {
    var container = document.getElementById('previewContainer');
    container.innerHTML = '';
    if (input.files) {
        Array.from(input.files).forEach(function(file, index) {
            var isPdf = file.type === 'application/pdf';
            var isExcel = file.type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || file.type === 'application/vnd.ms-excel';
            var div = document.createElement('div');
            div.className = 'relative group aspect-square rounded-xl border border-border overflow-hidden bg-muted hover:ring-2 hover:ring-primary hover:shadow-md transition-all';
            div.id = 'preview-' + index;
            if (isPdf) {
                div.innerHTML =
                    '<div class="flex flex-col items-center justify-center w-full h-full p-3">' +
                        '<i class="ki-filled ki-document text-3xl text-primary mb-2"></i>' +
                        '<span class="text-[10px] text-muted-foreground text-center truncate w-full">' + file.name + '</span>' +
                    '</div>';
            } else if (isExcel) {
                div.innerHTML =
                    '<div class="flex flex-col items-center justify-center w-full h-full p-3">' +
                        '<i class="ki-filled ki-excel text-3xl text-green-600 mb-2"></i>' +
                        '<span class="text-[10px] text-muted-foreground text-center truncate w-full">' + file.name + '</span>' +
                    '</div>';
            } else {
                var reader = new FileReader();
                reader.onload = (function(d, i) {
                    return function(e) {
                        d.innerHTML =
                            '<img src="' + e.target.result + '" class="w-full h-full object-cover" alt="Preview">' +
                            '<div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors pointer-events-none"></div>' +
                            '<button type="button" onclick="removePreview(' + i + ')" class="absolute top-1.5 right-1.5 size-6 rounded-full bg-destructive/90 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-sm" title="Hapus">' +
                                '<i class="ki-filled ki-cross text-xs"></i>' +
                            '</button>';
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
