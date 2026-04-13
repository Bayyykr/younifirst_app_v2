<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // TODO: Implement login logic
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        // TODO: Send reset link
    }

    public function showVerifyEmailForm()
    {
        return view('auth.verify-email');
    }

    public function verifyEmail(Request $request)
    {
        // TODO: Handle verification
    }

    public function showResetPasswordForm()
    {
        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        // TODO: Handle reset password
    }
}
