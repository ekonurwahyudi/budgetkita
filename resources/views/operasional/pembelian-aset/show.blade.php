@extends('layouts.app')

@section('title', 'Detail Pembelian Aset - ' . $pembelianAset->nama_aset)
@section('page-title', 'Detail Pembelian Aset')
@section('page-description', $pembelianAset->nama_aset)

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">{{ $pembelianAset->nama_aset }}</h3>
            <div class="flex items-center gap-2">
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
        <div class="kt-card-content py-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <table class="kt-table-auto">
                        <tbody>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8 w-44">Nama Aset</td>
                                <td class="text-sm pb-3 font-medium">{{ $pembelianAset->nama_aset }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Kategori</td>
                                <td class="text-sm pb-3">{{ $pembelianAset->kategoriAset?->deskripsi ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Tanggal Pembelian</td>
                                <td class="text-sm text-mono pb-3">{{ $pembelianAset->tgl_pembelian?->format('d/m/Y') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Nominal Pembelian</td>
                                <td class="text-sm text-mono pb-3 font-semibold">Rp {{ number_format($pembelianAset->nominal_pembelian, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Umur Manfaat</td>
                                <td class="text-sm pb-3">{{ $pembelianAset->umur_manfaat }} Tahun</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Nilai Residu</td>
                                <td class="text-sm text-mono pb-3">Rp {{ number_format($pembelianAset->nilai_residu, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Status</td>
                                <td class="text-sm pb-3">
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
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div>
                    <table class="kt-table-auto">
                        <tbody>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8 w-44">Depresiasi/Tahun</td>
                                <td class="text-sm text-mono pb-3">Rp {{ number_format($pembelianAset->depresiasi_per_tahun, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Depresiasi/Bulan</td>
                                <td class="text-sm text-mono pb-3">Rp {{ number_format($pembelianAset->depresiasi_per_bulan, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Umur Berjalan</td>
                                <td class="text-sm pb-3">{{ $pembelianAset->umur_berjalan }} Tahun</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Akumulasi Depresiasi</td>
                                <td class="text-sm text-mono pb-3">Rp {{ number_format($pembelianAset->akumulasi_depresiasi, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Nilai Buku</td>
                                <td class="text-sm text-mono pb-3 font-semibold text-primary">Rp {{ number_format($pembelianAset->nilai_buku_aset, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Jenis Pembayaran</td>
                                <td class="text-sm pb-3 capitalize">{{ $pembelianAset->jenis_pembayaran }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Account Bank</td>
                                <td class="text-sm pb-3">
                                    @if($pembelianAset->accountBank)
                                        {{ $pembelianAset->accountBank->nama_bank }} - {{ $pembelianAset->accountBank->nama_pemilik }}
                                    @else - @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Catatan</td>
                                <td class="text-sm pb-3">{{ $pembelianAset->catatan ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
