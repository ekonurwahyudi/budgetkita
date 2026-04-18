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
                    {{-- Deskripsi --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Deskripsi <span class="text-danger">*</span></label>
                        <textarea name="deskripsi" id="deskripsi" class="kt-input" rows="3" required>{{ old('deskripsi', $investasi?->deskripsi) }}</textarea>
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
                        <label class="text-sm font-medium text-foreground">Eviden (bisa pilih banyak file)</label>
                        <input type="file" name="eviden[]" id="eviden" class="kt-input"
                               accept=".jpg,.jpeg,.png,.gif,.bmp,.webp,.pdf,.xlsx,.xls" multiple>
                        <p class="text-xs text-muted-foreground">Max 5MB per file. Format: JPG, PNG, PDF, Excel</p>
                        @if($investasi && !empty($investasi->eviden))
                        <div class="flex flex-col gap-2 mt-2">
                            <label class="text-sm font-medium text-foreground">Eviden Tersimpan</label>
                            @foreach($investasi->eviden as $path)
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

                    {{-- Catatan --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Catatan</label>
                        <textarea name="catatan" id="catatan" class="kt-input" rows="3">{{ old('catatan', $investasi?->catatan) }}</textarea>
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
});
</script>
@endpush
