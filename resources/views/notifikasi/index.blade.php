@extends('layouts.app')

@section('title', 'Semua Notifikasi')
@section('page-title', 'Semua Notifikasi')
@section('page-description', $totalCount . ' notifikasi')

@section('content')
<div class="grid w-full space-y-5">
    <div class="kt-card">
        <div class="kt-card-header min-h-16">
            <form method="GET" class="flex items-center gap-2">
                <div class="kt-input" style="width:220px;">
                    <i class="ki-filled ki-magnifier text-muted-foreground"></i>
                    <input class="grow" type="text" name="search" placeholder="Cari notifikasi..." value="{{ request('search') }}"/>
                </div>
                <!-- Filter dropdown -->
                <div class="shrink-0" data-kt-dropdown="true" data-kt-dropdown-offset="0,10px" data-kt-dropdown-placement="bottom-start" data-kt-dropdown-trigger="click">
                    <button type="button" class="kt-btn kt-btn-outline kt-btn-sm" data-kt-dropdown-toggle="true">
                        <i class="ki-filled ki-filter"></i> Filter
                    </button>
                    <div class="kt-dropdown-menu w-[180px]" data-kt-dropdown-menu="true">
                        <div class="px-3 py-2 text-xs font-semibold text-muted-foreground">Status</div>
                        <ul class="kt-dropdown-menu-sub">
                            <li>
                                <button type="submit" name="status" value="" class="kt-dropdown-menu-link {{ !request('status') ? 'font-semibold' : '' }}">
                                    Semua
                                </button>
                            </li>
                            <li>
                                <button type="submit" name="status" value="baru" class="kt-dropdown-menu-link {{ request('status') === 'baru' ? 'font-semibold' : '' }}">
                                    Baru
                                </button>
                            </li>
                            <li>
                                <button type="submit" name="status" value="dibaca" class="kt-dropdown-menu-link {{ request('status') === 'dibaca' ? 'font-semibold' : '' }}">
                                    Sudah Dibaca
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </form>
            <form method="POST" action="{{ route('notifikasi.destroy-all') }}" onsubmit="return confirm('Hapus semua notifikasi?')">
                @csrf @method('DELETE')
                <button type="submit" class="kt-btn kt-btn-sm" style="color:#ef4444;border:1px solid #fca5a5;background:transparent;">
                    <i class="ki-filled ki-trash"></i> Hapus semua
                </button>
            </form>
        </div>
        <div class="kt-card-table">
            <div class="kt-table-wrapper kt-scrollable">
                <table class="kt-table">
                    <thead>
                        <tr>
                            <th class="w-12"></th>
                            <th>Tanggal</th>
                            <th>Judul</th>
                            <th>Pesan</th>
                            <th>Status</th>
                            <th class="w-16"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $notif)
                        <tr class="{{ !$notif->dibaca_pada ? 'bg-primary/5' : '' }}">
                            <td>
                                @if($notif->tipe === 'approval')
                                <i class="ki-filled ki-shield-tick" style="color:#f59e0b;"></i>
                                @elseif($notif->tipe === 'warning')
                                <i class="ki-filled ki-information-2" style="color:#ef4444;"></i>
                                @else
                                <i class="ki-filled ki-notification-on" style="color:#3b82f6;"></i>
                                @endif
                            </td>
                            <td class="text-sm text-muted-foreground whitespace-nowrap">{{ $notif->created_at->format('M d, Y H:i') }}</td>
                            <td class="text-sm font-medium text-foreground">{{ $notif->judul }}</td>
                            <td class="text-sm text-secondary-foreground">{{ $notif->pesan }}</td>
                            <td>
                                @if($notif->dibaca_pada)
                                <span class="kt-badge kt-badge-sm kt-badge-outline">Dibaca</span>
                                @else
                                <span class="kt-badge kt-badge-sm kt-badge-success">Baru</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($notif->link)
                                <a href="{{ $notif->link }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline"><i class="ki-filled ki-arrow-right"></i></a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:3rem;color:var(--muted-foreground);">Belum ada notifikasi</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($data->hasPages())
        <div class="flex items-center justify-between px-5 py-3 border-t border-border">
            <span class="text-sm text-muted-foreground">Menampilkan {{ $data->firstItem() }}-{{ $data->lastItem() }} dari {{ $data->total() }}</span>
            <div class="flex items-center gap-1">
                @if($data->onFirstPage())
                <span class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline opacity-50">←</span>
                @else
                <a href="{{ $data->previousPageUrl() }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline">←</a>
                @endif
                @foreach($data->getUrlRange(1, $data->lastPage()) as $page => $url)
                <a href="{{ $url }}" class="kt-btn kt-btn-sm kt-btn-icon {{ $page == $data->currentPage() ? 'kt-btn-primary' : 'kt-btn-outline' }}">{{ $page }}</a>
                @endforeach
                @if($data->hasMorePages())
                <a href="{{ $data->nextPageUrl() }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline">→</a>
                @else
                <span class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline opacity-50">→</span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection