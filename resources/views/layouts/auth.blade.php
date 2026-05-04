<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" dir="ltr" lang="id">
<head>
    <title>@yield('title', 'Login') - {{ config('app.name') }}</title>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate"/>
    <meta http-equiv="Pragma" content="no-cache"/>
    <meta http-equiv="Expires" content="0"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="{{ asset('assets/vendors/keenicons/styles.bundle.css') }}?v={{ config('app.asset_version', '1') }}" rel="stylesheet"/>
    <link href="{{ asset('assets/css/styles.css') }}?v={{ config('app.asset_version', '1') }}" rel="stylesheet"/>
</head>
<body class="antialiased flex h-full text-base text-foreground bg-background">
    <script>
        const defaultThemeMode = 'light';
        let themeMode;
        if (document.documentElement) {
            themeMode = localStorage.getItem('kt-theme') || document.documentElement.getAttribute('data-kt-theme-mode') || defaultThemeMode;
            if (themeMode === 'system') themeMode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.classList.add(themeMode);
        }
    </script>
    @yield('content')
    <script src="{{ asset('assets/js/core.bundle.js') }}?v={{ config('app.asset_version', '1') }}"></script>
    <script src="{{ asset('assets/vendors/ktui/ktui.min.js') }}?v={{ config('app.asset_version', '1') }}"></script>
    @stack('scripts')
</body>
</html>