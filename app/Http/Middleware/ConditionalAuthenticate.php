<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConditionalAuthenticate
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return $next($request);
            }
        }

        // Check if SSO/IAM is enabled
        $ssoEnabled = config('iam.enabled', false) || env('USE_SSO', false);

        if ($ssoEnabled) {
            // Redirect to IAM SSO login
            return redirect(config('iam.login_route', '/sso/login'));
        }

        // Default behavior - redirect to Filament login
        return redirect(route('filament.ikp-application.auth.login'));
    }
}
