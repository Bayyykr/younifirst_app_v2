<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\Request;


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
            'email'       => 'required|email',
            'password'    => 'required|string',
            'device_name' => 'nullable|string',
            'fcm_token'   => 'nullable|string',
        ]);

        // 1. Autentikasi ke Firebase menggunakan REST API
        $firebaseResult = $this->firebase->signInWithEmailAndPassword(
            $request->email,
            $request->password
        );

        if (! $firebaseResult) {
            return response()->json([
                'message' => 'Email atau password salah.',
            ], 401);
        }

        // 2. Cari user di database MySQL berdasarkan firebase_uid atau email
        $user = User::where('firebase_uid', $firebaseResult['uid'])->first();

        if (! $user) {
            $user = User::where('email', $request->email)->first();

            // Sinkronkan firebase_uid jika user ditemukan via email
            if ($user) {
                $user->update(['firebase_uid' => $firebaseResult['uid']]);
            }
        }

        if (! $user) {
            return response()->json([
                'message' => 'Akun tidak terdaftar di sistem. Hubungi administrator.',
            ], 404);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Akun Anda sedang dinonaktifkan.',
            ], 403);
        }

        if ($request->filled('fcm_token')) {
            $user->update(['fcm_token' => $request->fcm_token]);
        }

        // 3. Buat Sanctum Bearer Token
        $deviceName = $request->device_name ?? 'API Token';
        $token      = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'message' => 'Login sukses',
            'data'    => [
                'user'       => $user,
                'token'      => $token,
                'token_type' => 'Bearer',
            ]
        ]);
    }

    /**
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout sukses. Token telah dicabut.'
        ]);
    }
}
