@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<style>
    .page-bg { background-image: url('{{ asset("assets/media/images/2600x1200/bg-10.png") }}'); }
    .dark .page-bg { background-image: url('{{ asset("assets/media/images/2600x1200/bg-10-dark.png") }}'); }
</style>
<div class="flex items-center justify-center grow bg-center bg-no-repeat page-bg">
    <div class="kt-card max-w-[370px] w-full">
        <form action="{{ url('/login') }}" method="POST" class="kt-card-content flex flex-col gap-5 p-10" id="sign_in_form">
            @csrf
            <div class="text-center mb-2.5">
                <h3 class="text-lg font-medium text-mono leading-none mb-2.5">Masuk ke BudgetKita</h3>
                <span class="text-sm text-secondary-foreground">Aplikasi Pencatatan Keuangan Tambak Udang</span>
            </div>

            @include('partials.flash-messages')

            <div class="flex flex-col gap-1">
                <label class="kt-form-label font-normal text-mono" for="email">Email</label>
                <input class="kt-input" id="email" name="email" type="email" placeholder="email@email.com" value="{{ old('email') }}" required autofocus/>
            </div>

            <div class="flex flex-col gap-1">
                <div class="flex items-center justify-between gap-1">
                    <label class="kt-form-label font-normal text-mono" for="password">Password</label>
                </div>
                <div class="kt-input" data-kt-toggle-password="true">
                    <input id="password" name="password" placeholder="Masukkan Password" type="password" required/>
                    <button class="kt-btn kt-btn-sm kt-btn-ghost kt-btn-icon bg-transparent! -me-1.5" data-kt-toggle-password-trigger="true" type="button">
                        <span class="kt-toggle-password-active:hidden"><i class="ki-filled ki-eye text-muted-foreground"></i></span>
                        <span class="hidden kt-toggle-password-active:block"><i class="ki-filled ki-eye-slash text-muted-foreground"></i></span>
                    </button>
                </div>
            </div>

            <label class="kt-label">
                <input class="kt-checkbox kt-checkbox-sm" name="remember" type="checkbox" value="1"/>
                <span class="kt-checkbox-label">Ingat saya</span>
            </label>

            @if(config('services.recaptcha.site_key'))
            <input type="hidden" name="g-recaptcha-response" id="recaptcha_token">
            @endif

            <button type="submit" class="kt-btn kt-btn-primary flex justify-center grow">Masuk</button>
        </form>
    </div>
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
