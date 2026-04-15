<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected $auth;

    public function __construct()
    {
        try {
            $credentialsPath = base_path(env('FIREBASE_CREDENTIALS', 'storage/firebase/service-account.json'));
            
            $factory = (new Factory)
                ->withServiceAccount($credentialsPath);

            $this->auth = $factory->createAuth();
        } catch (\Exception $e) {
            Log::error('Firebase Initialization Failed: ' . $e->getMessage());
        }
    }

    /**
     * Verify Firebase ID Token
     */
    public function verifyIdToken(string $idToken)
    {
        try {
            $verifiedIdToken = $this->auth->verifyIdToken($idToken);
            return $verifiedIdToken->claims()->get('sub'); // Return Firebase UID
        } catch (FailedToVerifyToken $e) {
            Log::error('Firebase Token Verification Failed: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            Log::error('Firebase error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create Firebase User
     */
    public function createUser(string $email, string $password, string $displayName = null)
    {
        try {
            $userProperties = [
                'email' => $email,
                'emailVerified' => false,
                'password' => $password,
                'disabled' => false,
            ];

            if ($displayName) {
                $userProperties['displayName'] = $displayName;
            }

            $createdUser = $this->auth->createUser($userProperties);
            return $createdUser->uid;
        } catch (\Exception $e) {
            Log::error('Firebase User Creation Failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get User by UID
     */
    public function getUser(string $uid)
    {
        try {
            return $this->auth->getUser($uid);
        } catch (\Exception $e) {
            return null;
        }
    }
}
