<?php

use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\LostfoundController as AdminLostfoundController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────
//  Public Routes
    // ─────────────────────────────────────────────────────────────

Route::get('/', [LandingController::class, 'index'])->name('home');

// ─────────────────────────────────────────────────────────────
//  Admin Routes
// ─────────────────────────────────────────────────────────────

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::get('/teams', [TeamController::class, 'index'])->name('teams');
    Route::post('/teams/{member_id}/respond', [TeamController::class, 'respond'])->name('teams.respond');
    Route::delete('/teams/{team_id}', [TeamController::class, 'destroy'])->name('teams.destroy');
    Route::get('/announcement', [AnnouncementController::class, 'index'])->name('announcement');
    Route::post('/announcement', [AnnouncementController::class, 'store'])->name('announcement.store');
    Route::put('/announcement/{announcement_id}', [AnnouncementController::class, 'update'])->name('announcement.update');
    Route::delete('/announcement/{announcement_id}', [AnnouncementController::class, 'destroy'])->name('announcement.destroy');
    Route::get('/lostfound', [AdminLostfoundController::class, 'index'])->name('lostfound');
    Route::post('/lostfound', [AdminLostfoundController::class, 'store'])->name('lostfound.store');
    Route::post('/lostfound/{lostfound_id}/resolve', [AdminLostfoundController::class, 'resolve'])->name('lostfound.resolve');
    Route::delete('/lostfound/{lostfound_id}', [AdminLostfoundController::class, 'destroy'])->name('lostfound.destroy');
    Route::get('/events', [EventController::class, 'index'])->name('events');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::put('/events/{event_id}', [EventController::class, 'update'])->name('events.update');
    Route::post('/events/{event_id}/respond', [EventController::class, 'respond'])->name('events.respond');
    Route::delete('/events/{event_id}', [EventController::class, 'destroy'])->name('events.destroy');
});

// ─────────────────────────────────────────────────────────────
//  Profile Routes (Breeze)
// ─────────────────────────────────────────────────────────────

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';


