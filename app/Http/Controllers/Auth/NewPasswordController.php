<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', [
            'request' => $request,
            'token' => $request->route('token')
        ]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        // Check if the user successfully completed OTP verification for this email
        if ($request->session()->get('reset_password_email') === $request->email) {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return back()->withErrors(['email' => 'User tidak ditemukan.']);
            }

            // Update local DB password
            $user->forceFill([
                'password' => Hash::make($request->password),
            ])->save();

            // Update password in Firebase
            if ($user->firebase_uid) {
                try {
                    app(\App\Services\FirebaseService::class)->updateUserPassword($user->firebase_uid, $request->password);
                } catch (\Exception $e) {
                    // Log or handle Firebase exception if any
                }
            }

            event(new PasswordReset($user));

            // Clean up session
            $request->session()->forget('reset_password_email');

            return redirect()->route('login')->with('status', 'Kata sandi berhasil diatur ulang.');
        }

        // Fallback to standard Laravel Password broker reset if no OTP session found
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                ])->save();

                // Update password in Firebase
                if ($user->firebase_uid) {
                    app(\App\Services\FirebaseService::class)->updateUserPassword($user->firebase_uid, $request->password);
                }

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
