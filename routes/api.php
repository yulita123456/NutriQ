<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\Api\RiwayatScanController;
use App\Http\Controllers\Api\ForgotPasswordController;

// Route public
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password/request-code', [ForgotPasswordController::class, 'requestCode']);
Route::post('/forgot-password/verify-code', [ForgotPasswordController::class, 'verifyCode']);
Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword']);
Route::get('/product', [ProductController::class, 'getProduct']);
Route::post('/product/store', [ProductController::class, 'storeByUser']);
Route::get('/product/kode/{kode}', [ProductController::class, 'getByKode']);
Route::post('/ocr', [ProductController::class, 'extractTextFromImage']);

// Group route yang butuh autentikasi
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/transaction', [TransactionController::class, 'store']);
    Route::get('/transaction', [TransactionController::class, 'index']);
    Route::get('/transaction/{id}', [TransactionController::class, 'show']);
    Route::post('/riwayat-scan', [RiwayatScanController::class, 'store']);
    Route::get('/statistik-scan', [RiwayatScanController::class, 'statistik']);

    // Profil routes
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile/update', [AuthController::class, 'updateProfile']);        // UPDATE PROFILE
    Route::put('/profile/change-password', [AuthController::class, 'changePassword']); // GANTI PASSWORD
});

// Callback Midtrans (public)
Route::post('/midtrans/callback', [TransactionController::class, 'midtransCallback']);

// Route ini tetap di luar group, tapi tetap pakai middleware
Route::middleware('auth:sanctum')->get('/transaction/order/{order_id}', [TransactionController::class, 'showByOrderId']);
