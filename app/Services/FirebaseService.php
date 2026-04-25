<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected $auth;
    protected $messaging;
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = env('FIREBASE_API_KEY', '');

        try {
            $credentialsPath = base_path(env('FIREBASE_CREDENTIALS', 'storage/firebase/service-account.json'));

            $factory = (new Factory)
                ->withServiceAccount($credentialsPath);

            $this->auth = $factory->createAuth();
            $this->messaging = $factory->createMessaging();
        } catch (\Exception $e) {
            Log::error('Firebase Initialization Failed: ' . $e->getMessage());
        }
    }

    /**
     * Sign in with email & password via Firebase REST API.
     * Returns ['uid' => '...', 'idToken' => '...'] on success, or null on failure.
     */
    public function signInWithEmailAndPassword(string $email, string $password): ?array
    {
        try {
            $response = Http::post(
                "https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key={$this->apiKey}",
                [
                    'email'             => $email,
                    'password'          => $password,
                    'returnSecureToken' => true,
                ]
            );

            if ($response->failed()) {
                $error = $response->json('error.message', 'UNKNOWN_ERROR');
                Log::warning("Firebase signIn failed: {$error}");
                return null;
            }

            return [
                'uid'     => $response->json('localId'),
                'idToken' => $response->json('idToken'),
            ];
        } catch (\Exception $e) {
            Log::error('Firebase signInWithEmailAndPassword error: ' . $e->getMessage());
            return null;
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
                'email'         => $email,
                'emailVerified' => false,
                'password'      => $password,
                'disabled'      => false,
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

    /**
     * Send Push Notification via FCM
     */
    public function sendNotification(string $fcmToken, string $title, string $body, array $data = [])
    {
        try {
            $notification = \Kreait\Firebase\Messaging\Notification::create($title, $body);
            $message = \Kreait\Firebase\Messaging\CloudMessage::withTarget('token', $fcmToken)
                ->withNotification($notification)
                ->withData($data);

            $this->messaging->send($message);
            Log::info("FCM Notification sent to token: {$fcmToken}");
            return true;
        } catch (\Exception $e) {
            Log::error('FCM Notification Failed: ' . $e->getMessage());
            return false;
        }
    }
}

