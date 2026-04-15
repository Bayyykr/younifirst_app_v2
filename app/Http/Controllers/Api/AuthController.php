<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    /**
     * POST /api/auth/firebase
     * Login via Firebase ID Token
     */
    public function loginWithFirebase(Request $request)
    {
        $request->validate([
            'id_token'    => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        $firebaseUid = $this->firebase->verifyIdToken($request->id_token);

        if (!$firebaseUid) {
            return response()->json([
                'message' => 'Token Firebase tidak valid atau sudah kadaluwarsa.'
            ], 401);
        }

        // Cari user di MySQL berdasarkan firebase_uid
        $user = User::where('firebase_uid', $firebaseUid)->first();

        // Jika tidak ketemu, coba cari berdasarkan email dari Firebase
        if (!$user) {
            $firebaseUser = $this->firebase->getUser($firebaseUid);
            if ($firebaseUser && $firebaseUser->email) {
                $user = User::where('email', $firebaseUser->email)->first();
                
                // Jika ketemu berdasarkan email, update firebase_uid-nya
                if ($user) {
                    $user->update(['firebase_uid' => $firebaseUid]);
                }
            }
        }

        if (!$user) {
            return response()->json([
                'message' => 'User tidak terdaftar di sistem kami. Silakan hubungi admin.'
            ], 404);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Akun Anda sedang dinonaktifkan.'
            ], 403);
        }

        $deviceName = $request->device_name ?? 'Mobile App';
        $token      = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'message' => 'Login sukses',
            'data'    => [
                'user'  => [
                    'id'    => $user->user_id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'role'  => $user->role,
                ],
                'token' => $token,
            ]
        ]);
    }

    /**
     * POST /api/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'       => 'required|email',
            'password'    => 'required',
            'device_name' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan salah.'],
            ]);
        }

        $deviceName = $request->device_name ?? 'API Token';
        $token      = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'message' => 'Login sukses',
            'data'    => [
                'user'  => [
                    'id'    => $user->user_id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'role'  => $user->role,
                ],
                'token' => $token,
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
