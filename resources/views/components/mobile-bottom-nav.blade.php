<!-- Mobile Bottom Navigation -->
<div class="lg:hidden" style="position:fixed;bottom:0;left:0;right:0;z-index:30;background:var(--background);border-top:1px solid var(--border);padding-bottom:env(safe-area-inset-bottom);">
    <div style="display:flex;align-items:flex-end;justify-content:space-around;height:64px;padding:0 8px;">
        @php
            $userRole = auth()->user()?->roles->first()?->name;
            $isFinanceOrOwner = in_array($userRole, ['Owner', 'Finance']);
            $plusUrl = $isFinanceOrOwner ? '/keuangan/transaksi' : '/budidaya/pemberian-pakan';
        @endphp

        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}" style="display:flex;flex-direction:column;align-items:center;gap:4px;padding-bottom:8px;text-decoration:none;color:{{ request()->is('dashboard*') ? 'var(--primary)' : 'var(--muted-foreground)' }};min-width:56px;">
            <i class="ki-filled ki-element-11" style="font-size:22px;"></i>
            <span style="font-size:10px;font-weight:500;">Dashboard</span>
        </a>

        {{-- Tambak --}}
        <a href="{{ url('/budidaya/blok') }}" style="display:flex;flex-direction:column;align-items:center;gap:4px;padding-bottom:8px;text-decoration:none;color:{{ request()->is('budidaya/blok*') ? 'var(--primary)' : 'var(--muted-foreground)' }};min-width:56px;">
            <i class="ki-filled ki-geolocation" style="font-size:22px;"></i>
            <span style="font-size:10px;font-weight:500;">Blok/Kolam</span>
        </a>

        {{-- Center FAB --}}
        <a href="{{ url($plusUrl) }}" style="display:flex;align-items:center;justify-content:center;position:relative;top:-20px;text-decoration:none;">
            <div style="width:56px;height:56px;border-radius:50%;background:#000;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(0,0,0,0.25);">
                <i class="ki-filled ki-plus" style="font-size:26px;color:#fff;"></i>
            </div>
        </a>

        {{-- Notifikasi --}}
        <a href="{{ url('/notifikasi')}}" style="display:flex;flex-direction:column;align-items:center;gap:4px;padding-bottom:8px;background:none;border:none;color:{{ request()->is('notifikasi*') ? 'var(--primary)' : 'var(--muted-foreground)' }};min-width:56px;">
            <i class="ki-filled ki-notification-on" style="font-size:22px;"></i>
            <span style="font-size:10px;font-weight:500;">Notifikasi</span>
        </a>

        {{-- Lainnya --}}
        <button onclick="toggleBottomSheet()" style="display:flex;flex-direction:column;align-items:center;gap:4px;padding-bottom:8px;background:none;border:none;color:var(--muted-foreground);min-width:56px;cursor:pointer;">
            <i class="ki-filled ki-burger-menu" style="font-size:22px;"></i>
            <span style="font-size:10px;font-weight:500;">Lainnya</span>
        </button>
    </div>
</div>
