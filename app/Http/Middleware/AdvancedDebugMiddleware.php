<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class AdvancedDebugMiddleware
{
    private static $requestId;
    private static $startTime;
    private static $requestLog = [];

    public function handle(Request $request, Closure $next): Response
    {
        // Generate unique request ID
        self::$requestId = uniqid('req_', true);
        self::$startTime = microtime(true);

        // Log request start
        $this->logRequestStart($request);

        // Before middleware processing
        $inputBefore = [
            'authenticated' => auth()->check(),
            'session_id' => session()->getId(),
            'session_data' => $this->sanitizeSession(session()->all()),
            'cookies' => $this->sanitizeCookies($request),
            'headers_auth' => $request->header('Authorization') ? '***EXISTS***' : 'NONE',
        ];

        // Process request through the application
        $response = $next($request);

        // After middleware processing
        $inputAfter = [
            'authenticated' => auth()->check(),
            'session_id' => session()->getId(),
            'session_data' => $this->sanitizeSession(session()->all()),
            'response_status' => $response->getStatusCode(),
            'redirect_location' => $response->headers->get('Location'),
        ];

        // Log request end with comprehensive data
        $this->logRequestEnd($request, $response, $inputBefore, $inputAfter);

        return $response;
    }

    private function logRequestStart(Request $request): void
    {
        Log::channel('debug')->info('[ADVANCED_DEBUG] REQUEST_START', [
            'request_id' => self::$requestId,
            'method' => $request->getMethod(),
            'path' => $request->getPathInfo(),
            'url' => $request->url(),
            'query_string' => $request->getQueryString(),
            'authenticated_before' => auth()->check(),
            'session_id' => session()->getId(),
            'session_exists' => session() !== null,
            'has_token_param' => $request->has('token'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('Referer'),
        ]);
    }

    private function logRequestEnd(Request $request, Response $response, array $before, array $after): void
    {
        $duration = (microtime(true) - self::$startTime) * 1000;

        Log::channel('debug')->info('[ADVANCED_DEBUG] REQUEST_END', [
            'request_id' => self::$requestId,
            'method' => $request->getMethod(),
            'path' => $request->getPathInfo(),
            'status_code' => $response->getStatusCode(),
            'duration_ms' => round($duration, 2),
            'before_auth' => $before['authenticated'],
            'after_auth' => $after['authenticated'],
            'before_session' => $before['session_id'],
            'after_session' => $after['session_id'],
            'session_changed' => $before['session_id'] !== $after['session_id'],
            'is_redirect' => $response->getStatusCode() >= 300 && $response->getStatusCode() < 400,
            'redirect_to' => $after['redirect_location'] ?? 'NONE',
            'before_session_data' => array_keys($before['session_data']),
            'after_session_data' => array_keys($after['session_data']),
        ]);

        // Detect redirect loop
        if ($this->isRedirect($response)) {
            $this->detectRedirectLoop($request, $response, $before, $after);
        }
    }

    private function detectRedirectLoop(Request $request, Response $response, array $before, array $after): void
    {
        $redirectTo = $after['redirect_location'] ?? '';

        Log::channel('debug')->warning('[ADVANCED_DEBUG] REDIRECT_DETECTED', [
            'request_id' => self::$requestId,
            'from_url' => $request->url(),
            'redirect_to' => $redirectTo,
            'auth_status_before' => $before['authenticated'],
            'auth_status_after' => $after['authenticated'],
            'session_before' => $before['session_id'],
            'session_after' => $after['session_id'],
            'potential_loop' => $this->isPotentialLoop($request->url(), $redirectTo, $before, $after),
        ]);
    }

    private function isPotentialLoop(string $from, string $to, array $before, array $after): bool
    {
        // Check if we're redirecting to a URL that will redirect back
        $patterns = [
            '/sso/redirect' => '/sso/callback',
            '/sso/callback' => '/sso/redirect',
            '/admin' => '/sso/redirect',
            '/login' => '/sso/redirect',
        ];

        foreach ($patterns as $pattern => $expectedRedirect) {
            if (strpos($from, $pattern) !== false && strpos($to, $expectedRedirect) !== false) {
                // Check if auth state didn't change
                if ($before['authenticated'] === $after['authenticated']) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isRedirect(Response $response): bool
    {
        $code = $response->getStatusCode();
        return $code >= 300 && $code < 400;
    }

    private function sanitizeSession(array $session): array
    {
        $blacklist = ['token', 'password', 'secret', 'key', 'csrf'];
        $sanitized = [];

        foreach ($session as $key => $value) {
            $isSensitive = false;
            foreach ($blacklist as $pattern) {
                if (stripos($key, $pattern) !== false) {
                    $isSensitive = true;
                    break;
                }
            }

            $sanitized[$key] = $isSensitive ? '***REDACTED***' : $value;
        }

        return $sanitized;
    }

    private function sanitizeCookies(Request $request): array
    {
        $cookies = $request->cookies->all();
        $blacklist = ['token', 'session', 'password', 'secret', 'key'];
        $sanitized = [];

        foreach ($cookies as $key => $value) {
            $isSensitive = false;
            foreach ($blacklist as $pattern) {
                if (stripos($key, $pattern) !== false) {
                    $isSensitive = true;
                    break;
                }
            }

            $sanitized[$key] = $isSensitive ? '***REDACTED***' : substr($value, 0, 50);
        }

        return $sanitized;
    }
}
