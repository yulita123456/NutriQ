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
use App\Http\Controllers\Auth\AdminForgotPasswordController;

// ====================================================================
// KODE BARU DITAMBAHKAN DI SINI
// ====================================================================
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
// ====================================================================
// AKHIR DARI KODE BARU
// ====================================================================


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

Route::get('/lihat-log-debug', function (Request $request) {
    // Ganti 'kataRahasiaKita' dengan password yang Anda inginkan
    $password = 'kataRahasiaKita';

    if ($request->input('password') !== $password) {
        return response('Akses Ditolak.', 403);
    }

    $output = '<pre>';
    $output .= "--- STATUS DEBUG APLIKASI ---\n\n";

    // Cek Symlink
    $symlinkPath = public_path('storage');
    $output .= "--- Mengecek Symlink di: " . $symlinkPath . " ---\n";
    if (file_exists($symlinkPath)) {
        if (is_link($symlinkPath)) {
            $output .= "[OK] Status: Symlink Ditemukan.\n";
            $output .= "[OK] Link mengarah ke: " . readlink($symlinkPath) . "\n";
        } else {
            $output .= "[ERROR] Status: Ada file/folder bernama 'storage' di dalam 'public', tapi BUKAN symlink.\n";
        }
    } else {
        $output .= "[ERROR] Status: Symlink 'public/storage' TIDAK DITEMUKAN.\n";
    }
    $output .= "\n\n";


    // Cek Log File
    $logPath = storage_path('logs/debug.log');
    $output .= "--- Isi Log dari storage/logs/debug.log ---\n";

    if (File::exists($logPath)) {
        $output .= htmlspecialchars(File::get($logPath));
    } else {
        $output .= "File debug.log belum ada. Coba upload produk terlebih dahulu.";
    }

    $output .= '</pre>';

    return response($output, 200)
          ->header('Content-Type', 'text/html; charset=utf-8');
});

require __DIR__.'/auth.php';
