<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('components.header', function ($view) {
            $user = auth()->user();
            if (!$user) return;

            $tambaks = \App\Models\TambakAnggota::with('tambak')
                ->where('user_id', $user->id)
                ->get()
                ->pluck('tambak')
                ->filter();

            $activeTambakId = session('active_tambak_id');
            $activeTambak = $tambaks->firstWhere('id', $activeTambakId) ?? $tambaks->first();

            if ($activeTambak && !$activeTambakId) {
                session(['active_tambak_id' => $activeTambak->id]);
            }

            $view->with('headerTambaks', $tambaks);
            $view->with('activeTambak', $activeTambak);

            // Notifikasi
            $notifikasis = \App\Models\Notifikasi::where('user_id', $user->id)
                ->latest()
                ->take(10)
                ->get();
            $unreadCount = \App\Models\Notifikasi::where('user_id', $user->id)->belumDibaca()->count();
            $view->with('notifikasis', $notifikasis);
            $view->with('unreadCount', $unreadCount);
        });
    }
}
