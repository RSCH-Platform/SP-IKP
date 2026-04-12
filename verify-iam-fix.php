#!/usr/bin/env php
<?php

require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Foundation\AliasLoader;

$app = require_once __DIR__ . '/bootstrap/app.php';

echo "=" . str_repeat("=", 90) . "\n";
echo "IKP POST-FIX VERIFICATION\n";
echo "=" . str_repeat("=", 90) . "\n\n";

// 1. Configuration Verification
echo "[1] CONFIGURATION VERIFICATION\n";
echo str_repeat("-", 90) . "\n";

$config = [
    'SESSION_DRIVER' => config('session.driver'),
    'IAM_ENABLED' => config('iam.enabled') ? 'YES' : 'NO',
    'IAM_VERIFY_EACH_REQUEST' => env('IAM_VERIFY_EACH_REQUEST'),
    'IAM_VERIFY_REMOTE_EACH_REQUEST' => env('IAM_VERIFY_REMOTE_EACH_REQUEST'),
    'IAM_SYNC_SESSION_LIFETIME' => env('IAM_SYNC_SESSION_LIFETIME'),
    'IAM_ATTACH_VERIFY_MIDDLEWARE' => env('IAM_ATTACH_VERIFY_MIDDLEWARE'),
    'IAM_JWT_LEEWAY' => config('iam.jwt_leeway'),
];

foreach ($config as $key => $value) {
    $value = $value === 1 ? 'TRUE' : ($value === '1' ? 'TRUE' : $value);
    $value = $value === 0 ? 'FALSE' : ($value === '' ? 'FALSE' : $value);
    echo "✓ $key = $value\n";
}

echo "\n";

// 2. Database Verification
echo "[2] DATABASE VERIFICATION\n";
echo str_repeat("-", 90) . "\n";

try {
    $db = \Illuminate\Support\Facades\DB::connection();
    $tables = $db->getSchemaBuilder()->getTables();
    $tableNames = array_column($tables, 'name');

    $sessionTableExists = in_array('sessions', $tableNames);
    echo "Sessions table exists: " . ($sessionTableExists ? "✓ YES" : "✗ NO") . "\n";

    if ($sessionTableExists) {
        $sessionCount = \Illuminate\Support\Facades\DB::table('sessions')->count();
        echo "Sessions in database: $sessionCount\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n";

// 3. Middleware Verification
echo "[3] MIDDLEWARE VERIFICATION\n";
echo str_repeat("-", 90) . "\n";

$bootstrapContent = file_get_contents(__DIR__ . '/bootstrap/app.php');
$hasVerifyMiddleware = strpos($bootstrapContent, 'VerifyIamToken') !== false;
$hasEnforceMiddleware = strpos($bootstrapContent, 'EnforceSessionTimeout') !== false;
$hasRedirectGuests = strpos($bootstrapContent, 'redirectGuestsTo') !== false;

echo "VerifyIamToken middleware: " . ($hasVerifyMiddleware ? "✓ ATTACHED" : "✗ NOT FOUND") . "\n";
echo "EnforceSessionTimeout middleware: " . ($hasEnforceMiddleware ? "✓ ATTACHED" : "✗ NOT FOUND") . "\n";
echo "redirectGuestsTo configured: " . ($hasRedirectGuests ? "✓ YES" : "✗ NO") . "\n";

echo "\n";

// 4. Summary
echo "[4] SUMMARY\n";
echo str_repeat("-", 90) . "\n";

$allGood = (
    config('session.driver') === 'database' &&
    config('iam.enabled') &&
    env('IAM_VERIFY_EACH_REQUEST') &&
    env('IAM_VERIFY_REMOTE_EACH_REQUEST') &&
    env('IAM_SYNC_SESSION_LIFETIME') &&
    env('IAM_ATTACH_VERIFY_MIDDLEWARE') &&
    array_key_exists('sessions', array_column($tables, 'name')) &&
    $hasVerifyMiddleware &&
    $hasEnforceMiddleware &&
    $hasRedirectGuests
);

if ($allGood) {
    echo "✅ ALL CHECKS PASSED!\n";
    echo "\nIKP is now properly configured for IAM authentication:\n";
    echo "  1. ✓ Database sessions enabled (stateful)\n";
    echo "  2. ✓ Token verification middleware attached\n";
    echo "  3. ✓ Session timeout enforcement enabled\n";
    echo "  4. ✓ Session lifetime synced with token TTL\n";
    echo "  5. ✓ Remote verification enabled\n";
    echo "\nThe redirect loop issue should be RESOLVED!\n";
} else {
    echo "❌ SOME CHECKS FAILED\n";
    echo "\nPlease verify:\n";
    if (config('session.driver') !== 'database') {
        echo "  - SESSION_DRIVER should be 'database'\n";
    }
    if (!$hasVerifyMiddleware) {
        echo "  - VerifyIamToken middleware not attached in bootstrap/app.php\n";
    }
    if (!$hasEnforceMiddleware) {
        echo "  - EnforceSessionTimeout middleware not attached in bootstrap/app.php\n";
    }
    if (!$hasRedirectGuests) {
        echo "  - redirectGuestsTo not configured in bootstrap/app.php\n";
    }
}

echo "\n";
echo str_repeat("=", 90) . "\n";
echo "Verification complete.\n";
echo str_repeat("=", 90) . "\n";
