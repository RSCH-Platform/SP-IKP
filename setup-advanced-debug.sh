#!/bin/bash

# Advanced IAM Debug Script for IKP
# This script sets up and analyzes detailed IAM flow debugging

set -e

PROJECT_DIR="/home/juni/projects/ikp"
LOG_DIR="$PROJECT_DIR/storage/logs"
DEBUG_LOG="$LOG_DIR/debug.log"

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

print_header() {
    echo -e "\n${CYAN}════════════════════════════════════════════════════════════${NC}"
    echo -e "${CYAN}$1${NC}"
    echo -e "${CYAN}════════════════════════════════════════════════════════════${NC}\n"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

# Clear existing debug logs
clear_debug_logs() {
    if [ -f "$DEBUG_LOG" ]; then
        rm "$DEBUG_LOG"
        print_success "Debug logs cleared"
    fi
}

# Verify debug middlewares are attached
verify_middlewares() {
    print_header "Verifying Debug Middlewares"

    if grep -q "AdvancedDebugMiddleware" "$PROJECT_DIR/bootstrap/app.php"; then
        print_success "AdvancedDebugMiddleware is attached"
    else
        print_error "AdvancedDebugMiddleware is NOT attached"
    fi

    if grep -q "TokenValidationDebugMiddleware" "$PROJECT_DIR/bootstrap/app.php"; then
        print_success "TokenValidationDebugMiddleware is attached"
    else
        print_error "TokenValidationDebugMiddleware is NOT attached"
    fi

    if grep -q "'debug'" "$PROJECT_DIR/config/logging.php"; then
        print_success "Debug log channel is configured"
    else
        print_error "Debug log channel is NOT configured"
    fi
}

# Clear Laravel caches
clear_caches() {
    print_header "Clearing Laravel Caches"
    
    cd "$PROJECT_DIR"
    
    php artisan config:clear 2>&1 | grep -v "^$" || true
    print_success "Config cache cleared"
    
    php artisan cache:clear 2>&1 | grep -v "^$" || true
    print_success "Application cache cleared"
    
    rm -rf bootstrap/cache/* storage/framework/cache/* 2>/dev/null || true
    print_success "Framework cache cleared"
}

# Display boot instructions
show_instructions() {
    print_header "DEBUG BOOT INSTRUCTIONS"
    
    cat << 'EOF'
1. Start IAM Server (if not running):
   cd /home/juni/projects/IAM/laravel-iam
   php artisan serve --port=8010

2. Start IKP Application:
   cd /home/juni/projects/ikp
   php artisan serve --port=8200

3. In a new terminal, watch debug logs in real-time:
   cd /home/juni/projects/ikp
   tail -f storage/logs/debug.log | grep -E "ADVANCED_DEBUG|TOKEN_DEBUG"

4. Trigger the SSO flow:
   - Visit: http://127.0.0.1:8200
   - You should be redirected to IAM login
   - Log in with: email=0000.00000 / password=password

5. Analyze the debug logs:
   cd /home/juni/projects/ikp
   php artisan debug:iam-flow

6. For live monitoring during login:
   php artisan debug:iam-flow --live

EOF
}

# Show debug log locations
show_debug_info() {
    print_header "DEBUG LOG LOCATIONS"
    
    print_info "Full logs:"
    echo "  Laravel: $PROJECT_DIR/storage/logs/laravel.log"
    echo "  Debug:   $PROJECT_DIR/storage/logs/debug.log"
    
    print_info "Real-time view (in separate terminal):"
    echo "  tail -f $PROJECT_DIR/storage/logs/debug.log"
    
    print_info "Filter for specific events:"
    echo "  grep ADVANCED_DEBUG $PROJECT_DIR/storage/logs/debug.log"
    echo "  grep TOKEN_DEBUG $PROJECT_DIR/storage/logs/debug.log"
    echo "  grep REDIRECT_DETECTED $PROJECT_DIR/storage/logs/debug.log"
}

# Main execution
main() {
    clear
    print_header "IAM ADVANCED DEBUG SETUP"
    
    print_info "Setting up advanced debugging for IKP IAM integration..."
    echo ""
    
    verify_middlewares
    clear_debug_logs
    clear_caches
    show_debug_info
    show_instructions
    
    print_success "Advanced debugging is ready!"
    print_info "Make sure to run 'tail -f storage/logs/debug.log' in another terminal"
}

main "$@"
