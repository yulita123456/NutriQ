<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\Admin\PengaturanController;
use App\Http\Controllers\LogAktivitasController;
use App\Http\Controllers\Auth\AdminForgotPasswordController; // <-- DITAMBAHKAN

// Halaman depan
Route::redirect('/', '/login');

// Ganti grup route 'admin.password' yang lama dengan yang ini
Route::prefix('admin')->name('admin.')->group(function () {
    // Menampilkan form gabungan (email & kode)
    Route::get('forgot-password', [AdminForgotPasswordController::class, 'showForgotPasswordForm'])->name('password.request');

    // Mengirim kode lalu kembali ke halaman yang sama
    Route::post('forgot-password/send-code', [AdminForgotPasswordController::class, 'sendResetCode'])->name('password.send.code');

    // Memverifikasi kode lalu lanjut ke halaman password baru
    Route::post('forgot-password/verify-code', [AdminForgotPasswordController::class, 'verifyCode'])->name('password.verify.code');

    // Dua route ini tetap sama untuk halaman terakhir
    Route::get('reset-password', [AdminForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [AdminForgotPasswordController::class, 'resetPassword'])->name('password.update');
});


// Middleware 'auth' untuk semua dashboard/admin/user route
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [AdminDashboardController::class, 'exportExcel'])->name('dashboard.export');
    Route::get('/dashboard/rekap', [AdminDashboardController::class, 'exportRekap'])->name('dashboard.rekap');

    // Produk (Product Management)
    Route::prefix('produk')->name('produk.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'storebyAdmin'])->name('store');
        Route::get('/{product}', [ProductController::class, 'show'])->name('show');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
    });

    // User (User Management)
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    // Pembayaran (Transaksi Management) —> views/admin/transaksi/
    Route::get('/pembayaran', [TransactionController::class, 'adminLog'])->name('pembayaran.index');
    Route::get('/pembayaran/export', [TransactionController::class, 'exportExcel'])->name('pembayaran.export');
    Route::get('/pembayaran/rekap', [TransactionController::class, 'exportRekap'])->name('pembayaran.rekap');
    Route::get('/pembayaran/{transaction}', [TransactionController::class, 'adminShow'])
    ->name('pembayaran.show');

  // Pengaturan (Settings) —> views/admin/pengaturan/
    Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
    Route::get('/pengaturan/edit', [PengaturanController::class, 'edit'])->name('pengaturan.edit');
    Route::post('/pengaturan/update', [PengaturanController::class, 'update'])->name('pengaturan.update');
    // Log Aktivitas
    Route::get('/log-aktivitas', [LogAktivitasController::class, 'index'])->name('log_aktivitas');
    Route::get('/log-aktivitas/{id}', [LogAktivitasController::class, 'show'])->name('log_aktivitas.show');
});

// Produk extract text OCR (tidak perlu auth, tapi bisa juga auth)
Route::post('/produk/extract-text', [ProductController::class, 'extractTextFromImage'])->name('produk.extractText');

// Auth testing, SSL test, Midtrans finish
Route::get('/test-auth', [AuthController::class, 'login']);

// Route finish untuk Midtrans
Route::get('/finish', function () {
    return view('midtrans.finish');
});

// !!! INGAT HAPUS ROUTE INI SETELAH SELESAI DEBUG !!!
Route::get('/debug-storage', function () {
    $storagePath = storage_path('app/public');
    $output = [];

    $output['pesan'] = 'Hasil Debug Storage';
    $output['path_storage_app_public'] = $storagePath;
    $output['apakah_path_ada'] = File::exists($storagePath);

    if ($output['apakah_path_ada']) {
        // Cek pemilik folder
        $ownerId = fileowner($storagePath);
        $ownerInfo = posix_getpwuid($ownerId);
        $output['pemilik_folder'] = $ownerInfo['name'] ?? 'Tidak bisa dibaca';

        // Cek izin folder
        $output['izin_folder'] = substr(sprintf('%o', fileperms($storagePath)), -4);

        // Cek apakah folder 'foto_produk' ada
        $produkPath = $storagePath . '/foto_produk';
        $output['apakah_folder_foto_produk_ada'] = File::exists($produkPath);

         if ($output['apakah_folder_foto_produk_ada']) {
            $output['pemilik_folder_foto_produk'] = posix_getpwuid(fileowner($produkPath))['name'] ?? 'Tidak bisa dibaca';
            $output['izin_folder_foto_produk'] = substr(sprintf('%o', fileperms($produkPath)), -4);
        }
    }

    return response()->json($output, 200, [], JSON_PRETTY_PRINT);
});
require __DIR__.'/auth.php';
