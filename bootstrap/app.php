<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Advanced debugging middleware - logs detailed request/response flow
        $middleware->web(\App\Http\Middleware\AdvancedDebugMiddleware::class);

        // Token validation debugging - logs token structure and validation results
        $middleware->web(\App\Http\Middleware\TokenValidationDebugMiddleware::class);

        // IAM/SSO token verification middleware - check token validity on every web request
        $middleware->web(\Juniyasyos\IamClient\Http\Middleware\VerifyIamToken::class);

        // Enforce session timeout based on token TTL
        $middleware->web(\Juniyasyos\IamClient\Http\Middleware\EnforceSessionTimeout::class);

        // Configure authentication redirects based on IAM/SSO mode
        $middleware->redirectGuestsTo(function () {
            if (config('iam.enabled', false) || env('USE_SSO', false)) {
                return route('iam.sso.login');
            }
            return '/admin';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
