@extends('layouts.app')

@section('title', 'Detail Kolam - ' . $kolam->nama_kolam)
@section('page-title', 'Detail Kolam')
@section('page-description', $kolam->nama_kolam . ' · ' . $kolam->siklus?->nama_siklus)

@section('content')
<div class="grid w-full space-y-5">

    {{-- Header Info --}}
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">{{ $kolam->nama_kolam }}</h3>
            <div class="flex items-center gap-2">
                @if($kolam->status === 'aktif')
                    <span class="kt-badge kt-badge-success">Aktif</span>
                @elseif($kolam->status === 'selesai')
                    <span class="kt-badge kt-badge-primary">Selesai</span>
                @else
                    <span class="kt-badge kt-badge-destructive">Batal</span>
                @endif
                <a href="{{ route('siklus.show', $kolam->siklus_id) }}" class="kt-btn kt-btn-sm kt-btn-outline">
                    <i class="ki-filled ki-arrow-left"></i> Kembali ke Siklus
                </a>
            </div>
        </div>
        <div class="kt-card-content py-4">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-muted-foreground">Siklus</p>
                    <p class="text-sm font-medium">{{ $kolam->siklus?->nama_siklus ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Blok</p>
                    <p class="text-sm font-medium">{{ $kolam->blok?->nama_blok ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Total Tebar</p>
                    <p class="text-sm font-medium">{{ $kolam->total_tebar ? number_format($kolam->total_tebar) . ' ekor' : '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Tgl Berdiri</p>
                    <p class="text-sm font-medium">{{ $kolam->tgl_berdiri?->format('d/m/Y') ?? '-' }}</p>
                </div>
            </div>
            @if($kolam->users->count())
            <div class="mt-3">
                <p class="text-xs text-muted-foreground mb-1">Akses User</p>
                <div class="flex flex-wrap gap-1">
                    @foreach($kolam->users as $u)
                        <span class="kt-badge kt-badge-sm kt-badge-outline">{{ $u->nama }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Tambah Parameter --}}
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">Catat Parameter Harian</h3>
        </div>
        <div class="kt-card-content py-4">
            <form method="POST" action="{{ route('kolam.parameter.store', $kolam) }}">
                @csrf
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                    <div class="flex flex-col gap-1.5 col-span-2 sm:col-span-1">
                        <label class="text-xs font-medium text-muted-foreground">Tanggal <span class="text-danger">*</span></label>
                        <div class="kt-input">
                            <i class="ki-outline ki-calendar"></i>
                            <input class="grow" name="tgl_parameter" data-kt-date-picker="true" data-kt-date-picker-input-mode="true" placeholder="Pilih tanggal" readonly type="text" required value="{{ date('Y-m-d') }}"/>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-medium text-muted-foreground">Status</label>
                        <select name="status" class="kt-select" required>
                            <option value="normal">Normal</option>
                            <option value="perhatian">Perhatian</option>
                            <option value="kritis">Kritis</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
                    @foreach([
                        ['ph_pagi','pH Pagi'],['ph_sore','pH Sore'],
                        ['do_pagi','DO Pagi'],['do_sore','DO Sore'],
                        ['suhu_pagi','Suhu Pagi'],['suhu_sore','Suhu Sore'],
                        ['kecerahan_pagi','Kecerahan Pagi'],['kecerahan_sore','Kecerahan Sore'],
                        ['salinitas','Salinitas'],['tinggi_air','Tinggi Air'],
                        ['alk','ALK'],['ca','CA'],['mg','MG'],
                        ['mbw','MBW'],['masa','MASA'],['sr','SR'],['pcr','PCR'],
                    ] as [$name, $label])
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-muted-foreground">{{ $label }}</label>
                        <input type="number" name="{{ $name }}" class="kt-input" step="0.01" placeholder="-">
                    </div>
                    @endforeach
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-muted-foreground">Warna Air</label>
                        <input type="text" name="warna_air" class="kt-input" placeholder="Hijau, Coklat...">
                    </div>
                </div>

                <div class="flex flex-col gap-1.5 mb-4">
                    <label class="text-xs font-medium text-muted-foreground">Perlakuan Harian</label>
                    <textarea name="perlakuan_harian" class="kt-input" rows="2" placeholder="Catatan perlakuan hari ini..."></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-check"></i> Simpan Parameter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- History Parameter --}}
    <div class="kt-card">
        <div class="kt-card-header min-h-14">
            <h3 class="kt-card-title">History Parameter Harian</h3>
            <span class="text-sm text-muted-foreground">{{ $kolam->parameters->count() }} catatan</span>
        </div>
        <div class="kt-card-table">
            <div class="kt-table-wrapper kt-scrollable">
                <table class="kt-table">
                    <thead>
                        <tr>
                            <th class="w-12">No</th>
                            <th>Tanggal</th>
                            <th>pH P/S</th>
                            <th>DO P/S</th>
                            <th>Suhu P/S</th>
                            <th>Kecerahan P/S</th>
                            <th>Salinitas</th>
                            <th>Tinggi Air</th>
                            <th>Warna Air</th>
                            <th>ALK</th>
                            <th>CA</th>
                            <th>MG</th>
                            <th>MBW</th>
                            <th>MASA</th>
                            <th>SR</th>
                            <th>PCR</th>
                            <th>Perlakuan</th>
                            <th>Status</th>
                            <th>Oleh</th>
                            <th class="w-12">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kolam->parameters->sortByDesc('tgl_parameter') as $i => $p)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="whitespace-nowrap">{{ $p->tgl_parameter?->format('d/m/Y') }}</td>
                            <td class="text-mono text-xs">{{ $p->ph_pagi ?? '-' }} / {{ $p->ph_sore ?? '-' }}</td>
                            <td class="text-mono text-xs">{{ $p->do_pagi ?? '-' }} / {{ $p->do_sore ?? '-' }}</td>
                            <td class="text-mono text-xs">{{ $p->suhu_pagi ?? '-' }} / {{ $p->suhu_sore ?? '-' }}</td>
                            <td class="text-mono text-xs">{{ $p->kecerahan_pagi ?? '-' }} / {{ $p->kecerahan_sore ?? '-' }}</td>
                            <td class="text-mono text-xs">{{ $p->salinitas ?? '-' }}</td>
                            <td class="text-mono text-xs">{{ $p->tinggi_air ?? '-' }}</td>
                            <td class="text-xs">{{ $p->warna_air ?? '-' }}</td>
                            <td class="text-mono text-xs">{{ $p->alk ?? '-' }}</td>
                            <td class="text-mono text-xs">{{ $p->ca ?? '-' }}</td>
                            <td class="text-mono text-xs">{{ $p->mg ?? '-' }}</td>
                            <td class="text-mono text-xs">{{ $p->mbw ?? '-' }}</td>
                            <td class="text-mono text-xs">{{ $p->masa ?? '-' }}</td>
                            <td class="text-mono text-xs">{{ $p->sr ?? '-' }}</td>
                            <td class="text-mono text-xs">{{ $p->pcr ?? '-' }}</td>
                            <td class="text-xs max-w-32">{{ Str::limit($p->perlakuan_harian, 40) ?? '-' }}</td>
                            <td>
                                @if($p->status === 'kritis')
                                    <span class="kt-badge kt-badge-sm kt-badge-destructive">Kritis</span>
                                @elseif($p->status === 'perhatian')
                                    <span class="kt-badge kt-badge-sm kt-badge-warning">Perhatian</span>
                                @else
                                    <span class="kt-badge kt-badge-sm kt-badge-success">Normal</span>
                                @endif
                            </td>
                            <td class="text-xs text-muted-foreground">{{ $p->user?->nama ?? '-' }}</td>
                            <td>
                                <form method="POST" action="{{ route('kolam.parameter.destroy', $p) }}" onsubmit="return confirm('Hapus parameter ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger" title="Hapus">
                                        <i class="ki-filled ki-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="20" class="text-center text-muted-foreground py-6">Belum ada data parameter</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
