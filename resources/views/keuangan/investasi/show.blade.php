@extends('layouts.app')

@section('title', 'Detail Investasi - ' . $investasi->nomor_transaksi)
@section('page-title', 'Detail Investasi')
@section('page-description', $investasi->nomor_transaksi)

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">{{ $investasi->nomor_transaksi }}</h3>
            <div class="flex items-center gap-2">
                @if(auth()->user()->hasRole('Owner') || in_array($investasi->status, ['awaiting_approval','pending']))
                @can('investasi.edit')
                <a href="{{ route('investasi.edit', $investasi) }}" class="kt-btn kt-btn-sm kt-btn-outline">
                    <i class="ki-filled ki-pencil"></i> Edit
                </a>
                @endcan
                @endif
                <a href="{{ route('investasi.index') }}" class="kt-btn kt-btn-sm kt-btn-outline">
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
                                <td class="text-sm text-mono pb-3 font-medium">{{ $investasi->nomor_transaksi }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Deskripsi</td>
                                <td class="text-sm pb-3">{{ $investasi->deskripsi }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Kategori</td>
                                <td class="text-sm pb-3">{{ $investasi->kategoriInvestasi?->deskripsi ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Nominal</td>
                                <td class="text-sm text-mono pb-3 font-semibold">Rp {{ number_format($investasi->nominal, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Status</td>
                                <td class="text-sm pb-3">
                                    @if($investasi->status === 'selesai')
                                        <span class="kt-badge kt-badge-sm kt-badge-success">Selesai</span>
                                    @elseif($investasi->status === 'cancel')
                                        <span class="kt-badge kt-badge-sm kt-badge-destructive">Cancel</span>
                                    @elseif($investasi->status === 'proses')
                                        <span class="kt-badge kt-badge-sm kt-badge-primary">Proses</span>
                                    @elseif($investasi->status === 'pending')
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
                                <td class="text-sm text-secondary-foreground pb-3 pe-8 w-40">Jenis Pembayaran</td>
                                <td class="text-sm pb-3 capitalize">{{ $investasi->jenis_pembayaran }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Account Bank</td>
                                <td class="text-sm pb-3">
                                    @if($investasi->accountBank)
                                        {{ $investasi->accountBank->nama_bank }} - {{ $investasi->accountBank->nama_pemilik }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Catatan</td>
                                <td class="text-sm pb-3">{{ $investasi->catatan ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Tanggal</td>
                                <td class="text-sm text-mono pb-3">{{ $investasi->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>

                    @if(!empty($investasi->eviden))
                    <div class="mt-4">
                        <p class="text-sm font-medium text-foreground mb-2">Eviden</p>
                        <div class="flex flex-col gap-2">
                            @foreach($investasi->eviden as $path)
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
