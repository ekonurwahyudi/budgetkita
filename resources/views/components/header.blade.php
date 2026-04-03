<header class="kt-header fixed top-0 z-10 start-0 end-0 flex items-stretch shrink-0 bg-background" style="box-shadow:0 1px 3px rgba(0,0,0,0.08);" data-kt-sticky="true" data-kt-sticky-class="border-b border-border" data-kt-sticky-name="header" id="header">
    <div class="kt-container-fixed flex items-stretch lg:gap-4" id="headerContainer">

        <!-- Left: Mobile logo+menu OR Desktop tambak -->
        <div class="flex items-center gap-2.5 shrink-0">
            <!-- Mobile: Logo + Sidebar toggle -->
            <div class="flex gap-2 lg:hidden items-center">
                <a class="shrink-0" href="{{ route('dashboard') }}">
                    <img style="height:28px;width:auto;" src="{{ asset('assets/media/brand-logos/favicon.png') }}"/>
                </a>
                <button class="kt-btn kt-btn-icon kt-btn-ghost" data-kt-drawer-toggle="#sidebar">
                    <i class="ki-filled ki-menu"></i>
                </button>
            </div>
            <!-- Desktop: Tambak dropdown (left) -->
            @if(isset($activeTambak))
            <div class="hidden lg:flex shrink-0" data-kt-dropdown="true" data-kt-dropdown-offset="0, 10px" data-kt-dropdown-placement="bottom-start" data-kt-dropdown-trigger="click">
                <button class="kt-btn kt-btn-outline flex items-center gap-2 text-sm font-semibold" data-kt-dropdown-toggle="true">
                    <i class="ki-filled ki-geolocation text-base"></i>
                    {{ strtoupper($activeTambak->nama_tambak) }}
                    <i class="ki-filled ki-down text-xs"></i>
                </button>
                @include('components._tambak-dropdown-menu')
            </div>
            @endif
        </div>

        <!-- Center: Mobile tambak dropdown -->
        <div class="flex-1 flex items-center justify-center lg:hidden">
            @if(isset($activeTambak))
            <div class="shrink-0" data-kt-dropdown="true" data-kt-dropdown-offset="0, 10px" data-kt-dropdown-placement="bottom-start" data-kt-dropdown-trigger="click">
                <button class="kt-btn kt-btn-outline flex items-center gap-2 text-xs font-semibold" data-kt-dropdown-toggle="true">
                    <i class="ki-filled ki-geolocation text-base"></i>
                    {{ strtoupper($activeTambak->nama_tambak) }}
                    <i class="ki-filled ki-down text-xs"></i>
                </button>
                @include('components._tambak-dropdown-menu')
            </div>
            @endif
        </div>
        <!-- Desktop spacer -->
        <div class="hidden lg:flex flex-1"></div>

        <!-- Right: Notification + User -->
        <div class="flex items-center gap-2 lg:gap-3.5 shrink-0">
            <div class="hidden lg:block shrink-0" data-kt-dropdown="true" data-kt-dropdown-offset="10px, 10px" data-kt-dropdown-placement="bottom-end" data-kt-dropdown-trigger="click">
                <button class="kt-btn kt-btn-ghost kt-btn-icon relative" data-kt-dropdown-toggle="true">
                    <i class="ki-filled ki-notification-on text-base"></i>
                    @if(($unreadCount ?? 0) > 0)
                    <span style="position:absolute;top:4px;right:4px;width:8px;height:8px;border-radius:50%;background:#ef4444;"></span>
                    @endif
                </button>
                <div class="kt-dropdown-menu" style="width:380px;" data-kt-dropdown-menu="true">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-border">
                        <span class="text-sm font-semibold text-foreground">Notifikasi</span>
                        <div class="flex items-center gap-3">
                            @if(($unreadCount ?? 0) > 0)
                            <form method="POST" action="{{ route('notifikasi.baca-semua') }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs text-primary hover:underline font-medium">Read All</button>
                            </form>
                            @endif
                        </div>
                    </div>
                    <div style="max-height:320px;overflow-y:auto;">
                        @forelse(($notifikasis ?? []) as $notif)
                        <form method="POST" action="{{ route('notifikasi.baca', $notif) }}">
                            @csrf
                            <button type="submit" class="w-full text-start flex gap-3 px-4 py-3 hover:bg-accent/60 transition-colors {{ !$notif->dibaca_pada ? 'bg-primary/5' : '' }}" style="border-bottom:1px solid var(--border);">
                                <div class="shrink-0 mt-0.5">
                                    @if($notif->tipe === 'approval')
                                    <div style="width:32px;height:32px;border-radius:50%;background:rgba(245,158,11,0.1);display:flex;align-items:center;justify-content:center;">
                                        <i class="ki-filled ki-shield-tick" style="color:#f59e0b;"></i>
                                    </div>
                                    @elseif($notif->tipe === 'warning')
                                    <div style="width:32px;height:32px;border-radius:50%;background:rgba(239,68,68,0.1);display:flex;align-items:center;justify-content:center;">
                                        <i class="ki-filled ki-information-2" style="color:#ef4444;"></i>
                                    </div>
                                    @else
                                    <div style="width:32px;height:32px;border-radius:50%;background:rgba(59,130,246,0.1);display:flex;align-items:center;justify-content:center;">
                                        <i class="ki-filled ki-notification-on" style="color:#3b82f6;"></i>
                                    </div>
                                    @endif
                                </div>
                                <div class="flex flex-col gap-0.5 min-w-0 flex-1">
                                    <span class="text-sm {{ !$notif->dibaca_pada ? 'font-semibold' : 'font-medium' }} text-foreground">{{ $notif->judul }}</span>
                                    <span class="text-xs text-secondary-foreground">{{ Str::limit($notif->pesan, 60) }}</span>
                                    <span class="text-xs text-muted-foreground">{{ $notif->created_at->diffForHumans() }}</span>
                                </div>
                                @if(!$notif->dibaca_pada)
                                <span style="width:8px;height:8px;border-radius:50%;background:#22c55e;margin-top:6px;" class="shrink-0"></span>
                                @endif
                            </button>
                        </form>
                        @empty
                        <div class="flex flex-col items-center justify-center py-10 gap-2">
                            <i class="ki-filled ki-notification-on text-2xl text-muted-foreground"></i>
                            <p class="text-sm text-muted-foreground">Belum ada notifikasi</p>
                        </div>
                        @endforelse
                    </div>
                    <div class="border-t border-border px-4 py-2.5">
                        <a href="{{ route('notifikasi.index') }}" class="text-xs text-primary hover:underline font-medium flex items-center justify-center gap-1">
                            Lihat Semua Notifikasi
                            <i class="ki-filled ki-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="shrink-0" data-kt-dropdown="true" data-kt-dropdown-offset="10px, 10px" data-kt-dropdown-placement="bottom-end" data-kt-dropdown-trigger="click">
                <button class="cursor-pointer shrink-0" data-kt-dropdown-toggle="true">
                    <img src="{{ asset('assets/media/avatars/avatar.png') }}" alt="avatar" class="size-9 shrink-0" style="border-radius:20%;"/>
                </button>
                <div class="kt-dropdown-menu w-[250px]" data-kt-dropdown-menu="true">
                    <div class="flex items-center gap-2.5 px-2.5 py-1.5">
                        <img src="{{ asset('assets/media/avatars/avatar.png') }}" alt="avatar" class="size-9 shrink-0" style="border-radius:20%;"/>
                        <div class="flex flex-col gap-1">
                            <span class="text-sm text-foreground font-semibold leading-none">{{ auth()->user()->nama ?? '' }}</span>
                            <span class="text-xs text-muted-foreground leading-none">{{ auth()->user()->email ?? '' }}</span>
                            @if(auth()->user()->roles->first())
                            <span class="kt-badge kt-badge-sm kt-badge-primary kt-badge-outline mt-0.5">{{ auth()->user()->roles->first()->name }}</span>
                            @endif
                        </div>
                    </div>
                    <ul class="kt-dropdown-menu-sub">
                        <li><div class="kt-dropdown-menu-separator"></div></li>
                        <li><a class="kt-dropdown-menu-link" href="#"><i class="ki-filled ki-setting-2"></i> My Account</a></li>
                        <li><div class="kt-dropdown-menu-separator"></div></li>
                    </ul>
                    <div class="px-2.5 pt-1.5 mb-2.5 flex flex-col gap-3.5">
                        <div class="flex items-center gap-2 justify-between">
                            <span class="flex items-center gap-2">
                                <i class="ki-filled ki-moon text-base text-muted-foreground"></i>
                                <span class="font-medium text-2sm">Dark Mode</span>
                            </span>
                            <input class="kt-switch" data-kt-theme-switch-state="dark" data-kt-theme-switch-toggle="true" name="check" type="checkbox" value="1"/>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="kt-btn kt-btn-outline justify-center w-full">Log out</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>