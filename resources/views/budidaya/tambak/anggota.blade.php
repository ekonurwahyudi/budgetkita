@extends('layouts.app')

@section('title', 'Anggota Tambak - ' . $tambak->nama_tambak)
@section('page-title', 'Anggota Tambak')
@section('page-description', $tambak->nama_tambak)

@section('page-actions')
<a href="{{ url('/budidaya/tambak') }}" class="kt-btn kt-btn-outline">
    <i class="ki-filled ki-arrow-left"></i> Kembali
</a>
@endsection

@section('content')
<div class="grid gap-5 lg:gap-7.5">
    <div class="grid lg:grid-cols-2 gap-5">
        {{-- Daftar Anggota --}}
        <div class="kt-card">
            <div class="kt-card-header min-h-14">
                <span class="text-sm font-semibold text-foreground">Daftar Anggota</span>
            </div>
            <div class="kt-card-content p-0">
                <table class="kt-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Jabatan</th>
                            <th>Peran</th>
                            <th class="w-16"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tambak->anggotas as $anggota)
                        <tr>
                            <td>{{ $anggota->user->nama ?? '-' }}</td>
                            <td>{{ $anggota->user->jabatan ?? '-' }}</td>
                            <td>
                                <span class="kt-badge kt-badge-sm {{ $anggota->peran === 'owner' ? 'kt-badge-primary' : 'kt-badge-outline' }}">
                                    {{ ucfirst($anggota->peran) }}
                                </span>
                            </td>
                            <td class="text-end">
                                @if($anggota->peran !== 'owner')
                                <form method="POST" action="{{ route('tambak.anggota.destroy', [$tambak, $anggota]) }}" onsubmit="return confirm('Hapus anggota ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline text-danger">
                                        <i class="ki-filled ki-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted-foreground py-8">Belum ada anggota</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tambah Anggota --}}
        <div class="kt-card">
            <div class="kt-card-header min-h-14">
                <span class="text-sm font-semibold text-foreground">Tambah Anggota</span>
            </div>
            <div class="kt-card-content p-5">
                @if($users->isEmpty())
                <p class="text-sm text-muted-foreground">Semua user aktif sudah menjadi anggota tambak ini.</p>
                @else
                <form method="POST" action="{{ route('tambak.anggota.store', $tambak) }}" class="flex flex-col gap-4">
                    @csrf
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Pilih User <span class="text-danger">*</span></label>
                        <select name="user_id" class="kt-select" required>
                            <option value="">-- Pilih Karyawan --</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->nama }} - {{ $user->jabatan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium text-foreground">Peran <span class="text-danger">*</span></label>
                        <select name="peran" class="kt-select" required>
                            <option value="anggota">Anggota</option>
                            <option value="owner">Owner</option>
                        </select>
                    </div>
                    <button type="submit" class="kt-btn kt-btn-primary">
                        <i class="ki-filled ki-plus-squared"></i> Tambah Anggota
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
