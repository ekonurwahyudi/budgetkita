@extends('layouts.app')

@section('title', $transaksi ? 'Edit Transaksi' : 'Tambah Transaksi')
@section('page-title', $transaksi ? 'Edit Transaksi' : 'Tambah Transaksi')
@section('page-description', $transaksi ? $transaksi->nomor_transaksi : 'Input transaksi keuangan baru')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">{{ $transaksi ? 'Edit Transaksi' : 'Tambah Transaksi' }}</h3>
            <a href="{{ route('transaksi.index') }}" class="kt-btn kt-btn-outline kt-btn-sm">
                <i class="ki-filled ki-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="kt-card-content py-6">
            <form method="POST"
                  action="{{ $transaksi ? route('transaksi.update', $transaksi) : route('transaksi.store') }}"
                  enctype="multipart/form-data">
                @csrf
                @if($transaksi) @method('PUT') @endif

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Kolom Kiri --}}
                    <div class="flex flex-col gap-5">
                        {{-- Jenis & Tanggal --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Jenis Transaksi <span class="text-danger">*</span></label>
                                <select name="jenis_transaksi" id="jenis_transaksi" class="kt-select" required>
                                    <option value="uang_masuk" {{ old('jenis_transaksi', $transaksi?->jenis_transaksi) === 'uang_masuk' ? 'selected' : '' }}>Uang Masuk</option>
                                    <option value="uang_keluar" {{ old('jenis_transaksi', $transaksi?->jenis_transaksi) === 'uang_keluar' ? 'selected' : '' }}>Uang Keluar</option>
                                </select>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Tanggal Kwitansi <span class="text-danger">*</span></label>
                                <div class="kt-input">
                                    <i class="ki-outline ki-calendar"></i>
                                    <input class="grow" name="tgl_kwitansi" id="tgl_kwitansi"
                                           data-kt-date-picker="true" data-kt-date-picker-input-mode="true"
                                           placeholder="Pilih tanggal" readonly type="text" required
                                           value="{{ old('tgl_kwitansi', $transaksi?->tgl_kwitansi?->format('Y-m-d')) }}"/>
                                </div>
                            </div>
                        </div>

                        {{-- Aktivitas --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Aktivitas/Kegiatan <span class="text-danger">*</span></label>
                            <textarea name="aktivitas" id="aktivitas" class="kt-input" rows="3" required>{{ old('aktivitas', $transaksi?->aktivitas) }}</textarea>
                        </div>

                        {{-- Kategori & Item --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Kategori Transaksi <span class="text-danger">*</span></label>
                                <select name="kategori_transaksi_id" id="kategori_transaksi_id" class="kt-select" required onchange="loadItemsByKategori()">
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($kategoriTransaksis as $kat)
                                    <option value="{{ $kat->id }}" {{ old('kategori_transaksi_id', $transaksi?->kategori_transaksi_id) === $kat->id ? 'selected' : '' }}>
                                        {{ $kat->kode_kategori }} - {{ $kat->deskripsi }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Item Transaksi <span class="text-danger">*</span></label>
                                <select name="item_transaksi_id" id="item_transaksi_id" class="kt-select" required>
                                    <option value="">-- Pilih Item --</option>
                                    @foreach($itemTransaksis as $it)
                                    <option value="{{ $it->id }}" {{ old('item_transaksi_id', $transaksi?->item_transaksi_id) === $it->id ? 'selected' : '' }}>
                                        {{ $it->kode_item }}{{ $it->deskripsi ? ' - '.$it->deskripsi : '' }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Nominal --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Nominal <span class="text-danger">*</span></label>
                            <div class="kt-input-group">
                                <span class="kt-input-addon">Rp.</span>
                                <input class="kt-input" type="number" name="nominal" id="nominal"
                                       step="0.01" min="0" placeholder="0" required
                                       value="{{ old('nominal', $transaksi?->nominal) }}"/>
                            </div>
                        </div>

                        {{-- Tambak / Blok / Siklus --}}
                        <div class="grid grid-cols-3 gap-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Tambak <span class="text-danger">*</span></label>
                                <select name="tambak_id" id="tambak_id" class="kt-select" required onchange="loadBlokByTambak()">
                                    <option value="">-- Pilih --</option>
                                    @foreach($tambaks as $t)
                                    <option value="{{ $t->id }}" {{ old('tambak_id', $transaksi?->tambak_id) === $t->id ? 'selected' : '' }}>{{ $t->nama_tambak }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Blok</label>
                                <select name="blok_id" id="blok_id" class="kt-select" onchange="loadSiklusByBlok()">
                                    <option value="">-- Pilih --</option>
                                    @foreach($bloks ?? [] as $blok)
                                    <option value="{{ $blok->id }}" {{ old('blok_id', $transaksi?->blok_id) === $blok->id ? 'selected' : '' }}>{{ $blok->nama_blok }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Siklus</label>
                                <select name="siklus_id" id="siklus_id" class="kt-select">
                                    <option value="">-- Pilih --</option>
                                    @foreach($sikluses ?? [] as $siklus)
                                    <option value="{{ $siklus->id }}" {{ old('siklus_id', $transaksi?->siklus_id) === $siklus->id ? 'selected' : '' }}>{{ $siklus->nama_siklus }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Kolom Kanan --}}
                    <div class="flex flex-col gap-5">
                        {{-- Sumber Dana & Pembayaran --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Sumber Dana <span class="text-danger">*</span></label>
                                <select name="sumber_dana_id" id="sumber_dana_id" class="kt-select" required>
                                    <option value="">-- Pilih --</option>
                                    @foreach($sumberDanas as $sd)
                                    <option value="{{ $sd->id }}" {{ old('sumber_dana_id', $transaksi?->sumber_dana_id) === $sd->id ? 'selected' : '' }}>{{ $sd->deskripsi }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Pembayaran <span class="text-danger">*</span></label>
                                <select name="pembayaran_combo" id="pembayaran_combo" class="kt-select" required onchange="onPembayaranChange()">
                                    @foreach($accountBanks as $bank)
                                    <option value="bank|{{ $bank->id }}" data-saldo="{{ $bank->saldo }}"
                                        {{ old('account_bank_id', $transaksi?->account_bank_id) === $bank->id ? 'selected' : '' }}>
                                        {{ $bank->nama_bank }} - {{ $bank->nama_pemilik }}
                                    </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="jenis_pembayaran" id="jenis_pembayaran" value="{{ old('jenis_pembayaran', $transaksi?->jenis_pembayaran ?? 'bank') }}">
                                <input type="hidden" name="account_bank_id" id="account_bank_id" value="{{ old('account_bank_id', $transaksi?->account_bank_id) }}">
                                <span class="text-xs text-muted-foreground mt-1" id="saldoInfo">Saldo: <span class="text-mono font-medium text-primary" id="saldoValue"></span></span>
                            </div>
                        </div>

                        {{-- Catatan --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Catatan</label>
                            <textarea name="catatan" id="catatan" class="kt-input" rows="3">{{ old('catatan', $transaksi?->catatan) }}</textarea>
                        </div>

                        {{-- Eviden Upload Multi --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Eviden (bisa pilih banyak file)</label>
                            <input type="file" name="eviden[]" id="eviden" class="kt-input"
                                   accept=".jpg,.jpeg,.png,.gif,.bmp,.webp,.pdf,.xlsx,.xls"
                                   multiple>
                            <p class="text-xs text-muted-foreground">Max 5MB per file. Format: JPG, PNG, PDF, Excel</p>
                        </div>

                        {{-- Eviden yang sudah ada (edit mode) --}}
                        @if($transaksi && !empty($transaksi->eviden))
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium text-foreground">Eviden Tersimpan</label>
                            <div class="flex flex-col gap-2">
                                @foreach($transaksi->eviden as $path)
                                <div class="flex items-center justify-between p-2 rounded-lg border border-border bg-accent/30">
                                    <div class="flex items-center gap-2 text-sm">
                                        <i class="ki-filled ki-file text-muted-foreground"></i>
                                        <a href="{{ Storage::url($path) }}" target="_blank" class="kt-link text-xs">{{ basename($path) }}</a>
                                    </div>
                                    <label class="flex items-center gap-1.5 text-xs text-danger cursor-pointer">
                                        <input type="checkbox" name="hapus_eviden[]" value="{{ $path }}" class="size-3">
                                        Hapus
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Footer Actions --}}
                <div class="flex items-center justify-end gap-3 mt-8 pt-5 border-t border-border">
                    <a href="{{ route('transaksi.index') }}" class="kt-btn kt-btn-outline">Batal</a>
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check"></i>
                        {{ $transaksi ? 'Simpan Perubahan' : 'Simpan Transaksi' }}
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
    var val = sel.value;
    var parts = val.split('|');
    document.getElementById('jenis_pembayaran').value = 'bank';
    document.getElementById('account_bank_id').value = parts[1] || '';
    var saldo = sel.options[sel.selectedIndex]?.getAttribute('data-saldo');
    var saldoEl = document.getElementById('saldoValue');
    if (saldo !== null && saldo !== '') {
        saldoEl.textContent = 'Rp ' + Number(saldo || 0).toLocaleString('id-ID');
        document.getElementById('saldoInfo').style.display = '';
    } else {
        document.getElementById('saldoInfo').style.display = 'none';
    }
}

function loadItemsByKategori() {
    var katId = document.getElementById('kategori_transaksi_id').value;
    var sel = document.getElementById('item_transaksi_id');
    var currentVal = sel.value;
    if (!katId) { sel.innerHTML = '<option value="">-- Pilih Item --</option>'; return; }
    fetch('/keuangan/transaksi/items-by-kategori/' + katId)
        .then(r => r.json())
        .then(items => {
            sel.innerHTML = '<option value="">-- Pilih Item --</option>' +
                items.map(i => '<option value="'+i.id+'"'+(i.id===currentVal?' selected':'')+'>'+i.kode_item+(i.deskripsi?' - '+i.deskripsi:'')+'</option>').join('');
        });
}

function loadBlokByTambak(keepVal) {
    var tambakId = document.getElementById('tambak_id').value;
    var sel = document.getElementById('blok_id');
    document.getElementById('siklus_id').innerHTML = '<option value="">-- Pilih --</option>';
    if (!tambakId) { sel.innerHTML = '<option value="">-- Pilih --</option>'; return; }
    fetch('/budidaya/blok/by-tambak/' + tambakId)
        .then(r => r.json())
        .then(bloks => {
            sel.innerHTML = '<option value="">-- Pilih --</option>' +
                bloks.map(b => '<option value="'+b.id+'"'+(b.id===keepVal?' selected':'')+'>'+b.nama_blok+'</option>').join('');
            if (keepVal) loadSiklusByBlok('{{ old("siklus_id", $transaksi?->siklus_id) }}');
        });
}

function loadSiklusByBlok(keepVal) {
    var blokId = document.getElementById('blok_id').value;
    var sel = document.getElementById('siklus_id');
    if (!blokId) { sel.innerHTML = '<option value="">-- Pilih --</option>'; return; }
    fetch('/budidaya/siklus/by-blok/' + blokId)
        .then(r => r.json())
        .then(sikluses => {
            sel.innerHTML = '<option value="">-- Pilih --</option>' +
                sikluses.map(s => '<option value="'+s.id+'"'+(s.id===keepVal?' selected':'')+'>'+s.nama_siklus+'</option>').join('');
        });
}

// Init on page load
document.addEventListener('DOMContentLoaded', function() {
    onPembayaranChange();
    @if($transaksi?->tambak_id)
    loadBlokByTambak('{{ $transaksi->blok_id }}');
    @endif
});
</script>
@endpush
