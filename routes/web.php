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
use App\Http\Controllers\Budidaya\PemberianPakanController;
use App\Http\Controllers\Budidaya\PemberianKimiaController;
use App\Http\Controllers\Budidaya\KolamController;
use App\Http\Controllers\Pengaturan\RoleController;
use App\Http\Controllers\Keuangan\TransaksiKeuanganController;
use App\Http\Controllers\Keuangan\GajiKaryawanController;
use App\Http\Controllers\Keuangan\InvestasiController;
use App\Http\Controllers\Keuangan\HutangPiutangController;
use App\Http\Controllers\Api\LokasiController;
use App\Http\Controllers\Operasional\PersediaanController;
use App\Http\Controllers\Operasional\PembelianPersediaanController;
use App\Http\Controllers\Operasional\PembelianAsetController;
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
        Route::resource('account-bank', AccountBankController::class)->middleware('can:account-bank.view');
        Route::post('account-bank/transfer', [AccountBankController::class, 'transfer'])->name('account-bank.transfer')->middleware('can:account-bank.edit');
        Route::post('account-bank/{account_bank}/sync-saldo', [AccountBankController::class, 'syncSaldo'])->name('account-bank.sync-saldo')->middleware('can:account-bank.edit');
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
        Route::get('panen/create', [PanenController::class, 'create'])->name('panen.create')->middleware('can:panen.create');
        Route::resource('panen', PanenController::class)->except(['create'])->parameters(['panen' => 'panen'])->middleware('can:panen.view');
        Route::post('panen/{panen}/approve', [PanenController::class, 'approve'])->name('panen.approve');
        Route::post('panen/{panen}/reject', [PanenController::class, 'reject'])->name('panen.reject');

        Route::get('pemberian-pakan/create', [PemberianPakanController::class, 'create'])->name('pemberian-pakan.create')->middleware('can:pemberian-pakan.create');
        Route::resource('pemberian-pakan', PemberianPakanController::class)->except(['create', 'edit', 'update'])->parameters(['pemberian_pakan' => 'pemberianPakan'])->middleware('can:pemberian-pakan.view');

        Route::get('pemberian-kimia/create', [PemberianKimiaController::class, 'create'])->name('pemberian-kimia.create')->middleware('can:pemberian-pakan.create');
        Route::resource('pemberian-kimia', PemberianKimiaController::class)->except(['create', 'edit', 'update'])->parameters(['pemberian-kimia' => 'pemberianKimia'])->middleware('can:pemberian-pakan.view');

        // Kolam
        Route::post('kolam', [KolamController::class, 'store'])->name('kolam.store');
        Route::put('kolam/{kolam}', [KolamController::class, 'update'])->name('kolam.update');
        Route::delete('kolam/{kolam}', [KolamController::class, 'destroy'])->name('kolam.destroy');
        Route::get('kolam/{kolam}', [KolamController::class, 'show'])->name('kolam.show');
        Route::get('kolam/{kolam}/export-parameter', [KolamController::class, 'exportParameter'])->name('kolam.parameter.export');
        Route::post('kolam/{kolam}/import-parameter', [KolamController::class, 'importParameter'])->name('kolam.parameter.import');
        Route::post('kolam/{kolam}/parameter', [KolamController::class, 'storeParameter'])->name('kolam.parameter.store');
        Route::put('kolam-parameter/{parameter}', [KolamController::class, 'updateParameter'])->name('kolam.parameter.update');
        Route::delete('kolam-parameter/{parameter}', [KolamController::class, 'destroyParameter'])->name('kolam.parameter.destroy');
    });

    // Pengaturan
    Route::prefix('pengaturan')->middleware('can:roles.view')->group(function () {
        Route::resource('roles', RoleController::class)->except(['show', 'create']);
    });

    // Keuangan
    Route::prefix('keuangan')->group(function () {
        Route::get('transaksi/create', [\App\Http\Controllers\Keuangan\TransaksiKeuanganController::class, 'create'])->name('transaksi.create')->middleware('can:transaksi-keuangan.create');
        Route::resource('transaksi', TransaksiKeuanganController::class)->except(['create'])->parameters(['transaksi' => 'transaksi'])->middleware('can:transaksi-keuangan.view');
        Route::post('transaksi/{transaksi}/approve', [TransaksiKeuanganController::class, 'approve'])->name('transaksi.approve');
        Route::post('transaksi/{transaksi}/reject', [TransaksiKeuanganController::class, 'reject'])->name('transaksi.reject');
        Route::get('transaksi/items-by-kategori/{kategori}', [TransaksiKeuanganController::class, 'itemsByKategori'])->name('transaksi.items-by-kategori');
        Route::get('transaksi-export', [TransaksiKeuanganController::class, 'export'])->name('transaksi.export');

        Route::get('gaji/create', [GajiKaryawanController::class, 'create'])->name('gaji.create')->middleware('can:gaji-karyawan.create');
        Route::resource('gaji', GajiKaryawanController::class)->except(['create'])->parameters(['gaji' => 'gaji'])->middleware('can:gaji-karyawan.view');
        Route::post('gaji/{gaji}/approve', [GajiKaryawanController::class, 'approve'])->name('gaji.approve');
        Route::post('gaji/{gaji}/reject', [GajiKaryawanController::class, 'reject'])->name('gaji.reject');

        Route::get('investasi/create', [InvestasiController::class, 'create'])->name('investasi.create')->middleware('can:investasi.create');
        Route::resource('investasi', InvestasiController::class)->except(['create'])->parameters(['investasi' => 'investasi'])->middleware('can:investasi.view');
        Route::post('investasi/{investasi}/approve', [InvestasiController::class, 'approve'])->name('investasi.approve');
        Route::post('investasi/{investasi}/reject', [InvestasiController::class, 'reject'])->name('investasi.reject');

        Route::get('hutang-piutang/create', [HutangPiutangController::class, 'create'])->name('hutang-piutang.create')->middleware('can:hutang-piutang.create');
        Route::resource('hutang-piutang', HutangPiutangController::class)->except(['create'])->parameters(['hutang_piutang' => 'hutangPiutang'])->middleware('can:hutang-piutang.view');
        Route::post('hutang-piutang/{hutangPiutang}/approve', [HutangPiutangController::class, 'approve'])->name('hutang-piutang.approve');
        Route::post('hutang-piutang/{hutangPiutang}/reject', [HutangPiutangController::class, 'reject'])->name('hutang-piutang.reject');
        Route::patch('hutang-piutang/{hutangPiutang}/bayar', [HutangPiutangController::class, 'bayar'])->name('hutang-piutang.bayar');
    });

    // Operasional
    Route::prefix('operasional')->group(function () {
        Route::get('persediaan', [PersediaanController::class, 'index'])->name('persediaan.index')->middleware('can:persediaan.view');
        Route::get('persediaan/{persediaan}', [PersediaanController::class, 'show'])->name('persediaan.show')->middleware('can:persediaan.view');
        Route::post('persediaan/{persediaan}/adjust', [PersediaanController::class, 'adjust'])->name('persediaan.adjust')->middleware('can:persediaan.edit');

        Route::get('pembelian-persediaan/create', [PembelianPersediaanController::class, 'create'])->name('pembelian-persediaan.create')->middleware('can:pembelian-persediaan.create');
        Route::resource('pembelian-persediaan', PembelianPersediaanController::class)->except(['create'])->parameters(['pembelian-persediaan' => 'pembelianPersediaan'])->middleware('can:pembelian-persediaan.view');
        Route::post('pembelian-persediaan/{pembelianPersediaan}/approve', [PembelianPersediaanController::class, 'approve'])->name('pembelian-persediaan.approve');
        Route::post('pembelian-persediaan/{pembelianPersediaan}/reject', [PembelianPersediaanController::class, 'reject'])->name('pembelian-persediaan.reject');

        Route::get('pembelian-aset/create', [PembelianAsetController::class, 'create'])->name('pembelian-aset.create')->middleware('can:pembelian-aset.create');
        Route::resource('pembelian-aset', PembelianAsetController::class)->except(['create'])->parameters(['pembelian-aset' => 'pembelianAset'])->middleware('can:pembelian-aset.view');
        Route::post('pembelian-aset/{pembelianAset}/approve', [PembelianAsetController::class, 'approve'])->name('pembelian-aset.approve');
        Route::post('pembelian-aset/{pembelianAset}/reject', [PembelianAsetController::class, 'reject'])->name('pembelian-aset.reject');
    });
});
