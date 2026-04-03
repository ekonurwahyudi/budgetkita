<div class="kt-dropdown-menu w-[260px]" data-kt-dropdown-menu="true">
    <div class="px-3 py-2 text-xs font-semibold text-muted-foreground">Tambak Kamu</div>
    <ul class="kt-dropdown-menu-sub">
        @foreach($headerTambaks as $t)
        <li>
            <form method="POST" action="{{ route('switch-tambak', $t) }}">
                @csrf
                <button type="submit" class="kt-dropdown-menu-link w-full text-start font-semibold">
                <i class="ki-filled ki-geolocation text-base"></i>    
                {{ strtoupper($t->nama_tambak) }}
                    @if($t->id === $activeTambak->id)
                    <i class="ki-solid ki-check ms-auto text-base"></i>
                    @endif
                </button>
            </form>
        </li>
        @endforeach
        <li><div class="kt-dropdown-menu-separator"></div></li>
        <li>
            <a class="kt-dropdown-menu-link" href="{{ url('/budidaya/tambak') }}">
                <i class="ki-filled ki-plus"></i>
                Tambah Tambak Baru
            </a>
        </li>
        <li>
            <a class="kt-dropdown-menu-link" href="{{ url('/budidaya/tambak') }}">
                <i class="ki-filled ki-setting-2"></i>
                Kelola Tambak
            </a>
        </li>
    </ul>
</div>
