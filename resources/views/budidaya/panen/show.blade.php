@extends('layouts.app')

@section('title', 'Detail Panen')
@section('page-title', 'Detail Panen')
@section('page-description', 'Detail data panen')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">Detail Panen</h3>
            <div class="flex items-center gap-2">
                @can('panen.edit')
                @if(auth()->user()->hasRole('Owner') || in_array($panen->status, ['awaiting_approval','pending']))
                <a href="{{ route('panen.edit', $panen) }}" class="kt-btn kt-btn-sm kt-btn-outline">
                    <i class="ki-filled ki-pencil"></i> Edit
                </a>
                @endif
                @endcan
                <a href="{{ route('panen.index') }}" class="kt-btn kt-btn-sm kt-btn-outline">
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
                                <td class="text-sm text-secondary-foreground pb-3 pe-8 w-40">Siklus</td>
                                <td class="text-sm text-mono pb-3 font-medium">{{ $panen->siklus?->nama_siklus ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Tambak / Blok</td>
                                <td class="text-sm pb-3">{{ $panen->siklus?->blok?->tambak?->nama_tambak ?? '-' }} &rsaquo; {{ $panen->siklus?->blok?->nama_blok ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Tanggal Panen</td>
                                <td class="text-sm text-mono pb-3">{{ $panen->tgl_panen?->format('d/m/Y') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Tipe Panen</td>
                                <td class="text-sm pb-3">
                                    @if($panen->tipe_panen === 'parsial')
                                        <span class="kt-badge kt-badge-sm kt-badge-warning kt-badge-outline">Parsial</span>
                                    @elseif($panen->tipe_panen === 'gagal')
                                        <span class="kt-badge kt-badge-sm kt-badge-destructive kt-badge-outline">Gagal</span>
                                    @else
                                        <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">Full</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Status</td>
                                <td class="text-sm pb-3">
                                    @if($panen->status === 'selesai')
                                        <span class="kt-badge kt-badge-sm kt-badge-success">Selesai</span>
                                    @elseif($panen->status === 'cancel')
                                        <span class="kt-badge kt-badge-sm kt-badge-destructive">Cancel</span>
                                    @elseif($panen->status === 'proses')
                                        <span class="kt-badge kt-badge-sm kt-badge-primary">Proses</span>
                                    @elseif($panen->status === 'pending')
                                        <span class="kt-badge kt-badge-sm kt-badge-warning">Pending</span>
                                    @else
                                        <span class="kt-badge kt-badge-sm kt-badge-outline">Awaiting</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Umur</td>
                                <td class="text-sm text-mono pb-3">{{ $panen->umur ?? '-' }} Hari</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Ukuran</td>
                                <td class="text-sm text-mono pb-3">{{ $panen->ukuran ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div>
                    <table class="kt-table-auto">
                        <tbody>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8 w-40">Total Berat</td>
                                <td class="text-sm text-mono pb-3">{{ number_format($panen->total_berat ?? 0, 0, ',', '.') }} kg</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Harga Jual</td>
                                <td class="text-sm text-mono pb-3">Rp {{ number_format($panen->harga_jual ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Total Penjualan</td>
                                <td class="text-sm text-mono pb-3 font-semibold">Rp {{ number_format($panen->total_penjualan ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Pembeli</td>
                                <td class="text-sm pb-3">{{ $panen->pembeli ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Jenis Pembayaran</td>
                                <td class="text-sm pb-3 capitalize">{{ $panen->jenis_pembayaran ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Account Bank</td>
                                <td class="text-sm pb-3">
                                    @if($panen->accountBank)
                                        {{ $panen->accountBank->nama_bank }} - {{ $panen->accountBank->nama_pemilik }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Pembayaran</td>
                                <td class="text-sm pb-3 capitalize">{{ $panen->pembayaran ?? '-' }}</td>
                            </tr>
                            @if($panen->pembayaran === 'piutang')
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Sisa Bayar</td>
                                <td class="text-sm text-mono pb-3 text-danger">Rp {{ number_format($panen->sisa_bayar ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection