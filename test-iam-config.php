<?php
// Test IAM Configuration pada IKP Client

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\Config;

echo "=" . str_repeat("=", 90) . "\n";
echo "IAM CLIENT CONFIGURATION TEST - IKP\n";
echo "=" . str_repeat("=", 90) . "\n\n";

// 1. Check if IAM is enabled
echo "[1] IAM ENABLED STATUS\n";
echo str_repeat("-", 50) . "\n";
$iamEnabled = config('iam.enabled', false);
$useSso = env('USE_SSO', false);
echo "config('iam.enabled'): " . ($iamEnabled ? 'TRUE' : 'FALSE') . "\n";
echo "env('USE_SSO'): " . ($useSso ? 'TRUE' : 'FALSE') . "\n";
echo "Active: " . (($iamEnabled || $useSso) ? 'YES' : 'NO') . "\n\n";

// 2. Session Configuration
echo "[2] SESSION CONFIGURATION\n";
echo str_repeat("-", 50) . "\n";
$sessionDriver = config('session.driver');
$sessionLifetime = config('session.lifetime');
echo "SESSION_DRIVER: " . $sessionDriver . " (Expected: 'database' for IAM)\n";
echo "SESSION_LIFETIME: " . $sessionLifetime . " minutes\n";
echo "Status: " . (($sessionDriver === 'database') ? '✓ CORRECT' : '✗ WRONG - Should be database') . "\n\n";

// 3. IAM Base Configuration
echo "[3] IAM BASE CONFIGURATION\n";
echo str_repeat("-", 50) . "\n";
$iamConfig = [
    'base_url' => config('iam.base_url'),
    'verify_endpoint' => config('iam.verify_endpoint'),
    'app_key' => config('iam.app_key'),
    'guard' => config('iam.guard'),
    'identifier_field' => config('iam.identifier_field'),
];

foreach ($iamConfig as $key => $value) {
    echo "$key: " . ($value ?? 'NULL') . "\n";
}
echo "\n";

// 4. JWT Token Settings
echo "[4] JWT TOKEN SETTINGS\n";
echo str_repeat("-", 50) . "\n";
$jwtConfig = [
    'jwt_algorithm' => config('iam.jwt_algorithm'),
    'jwt_leeway' => config('iam.jwt_leeway'),
    'jwt_secret' => (config('iam.jwt_secret') ? 'SET' : 'NOT SET'),
];

foreach ($jwtConfig as $key => $value) {
    echo "$key: " . ($value ?? 'NOT SET') . "\n";
}
echo "\n";

// 5. Token Verification Settings - CRITICAL
echo "[5] TOKEN VERIFICATION SETTINGS - CRITICAL!\n";
echo str_repeat("-", 50) . "\n";
$verifyConfig = [
    'IAM_VERIFY_EACH_REQUEST' => env('IAM_VERIFY_EACH_REQUEST', 'NOT SET'),
    'IAM_VERIFY_REMOTE_EACH_REQUEST' => env('IAM_VERIFY_REMOTE_EACH_REQUEST', 'NOT SET'),
    'IAM_ATTACH_VERIFY_MIDDLEWARE' => env('IAM_ATTACH_VERIFY_MIDDLEWARE', 'NOT SET'),
];

foreach ($verifyConfig as $key => $value) {
    echo "$key: " . ($value ?? 'NOT SET') . "\n";
}
echo "\n";

// 6. Session Settings
echo "[6] SESSION & TOKEN MANAGEMENT SETTINGS\n";
echo str_repeat("-", 50) . "\n";
$sessionConfig = [
    'IAM_PRESERVE_SESSION_ID' => env('IAM_PRESERVE_SESSION_ID', 'NOT SET'),
    'IAM_STORE_TOKEN_IN_SESSION' => env('IAM_STORE_TOKEN_IN_SESSION', 'NOT SET'),
    'IAM_REPLACE_SESSION_ON_CALLBACK' => env('IAM_REPLACE_SESSION_ON_CALLBACK', 'NOT SET'),
    'IAM_SYNC_SESSION_LIFETIME' => env('IAM_SYNC_SESSION_LIFETIME', 'NOT SET'),
];

foreach ($sessionConfig as $key => $value) {
    echo "$key: " . ($value ?? 'NOT SET') . "\n";
}
echo "\n";

// 7. Middleware Attachment Check
echo "[7] MIDDLEWARE ATTACHMENT CHECK\n";
echo str_repeat("-", 50) . "\n";
echo "Looking for VerifyIamToken middleware in bootstrap/app.php...\n";
$bootstrapFile = file_get_contents(__DIR__ . '/bootstrap/app.php');
$hasVerifyMiddleware = strpos($bootstrapFile, 'VerifyIamToken') !== false;
$hasEnforceMiddleware = strpos($bootstrapFile, 'EnforceSessionTimeout') !== false;

echo "VerifyIamToken middleware: " . ($hasVerifyMiddleware ? '✓ FOUND' : '✗ NOT FOUND') . "\n";
echo "EnforceSessionTimeout middleware: " . ($hasEnforceMiddleware ? '✓ FOUND' : '✗ NOT FOUND') . "\n\n";

// 8. Comparison with SIIMUT configuration
echo "[8] COMPARISON WITH SIIMUT (WORKING CLIENT)\n";
echo str_repeat("-", 50) . "\n";
echo "IKP vs SIIMUT Differences:\n";
echo "\nIKP Session Driver: $sessionDriver\n";
echo "SIIMUT Session Driver: database (Expected for IAM)\n";
echo "\nIKP Middleware: " . ($hasVerifyMiddleware ? 'ATTACHED' : 'NOT ATTACHED') . "\n";
echo "SIIMUT Middleware: ATTACHED (VerifyIamToken + EnforceSessionTimeout)\n";

echo "\n";

// 9. Summary & Recommendations
echo "[9] SUMMARY & RECOMMENDATIONS\n";
echo str_repeat("-", 50) . "\n";

$issues = [];

if ($sessionDriver !== 'database') {
    $issues[] = "SESSION_DRIVER=cookie - SHOULD BE 'database' for IAM compatibility";
}

if (!$hasVerifyMiddleware) {
    $issues[] = "VerifyIamToken middleware NOT attached to web routes";
}

if (!$hasEnforceMiddleware) {
    $issues[] = "EnforceSessionTimeout middleware NOT attached to web routes";
}

$verifyEachRequest = env('IAM_VERIFY_EACH_REQUEST');
if ($verifyEachRequest === false || $verifyEachRequest === 'false') {
    $issues[] = "IAM_VERIFY_EACH_REQUEST disabled - Token won't be verified";
}

$verifyRemote = env('IAM_VERIFY_REMOTE_EACH_REQUEST');
if ($verifyRemote === true || $verifyRemote === 'true') {
    $issues[] = "IAM_VERIFY_REMOTE_EACH_REQUEST=true - May cause performance issues";
}

if (empty($issues)) {
    echo "✓ NO CRITICAL ISSUES FOUND\n";
} else {
    echo "✗ CRITICAL ISSUES FOUND:\n";
    foreach ($issues as $i => $issue) {
        echo "  " . ($i + 1) . ". $issue\n";
    }
}

echo "\n";
echo "=" . str_repeat("=", 90) . "\n";
echo "END OF TEST\n";
echo "=" . str_repeat("=", 90) . "\n";
