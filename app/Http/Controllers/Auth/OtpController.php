<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;

class OtpController extends Controller
{
    /**
     * Send OTP to email for password reset.
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $otp = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $otp,
                'created_at' => now(),
            ]
        );

        try {
            Mail::to($request->email)->send(new OtpMail($otp));
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Gagal mengirim OTP: ' . $e->getMessage()]);
        }

        // Store email in session for verification step
        $request->session()->put('otp_email', $request->email);
        return redirect()->route('otp.verify')->with('status', 'Kode OTP telah dikirim ke email Anda.');
    }

    /**
     * Show OTP verification form.
     */
    public function showVerifyForm(Request $request)
    {
        $email = $request->session()->get('otp_email');
        if (! $email) {
            return redirect()->route('password.request')->withErrors(['email' => 'Silakan masukkan email terlebih dahulu.']);
        }
        return view('auth.otp-verify', ['email' => $email]);
    }

    /**
     * Verify OTP and redirect to password reset form.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:4'],
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->otp)
            ->first();

        if (! $record) {
            return back()->withErrors(['otp' => 'Kode OTP tidak valid.']);
        }

        // Expire after 15 minutes
        if (Carbon::parse($record->created_at)->diffInMinutes(now()) > 15) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['otp' => 'Kode OTP telah kedaluwarsa.']);
        }

        // OTP is valid – clean OTP session email, but set reset_password_email to authorize the reset
        $request->session()->forget('otp_email');
        $request->session()->put('reset_password_email', $request->email);
        
        // Delete used OTP
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        
        return redirect()->route('password.reset', [
            'token' => $request->otp,
            'email' => $request->email
        ]);
    }
}
?>
