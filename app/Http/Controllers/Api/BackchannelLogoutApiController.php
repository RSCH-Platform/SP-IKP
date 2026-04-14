<?php

namespace App\Http\Controllers\Api;

use App\Services\IamBackchannelLogoutService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Juniyasyos\IamClient\Support\IamConfig;

/**
 * API Controller untuk menangani backchannel logout requests dari IAM server
 * 
 * Backchannel Logout bekerja dengan cara:
 * 1. IAM server memiliki daftar semua session/user yang currently logged in
 * 2. Ketika user logout dari salah satu app atau session expired di IAM
 * 3. IAM mengirim backchannel logout request ke semua client apps yang user tersebut login
 * 4. Setiap client app merespons dengan invalidate session user tersebut lokal
 * 
 * Endpoint: POST /api/iam/backchannel-logout (dari IamClient package)
 * 
 * Payload bisa berupa:
 * - logout_token (JWT token yang mengindikasikan siapa yang logout)
 * - user_id + session_id (direct identifier)
 * - username (fallback)
 */
class BackchannelLogoutApiController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Handle backchannel logout request dari IAM server
     * Invalidate session user yang logout
     */
    public function handleLogout(Request $request): Response
    {
        try {
            // Verify HMAC signature (jika enabled)
            $signature = $request->header('X-Signature');
            $payload = $request->all();

            if (!IamBackchannelLogoutService::verifyBackchannelRequest($payload, $signature)) {
                Log::warning('Invalid backchannel logout request', [
                    'signature' => $signature,
                    'ip' => $request->ip(),
                ]);
                return response('Unauthorized', Response::HTTP_UNAUTHORIZED);
            }

            Log::channel('debug')->info('=== BACKCHANNEL LOGOUT REQUEST RECEIVED ===', [
                'payload' => $this->sanitizePayload($payload),
                'ip' => $request->ip(),
                'timestamp' => now()->toIso8601String(),
            ]);

            // Extract user identifier
            $userId = $payload['user_id'] ?? null;
            $sessionId = $payload['session_id'] ?? null;
            $username = $payload['username'] ?? null;
            $iamId = $payload['iam_id'] ?? null;

            // Find dan invalidate user session(s)
            $invalidateCount = 0;

            if ($userId) {
                $invalidateCount = $this->invalidateUserSessions($userId);
            } elseif ($iamId) {
                $invalidateCount = $this->invalidateUserSessionsByIamId($iamId);
            } elseif ($username) {
                $invalidateCount = $this->invalidateUserSessionsByUsername($username);
            } elseif ($sessionId) {
                $invalidateCount = $this->invalidateSession($sessionId) ? 1 : 0;
            }

            Log::channel('debug')->info('=== BACKCHANNEL LOGOUT PROCESSED ===', [
                'user_id' => $userId,
                'session_id' => $sessionId,
                'username' => $username,
                'iam_id' => $iamId,
                'invalidated_sessions' => $invalidateCount,
            ]);

            Log::info('Backchannel logout processed', [
                'user_id' => $userId,
                'invalidated_sessions' => $invalidateCount,
                'action' => 'backchannel_logout',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => "Logged out {$invalidateCount} session(s)",
                'invalidated' => $invalidateCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Backchannel logout error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Invalidate semua session untuk suatu user ID
     */
    private function invalidateUserSessions(string $userId): int
    {
        try {
            $affectedRows = DB::table('sessions')
                ->whereJsonContains('payload', '"iam_id":"' . $userId . '"')
                ->orWhereJsonContains('payload', '"user_id":"' . $userId . '"')
                ->delete();

            if ($affectedRows > 0) {
                Log::channel('debug')->info("Invalidated {$affectedRows} sessions for user", [
                    'user_id' => $userId,
                ]);
            }

            return $affectedRows;
        } catch (\Exception $e) {
            Log::error('Error invalidating user sessions', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
            ]);
            return 0;
        }
    }

    /**
     * Invalidate semua session berdasarkan IAM ID
     */
    private function invalidateUserSessionsByIamId(string $iamId): int
    {
        try {
            // Cari user berdasarkan iam_id
            $userModel = config('iam.user_model', 'App\\Models\\User');
            $user = $userModel::where('iam_id', $iamId)->first();

            if (!$user) {
                Log::debug("User not found with iam_id", ['iam_id' => $iamId]);
                return 0;
            }

            return $this->invalidateUserSessions($user->id);
        } catch (\Exception $e) {
            Log::error('Error invalidating sessions by iam_id', [
                'error' => $e->getMessage(),
                'iam_id' => $iamId,
            ]);
            return 0;
        }
    }

    /**
     * Invalidate semua session berdasarkan username
     */
    private function invalidateUserSessionsByUsername(string $username): int
    {
        try {
            $userModel = config('iam.user_model', 'App\\Models\\User');
            $user = $userModel::where('email', $username)
                ->orWhere('username', $username)
                ->first();

            if (!$user) {
                Log::debug("User not found with username", ['username' => $username]);
                return 0;
            }

            return $this->invalidateUserSessions($user->id);
        } catch (\Exception $e) {
            Log::error('Error invalidating sessions by username', [
                'error' => $e->getMessage(),
                'username' => $username,
            ]);
            return 0;
        }
    }

    /**
     * Invalidate spesifik session berdasarkan session ID
     */
    private function invalidateSession(string $sessionId): bool
    {
        try {
            $affectedRows = DB::table('sessions')
                ->where('id', $sessionId)
                ->delete();

            if ($affectedRows > 0) {
                Log::channel('debug')->info("Invalidated session", [
                    'session_id' => $sessionId,
                ]);
            }

            return $affectedRows > 0;
        } catch (\Exception $e) {
            Log::error('Error invalidating session', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId,
            ]);
            return false;
        }
    }

    /**
     * Sanitize payload untuk logging (remove sensitive data)
     */
    private function sanitizePayload(array $payload): array
    {
        $sanitized = [];
        $sanitizeFields = ['logout_token', 'token', 'password', 'secret', 'signature'];

        foreach ($payload as $key => $value) {
            if (in_array($key, $sanitizeFields)) {
                $sanitized[$key] = '***REDACTED***';
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
