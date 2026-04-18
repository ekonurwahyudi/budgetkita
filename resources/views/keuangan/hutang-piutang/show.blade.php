@extends('layouts.app')

@section('title', 'Detail Hutang/Piutang - ' . $hutangPiutang->nomor_transaksi)
@section('page-title', 'Detail Hutang/Piutang')
@section('page-description', $hutangPiutang->nomor_transaksi)

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">{{ $hutangPiutang->nomor_transaksi }}</h3>
            <div class="flex items-center gap-2">
                @if(auth()->user()->hasRole('Owner') || in_array($hutangPiutang->status, ['awaiting_approval','pending']))
                @can('hutang-piutang.edit')
                <a href="{{ route('hutang-piutang.edit', $hutangPiutang) }}" class="kt-btn kt-btn-sm kt-btn-outline">
                    <i class="ki-filled ki-pencil"></i> Edit
                </a>
                @endcan
                @endif
                <a href="{{ route('hutang-piutang.index') }}" class="kt-btn kt-btn-sm kt-btn-outline">
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
                                <td class="text-sm text-mono pb-3 font-medium">{{ $hutangPiutang->nomor_transaksi }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Jenis</td>
                                <td class="text-sm pb-3">
                                    @if($hutangPiutang->jenis === 'hutang')
                                        <span class="kt-badge kt-badge-sm kt-badge-destructive kt-badge-outline">Hutang</span>
                                    @else
                                        <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">Piutang</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Kategori</td>
                                <td class="text-sm pb-3">{{ $hutangPiutang->kategoriHutangPiutang?->deskripsi ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Aktivitas</td>
                                <td class="text-sm pb-3">{{ $hutangPiutang->aktivitas }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Status</td>
                                <td class="text-sm pb-3">
                                    @if($hutangPiutang->status === 'selesai')
                                        <span class="kt-badge kt-badge-sm kt-badge-success">Selesai</span>
                                    @elseif($hutangPiutang->status === 'cancel')
                                        <span class="kt-badge kt-badge-sm kt-badge-destructive">Cancel</span>
                                    @elseif($hutangPiutang->status === 'proses')
                                        <span class="kt-badge kt-badge-sm kt-badge-primary">Proses</span>
                                    @elseif($hutangPiutang->status === 'pending')
                                        <span class="kt-badge kt-badge-sm kt-badge-warning">Pending</span>
                                    @else
                                        <span class="kt-badge kt-badge-sm kt-badge-outline">Awaiting</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Jatuh Tempo</td>
                                <td class="text-sm text-mono pb-3">{{ $hutangPiutang->jatuh_tempo?->format('d/m/Y') ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div>
                    <table class="kt-table-auto">
                        <tbody>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8 w-40">Nominal</td>
                                <td class="text-sm text-mono pb-3 font-semibold">Rp {{ number_format($hutangPiutang->nominal, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Nominal Bayar</td>
                                <td class="text-sm text-mono pb-3">Rp {{ number_format($hutangPiutang->nominal_bayar ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Sisa Pembayaran</td>
                                <td class="text-sm text-mono pb-3 font-semibold text-primary">Rp {{ number_format($hutangPiutang->sisa_pembayaran, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Jenis Pembayaran</td>
                                <td class="text-sm pb-3 capitalize">{{ $hutangPiutang->jenis_pembayaran }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Account Bank</td>
                                <td class="text-sm pb-3">
                                    @if($hutangPiutang->accountBank)
                                        {{ $hutangPiutang->accountBank->nama_bank }} - {{ $hutangPiutang->accountBank->nama_pemilik }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Catatan</td>
                                <td class="text-sm pb-3">{{ $hutangPiutang->catatan ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Tanggal</td>
                                <td class="text-sm text-mono pb-3">{{ $hutangPiutang->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>

                    @if(!empty($hutangPiutang->eviden))
                    <div class="mt-4">
                        <p class="text-sm font-medium text-foreground mb-2">Eviden</p>
                        <div class="flex flex-col gap-2">
                            @foreach($hutangPiutang->eviden as $path)
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
