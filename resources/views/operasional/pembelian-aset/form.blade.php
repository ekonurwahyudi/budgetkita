@extends('layouts.app')

@section('title', $pembelianAset ? 'Edit Pembelian Aset' : 'Tambah Pembelian Aset')
@section('page-title', $pembelianAset ? 'Edit Pembelian Aset' : 'Tambah Pembelian Aset')
@section('page-description', $pembelianAset ? $pembelianAset->nama_aset : 'Input pembelian aset baru')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">{{ $pembelianAset ? 'Edit Pembelian Aset' : 'Tambah Pembelian Aset' }}</h3>
            <a href="{{ route('pembelian-aset.index') }}" class="kt-btn kt-btn-outline kt-btn-sm">
                <i class="ki-filled ki-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="kt-card-content py-6">
            <form method="POST"
                  action="{{ $pembelianAset ? route('pembelian-aset.update', $pembelianAset) : route('pembelian-aset.store') }}">
                @csrf
                @if($pembelianAset) @method('PUT') @endif
                <input type="hidden" name="jenis_pembayaran" id="jenis_pembayaran" value="bank">
                <input type="hidden" name="account_bank_id" id="account_bank_id" value="{{ old('account_bank_id', $pembelianAset?->account_bank_id) }}">
                <input type="hidden" name="nominal_pembelian" id="nominal_pembelian_val" value="{{ old('nominal_pembelian', (int)($pembelianAset?->nominal_pembelian ?? 0)) }}">
                <input type="hidden" name="nilai_residu" id="nilai_residu_val" value="{{ old('nilai_residu', (int)($pembelianAset?->nilai_residu ?? 0)) }}">

                <div class="flex flex-col gap-5 max-w-2xl">
                    {{-- Nama Aset --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Nama Aset <span class="text-danger">*</span></label>
                        <input type="text" name="nama_aset" class="kt-input" required value="{{ old('nama_aset', $pembelianAset?->nama_aset) }}" placeholder="Nama aset" />
                    </div>

                    {{-- Kategori & Tanggal --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Kategori Aset <span class="text-danger">*</span></label>
                            <select name="kategori_aset_id" class="kt-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($kategoriAsets as $kat)
                                <option value="{{ $kat->id }}" {{ old('kategori_aset_id', $pembelianAset?->kategori_aset_id) === $kat->id ? 'selected' : '' }}>
                                    {{ $kat->kode_aset }} - {{ $kat->deskripsi }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Tanggal Pembelian <span class="text-danger">*</span></label>
                            <div class="kt-input">
                                <i class="ki-outline ki-calendar"></i>
                                <input class="grow" name="tgl_pembelian" data-kt-date-picker="true" data-kt-date-picker-input-mode="true"
                                       placeholder="Pilih tanggal" readonly type="text" required
                                       value="{{ old('tgl_pembelian', $pembelianAset?->tgl_pembelian?->format('Y-m-d')) }}"/>
                            </div>
                        </div>
                    </div>

                    {{-- Nominal & Umur Manfaat --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Nominal Pembelian <span class="text-danger">*</span></label>
                            <div class="kt-input-group">
                                <span class="kt-input-addon">Rp.</span>
                                <input class="kt-input money-input" type="text" id="nominal_pembelian_display" placeholder="0" data-target="nominal_pembelian_val" required/>
                            </div>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Umur Manfaat (Tahun) <span class="text-danger">*</span></label>
                            <input type="number" name="umur_manfaat" class="kt-input" min="1" required value="{{ old('umur_manfaat', $pembelianAset?->umur_manfaat) }}" placeholder="Tahun" />
                        </div>
                    </div>

                    {{-- Nilai Residu --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Nilai Residu <span class="text-danger">*</span></label>
                        <div class="kt-input-group">
                            <span class="kt-input-addon">Rp.</span>
                            <input class="kt-input money-input" type="text" id="nilai_residu_display" placeholder="0" data-target="nilai_residu_val" required/>
                        </div>
                    </div>

                    {{-- Pembayaran --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Pembayaran <span class="text-danger">*</span></label>
                        <select id="pembayaran_combo" class="kt-select" onchange="onPembayaranChange()">
                            @foreach($accountBanks as $bank)
                            <option value="{{ $bank->id }}" data-saldo="{{ $bank->saldo }}"
                                {{ old('account_bank_id', $pembelianAset?->account_bank_id) === $bank->id ? 'selected' : '' }}>
                                {{ $bank->nama_bank }} - {{ $bank->nama_pemilik }}
                            </option>
                            @endforeach
                        </select>
                        <span class="text-xs text-muted-foreground mt-1" id="saldoInfo">
                            Saldo: <span class="text-mono font-medium text-primary" id="saldoValue"></span>
                        </span>
                    </div>

                    {{-- Catatan --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Catatan</label>
                        <textarea name="catatan" class="kt-input" rows="3">{{ old('catatan', $pembelianAset?->catatan) }}</textarea>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 mt-8 pt-5 border-t border-border">
                    <a href="{{ route('pembelian-aset.index') }}" class="kt-btn kt-btn-outline">Batal</a>
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check"></i>
                        {{ $pembelianAset ? 'Simpan Perubahan' : 'Simpan' }}
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
    });
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
    onPembayaranChange();
});
</script>
@endpush
