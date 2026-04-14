#!/bin/bash

# Offline verification of session expiration implementation

echo "════════════════════════════════════════════════════════════════"
echo "     Session Expiration Implementation Verification"
echo "════════════════════════════════════════════════════════════════"
echo ""

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

PASS=0
FAIL=0

test_file() {
    local name=$1
    local path=$2
    if [ -f "$path" ]; then
        echo -e "${GREEN}✓${NC} $name"
        ((PASS++))
    else
        echo -e "${RED}✗${NC} $name (missing: $path)"
        ((FAIL++))
    fi
}

test_content() {
    local name=$1
    local file=$2
    local pattern=$3
    if grep -q "$pattern" "$file" 2>/dev/null; then
        echo -e "${GREEN}✓${NC} $name"
        ((PASS++))
    else
        echo -e "${RED}✗${NC} $name"
        ((FAIL++))
    fi
}

echo -e "${BLUE}[FILES CHECK]${NC}"
test_file "HandleSessionExpiration middleware" "app/Http/Middleware/HandleSessionExpiration.php"
test_file "IamBackchannelLogoutService service" "app/Services/IamBackchannelLogoutService.php"
test_file "BackchannelLogoutApiController controller" "app/Http/Controllers/Api/BackchannelLogoutApiController.php"
test_file "Documentation file" "SESSION_EXPIRATION_AND_BACKCHANNEL_LOGOUT.md"
test_file "Test script" "test-session-expiration.sh"
test_file "Implementation checklist" "IMPLEMENTATION_CHECKLIST_SESSION_EXPIRATION.md"
echo ""

echo -e "${BLUE}[CODE VERIFICATION]${NC}"
test_content "Middleware in bootstrap" "bootstrap/app.php" "HandleSessionExpiration"
test_content "Service imported in LogoutController" "app/Http/Controllers/Auth/LogoutController.php" "IamBackchannelLogoutService"
test_content "Service used in LogoutController" "app/Http/Controllers/Auth/LogoutController.php" "IamBackchannelLogoutService::trigger"
echo ""

echo -e "${BLUE}[CLASS VERIFICATION]${NC}"
php -l app/Http/Middleware/HandleSessionExpiration.php > /dev/null 2>&1 && echo -e "${GREEN}✓${NC} HandleSessionExpiration syntax valid" || echo -e "${RED}✗${NC} HandleSessionExpiration syntax error"
php -l app/Services/IamBackchannelLogoutService.php > /dev/null 2>&1 && echo -e "${GREEN}✓${NC} IamBackchannelLogoutService syntax valid" || echo -e "${RED}✗${NC} IamBackchannelLogoutService syntax error"
php -l app/Http/Controllers/Api/BackchannelLogoutApiController.php > /dev/null 2>&1 && echo -e "${GREEN}✓${NC} BackchannelLogoutApiController syntax valid" || echo -e "${RED}✗${NC} BackchannelLogoutApiController syntax error"
php -l app/Http/Controllers/Auth/LogoutController.php > /dev/null 2>&1 && echo -e "${GREEN}✓${NC} LogoutController syntax valid" || echo -e "${RED}✗${NC} LogoutController syntax error"
echo ""

echo -e "${BLUE}[CONFIGURATION CHECK]${NC}"
if grep -q SESSION_DRIVER .env 2>/dev/null; then
    SESSION_DRIVER=$(grep SESSION_DRIVER .env | cut -d= -f2 | tr -d '\r')
    if [ "$SESSION_DRIVER" = "database" ]; then
        echo -e "${GREEN}✓${NC} SESSION_DRIVER set to database"
    else
        echo -e "${YELLOW}⚠${NC} SESSION_DRIVER is '$SESSION_DRIVER', recommend 'database'"
    fi
else
    echo -e "${YELLOW}⚠${NC} SESSION_DRIVER not set in .env"
fi

if grep -q IAM_ENABLED .env 2>/dev/null; then
    echo -e "${GREEN}✓${NC} IAM configuration found in .env"
else
    echo -e "${YELLOW}⚠${NC} IAM_ENABLED not set in .env"
fi
echo ""

echo "════════════════════════════════════════════════════════════════"
echo -e "Results: ${GREEN}${PASS} passed${NC}, ${RED}${FAIL} failed${NC}"
if [ $FAIL -eq 0 ]; then
    echo -e "${GREEN}✓ All checks passed!${NC}"
else
    echo -e "${RED}✗ Some checks failed${NC}"
fi
echo "════════════════════════════════════════════════════════════════"
