<div class="kt-sidebar bg-background border-e border-e-border fixed top-0 bottom-0 z-20 hidden lg:flex flex-col items-stretch shrink-0 [--kt-drawer-enable:true] lg:[--kt-drawer-enable:false]" data-kt-drawer="true" data-kt-drawer-class="kt-drawer kt-drawer-start top-0 bottom-0" id="sidebar">
    <div class="kt-sidebar-header hidden lg:flex items-center relative justify-between px-3 lg:px-6 shrink-0" id="sidebar_header">
        <a href="{{ route('dashboard') }}">
            <img src="{{ asset('assets/media/brand-logos/logos.png') }}" alt="BudgetKita" style="max-width:80%; display:block; margin:0 auto;" />
        </a>
        <button class="kt-btn kt-btn-outline kt-btn-icon size-[30px] absolute start-full top-2/4 -translate-x-2/4 -translate-y-2/4" data-kt-toggle="body" data-kt-toggle-class="kt-sidebar-collapse" id="sidebar_toggle">
            <i class="ki-filled ki-black-left-line kt-toggle-active:rotate-180 transition-all duration-300"></i>
        </button>
    </div>
    <div class="kt-sidebar-content flex grow shrink-0 py-5 pe-2" id="sidebar_content">
        <div class="kt-scrollable-y-hover grow shrink-0 flex ps-2 lg:ps-5 pe-1 lg:pe-3" data-kt-scrollable="true" data-kt-scrollable-dependencies="#sidebar_header" data-kt-scrollable-height="auto" data-kt-scrollable-offset="0px" data-kt-scrollable-wrappers="#sidebar_content" id="sidebar_scrollable">
            <div class="kt-menu flex flex-col grow gap-1" data-kt-menu="true" data-kt-menu-accordion-expand-all="false" id="sidebar_menu">

                {{-- Logo for mobile sidebar --}}
                <div class="kt-menu-item lg:hidden pb-3 mb-2 border-b border-border">
                    <a href="{{ route('dashboard') }}" class="flex justify-center px-[10px] py-[6px]">
                        <img src="{{ asset('assets/media/brand-logos/logos.png') }}" alt="BudgetKita" style="max-width:70%;" />
                    </a>
                </div>

                {{-- Dashboard --}}
                @can('dashboard.view')
                <div class="kt-menu-item">
                    <a class="kt-menu-link flex items-center grow cursor-pointer border border-transparent gap-[10px] ps-[10px] pe-[10px] py-[6px] {{ request()->is('dashboard*') ? 'active' : '' }}" href="{{ route('dashboard') }}" tabindex="0">
                        <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                            <i class="ki-filled ki-technology-3 text-lg"></i>
                        </span>
                        <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-link-hover:!text-primary">Dashboard</span>
                    </a>
                </div>
                @endcan

                {{-- KEUANGAN --}}
                @php
                $keuanganMainMenus = [
                    ['url' => '/keuangan/transaksi', 'icon' => 'ki-cheque', 'label' => 'Transaksi Keuangan', 'perm' => 'transaksi-keuangan.view'],
                    ['url' => '/masterdata/account-bank', 'icon' => 'ki-two-credit-cart', 'label' => 'Account Bank', 'perm' => 'account-bank.view'],
                ];
                $transaksiLainnyaMenus = [
                    ['url' => '/keuangan/gaji', 'icon' => 'ki-people', 'label' => 'Gaji Karyawan', 'perm' => 'gaji-karyawan.view'],
                    ['url' => '/keuangan/investasi', 'icon' => 'ki-chart-line-up-2', 'label' => 'Investasi', 'perm' => 'investasi.view'],
                    ['url' => '/keuangan/hutang-piutang', 'icon' => 'ki-bill', 'label' => 'Hutang/Piutang', 'perm' => 'hutang-piutang.view'],
                ];
                $showKeuangan = collect(array_merge($keuanganMainMenus, $transaksiLainnyaMenus))->contains(fn($m) => auth()->user()?->can($m['perm']));
                $showTransaksiLainnya = collect($transaksiLainnyaMenus)->contains(fn($m) => auth()->user()?->can($m['perm']));
                $transaksiLainnyaActive = request()->is('keuangan/gaji*') || request()->is('keuangan/investasi*') || request()->is('keuangan/hutang-piutang*');
                @endphp
                @if($showKeuangan)
                <div class="kt-menu-item pt-2.25 pb-px">
                    <span class="kt-menu-heading uppercase text-xs font-medium text-muted-foreground ps-[10px] pe-[10px]">Keuangan</span>
                </div>
                @foreach($keuanganMainMenus as $menu)
                @can($menu['perm'])
                <div class="kt-menu-item">
                    <a class="kt-menu-link border border-transparent items-center grow kt-menu-item-active:bg-accent/60 hover:bg-accent/60 hover:rounded-lg gap-[10px] ps-[10px] pe-[10px] py-[6px] {{ request()->is(ltrim($menu['url'], '/').'*') ? 'active' : '' }}" href="{{ $menu['url'] }}" tabindex="0">
                        <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                            <i class="ki-filled {{ $menu['icon'] }} text-lg"></i>
                        </span>
                        <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-link-hover:!text-primary">{{ $menu['label'] }}</span>
                    </a>
                </div>
                @endcan
                @endforeach

                {{-- Transaksi Lainnya (Collapsible) --}}
                @if($showTransaksiLainnya)
                <div class="kt-menu-item {{ $transaksiLainnyaActive ? 'here show' : '' }}" data-kt-menu-item-toggle="accordion" data-kt-menu-item-trigger="click">
                    <div class="kt-menu-link flex items-center grow cursor-pointer border border-transparent gap-[10px] ps-[10px] pe-[10px] py-[6px]" tabindex="0">
                        <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                            <i class="ki-filled ki-notepad text-lg"></i>
                        </span>
                        <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-link-hover:!text-primary">Transaksi Lainnya</span>
                        <span class="kt-menu-arrow text-muted-foreground w-[20px] shrink-0 justify-end ms-1 me-[-10px]">
                            <span class="inline-flex kt-menu-item-show:hidden">
                                <i class="ki-filled ki-plus text-[11px]"></i>
                            </span>
                            <span class="hidden kt-menu-item-show:inline-flex">
                                <i class="ki-filled ki-minus text-[11px]"></i>
                            </span>
                        </span>
                    </div>
                    <div class="kt-menu-accordion gap-1 ps-[10px] relative">
                        @foreach($transaksiLainnyaMenus as $sub)
                        @can($sub['perm'])
                        <div class="kt-menu-item">
                            <a class="kt-menu-link border border-transparent items-center grow kt-menu-item-active:bg-accent/60 kt-menu-item-active:rounded-lg hover:bg-accent/60 hover:rounded-lg gap-[14px] ps-[10px] pe-[10px] py-[8px] {{ request()->is(ltrim($sub['url'], '/').'*') ? 'active' : '' }}" href="{{ $sub['url'] }}" tabindex="0">
                                <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                                    <i class="ki-filled {{ $sub['icon'] }} text-lg"></i>
                                </span>
                                <span class="kt-menu-title text-2sm font-normal text-foreground kt-menu-item-active:text-primary kt-menu-item-active:font-semibold kt-menu-link-hover:!text-primary">{{ $sub['label'] }}</span>
                            </a>
                        </div>
                        @endcan
                        @endforeach
                    </div>
                </div>
                @endif
                @endif

                {{-- OPERASIONAL --}}
                @php
                $operasionalMenus = [
                    ['url' => '/operasional/persediaan', 'icon' => 'ki-parcel', 'label' => 'Persediaan', 'perm' => 'persediaan.view'],
                    ['url' => '/operasional/pembelian-persediaan', 'icon' => 'ki-handcart', 'label' => 'Pembelian Persediaan', 'perm' => 'pembelian-persediaan.view'],
                    ['url' => '/operasional/pembelian-aset', 'icon' => 'ki-home-2', 'label' => 'Pembelian Aset', 'perm' => 'pembelian-aset.view'],
                ];
                $showOperasional = collect($operasionalMenus)->contains(fn($m) => auth()->user()?->can($m['perm']));
                @endphp
                @if($showOperasional)
                <div class="kt-menu-item pt-2.25 pb-px">
                    <span class="kt-menu-heading uppercase text-xs font-medium text-muted-foreground ps-[10px] pe-[10px]">Operasional</span>
                </div>
                @foreach($operasionalMenus as $menu)
                @can($menu['perm'])
                <div class="kt-menu-item">
                    <a class="kt-menu-link border border-transparent items-center grow kt-menu-item-active:bg-accent/60 hover:bg-accent/60 hover:rounded-lg gap-[10px] ps-[10px] pe-[10px] py-[6px] {{ request()->is(ltrim($menu['url'], '/').'*') ? 'active' : '' }}" href="{{ $menu['url'] }}" tabindex="0">
                        <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                            <i class="ki-filled {{ $menu['icon'] }} text-lg"></i>
                        </span>
                        <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-link-hover:!text-primary">{{ $menu['label'] }}</span>
                    </a>
                </div>
                @endcan
                @endforeach
                @endif

                {{-- BUDIDAYA --}}
                @php
                $budidayaMenus = [
                    ['url' => '/budidaya/blok', 'icon' => 'ki-scan-barcode', 'label' => 'Daftar Blok/Kolam', 'perm' => 'blok.view'],
                    ['url' => '/budidaya/panen', 'icon' => 'ki-basket', 'label' => 'Panen', 'perm' => 'panen.view'],
                ];
                $pakanMenus = [
                    ['url' => '/budidaya/pemberian-pakan', 'icon' => 'ki-delivery-3', 'label' => 'Pemberian Pakan', 'perm' => 'pemberian-pakan.view'],
                    ['url' => '/budidaya/pemberian-kimia', 'icon' => 'ki-flask', 'label' => 'Kimia/Antibiotik', 'perm' => 'pemberian-pakan.view'],
                ];
                $showBudidaya = collect(array_merge($budidayaMenus, $pakanMenus))->contains(fn($m) => auth()->user()?->can($m['perm']));
                $showPakan = collect($pakanMenus)->contains(fn($m) => auth()->user()?->can($m['perm']));
                $pakanActive = request()->is('budidaya/pemberian-pakan*') || request()->is('budidaya/pemberian-kimia*');
                @endphp
                @if($showBudidaya)
                <div class="kt-menu-item pt-2.25 pb-px">
                    <span class="kt-menu-heading uppercase text-xs font-medium text-muted-foreground ps-[10px] pe-[10px]">Budidaya</span>
                </div>
                @foreach($budidayaMenus as $menu)
                @can($menu['perm'])
                <div class="kt-menu-item">
                    <a class="kt-menu-link border border-transparent items-center grow kt-menu-item-active:bg-accent/60 hover:bg-accent/60 hover:rounded-lg gap-[10px] ps-[10px] pe-[10px] py-[6px] {{ request()->is(ltrim($menu['url'], '/').'*') ? 'active' : '' }}" href="{{ $menu['url'] }}" tabindex="0">
                        <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                            <i class="ki-filled {{ $menu['icon'] }} text-lg"></i>
                        </span>
                        <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-link-hover:!text-primary">{{ $menu['label'] }}</span>
                    </a>
                </div>
                @endcan
                @endforeach

                {{-- Pakan & Antibiotik (Collapsible) --}}
                @if($showPakan)
                <div class="kt-menu-item {{ $pakanActive ? 'here show' : '' }}" data-kt-menu-item-toggle="accordion" data-kt-menu-item-trigger="click">
                    <div class="kt-menu-link flex items-center grow cursor-pointer border border-transparent gap-[10px] ps-[10px] pe-[10px] py-[6px]" tabindex="0">
                        <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                            <i class="ki-filled ki-bucket text-lg"></i>
                        </span>
                        <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-link-hover:!text-primary">Pakan & Antibiotik</span>
                        <span class="kt-menu-arrow text-muted-foreground w-[20px] shrink-0 justify-end ms-1 me-[-10px]">
                            <span class="inline-flex kt-menu-item-show:hidden">
                                <i class="ki-filled ki-plus text-[11px]"></i>
                            </span>
                            <span class="hidden kt-menu-item-show:inline-flex">
                                <i class="ki-filled ki-minus text-[11px]"></i>
                            </span>
                        </span>
                    </div>
                    <div class="kt-menu-accordion gap-1 ps-[10px] relative">
                        @foreach($pakanMenus as $sub)
                        @can($sub['perm'])
                        <div class="kt-menu-item">
                            <a class="kt-menu-link border border-transparent items-center grow kt-menu-item-active:bg-accent/60 dark:menu-item-active:border-border kt-menu-item-active:rounded-lg hover:bg-accent/60 hover:rounded-lg gap-[14px] ps-[10px] pe-[10px] py-[8px] {{ request()->is(ltrim($sub['url'], '/').'*') ? 'active' : '' }}" href="{{ $sub['url'] }}" tabindex="0">
                                <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                                    <i class="ki-filled {{ $sub['icon'] }} text-lg"></i>
                                </span>
                                <span class="kt-menu-title text-2sm font-normal text-foreground kt-menu-item-active:text-primary kt-menu-item-active:font-semibold kt-menu-link-hover:!text-primary">{{ $sub['label'] }}</span>
                            </a>
                        </div>
                        @endcan
                        @endforeach
                    </div>
                </div>
                @endif
                @endif


                {{-- MASTER DATA --}}
                @php
                $masterdataMainMenus = [
                    ['url' => '/masterdata/karyawan', 'icon' => 'ki-people', 'label' => 'Data Karyawan', 'perm' => 'karyawan.view'],
                ];
                $kategoriMenus = [
                    ['url' => '/masterdata/kategori-transaksi', 'icon' => 'ki-category', 'label' => 'Kategori Transaksi', 'perm' => 'kategori-transaksi.view'],
                    ['url' => '/masterdata/item-transaksi', 'icon' => 'ki-document', 'label' => 'Item Transaksi', 'perm' => 'item-transaksi.view'],
                    ['url' => '/masterdata/sumber-dana', 'icon' => 'ki-wallet', 'label' => 'Sumber Dana', 'perm' => 'sumber-dana.view'],
                    ['url' => '/masterdata/item-persediaan', 'icon' => 'ki-package', 'label' => 'Item Persediaan', 'perm' => 'item-persediaan.view'],
                    ['url' => '/masterdata/kategori-persediaan', 'icon' => 'ki-parcel', 'label' => 'Kategori Persediaan', 'perm' => 'kategori-persediaan.view'],
                    ['url' => '/masterdata/kategori-investasi', 'icon' => 'ki-chart-line-up-2', 'label' => 'Kategori Investasi', 'perm' => 'kategori-investasi.view'],
                    ['url' => '/masterdata/kategori-aset', 'icon' => 'ki-home-2', 'label' => 'Kategori Aset', 'perm' => 'kategori-aset.view'],
                    ['url' => '/masterdata/kategori-hutang-piutang', 'icon' => 'ki-document', 'label' => 'Kategori Hutang', 'perm' => 'kategori-hutang-piutang.view'],
                ];
                $showMasterdata = collect(array_merge($masterdataMainMenus, $kategoriMenus))->contains(fn($m) => auth()->user()?->can($m['perm']));
                $showKategori = collect($kategoriMenus)->contains(fn($m) => auth()->user()?->can($m['perm']));
                $kategoriActive = request()->is('masterdata/kategori-persediaan*') || request()->is('masterdata/kategori-investasi*') || request()->is('masterdata/kategori-aset*') || request()->is('masterdata/kategori-hutang-piutang*') || request()->is('masterdata/kategori-transaksi*') || request()->is('masterdata/item-transaksi*') || request()->is('masterdata/sumber-dana*') || request()->is('masterdata/item-persediaan*');
                @endphp
                @if($showMasterdata)
                <div class="kt-menu-item pt-2.25 pb-px">
                    <span class="kt-menu-heading uppercase text-xs font-medium text-muted-foreground ps-[10px] pe-[10px]">Master Data</span>
                </div>
                @foreach($masterdataMainMenus as $menu)
                @can($menu['perm'])
                <div class="kt-menu-item">
                    <a class="kt-menu-link border border-transparent items-center grow kt-menu-item-active:bg-accent/60 dark:kt-menu-item-active:border-border kt-menu-item-active:rounded-lg hover:bg-accent/60 hover:rounded-lg gap-[10px] ps-[10px] pe-[10px] py-[6px] {{ request()->is(ltrim($menu['url'], '/').'*') ? 'active' : '' }}" href="{{ $menu['url'] }}" tabindex="0">
                        <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                            <i class="ki-filled {{ $menu['icon'] }} text-lg"></i>
                        </span>
                        <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-link-hover:!text-primary">{{ $menu['label'] }}</span>
                    </a>
                </div>
                @endcan
                @endforeach

                {{-- Kategori (Collapsible) --}}
                @if($showKategori)
                <div class="kt-menu-item {{ $kategoriActive ? 'here show' : '' }}" data-kt-menu-item-toggle="accordion" data-kt-menu-item-trigger="click">
                    <div class="kt-menu-link flex items-center grow cursor-pointer border border-transparent gap-[10px] ps-[10px] pe-[10px] py-[6px]" tabindex="0">
                        <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                            <i class="ki-filled ki-category text-lg"></i>
                        </span>
                        <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-link-hover:!text-primary">Kategori</span>
                        <span class="kt-menu-arrow text-muted-foreground w-[20px] shrink-0 justify-end ms-1 me-[-10px]">
                            <span class="inline-flex kt-menu-item-show:hidden">
                                <i class="ki-filled ki-plus text-[11px]"></i>
                            </span>
                            <span class="hidden kt-menu-item-show:inline-flex">
                                <i class="ki-filled ki-minus text-[11px]"></i>
                            </span>
                        </span>
                    </div>
                    <div class="kt-menu-accordion gap-1 ps-[10px] relative">
                        @foreach($kategoriMenus as $sub)
                        @can($sub['perm'])
                        <div class="kt-menu-item">
                            <a class="kt-menu-link border border-transparent items-center grow kt-menu-item-active:bg-accent/60 kt-menu-item-active:rounded-lg hover:bg-accent/60 hover:rounded-lg gap-[14px] ps-[10px] pe-[10px] py-[8px] {{ request()->is(ltrim($sub['url'], '/').'*') ? 'active' : '' }}" href="{{ $sub['url'] }}" tabindex="0">
                                <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                                    <i class="ki-filled {{ $sub['icon'] }} text-lg"></i>
                                </span>
                                <span class="kt-menu-title text-2sm font-normal text-foreground kt-menu-item-active:text-primary kt-menu-item-active:font-semibold kt-menu-link-hover:!text-primary">{{ $sub['label'] }}</span>
                            </a>
                        </div>
                        @endcan
                        @endforeach
                    </div>
                </div>
                @endif
                @endif
                
                {{-- PENGATURAN --}}
                @can('roles.view')
                <div class="kt-menu-item pt-2.25 pb-px">
                    <span class="kt-menu-heading uppercase text-xs font-medium text-muted-foreground ps-[10px] pe-[10px]">Pengaturan</span>
                </div>
                <div class="kt-menu-item">
                    <a class="kt-menu-link border border-transparent items-center grow kt-menu-item-active:bg-accent/60 hover:bg-accent/60 hover:rounded-lg gap-[10px] ps-[10px] pe-[10px] py-[6px] {{ request()->is('pengaturan/roles*') ? 'active' : '' }}" href="/pengaturan/roles" tabindex="0">
                        <span class="kt-menu-icon items-start text-muted-foreground w-[20px]">
                            <i class="ki-filled ki-shield-tick text-lg"></i>
                        </span>
                        <span class="kt-menu-title text-sm font-medium text-foreground kt-menu-item-active:text-primary kt-menu-link-hover:!text-primary">Role & Permission</span>
                    </a>
                </div>
                @endcan

            </div>
        </div>
    </div>
</div>