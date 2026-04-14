@extends('layouts.app')

@section('title', 'Detail Pembelian Persediaan - ' . $pembelianPersediaan->nomor_transaksi)
@section('page-title', 'Detail Pembelian Persediaan')
@section('page-description', $pembelianPersediaan->nomor_transaksi)

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">{{ $pembelianPersediaan->nomor_transaksi }}</h3>
            <div class="flex items-center gap-2">
                @if(auth()->user()->hasRole('Owner') || in_array($pembelianPersediaan->status, ['awaiting_approval','pending']))
                @can('pembelian-persediaan.edit')
                <a href="{{ route('pembelian-persediaan.edit', $pembelianPersediaan) }}" class="kt-btn kt-btn-sm kt-btn-outline">
                    <i class="ki-filled ki-pencil"></i> Edit
                </a>
                @endcan
                @endif
                <a href="{{ route('pembelian-persediaan.index') }}" class="kt-btn kt-btn-sm kt-btn-outline">
                    <i class="ki-filled ki-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
        <div class="kt-card-content py-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <table class="kt-table-auto">
                    <tbody>
                        <tr>
                            <td class="text-sm text-secondary-foreground pb-3 pe-8 w-40">No. Transaksi</td>
                            <td class="text-sm text-mono pb-3 font-medium">{{ $pembelianPersediaan->nomor_transaksi }}</td>
                        </tr>
                        <tr>
                            <td class="text-sm text-secondary-foreground pb-3 pe-8">Tanggal Pembelian</td>
                            <td class="text-sm text-mono pb-3">{{ $pembelianPersediaan->tgl_pembelian?->format('d/m/Y') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-sm text-secondary-foreground pb-3 pe-8">Status</td>
                            <td class="text-sm pb-3">
                                @if($pembelianPersediaan->status === 'selesai')
                                    <span class="kt-badge kt-badge-sm kt-badge-success">Selesai</span>
                                @elseif($pembelianPersediaan->status === 'cancel')
                                    <span class="kt-badge kt-badge-sm kt-badge-destructive">Cancel</span>
                                @elseif($pembelianPersediaan->status === 'proses')
                                    <span class="kt-badge kt-badge-sm kt-badge-primary">Proses</span>
                                @elseif($pembelianPersediaan->status === 'pending')
                                    <span class="kt-badge kt-badge-sm kt-badge-warning">Pending</span>
                                @else
                                    <span class="kt-badge kt-badge-sm kt-badge-outline">Awaiting</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table class="kt-table-auto">
                    <tbody>
                        <tr>
                            <td class="text-sm text-secondary-foreground pb-3 pe-8 w-40">Jenis Pembayaran</td>
                            <td class="text-sm pb-3 capitalize">{{ $pembelianPersediaan->jenis_pembayaran }}</td>
                        </tr>
                        <tr>
                            <td class="text-sm text-secondary-foreground pb-3 pe-8">Account Bank</td>
                            <td class="text-sm pb-3">
                                @if($pembelianPersediaan->accountBank)
                                    {{ $pembelianPersediaan->accountBank->nama_bank }} - {{ $pembelianPersediaan->accountBank->nama_pemilik }}
                                @else - @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-sm text-secondary-foreground pb-3 pe-8">Catatan</td>
                            <td class="text-sm pb-3">{{ $pembelianPersediaan->catatan ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if(!empty($pembelianPersediaan->eviden))
            <div class="mt-4">
                <p class="text-sm font-medium text-foreground mb-2">Eviden</p>
                <div class="flex flex-col gap-2">
                    @foreach($pembelianPersediaan->eviden as $path)
                    <a href="{{ Storage::url($path) }}" target="_blank"
                       class="flex items-center gap-2 p-2 rounded-lg border border-border hover:bg-accent/40 text-sm kt-link">
                        <i class="ki-filled ki-file text-muted-foreground"></i>
                        {{ basename($path) }}
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Items Table --}}
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">Item Pembelian</h3>
        </div>
        <div class="kt-card-table">
            <div class="kt-table-wrapper kt-scrollable">
                <table class="kt-table">
                    <thead>
                        <tr>
                            <th class="w-12">No</th>
                            <th>Item Persediaan</th>
                            <th>Qty</th>
                            <th>Satuan</th>
                            <th>Harga Satuan</th>
                            <th>Harga Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pembelianPersediaan->items as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $item->itemPersediaan?->kode_item_persediaan }} - {{ $item->itemPersediaan?->deskripsi }}</td>
                            <td class="text-mono">{{ number_format($item->qty, 0, ',', '.') }}</td>
                            <td>{{ $item->satuan }}</td>
                            <td class="text-mono">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                            <td class="text-mono">Rp {{ number_format($item->harga_total, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-end font-semibold">Grand Total</td>
                            <td class="text-mono font-semibold text-primary">Rp {{ number_format($pembelianPersediaan->items->sum('harga_total'), 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
