<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes - Laporin
|--------------------------------------------------------------------------
|
| Semua endpoint API untuk aplikasi Laporin
| Menggunakan Laravel Sanctum untuk autentikasi.
|
*/

// 🧩 Auth (Register & Login)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// 🔐 Hanya untuk user yang sudah login
Route::middleware('auth:sanctum')->group(function () {

    // 👤 Profil & Logout
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // 📢 Laporan (User)
    Route::get('/my-reports', [ReportController::class, 'myReports']);
    Route::post('/reports', [ReportController::class, 'store']);
    Route::get('/reports/{report}', [ReportController::class, 'show']);
    Route::delete('/reports/{report}', [ReportController::class, 'destroy']);

    // 🛠️ Laporan (Admin)
    Route::middleware('admin')->group(function () {
        Route::get('/reports', [ReportController::class, 'index']);
        Route::put('/reports/{report}/status', [ReportController::class, 'updateStatus']);
    });
});
