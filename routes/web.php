<?php

use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────
//  Public Routes
// ─────────────────────────────────────────────────────────────

Route::get('/', [LandingController::class, 'index'])->name('home');

// ─────────────────────────────────────────────────────────────
//  Auth Routes
// ─────────────────────────────────────────────────────────────

Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login')->name('login.post');

    Route::get('/forgot-password', 'showForgotPasswordForm')->name('password.request');
    Route::post('/forgot-password', 'sendResetLinkEmail')->name('password.email');

    Route::get('/verify-email', 'showVerifyEmailForm')->name('verification.notice');
    Route::post('/verify-email', 'verifyEmail')->name('verification.verify');

    Route::get('/reset-password', 'showResetPasswordForm')->name('password.reset');
    Route::post('/reset-password', 'resetPassword')->name('password.update');
});

// ─────────────────────────────────────────────────────────────
//  Admin Routes
// ─────────────────────────────────────────────────────────────

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::get('/teams', [TeamController::class, 'index'])->name('teams');
    Route::get('/announcement', [AnnouncementController::class, 'index'])->name('announcement');
});
