<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    /**
     * Resolve the credentials path using multiple fallbacks.
     */
    private static function getCredentialsPath()
    {
        $envPath = env('FIREBASE_CREDENTIALS');
        if ($envPath) {
            $resolved = file_exists($envPath) ? $envPath : base_path($envPath);
            if (file_exists($resolved)) {
                return $resolved;
            }
        }

        $storagePath = storage_path('app/firebase-credentials.json');
        if (file_exists($storagePath)) {
            return $storagePath;
        }

        $basePath = base_path('firebase-credentials.json');
        if (file_exists($basePath)) {
            return $basePath;
        }

        return null;
    }

    /**
     * Get Google OAuth2 Access Token using JWT authentication
     */
    private static function getAccessToken()
    {
        $path = self::getCredentialsPath();

        if (!$path || !file_exists($path)) {
            Log::error("FCM Error: Credentials file not found (checked storage, base_path and env).");
            return null;
        }

        try {
            $config = json_decode(file_get_contents($path), true);
            if (!$config) {
                Log::error("FCM Error: Credentials file is invalid JSON.");
                return null;
            }

            $privateKey = $config['private_key'] ?? null;
            $clientEmail = $config['client_email'] ?? null;

            if (!$privateKey || !$clientEmail) {
                Log::error("FCM Error: Missing private_key or client_email in credentials.");
                return null;
            }

            // JWT Header
            $header = self::base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));

            // JWT Claim set
            $now = time();
            $claim = self::base64UrlEncode(json_encode([
                'iss' => $clientEmail,
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => $now + 3600,
                'iat' => $now
            ]));

            // Sign
            $signatureInput = $header . '.' . $claim;
            $signature = '';
            
            if (!openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
                Log::error("FCM Error: openssl_sign failed. Check if OpenSSL extension is loaded.");
                return null;
            }

            $base64Signature = self::base64UrlEncode($signature);
            $jwt = $signatureInput . '.' . $base64Signature;

            // Fetch Token
            $response = Http::asForm()->withoutVerifying()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ]);

            if ($response->failed()) {
                Log::error("FCM Error: OAuth token request failed: " . $response->body());
                return null;
            }

            return $response->json()['access_token'] ?? null;

        } catch (\Exception $e) {
            Log::error("FCM Error: Exception generating access token: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Send push notification to a device token
     */
    public static function sendNotification($fcmToken, $title, $body, $data = [])
    {
        if (empty($fcmToken)) {
            return false;
        }

        $path = self::getCredentialsPath();
        if (!$path || !file_exists($path)) {
            Log::warning("FCM: Skipping notification send. Credentials file not found (checked storage, base_path and env).");
            return false;
        }

        $config = json_decode(file_get_contents($path), true);
        $projectId = $config['project_id'] ?? null;

        if (!$projectId) {
            Log::error("FCM Error: project_id missing in credentials.");
            return false;
        }

        $accessToken = self::getAccessToken();
        if (!$accessToken) {
            Log::error("FCM Error: Failed to acquire OAuth access token.");
            return false;
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $payload = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => array_merge($data, [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'id' => '1',
                    'status' => 'done',
                ]),
                'android' => [
                    'notification' => [
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'sound' => 'default',
                        'priority' => 'high',
                    ]
                ]
            ]
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->withoutVerifying()->post($url, $payload);

            if ($response->successful()) {
                Log::info("FCM Notification successfully sent to token: " . substr($fcmToken, 0, 15) . "...");
                return true;
            }

            Log::error("FCM Notification delivery failed: " . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error("FCM Notification send exception: " . $e->getMessage());
            return false;
        }
    }

    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
