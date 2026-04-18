@extends('layouts.app')

@section('title', 'Detail Persediaan')
@section('page-title', 'Detail Persediaan')
@section('page-description', $persediaan->itemPersediaan?->deskripsi)

@section('content')
<div class="grid w-full space-y-5">
    {{-- Info Card --}}
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">Informasi Persediaan</h3>
            <div class="flex items-center gap-2">
                @can('persediaan.edit')
                <button type="button" class="kt-btn kt-btn-sm kt-btn-primary" onclick="KTModal.getInstance(document.querySelector('#penyesuaianModal')).show()">
                    <i class="ki-filled ki-setting-2"></i> Penyesuaian Stok
                </button>
                @endcan
                <a href="{{ route('persediaan.index') }}" class="kt-btn kt-btn-sm kt-btn-outline">
                    <i class="ki-filled ki-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
        <div class="kt-card-content py-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <table class="kt-table-auto">
                    <tbody>
                        <tr>
                            <td class="text-sm text-secondary-foreground pb-3 pe-8 w-40">Nama Produk</td>
                            <td class="text-sm pb-3 font-medium">{{ $persediaan->itemPersediaan?->kode_item_persediaan }} - {{ $persediaan->itemPersediaan?->deskripsi }}</td>
                        </tr>
                        <tr>
                            <td class="text-sm text-secondary-foreground pb-3 pe-8">Kategori</td>
                            <td class="text-sm pb-3">{{ $persediaan->itemPersediaan?->kategoriPersediaan?->deskripsi ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-sm text-secondary-foreground pb-3 pe-8">Qty</td>
                            <td class="text-sm text-mono pb-3 font-semibold">{{ number_format($persediaan->qty, 2, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
                <table class="kt-table-auto">
                    <tbody>
                        <tr>
                            <td class="text-sm text-secondary-foreground pb-3 pe-8 w-40">Unit</td>
                            <td class="text-sm pb-3">{{ $persediaan->unit ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-sm text-secondary-foreground pb-3 pe-8">Harga/Unit</td>
                            <td class="text-sm text-mono pb-3">Rp {{ number_format($persediaan->harga_per_unit, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-sm text-secondary-foreground pb-3 pe-8">Total Harga</td>
                            <td class="text-sm text-mono pb-3 font-semibold text-primary">Rp {{ number_format($persediaan->total_harga, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Riwayat Persediaan --}}
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">Riwayat Persediaan</h3>
        </div>
        <div class="kt-card-table">
            <div class="kt-table-wrapper kt-scrollable">
                <table class="kt-table">
                    <thead>
                        <tr>
                            <th class="w-12">No</th>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Qty Masuk</th>
                            <th>Qty Keluar</th>
                            <th>Blok/Kolam</th>
                            <th>Siklus</th>
                            <th>Harga/Unit</th>
                            <th>Harga Total</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($persediaan->riwayats as $i => $riwayat)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="text-mono">{{ $riwayat->created_at?->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($riwayat->jenis === 'penambahan')
                                    <span class="kt-badge kt-badge-sm kt-badge-success">Penambahan</span>
                                @else
                                    <span class="kt-badge kt-badge-sm kt-badge-destructive">Pengeluaran</span>
                                @endif
                            </td>
                            <td class="text-mono">{{ $riwayat->qty_masuk ? number_format($riwayat->qty_masuk, 2, ',', '.') : '-' }}</td>
                            <td class="text-mono">{{ $riwayat->qty_keluar ? number_format($riwayat->qty_keluar, 2, ',', '.') : '-' }}</td>
                            <td>{{ $riwayat->blok?->nama_blok ?? '-' }}</td>
                            <td>{{ $riwayat->siklus?->nama_siklus ?? '-' }}</td>
                            <td class="text-mono">{{ $riwayat->harga_per_unit ? 'Rp '.number_format($riwayat->harga_per_unit, 0, ',', '.') : '-' }}</td>
                            <td class="text-mono">{{ $riwayat->harga_total ? 'Rp '.number_format($riwayat->harga_total, 0, ',', '.') : '-' }}</td>
                            <td>{{ $riwayat->catatan ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="10" class="text-center text-muted-foreground py-4">Belum ada riwayat</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Penyesuaian --}}
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">Penyesuaian Stok</h3>
        </div>
        <div class="kt-card-table">
            <div class="kt-table-wrapper kt-scrollable">
                <table class="kt-table">
                    <thead>
                        <tr>
                            <th class="w-12">No</th>
                            <th>Tanggal</th>
                            <th>Qty Sistem</th>
                            <th>Qty Fisik</th>
                            <th>Selisih</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($penyesuaians as $i => $p)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="text-mono">{{ $p->tgl_penyesuaian?->format('d/m/Y') }}</td>
                            <td class="text-mono">{{ number_format($p->qty_sistem, 2, ',', '.') }}</td>
                            <td class="text-mono">{{ number_format($p->qty_fisik, 2, ',', '.') }}</td>
                            <td class="text-mono">
                                @php $selisih = $p->qty_fisik - $p->qty_sistem; @endphp
                                <span class="{{ $selisih >= 0 ? 'text-success' : 'text-danger' }}">{{ $selisih >= 0 ? '+' : '' }}{{ number_format($selisih, 2, ',', '.') }}</span>
                            </td>
                            <td>{{ $p->catatan ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted-foreground py-4">Belum ada penyesuaian</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Penyesuaian --}}
<div class="kt-modal" data-kt-modal="true" id="penyesuaianModal">
    <div class="kt-modal-content max-w-[420px] top-5 lg:top-[20%]">
        <div class="kt-modal-header">
            <h3 class="kt-modal-title">Penyesuaian Stok</h3>
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost" data-kt-modal-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('persediaan.adjust', $persediaan) }}">
            @csrf
            <div class="kt-modal-body flex flex-col gap-4">
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-foreground">Qty Sistem</label>
                    <input type="text" class="kt-input" value="{{ number_format($persediaan->qty, 2, ',', '.') }}" readonly style="background:var(--muted);" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-foreground">Qty Fisik <span class="text-danger">*</span></label>
                    <input type="number" name="qty_fisik" class="kt-input" min="0" step="1" required placeholder="Masukkan qty fisik" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-foreground">Catatan <span class="text-danger">*</span></label>
                    <textarea name="catatan" class="kt-input" rows="3" required placeholder="Alasan penyesuaian..."></textarea>
                </div>
            </div>
            <div class="kt-modal-footer justify-end">
                <button type="button" class="kt-btn kt-btn-outline" data-kt-modal-dismiss="true">Batal</button>
                <button type="submit" class="kt-btn kt-btn-primary">
                    <i class="ki-filled ki-check"></i> Simpan Penyesuaian
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
