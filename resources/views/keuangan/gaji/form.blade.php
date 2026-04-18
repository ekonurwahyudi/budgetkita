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
                        <label class="text-sm font-medium text-foreground">Eviden (bisa pilih banyak file)</label>
                        <input type="file" name="eviden[]" class="kt-input" accept=".jpg,.jpeg,.png,.gif,.bmp,.webp,.pdf,.xlsx,.xls" multiple>
                        <p class="text-xs text-muted-foreground">Max 5MB per file. Format: JPG, PNG, PDF, Excel</p>
                    </div>
                    @if($gaji && !empty($gaji->eviden))
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-foreground">Eviden Tersimpan</label>
                        @foreach($gaji->eviden as $path)
                        <div class="flex items-center justify-between p-2 rounded-lg border border-border bg-accent/30">
                            <div class="flex items-center gap-2 text-sm">
                                <i class="ki-filled ki-file text-muted-foreground"></i>
                                <a href="{{ Storage::url($path) }}" target="_blank" class="kt-link text-xs">{{ basename($path) }}</a>
                            </div>
                            <label class="flex items-center gap-1.5 text-xs text-danger cursor-pointer">
                                <input type="checkbox" name="hapus_eviden[]" value="{{ $path }}" class="size-3"> Hapus
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @endif
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
</script>
@endpush
