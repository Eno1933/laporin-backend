<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResponseController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CategoryController;

/*
|--------------------------------------------------------------------------
| 🌐 API Routes - Laporin
|--------------------------------------------------------------------------
|
| Semua endpoint API untuk aplikasi Laporin.
| Menggunakan Laravel Sanctum untuk autentikasi dan middleware admin.
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

    // 💬 Tanggapan Laporan (Responses)
    Route::get('/reports/{report}/responses', [ResponseController::class, 'show']);   // lihat tanggapan laporan
    Route::post('/reports/{report}/responses', [ResponseController::class, 'store']); // admin menanggapi laporan

    // 🔔 Notifikasi
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);

    // 📂 Kategori (Admin Only)
    Route::middleware('admin')->group(function () {
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

        // 🛠️ Laporan (Admin)
        Route::get('/reports', [ReportController::class, 'index']);
        Route::put('/reports/{report}/status', [ReportController::class, 'updateStatus']);
    });
});
