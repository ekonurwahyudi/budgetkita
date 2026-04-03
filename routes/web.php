<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Masterdata\KaryawanController;
use App\Http\Controllers\Masterdata\KategoriTransaksiController;
use App\Http\Controllers\Masterdata\ItemTransaksiController;
use App\Http\Controllers\Masterdata\SumberDanaController;
use App\Http\Controllers\Masterdata\KategoriPersediaanController;
use App\Http\Controllers\Masterdata\ItemPersediaanController;
use App\Http\Controllers\Masterdata\KategoriInvestasiController;
use App\Http\Controllers\Masterdata\KategoriHutangPiutangController;
use App\Http\Controllers\Masterdata\KategoriAsetController;
use App\Http\Controllers\Masterdata\AccountBankController;
use App\Http\Controllers\Budidaya\TambakController;
use App\Http\Controllers\Budidaya\BlokController;
use App\Http\Controllers\Budidaya\SiklusController;
use App\Http\Controllers\Budidaya\AnggotaTambakController;
use App\Http\Controllers\Budidaya\PanenController;
use App\Http\Controllers\Pengaturan\RoleController;
use App\Http\Controllers\Keuangan\TransaksiKeuanganController;
use App\Http\Controllers\Keuangan\GajiKaryawanController;
use App\Http\Controllers\Keuangan\InvestasiController;
use App\Http\Controllers\Keuangan\HutangPiutangController;
use App\Http\Controllers\Api\LokasiController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Auth routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/', fn () => redirect('/dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/lokasi/search', [LokasiController::class, 'search'])->name('api.lokasi.search');
    Route::post('/switch-tambak/{tambak}', function (\App\Models\Tambak $tambak) {
        session(['active_tambak_id' => $tambak->id]);
        return redirect()->back();
    })->name('switch-tambak');

    // Notifikasi
    Route::post('/notifikasi/{notifikasi}/baca', function (\App\Models\Notifikasi $notifikasi) {
        $notifikasi->update(['dibaca_pada' => now()]);
        return $notifikasi->link ? redirect($notifikasi->link) : redirect()->back();
    })->name('notifikasi.baca');
    Route::post('/notifikasi/baca-semua', function () {
        \App\Models\Notifikasi::where('user_id', auth()->id())->belumDibaca()->update(['dibaca_pada' => now()]);
        return redirect()->back();
    })->name('notifikasi.baca-semua');
    Route::get('/notifikasi', [\App\Http\Controllers\NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::delete('/notifikasi/hapus-semua', [\App\Http\Controllers\NotifikasiController::class, 'destroyAll'])->name('notifikasi.destroy-all');

    // Master Data
    Route::prefix('masterdata')->group(function () {
        Route::resource('karyawan', KaryawanController::class)->except(['show'])->middleware('can:karyawan.view');
        Route::resource('kategori-transaksi', KategoriTransaksiController::class)->except(['show'])->middleware('can:kategori-transaksi.view');
        Route::resource('item-transaksi', ItemTransaksiController::class)->except(['show'])->middleware('can:item-transaksi.view');
        Route::resource('sumber-dana', SumberDanaController::class)->except(['show'])->middleware('can:sumber-dana.view');
        Route::resource('kategori-persediaan', KategoriPersediaanController::class)->except(['show'])->middleware('can:kategori-persediaan.view');
        Route::resource('item-persediaan', ItemPersediaanController::class)->except(['show'])->middleware('can:item-persediaan.view');
        Route::resource('kategori-investasi', KategoriInvestasiController::class)->except(['show'])->middleware('can:kategori-investasi.view');
        Route::resource('kategori-hutang-piutang', KategoriHutangPiutangController::class)->except(['show'])->middleware('can:kategori-hutang-piutang.view');
        Route::resource('kategori-aset', KategoriAsetController::class)->except(['show'])->middleware('can:kategori-aset.view');
        Route::resource('account-bank', AccountBankController::class)->except(['show'])->middleware('can:account-bank.view');
    });

    // Budidaya
    Route::prefix('budidaya')->group(function () {
        Route::resource('tambak', TambakController::class)->except(['show'])->middleware('can:tambak.view');
        Route::get('tambak/{tambak}/anggota', [AnggotaTambakController::class, 'index'])->name('tambak.anggota.index');
        Route::post('tambak/{tambak}/anggota', [AnggotaTambakController::class, 'store'])->name('tambak.anggota.store');
        Route::delete('tambak/{tambak}/anggota/{anggota}', [AnggotaTambakController::class, 'destroy'])->name('tambak.anggota.destroy');
        Route::resource('blok', BlokController::class)->except(['show'])->middleware('can:blok.view');
        Route::get('blok/by-tambak/{tambak}', [BlokController::class, 'byTambak'])->name('blok.by-tambak');
        Route::resource('siklus', SiklusController::class)->parameters(['siklus' => 'siklus'])->middleware('can:siklus.view');
        Route::get('siklus/by-blok/{blok}', [SiklusController::class, 'byBlok'])->name('siklus.by-blok');
        Route::post('panen', [PanenController::class, 'store'])->name('panen.store')->middleware('can:panen.create');
        Route::delete('panen/{panen}', [PanenController::class, 'destroy'])->name('panen.destroy')->middleware('can:panen.delete');
    });

    // Pengaturan
    Route::prefix('pengaturan')->middleware('can:roles.view')->group(function () {
        Route::resource('roles', RoleController::class)->except(['show', 'create']);
    });

    // Keuangan
    Route::prefix('keuangan')->group(function () {
        Route::resource('transaksi', TransaksiKeuanganController::class)->except(['show', 'create'])->parameters(['transaksi' => 'transaksi'])->middleware('can:transaksi-keuangan.view');
        Route::post('transaksi/{transaksi}/approve', [TransaksiKeuanganController::class, 'approve'])->name('transaksi.approve');
        Route::post('transaksi/{transaksi}/reject', [TransaksiKeuanganController::class, 'reject'])->name('transaksi.reject');
        Route::get('transaksi/items-by-kategori/{kategori}', [TransaksiKeuanganController::class, 'itemsByKategori'])->name('transaksi.items-by-kategori');

        Route::resource('gaji', GajiKaryawanController::class)->except(['show', 'create'])->parameters(['gaji' => 'gaji'])->middleware('can:gaji-karyawan.view');
        Route::post('gaji/{gaji}/approve', [GajiKaryawanController::class, 'approve'])->name('gaji.approve');
        Route::post('gaji/{gaji}/reject', [GajiKaryawanController::class, 'reject'])->name('gaji.reject');

        Route::resource('investasi', InvestasiController::class)->except(['show', 'create'])->parameters(['investasi' => 'investasi'])->middleware('can:investasi.view');
        Route::post('investasi/{investasi}/approve', [InvestasiController::class, 'approve'])->name('investasi.approve');
        Route::post('investasi/{investasi}/reject', [InvestasiController::class, 'reject'])->name('investasi.reject');

        Route::resource('hutang-piutang', HutangPiutangController::class)->except(['show', 'create'])->parameters(['hutang_piutang' => 'hutangPiutang'])->middleware('can:hutang-piutang.view');
        Route::post('hutang-piutang/{hutangPiutang}/approve', [HutangPiutangController::class, 'approve'])->name('hutang-piutang.approve');
        Route::post('hutang-piutang/{hutangPiutang}/reject', [HutangPiutangController::class, 'reject'])->name('hutang-piutang.reject');
    });
});
