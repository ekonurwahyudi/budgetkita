@extends('layouts.app')

@section('title', 'Detail Pembelian Aset - ' . $pembelianAset->nama_aset)
@section('page-title', 'Detail Pembelian Aset')
@section('page-description', $pembelianAset->nama_aset)

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <div class="flex items-center gap-3">
                <h3 class="kt-card-title">{{ $pembelianAset->nama_aset }}</h3>
                @if($pembelianAset->status === 'selesai')
                    <span class="kt-badge kt-badge-sm kt-badge-success">Selesai</span>
                @elseif($pembelianAset->status === 'cancel')
                    <span class="kt-badge kt-badge-sm kt-badge-destructive">Cancel</span>
                @elseif($pembelianAset->status === 'proses')
                    <span class="kt-badge kt-badge-sm kt-badge-primary">Proses</span>
                @elseif($pembelianAset->status === 'pending')
                    <span class="kt-badge kt-badge-sm kt-badge-warning">Pending</span>
                @else
                    <span class="kt-badge kt-badge-sm kt-badge-outline">Awaiting</span>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @if($pembelianAset->status === 'awaiting_approval' && auth()->user()->hasRole('Owner'))
                <form method="POST" action="{{ route('pembelian-aset.approve', $pembelianAset) }}" class="inline">@csrf<button type="submit" class="kt-btn kt-btn-primary kt-btn-sm"><i class="ki-filled ki-check"></i> Approve</button></form>
                <button type="button" class="kt-btn kt-btn-destructive kt-btn-sm" onclick="openRejectModal('{{ route('pembelian-aset.reject', $pembelianAset) }}', '{{ e($pembelianAset->nomor_transaksi) }}')"><i class="ki-filled ki-cross"></i> Reject</button>
                @endif
                @if(auth()->user()->hasRole('Owner') || in_array($pembelianAset->status, ['awaiting_approval','pending']))
                @can('pembelian-aset.edit')
                <a href="{{ route('pembelian-aset.edit', $pembelianAset) }}" class="kt-btn kt-btn-sm kt-btn-outline">
                    <i class="ki-filled ki-pencil"></i> Edit
                </a>
                @endcan
                @endif
                <a href="{{ route('pembelian-aset.index') }}" class="kt-btn kt-btn-sm kt-btn-outline">
                    <i class="ki-filled ki-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div class="kt-card-content py-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <table class="kt-table-auto">
                        <tbody>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8 w-40">Nama Aset</td>
                                <td class="text-sm pb-3 font-medium">{{ $pembelianAset->nama_aset }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8 w-40">No. Transaksi</td>
                                <td class="text-sm text-mono pb-3">{{ $pembelianAset->nomor_transaksi }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Kategori</td>
                                <td class="text-sm pb-3">{{ $pembelianAset->kategoriAset?->deskripsi ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Tambak</td>
                                <td class="text-sm pb-3">{{ $pembelianAset->tambak?->nama_tambak ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Blok</td>
                                <td class="text-sm pb-3">{{ $pembelianAset->blok?->nama_blok ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Siklus</td>
                                <td class="text-sm pb-3">{{ $pembelianAset->siklus?->nama_siklus ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Tanggal Pembelian</td>
                                <td class="text-sm text-mono pb-3">{{ $pembelianAset->tgl_pembelian?->format('d/m/Y') ?? '-' }}</td>
                            </tr>
                            @php
                                $qtyOnHandDetail = (int) ($pembelianAset->qty_tersedia ?? $pembelianAset->qty) + (int) ($pembelianAset->qty_rusak ?? 0);
                            @endphp
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Qty On Hand</td>
                                <td class="text-sm text-mono pb-3">{{ number_format($qtyOnHandDetail, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Harga Satuan</td>
                                <td class="text-sm text-mono pb-3">Rp {{ number_format($pembelianAset->harga_satuan ?: $pembelianAset->nominal_pembelian, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Total Harga</td>
                                <td class="text-sm text-mono pb-3 font-semibold">Rp {{ number_format($pembelianAset->nominal_pembelian, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Umur Manfaat</td>
                                <td class="text-sm pb-3">{{ $pembelianAset->umur_manfaat }} Tahun</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Metode Depresiasi</td>
                                <td class="text-sm pb-3">
                                    @if($pembelianAset->metode_depresiasi === 'persen')
                                        <span class="kt-badge kt-badge-sm kt-badge-primary kt-badge-outline">Persen ({{ $pembelianAset->persen_depresiasi }}%)</span>
                                    @elseif($pembelianAset->metode_depresiasi === 'tanpa')
                                        <span class="kt-badge kt-badge-sm kt-badge-warning kt-badge-outline">Tanpa Depresiasi</span>
                                    @else
                                        <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">Garis Lurus</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div>
                    <table class="kt-table-auto">
                        <tbody>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8 w-40">Nilai Residu</td>
                                <td class="text-sm text-mono pb-3">Rp {{ number_format($pembelianAset->nilai_residu, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Status Pembayaran</td>
                                <td class="text-sm pb-3">
                                    @if($pembelianAset->status_pembayaran === 'hutang')
                                        <span class="kt-badge kt-badge-sm kt-badge-warning">Hutang</span>
                                    @elseif($pembelianAset->status_pembayaran === 'sebagian')
                                        <span class="kt-badge kt-badge-sm kt-badge-primary">Bayar Sebagian</span>
                                    @else
                                        <span class="kt-badge kt-badge-sm kt-badge-success">Lunas</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Dibayar</td>
                                <td class="text-sm text-mono pb-3">Rp {{ number_format($pembelianAset->nominal_dibayar ?? $pembelianAset->nominal_pembelian, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Sisa Hutang</td>
                                <td class="text-sm text-mono pb-3">Rp {{ number_format(max(0, $pembelianAset->nominal_pembelian - ($pembelianAset->nominal_dibayar ?? 0)), 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Account Bank</td>
                                <td class="text-sm pb-3">
                                    @if($pembelianAset->accountBank)
                                        {{ $pembelianAset->accountBank->nama_bank }} - {{ $pembelianAset->accountBank->nama_pemilik }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @if($pembelianAset->hutangPiutang)
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Catatan Hutang</td>
                                <td class="text-sm pb-3">
                                    <a href="{{ route('hutang-piutang.show', $pembelianAset->hutangPiutang) }}" class="text-primary hover:underline">
                                        {{ $pembelianAset->hutangPiutang->nomor_transaksi }}
                                    </a>
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Catatan</td>
                                <td class="text-sm pb-3">{{ $pembelianAset->catatan ?? '-' }}</td>
                            </tr>
                            @if($pembelianAset->status === 'cancel' && $pembelianAset->reject_reason)
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Alasan Reject</td>
                                <td class="text-sm pb-3">
                                    <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium" style="background:rgba(241,65,108,0.12); color:#f1416c;">
                                        {{ $pembelianAset->reject_reason }}
                                    </span>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Depresiasi Summary Cards --}}
    @if($pembelianAset->metode_depresiasi !== 'tanpa')
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="kt-card">
            <div class="kt-card-content py-4 flex items-center gap-3">
                <div class="size-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                    <i class="ki-filled ki-calculator text-primary text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Depresiasi / Tahun</p>
                    <p class="text-base font-semibold text-mono">Rp {{ number_format($pembelianAset->depresiasi_per_tahun, 0, ',', '.') }}</p>
                    @if($pembelianAset->metode_depresiasi === 'persen')
                    <p class="text-[11px] text-muted-foreground">{{ $pembelianAset->persen_depresiasi }}% per tahun</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="kt-card">
            <div class="kt-card-content py-4 flex items-center gap-3">
                <div class="size-10 rounded-full bg-info/10 flex items-center justify-center shrink-0">
                    <i class="ki-filled ki-chart-line text-info text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Umur Berjalan</p>
                    <p class="text-base font-semibold">{{ $pembelianAset->umur_berjalan }} Tahun</p>
                </div>
            </div>
        </div>
        <div class="kt-card">
            <div class="kt-card-content py-4 flex items-center gap-3">
                <div class="size-10 rounded-full bg-warning/10 flex items-center justify-center shrink-0">
                    <i class="ki-filled ki-graph-up text-warning text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Akumulasi Depresiasi</p>
                    <p class="text-base font-semibold text-mono">Rp {{ number_format($pembelianAset->akumulasi_depresiasi, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="kt-card">
            <div class="kt-card-content py-4 flex items-center gap-3">
                <div class="size-10 rounded-full bg-success/10 flex items-center justify-center shrink-0">
                    <i class="ki-filled ki-dollar text-success text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Nilai Buku</p>
                    <p class="text-base font-semibold text-success text-mono">Rp {{ number_format($pembelianAset->nilai_buku_aset, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="kt-card">
        <div class="kt-card-content py-4 flex items-center gap-3">
            <div class="size-10 rounded-full bg-gray-100 flex items-center justify-center shrink-0">
                <i class="ki-filled ki-shield-check text-gray-500 text-lg"></i>
            </div>
            <div>
                <p class="text-sm font-medium">Tanpa Depresiasi</p>
                <p class="text-xs text-muted-foreground">Aset ini tidak memiliki depresiasi (seperti tanah)</p>
                <p class="text-base font-semibold text-success text-mono mt-1">Nilai Buku: Rp {{ number_format($pembelianAset->nilai_buku_aset, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Kondisi & Penjualan Aset --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="kt-card">
            <div class="kt-card-header min-h-14">
                <h3 class="kt-card-title">Jual Aset</h3>
            </div>
            <form method="POST" action="{{ route('pembelian-aset.jual', $pembelianAset) }}" id="jualAsetForm">
                @csrf
                <input type="hidden" name="harga_satuan_jual" id="harga_satuan_jual_val" value="0">
                <input type="hidden" name="nominal_dibayar_jual" id="nominal_dibayar_jual_val" value="0">
                <div class="kt-card-content py-4 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Tanggal</label>
                            <div class="kt-input">
                                <i class="ki-outline ki-calendar"></i>
                                <input class="grow" name="tgl_penjualan" data-kt-date-picker="true" data-kt-date-picker-input-mode="true" readonly type="text" value="{{ now()->format('Y-m-d') }}" required />
                            </div>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Kondisi Dijual</label>
                            <select name="kondisi" id="jual_kondisi" class="kt-select" required>
                                <option value="baik">Baik</option>
                                <option value="rusak">Rusak</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Qty Jual</label>
                            <input type="number" name="qty_jual" id="qty_jual" class="kt-input" min="1" step="1" value="1" required>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Harga Satuan</label>
                            <div class="kt-input-group">
                                <span class="kt-input-addon">Rp.</span>
                                <input type="text" id="harga_satuan_jual_display" class="kt-input sale-money" data-target="harga_satuan_jual_val" value="0" required>
                            </div>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Total</label>
                            <div class="kt-input-group">
                                <span class="kt-input-addon">Rp.</span>
                                <input type="text" id="total_jual_display" class="kt-input" readonly style="background:var(--muted);" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Pembeli</label>
                            <input type="text" name="pembeli" class="kt-input" placeholder="Nama pembeli">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-foreground">Status Pembayaran</label>
                            <select name="status_pembayaran_jual" id="status_pembayaran_jual" class="kt-select" required>
                                <option value="lunas">Lunas</option>
                                <option value="piutang">Piutang</option>
                                <option value="sebagian">Bayar Sebagian</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5" id="nominal_dibayar_jual_wrap" style="display:none;">
                            <label class="text-sm font-medium text-foreground">Dibayar Sekarang</label>
                            <div class="kt-input-group">
                                <span class="kt-input-addon">Rp.</span>
                                <input type="text" id="nominal_dibayar_jual_display" class="kt-input sale-money" data-target="nominal_dibayar_jual_val" value="0">
                            </div>
                        </div>
                        <div class="flex flex-col gap-1.5" id="account_bank_jual_wrap">
                            <label class="text-sm font-medium text-foreground">Masuk Ke Bank</label>
                            <select name="account_bank_id_jual" id="account_bank_id_jual" class="kt-select">
                                <option value="">-- Pilih Bank --</option>
                                @foreach($accountBanks as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->nama_bank }} - {{ $bank->nama_pemilik }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-foreground">Catatan</label>
                        <textarea name="catatan_jual" class="kt-input" rows="2" style="height:64px;"></textarea>
                    </div>
                </div>
                <div class="kt-card-footer justify-end">
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-dollar"></i> Simpan Penjualan
                    </button>
                </div>
            </form>
        </div>    
    <div class="kt-card">
            <div class="kt-card-header min-h-14">
                <h3 class="kt-card-title">Kondisi Aset</h3>
            </div>
            <div class="kt-card-content py-4 space-y-4">
                @php
                    $qtyBaik = (int) ($pembelianAset->qty_tersedia ?? $pembelianAset->qty);
                    $qtyRusak = (int) ($pembelianAset->qty_rusak ?? 0);
                    $qtyTerjual = (int) $pembelianAset->penjualanAsets->sum('qty');
                    $qtyOnHand = $qtyBaik + $qtyRusak;
                @endphp
                <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:0.5rem;">
                    <div class="rounded-lg border border-border p-2.5 min-w-0">
                        <p class="text-xs text-muted-foreground">Qty On Hand</p>
                        <p class="text-lg font-semibold text-mono">{{ number_format($qtyOnHand, 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-lg border border-border p-2.5 min-w-0">
                        <p class="text-xs text-muted-foreground">Baik</p>
                        <p class="text-lg font-semibold text-success text-mono">{{ number_format($qtyBaik, 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-lg border border-border p-2.5 min-w-0">
                        <p class="text-xs text-muted-foreground">Rusak</p>
                        <p class="text-lg font-semibold text-warning text-mono">{{ number_format($qtyRusak, 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-lg border border-border p-2.5 min-w-0">
                        <p class="text-xs text-muted-foreground">Terjual</p>
                        <p class="text-lg font-semibold text-primary text-mono">{{ number_format($qtyTerjual, 0, ',', '.') }}</p>
                    </div>
                </div>

                @can('pembelian-aset.edit')
                <form method="POST" action="{{ route('pembelian-aset.kondisi', $pembelianAset) }}" class="flex items-end gap-3">
                    @csrf
                    @method('PATCH')
                    <div class="flex flex-col gap-1.5 grow">
                        <label class="text-sm font-medium text-foreground">Qty Rusak</label>
                        <input type="number" name="qty_rusak" class="kt-input" min="0" step="1" value="{{ (int) ($pembelianAset->qty_rusak ?? 0) }}" />
                    </div>
                    <button type="submit" class="kt-btn kt-btn-outline">Simpan Kondisi</button>
                </form>
                @endcan
            </div>
        </div>
    </div>

    @if($pembelianAset->penjualanAsets->isNotEmpty())
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">Riwayat Penjualan Aset</h3>
        </div>
        <div class="kt-card-content py-4">
            <div class="kt-table-wrapper kt-scrollable">
                <table class="kt-table">
                    <thead>
                        <tr>
                            <th>No. Transaksi</th>
                            <th>Tanggal</th>
                            <th>Qty</th>
                            <th>Kondisi</th>
                            <th>Total</th>
                            <th>Dibayar</th>
                            <th>Status</th>
                            <th>Bank/Piutang</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pembelianAset->penjualanAsets as $jual)
                        <tr>
                            <td class="text-mono">{{ $jual->nomor_transaksi }}</td>
                            <td>{{ $jual->tgl_penjualan?->format('d/m/Y') }}</td>
                            <td class="text-mono">{{ number_format($jual->qty, 0, ',', '.') }}</td>
                            <td>{{ ucfirst($jual->kondisi) }}</td>
                            <td class="text-mono">Rp {{ number_format($jual->total_penjualan, 0, ',', '.') }}</td>
                            <td class="text-mono">Rp {{ number_format($jual->nominal_dibayar, 0, ',', '.') }}</td>
                            <td>
                                @if($jual->status_pembayaran === 'lunas')
                                    <span class="kt-badge kt-badge-sm kt-badge-success">Lunas</span>
                                @elseif($jual->status_pembayaran === 'sebagian')
                                    <span class="kt-badge kt-badge-sm kt-badge-primary">Sebagian</span>
                                @else
                                    <span class="kt-badge kt-badge-sm kt-badge-warning">Piutang</span>
                                @endif
                            </td>
                            <td>
                                @if($jual->accountBank)
                                    {{ $jual->accountBank->nama_bank }}
                                @endif
                                @if($jual->piutang)
                                    <a href="{{ route('hutang-piutang.show', $jual->piutang) }}" class="text-primary hover:underline ms-2">{{ $jual->piutang->nomor_transaksi }}</a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Foto Aset --}}
    @if(!empty($pembelianAset->foto_aset))
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">Foto Aset</h3>
            <span class="text-sm text-muted-foreground">{{ count($pembelianAset->foto_aset) }} foto</span>
        </div>
        <div class="kt-card-content py-4">
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
                @foreach($pembelianAset->foto_aset as $idx => $f)
                @php $fotoUrl = \Illuminate\Support\Facades\Storage::url($f); @endphp
                <div class="lb-thumb group relative aspect-square rounded-xl border border-border overflow-hidden bg-muted cursor-pointer hover:ring-2 hover:ring-primary hover:shadow-md transition-all"
                     data-src="{{ $fotoUrl }}">
                    <img src="{{ $fotoUrl }}" class="w-full h-full object-cover" alt="Foto Aset {{ $idx + 1 }}">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors flex items-center justify-center pointer-events-none">
                        <i class="ki-filled ki-eye text-white text-2xl drop-shadow-lg opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Eviden --}}
    @if(!empty($pembelianAset->eviden))
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">Eviden</h3>
            <span class="text-sm text-muted-foreground">{{ count($pembelianAset->eviden) }} file</span>
        </div>
        <div class="kt-card-content py-4">
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
                @foreach($pembelianAset->eviden as $idx => $ev)
                @php
                    $isPdf = \Illuminate\Support\Str::endsWith(strtolower($ev), ['.pdf']);
                    $url = \Illuminate\Support\Facades\Storage::url($ev);
                @endphp
                @if($isPdf)
                <a href="{{ $url }}" target="_blank" class="group relative aspect-square rounded-xl border border-border overflow-hidden bg-muted flex flex-col items-center justify-center p-3 hover:shadow-md hover:border-primary/50 transition-all">
                    <i class="ki-filled ki-document text-3xl text-primary mb-2"></i>
                    <span class="text-[10px] text-muted-foreground text-center truncate w-full">PDF</span>
                </a>
                @else
                <div class="lb-thumb group relative aspect-square rounded-xl border border-border overflow-hidden bg-muted cursor-pointer hover:ring-2 hover:ring-primary hover:shadow-md transition-all"
                     data-src="{{ $url }}">
                    <img src="{{ $url }}" class="w-full h-full object-cover" alt="Eviden {{ $idx + 1 }}">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors flex items-center justify-center pointer-events-none">
                        <i class="ki-filled ki-eye text-white text-2xl drop-shadow-lg opacity-0 group-hover:opacity-100 transition-opacity"></i>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<div id="rejectModal" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; padding:1rem;">
    <div class="kt-card w-full max-w-[460px] shadow-2xl">
        <div class="kt-card-header min-h-14">
            <div>
                <h3 class="kt-card-title">Reject Pembelian Aset</h3>
                <p class="text-xs text-muted-foreground mt-1" id="rejectNomor">-</p>
            </div>
            <button type="button" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" onclick="closeRejectModal()">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form method="POST" id="rejectForm">
            @csrf
            <div class="kt-card-content py-4">
                <label class="text-sm font-medium text-foreground" for="alasan_reject">Alasan Reject <span class="text-danger">*</span></label>
                <textarea id="alasan_reject" name="alasan_reject" class="kt-input mt-2" rows="4" style="height:120px;" placeholder="Tuliskan alasan pembelian aset ditolak..." required minlength="5"></textarea>
            </div>
            <div class="kt-card-footer justify-end gap-2">
                <button type="button" class="kt-btn kt-btn-outline" onclick="closeRejectModal()">Batal</button>
                <button type="submit" class="kt-btn kt-btn-destructive">
                    <i class="ki-filled ki-cross"></i> Reject
                </button>
            </div>
        </form>
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
function parseSaleMoney(val) {
    return parseInt(String(val).replace(/\D/g, '')) || 0;
}

function formatSaleMoney(val) {
    return (parseInt(val) || 0).toLocaleString('id-ID');
}

function updateSaleTotal() {
    var qty = parseInt(document.getElementById('qty_jual')?.value) || 0;
    var harga = parseSaleMoney(document.getElementById('harga_satuan_jual_val')?.value || 0);
    var total = qty * harga;
    var status = document.getElementById('status_pembayaran_jual')?.value;
    var paidVal = document.getElementById('nominal_dibayar_jual_val');
    var paidDisplay = document.getElementById('nominal_dibayar_jual_display');

    document.getElementById('total_jual_display').value = formatSaleMoney(total);

    if (status === 'lunas') {
        paidVal.value = total;
        if (paidDisplay) paidDisplay.value = formatSaleMoney(total);
    } else if (status === 'piutang') {
        paidVal.value = 0;
        if (paidDisplay) paidDisplay.value = '0';
    } else {
        var paid = Math.min(parseSaleMoney(paidVal.value), total);
        paidVal.value = paid;
        if (paidDisplay) paidDisplay.value = formatSaleMoney(paid);
    }
}

function onSalePaymentChange() {
    var status = document.getElementById('status_pembayaran_jual').value;
    document.getElementById('nominal_dibayar_jual_wrap').style.display = status === 'sebagian' ? '' : 'none';
    document.getElementById('account_bank_jual_wrap').style.display = status === 'piutang' ? 'none' : '';
    document.getElementById('account_bank_id_jual').required = status !== 'piutang';
    updateSaleTotal();
}

function initSaleMoneyInputs() {
    document.querySelectorAll('.sale-money').forEach(function(el) {
        var target = document.getElementById(el.dataset.target);
        el.addEventListener('input', function() {
            var raw = parseSaleMoney(this.value);
            this.value = formatSaleMoney(raw);
            target.value = raw;
            updateSaleTotal();
        });
        el.addEventListener('blur', function() {
            var raw = parseSaleMoney(this.value);
            this.value = formatSaleMoney(raw);
            target.value = raw;
            updateSaleTotal();
        });
    });
}

function openRejectModal(action, nomor) {
    var modal = document.getElementById('rejectModal');
    var form = document.getElementById('rejectForm');
    var label = document.getElementById('rejectNomor');
    var textarea = document.getElementById('alasan_reject');

    form.action = action;
    label.textContent = nomor;
    textarea.value = '';
    modal.style.display = 'flex';
    textarea.focus();
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    initSaleMoneyInputs();
    if (document.getElementById('jualAsetForm')) {
        document.getElementById('qty_jual').addEventListener('input', updateSaleTotal);
        document.getElementById('status_pembayaran_jual').addEventListener('change', onSalePaymentChange);
        onSalePaymentChange();

        document.getElementById('jualAsetForm').addEventListener('submit', function(e) {
            var status = document.getElementById('status_pembayaran_jual').value;
            var total = parseSaleMoney(document.getElementById('total_jual_display').value);
            var paid = parseSaleMoney(document.getElementById('nominal_dibayar_jual_val').value);
            var sisa = Math.max(0, total - paid);

            if ((status === 'piutang' || status === 'sebagian') && sisa > 0) {
                if (!confirm('Sisa penjualan sebesar Rp ' + formatSaleMoney(sisa) + ' akan dicatat sebagai piutang. Lanjutkan?')) {
                    e.preventDefault();
                }
            }
        });
    }

    var modal = document.getElementById('lb-modal');
    var img = document.getElementById('lb-img');
    var closeBtn = document.getElementById('lb-close');

    function open(src) {
        img.src = src;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    function close() {
        modal.style.display = 'none';
        img.src = '';
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.lb-thumb').forEach(function(el) {
        el.addEventListener('click', function() {
            open(this.dataset.src);
        });
    });

    closeBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        close();
    });

    modal.addEventListener('click', function(e) {
        if (e.target === modal || e.target === img) {
            close();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') close();
    });
});
</script>
@endpush
