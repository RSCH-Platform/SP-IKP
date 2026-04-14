<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Juniyasyos\IamClient\Services\UserApplicationsService;
use Juniyasyos\IamClient\Support\IamConfig;
use App\Services\IamBackchannelLogoutService;
class LogoutController extends Controller
{
    public function __invoke()
    {
        // Check if SSO is enabled
        $ssoEnabled = config('iam.enabled', false) || env('USE_SSO', false);

        Log::channel('debug')->info('=== IKP LOGOUT CONTROLLER INVOKED ===', [
            'sso_enabled' => $ssoEnabled,
            'timestamp' => now()->toIso8601String(),
            'url' => request()->url(),
            'method' => request()->method(),
        ]);

        if ($ssoEnabled) {
            // Use IAM logout when SSO is enabled
            return $this->handleSSOLogout();
        }

        // Non-SSO logout - redirect to filament panel
        return $this->handleLocalLogout();
    }

    /**
     * Handle SSO logout by redirecting to IAM server
     * This triggers global logout across all IAM client apps
     */
    private function handleSSOLogout()
    {
        $guardName = IamConfig::guardName('web');
        $guardInstance = Auth::guard($guardName);

        $userId = $guardInstance->id();
        $sessionId = session()->getId();

        Log::channel('debug')->info('=== SSO LOGOUT INITIATED (PHASE 1: Pre-Cleanup) ===', [
            'user_id' => $userId,
            'session_id' => $sessionId,
            'guard' => $guardName,
            'auth_user' => Auth::user()?->getAttributes() ?? null,
            'session_data_keys' => array_keys(session()->all()),
        ]);

        Log::info('SSO logout initiated', [
            'user_id' => $userId,
            'session_id' => $sessionId,
            'guard' => $guardName,
        ]);

        // Clear application cache before logout
        Log::channel('debug')->info('Clearing UserApplicationsService cache...', [
            'user_id' => $userId,
        ]);
        
        $userAppsClearResult = UserApplicationsService::clearUserAppCache($userId);
        $sessionAppsClearResult = UserApplicationsService::clearSessionAppCache();

        Log::channel('debug')->info('Cache clearing results', [
            'user_apps_cleared' => $userAppsClearResult,
            'session_apps_cleared' => $sessionAppsClearResult,
        ]);

        // Trigger backchannel logout ke IAM (optional, as fallback)
        // The main logout akan di-handle IAM server setelah redirect
        if ($userId && $sessionId) {
            IamBackchannelLogoutService::triggerBackchannelLogout($userId, $sessionId);
        }

        // Logout from guard
        Log::channel('debug')->info('Logging out from guard...', [
            'guard' => $guardName,
        ]);
        $guardInstance->logout();

        // Invalidate session
        Log::channel('debug')->info('Invalidating session...', [
            'old_session_id' => $sessionId,
        ]);
        request()->session()->invalidate();

        // Regenerate token
        Log::channel('debug')->info('Regenerating CSRF token...');
        request()->session()->regenerateToken();

        // Forget IAM session keys
        Log::channel('debug')->info('Forgetting IAM session keys...');
        request()->session()->forget('iam');

        $newSessionId = session()->getId();

        Log::channel('debug')->info('=== SSO LOGOUT COMPLETED (PHASE 2: Post-Cleanup) ===', [
            'previous_user_id' => $userId,
            'old_session_id' => $sessionId,
            'new_session_id' => $newSessionId,
            'guard' => $guardName,
            'remaining_session_keys' => array_keys(session()->all()),
        ]);

        Log::info('SSO logout completed', [
            'previous_user_id' => $userId,
            'old_session_id' => $sessionId,
            'new_session_id' => $newSessionId,
            'guard' => $guardName,
        ]);

        // Prepare redirect to IAM server
        $iamBase = trim((string) IamConfig::baseUrl());

        Log::channel('debug')->info('=== SSO LOGOUT PHASE 3: Prepare Redirect ===', [
            'iam_base_url' => $iamBase,
        ]);

        if ($iamBase === '') {
            Log::channel('debug')->warning('IAM base URL is empty, using fallback redirect');

            $redirectRouteName = IamConfig::logoutRedirectRoute('web');

            if ($redirectRouteName && Route::has($redirectRouteName)) {
                $redirectUrl = route($redirectRouteName);
                Log::channel('debug')->info('Redirecting to logout route', [
                    'route_name' => $redirectRouteName,
                    'url' => $redirectUrl,
                ]);
                return redirect()->route($redirectRouteName)->with('message', 'You have been logged out successfully.');
            }

            $fallbackUrl = IamConfig::guardRedirect('web');
            Log::channel('debug')->info('Redirecting to guard default redirect', [
                'url' => $fallbackUrl,
            ]);
            return redirect($fallbackUrl)->with('message', 'You have been logged out successfully.');
        }

        // Redirect to IAM server logout endpoint, which will trigger logout chain
        // across all IAM client applications
        $iamLogoutUrl = rtrim($iamBase, '/') . '/logout';

        Log::channel('debug')->info('=== SSO LOGOUT PHASE 4: Redirect To IAM Server ===', [
            'iam_logout_url' => $iamLogoutUrl,
            'iam_base_url' => $iamBase,
            'message' => 'User will be redirected to IAM server. IAM will trigger backchannel logout to all client apps.',
        ]);

        Log::info('Redirecting to IAM logout endpoint', [
            'iam_logout_url' => $iamLogoutUrl,
        ]);

        return redirect()->away($iamLogoutUrl);
    }

    /**
     * Handle local logout without SSO (redirect using relative path)
     */
    private function handleLocalLogout()
    {
        Log::channel('debug')->info('=== LOCAL LOGOUT (NON-SSO) ===', [
            'timestamp' => now()->toIso8601String(),
        ]);

        Auth::guard('web')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        Log::info('Local logout completed');
        Log::channel('debug')->info('Local logout completed, redirecting to admin panel');

        // Use relative path to filament admin panel
        return redirect(config('filament.path', '/ikp-application'))->with('message', 'You have been logged out successfully.');
    }
}
