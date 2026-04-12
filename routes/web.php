<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────
// Redirect root → dashboard atau login
// ─────────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('dashboard'));

// ─────────────────────────────────────────────────────────────
// Semua route butuh login
// ─────────────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // ── Dashboard ──
    Route::get('/dashboard', [ReportController::class, 'dashboard'])
        ->name('dashboard');

    // ─────────────────────────────────────────────────────────
    // KASIR — bisa diakses admin & kasir
    // ─────────────────────────────────────────────────────────
    Route::middleware(['role:admin,kasir'])->group(function () {

        // Halaman transaksi
        Route::get('/kasir',              [TransactionController::class, 'index'])->name('transactions.index');
        Route::post('/kasir/bayar',       [TransactionController::class, 'store'])->name('transactions.store');
        Route::get('/kasir/struk/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');

        // Riwayat transaksi (kasir hanya lihat milik sendiri, diatur di controller)
        Route::get('/transaksi/riwayat',  [TransactionController::class, 'history'])->name('transactions.history');

        // API cari produk (dipakai AJAX di halaman kasir)
        Route::get('/api/products/search', [ProductController::class, 'search'])->name('products.search');
    });

    // ─────────────────────────────────────────────────────────
    // ADMIN ONLY
    // ─────────────────────────────────────────────────────────
    Route::middleware(['role:admin'])->group(function () {

        // ── Kategori ──
        Route::get('/kategori',              [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/kategori',             [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/kategori/{category}',   [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/kategori/{category}',[CategoryController::class, 'destroy'])->name('categories.destroy');

        // ── Produk ──
        Route::resource('produk', ProductController::class)->names([
            'index'   => 'products.index',
            'create'  => 'products.create',
            'store'   => 'products.store',
            'edit'    => 'products.edit',
            'update'  => 'products.update',
            'destroy' => 'products.destroy',
        ]);

        // ── Laporan ──
        Route::prefix('laporan')->name('reports.')->group(function () {
            Route::get('/harian',          [ReportController::class, 'daily'])->name('daily');
            Route::get('/bulanan',         [ReportController::class, 'monthly'])->name('monthly');
            Route::get('/produk-terlaris', [ReportController::class, 'bestSelling'])->name('best-selling');
        });
    });
});

// ─────────────────────────────────────────────────────────────
// Auth routes dari Breeze
// ─────────────────────────────────────────────────────────────
require __DIR__ . '/auth.php';
