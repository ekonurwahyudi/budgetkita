@extends('layouts.app')

@section('title', $panen ? 'Edit Panen' : 'Tambah Panen')
@section('page-title', $panen ? 'Edit Panen' : 'Tambah Panen')
@section('page-description', $panen ? 'Edit data panen' : 'Input data panen baru')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">{{ $panen ? 'Edit Panen' : 'Tambah Panen' }}</h3>
            <a href="{{ route('panen.index') }}" class="kt-btn kt-btn-outline kt-btn-sm">
                <i class="ki-filled ki-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="kt-card-content py-6">
            <form method="POST"
                  action="{{ $panen ? route('panen.update', $panen) : route('panen.store') }}">
                @csrf
                @if($panen) @method('PUT') @endif

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Kolom Kiri --}}
                    <div class="flex flex-col gap-5">
                        {{-- Siklus --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Siklus <span class="text-danger">*</span></label>
                            <select name="siklus_id" id="siklus_id" class="kt-select" required>
                                <option value="">-- Pilih Siklus --</option>
                                @foreach($sikluses as $s)
                                <option value="{{ $s->id }}" {{ old('siklus_id', $panen?->siklus_id) === $s->id ? 'selected' : '' }}>
                                    {{ $s->blok?->tambak?->nama_tambak ?? '-' }} &rsaquo; {{ $s->blok?->nama_blok ?? '-' }} &rsaquo; {{ $s->nama_siklus }}
                                </option>
                                @endforeach
                            </select>
                            @error('siklus_id')<span class="text-xs text-danger">{{ $message }}</span>@enderror
                        </div>

                        {{-- Tgl Panen & Tipe --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Tanggal Panen <span class="text-danger">*</span></label>
                                <div class="kt-input">
                                    <i class="ki-outline ki-calendar"></i>
                                    <input class="grow" name="tgl_panen" id="tgl_panen"
                                           data-kt-date-picker="true" data-kt-date-picker-input-mode="true"
                                           placeholder="Pilih tanggal" readonly type="text" required
                                           value="{{ old('tgl_panen', $panen?->tgl_panen?->format('Y-m-d')) }}"/>
                                </div>
                                @error('tgl_panen')<span class="text-xs text-danger">{{ $message }}</span>@enderror
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Tipe Panen <span class="text-danger">*</span></label>
                                <select name="tipe_panen" id="tipe_panen" class="kt-select" required>
                                    <option value="full" {{ old('tipe_panen', $panen?->tipe_panen) === 'full' ? 'selected' : '' }}>Full</option>
                                    <option value="parsial" {{ old('tipe_panen', $panen?->tipe_panen) === 'parsial' ? 'selected' : '' }}>Parsial</option>
                                    <option value="gagal" {{ old('tipe_panen', $panen?->tipe_panen) === 'gagal' ? 'selected' : '' }}>Gagal</option>
                                </select>
                            </div>
                        </div>

                        {{-- Umur, Ukuran, Total Berat --}}
                        <div class="grid grid-cols-3 gap-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Umur <span class="text-danger">*</span></label>
                                <div class="kt-input-group">
                                    <input class="kt-input" type="number" name="umur" id="umur" placeholder="0" required
                                           value="{{ old('umur', $panen?->umur) }}"/>
                                    <span class="kt-input-addon">Hari</span>
                                </div>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Ukuran <span class="text-danger">*</span></label>
                                <input type="number" name="ukuran" id="ukuran" class="kt-input" step="0.01" required
                                       value="{{ old('ukuran', $panen?->ukuran) }}">
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Total Berat <span class="text-danger">*</span></label>
                                <div class="kt-input-group">
                                    <input class="kt-input" type="text" id="total_berat_display" placeholder="0"
                                           oninput="formatMoney(this,'total_berat'); calcPanen()"
                                           value="{{ old('total_berat', $panen ? number_format($panen->total_berat, 0, '', '') : '') }}"/>
                                    <input type="hidden" name="total_berat" id="total_berat"
                                           value="{{ old('total_berat', $panen?->total_berat) }}"/>
                                    <span class="kt-input-addon">kg</span>
                                </div>
                            </div>
                        </div>

                        {{-- Harga Jual & Total Penjualan --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Harga Jual (/kg) <span class="text-danger">*</span></label>
                                <div class="kt-input-group">
                                    <span class="kt-input-addon">Rp.</span>
                                    <input class="kt-input" type="text" id="harga_jual_display" placeholder="0"
                                           oninput="formatMoney(this,'harga_jual'); calcPanen()"
                                           value="{{ old('harga_jual', $panen ? number_format($panen->harga_jual, 0, ',', '.') : '') }}"/>
                                    <input type="hidden" name="harga_jual" id="harga_jual"
                                           value="{{ old('harga_jual', $panen?->harga_jual) }}"/>
                                </div>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Total Penjualan</label>
                                <div class="kt-input-group">
                                    <span class="kt-input-addon">Rp.</span>
                                    <input class="kt-input" type="text" id="total_penjualan_display" readonly
                                           style="background:var(--muted);"
                                           value="{{ old('total_penjualan', $panen ? number_format($panen->total_penjualan, 0, ',', '.') : '0') }}"/>
                                    <input type="hidden" name="total_penjualan" id="total_penjualan"
                                           value="{{ old('total_penjualan', $panen?->total_penjualan ?? 0) }}"/>
                                </div>
                            </div>
                        </div>

                        {{-- Pembeli --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Pembeli <span class="text-danger">*</span></label>
                            <input type="text" name="pembeli" id="pembeli" class="kt-input" required
                                   value="{{ old('pembeli', $panen?->pembeli) }}">
                            @error('pembeli')<span class="text-xs text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    {{-- Kolom Kanan --}}
                    <div class="flex flex-col gap-5">
                        {{-- Jenis Pembayaran & Account Bank --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Jenis Pembayaran <span class="text-danger">*</span></label>
                                <select name="jenis_pembayaran" id="jenis_pembayaran" class="kt-select" required onchange="toggleBankField()">
                                    <option value="cash" {{ old('jenis_pembayaran', $panen?->jenis_pembayaran) === 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="bank" {{ old('jenis_pembayaran', $panen?->jenis_pembayaran ?? 'bank') === 'bank' ? 'selected' : '' }}>Bank</option>
                                </select>
                            </div>
                            <div class="flex flex-col gap-1.5" id="bankField">
                                <label class="text-sm font-medium text-foreground">Account Bank</label>
                                <select name="account_bank_id" id="account_bank_id" class="kt-select" onchange="showSaldo()">
                                    <option value="">-- Pilih Bank --</option>
                                    @foreach($accountBanks as $bank)
                                    <option value="{{ $bank->id }}" data-saldo="{{ $bank->saldo }}"
                                        {{ old('account_bank_id', $panen?->account_bank_id) === $bank->id ? 'selected' : '' }}>
                                        {{ $bank->nama_bank }} - {{ $bank->nama_pemilik }}
                                    </option>
                                    @endforeach
                                </select>
                                <span class="text-xs text-muted-foreground mt-1" id="saldoInfo" style="display:none;">Saldo: <span class="text-mono font-medium text-primary" id="saldoValue"></span></span>
                            </div>
                        </div>

                        {{-- Pembayaran & Sisa Bayar --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Pembayaran <span class="text-danger">*</span></label>
                                <select name="pembayaran" id="pembayaran" class="kt-select" required onchange="toggleSisaBayar()">
                                    <option value="lunas" {{ old('pembayaran', $panen?->pembayaran) === 'lunas' ? 'selected' : '' }}>Lunas</option>
                                    <option value="piutang" {{ old('pembayaran', $panen?->pembayaran) === 'piutang' ? 'selected' : '' }}>Piutang</option>
                                </select>
                            </div>
                            <div class="flex flex-col gap-1.5" id="sisaBayarField" style="display:none;">
                                <label class="text-sm font-medium text-foreground">Sisa Bayar</label>
                                <div class="kt-input-group">
                                    <span class="kt-input-addon">Rp.</span>
                                    <input class="kt-input" type="text" id="sisa_bayar_display" placeholder="0"
                                           oninput="formatMoney(this,'sisa_bayar')"
                                           value="{{ old('sisa_bayar', $panen && $panen->sisa_bayar ? number_format($panen->sisa_bayar, 0, ',', '.') : '') }}"/>
                                    <input type="hidden" name="sisa_bayar" id="sisa_bayar"
                                           value="{{ old('sisa_bayar', $panen?->sisa_bayar) }}"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer Actions --}}
                <div class="flex items-center justify-end gap-3 mt-8 pt-5 border-t border-border">
                    <a href="{{ route('panen.index') }}" class="kt-btn kt-btn-outline">Batal</a>
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check"></i>
                        {{ $panen ? 'Simpan Perubahan' : 'Simpan Panen' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function formatMoney(el, hiddenId) {
    var raw = el.value.replace(/[^0-9]/g, '');
    document.getElementById(hiddenId).value = raw;
    el.value = raw ? Number(raw).toLocaleString('id-ID') : '';
}

function calcPanen() {
    var berat = parseFloat(document.getElementById('total_berat').value) || 0;
    var harga = parseFloat(document.getElementById('harga_jual').value) || 0;
    var total = berat * harga;
    document.getElementById('total_penjualan').value = total;
    document.getElementById('total_penjualan_display').value = total ? Number(total).toLocaleString('id-ID') : '0';
}

function toggleBankField() {
    var isCash = document.getElementById('jenis_pembayaran').value === 'cash';
    document.getElementById('bankField').style.display = isCash ? 'none' : '';
    if (isCash) {
        document.getElementById('account_bank_id').value = '';
        document.getElementById('saldoInfo').style.display = 'none';
    } else {
        showSaldo();
    }
}

function showSaldo() {
    var sel = document.getElementById('account_bank_id');
    var opt = sel.options[sel.selectedIndex];
    var saldo = opt?.getAttribute('data-saldo');
    if (saldo && sel.value) {
        document.getElementById('saldoValue').textContent = 'Rp ' + Number(saldo).toLocaleString('id-ID');
        document.getElementById('saldoInfo').style.display = '';
    } else {
        document.getElementById('saldoInfo').style.display = 'none';
    }
}

function toggleSisaBayar() {
    document.getElementById('sisaBayarField').style.display = document.getElementById('pembayaran').value === 'piutang' ? '' : 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    toggleBankField();
    toggleSisaBayar();
    showSaldo();
    // Init display values for edit mode
    var beratEl = document.getElementById('total_berat_display');
    var hargaEl = document.getElementById('harga_jual_display');
    if (beratEl.value && !beratEl.value.includes('.')) {
        var raw = beratEl.value.replace(/[^0-9]/g, '');
        if (raw) beratEl.value = Number(raw).toLocaleString('id-ID');
    }
    if (hargaEl.value && !hargaEl.value.includes('.')) {
        var raw2 = hargaEl.value.replace(/[^0-9]/g, '');
        if (raw2) hargaEl.value = Number(raw2).toLocaleString('id-ID');
    }
});
</script>
@endpush