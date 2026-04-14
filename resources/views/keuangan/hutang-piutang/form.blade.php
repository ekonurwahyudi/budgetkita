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

                    {{-- Aktivitas --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Aktivitas/Kegiatan <span class="text-danger">*</span></label>
                        <textarea name="aktivitas" id="aktivitas" class="kt-input" rows="3" required>{{ old('aktivitas', $hutangPiutang?->aktivitas) }}</textarea>
                        @error('aktivitas')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Nominal & Jatuh Tempo --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Nominal <span class="text-danger">*</span></label>
                            <div class="kt-input-group">
                                <span class="kt-input-addon">Rp.</span>
                                <input class="kt-input" type="text" id="nominal_display" placeholder="0" required/>
                                <input type="hidden" name="nominal" id="nominal_val" value="{{ old('nominal', (int)($hutangPiutang?->nominal ?? 0)) }}"/>
                            </div>
                            @error('nominal')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
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

                    {{-- Nominal Bayar & Sisa --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Nominal Bayar</label>
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
                        <label class="text-sm font-medium text-foreground">Pembayaran</label>
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
                        <textarea name="catatan" id="catatan" class="kt-input" rows="3">{{ old('catatan', $hutangPiutang?->catatan) }}</textarea>
                    </div>

                    {{-- Eviden --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Eviden (bisa pilih banyak file)</label>
                        <input type="file" name="eviden[]" class="kt-input" accept=".jpg,.jpeg,.png,.gif,.bmp,.webp,.pdf,.xlsx,.xls" multiple>
                        <p class="text-xs text-muted-foreground">Max 5MB per file. Format: JPG, PNG, PDF, Excel</p>
                    </div>
                    @if($hutangPiutang && !empty($hutangPiutang->eviden))
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-foreground">Eviden Tersimpan</label>
                        @foreach($hutangPiutang->eviden as $path)
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
@endsection

@push('scripts')
<script>
function filterKategori() {
    var jenis = document.getElementById('jenis').value;
    var sel = document.getElementById('kategori_hutang_piutang_id');
    var currentVal = sel.value;
    Array.from(sel.options).forEach(function(opt) {
        if (!opt.value) return; // skip placeholder
        opt.hidden = opt.getAttribute('data-jenis') !== jenis;
    });
    // Reset jika pilihan saat ini tidak sesuai jenis
    if (sel.options[sel.selectedIndex] && sel.options[sel.selectedIndex].hidden) {
        sel.value = '';
    }
}

function calcSisa() {
    var nom = parseInt(document.getElementById('nominal_val').value) || 0;
    var bayar = parseInt(document.getElementById('nominal_bayar_val').value) || 0;
    document.getElementById('sisa_display').value = (nom - bayar).toLocaleString('id-ID');
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
    initMoney('nominal_bayar_display', 'nominal_bayar_val', calcSisa);
    calcSisa();
    onPembayaranChange();
});
</script>
@endpush
