<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResponseController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\UserController;

// ==============================
// 🧩 AUTH (REGISTER & LOGIN)
// ==============================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ==============================
// 🔐 GOOGLE AUTH
// ==============================
Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);

// =====================================================
// 🔐 ROUTE UNTUK USER YANG SUDAH LOGIN (SANCTUM)
// =====================================================
Route::middleware('auth:sanctum')->group(function () {

    // ==========================
    // 👤 PROFIL & LOGOUT
    // ==========================
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // ==========================
    // 📢 LAPORAN (USER)
    // ==========================
    Route::get('/my-reports', [ReportController::class, 'myReports']);
    Route::post('/reports', [ReportController::class, 'store']);
    Route::get('/reports/{report}', [ReportController::class, 'show']);
    Route::delete('/reports/{report}', [ReportController::class, 'destroy']);

    // ==========================
    // 💬 TANGGAPAN LAPORAN
    // ==========================
    Route::get('/reports/{report}/responses', [ResponseController::class, 'show']);
    Route::post('/reports/{report}/responses', [ResponseController::class, 'store']);

    // ==========================
    // 🔔 NOTIFIKASI
    // ==========================
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);

    // ==========================
    // 📂 KATEGORI (READ ONLY - USER)
    // 🔥 INI YANG DIPERBAIKI (OPS I A)
    // ==========================
    Route::get('/categories', [CategoryController::class, 'index']);

    // =================================================
    // 🔐 ADMIN ONLY ROUTES
    // =================================================
    Route::middleware('admin')->group(function () {

        // ==========================
        // 📂 KATEGORI (ADMIN CRUD)
        // ==========================
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

        // ==========================
        // 👥 USERS MANAGEMENT
        // ==========================
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
        Route::put('/users/{user}/toggle-status', [UserController::class, 'toggleStatus']);

        // ==========================
        // 🛠️ LAPORAN (ADMIN)
        // ==========================
        Route::get('/reports', [ReportController::class, 'index']);
        Route::put('/reports/{report}/status', [ReportController::class, 'updateStatus']);
    });
});
