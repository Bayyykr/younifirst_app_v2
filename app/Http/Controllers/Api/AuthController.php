<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

class AuthController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    /**
     * POST /api/login
     * Login via email & password – divalidasi langsung ke Firebase
     */
    public function login(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "password" => "required|string",
            "device_name" => "nullable|string",
            "fcm_token" => "nullable|string",
            "remember" => "required|boolean",
        ]);

        // 1. Autentikasi ke Firebase menggunakan REST API
        $firebaseResult = $this->firebase->signInWithEmailAndPassword(
            $request->email,
            $request->password,
        );

        if (!$firebaseResult) {
            return response()->json(
                [
                    "message" => "Email atau password salah.",
                ],
                401,
            );
        }

        // 2. Cari user di database MySQL berdasarkan firebase_uid atau email
        $user = User::where("firebase_uid", $firebaseResult["uid"])->first();

        if (!$user) {
            $user = User::where("email", $request->email)->first();

            // Sinkronkan firebase_uid jika user ditemukan via email
            if ($user) {
                $user->update(["firebase_uid" => $firebaseResult["uid"]]);
            }
        }

        if (!$user) {
            return response()->json(
                [
                    "message" =>
                        "Akun tidak terdaftar di sistem. Hubungi administrator.",
                ],
                404,
            );
        }

        if ($user->status !== "active") {
            return response()->json(
                [
                    "message" => "Akun Anda sedang dinonaktifkan.",
                ],
                403,
            );
        }

        if ($request->filled("fcm_token")) {
            $user->update(["fcm_token" => $request->fcm_token]);
        }

        // 3. Buat Sanctum Bearer Token
        $deviceName = $request->device_name ?? "API Token";
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            "message" => "Login sukses",
            "data" => [
                "user" => $user,
                "token" => $token,
                "token_type" => "Bearer",
            ],
        ]);
    }

    /**
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            "message" => "Logout sukses. Token telah dicabut.",
        ]);
    }

    /**
     * POST /api/forgot-password
     * Request OTP for password reset
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            "email" => "required|email|exists:users,email",
        ]);

        $otp = str_pad(random_int(0, 9999), 4, "0", STR_PAD_LEFT);

        DB::table("password_reset_tokens")->updateOrInsert(
            ["email" => $request->email],
            [
                "token" => $otp,
                "created_at" => now(),
            ],
        );

        try {
            Mail::to($request->email)->send(new OtpMail($otp));
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Gagal mengirim email OTP.",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }

        return response()->json([
            "message" => "OTP telah dikirim ke email Anda.",
        ]);
    }

    /**
     * POST /api/reset-password
     * Reset password using OTP
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            "email" => "required|email|exists:users,email",
            "otp" => "required|digits:4",
            "new_password" => "required|string|min:8|confirmed",
        ]);

        $resetRecord = DB::table("password_reset_tokens")
            ->where("email", $request->email)
            ->where("token", $request->otp)
            ->first();

        if (!$resetRecord) {
            return response()->json([
                "message" => "OTP tidak valid.",
            ], 400);
        }

        // Check if OTP is expired (older than 15 minutes)
        if (now()->diffInMinutes($resetRecord->created_at) > 15) {
            DB::table("password_reset_tokens")
                ->where("email", $request->email)
                ->delete();
            return response()->json([
                "message" => "OTP sudah kedaluwarsa.",
            ], 400);
        }

        $user = User::where("email", $request->email)->first();

        // Update password in Firebase
        if ($user->firebase_uid) {
            $firebaseUpdated = $this->firebase->updateUserPassword(
                $user->firebase_uid,
                $request->new_password,
            );
            if (!$firebaseUpdated) {
                return response()->json([
                    "message" => "Gagal mengupdate password di server autentikasi.",
                ], 500);
            }
        }

        // Update password in local database
        $user->update([
            "password" => Hash::make($request->new_password),
        ]);

        // Delete the used OTP
        DB::table("password_reset_tokens")
            ->where("email", $request->email)
            ->delete();

        return response()->json([
            "message" => "Password berhasil direset.",
        ]);
    }
}
