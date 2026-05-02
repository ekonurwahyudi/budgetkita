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

    {{-- Summary & Rekonsiliasi --}}
    @php
        $totalMasuk  = $histories->where('jenis', 'masuk')->sum('nominal');
        $totalKeluar = $histories->where('jenis', 'keluar')->sum('nominal');
        $mutasiBersih = $totalMasuk - $totalKeluar;
        $saldoAwalHitung = $account_bank->saldo - $mutasiBersih;
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
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
        <div class="kt-card">
            <div class="kt-card-content py-4 flex items-center gap-3">
                <div class="size-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                    <i class="ki-filled ki-arrows-circle text-primary text-lg"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-muted-foreground">Mutasi Bersih</p>
                    <p class="text-base font-semibold whitespace-nowrap {{ $mutasiBersih >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $mutasiBersih >= 0 ? '+' : '-' }} Rp {{ number_format(abs($mutasiBersih), 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
        <div class="kt-card">
            <div class="kt-card-content py-4 flex items-center gap-3">
                <div class="size-10 rounded-full bg-warning/10 flex items-center justify-center shrink-0">
                    <i class="ki-filled ki-wallet text-warning text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Saldo Awal (Terhitung)</p>
                    <p class="text-base font-semibold {{ $saldoAwalHitung >= 0 ? 'text-primary' : 'text-danger' }}">Rp {{ number_format($saldoAwalHitung, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="kt-card">
            <div class="kt-card-content py-4 flex items-center gap-3">
                <div class="size-10 rounded-full bg-info/10 flex items-center justify-center shrink-0">
                    <i class="ki-filled ki-calculator text-info text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Saldo Seharusnya</p>
                    <p class="text-base font-semibold {{ ($saldoSeharusnya ?? $account_bank->saldo) >= 0 ? 'text-info' : 'text-danger' }}">
                        @if($saldoSeharusnya !== null)
                            Rp {{ number_format($saldoSeharusnya, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Alert Sinkronisasi --}}
    @if($account_bank->saldo_awal === null)
    <div class="rounded-lg border border-amber-300 bg-amber-50 p-4 flex items-start gap-3">
        <i class="ki-filled ki-information-2 text-amber-600 text-lg mt-0.5"></i>
        <div class="flex-1">
            <p class="font-medium text-amber-800">Saldo Awal Belum Ditentukan</p>
            <p class="text-sm text-amber-700">Silakan edit bank ini dan isi field "Saldo Awal" dengan saldo awal saat bank pertama kali dibuat. Setelah itu Anda bisa sinkronkan saldo secara otomatis.</p>
        </div>
    </div>
    @elseif($selisih !== null && abs($selisih) > 0.01)
    <div class="rounded-lg border border-amber-300 bg-amber-50 p-4 flex items-start gap-3">
        <i class="ki-filled ki-warning text-amber-600 text-lg mt-0.5"></i>
        <div class="flex-1">
            <p class="font-medium text-amber-800">Saldo Tidak Sinkron</p>
            <p class="text-sm text-amber-700">
                Terdapat selisih <strong>Rp {{ number_format(abs($selisih), 0, ',', '.') }}</strong> 
                antara saldo tersimpan (Rp {{ number_format($account_bank->saldo, 0, ',', '.') }}) 
                dengan saldo seharusnya (Rp {{ number_format($saldoSeharusnya, 0, ',', '.') }}). 
                {{ $selisih > 0 ? 'Saldo tersimpan lebih besar.' : 'Saldo tersimpan lebih kecil.' }}
            </p>
            <form method="POST" action="{{ route('account-bank.sync-saldo', $account_bank) }}" class="mt-3" onsubmit="return confirm('Yakin sinkronkan saldo? Saldo akan diupdate otomatis berdasarkan history transaksi.')">
                @csrf
                <button type="submit" class="kt-btn kt-btn-sm kt-btn-primary">
                    <i class="ki-filled ki-arrows-circle"></i> Sinkronkan Saldo
                </button>
            </form>
        </div>
    </div>
    @else
    <div class="rounded-lg border border-emerald-300 bg-emerald-50 p-4 flex items-center gap-3">
        <i class="ki-filled ki-check-circle text-emerald-600 text-lg"></i>
        <div>
            <p class="font-medium text-emerald-800">Saldo Sinkron</p>
            <p class="text-sm text-emerald-700">Saldo tersimpan sesuai dengan perhitungan dari history transaksi.</p>
        </div>
    </div>
    @endif

    {{-- Tabel History Transaksi Bisnis --}}
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
                            <th>Saldo</th>
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
                            <td class="text-mono font-medium">
                                Rp {{ number_format($h['running_balance'], 0, ',', '.') }}
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
                            <td colspan="10" class="text-center text-muted-foreground py-6">Belum ada history transaksi untuk bank ini</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Tabel Log Penyesuaian Saldo --}}
    @if($adjustments->count() > 0)
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">Log Penyesuaian Saldo</h3>
            <span class="text-sm text-muted-foreground">{{ $adjustments->count() }} penyesuaian</span>
        </div>
        <div class="kt-card-table">
            <div class="kt-table-wrapper kt-scrollable">
                <table class="kt-table">
                    <thead>
                        <tr>
                            <th class="w-12">No</th>
                            <th>Tanggal</th>
                            <th>No. Referensi</th>
                            <th>Keterangan</th>
                            <th>Jenis</th>
                            <th>Nominal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($adjustments->sortByDesc('tanggal')->values() as $i => $a)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $a['tanggal'] instanceof \Carbon\Carbon ? $a['tanggal']->format('d/m/Y') : (\Carbon\Carbon::parse($a['tanggal'])->format('d/m/Y')) }}</td>
                            <td class="text-mono text-xs">{{ Str::limit($a['nomor'], 20) }}</td>
                            <td>{{ Str::limit($a['keterangan'], 50) }}</td>
                            <td>
                                @if($a['jenis'] === 'masuk')
                                    <span class="kt-badge kt-badge-sm kt-badge-success kt-badge-outline">
                                        <i class="ki-filled ki-arrow-down text-xs me-1"></i>Penambahan
                                    </span>
                                @else
                                    <span class="kt-badge kt-badge-sm kt-badge-destructive kt-badge-outline">
                                        <i class="ki-filled ki-arrow-up text-xs me-1"></i>Pengurangan
                                    </span>
                                @endif
                            </td>
                            <td class="text-mono font-medium {{ $a['jenis'] === 'masuk' ? 'text-success' : 'text-danger' }}">
                                Rp {{ number_format($a['nominal'], 0, ',', '.') }}
                            </td>
                            <td>
                                <span class="kt-badge kt-badge-sm kt-badge-success">Selesai</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
