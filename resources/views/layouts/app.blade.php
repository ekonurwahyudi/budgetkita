<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" dir="ltr" lang="id">
<head>
    <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ asset('assets/media/brand-logos/favicon.png') }}" rel="shortcut icon"/>
    <link href="https://fonts.googleapis.com/css2?family=Onest:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <style>:root { --font-sans: 'Onest', sans-serif; } body, * { font-family: 'Onest', sans-serif !important; }</style>
    <link href="{{ asset('assets/vendors/apexcharts/apexcharts.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/vendors/keenicons/styles.bundle.css') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet"/>
    @stack('styles')
</head>
<body class="antialiased flex h-full text-base text-foreground bg-background demo1 kt-sidebar-fixed kt-header-fixed">
    <script>
        const defaultThemeMode = 'light';
        let themeMode;
        if (document.documentElement) {
            themeMode = localStorage.getItem('kt-theme') || document.documentElement.getAttribute('data-kt-theme-mode') || defaultThemeMode;
            if (themeMode === 'system') themeMode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.classList.add(themeMode);
        }
    </script>

    <!-- Page -->
    <!-- Main -->
    <div class="flex grow">
        <!-- Sidebar -->
        @include('components.sidebar')
        <!-- End of Sidebar -->

        <!-- Wrapper -->
        <div class="kt-wrapper flex grow flex-col bg-muted/40">
            <!-- Header -->
            @include('components.header')
            <!-- End of Header -->

            <!-- Content -->
            <main class="grow pt-5 pb-20 lg:pb-0" id="content" role="content">
                <!-- Container -->
                <div class="kt-container-fixed">
                    @include('partials.flash-messages')
                    <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
                        <div class="flex flex-col justify-center gap-2">
                            <h1 class="text-xl font-medium leading-none text-mono">
                                @yield('page-title', 'Dashboard')
                            </h1>
                            <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
                                @yield('page-description', '')
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5">
                            @yield('page-actions')
                        </div>
                    </div>
                </div>
                <!-- End of Container -->

                <!-- Container -->
                <div class="kt-container-fixed">
                    @yield('content')
                </div>
                <!-- End of Container -->
            </main>
            <!-- End of Content -->

            <!-- Footer -->
            @include('components.footer')
            <!-- End of Footer -->
        </div>
        <!-- End of Wrapper -->
    </div>
    <!-- End of Main -->

    <!-- Mobile Bottom Nav -->
    @include('components.mobile-bottom-nav')
    @include('components.mobile-bottom-sheet')

    <!-- Scripts -->
    <script src="{{ asset('assets/js/core.bundle.js') }}"></script>
    <script src="{{ asset('assets/vendors/ktui/ktui.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/apexcharts/apexcharts.min.js') }}"></script>
    <script>
    // No records found center fix
    document.addEventListener('DOMContentLoaded', function() {
        new MutationObserver(function() {
            document.querySelectorAll('.kt-table tbody tr').forEach(function(tr) {
                var cells = tr.querySelectorAll('td');
                if (cells.length === 1 && cells[0].textContent.trim().match(/no records found/i)) {
                    cells[0].setAttribute('colspan', tr.closest('table').querySelectorAll('thead th').length);
                    cells[0].style.textAlign = 'center';
                    cells[0].style.padding = '3rem 1rem';
                    cells[0].style.color = 'var(--muted-foreground)';
                }
            });
        }).observe(document.body, {childList: true, subtree: true});
    });

    // Lokasi autocomplete
    var lokasiTimer;
    function searchLokasi(input) {
        var dropdown = input.parentElement.querySelector('.lokasi-dropdown');
        clearTimeout(lokasiTimer);
        if (input.value.length < 2) { dropdown.classList.add('hidden'); return; }
        lokasiTimer = setTimeout(function() {
            fetch('/api/lokasi/search?q=' + encodeURIComponent(input.value))
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (!data.length) { dropdown.classList.add('hidden'); return; }
                    dropdown.innerHTML = data.map(function(item) {
                        return '<div class="px-3 py-2 text-sm cursor-pointer hover:bg-accent/60" onclick="selectLokasi(this)">' + item.label + '</div>';
                    }).join('');
                    dropdown.classList.remove('hidden');
                });
        }, 300);
    }
    function selectLokasi(el) {
        var input = el.closest('.relative').querySelector('input');
        input.value = el.textContent;
        el.parentElement.classList.add('hidden');
    }
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.relative')) {
            document.querySelectorAll('.lokasi-dropdown').forEach(function(d) { d.classList.add('hidden'); });
        }
    });
    // Bottom sheet toggle
    function toggleBottomSheet() {
        var overlay = document.getElementById('mobileBottomSheet');
        var panel = document.getElementById('bottomSheetPanel');
        if (overlay.style.display === 'none' || !overlay.style.display) {
            overlay.style.display = 'block';
            setTimeout(function() { panel.style.transform = 'translateY(0)'; }, 10);
        } else {
            panel.style.transform = 'translateY(100%)';
            setTimeout(function() { overlay.style.display = 'none'; }, 300);
        }
    }
    </script>
    @stack('scripts')
</body>
</html>
