<!-- Notifications Drawer -->
<div class="hidden kt-drawer kt-drawer-end card flex-col max-w-[90%] w-[420px] top-5 bottom-5 end-5 rounded-xl border border-border" data-kt-drawer="true" data-kt-drawer-container="body" id="notifications_drawer">
    <div class="flex items-center justify-between gap-2.5 text-sm text-mono font-semibold px-5 py-3 border-b border-b-border">
        Notifikasi
        <div class="flex items-center gap-2">
            @if(($unreadCount ?? 0) > 0)
            <form method="POST" action="{{ route('notifikasi.baca-semua') }}" class="inline">
                @csrf
                <button type="submit" class="text-xs text-primary hover:underline">Tandai semua dibaca</button>
            </form>
            @endif
            <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-dim shrink-0" data-kt-drawer-dismiss="true">
                <i class="ki-filled ki-cross"></i>
            </button>
        </div>
    </div>
    <div class="grow kt-scrollable-y-auto" data-kt-scrollable="true" data-kt-scrollable-max-height="auto" data-kt-scrollable-offset="150px">
        <div class="flex flex-col">
            @forelse(($notifikasis ?? []) as $notif)
            <form method="POST" action="{{ route('notifikasi.baca', $notif) }}">
                @csrf
                <button type="submit" class="w-full text-start flex gap-3 px-5 py-4 hover:bg-accent/60 transition-colors border-b border-border/50 {{ !$notif->dibaca_pada ? 'bg-primary/5' : '' }}">
                    <div class="shrink-0 mt-0.5">
                        @if($notif->tipe === 'approval')
                        <div style="width:36px;height:36px;border-radius:50%;background:rgba(245,158,11,0.1);display:flex;align-items:center;justify-content:center;">
                            <i class="ki-filled ki-shield-tick" style="color:#f59e0b;font-size:16px;"></i>
                        </div>
                        @elseif($notif->tipe === 'warning')
                        <div style="width:36px;height:36px;border-radius:50%;background:rgba(239,68,68,0.1);display:flex;align-items:center;justify-content:center;">
                            <i class="ki-filled ki-information-2" style="color:#ef4444;font-size:16px;"></i>
                        </div>
                        @else
                        <div style="width:36px;height:36px;border-radius:50%;background:rgba(59,130,246,0.1);display:flex;align-items:center;justify-content:center;">
                            <i class="ki-filled ki-notification-on" style="color:#3b82f6;font-size:16px;"></i>
                        </div>
                        @endif
                    </div>
                    <div class="flex flex-col gap-1.5 min-w-0">
                        <div class="text-sm {{ !$notif->dibaca_pada ? 'font-semibold' : 'font-medium' }} text-foreground">
                            {{ $notif->judul }}
                        </div>
                        <div class="text-xs text-secondary-foreground">{{ $notif->pesan }}</div>
                        <span class="text-xs text-muted-foreground">{{ $notif->created_at->diffForHumans() }}</span>
                    </div>
                    @if(!$notif->dibaca_pada)
                    <span style="width:8px;height:8px;border-radius:50%;background:#22c55e;margin-top:6px;" class="shrink-0"></span>
                    @endif
                </button>
            </form>
            @empty
            <div class="flex flex-col items-center justify-center py-16 gap-3">
                <i class="ki-filled ki-notification-on text-3xl text-muted-foreground"></i>
                <p class="text-sm text-muted-foreground">Belum ada notifikasi</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
