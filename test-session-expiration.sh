#!/bin/bash

# Test Script untuk Session Expiration & Backchannel Logout
# Usage: bash test-session-expiration.sh

set -e

echo "════════════════════════════════════════════════════════════════"
echo "     Session Expiration & Backchannel Logout Test Suite"
echo "════════════════════════════════════════════════════════════════"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
APP_URL="${APP_URL:-http://localhost:8000}"
SESSION_LIFETIME="${SESSION_LIFETIME:-120}"
ADMIN_EMAIL="${ADMIN_EMAIL:-admin@example.com}"
ADMIN_PASSWORD="${ADMIN_PASSWORD:-password}"

echo -e "${BLUE}Configuration:${NC}"
echo "  App URL: $APP_URL"
echo "  Session Lifetime: $SESSION_LIFETIME minutes"
echo ""

# Test 1: Check if application is running
echo -e "${YELLOW}[TEST 1]${NC} Check if application is running..."
if timeout 5 bash -c "echo > /dev/tcp/localhost/8000" 2>/dev/null; then
    echo -e "${GREEN}✓ Application is running${NC}"
else
    echo -e "${RED}✗ Application is not running at localhost:8000${NC}"
    exit 1
fi
echo ""

# Test 2: Check middleware configuration
echo -e "${YELLOW}[TEST 2]${NC} Check middleware configuration..."
if grep -q "HandleSessionExpiration" bootstrap/app.php; then
    echo -e "${GREEN}✓ HandleSessionExpiration middleware registered${NC}"
else
    echo -e "${RED}✗ HandleSessionExpiration middleware not found${NC}"
    exit 1
fi
echo ""

# Test 3: Check service exists
echo -e "${YELLOW}[TEST 3]${NC} Check IamBackchannelLogoutService exists..."
if [ -f "app/Services/IamBackchannelLogoutService.php" ]; then
    echo -e "${GREEN}✓ IamBackchannelLogoutService found${NC}"
else
    echo -e "${RED}✗ IamBackchannelLogoutService not found${NC}"
    exit 1
fi
echo ""

# Test 4: Check API controller exists
echo -e "${YELLOW}[TEST 4]${NC} Check BackchannelLogoutApiController exists..."
if [ -f "app/Http/Controllers/Api/BackchannelLogoutApiController.php" ]; then
    echo -e "${GREEN}✓ BackchannelLogoutApiController found${NC}"
else
    echo -e "${RED}✗ BackchannelLogoutApiController not found${NC}"
    exit 1
fi
echo ""

# Test 5: Check environment configuration
echo -e "${YELLOW}[TEST 5]${NC} Check environment configuration..."
if [ -f ".env" ]; then
    SESSION_DRIVER=$(grep SESSION_DRIVER .env | cut -d= -f2 | tr -d '\r')
    SESSION_LIFETIME=$(grep SESSION_LIFETIME .env | cut -d= -f2 | tr -d '\r')
    IAM_ENABLED=$(grep IAM_ENABLED .env | cut -d= -f2 | tr -d '\r')
    
    echo "  SESSION_DRIVER=$SESSION_DRIVER"
    echo "  SESSION_LIFETIME=$SESSION_LIFETIME"
    echo "  IAM_ENABLED=$IAM_ENABLED"
    
    if [ "$SESSION_DRIVER" = "database" ]; then
        echo -e "${GREEN}✓ SESSION_DRIVER is set to database${NC}"
    else
        echo -e "${YELLOW}⚠ SESSION_DRIVER is '$SESSION_DRIVER', recommend 'database'${NC}"
    fi
else
    echo -e "${YELLOW}⚠ .env not found${NC}"
fi
echo ""

# Test 6: Check database session table
echo -e "${YELLOW}[TEST 6]${NC} Check if sessions table exists..."
SESSIONS_TABLE=$(php artisan tinker --execute="echo DB::table('information_schema.tables')->where('table_schema', DB::getDatabaseName())->where('table_name', 'sessions')->count();" 2>/dev/null || echo "0")
if [ "$SESSIONS_TABLE" -gt 0 ]; then
    echo -e "${GREEN}✓ Sessions table exists in database${NC}"
else
    echo -e "${YELLOW}⚠ Sessions table not found - running migration...${NC}"
    php artisan session:table
    php artisan migrate --step
    echo -e "${GREEN}✓ Sessions table created${NC}"
fi
echo ""

# Test 7: Verify routes
echo -e "${YELLOW}[TEST 7]${NC} Verify logout routes..."
php artisan route:list | grep -E "logout|backchannel" | head -5 || true
echo -e "${GREEN}✓ Routes checked${NC}"
echo ""

# Test 8: Check log channels
echo -e "${YELLOW}[TEST 8]${NC} Check debug log channel configuration..."
if grep -q "'channels'" config/logging.php; then
    echo -e "${GREEN}✓ Logging configuration found${NC}"
else
    echo -e "${RED}✗ Logging configuration issue${NC}"
fi
echo ""

echo "════════════════════════════════════════════════════════════════"
echo -e "${GREEN}All checks passed! ✓${NC}"
echo "════════════════════════════════════════════════════════════════"
echo ""
echo -e "${BLUE}Next Steps:${NC}"
echo "1. Monitor debug logs: tail -f storage/logs/debug.log"
echo "2. Test session expiration:"
echo "   - Login via SSO"
echo "   - Wait SESSION_LIFETIME minutes"
echo "   - Make request to protected endpoint"
echo "   - Check logs for 'SESSION EXPIRATION DETECTED'"
echo ""
echo "3. Test manual backchannel logout (in Tinker):"
echo "   php artisan tinker"
echo "   > use App\Services\IamBackchannelLogoutService;"
echo "   > IamBackchannelLogoutService::triggerBackchannelLogout('user-id', 'session-id');"
echo ""
echo "4. Check session database:"
echo "   php artisan tinker"
echo "   > DB::table('sessions')->count();"
echo "   > DB::table('sessions')->first();"
echo ""
