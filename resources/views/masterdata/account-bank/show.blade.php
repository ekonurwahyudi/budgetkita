@extends('layouts.app')

@section('title', 'History - ' . $account_bank->nama_bank)
@section('page-title', 'History Penggunaan Bank')
@section('page-description', $account_bank->nama_bank . ' - ' . $account_bank->nama_pemilik)

@section('content')
<div class="grid w-full space-y-5">
    {{-- Info Bank --}}
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">{{ $account_bank->nama_bank }}</h3>
            <a href="{{ route('account-bank.index') }}" class="kt-btn kt-btn-sm kt-btn-outline">
                <i class="ki-filled ki-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="kt-card-content py-4">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-muted-foreground">Kode Account</p>
                    <p class="text-sm font-medium text-mono">{{ $account_bank->kode_account }}</p>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Nama Pemilik</p>
                    <p class="text-sm font-medium">{{ $account_bank->nama_pemilik ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">No. Rekening</p>
                    <p class="text-sm font-medium text-mono">{{ $account_bank->nomor_rekening ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Saldo Saat Ini</p>
                    <p class="text-sm font-semibold text-primary">Rp {{ number_format($account_bank->saldo, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Masuk / Keluar --}}
    @php
        $totalMasuk  = $histories->where('jenis', 'masuk')->where('status', 'selesai')->sum('nominal');
        $totalKeluar = $histories->where('jenis', 'keluar')->where('status', 'selesai')->sum('nominal');
    @endphp
    <div class="grid grid-cols-2 gap-4">
        <div class="kt-card">
            <div class="kt-card-content py-4 flex items-center gap-3">
                <div class="size-10 rounded-full bg-success/10 flex items-center justify-center shrink-0">
                    <i class="ki-filled ki-arrow-down text-success text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Total Masuk (Selesai)</p>
                    <p class="text-base font-semibold text-success">Rp {{ number_format($totalMasuk, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="kt-card">
            <div class="kt-card-content py-4 flex items-center gap-3">
                <div class="size-10 rounded-full bg-destructive/10 flex items-center justify-center shrink-0">
                    <i class="ki-filled ki-arrow-up text-danger text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Total Keluar (Selesai)</p>
                    <p class="text-base font-semibold text-danger">Rp {{ number_format($totalKeluar, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel History --}}
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">History Transaksi</h3>
            <span class="text-sm text-muted-foreground">{{ $histories->count() }} transaksi</span>
        </div>
        <div class="kt-card-table">
            <div class="kt-table-wrapper kt-scrollable">
                <table class="kt-table">
                    <thead>
                        <tr>
                            <th class="w-12">No</th>
                            <th>Tanggal</th>
                            <th>Kategori</th>
                            <th>No. Referensi</th>
                            <th>Keterangan</th>
                            <th>Jenis</th>
                            <th>Nominal</th>
                            <th>Status</th>
                            <th class="w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($histories as $i => $h)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $h['tanggal'] instanceof \Carbon\Carbon ? $h['tanggal']->format('d/m/Y') : (\Carbon\Carbon::parse($h['tanggal'])->format('d/m/Y')) }}</td>
                            <td>
                                <span class="kt-badge kt-badge-sm kt-badge-outline">{{ $h['modul'] }}</span>
                            </td>
                            <td class="text-mono text-xs">{{ Str::limit($h['nomor'], 20) }}</td>
                            <td>{{ Str::limit($h['keterangan'], 40) }}</td>
                            <td>
                                @if($h['jenis'] === 'masuk')
                                    <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">
                                        <i class="ki-filled ki-arrow-down text-xs me-1"></i>Masuk
                                    </span>
                                @else
                                    <span class="kt-badge kt-badge-sm kt-badge-destructive kt-badge-outline">
                                        <i class="ki-filled ki-arrow-up text-xs me-1"></i>Keluar
                                    </span>
                                @endif
                            </td>
                            <td class="text-mono font-medium {{ $h['jenis'] === 'masuk' ? 'text-success' : 'text-danger' }}">
                                {{ $h['jenis'] === 'masuk' ? '+' : '-' }} Rp {{ number_format($h['nominal'], 0, ',', '.') }}
                            </td>
                            <td>
                                @if($h['status'] === 'selesai')
                                    <span class="kt-badge kt-badge-sm kt-badge-success">Selesai</span>
                                @elseif($h['status'] === 'cancel')
                                    <span class="kt-badge kt-badge-sm kt-badge-destructive">Cancel</span>
                                @elseif($h['status'] === 'proses')
                                    <span class="kt-badge kt-badge-sm kt-badge-primary">Proses</span>
                                @elseif($h['status'] === 'pending')
                                    <span class="kt-badge kt-badge-sm kt-badge-warning">Pending</span>
                                @else
                                    <span class="kt-badge kt-badge-sm kt-badge-outline">Awaiting</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if(!empty($h['view_url']))
                                <a href="{{ $h['view_url'] }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline" title="Lihat Detail">
                                    <i class="ki-filled ki-eye"></i>
                                </a>
                                @else
                                <span class="text-xs text-muted-foreground">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted-foreground py-6">Belum ada history transaksi untuk bank ini</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
