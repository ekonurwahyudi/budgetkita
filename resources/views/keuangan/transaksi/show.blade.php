@extends('layouts.app')

@section('title', 'Detail Transaksi - ' . $transaksi->nomor_transaksi)
@section('page-title', 'Detail Transaksi')
@section('page-description', $transaksi->nomor_transaksi)

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">{{ $transaksi->nomor_transaksi }}</h3>
            <div class="flex items-center gap-2">
                @if(auth()->user()->hasRole('Owner') || in_array($transaksi->status, ['awaiting_approval','pending']))
                @can('transaksi-keuangan.edit')
                <a href="{{ route('transaksi.edit', $transaksi) }}" class="kt-btn kt-btn-sm kt-btn-outline">
                    <i class="ki-filled ki-pencil"></i> Edit
                </a>
                @endcan
                @endif
                <a href="{{ route('transaksi.index') }}" class="kt-btn kt-btn-sm kt-btn-outline">
                    <i class="ki-filled ki-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
        <div class="kt-card-content py-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <table class="kt-table-auto">
                        <tbody>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8 w-40">No. Transaksi</td>
                                <td class="text-sm text-mono pb-3 font-medium">{{ $transaksi->nomor_transaksi }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Jenis</td>
                                <td class="text-sm pb-3">
                                    @if($transaksi->jenis_transaksi === 'uang_masuk')
                                        <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">Uang Masuk</span>
                                    @elseif($transaksi->jenis_transaksi === 'uang_keluar')
                                        <span class="kt-badge kt-badge-sm kt-badge-destructive kt-badge-outline">Uang Keluar</span>
                                    @else
                                        <span class="kt-badge kt-badge-sm kt-badge-warning kt-badge-outline">Cash Card</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Status</td>
                                <td class="text-sm pb-3">
                                    @if($transaksi->status === 'selesai')
                                        <span class="kt-badge kt-badge-sm kt-badge-success">Selesai</span>
                                    @elseif($transaksi->status === 'cancel')
                                        <span class="kt-badge kt-badge-sm kt-badge-destructive">Cancel</span>
                                    @elseif($transaksi->status === 'proses')
                                        <span class="kt-badge kt-badge-sm kt-badge-primary">Proses</span>
                                    @elseif($transaksi->status === 'pending')
                                        <span class="kt-badge kt-badge-sm kt-badge-warning">Pending</span>
                                    @else
                                        <span class="kt-badge kt-badge-sm kt-badge-outline">Awaiting</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Tanggal Kwitansi</td>
                                <td class="text-sm text-mono pb-3">{{ $transaksi->tgl_kwitansi?->format('d/m/Y') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Aktivitas</td>
                                <td class="text-sm pb-3">{{ $transaksi->aktivitas }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Nominal</td>
                                <td class="text-sm text-mono pb-3 font-semibold">Rp {{ number_format($transaksi->nominal, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Kategori</td>
                                <td class="text-sm pb-3">{{ $transaksi->kategoriTransaksi?->deskripsi ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Item Transaksi</td>
                                <td class="text-sm pb-3">{{ $transaksi->itemTransaksi?->kode_item ?? '-' }}{{ $transaksi->itemTransaksi?->deskripsi ? ' - '.$transaksi->itemTransaksi->deskripsi : '' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div>
                    <table class="kt-table-auto">
                        <tbody>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8 w-40">Tambak</td>
                                <td class="text-sm pb-3">{{ $transaksi->tambak?->nama_tambak ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Blok</td>
                                <td class="text-sm pb-3">{{ $transaksi->blok?->nama_blok ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Siklus</td>
                                <td class="text-sm pb-3">{{ $transaksi->siklus?->nama_siklus ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Sumber Dana</td>
                                <td class="text-sm pb-3">{{ $transaksi->sumberDana?->deskripsi ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Jenis Pembayaran</td>
                                <td class="text-sm pb-3 capitalize">{{ $transaksi->jenis_pembayaran }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Account Bank</td>
                                <td class="text-sm pb-3">
                                    @if($transaksi->accountBank)
                                        {{ $transaksi->accountBank->nama_bank }} - {{ $transaksi->accountBank->nama_pemilik }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Catatan</td>
                                <td class="text-sm pb-3">{{ $transaksi->catatan ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>

                    @if(!empty($transaksi->eviden))
                    <div class="mt-4">
                        <p class="text-sm font-medium text-foreground mb-2">Eviden</p>
                        <div class="flex flex-col gap-2">
                            @foreach($transaksi->eviden as $path)
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
        </div>
    </div>
</div>
@endsection
