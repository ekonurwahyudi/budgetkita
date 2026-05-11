@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<style>
    .login-shell {
        background:
            linear-gradient(90deg, #f8fbff 0%, #f8fbff 50%, #ffffff 50%, #ffffff 100%);
    }

    .login-hero {
        background: #f2f7fb;
        border-right: 1px solid var(--border);
    }

    .login-pond-panel {
        background: #ffffff;
        border: 1px solid #dbe7f0;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
    }

    .login-water {
        background:
            linear-gradient(180deg, rgba(30, 136, 229, 0.1), rgba(20, 184, 166, 0.08)),
            repeating-linear-gradient(135deg, rgba(37, 99, 235, 0.12) 0 1px, transparent 1px 18px);
    }

    @media (max-width: 1023px) {
        .login-shell { background: #ffffff; }
        .login-hero { border-right: 0; border-bottom: 1px solid var(--border); }
    }
</style>
<div class="login-shell grow grid grid-cols-1 lg:grid-cols-2 min-h-full">
    <section class="login-hero flex items-center justify-center p-6 lg:p-12">
        <div class="w-full max-w-[560px] flex flex-col gap-8">
            <img src="{{ asset('assets/media/brand-logos/logos.png') }}" alt="BudgetKita" class="h-10 w-fit">

            <div class="flex flex-col gap-4">
                <h1 class="text-3xl lg:text-5xl font-semibold text-mono leading-tight">
                    Kelola keuangan tambak dengan lebih tenang.
                </h1>
                <p class="text-sm lg:text-base text-secondary-foreground leading-7 max-w-[460px]">
                    Pantau investasi, transaksi, hutang piutang, stok, dan saldo rekening dalam satu dashboard kerja.
                </p>
            </div>

            <div class="login-pond-panel rounded-2xl overflow-hidden">
                <div class="login-water h-44 p-5 flex items-end">
                    <div class="grid grid-cols-3 gap-3 w-full">
                        <div class="rounded-xl bg-white/90 border border-white p-3">
                            <p class="text-[10px] uppercase text-muted-foreground mb-1">Cashflow</p>
                            <p class="text-sm font-semibold text-mono">Real-time</p>
                        </div>
                        <div class="rounded-xl bg-white/90 border border-white p-3">
                            <p class="text-[10px] uppercase text-muted-foreground mb-1">Stok</p>
                            <p class="text-sm font-semibold text-mono">Terpantau</p>
                        </div>
                        <div class="rounded-xl bg-white/90 border border-white p-3">
                            <p class="text-[10px] uppercase text-muted-foreground mb-1">Rekening</p>
                            <p class="text-sm font-semibold text-mono">Tersinkron</p>
                        </div>
                    </div>
                </div>
                <div class="p-5 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center size-9 rounded-lg bg-primary/10 text-primary">
                            <i class="ki-filled ki-chart-line-up text-base"></i>
                        </span>
                        <span class="text-sm font-medium text-mono">Laporan rapi</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center size-9 rounded-lg bg-success/10 text-success">
                            <i class="ki-filled ki-shield-tick text-base"></i>
                        </span>
                        <span class="text-sm font-medium text-mono">Data aman</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center size-9 rounded-lg bg-warning/10 text-warning">
                            <i class="ki-filled ki-time text-base"></i>
                        </span>
                        <span class="text-sm font-medium text-mono">Deadline jelas</span>
                    </div>
                </div>
            </div>

            <p class="text-xs text-muted-foreground">BudgetKita &copy; {{ date('Y') }}</p>
        </div>
    </section>

    <section class="flex items-center justify-center p-6 lg:p-12">
        <form action="{{ url('/login') }}" method="POST" class="w-full max-w-[420px] flex flex-col gap-5" id="sign_in_form">
            @csrf
            <div class="flex flex-col gap-2 mb-2">
                <img src="{{ asset('assets/media/brand-logos/logos.png') }}" alt="BudgetKita" class="h-9 w-fit lg:hidden mb-4">
                <h2 class="text-2xl font-semibold text-mono leading-tight">Masuk ke BudgetKita</h2>
                <p class="text-sm text-secondary-foreground">Lanjutkan pencatatan keuangan tambak Anda.</p>
            </div>

            @include('partials.flash-messages')

            <div class="flex flex-col gap-1.5">
                <label class="kt-form-label font-medium text-mono" for="email">Email</label>
                <div class="kt-input">
                    <i class="ki-filled ki-sms text-muted-foreground"></i>
                    <input class="grow" id="email" name="email" type="email" placeholder="email@email.com" value="{{ old('email') }}" required autofocus/>
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <div class="flex items-center justify-between gap-1">
                    <label class="kt-form-label font-medium text-mono" for="password">Password</label>
                </div>
                <div class="kt-input" data-kt-toggle-password="true">
                    <i class="ki-filled ki-lock-2 text-muted-foreground"></i>
                    <input id="password" name="password" placeholder="Masukkan Password" type="password" required/>
                    <button class="kt-btn kt-btn-sm kt-btn-ghost kt-btn-icon bg-transparent! -me-1.5" data-kt-toggle-password-trigger="true" type="button">
                        <span class="kt-toggle-password-active:hidden"><i class="ki-filled ki-eye text-muted-foreground"></i></span>
                        <span class="hidden kt-toggle-password-active:block"><i class="ki-filled ki-eye-slash text-muted-foreground"></i></span>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3">
                <label class="kt-label">
                    <input class="kt-checkbox kt-checkbox-sm" name="remember" type="checkbox" value="1"/>
                    <span class="kt-checkbox-label">Ingat saya</span>
                </label>
            </div>

            @if(config('services.recaptcha.site_key'))
            <input type="hidden" name="g-recaptcha-response" id="recaptcha_token">
            @endif

            <button type="submit" class="kt-btn kt-btn-primary flex justify-center grow h-11">
                Masuk <i class="ki-filled ki-arrow-right text-xs"></i>
            </button>

            <div class="text-center text-xs text-muted-foreground pt-2">
                Sistem internal BudgetKita. Gunakan akun yang telah terdaftar.
            </div>
        </form>
    </section>
</div>
@endsection

@push('scripts')
@if(config('services.recaptcha.site_key'))
<script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
<script>
    document.getElementById('sign_in_form').addEventListener('submit', function(e) {
        e.preventDefault();
        grecaptcha.ready(function() {
            grecaptcha.execute('{{ config("services.recaptcha.site_key") }}', {action: 'login'}).then(function(token) {
                document.getElementById('recaptcha_token').value = token;
                document.getElementById('sign_in_form').submit();
            });
        });
    });
</script>
@endif
@endpush
