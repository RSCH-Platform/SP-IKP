<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Juniyasyos\IamClient\Support\IamConfig;

/**
 * Service untuk menangani backchannel logout dengan IAM server
 * 
 * Backchannel Logout Flow:
 * 1. User logout dari IKP
 * 2. IKP mengirim signal ke IAM untuk logout
 * 3. IAM broadcast logout ke semua client apps
 * 4. Setiap client menerima backchannel logout request
 * 5. Client invalidate local session user yang bersangkutan
 */
class IamBackchannelLogoutService
{
    /**
     * Trigger backchannel logout ke IAM server
     * Digunakan ketika session expired atau user logout secara eksplisit
     */
    public static function triggerBackchannelLogout(?string $userId = null, ?string $sessionId = null): bool
    {
        if (!config('iam.enabled', false) && !env('USE_SSO', false)) {
            Log::debug('SSO not enabled, backchannel logout not triggered');
            return true;
        }

        $iamBaseUrl = trim((string) IamConfig::baseUrl());
        
        if (empty($iamBaseUrl)) {
            Log::warning('IAM base URL not configured, cannot trigger backchannel logout');
            return false;
        }

        try {
            $backchannelUrl = rtrim($iamBaseUrl, '/') . '/api/backchannel-logout';
            $payload = [];

            if ($userId) {
                $payload['user_id'] = $userId;
            }
            if ($sessionId) {
                $payload['session_id'] = $sessionId;
            }

            Log::channel('debug')->info('Triggering backchannel logout', [
                'iam_url' => $backchannelUrl,
                'payload' => $payload,
            ]);

            $response = Http::timeout(5)
                ->retry(2, 100)
                ->post($backchannelUrl, $payload);

            $success = $response->successful();

            Log::channel('debug')->info('Backchannel logout response', [
                'success' => $success,
                'status_code' => $response->status(),
                'response' => $response->json(),
            ]);

            return $success;
        } catch (\Exception $e) {
            Log::error('Backchannel logout failed', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
            ]);
            return false;
        }
    }

    /**
     * Notify IAM tentang session expiration
     * Memungkinkan IAM untuk cleanup token yang sudah expired
     */
    public static function notifySessionExpiration(string $userId, string $sessionId): bool
    {
        if (!config('iam.enabled', false) && !env('USE_SSO', false)) {
            return true;
        }

        try {
            $iamBaseUrl = trim((string) IamConfig::baseUrl());
            
            if (empty($iamBaseUrl)) {
                return false;
            }

            $notifyUrl = rtrim($iamBaseUrl, '/') . '/api/session-expired';
            
            Log::channel('debug')->info('Notifying IAM about session expiration', [
                'user_id' => $userId,
                'session_id' => $sessionId,
            ]);

            $response = Http::timeout(5)
                ->post($notifyUrl, [
                    'user_id' => $userId,
                    'session_id' => $sessionId,
                    'app_key' => IamConfig::appKey(),
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Session expiration notification failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Verify backchannel logout request dari IAM
     * Validate HMAC signature untuk security
     */
    public static function verifyBackchannelRequest(array $payload, ?string $signature = null): bool
    {
        // Jika backchannel verify disabled, accept semua request
        if (!config('iam.backchannel_verify', true)) {
            Log::warning('Backchannel verification disabled, accepting all requests');
            return true;
        }

        if (!$signature) {
            Log::warning('No signature provided for backchannel request');
            return false;
        }

        try {
            $secret = config('iam.sso_secret');
            $expected = hash_hmac('sha256', json_encode($payload), $secret);
            
            return hash_equals($expected, $signature);
        } catch (\Exception $e) {
            Log::error('Backchannel verification failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
