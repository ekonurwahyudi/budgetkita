@extends('layouts.app')

@section('title', 'Detail Siklus - ' . $siklus->nama_siklus)
@section('page-title', 'Detail Siklus')
@section('page-description', $siklus->nama_siklus)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-7.5">
    {{-- KIRI: Tabel History --}}
    <div class="col-span-2">
        <div class="flex flex-col gap-5 lg:gap-7.5">
            {{-- Tabel History Panen --}}
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">History Panen</h3>
                    @can('panen.create')
                    <button type="button" class="kt-btn kt-btn kt-btn-primary" onclick="openPanenModal()">
                        <i class="ki-filled ki-plus-squared"></i> Tambah Panen
                    </button>
                    @endcan
                </div>
                <div class="kt-card-table">
                    <div class="kt-table-wrapper kt-scrollable">
                        <table class="kt-table">
                            <thead>
                                <tr>
                                    <th class="w-12">No</th>
                                    <th>Tgl Panen</th>
                                    <th>Tipe</th>
                                    <th>Umur</th>
                                    <th>Ukuran</th>
                                    <th>Total Berat</th>
                                    <th>Harga Jual</th>
                                    <th>Total Penjualan</th>
                                    <th>Pembeli</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($siklus->panens as $i => $panen)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $panen->tgl_panen?->format('d/m/Y') ?? '-' }}</td>
                                    <td>
                                        @if($panen->tipe_panen === 'parsial')
                                            <span class="kt-badge kt-badge-sm kt-badge-warning kt-badge-outline">Parsial</span>
                                        @else
                                            <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">Total</span>
                                        @endif
                                    </td>
                                    <td>{{ $panen->umur ?? '-' }} Hari</td>
                                    <td>{{ $panen->ukuran ?? '-' }}</td>
                                    <td>{{ number_format($panen->total_berat ?? 0) }} kg</td>
                                    <td>Rp {{ number_format($panen->harga_jual ?? 0, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($panen->total_penjualan ?? 0, 0, ',', '.') }}</td>
                                    <td>{{ $panen->pembeli ?? '-' }}</td>
                                    <td>
                                        @if($panen->status === 'lunas')
                                            <span class="kt-badge kt-badge-sm kt-badge-success">Lunas</span>
                                        @else
                                            <span class="kt-badge kt-badge-sm kt-badge-warning">Belum Lunas</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="10" class="text-center text-muted-foreground py-4">Belum ada data panen</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Tabel Transaksi Keuangan --}}
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Transaksi Keuangan</h3>
                </div>
                <div class="kt-card-table">
                    <div class="kt-table-wrapper kt-scrollable">
                        <table class="kt-table">
                            <thead>
                                <tr>
                                    <th class="w-12">No</th>
                                    <th>No. Transaksi</th>
                                    <th>Tgl Kwitansi</th>
                                    <th>Jenis</th>
                                    <th>Aktivitas</th>
                                    <th>Kategori</th>
                                    <th>Item</th>
                                    <th>Nominal</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transaksis as $i => $trx)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td class="text-mono">{{ $trx->nomor_transaksi }}</td>
                                    <td>{{ $trx->tgl_kwitansi?->format('d/m/Y') ?? '-' }}</td>
                                    <td>
                                        @if($trx->jenis_transaksi === 'pemasukan')
                                            <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">Pemasukan</span>
                                        @else
                                            <span class="kt-badge kt-badge-sm kt-badge-destructive kt-badge-outline">Pengeluaran</span>
                                        @endif
                                    </td>
                                    <td>{{ $trx->aktivitas ?? '-' }}</td>
                                    <td>{{ $trx->kategoriTransaksi?->nama ?? '-' }}</td>
                                    <td>{{ $trx->itemTransaksi?->nama ?? '-' }}</td>
                                    <td class="text-mono">Rp {{ number_format($trx->nominal ?? 0, 0, ',', '.') }}</td>
                                    <td>
                                        @if($trx->status === 'approved')
                                            <span class="kt-badge kt-badge-sm kt-badge-success">Approved</span>
                                        @elseif($trx->status === 'rejected')
                                            <span class="kt-badge kt-badge-sm kt-badge-destructive">Rejected</span>
                                        @else
                                            <span class="kt-badge kt-badge-sm kt-badge-warning">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="9" class="text-center text-muted-foreground py-4">Belum ada transaksi keuangan</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Tabel Pemberian Pakan --}}
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Pemberian Pakan</h3>
                </div>
                <div class="kt-card-table">
                    <div class="kt-table-wrapper kt-scrollable">
                        <table class="kt-table">
                            <thead>
                                <tr>
                                    <th class="w-12">No</th>
                                    <th>Tanggal</th>
                                    <th>Item Pakan</th>
                                    <th>Jumlah Pakan (kg)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pemberianPakans as $i => $pakan)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $pakan->tgl_pakan?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td>{{ $pakan->itemPersediaan?->nama ?? '-' }}</td>
                                    <td class="text-mono">{{ number_format($pakan->jumlah_pakan ?? 0, 2) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted-foreground py-4">Belum ada data pemberian pakan</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- KANAN: Info Cards --}}
    <div class="col-span-1">
        <div class="grid gap-5 lg:gap-7.5">
            {{-- Info Siklus --}}
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Info Siklus</h3>
                    <div>
                        @if($siklus->status === 'aktif')
                            <span class="kt-badge kt-badge-success">Aktif</span>
                        @elseif($siklus->status === 'selesai')
                            <span class="kt-badge kt-badge-primary">Selesai</span>
                        @else
                            <span class="kt-badge kt-badge-destructive">Gagal</span>
                        @endif
                    </div>
                </div>
                <div class="kt-card-content pt-3.5 pb-3.5">
                    <table class="kt-table-auto">
                        <tbody>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">Nama Siklus:</td>
                                <td class="text-sm text-mono pb-3">{{ $siklus->nama_siklus }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">Tgl Siklus:</td>
                                <td class="text-sm text-mono pb-3">{{ $siklus->tgl_siklus?->format('d/m/Y') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">Tgl Tebar:</td>
                                <td class="text-sm text-mono pb-3">{{ $siklus->tgl_tebar?->format('d/m/Y') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">Umur Awal:</td>
                                <td class="text-sm text-mono pb-3">{{ $siklus->umur_awal ?? '-' }} Hari</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">Total Tebar:</td>
                                <td class="text-sm text-mono pb-3">{{ number_format($siklus->total_tebar ?? 0) }} Ekor</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">Spesies:</td>
                                <td class="text-sm text-mono pb-3">{{ $siklus->spesies_udang ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">Lama Persiapan:</td>
                                <td class="text-sm text-mono pb-3">{{ $siklus->lama_persiapan ?? '-' }} Hari</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">Harga Pakan:</td>
                                <td class="text-sm text-mono pb-3">{{ $siklus->harga_pakan ? 'Rp ' . number_format($siklus->harga_pakan, 0, ',', '.') : '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Parameter Air & Performa --}}
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Parameter Air & Performa</h3>
                </div>
                <div class="kt-card-content pt-3.5 pb-3.5">
                    <table class="kt-table-auto">
                        <tbody>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">Kecerahan:</td>
                                <td class="text-sm text-mono pb-3">{{ $siklus->kecerahan ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">Suhu:</td>
                                <td class="text-sm text-mono pb-3">{{ $siklus->suhu ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">DO Level:</td>
                                <td class="text-sm text-mono pb-3">{{ $siklus->do_level ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">Salinitas:</td>
                                <td class="text-sm text-mono pb-3">{{ $siklus->salinitas ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">pH Pagi:</td>
                                <td class="text-sm text-mono pb-3">{{ $siklus->ph_pagi ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">pH Sore:</td>
                                <td class="text-sm text-mono pb-3">{{ $siklus->ph_sore ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">Selisih pH:</td>
                                <td class="text-sm text-mono pb-3">{{ $siklus->selisih_ph ?? '-' }}</td>
                            </tr>
                            <tr><td colspan="2" class="pb-2"></td></tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">FCR:</td>
                                <td class="text-sm text-mono pb-3">{{ $siklus->fcr ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">ADG:</td>
                                <td class="text-sm text-mono pb-3">{{ $siklus->adg ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">SR:</td>
                                <td class="text-sm text-mono pb-3">{{ $siklus->sr ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">MBW:</td>
                                <td class="text-sm text-mono pb-3">{{ $siklus->mbw ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">Size:</td>
                                <td class="text-sm text-mono pb-3">{{ $siklus->size ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Info Kolam --}}
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Info Kolam/Blok</h3>
                </div>
                <div class="kt-card-content pt-3.5 pb-3.5">
                    <table class="kt-table-auto">
                        <tbody>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">Nama Blok:</td>
                                <td class="text-sm text-mono pb-3">{{ $siklus->blok->nama_blok ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">Didirikan:</td>
                                <td class="text-sm text-mono pb-3">{{ $siklus->blok?->didirikan_pada?->format('d/m/Y') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">Ukuran:</td>
                                <td class="text-sm text-mono pb-3">{{ $siklus->blok?->panjang ?? '-' }} x {{ $siklus->blok?->lebar ?? '-' }} x {{ $siklus->blok?->kedalaman ?? '-' }} m</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">Luas:</td>
                                <td class="text-sm text-mono pb-3">{{ number_format(($siklus->blok?->panjang ?? 0) * ($siklus->blok?->lebar ?? 0), 2) }} m²</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">Jumlah Anco:</td>
                                <td class="text-sm text-mono pb-3">{{ $siklus->blok?->jumlah_anco ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">Status Blok:</td>
                                <td class="text-sm text-mono pb-3">
                                    @if($siklus->blok?->status_blok === 'aktif')
                                        <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">Aktif</span>
                                    @elseif($siklus->blok?->status_blok === 'maintenance')
                                        <span class="kt-badge kt-badge-sm kt-badge-warning kt-badge-outline">Maintenance</span>
                                    @else
                                        <span class="kt-badge kt-badge-sm kt-badge-destructive kt-badge-outline">Nonaktif</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Info Tambak --}}
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Info Tambak</h3>
                </div>
                <div class="kt-card-content pt-3.5 pb-3.5">
                    <table class="kt-table-auto">
                        <tbody>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">Nama Tambak:</td>
                                <td class="text-sm text-mono pb-3">{{ $siklus->blok?->tambak?->nama_tambak ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">Lokasi:</td>
                                <td class="text-sm text-mono pb-3">{{ $siklus->blok?->tambak?->lokasi ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">Alamat:</td>
                                <td class="text-sm text-mono pb-3">{{ $siklus->blok?->tambak?->alamat ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">Total Lahan:</td>
                                <td class="text-sm text-mono pb-3">{{ $siklus->blok?->tambak?->total_lahan ? number_format($siklus->blok->tambak->total_lahan, 2) . ' m²' : '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-4 lg:pe-8">Didirikan:</td>
                                <td class="text-sm text-mono pb-3">{{ $siklus->blok?->tambak?->didirikan_pada?->format('d/m/Y') ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Panen -->
<div class="kt-modal" data-kt-modal="true" id="panenModal">
    <div class="kt-modal-content max-w-[600px] top-5 lg:top-[10%]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title" id="panenModalTitle">Tambah Panen</h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form id="panenForm" method="POST" action="{{ route('panen.store') }}">
            @csrf
            <input type="hidden" name="siklus_id" value="{{ $siklus->id }}">
            <input type="hidden" name="_method" id="panenFormMethod" value="POST">
            <div class="kt-modal-body flex flex-col gap-4" style="max-height:75vh;overflow-y:auto;">
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Tanggal Panen <span class="text-danger">*</span></label>
                        <div class="kt-input">
                            <i class="ki-outline ki-calendar"></i>
                            <input class="grow" name="tgl_panen" id="p_tgl_panen" data-kt-date-picker="true" data-kt-date-picker-input-mode="true" placeholder="Pilih tanggal" readonly type="text" required/>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Tipe Panen <span class="text-danger">*</span></label>
                        <select name="tipe_panen" id="p_tipe_panen" class="kt-select" required>
                            <option value="full">Full</option>
                            <option value="parsial">Parsial</option>
                            <option value="gagal">Gagal</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Umur <span class="text-danger">*</span></label>
                        <div class="kt-input-group">
                            <input class="kt-input" type="number" name="umur" id="p_umur" placeholder="0" required/>
                            <span class="kt-input-addon">Hari</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Ukuran <span class="text-danger">*</span></label>
                        <input type="number" name="ukuran" id="p_ukuran" class="kt-input" step="0.01" required>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Total Berat <span class="text-danger">*</span></label>
                        <div class="kt-input-group">
                            <input class="kt-input" type="number" name="total_berat" id="p_total_berat" step="0.01" placeholder="0" required onchange="calcPanen()"/>
                            <span class="kt-input-addon">kg</span>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Harga Jual (/kg) <span class="text-danger">*</span></label>
                        <div class="kt-input-group">
                            <span class="kt-input-addon">Rp.</span>
                            <input class="kt-input" type="number" name="harga_jual" id="p_harga_jual" step="0.01" placeholder="0" required onchange="calcPanen()"/>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Total Penjualan</label>
                        <div class="kt-input-group">
                            <span class="kt-input-addon">Rp.</span>
                            <input class="kt-input" type="number" name="total_penjualan" id="p_total_penjualan" step="0.01" readonly style="background:var(--muted);"/>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-foreground">Pembeli <span class="text-danger">*</span></label>
                    <input type="text" name="pembeli" id="p_pembeli" class="kt-input" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Jenis Pembayaran <span class="text-danger">*</span></label>
                        <select name="jenis_pembayaran" id="p_jenis_pembayaran" class="kt-select" required onchange="toggleBank()">
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1" id="bankField" style="display:none;">
                        <label class="text-sm font-medium text-foreground">Account Bank</label>
                        <select name="account_bank_id" id="p_account_bank_id" class="kt-select">
                            <option value="">-- Pilih Bank --</option>
                            @foreach($accountBanks as $bank)
                            <option value="{{ $bank->id }}">{{ $bank->nama_bank }} - {{ $bank->nama_pemilik }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Pembayaran <span class="text-danger">*</span></label>
                        <select name="pembayaran" id="p_pembayaran" class="kt-select" required onchange="toggleSisaBayar()">
                            <option value="lunas">Lunas</option>
                            <option value="piutang">Piutang</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1" id="sisaBayarField" style="display:none;">
                        <label class="text-sm font-medium text-foreground">Sisa Bayar</label>
                        <div class="kt-input-group">
                            <span class="kt-input-addon">Rp.</span>
                            <input class="kt-input" type="number" name="sisa_bayar" id="p_sisa_bayar" step="0.01" placeholder="0"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="kt-modal-footer justify-end">
                <button type="button" class="kt-btn kt-btn-outline" data-kt-modal-dismiss="true">Batal</button>
                <button type="submit" class="kt-btn kt-btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function calcPanen() {
    var berat = parseFloat(document.getElementById('p_total_berat').value) || 0;
    var harga = parseFloat(document.getElementById('p_harga_jual').value) || 0;
    document.getElementById('p_total_penjualan').value = (berat * harga).toFixed(2);
}

function toggleBank() {
    document.getElementById('bankField').style.display = document.getElementById('p_jenis_pembayaran').value === 'bank' ? '' : 'none';
}

function toggleSisaBayar() {
    document.getElementById('sisaBayarField').style.display = document.getElementById('p_pembayaran').value === 'piutang' ? '' : 'none';
}

function openPanenModal() {
    document.getElementById('panenModalTitle').textContent = 'Tambah Panen';
    document.getElementById('panenForm').action = "{{ route('panen.store') }}";
    document.getElementById('panenFormMethod').value = 'POST';
    ['p_tgl_panen','p_umur','p_ukuran','p_total_berat','p_harga_jual','p_total_penjualan','p_pembeli','p_sisa_bayar'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.value = '';
    });
    document.getElementById('p_tipe_panen').value = 'full';
    document.getElementById('p_jenis_pembayaran').value = 'cash';
    document.getElementById('p_pembayaran').value = 'lunas';
    document.getElementById('p_account_bank_id').value = '';
    toggleBank();
    toggleSisaBayar();
    KTModal.getInstance(document.querySelector('#panenModal')).show();
}
</script>
@endpush
