<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class TokenValidationDebugMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $request->header('X-Request-ID') ?? uniqid('token-', true);

        // Check token in various places
        $token = $this->extractToken($request);

        if ($token) {
            $this->debugTokenValidation($token, $requestId);
        } else {
            Log::channel('debug')->info('[TOKEN_DEBUG] NO_TOKEN_FOUND', [
                'request_id' => $requestId,
                'path' => $request->getPathInfo(),
                'query_has_token' => $request->has('token'),
                'header_auth' => $request->header('Authorization') ? 'YES' : 'NO',
                'bearer_token' => $request->bearerToken() ? 'YES' : 'NO',
            ]);
        }

        $response = $next($request);

        // Log authentication state after request processing
        Log::channel('debug')->info('[TOKEN_DEBUG] AUTH_AFTER_REQUEST', [
            'request_id' => $requestId,
            'path' => $request->getPathInfo(),
            'authenticated' => auth()->check(),
            'user_id' => auth()->id() ?? 'NONE',
            'response_status' => $response->getStatusCode(),
        ]);

        return $response;
    }

    private function extractToken(Request $request): ?string
    {
        // Check query parameter first
        if ($request->has('token')) {
            return $request->query('token');
        }

        // Check Authorization header
        if ($request->bearerToken()) {
            return $request->bearerToken();
        }

        // Check Cookie
        $token = $request->cookie('iam_token') ?? $request->cookie('token');
        if ($token) {
            return $token;
        }

        return null;
    }

    private function debugTokenValidation(string $token, string $requestId): void
    {
        $iamSecret = config('iam.secret');
        $iamLeeway = config('iam.jwt_leeway', 60);

        try {
            // Decode token header to see what algorithm is used
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                Log::channel('debug')->warning('[TOKEN_DEBUG] INVALID_JWT_FORMAT', [
                    'request_id' => $requestId,
                    'parts_count' => count($parts),
                    'expected' => 3,
                ]);
                return;
            }

            $header = json_decode(base64_decode($parts[0]), true);
            $payload = json_decode(base64_decode($parts[1]), true);

            Log::channel('debug')->info('[TOKEN_DEBUG] TOKEN_STRUCTURE', [
                'request_id' => $requestId,
                'header' => $header,
                'payload_keys' => array_keys($payload ?? []),
                'sub' => $payload['sub'] ?? 'MISSING',
                'nip' => $payload['nip'] ?? 'MISSING',
                'app' => $payload['app'] ?? 'MISSING',
                'iss' => $payload['iss'] ?? 'MISSING',
                'iat' => $payload['iat'] ?? 'MISSING',
                'exp' => $payload['exp'] ?? 'MISSING',
                'iat_timestamp' => $payload['iat'] ? date('Y-m-d H:i:s', $payload['iat']) : 'MISSING',
                'exp_timestamp' => $payload['exp'] ? date('Y-m-d H:i:s', $payload['exp']) : 'MISSING',
            ]);

            // Check token expiry
            if ($payload['exp'] ?? null) {
                $now = time();
                $tokenExpiry = $payload['exp'];
                $isExpired = $now > $tokenExpiry;
                $secondsUntilExpiry = $tokenExpiry - $now;

                Log::channel('debug')->info('[TOKEN_DEBUG] EXPIRY_CHECK', [
                    'request_id' => $requestId,
                    'current_time' => $now,
                    'token_exp' => $tokenExpiry,
                    'seconds_until_expiry' => $secondsUntilExpiry,
                    'is_expired' => $isExpired,
                    'current_time_formatted' => date('Y-m-d H:i:s', $now),
                    'token_exp_formatted' => date('Y-m-d H:i:s', $tokenExpiry),
                ]);
            }

            // Try to verify token
            try {
                JWT::decode($token, new Key($iamSecret, 'HS256'));
                Log::channel('debug')->info('[TOKEN_DEBUG] TOKEN_VALID', [
                    'request_id' => $requestId,
                    'is_valid' => true,
                    'algorithm' => 'HS256',
                ]);
            } catch (Exception $e) {
                Log::channel('debug')->warning('[TOKEN_DEBUG] TOKEN_VERIFICATION_FAILED', [
                    'request_id' => $requestId,
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                ]);
            }
        } catch (Exception $e) {
            Log::channel('debug')->error('[TOKEN_DEBUG] TOKEN_DECODE_ERROR', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
            ]);
        }
    }
}
