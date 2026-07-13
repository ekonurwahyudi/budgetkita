@extends('layouts.app')

@section('title', $pembelianAset ? 'Edit Pembelian Aset' : 'Tambah Pembelian Aset')
@section('page-title', $pembelianAset ? 'Edit Pembelian Aset' : 'Tambah Pembelian Aset')
@section('page-description', $pembelianAset ? $pembelianAset->nama_aset : 'Input pembelian aset baru')

@section('content')
<style>
@media (min-width: 1024px) {
    .asset-form-grid {
        display: grid;
        grid-template-columns: minmax(0, 8fr) minmax(280px, 4fr);
        gap: 1.5rem;
        align-items: start;
    }
    .asset-summary {
        position: sticky;
        top: 5.5rem;
    }
}
</style>
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
                  action="{{ $pembelianAset ? route('pembelian-aset.update', $pembelianAset) : route('pembelian-aset.store') }}"
                  enctype="multipart/form-data">
                @csrf
                @if($pembelianAset) @method('PUT') @endif
                <input type="hidden" name="jenis_pembayaran" id="jenis_pembayaran" value="bank">
                <input type="hidden" name="account_bank_id" id="account_bank_id" value="{{ old('account_bank_id', $pembelianAset?->account_bank_id) }}">
                <input type="hidden" name="nominal_pembelian" id="nominal_pembelian_val" value="{{ old('nominal_pembelian', (int)($pembelianAset?->nominal_pembelian ?? 0)) ?: '0' }}">
                <input type="hidden" name="harga_satuan" id="harga_satuan_val" value="{{ old('harga_satuan', (int)($pembelianAset?->harga_satuan ?? $pembelianAset?->nominal_pembelian ?? 0)) ?: '0' }}">
                <input type="hidden" name="nominal_dibayar" id="nominal_dibayar_val" value="{{ old('nominal_dibayar', (int)($pembelianAset?->nominal_dibayar ?? $pembelianAset?->nominal_pembelian ?? 0)) ?: '0' }}">
                <input type="hidden" name="konfirmasi_hutang" id="konfirmasi_hutang" value="0">
                <input type="hidden" name="nilai_residu" id="nilai_residu_val" value="{{ old('nilai_residu', (int)($pembelianAset?->nilai_residu ?? 0)) ?: '0' }}">

                <div class="asset-form-grid grid grid-cols-1 gap-6 items-start">
                    <div class="flex flex-col gap-5">
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

                    {{-- Lokasi --}}
                    <div class="grid grid-cols-3 gap-4">
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

                    {{-- Harga --}}
                    <div class="grid grid-cols-3 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Qty <span class="text-danger">*</span></label>
                            <input type="number" name="qty" id="qty" class="kt-input" min="1" step="1" required value="{{ old('qty', (int)($pembelianAset?->qty ?? 1)) }}" placeholder="1" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Harga Satuan <span class="text-danger">*</span></label>
                            <div class="kt-input-group">
                                <span class="kt-input-addon">Rp.</span>
                                <input class="kt-input money-input" type="text" id="harga_satuan_display" placeholder="0" data-target="harga_satuan_val" required/>
                            </div>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Total Harga</label>
                            <div class="kt-input-group">
                                <span class="kt-input-addon">Rp.</span>
                                <input class="kt-input font-semibold" type="text" id="nominal_pembelian_display" placeholder="0" readonly style="background:var(--muted);"/>
                            </div>
                        </div>
                    </div>

                    {{-- Metode Depresiasi & Persen --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Metode Depresiasi <span class="text-danger">*</span></label>
                            <select name="metode_depresiasi" id="metode_depresiasi" class="kt-select" required onchange="onMetodeDepresiasiChange()">
                                <option value="garis_lurus" {{ old('metode_depresiasi', $pembelianAset?->metode_depresiasi ?? 'garis_lurus') === 'garis_lurus' ? 'selected' : '' }}>Garis Lurus</option>
                                <option value="persen" {{ old('metode_depresiasi', $pembelianAset?->metode_depresiasi) === 'persen' ? 'selected' : '' }}>Persen (%)</option>
                                <option value="tanpa" {{ old('metode_depresiasi', $pembelianAset?->metode_depresiasi) === 'tanpa' ? 'selected' : '' }}>Tanpa Depresiasi</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1.5" id="persen_depresiasi_wrap" style="{{ old('metode_depresiasi', $pembelianAset?->metode_depresiasi ?? 'garis_lurus') === 'persen' ? '' : 'display:none' }}">
                            <label class="text-sm font-medium text-foreground">Persen Depresiasi (% per tahun) <span class="text-danger">*</span></label>
                            <div class="kt-input-group">
                                <input type="number" name="persen_depresiasi" id="persen_depresiasi" class="kt-input grow" min="0" max="100" step="0.01" value="{{ old('persen_depresiasi', $pembelianAset?->persen_depresiasi) }}" placeholder="0" onchange="updateDepresiasiPreview()" oninput="updateDepresiasiPreview()" />
                                <span class="kt-input-addon">%</span>
                            </div>
                        </div>
                    </div>

                    {{-- Umur Manfaat & Nilai Residu --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Umur Manfaat (Tahun) <span class="text-danger">*</span></label>
                            <input type="number" name="umur_manfaat" id="umur_manfaat" class="kt-input" min="0" required value="{{ old('umur_manfaat', $pembelianAset?->umur_manfaat) }}" placeholder="Tahun" />
                        </div>
                        <div class="flex flex-col gap-1.5" id="nilai_residu_wrap">
                            <label class="text-sm font-medium text-foreground">Nilai Residu (Optional)</label>
                            <div class="kt-input-group">
                                <span class="kt-input-addon">Rp.</span>
                                <input class="kt-input money-input" type="text" id="nilai_residu_display" placeholder="0" data-target="nilai_residu_val"/>
                            </div>
                        </div>
                    </div>

                    {{-- Pembayaran --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Status Pembayaran <span class="text-danger">*</span></label>
                            <select name="status_pembayaran" id="status_pembayaran" class="kt-select" required onchange="onStatusPembayaranChange()">
                                <option value="lunas" {{ old('status_pembayaran', $pembelianAset?->status_pembayaran ?? 'lunas') === 'lunas' ? 'selected' : '' }}>Lunas</option>
                                <option value="hutang" {{ old('status_pembayaran', $pembelianAset?->status_pembayaran) === 'hutang' ? 'selected' : '' }}>Hutang</option>
                                <option value="sebagian" {{ old('status_pembayaran', $pembelianAset?->status_pembayaran) === 'sebagian' ? 'selected' : '' }}>Bayar Sebagian</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1.5" id="nominal_dibayar_wrap" style="display:none;">
                            <label class="text-sm font-medium text-foreground">Dibayar Sekarang <span class="text-danger">*</span></label>
                            <div class="kt-input-group">
                                <span class="kt-input-addon">Rp.</span>
                                <input class="kt-input money-input" type="text" id="nominal_dibayar_display" placeholder="0" data-target="nominal_dibayar_val"/>
                            </div>
                        </div>
                        <div class="flex flex-col gap-1.5" id="pembayaran_bank_wrap">
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
                    </div>

                    {{-- Catatan --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Catatan</label>
                        <textarea name="catatan" class="kt-input" rows="3"style="height: 60px;">{{ old('catatan', $pembelianAset?->catatan) }}</textarea>
                    </div>

                    {{-- Foto Aset --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Foto Aset</label>
                        <input type="file" name="foto_aset[]" id="fotoAsetInput" class="kt-input" multiple accept=".png,.jpg,.jpeg" onchange="previewFotoAset(this)">
                        <p class="text-xs text-muted-foreground">Maksimal 5MB per file. Format: PNG, JPG, JPEG. Bisa lebih dari 1 file.</p>
                        <div id="fotoAsetPreview" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3 mt-2"></div>
                        @if($pembelianAset && !empty($pembelianAset->foto_aset))
                        <div id="existingFotoAset" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3 mt-2">
                            @foreach($pembelianAset->foto_aset as $idx => $f)
                            @php $fotoUrl = \Illuminate\Support\Facades\Storage::url($f); @endphp
                            <div class="relative group rounded-xl border border-border overflow-hidden bg-muted hover:ring-2 hover:ring-primary hover:shadow-md transition-all" id="existing-foto-{{ $idx }}">
                                <img src="{{ $fotoUrl }}" class="w-full h-24 object-cover cursor-pointer lb-thumb" alt="Foto Aset {{ $idx + 1 }}" data-src="{{ $fotoUrl }}">
                                <div class="flex items-center justify-end px-2 py-1.5 border-t border-border">
                                    <button type="button" onclick="hapusExistingFoto('{{ $idx }}', 'existing-foto-{{ $idx }}', '{{ $f }}')" class="size-5 rounded-full bg-destructive text-white flex items-center justify-center hover:bg-destructive/80 transition-colors" title="Hapus">
                                        <i class="ki-filled ki-cross text-[10px]"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    {{-- Eviden --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Eviden</label>
                        <input type="file" name="eviden[]" id="evidenInput" class="kt-input" multiple accept=".png,.jpg,.jpeg,.pdf" onchange="previewEviden(this)">
                        <p class="text-xs text-muted-foreground">Maksimal 5MB per file. Format: PNG, JPG, JPEG, PDF.</p>

                        {{-- Preview file yang baru dipilih --}}
                        <div id="previewContainer" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3 mt-2"></div>

                        {{-- File yang sudah tersimpan (mode edit) --}}
                        @if($pembelianAset && !empty($pembelianAset->eviden))
                        <div id="existingEviden" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3 mt-2">
                            @foreach($pembelianAset->eviden as $idx => $ev)
                            @php
                                $isPdf = \Illuminate\Support\Str::endsWith(strtolower($ev), ['.pdf']);
                                $evUrl = \Illuminate\Support\Facades\Storage::url($ev);
                            @endphp
                            <div class="relative group rounded-xl border border-border overflow-hidden bg-muted hover:ring-2 hover:ring-primary hover:shadow-md transition-all" id="existing-ev-{{ $idx }}">
                                @if($isPdf)
                                    <a href="{{ $evUrl }}" target="_blank" class="flex flex-col items-center justify-center w-full h-24 p-3">
                                        <i class="ki-filled ki-document text-3xl text-primary mb-2"></i>
                                        <span class="text-[10px] text-muted-foreground text-center truncate w-full">PDF</span>
                                    </a>
                                @else
                                    <img src="{{ $evUrl }}" class="w-full h-24 object-cover cursor-pointer lb-thumb" alt="Eviden {{ $idx + 1 }}" data-src="{{ $evUrl }}">
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

                    <aside class="asset-summary rounded-xl border border-border bg-muted/30 p-5">
                        <div class="flex items-center gap-2 border-b border-border pb-3 mb-4">
                            <i class="ki-filled ki-chart-simple text-primary"></i>
                            <h4 class="text-sm font-semibold text-foreground">Ringkasan Pembelian</h4>
                        </div>

                        <div class="space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-muted-foreground">Total Harga</span>
                                <span class="font-semibold text-mono text-foreground" id="summary_total">Rp 0</span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-muted-foreground">Status</span>
                                <span class="font-semibold text-foreground" id="summary_status">Lunas</span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-muted-foreground">Dibayar</span>
                                <span class="font-semibold text-mono text-foreground" id="summary_dibayar">Rp 0</span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-muted-foreground">Sisa Hutang</span>
                                <span class="font-semibold text-mono text-warning" id="summary_hutang">Rp 0</span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-muted-foreground">Saldo Bank</span>
                                <span class="font-semibold text-mono text-primary" id="summary_saldo">Rp 0</span>
                            </div>
                        </div>

                        <div id="hutang_preview" class="hidden rounded-xl border border-warning/30 bg-warning/10 p-4 mt-5">
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

                        <div id="depresiasi_preview" class="hidden rounded-xl border border-border bg-background p-4 mt-5">
                            <p class="text-sm font-medium text-foreground mb-2">Simulasi Depresiasi</p>
                            <div class="grid grid-cols-1 gap-3 text-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-muted-foreground">Depresiasi / Tahun</span>
                                    <span class="font-semibold text-mono" id="preview_depresiasi_per_tahun">Rp 0</span>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-muted-foreground">Total Depresiasi</span>
                                    <span class="font-semibold text-mono" id="preview_total_depresiasi">Rp 0</span>
                                </div>
                            </div>
                        </div>
                    </aside>
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
function formatMoney(val) {
    var n = parseInt(String(val).replace(/\D/g, '')) || 0;
    return n.toLocaleString('id-ID');
}
function parseMoney(val) {
    return parseInt(String(val).replace(/\D/g, '')) || 0;
}

var selectedTambakId = @json($selectedTambakId);
var selectedBlokId = @json($selectedBlokId);
var selectedSiklusId = @json($selectedSiklusId);

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
            if (keepVal) loadSiklusByBlok(selectedSiklusId);
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

function initMoneyInput(el) {
    var target = document.getElementById(el.dataset.target);
    var initVal = parseInt(target.value) || 0;
    el.value = initVal > 0 ? formatMoney(initVal) : '0';
    el.addEventListener('input', function() {
        var raw = parseMoney(this.value);
        this.value = raw > 0 ? formatMoney(raw) : '0';
        target.value = raw;
        updateAssetTotal();
    });
    el.addEventListener('blur', function() {
        var raw = parseMoney(this.value);
        this.value = raw > 0 ? formatMoney(raw) : '0';
        target.value = raw;
        updateAssetTotal();
    });
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
    updateSummary();
}

function updateAssetTotal() {
    var qty = parseInt(document.getElementById('qty').value) || 0;
    var hargaSatuan = parseInt(document.getElementById('harga_satuan_val').value) || 0;
    var total = Math.round(qty * hargaSatuan);
    var status = document.getElementById('status_pembayaran').value;
    var nominalDibayarVal = document.getElementById('nominal_dibayar_val');
    var nominalDibayarDisplay = document.getElementById('nominal_dibayar_display');

    document.getElementById('nominal_pembelian_val').value = total;
    document.getElementById('nominal_pembelian_display').value = formatMoney(total);

    if (status === 'lunas') {
        nominalDibayarVal.value = total;
        if (nominalDibayarDisplay) nominalDibayarDisplay.value = formatMoney(total);
    } else if (status === 'hutang') {
        nominalDibayarVal.value = 0;
        if (nominalDibayarDisplay) nominalDibayarDisplay.value = '0';
    } else {
        var dibayar = Math.min(parseInt(nominalDibayarVal.value) || 0, total);
        nominalDibayarVal.value = dibayar;
        if (nominalDibayarDisplay) nominalDibayarDisplay.value = formatMoney(dibayar);
    }

    updateDepresiasiPreview();
    updateHutangPreview();
    updateSummary();
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

    onPembayaranChange();
    updateAssetTotal();
}

function updateHutangPreview() {
    var status = document.getElementById('status_pembayaran').value;
    var total = parseInt(document.getElementById('nominal_pembelian_val').value) || 0;
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

function updateSummary() {
    var status = document.getElementById('status_pembayaran')?.value || 'lunas';
    var total = parseInt(document.getElementById('nominal_pembelian_val')?.value) || 0;
    var dibayar = parseInt(document.getElementById('nominal_dibayar_val')?.value) || 0;
    var hutang = Math.max(0, total - dibayar);
    var bank = document.getElementById('pembayaran_combo');
    var saldo = bank?.options[bank.selectedIndex]?.getAttribute('data-saldo') || 0;
    var label = { lunas: 'Lunas', hutang: 'Hutang', sebagian: 'Bayar Sebagian' }[status] || status;

    document.getElementById('summary_total').textContent = formatRp(total);
    document.getElementById('summary_status').textContent = label;
    document.getElementById('summary_dibayar').textContent = formatRp(dibayar);
    document.getElementById('summary_hutang').textContent = formatRp(hutang);
    document.getElementById('summary_saldo').textContent = status === 'hutang' ? '-' : formatRp(saldo);
}

function onMetodeDepresiasiChange() {
    var metode = document.getElementById('metode_depresiasi').value;
    var persenWrap = document.getElementById('persen_depresiasi_wrap');
    var nilaiResiduWrap = document.getElementById('nilai_residu_wrap');
    var umurManfaatEl = document.getElementById('umur_manfaat');

    if (metode === 'persen') {
        persenWrap.style.display = '';
    } else {
        persenWrap.style.display = 'none';
        document.getElementById('persen_depresiasi').value = '';
    }

    if (metode === 'tanpa') {
        nilaiResiduWrap.style.display = 'none';
        document.getElementById('nilai_residu_display').value = '0';
        document.getElementById('nilai_residu_val').value = '0';
        umurManfaatEl.value = '0';
        document.getElementById('depresiasi_preview').classList.add('hidden');
    } else {
        nilaiResiduWrap.style.display = '';
        if (document.getElementById('nilai_residu_display').value === '') {
            document.getElementById('nilai_residu_display').value = '0';
            document.getElementById('nilai_residu_val').value = '0';
        }
        updateDepresiasiPreview();
    }
}

function formatRp(num) {
    return 'Rp ' + Math.round(num).toLocaleString('id-ID');
}

function updateDepresiasiPreview() {
    var metode = document.getElementById('metode_depresiasi').value;
    var nominal = parseInt(document.getElementById('nominal_pembelian_val').value) || 0;
    var residu = parseInt(document.getElementById('nilai_residu_val').value) || 0;
    var umur = parseInt(document.getElementById('umur_manfaat').value) || 0;
    var persen = parseFloat(document.getElementById('persen_depresiasi')?.value) || 0;
    var preview = document.getElementById('depresiasi_preview');

    if (metode === 'tanpa' || nominal <= 0) {
        preview.classList.add('hidden');
        return;
    }

    var perTahun = 0;
    if (metode === 'persen') {
        perTahun = (nominal - residu) * (persen / 100);
    } else {
        if (umur <= 0) { preview.classList.add('hidden'); return; }
        perTahun = (nominal - residu) / umur;
    }

    var total = perTahun * umur;
    var maxDepresiasi = Math.max(0, nominal - residu);
    if (total > maxDepresiasi) total = maxDepresiasi;

    document.getElementById('preview_depresiasi_per_tahun').textContent = formatRp(perTahun);
    var totalEl = document.getElementById('preview_total_depresiasi');
    totalEl.textContent = formatRp(total) + ' (' + umur + ' thn)';

    preview.classList.remove('hidden');
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.money-input').forEach(initMoneyInput);
    if (selectedTambakId) {
        loadBlokByTambak(selectedBlokId);
    }

    onPembayaranChange();
    onMetodeDepresiasiChange();
    onStatusPembayaranChange();

    document.getElementById('qty').addEventListener('input', updateAssetTotal);
    document.getElementById('harga_satuan_display').addEventListener('input', updateAssetTotal);
    document.getElementById('nominal_dibayar_display').addEventListener('input', updateHutangPreview);
    document.getElementById('nilai_residu_display').addEventListener('input', updateDepresiasiPreview);
    document.getElementById('umur_manfaat').addEventListener('input', updateDepresiasiPreview);

    document.querySelector('form[enctype="multipart/form-data"]').addEventListener('submit', function(e) {
        var status = document.getElementById('status_pembayaran').value;
        var total = parseInt(document.getElementById('nominal_pembelian_val').value) || 0;
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
            if (thumb) {
                e.preventDefault();
                openLb(thumb.dataset.src);
                return;
            }
            var preview = e.target.closest('.lb-preview');
            if (preview) {
                e.preventDefault();
                openLb(preview.src);
                return;
            }
        });

        closeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            closeLb();
        });

        modal.addEventListener('click', function(e) {
            if (e.target === modal || e.target === img) {
                closeLb();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeLb();
        });
    }
});

// Preview eviden sebelum submit
function previewEviden(input) {
    var container = document.getElementById('previewContainer');
    container.innerHTML = '';
    if (input.files) {
        Array.from(input.files).forEach(function(file, index) {
            var isPdf = file.type === 'application/pdf';
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
                container.appendChild(div);
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

function previewFotoAset(input) {
    var container = document.getElementById('fotoAsetPreview');
    container.innerHTML = '';
    if (input.files) {
        Array.from(input.files).forEach(function(file, index) {
            var reader = new FileReader();
            var div = document.createElement('div');
            div.className = 'relative group rounded-xl border border-border overflow-hidden bg-muted hover:ring-2 hover:ring-primary hover:shadow-md transition-all';
            div.id = 'foto-preview-' + index;
            reader.onload = (function(d, i) {
                return function(e) {
                    d.innerHTML =
                        '<img src="' + e.target.result + '" class="w-full h-24 object-cover cursor-pointer lb-preview" alt="Preview">' +
                        '<div class="flex items-center justify-end px-2 py-1.5 border-t border-border">' +
                            '<button type="button" onclick="removeFotoPreview(' + i + ')" class="size-5 rounded-full bg-destructive text-white flex items-center justify-center hover:bg-destructive/80 transition-colors" title="Hapus">' +
                                '<i class="ki-filled ki-cross text-[10px]"></i>' +
                            '</button>' +
                        '</div>';
                };
            })(div, index);
            reader.readAsDataURL(file);
            container.appendChild(div);
        });
    }
}

function removeFotoPreview(index) {
    var el = document.getElementById('foto-preview-' + index);
    if (el) el.remove();
}

var hapusFotoList = [];
function hapusExistingFoto(idx, elementId, path) {
    if (!confirm('Yakin hapus foto ini?')) return;
    hapusFotoList.push(path);
    var form = document.querySelector('form[enctype="multipart/form-data"]');
    document.querySelectorAll('input[name^="hapus_foto["]').forEach(function(el) { el.remove(); });
    hapusFotoList.forEach(function(p, i) {
        var inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'hapus_foto[' + i + ']';
        inp.value = p;
        form.appendChild(inp);
    });
    document.getElementById(elementId).remove();
}
</script>
@endpush
