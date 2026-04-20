<?php

use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\LostfoundController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * ─────────────────────────────────────────────────────────────
 *  Public API Routes
 * ─────────────────────────────────────────────────────────────
 */

Route::post('/login', [AuthController::class, 'login']);

/**
 * ─────────────────────────────────────────────────────────────
 *  Auth-protected routes (Sanctum)
 * ─────────────────────────────────────────────────────────────
 */

Route::middleware('auth:sanctum')->group(function () {
    
    // Auth Status & Logout
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // ── USERS ────────────────────────────────────────────────
    Route::prefix('users')->controller(UserController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{user_id}', 'show');
        Route::post('/add', 'store');
        Route::put('/{user_id}', 'update');
        Route::delete('/{user_id}', 'destroy');
    });

    // ── EVENTS ───────────────────────────────────────────────
    Route::prefix('events')->controller(EventController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{event_id}', 'show');
        Route::get('/{event_id}/likes', 'likes');
        Route::post('/add', 'store');
        Route::put('/{event_id}', 'update');
        Route::delete('/{event_id}', 'destroy');
        Route::post('/{event_id}/like', 'toggleLike');
    });

    // ── TEAMS ────────────────────────────────────────────────
    Route::prefix('teams')->controller(TeamController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{team_id}', 'show');
        Route::get('/{team_id}/members', 'members');
        Route::post('/add', 'store');
        Route::put('/{team_id}', 'update');
        Route::delete('/{team_id}', 'destroy');
        Route::post('/{team_id}/join', 'join');
        Route::post('/{team_id}/members/{member_id}/respond', 'respondJoin');
    });

    // ── ANNOUNCEMENTS ─────────────────────────────────────────
    Route::prefix('announcements')->controller(AnnouncementController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{announcement_id}', 'show');
        Route::post('/add', 'store');
        Route::put('/{announcement_id}', 'update');
        Route::delete('/{announcement_id}', 'destroy');
    });

    // ── LOST & FOUND ─────────────────────────────────────────
    Route::prefix('lostfound')->controller(LostfoundController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{lostfound_id}', 'show');
        Route::get('/{lostfound_id}/comments', 'comments');
        Route::post('/add', 'store');
        Route::put('/{lostfound_id}', 'update');
        Route::delete('/{lostfound_id}', 'destroy');
        Route::post('/{lostfound_id}/comments', 'addComment');
    });
});
