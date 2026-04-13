<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function () {
    // Handle login logic here
})->name('login.post');

Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('password.request');

Route::post('/forgot-password', function () {
    // Handle send reset link logic here
})->name('password.email');

Route::get('/verify-email', function () {
    return view('auth.verify-email');
})->name('verification.notice');

Route::post('/verify-email', function () {
    // Handle verification logic
})->name('verification.verify');

Route::get('/reset-password', function () {
    return view('auth.reset-password');
})->name('password.reset');

Route::post('/reset-password', function () {
    // Handle reset password logic
})->name('password.update');
Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');

Route::get('/admin/users', function () {
    $users = \App\Models\User::latest()->get()->map(function($user) {
        return [
            'id' => $user->user_id,
            'name' => $user->name,
            'email' => $user->email,
            'nim' => $user->nim ?? '-',
            'prodi' => $user->prodi ?? '-',
            'joined' => $user->created_at->format('d M Y'),
            'status' => strtolower($user->status),
            'encoded_name' => urlencode($user->name)
        ];
    });
    
    return view('admin.users', compact('users'));
})->name('admin.users');
