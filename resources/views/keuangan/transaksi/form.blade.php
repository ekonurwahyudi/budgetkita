@extends('layouts.app')

@section('title', $transaksi ? 'Edit Transaksi' : 'Tambah Transaksi')
@section('page-title', $transaksi ? 'Edit Transaksi' : 'Tambah Transaksi')
@section('page-description', $transaksi ? $transaksi->nomor_transaksi : 'Input transaksi keuangan baru')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <div class="flex items-center gap-4">
                <h3 class="kt-card-title">{{ $transaksi ? 'Edit Transaksi' : 'Tambah Transaksi' }}</h3>
                @if(!$transaksi)
                <div class="flex items-center gap-2">
                    <label class="text-xs font-medium text-secondary-foreground whitespace-nowrap" for="tipe_transaksi">Tipe</label>
                    <select name="tipe_transaksi" id="tipe_transaksi" form="transaksiForm" class="kt-select kt-select-sm min-w-44" onchange="onTipeTransaksiChange()">
                        <option value="transaksi_harian" {{ old('tipe_transaksi', 'transaksi_harian') === 'transaksi_harian' ? 'selected' : '' }}>Transaksi Harian</option>
                        <option value="pembelian_aset" {{ old('tipe_transaksi') === 'pembelian_aset' ? 'selected' : '' }}>Pembelian Aset</option>
                        <option value="pembelian_persediaan" {{ old('tipe_transaksi') === 'pembelian_persediaan' ? 'selected' : '' }}>Pembelian Persediaan</option>
                        <option value="gaji_karyawan" {{ old('tipe_transaksi') === 'gaji_karyawan' ? 'selected' : '' }}>Gaji Karyawan</option>
                        <option value="investasi" {{ old('tipe_transaksi') === 'investasi' ? 'selected' : '' }}>Investasi</option>
                        <option value="hutang_piutang" {{ old('tipe_transaksi') === 'hutang_piutang' ? 'selected' : '' }}>Hutang/Piutang</option>
                    </select>
                </div>
                @endif
            </div>
            <a href="{{ route('transaksi.index') }}" class="kt-btn kt-btn-outline kt-btn-sm">
                <i class="ki-filled ki-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="kt-card-content py-6">
            <form method="POST"
                  id="transaksiForm"
                  action="{{ $transaksi ? route('transaksi.update', $transaksi) : route('transaksi.store') }}"
                  data-transaksi-action="{{ $transaksi ? route('transaksi.update', $transaksi) : route('transaksi.store') }}"
                  data-aset-action="{{ route('pembelian-aset.store') }}"
                  data-persediaan-action="{{ route('pembelian-persediaan.store') }}"
                  data-gaji-action="{{ route('gaji.store') }}"
                  data-investasi-action="{{ route('investasi.store') }}"
                  data-hutang-piutang-action="{{ route('hutang-piutang.store') }}"
                  enctype="multipart/form-data">
                @csrf
                @if($transaksi) @method('PUT') @endif

                <div id="transaksiHarianSection">
                <input type="hidden" name="nominal_dibayar" id="nominal_dibayar_val" value="{{ old('nominal_dibayar', (int)($transaksi?->nominal_dibayar ?? 0)) }}">
                <input type="hidden" name="konfirmasi_hutang" id="konfirmasi_hutang" value="0">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Kolom Kiri --}}
                    <div class="flex flex-col gap-5">
                        {{-- Jenis & Tanggal --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Jenis Transaksi <span class="text-danger">*</span></label>
                                <select name="jenis_transaksi" id="jenis_transaksi" class="kt-select" required onchange="updateHutangPreview()">
                                    <option value="uang_masuk" {{ old('jenis_transaksi', $transaksi?->jenis_transaksi ?? 'uang_keluar') === 'uang_masuk' ? 'selected' : '' }}>Uang Masuk</option>
                                    <option value="uang_keluar" {{ old('jenis_transaksi', $transaksi?->jenis_transaksi ?? 'uang_keluar') === 'uang_keluar' ? 'selected' : '' }}>Uang Keluar</option>
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
                            <textarea name="aktivitas" id="aktivitas" class="kt-input" rows="3" style="height: 60px;" required>{{ old('aktivitas', $transaksi?->aktivitas) }}</textarea>
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
                                <input class="kt-input money-input" type="text" id="nominal_display" placeholder="0" data-target="nominal_val" required/>
                                <input type="hidden" name="nominal" id="nominal_val" value="{{ old('nominal', (int)($transaksi?->nominal ?? 0)) }}"/>
                            </div>
                        </div>

                        {{-- Tambak / Blok / Siklus --}}
                        <div class="grid grid-cols-3 gap-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Tambak <span class="text-danger">*</span></label>
                                <select name="tambak_id" id="tambak_id" class="kt-select" required onchange="loadBlokByTambak()">
                                    <option value="">-- Pilih --</option>
                                    @foreach($tambaks as $t)
                                    <option value="{{ $t->id }}" {{ old('tambak_id', $transaksi?->tambak_id ?? $selectedTambakId) === $t->id ? 'selected' : '' }}>{{ $t->nama_tambak }}</option>
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
                        {{-- Sumber Dana & Status Pembayaran --}}
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
                                <label class="text-sm font-medium text-foreground">Status Pembayaran <span class="text-danger">*</span></label>
                                <select name="status_pembayaran" id="status_pembayaran" class="kt-select" required onchange="onStatusPembayaranChange()">
                                    <option value="lunas" {{ old('status_pembayaran', $transaksi?->status_pembayaran ?? 'lunas') === 'lunas' ? 'selected' : '' }}>Lunas</option>
                                    <option value="hutang" {{ old('status_pembayaran', $transaksi?->status_pembayaran) === 'hutang' ? 'selected' : '' }}>Hutang</option>
                                    <option value="sebagian" {{ old('status_pembayaran', $transaksi?->status_pembayaran) === 'sebagian' ? 'selected' : '' }}>Bayar Sebagian</option>
                                </select>
                            </div>
                        </div>

                        {{-- Pembayaran & Dibayar Sekarang --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1.5" id="pembayaran_bank_wrap">
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
                            <div class="flex flex-col gap-1.5" id="nominal_dibayar_wrap" style="display:none;">
                                <label class="text-sm font-medium text-foreground">Dibayar Sekarang <span class="text-danger">*</span></label>
                                <div class="kt-input-group">
                                    <span class="kt-input-addon">Rp.</span>
                                    <input class="kt-input money-input" type="text" id="nominal_dibayar_display" placeholder="0" data-target="nominal_dibayar_val"/>
                                </div>
                            </div>
                        </div>

                        {{-- Keterangan Hutang (muncul saat hutang / bayar sebagian) --}}
                        <div id="hutang_preview" class="hidden rounded-xl border border-warning/30 bg-warning/10 p-4">
                            <p class="text-sm font-medium text-foreground mb-2">Ringkasan Hutang</p>
                            <div class="grid grid-cols-3 gap-3 text-sm">
                                <div>
                                    <span class="text-muted-foreground block">Total Transaksi</span>
                                    <span class="font-semibold text-mono" id="preview_total">Rp 0</span>
                                </div>
                                <div>
                                    <span class="text-muted-foreground block">Dibayar</span>
                                    <span class="font-semibold text-mono" id="preview_dibayar">Rp 0</span>
                                </div>
                                <div>
                                    <span class="text-muted-foreground block" id="preview_sisa_label">Dicatat Hutang</span>
                                    <span class="font-semibold text-mono text-warning" id="preview_hutang">Rp 0</span>
                                </div>
                            </div>
                        </div>


                        {{-- Eviden --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Eviden</label>
                            <input type="file" name="eviden[]" id="evidenInput" class="kt-input" multiple accept=".png,.jpg,.jpeg,.pdf" onchange="previewEviden(this)">
                            <p class="text-xs text-muted-foreground">Maksimal 5MB per file. Format: PNG, JPG, JPEG, PDF.</p>

                            <div id="previewContainer" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3 mt-2"></div>

                            @if($transaksi && !empty($transaksi->eviden))
                            <div id="existingEviden" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3 mt-2">
                                @foreach($transaksi->eviden as $idx => $ev)
                                @php
                                    $isPdf = \Illuminate\Support\Str::endsWith(strtolower($ev), ['.pdf']);
                                    $url = \Illuminate\Support\Facades\Storage::url($ev);
                                @endphp
                                <div class="relative group rounded-xl border border-border overflow-hidden bg-muted hover:ring-2 hover:ring-primary hover:shadow-md transition-all" id="existing-ev-{{ $idx }}">
                                    @if($isPdf)
                                        <a href="{{ $url }}" target="_blank" class="flex flex-col items-center justify-center w-full h-24 p-3">
                                            <i class="ki-filled ki-document text-3xl text-primary mb-2"></i>
                                            <span class="text-[10px] text-muted-foreground text-center truncate w-full">PDF</span>
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
                            <textarea name="catatan" id="catatan" class="kt-input" rows="3" style="height: 94px;">{{ old('catatan', $transaksi?->catatan) }}</textarea>
                        </div>
                    </div>
                </div>
                </div>

                @if(!$transaksi)
                <div id="pembelianAsetSection" style="display:none;">
                    <input type="hidden" name="nominal_pembelian" id="nominal_pembelian_val" value="{{ old('nominal_pembelian', 0) }}">
                    <input type="hidden" name="nilai_residu" id="nilai_residu_val" value="{{ old('nilai_residu', 0) }}">

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="flex flex-col gap-5">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Nama Aset <span class="text-danger">*</span></label>
                                <input type="text" name="nama_aset" class="kt-input aset-required" value="{{ old('nama_aset') }}" placeholder="Nama aset" disabled />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-sm font-medium text-foreground">Kategori Aset <span class="text-danger">*</span></label>
                                    <select name="kategori_aset_id" class="kt-select aset-required" disabled>
                                        <option value="">-- Pilih Kategori --</option>
                                        @foreach($kategoriAsets as $kat)
                                        <option value="{{ $kat->id }}" {{ old('kategori_aset_id') === $kat->id ? 'selected' : '' }}>
                                            {{ $kat->kode_aset }} - {{ $kat->deskripsi }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-sm font-medium text-foreground">Tanggal Pembelian <span class="text-danger">*</span></label>
                                    <div class="kt-input">
                                        <i class="ki-outline ki-calendar"></i>
                                        <input class="grow aset-required" name="tgl_pembelian" id="tgl_pembelian_aset"
                                               data-kt-date-picker="true" data-kt-date-picker-input-mode="true"
                                               placeholder="Pilih tanggal" readonly type="text"
                                               value="{{ old('tgl_pembelian') }}" disabled />
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-sm font-medium text-foreground">Nominal Pembelian <span class="text-danger">*</span></label>
                                    <div class="kt-input-group">
                                        <span class="kt-input-addon">Rp.</span>
                                        <input class="kt-input money-input aset-required" type="text" id="nominal_pembelian_display" placeholder="0" data-target="nominal_pembelian_val" disabled/>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-sm font-medium text-foreground">Umur Manfaat (Tahun) <span class="text-danger">*</span></label>
                                    <input type="number" name="umur_manfaat" id="umur_manfaat" class="kt-input aset-required" min="0" value="{{ old('umur_manfaat') }}" placeholder="Tahun" disabled />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-sm font-medium text-foreground">Metode Depresiasi <span class="text-danger">*</span></label>
                                    <select name="metode_depresiasi" id="metode_depresiasi" class="kt-select aset-required" onchange="onMetodeDepresiasiChange()" disabled>
                                        <option value="garis_lurus" {{ old('metode_depresiasi', 'garis_lurus') === 'garis_lurus' ? 'selected' : '' }}>Garis Lurus</option>
                                        <option value="persen" {{ old('metode_depresiasi') === 'persen' ? 'selected' : '' }}>Persen (%)</option>
                                        <option value="tanpa" {{ old('metode_depresiasi') === 'tanpa' ? 'selected' : '' }}>Tanpa Depresiasi</option>
                                    </select>
                                </div>
                                <div class="flex flex-col gap-1.5" id="persen_depresiasi_wrap" style="{{ old('metode_depresiasi', 'garis_lurus') === 'persen' ? '' : 'display:none' }}">
                                    <label class="text-sm font-medium text-foreground">Persen Depresiasi (% per tahun) <span class="text-danger">*</span></label>
                                    <div class="kt-input-group">
                                        <input type="number" name="persen_depresiasi" id="persen_depresiasi" class="kt-input grow" min="0" max="100" step="0.01" value="{{ old('persen_depresiasi') }}" placeholder="0" onchange="updateDepresiasiPreview()" oninput="updateDepresiasiPreview()" disabled />
                                        <span class="kt-input-addon">%</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col gap-1.5" id="nilai_residu_wrap">
                                <label class="text-sm font-medium text-foreground">Nilai Residu (Optional)</label>
                                <div class="kt-input-group">
                                    <span class="kt-input-addon">Rp.</span>
                                    <input class="kt-input money-input" type="text" id="nilai_residu_display" placeholder="0" data-target="nilai_residu_val" disabled/>
                                </div>
                            </div>

                            <div id="depresiasi_preview" class="hidden rounded-xl border border-border bg-muted/50 p-4">
                                <p class="text-sm font-medium text-foreground mb-2">Simulasi Depresiasi</p>
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <div>
                                        <span class="text-muted-foreground">Depresiasi / Tahun:</span>
                                        <span class="font-semibold text-mono" id="preview_depresiasi_per_tahun">Rp 0</span>
                                    </div>
                                    <div>
                                        <span class="text-muted-foreground">Total Depresiasi:</span>
                                        <span class="font-semibold text-mono" id="preview_total_depresiasi">Rp 0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-5">
                            <input type="hidden" name="jenis_pembayaran" id="jenis_pembayaran_aset" value="bank" disabled>
                            <input type="hidden" name="account_bank_id" id="account_bank_id_aset" value="{{ old('account_bank_id') }}" disabled>

                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Pembayaran <span class="text-danger">*</span></label>
                                <select id="pembayaran_combo_aset" class="kt-select aset-required" onchange="onPembayaranAsetChange()" disabled>
                                    @foreach($accountBanks as $bank)
                                    <option value="{{ $bank->id }}" data-saldo="{{ $bank->saldo }}"
                                        {{ old('account_bank_id') === $bank->id ? 'selected' : '' }}>
                                        {{ $bank->nama_bank }} - {{ $bank->nama_pemilik }}
                                    </option>
                                    @endforeach
                                </select>
                                <span class="text-xs text-muted-foreground mt-1" id="saldoInfoAset">
                                    Saldo: <span class="text-mono font-medium text-primary" id="saldoValueAset"></span>
                                </span>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Foto Aset</label>
                                <input type="file" name="foto_aset[]" id="fotoAsetInput" class="kt-input" multiple accept=".png,.jpg,.jpeg" onchange="previewFotoAset(this)" disabled>
                                <p class="text-xs text-muted-foreground">Maksimal 5MB per file. Format: PNG, JPG, JPEG. Bisa lebih dari 1 file.</p>
                                <div id="fotoAsetPreview" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3 mt-2"></div>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Eviden</label>
                                <input type="file" name="eviden[]" id="evidenInputAset" class="kt-input" multiple accept=".png,.jpg,.jpeg,.pdf" onchange="previewEvidenAset(this)" disabled>
                                <p class="text-xs text-muted-foreground">Maksimal 5MB per file. Format: PNG, JPG, JPEG, PDF.</p>
                                <div id="previewContainerAset" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3 mt-2"></div>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Catatan</label>
                                <textarea name="catatan" id="catatan_aset" class="kt-input" rows="3" style="height: 94px;" disabled>{{ old('catatan') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="gajiKaryawanSection" style="display:none;">
                    <input type="hidden" name="jenis_pembayaran" id="jenis_pembayaran_gaji" value="bank" disabled>
                    <input type="hidden" name="account_bank_id" id="account_bank_id_gaji" value="{{ old('account_bank_id') }}" disabled>
                    <input type="hidden" name="gaji_pokok" id="gaji_pokok_gaji_val" value="{{ old('gaji_pokok', 0) }}" disabled>
                    <input type="hidden" name="upah_lembur" id="upah_lembur_gaji_val" value="{{ old('upah_lembur', 0) }}" disabled>
                    <input type="hidden" name="bonus" id="bonus_gaji_val" value="{{ old('bonus', 0) }}" disabled>
                    <input type="hidden" name="pajak" id="pajak_gaji_val" value="{{ old('pajak', 0) }}" disabled>
                    <input type="hidden" name="bpjs" id="bpjs_gaji_val" value="{{ old('bpjs', 0) }}" disabled>
                    <input type="hidden" name="potongan" id="potongan_gaji_val" value="{{ old('potongan', 0) }}" disabled>
                    <input type="hidden" name="thp" id="thp_gaji_val" value="{{ old('thp', 0) }}" disabled>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="flex flex-col gap-5">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-sm font-medium text-foreground">Tanggal <span class="text-danger">*</span></label>
                                    <div class="kt-input">
                                        <i class="ki-outline ki-calendar"></i>
                                        <input class="grow gaji-required" name="created_at" data-kt-date-picker="true" data-kt-date-picker-input-mode="true" placeholder="Pilih tanggal" readonly type="text" value="{{ old('created_at', now()->format('Y-m-d')) }}" disabled/>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-sm font-medium text-foreground">Karyawan <span class="text-danger">*</span></label>
                                    <select name="user_id" class="kt-select gaji-required" disabled>
                                        <option value="">-- Pilih Karyawan --</option>
                                        @foreach($karyawans as $karyawan)
                                        <option value="{{ $karyawan->id }}" {{ old('user_id') === $karyawan->id ? 'selected' : '' }}>
                                            {{ $karyawan->nama }}{{ $karyawan->jabatan ? ' - '.$karyawan->jabatan : '' }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                @foreach([
                                    ['gaji_pokok_gaji_display', 'gaji_pokok_gaji_val', 'Gaji Pokok', true],
                                    ['upah_lembur_gaji_display', 'upah_lembur_gaji_val', 'Upah Lembur', false],
                                    ['bonus_gaji_display', 'bonus_gaji_val', 'Bonus', false],
                                    ['pajak_gaji_display', 'pajak_gaji_val', 'Pajak', false],
                                    ['bpjs_gaji_display', 'bpjs_gaji_val', 'BPJS', false],
                                    ['potongan_gaji_display', 'potongan_gaji_val', 'Potongan', false],
                                ] as [$displayId, $targetId, $label, $required])
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-sm font-medium text-foreground">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
                                    <div class="kt-input-group">
                                        <span class="kt-input-addon">Rp.</span>
                                        <input class="kt-input money-input {{ $required ? 'gaji-required' : '' }}" type="text" id="{{ $displayId }}" data-target="{{ $targetId }}" placeholder="0" disabled/>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="flex flex-col gap-5">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">THP (Take Home Pay)</label>
                                <div class="kt-input-group">
                                    <span class="kt-input-addon">Rp.</span>
                                    <input class="kt-input" type="text" id="thp_gaji_display" placeholder="0" readonly style="background:var(--muted);" disabled/>
                                </div>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Pembayaran <span class="text-danger">*</span></label>
                                <select id="pembayaran_combo_gaji" class="kt-select gaji-required" onchange="onBankModeChange('gaji')" disabled>
                                    @foreach($accountBanks as $bank)
                                    <option value="{{ $bank->id }}" data-saldo="{{ $bank->saldo }}">{{ $bank->nama_bank }} - {{ $bank->nama_pemilik }}</option>
                                    @endforeach
                                </select>
                                <span class="text-xs text-muted-foreground mt-1" id="saldoInfoGaji">Saldo: <span class="text-mono font-medium text-primary" id="saldoValueGaji"></span></span>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Eviden</label>
                                <input type="file" name="eviden[]" id="evidenInputGaji" class="kt-input" multiple accept="image/*,.pdf,.xlsx,.xls" onchange="previewModeEviden(this, 'Gaji')" disabled>
                                <p class="text-xs text-muted-foreground">Maksimal 5MB per file. Format: JPG, PNG, PDF, Excel.</p>
                                <div id="previewContainerGaji" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3 mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="pembelianPersediaanSection" style="display:none;">
                    <input type="hidden" name="jenis_pembayaran" id="jenis_pembayaran_persediaan" value="bank" disabled>
                    <input type="hidden" name="account_bank_id" id="account_bank_id_persediaan" value="{{ old('account_bank_id') }}" disabled>
                    <div class="flex flex-col gap-5">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Tanggal Pembelian <span class="text-danger">*</span></label>
                                <div class="kt-input">
                                    <i class="ki-outline ki-calendar"></i>
                                    <input class="grow persediaan-required" name="tgl_pembelian" data-kt-date-picker="true" data-kt-date-picker-input-mode="true" placeholder="Pilih tanggal" readonly type="text" value="{{ old('tgl_pembelian', now()->format('Y-m-d')) }}" disabled/>
                                </div>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Pembayaran <span class="text-danger">*</span></label>
                                <select id="pembayaran_combo_persediaan" class="kt-select persediaan-required" onchange="onBankModeChange('persediaan')" disabled>
                                    @foreach($accountBanks as $bank)
                                    <option value="{{ $bank->id }}" data-saldo="{{ $bank->saldo }}">{{ $bank->nama_bank }} - {{ $bank->nama_pemilik }}</option>
                                    @endforeach
                                </select>
                                <span class="text-xs text-muted-foreground mt-1" id="saldoInfoPersediaan">Saldo: <span class="text-mono font-medium text-primary" id="saldoValuePersediaan"></span></span>
                            </div>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Eviden</label>
                            <input type="file" name="eviden[]" id="evidenInputPersediaan" class="kt-input" multiple accept=".jpg,.jpeg,.png,.gif,.bmp,.webp,.pdf,.xlsx,.xls" onchange="previewModeEviden(this, 'Persediaan')" disabled>
                            <p class="text-xs text-muted-foreground">Maksimal 5MB per file. Format: JPG, PNG, PDF, Excel.</p>
                            <div id="previewContainerPersediaan" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3 mt-2"></div>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Catatan</label>
                            <textarea name="catatan" class="kt-input" rows="2" disabled>{{ old('catatan') }}</textarea>
                        </div>

                        <div class="flex flex-col gap-2">
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium text-foreground">Item Pembelian</label>
                                <button type="button" class="kt-btn kt-btn-sm kt-btn-outline" onclick="addPersediaanItem()">
                                    <i class="ki-filled ki-plus"></i> Tambah Item
                                </button>
                            </div>
                            <div class="kt-table-wrapper kt-scrollable">
                                <table class="kt-table" id="persediaanItemsTable">
                                    <thead>
                                        <tr>
                                            <th style="min-width:160px">Kategori</th>
                                            <th style="min-width:200px">Item Persediaan</th>
                                            <th style="width:100px">Qty</th>
                                            <th style="width:120px">Satuan</th>
                                            <th style="min-width:160px">Harga Satuan</th>
                                            <th style="min-width:160px">Harga Total</th>
                                            <th style="width:50px"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="persediaanItemsBody"></tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5" class="text-end font-semibold">Grand Total</td>
                                            <td class="text-mono font-semibold" id="persediaanGrandTotalDisplay">Rp 0</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="investasiSection" style="display:none;">
                    <input type="hidden" name="jenis_pembayaran" id="jenis_pembayaran_investasi" value="bank" disabled>
                    <input type="hidden" name="account_bank_id" id="account_bank_id_investasi" value="{{ old('account_bank_id') }}" disabled>
                    <input type="hidden" name="nominal" id="nominal_investasi_val" value="{{ old('nominal', 0) }}" disabled>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="flex flex-col gap-5">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Tanggal <span class="text-danger">*</span></label>
                                <div class="kt-input">
                                    <i class="ki-outline ki-calendar"></i>
                                    <input class="grow investasi-required" name="created_at" data-kt-date-picker="true" data-kt-date-picker-input-mode="true" placeholder="Pilih tanggal" readonly type="text" value="{{ old('created_at', now()->format('Y-m-d')) }}" disabled/>
                                </div>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Deskripsi <span class="text-danger">*</span></label>
                                <textarea name="deskripsi" class="kt-input investasi-required" rows="3" style="height:60px;" disabled>{{ old('deskripsi') }}</textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-sm font-medium text-foreground">Nominal <span class="text-danger">*</span></label>
                                    <div class="kt-input-group">
                                        <span class="kt-input-addon">Rp.</span>
                                        <input class="kt-input money-input investasi-required" type="text" id="nominal_investasi_display" data-target="nominal_investasi_val" placeholder="0" disabled/>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-sm font-medium text-foreground">Kategori Investasi <span class="text-danger">*</span></label>
                                    <select name="kategori_investasi_id" class="kt-select investasi-required" disabled>
                                        <option value="">-- Pilih Kategori --</option>
                                        @foreach($kategoriInvestasis as $kat)
                                        <option value="{{ $kat->id }}" {{ old('kategori_investasi_id') === $kat->id ? 'selected' : '' }}>{{ $kat->deskripsi }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col gap-5">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Pembayaran <span class="text-danger">*</span></label>
                                <select id="pembayaran_combo_investasi" class="kt-select investasi-required" onchange="onBankModeChange('investasi')" disabled>
                                    @foreach($accountBanks as $bank)
                                    <option value="{{ $bank->id }}" data-saldo="{{ $bank->saldo }}">{{ $bank->nama_bank }} - {{ $bank->nama_pemilik }}</option>
                                    @endforeach
                                </select>
                                <span class="text-xs text-muted-foreground mt-1" id="saldoInfoInvestasi">Saldo: <span class="text-mono font-medium text-primary" id="saldoValueInvestasi"></span></span>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Eviden</label>
                                <input type="file" name="eviden[]" id="evidenInputInvestasi" class="kt-input" multiple accept="image/*,.pdf,.xlsx,.xls" onchange="previewModeEviden(this, 'Investasi')" disabled>
                                <p class="text-xs text-muted-foreground">Maksimal 5MB per file. Format: JPG, PNG, PDF, Excel.</p>
                                <div id="previewContainerInvestasi" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3 mt-2"></div>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Catatan</label>
                                <textarea name="catatan" class="kt-input" rows="3" style="height:94px;" disabled>{{ old('catatan') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="hutangPiutangSection" style="display:none;">
                    <input type="hidden" name="jenis_pembayaran" id="jenis_pembayaran_hutang_piutang" value="bank" disabled>
                    <input type="hidden" name="account_bank_id" id="account_bank_id_hutang_piutang" value="{{ old('account_bank_id') }}" disabled>
                    <input type="hidden" name="nominal" id="nominal_hp_val" value="{{ old('nominal', 0) }}" disabled>
                    <input type="hidden" name="total_bayar" id="total_bayar_hp_val" value="{{ old('total_bayar', 0) }}" disabled>
                    <input type="hidden" name="nominal_bayar" id="nominal_bayar_hp_val" value="{{ old('nominal_bayar', 0) }}" disabled>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="flex flex-col gap-5">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-sm font-medium text-foreground">Tanggal <span class="text-danger">*</span></label>
                                    <div class="kt-input">
                                        <i class="ki-outline ki-calendar"></i>
                                        <input class="grow hp-required" name="created_at" data-kt-date-picker="true" data-kt-date-picker-input-mode="true" placeholder="Pilih tanggal" readonly type="text" value="{{ old('created_at', now()->format('Y-m-d')) }}" disabled/>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-sm font-medium text-foreground">Jatuh Tempo <span class="text-danger">*</span></label>
                                    <div class="kt-input">
                                        <i class="ki-outline ki-calendar"></i>
                                        <input class="grow hp-required" name="jatuh_tempo" data-kt-date-picker="true" data-kt-date-picker-input-mode="true" placeholder="Pilih tanggal" readonly type="text" value="{{ old('jatuh_tempo') }}" disabled/>
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-sm font-medium text-foreground">Jenis <span class="text-danger">*</span></label>
                                    <select name="jenis" id="jenis_hp" class="kt-select hp-required" onchange="filterKategoriHp()" disabled>
                                        <option value="hutang" {{ old('jenis') === 'hutang' ? 'selected' : '' }}>Hutang</option>
                                        <option value="piutang" {{ old('jenis') === 'piutang' ? 'selected' : '' }}>Piutang</option>
                                    </select>
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-sm font-medium text-foreground">Kategori <span class="text-danger">*</span></label>
                                    <select name="kategori_hutang_piutang_id" id="kategori_hutang_piutang_hp_id" class="kt-select hp-required" disabled>
                                        <option value="">-- Pilih Kategori --</option>
                                        @foreach($kategoriHutangPiutangs as $kat)
                                        <option value="{{ $kat->id }}" data-jenis="{{ stripos($kat->deskripsi, 'piutang') !== false ? 'piutang' : 'hutang' }}" {{ old('kategori_hutang_piutang_id') === $kat->id ? 'selected' : '' }}>{{ $kat->deskripsi }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground" id="nama_pemberi_hutang_hp_label">Nama Pemberi Hutang</label>
                                <input type="text" name="nama_pemberi_hutang" id="nama_pemberi_hutang_hp" class="kt-input" value="{{ old('nama_pemberi_hutang') }}" placeholder="Nama pihak pemberi hutang" disabled/>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Aktivitas/Kegiatan <span class="text-danger">*</span></label>
                                <textarea name="aktivitas" class="kt-input hp-required" rows="3" style="height:94px;" disabled>{{ old('aktivitas') }}</textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-sm font-medium text-foreground">Nominal <span class="text-danger">*</span></label>
                                    <div class="kt-input-group">
                                        <span class="kt-input-addon">Rp.</span>
                                        <input class="kt-input money-input hp-required" type="text" id="nominal_hp_display" data-target="nominal_hp_val" placeholder="0" disabled/>
                                    </div>
                                </div>
                                <div id="total_bayar_hp_wrap" class="flex flex-col gap-1.5">
                                    <label class="text-sm font-medium text-foreground">Total Bayar</label>
                                    <div class="kt-input-group">
                                        <span class="kt-input-addon">Rp.</span>
                                        <input class="kt-input money-input" type="text" id="total_bayar_hp_display" data-target="total_bayar_hp_val" placeholder="Kosongkan jika sama" disabled/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col gap-5">
                            <div id="nominal_bayar_hp_wrap" class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Sudah Dibayar</label>
                                <div class="kt-input-group">
                                    <span class="kt-input-addon">Rp.</span>
                                    <input class="kt-input money-input" type="text" id="nominal_bayar_hp_display" data-target="nominal_bayar_hp_val" placeholder="0" disabled/>
                                </div>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Account Bank</label>
                                <select id="pembayaran_combo_hutang_piutang" class="kt-select" onchange="onBankModeChange('hutang_piutang')" disabled>
                                    <option value="">-- Pilih Bank --</option>
                                    @foreach($accountBanks as $bank)
                                    <option value="{{ $bank->id }}" data-saldo="{{ $bank->saldo }}">{{ $bank->nama_bank }} - {{ $bank->nama_pemilik }}</option>
                                    @endforeach
                                </select>
                                <span class="text-xs text-muted-foreground mt-1" id="saldoInfoHutangPiutang" style="display:none;">Saldo: <span class="text-mono font-medium text-primary" id="saldoValueHutangPiutang"></span></span>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Eviden</label>
                                <input type="file" name="eviden[]" id="evidenInputHp" class="kt-input" multiple accept="image/*,.pdf,.xlsx,.xls" onchange="previewModeEviden(this, 'Hp')" disabled>
                                <p class="text-xs text-muted-foreground">Maksimal 5MB per file. Format: JPG, PNG, PDF, Excel.</p>
                                <div id="previewContainerHp" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3 mt-2"></div>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-sm font-medium text-foreground">Catatan</label>
                                <textarea name="catatan" class="kt-input" rows="3" style="height:94px;" disabled>{{ old('catatan') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

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
    if (!sel) return;
    var val = sel.value;
    var parts = val.split('|');
    var status = document.getElementById('status_pembayaran')?.value || 'lunas';
    document.getElementById('jenis_pembayaran').value = 'bank';
    document.getElementById('account_bank_id').value = status === 'hutang' ? '' : (parts[1] || '');
    var saldo = sel.options[sel.selectedIndex]?.getAttribute('data-saldo');
    var saldoEl = document.getElementById('saldoValue');
    if (saldo !== null && saldo !== '') {
        saldoEl.textContent = 'Rp ' + Number(saldo || 0).toLocaleString('id-ID');
        document.getElementById('saldoInfo').style.display = '';
    } else {
        document.getElementById('saldoInfo').style.display = 'none';
    }
}

function updateNominalDibayar() {
    var statusEl = document.getElementById('status_pembayaran');
    var nominalVal = document.getElementById('nominal_val');
    var dibayarVal = document.getElementById('nominal_dibayar_val');
    var dibayarDisplay = document.getElementById('nominal_dibayar_display');
    if (!statusEl || !nominalVal || !dibayarVal) return;
    var status = statusEl.value;
    var nominal = parseMoney(nominalVal.value);
    if (status === 'lunas') {
        dibayarVal.value = nominal;
        if (dibayarDisplay) dibayarDisplay.value = nominal > 0 ? formatMoney(nominal) : '';
    } else if (status === 'hutang') {
        dibayarVal.value = 0;
        if (dibayarDisplay) dibayarDisplay.value = '';
    } else {
        var dibayar = Math.min(parseMoney(dibayarVal.value), nominal);
        dibayarVal.value = dibayar;
        if (dibayarDisplay) dibayarDisplay.value = dibayar > 0 ? formatMoney(dibayar) : '';
    }
    updateHutangPreview();
}

function onStatusPembayaranChange() {
    var statusEl = document.getElementById('status_pembayaran');
    if (!statusEl) return;
    var status = statusEl.value;
    var bankWrap = document.getElementById('pembayaran_bank_wrap');
    var dibayarWrap = document.getElementById('nominal_dibayar_wrap');
    var bankSelect = document.getElementById('pembayaran_combo');
    var konf = document.getElementById('konfirmasi_hutang');
    if (bankWrap) bankWrap.style.display = status === 'hutang' ? 'none' : '';
    if (dibayarWrap) dibayarWrap.style.display = status === 'sebagian' ? '' : 'none';
    if (bankSelect) bankSelect.required = status !== 'hutang';
    if (konf) konf.value = '0';
    updateNominalDibayar();
    onPembayaranChange();
}

function updateHutangPreview() {
    var statusEl = document.getElementById('status_pembayaran');
    var preview = document.getElementById('hutang_preview');
    if (!statusEl || !preview) return;
    var status = statusEl.value;
    var nominal = parseMoney(document.getElementById('nominal_val').value);
    var dibayar = parseMoney(document.getElementById('nominal_dibayar_val').value);
    var hutang = Math.max(0, nominal - dibayar);

    if (!['hutang', 'sebagian'].includes(status) || hutang <= 0) {
        preview.classList.add('hidden');
        return;
    }

    var isMasuk = document.getElementById('jenis_transaksi')?.value === 'uang_masuk';
    var sisaLabel = document.getElementById('preview_sisa_label');
    if (sisaLabel) sisaLabel.textContent = isMasuk ? 'Dicatat Piutang' : 'Dicatat Hutang';
    document.getElementById('preview_total').textContent = formatRp(nominal);
    document.getElementById('preview_dibayar').textContent = formatRp(dibayar);
    document.getElementById('preview_hutang').textContent = formatRp(hutang);
    preview.classList.remove('hidden');
}

function onPembayaranAsetChange() {
    var sel = document.getElementById('pembayaran_combo_aset');
    if (!sel) return;
    document.getElementById('jenis_pembayaran_aset').value = 'bank';
    document.getElementById('account_bank_id_aset').value = sel.value || '';
    var saldo = sel.options[sel.selectedIndex]?.getAttribute('data-saldo');
    var saldoEl = document.getElementById('saldoValueAset');
    if (saldo !== null && saldo !== '') {
        saldoEl.textContent = 'Rp ' + Number(saldo || 0).toLocaleString('id-ID');
        document.getElementById('saldoInfoAset').style.display = '';
    } else {
        document.getElementById('saldoInfoAset').style.display = 'none';
    }
}

function onBankModeChange(mode) {
    var sel = document.getElementById('pembayaran_combo_' + mode);
    if (!sel) return;
    var hiddenMode = mode === 'hutang_piutang' ? 'hutang_piutang' : mode;
    var jenis = document.getElementById('jenis_pembayaran_' + hiddenMode);
    var account = document.getElementById('account_bank_id_' + hiddenMode);
    var saldoId = mode === 'gaji' ? 'Gaji' : (mode === 'investasi' ? 'Investasi' : (mode === 'persediaan' ? 'Persediaan' : 'HutangPiutang'));
    var saldoInfo = document.getElementById('saldoInfo' + saldoId);
    var saldoValue = document.getElementById('saldoValue' + saldoId);
    var val = sel.value || '';
    if (jenis) jenis.value = val ? 'bank' : 'cash';
    if (account) account.value = val;
    var saldo = val ? sel.options[sel.selectedIndex]?.getAttribute('data-saldo') : null;
    if (saldo && saldoInfo && saldoValue) {
        saldoValue.textContent = 'Rp ' + Number(saldo).toLocaleString('id-ID');
        saldoInfo.style.display = '';
    } else if (saldoInfo) {
        saldoInfo.style.display = 'none';
    }
}

function onTipeTransaksiChange() {
    var tipe = document.getElementById('tipe_transaksi');
    var form = document.getElementById('transaksiForm');
    if (!tipe || !form) return;

    var active = tipe.value;
    var actionMap = {
        transaksi_harian: form.dataset.transaksiAction,
        pembelian_aset: form.dataset.asetAction,
        pembelian_persediaan: form.dataset.persediaanAction,
        gaji_karyawan: form.dataset.gajiAction,
        investasi: form.dataset.investasiAction,
        hutang_piutang: form.dataset.hutangPiutangAction
    };
    var sectionMap = {
        transaksi_harian: document.getElementById('transaksiHarianSection'),
        pembelian_aset: document.getElementById('pembelianAsetSection'),
        pembelian_persediaan: document.getElementById('pembelianPersediaanSection'),
        gaji_karyawan: document.getElementById('gajiKaryawanSection'),
        investasi: document.getElementById('investasiSection'),
        hutang_piutang: document.getElementById('hutangPiutangSection')
    };
    var requiredMap = {
        pembelian_aset: 'aset-required',
        pembelian_persediaan: 'persediaan-required',
        gaji_karyawan: 'gaji-required',
        investasi: 'investasi-required',
        hutang_piutang: 'hp-required'
    };

    form.action = actionMap[active] || form.dataset.transaksiAction;
    Object.keys(sectionMap).forEach(function(mode) {
        var section = sectionMap[mode];
        if (!section) return;
        var enabled = mode === active;
        section.style.display = enabled ? '' : 'none';
        section.querySelectorAll('input, select, textarea').forEach(function(el) {
            el.disabled = !enabled;
        });
    });

    Object.values(requiredMap).forEach(function(className) {
        document.querySelectorAll('.' + className).forEach(function(el) {
            el.removeAttribute('required');
        });
    });
    if (requiredMap[active]) {
        document.querySelectorAll('.' + requiredMap[active]).forEach(function(el) {
            el.setAttribute('required', 'required');
        });
    }

    if (active === 'pembelian_aset') {
        onPembayaranAsetChange();
        onMetodeDepresiasiChange();
    } else if (active === 'gaji_karyawan') {
        onBankModeChange('gaji');
        calcGajiMode();
    } else if (active === 'pembelian_persediaan') {
        onBankModeChange('persediaan');
        ensurePersediaanItem();
    } else if (active === 'investasi') {
        onBankModeChange('investasi');
    } else if (active === 'hutang_piutang') {
        onBankModeChange('hutang_piutang');
        filterKategoriHp();
    } else {
        onStatusPembayaranChange();
    }
}

function onMetodeDepresiasiChange() {
    var metodeEl = document.getElementById('metode_depresiasi');
    if (!metodeEl) return;
    var metode = metodeEl.value;
    var persenWrap = document.getElementById('persen_depresiasi_wrap');
    var persenInput = document.getElementById('persen_depresiasi');
    var nilaiResiduWrap = document.getElementById('nilai_residu_wrap');
    var nilaiResiduDisplay = document.getElementById('nilai_residu_display');
    var nilaiResiduVal = document.getElementById('nilai_residu_val');
    var umurManfaatEl = document.getElementById('umur_manfaat');
    var preview = document.getElementById('depresiasi_preview');

    if (metode === 'persen') {
        persenWrap.style.display = '';
        persenInput.disabled = false;
        persenInput.setAttribute('required', 'required');
    } else {
        persenWrap.style.display = 'none';
        persenInput.value = '';
        persenInput.disabled = true;
        persenInput.removeAttribute('required');
    }

    if (metode === 'tanpa') {
        nilaiResiduWrap.style.display = 'none';
        nilaiResiduDisplay.value = '0';
        nilaiResiduVal.value = '0';
        umurManfaatEl.value = '0';
        preview.classList.add('hidden');
    } else {
        nilaiResiduWrap.style.display = '';
        updateDepresiasiPreview();
    }
}

function formatRp(num) {
    return 'Rp ' + Math.round(num).toLocaleString('id-ID');
}

function updateDepresiasiPreview() {
    var metodeEl = document.getElementById('metode_depresiasi');
    if (!metodeEl) return;
    var metode = metodeEl.value;
    var nominal = parseInt(document.getElementById('nominal_pembelian_val')?.value) || 0;
    var residu = parseInt(document.getElementById('nilai_residu_val')?.value) || 0;
    var umur = parseInt(document.getElementById('umur_manfaat')?.value) || 0;
    var persen = parseFloat(document.getElementById('persen_depresiasi')?.value) || 0;
    var preview = document.getElementById('depresiasi_preview');

    if (!preview || metode === 'tanpa' || nominal <= 0) {
        preview?.classList.add('hidden');
        return;
    }

    var perTahun = 0;
    if (metode === 'persen') {
        perTahun = (nominal - residu) * (persen / 100);
    } else {
        if (umur <= 0) {
            preview.classList.add('hidden');
            return;
        }
        perTahun = (nominal - residu) / umur;
    }

    var total = Math.min(perTahun * umur, Math.max(0, nominal - residu));
    document.getElementById('preview_depresiasi_per_tahun').textContent = formatRp(perTahun);
    document.getElementById('preview_total_depresiasi').textContent = formatRp(total) + ' (' + umur + ' thn)';
    preview.classList.remove('hidden');
}

function calcGajiMode() {
    var ids = ['gaji_pokok', 'upah_lembur', 'bonus', 'pajak', 'bpjs', 'potongan'];
    var values = {};
    ids.forEach(function(id) {
        values[id] = parseInt(document.getElementById(id + '_gaji_val')?.value) || 0;
    });
    var thp = values.gaji_pokok + values.upah_lembur + values.bonus - values.pajak - values.bpjs - values.potongan;
    var thpVal = document.getElementById('thp_gaji_val');
    var thpDisplay = document.getElementById('thp_gaji_display');
    if (thpVal) thpVal.value = thp;
    if (thpDisplay) thpDisplay.value = formatMoney(thp);
}

function filterKategoriHp() {
    var jenis = document.getElementById('jenis_hp')?.value || 'hutang';
    var sel = document.getElementById('kategori_hutang_piutang_hp_id');
    var namaLabel = document.getElementById('nama_pemberi_hutang_hp_label');
    var namaInput = document.getElementById('nama_pemberi_hutang_hp');
    if (namaLabel) namaLabel.textContent = jenis === 'piutang' ? 'Nama Penerima' : 'Nama Pemberi Hutang';
    if (namaInput) namaInput.placeholder = jenis === 'piutang' ? 'Nama pihak penerima piutang' : 'Nama pihak pemberi hutang';
    if (sel) {
        Array.from(sel.options).forEach(function(opt) {
            if (!opt.value) return;
            opt.hidden = opt.getAttribute('data-jenis') !== jenis;
        });
        if (sel.options[sel.selectedIndex] && sel.options[sel.selectedIndex].hidden) {
            sel.value = '';
        }
    }
    var showHutangFields = jenis === 'hutang';
    var totalWrap = document.getElementById('total_bayar_hp_wrap');
    var bayarWrap = document.getElementById('nominal_bayar_hp_wrap');
    if (totalWrap) totalWrap.style.display = showHutangFields ? '' : 'none';
    if (bayarWrap) bayarWrap.style.display = showHutangFields ? '' : 'none';
}

var persediaanItemIndex = 0;
var persediaanItemOptions = @json($itemPersediaans->map(fn($ip) => ['id' => $ip->id, 'kategori_id' => $ip->kategori_persediaan_id, 'label' => $ip->kode_item_persediaan . ' - ' . $ip->deskripsi]));
var persediaanKategoriOptions = @json($kategoriPersediaans->map(fn($k) => ['id' => $k->id, 'label' => $k->deskripsi]));

function ensurePersediaanItem() {
    if (!document.querySelector('#persediaanItemsBody tr')) {
        addPersediaanItem();
    }
}

function addPersediaanItem(data) {
    data = data || {};
    var idx = persediaanItemIndex++;
    var katHtml = '<option value="">-- Kategori --</option>' +
        persediaanKategoriOptions.map(function(k) {
            return '<option value="'+k.id+'"'+(data.kategori_id === k.id ? ' selected' : '')+'>'+k.label+'</option>';
        }).join('');
    var row = document.createElement('tr');
    row.id = 'persediaan-item-row-' + idx;
    row.innerHTML =
        '<td><select class="kt-select" id="persediaan_kat_'+idx+'" onchange="filterPersediaanItems('+idx+')">'+katHtml+'</select></td>' +
        '<td><select name="items['+idx+'][item_persediaan_id]" id="persediaan_item_'+idx+'" class="kt-select persediaan-required"><option value="">-- Pilih Item --</option></select></td>' +
        '<td><input type="number" name="items['+idx+'][qty]" class="kt-input persediaan-required" min="0.01" step="any" value="'+(data.qty||'')+'" onchange="calcPersediaanItemTotal('+idx+')" oninput="calcPersediaanItemTotal('+idx+')"/></td>' +
        '<td><select name="items['+idx+'][satuan]" class="kt-select persediaan-required"><option value="kg">kg</option><option value="gram">gram</option><option value="liter">liter</option><option value="ml">ml</option><option value="pcs">pcs</option><option value="karung">karung</option><option value="botol">botol</option></select></td>' +
        '<td><div class="kt-input-group"><span class="kt-input-addon">Rp</span><input type="text" class="kt-input" id="persediaan_harga_satuan_display_'+idx+'" placeholder="0" oninput="onPersediaanHargaInput('+idx+')"/></div><input type="hidden" name="items['+idx+'][harga_satuan]" id="persediaan_harga_satuan_val_'+idx+'" value="'+(data.harga_satuan||0)+'"/></td>' +
        '<td><div class="kt-input-group"><span class="kt-input-addon">Rp</span><input type="text" class="kt-input" id="persediaan_harga_total_display_'+idx+'" readonly style="background:var(--muted);" value="0"/></div></td>' +
        '<td><button type="button" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger" onclick="removePersediaanItem('+idx+')"><i class="ki-filled ki-trash"></i></button></td>';
    document.getElementById('persediaanItemsBody').appendChild(row);
    filterPersediaanItems(idx, data.item_persediaan_id);
    if (data.satuan) document.querySelector('[name="items['+idx+'][satuan]"]').value = data.satuan;
    if (data.harga_satuan) document.getElementById('persediaan_harga_satuan_display_'+idx).value = formatMoney(data.harga_satuan);
    calcPersediaanItemTotal(idx);
    onTipeTransaksiChange();
}

function filterPersediaanItems(idx, keepVal) {
    var katId = document.getElementById('persediaan_kat_' + idx).value;
    var sel = document.getElementById('persediaan_item_' + idx);
    var filtered = katId ? persediaanItemOptions.filter(function(o) { return o.kategori_id === katId; }) : persediaanItemOptions;
    sel.innerHTML = '<option value="">-- Pilih Item --</option>' +
        filtered.map(function(o) {
            return '<option value="'+o.id+'"'+(keepVal === o.id ? ' selected' : '')+'>'+o.label+'</option>';
        }).join('');
}

function removePersediaanItem(idx) {
    var row = document.getElementById('persediaan-item-row-' + idx);
    if (row) row.remove();
    calcPersediaanGrandTotal();
}

function onPersediaanHargaInput(idx) {
    var display = document.getElementById('persediaan_harga_satuan_display_' + idx);
    var raw = parseMoney(display.value);
    display.value = raw > 0 ? formatMoney(raw) : '';
    document.getElementById('persediaan_harga_satuan_val_' + idx).value = raw;
    calcPersediaanItemTotal(idx);
}

function calcPersediaanItemTotal(idx) {
    var qty = parseFloat(document.querySelector('[name="items['+idx+'][qty]"]')?.value) || 0;
    var harga = parseInt(document.getElementById('persediaan_harga_satuan_val_' + idx)?.value) || 0;
    var total = qty * harga;
    var el = document.getElementById('persediaan_harga_total_display_' + idx);
    if (el) el.value = formatMoney(total);
    calcPersediaanGrandTotal();
}

function calcPersediaanGrandTotal() {
    var total = 0;
    document.querySelectorAll('[id^="persediaan_harga_total_display_"]').forEach(function(el) {
        total += parseMoney(el.value);
    });
    var display = document.getElementById('persediaanGrandTotalDisplay');
    if (display) display.textContent = 'Rp ' + formatMoney(total);
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
            } else {
                var reader = new FileReader();
                reader.onload = (function(d, i, fname) {
                    return function(e) {
                        d.innerHTML =
                            '<img src="' + e.target.result + '" class="w-full h-24 object-cover cursor-pointer lb-preview" alt="Preview">' +
                            '<div class="flex items-center justify-end px-2 py-1.5 border-t border-border">' +
                                '<button type="button" onclick="removePreview(' + i + ')" class="size-5 rounded-full bg-destructive text-white flex items-center justify-center hover:bg-destructive/80 transition-colors" title="Hapus">' +
                                    '<i class="ki-filled ki-cross text-[10px]"></i>' +
                                '</button>' +
                            '</div>';
                    };
                })(div, index, file.name);
                reader.readAsDataURL(file);
                container.appendChild(div);
                return;
            }
            container.appendChild(div);
        });
    }
}

function previewEvidenAset(input) {
    renderFilePreview(input, document.getElementById('previewContainerAset'), 'aset-ev-preview-');
}

function previewModeEviden(input, mode) {
    renderFilePreview(input, document.getElementById('previewContainer' + mode), mode.toLowerCase() + '-ev-preview-');
}

function renderFilePreview(input, container, prefix) {
    if (!container) return;
    container.innerHTML = '';
    if (input.files) {
        Array.from(input.files).forEach(function(file, index) {
            var isPdf = file.type === 'application/pdf';
            var isExcel = file.type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || file.type === 'application/vnd.ms-excel';
            var div = document.createElement('div');
            div.className = 'relative group rounded-xl border border-border overflow-hidden bg-muted hover:ring-2 hover:ring-primary hover:shadow-md transition-all';
            div.id = prefix + index;
            if (isPdf || isExcel) {
                div.innerHTML =
                    '<div class="flex flex-col items-center justify-center w-full h-24 p-3">' +
                        '<i class="ki-filled ' + (isExcel ? 'ki-excel text-green-600' : 'ki-document text-primary') + ' text-3xl mb-2"></i>' +
                        '<span class="text-[10px] text-muted-foreground text-center truncate w-full">' + file.name + '</span>' +
                    '</div>' +
                    '<div class="flex items-center justify-end px-2 py-1.5 border-t border-border">' +
                        '<button type="button" onclick="removeElementById(\'' + prefix + index + '\')" class="size-5 rounded-full bg-destructive text-white flex items-center justify-center hover:bg-destructive/80 transition-colors" title="Hapus">' +
                            '<i class="ki-filled ki-cross text-[10px]"></i>' +
                        '</button>' +
                    '</div>';
                container.appendChild(div);
            } else {
                var reader = new FileReader();
                reader.onload = (function(d, id) {
                    return function(e) {
                        d.innerHTML =
                            '<img src="' + e.target.result + '" class="w-full h-24 object-cover cursor-pointer lb-preview" alt="Preview">' +
                            '<div class="flex items-center justify-end px-2 py-1.5 border-t border-border">' +
                                '<button type="button" onclick="removeElementById(\'' + id + '\')" class="size-5 rounded-full bg-destructive text-white flex items-center justify-center hover:bg-destructive/80 transition-colors" title="Hapus">' +
                                    '<i class="ki-filled ki-cross text-[10px]"></i>' +
                                '</button>' +
                            '</div>';
                    };
                })(div, div.id);
                reader.readAsDataURL(file);
                container.appendChild(div);
            }
        });
    }
}

function previewFotoAset(input) {
    renderImagePreview(input, document.getElementById('fotoAsetPreview'), 'foto-aset-preview-');
}

function renderImagePreview(input, container, prefix) {
    if (!container) return;
    container.innerHTML = '';
    if (input.files) {
        Array.from(input.files).forEach(function(file, index) {
            var reader = new FileReader();
            var div = document.createElement('div');
            div.className = 'relative group rounded-xl border border-border overflow-hidden bg-muted hover:ring-2 hover:ring-primary hover:shadow-md transition-all';
            div.id = prefix + index;
            reader.onload = (function(d, id) {
                return function(e) {
                    d.innerHTML =
                        '<img src="' + e.target.result + '" class="w-full h-24 object-cover cursor-pointer lb-preview" alt="Preview">' +
                        '<div class="flex items-center justify-end px-2 py-1.5 border-t border-border">' +
                            '<button type="button" onclick="removeElementById(\'' + id + '\')" class="size-5 rounded-full bg-destructive text-white flex items-center justify-center hover:bg-destructive/80 transition-colors" title="Hapus">' +
                                '<i class="ki-filled ki-cross text-[10px]"></i>' +
                            '</button>' +
                        '</div>';
                };
            })(div, div.id);
            reader.readAsDataURL(file);
            container.appendChild(div);
        });
    }
}

function removeElementById(id) {
    var el = document.getElementById(id);
    if (el) el.remove();
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
        if (target.id && target.id.includes('_gaji_val')) {
            calcGajiMode();
        }
        if (target.id === 'nominal_val') {
            updateNominalDibayar();
        }
    });
}

// Init on page load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.money-input').forEach(initMoneyInput);
    onPembayaranChange();
    onPembayaranAsetChange();
    onStatusPembayaranChange();
    onTipeTransaksiChange();

    document.querySelector('form[enctype="multipart/form-data"]').addEventListener('submit', function(e) {
        var statusEl = document.getElementById('status_pembayaran');
        if (!statusEl) return;
        var status = statusEl.value;
        var nominal = parseMoney(document.getElementById('nominal_val').value);
        var dibayar = parseMoney(document.getElementById('nominal_dibayar_val').value);
        var sisa = Math.max(0, nominal - dibayar);
        if (status === 'sebagian' && sisa > 0 && document.getElementById('konfirmasi_hutang').value !== '1') {
            if (!confirm('Sisa pembayaran sebesar ' + formatRp(sisa) + ' akan dicatat sebagai hutang. Lanjutkan?')) {
                e.preventDefault();
                return;
            }
            document.getElementById('konfirmasi_hutang').value = '1';
        }
    });

    var nominalAset = document.getElementById('nominal_pembelian_display');
    var residuAset = document.getElementById('nilai_residu_display');
    var umurAset = document.getElementById('umur_manfaat');
    if (nominalAset) nominalAset.addEventListener('input', updateDepresiasiPreview);
    if (residuAset) residuAset.addEventListener('input', updateDepresiasiPreview);
    if (umurAset) umurAset.addEventListener('input', updateDepresiasiPreview);

    var dibayarDisplay = document.getElementById('nominal_dibayar_display');
    if (dibayarDisplay) dibayarDisplay.addEventListener('input', updateHutangPreview);

    var initTambak = document.getElementById('tambak_id');
    if (initTambak && initTambak.value) {
        loadBlokByTambak('{{ old('blok_id', $transaksi?->blok_id) }}');
    }

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
</script>
@endpush
