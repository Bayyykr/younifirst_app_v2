<?php

use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\LostfoundController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────
//  Auth-protected route (Sanctum)
// ─────────────────────────────────────────────────────────────

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ─────────────────────────────────────────────────────────────
//  API Routes — semua read-only via Database Views
//  Base URL: /api/...
// ─────────────────────────────────────────────────────────────

// ── USERS ────────────────────────────────────────────────
Route::prefix('users')->controller(UserController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/{user_id}', 'show');
});

// ── EVENTS ───────────────────────────────────────────────
Route::prefix('events')->controller(EventController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/{event_id}', 'show');
    Route::get('/{event_id}/likes', 'likes');
});

// ── TEAMS ────────────────────────────────────────────────
Route::prefix('teams')->controller(TeamController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/{team_id}', 'show');
    Route::get('/{team_id}/members', 'members');
});

// ── ANNOUNCEMENTS ─────────────────────────────────────────
Route::prefix('announcements')->controller(AnnouncementController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/{announcement_id}', 'show');
});

// ── LOST & FOUND ─────────────────────────────────────────
Route::prefix('lostfound')->controller(LostfoundController::class)->group(function () {
    Route::get('/', 'index');
    Route::get('/{lostfound_id}', 'show');
    Route::get('/{lostfound_id}/comments', 'comments');
});
