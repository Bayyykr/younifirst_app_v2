<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    protected $auth;

    public function __construct(FirebaseAuth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Get a Firebase Custom Token for the authenticated user.
     * 
     * This token allows the mobile app to sign in to Firebase 
     * with the same identity as the Laravel user.
     */
    public function getFirebaseToken()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Use user_id as the UID for Firebase (ensure it's a string)
            $uid = (string) $user->user_id;

            if (empty($uid)) {
                throw new \Exception("User ID is empty");
            }

            // Create a custom token for the user's ID
            $customToken = $this->auth->createCustomToken($uid, [
                'name' => $user->name,
                'email' => $user->email,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'firebase_token' => $customToken->toString(),
                    'user_id' => $uid,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate Firebase token: ' . $e->getMessage()
            ], 500);
        }
    }
}
