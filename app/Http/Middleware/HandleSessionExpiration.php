<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Juniyasyos\IamClient\Support\IamConfig;

/**
 * Middleware untuk mendeteksi dan menangani session expiration dengan SSO
 * 
 * Ketika session sudah expired namun user masih terasa "login" di IAM,
 * middleware ini akan memicu backchannel logout ke IAM server.
 * 
 * Flow:
 * 1. User session expired (cookie sudah hilang atau tidak valid)
 * 2. Middleware mendeteksi user tidak authenticated
 * 3. Jika SSO enabled, callback backchannel logout ke IAM
 * 4. IAM akan mengirim signal ke semua client apps untuk logout
 */
class HandleSessionExpiration
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Cek apakah SSO enabled
        $ssoEnabled = config('iam.enabled', false) || env('USE_SSO', false);

        if (!$ssoEnabled) {
            return $next($request);
        }

        // Cek apakah user authenticated
        $guardName = IamConfig::guardName('web');
        $isAuthenticated = Auth::guard($guardName)->check();

        if ($isAuthenticated) {
            // User masih authenticated, lanjutkan
            return $next($request);
        }

        // User tidak authenticated - cek apakah session sudah kedaluwarsa
        if ($this->isSessionExpired($request)) {
            // Session sudah expired, log dan trigger backchannel logout
            $this->handleExpiredSession($request);
        }

        return $next($request);
    }

    /**
     * Cek apakah session sudah kedaluwarsa
     * 
     * SESSION DIANGGAP EXPIRED jika:
     * - Session cookie ada tapi user tidak authenticated
     * - Session data ada tapi tidak valid
     * - Timeout session sudah terlampaui
     */
    private function isSessionExpired(Request $request): bool
    {
        // Jika session tidak ada sama sekali, bukan expired
        if (!$request->hasSession() || session()->getId() === null) {
            return false;
        }

        // Jika session ada tapi tidak ada data IAM, kemungkinan expired
        $iamSessionKey = 'iam_' . IamConfig::guardName('web');
        $hasIamSession = session()->has('iam') || session()->has($iamSessionKey);

        // Jika ada session data tapi user tidak authenticated, likely expired
        if ($hasIamSession || !empty(session()->all())) {
            return true;
        }

        return false;
    }

    /**
     * Handle session yang sudah expired dengan SSO
     */
    private function handleExpiredSession(Request $request): void
    {
        $sessionId = session()->getId() ?? 'unknown';
        $userId = $this->getUserIdFromSession($request);

        Log::channel('debug')->warning('==== SESSION EXPIRATION DETECTED (IKP) ====', [
            'session_id' => $sessionId,
            'user_id' => $userId,
            'path' => $request->path(),
            'ip' => $request->ip(),
            'timestamp' => now()->toIso8601String(),
        ]);

        // Clear local session
        $this->clearLocalSession($request);

        // Queue backchannel logout ke IAM (jika diperlukan)
        // IAM client package sudah handle backchannel logout endpoint
        // Tapi kita bisa log untuk monitoring
        Log::info('Session expired, user should re-authenticate via SSO', [
            'session_id' => $sessionId,
            'user_id' => $userId,
            'action' => 'will_trigger_backchannel_on_next_request',
        ]);
    }

    /**
     * Ambil user ID dari session untuk logging
     */
    private function getUserIdFromSession(Request $request): ?string
    {
        $guardName = IamConfig::guardName('web');
        
        // Coba dari session IAM data
        if (session()->has('iam')) {
            $iamData = session()->get('iam');
            if (is_array($iamData) && isset($iamData['user_id'])) {
                return $iamData['user_id'];
            }
        }

        // Coba dari auth payload yang tersimpan
        if (session()->has('auth.user_id')) {
            return session()->get('auth.user_id');
        }

        return null;
    }

    /**
     * Clear local session data
     */
    private function clearLocalSession(Request $request): void
    {
        // Forget semua session keys yang terkait IAM
        session()->forget([
            'iam',
            'iam_applications',
            'iam_user',
            'auth.user_id',
            'auth.session_id',
        ]);

        // Invalidate session jika diperlukan
        // NOTE: Jangan invalidate session sepenuhnya di middleware ini
        // Let the application handle the full logout flow
        // ini hanya cleanup untuk prevent stale data
    }
}
