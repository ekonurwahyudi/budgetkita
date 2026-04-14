@extends('layouts.app')

@section('title', 'Detail Gaji - ' . $gaji->nomor_transaksi)
@section('page-title', 'Detail Gaji Karyawan')
@section('page-description', $gaji->nomor_transaksi)

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">{{ $gaji->nomor_transaksi }}</h3>
            <div class="flex items-center gap-2">
                @if(auth()->user()->hasRole('Owner') || in_array($gaji->status, ['awaiting_approval','pending']))
                @can('gaji-karyawan.edit')
                <a href="{{ route('gaji.edit', $gaji) }}" class="kt-btn kt-btn-sm kt-btn-outline">
                    <i class="ki-filled ki-pencil"></i> Edit
                </a>
                @endcan
                @endif
                <a href="{{ route('gaji.index') }}" class="kt-btn kt-btn-sm kt-btn-outline">
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
                                <td class="text-sm text-mono pb-3 font-medium">{{ $gaji->nomor_transaksi }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Karyawan</td>
                                <td class="text-sm pb-3">{{ $gaji->user?->nama ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Jabatan</td>
                                <td class="text-sm pb-3">{{ $gaji->user?->jabatan ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Status</td>
                                <td class="text-sm pb-3">
                                    @if($gaji->status === 'selesai')
                                        <span class="kt-badge kt-badge-sm kt-badge-success">Selesai</span>
                                    @elseif($gaji->status === 'cancel')
                                        <span class="kt-badge kt-badge-sm kt-badge-destructive">Cancel</span>
                                    @elseif($gaji->status === 'proses')
                                        <span class="kt-badge kt-badge-sm kt-badge-primary">Proses</span>
                                    @elseif($gaji->status === 'pending')
                                        <span class="kt-badge kt-badge-sm kt-badge-warning">Pending</span>
                                    @else
                                        <span class="kt-badge kt-badge-sm kt-badge-outline">Awaiting</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Gaji Pokok</td>
                                <td class="text-sm text-mono pb-3 font-semibold">Rp {{ number_format($gaji->gaji_pokok, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Upah Lembur</td>
                                <td class="text-sm text-mono pb-3">Rp {{ number_format($gaji->upah_lembur ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Bonus</td>
                                <td class="text-sm text-mono pb-3">Rp {{ number_format($gaji->bonus ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div>
                    <table class="kt-table-auto">
                        <tbody>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8 w-40">Pajak</td>
                                <td class="text-sm text-mono pb-3">Rp {{ number_format($gaji->pajak ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">BPJS</td>
                                <td class="text-sm text-mono pb-3">Rp {{ number_format($gaji->bpjs ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Potongan</td>
                                <td class="text-sm text-mono pb-3">Rp {{ number_format($gaji->potongan ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">THP</td>
                                <td class="text-sm text-mono pb-3 font-semibold text-primary">Rp {{ number_format($gaji->thp, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Jenis Pembayaran</td>
                                <td class="text-sm pb-3 capitalize">{{ $gaji->jenis_pembayaran }}</td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Account Bank</td>
                                <td class="text-sm pb-3">
                                    @if($gaji->accountBank)
                                        {{ $gaji->accountBank->nama_bank }} - {{ $gaji->accountBank->nama_pemilik }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-sm text-secondary-foreground pb-3 pe-8">Tanggal</td>
                                <td class="text-sm text-mono pb-3">{{ $gaji->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>

                    @if(!empty($gaji->eviden))
                    <div class="mt-4">
                        <p class="text-sm font-medium text-foreground mb-2">Eviden</p>
                        <div class="flex flex-col gap-2">
                            @foreach($gaji->eviden as $path)
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
