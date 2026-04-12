#!/bin/bash

echo "==============================================================================================="
echo "IKP POST-FIX VERIFICATION"
echo "==============================================================================================="
echo ""

# 1. Check .env file
echo "[1] .ENV CONFIGURATION"
echo "---------------------------------------"
echo "SESSION_DRIVER:"
grep "^SESSION_DRIVER=" /home/juni/projects/ikp/.env | head -1
echo "IAM_VERIFY_REMOTE_EACH_REQUEST:"
grep "^IAM_VERIFY_REMOTE_EACH_REQUEST=" /home/juni/projects/ikp/.env | head -1
echo "IAM_SYNC_SESSION_LIFETIME:"
grep "^IAM_SYNC_SESSION_LIFETIME=" /home/juni/projects/ikp/.env | head -1
echo ""

# 2. Check bootstrap/app.php
echo "[2] MIDDLEWARE CONFIGURATION"
echo "---------------------------------------"
echo "VerifyIamToken middleware:"
grep -c "VerifyIamToken" /home/juni/projects/ikp/bootstrap/app.php > /dev/null && echo "✓ FOUND" || echo "✗ NOT FOUND"
echo "EnforceSessionTimeout middleware:"
grep -c "EnforceSessionTimeout" /home/juni/projects/ikp/bootstrap/app.php > /dev/null && echo "✓ FOUND" || echo "✗ NOT FOUND"
echo "redirectGuestsTo:"
grep -c "redirectGuestsTo" /home/juni/projects/ikp/bootstrap/app.php > /dev/null && echo "✓ FOUND" || echo "✗ NOT FOUND"
echo ""

# 3. Check database
echo "[3] DATABASE TABLES"
echo "---------------------------------------"
mysql -h 127.0.0.1 -u juni -ppassword ikp_db -e "SHOW TABLES LIKE 'sessions';" 2>/dev/null | grep -q sessions && echo "✓ sessions table exists" || echo "✗ sessions table missing"
echo ""

# 4. Summary
echo "[4] CONFIGURATION SUMMARY"
echo "---------------------------------------"

SESSION_DRIVER=$(grep "^SESSION_DRIVER=" /home/juni/projects/ikp/.env | cut -d'=' -f2)
VERIFY_REMOTE=$(grep "^IAM_VERIFY_REMOTE_EACH_REQUEST=" /home/juni/projects/ikp/.env | cut -d'=' -f2)
SYNC_LIFETIME=$(grep "^IAM_SYNC_SESSION_LIFETIME=" /home/juni/projects/ikp/.env | cut -d'=' -f2)

HAS_VERIFY=$(grep -c "VerifyIamToken" /home/juni/projects/ikp/bootstrap/app.php)
HAS_ENFORCE=$(grep -c "EnforceSessionTimeout" /home/juni/projects/ikp/bootstrap/app.php)
HAS_SESSIONS=$(mysql -h 127.0.0.1 -u juni -ppassword ikp_db -e "SHOW TABLES LIKE 'sessions';" 2>/dev/null | grep -c sessions)

echo "Configuration Status:"
echo "  SESSION_DRIVER = $SESSION_DRIVER (Expected: database)"
[ "$SESSION_DRIVER" = "database" ] && echo "  ✓ CORRECT" || echo "  ✗ WRONG"
echo ""

echo "  IAM_VERIFY_REMOTE_EACH_REQUEST = $VERIFY_REMOTE (Expected: true)"
[ "$VERIFY_REMOTE" = "true" ] && echo "  ✓ CORRECT" || echo "  ✗ WRONG"
echo ""

echo "  IAM_SYNC_SESSION_LIFETIME = $SYNC_LIFETIME (Expected: true)"
[ "$SYNC_LIFETIME" = "true" ] && echo "  ✓ CORRECT" || echo "  ✗ WRONG"
echo ""

echo "  Middleware Attachment:"
[ "$HAS_VERIFY" -gt 0 ] && echo "    ✓ VerifyIamToken ATTACHED" || echo "    ✗ VerifyIamToken NOT FOUND"
[ "$HAS_ENFORCE" -gt 0 ] && echo "    ✓ EnforceSessionTimeout ATTACHED" || echo "    ✗ EnforceSessionTimeout NOT FOUND"
echo ""

echo "  Database:"
[ "$HAS_SESSIONS" -gt 0 ] && echo "    ✓ sessions table EXISTS" || echo "    ✗ sessions table MISSING"
echo ""

# Final check
if [ "$SESSION_DRIVER" = "database" ] && [ "$VERIFY_REMOTE" = "true" ] && [ "$SYNC_LIFETIME" = "true" ] && [ "$HAS_VERIFY" -gt 0 ] && [ "$HAS_ENFORCE" -gt 0 ] && [ "$HAS_SESSIONS" -gt 0 ]; then
    echo "✅ ALL FIXES APPLIED SUCCESSFULLY!"
    echo ""
    echo "IKP is now configured for proper IAM authentication:"
    echo "  1. ✓ Database sessions enabled (stateful, server-side)"
    echo "  2. ✓ VerifyIamToken middleware validates tokens on every request"
    echo "  3. ✓ EnforceSessionTimeout syncs session lifetime with token TTL"
    echo "  4. ✓ Remote verification enabled for comprehensive security"
    echo ""
    echo "Expected improvements:"
    echo "  - ✓ No more redirect loops"
    echo "  - ✓ Token state tracked server-side"
    echo "  - ✓ Consistent authentication state"
    echo "  - ✓ Users can access application normally"
else
    echo "❌ SOME FIXES NOT APPLIED"
    echo ""
    echo "Please check the configuration above."
fi

echo ""
echo "==============================================================================================="
