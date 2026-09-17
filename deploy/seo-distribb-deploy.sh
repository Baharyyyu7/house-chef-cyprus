#!/usr/bin/env bash
set -euo pipefail

# ============================================================
# HouseChef Cyprus — SEO + Distribb Deployment Script
# ============================================================
# Usage: ./deploy/seo-distribb-deploy.sh [--dry-run] [--verify-only]
# ============================================================

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
DRY_RUN=false
VERIFY_ONLY=false

for arg in "$@"; do
    case "$arg" in
        --dry-run) DRY_RUN=true ;;
        --verify-only) VERIFY_ONLY=true ;;
    esac
done

echo "╔═══════════════════════════════════════════════════════════╗"
echo "║  HouseChef Cyprus — SEO + Distribb Deployment           ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""

# ---- Colors ----
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
NC='\033[0m'

pass() { echo -e "${GREEN}✅ PASS${NC}: $1"; }
fail() { echo -e "${RED}❌ FAIL${NC}: $1"; }
warn() { echo -e "${YELLOW}⚠️  WARN${NC}: $1"; }
info() { echo -e "ℹ️  INFO: $1"; }

# ---- Phase 1: Configuration Validation ----
phase1_config() {
    info "Phase 1: Configuration Validation"
    
    # Check .env exists
    if [ -f "$PROJECT_ROOT/.env" ]; then
        pass ".env file exists"
    else
        fail ".env file missing"
        return 1
    fi
    
    # Check SEO config
    if [ -f "$PROJECT_ROOT/config/seo.php" ]; then
        pass "config/seo.php exists"
    else
        fail "config/seo.php missing"
    fi
    
    # Check DISTRIBB_API_KEY is set
    if grep -q "DISTRIBB_API_KEY=" "$PROJECT_ROOT/.env" && ! grep -q "^DISTRIBB_API_KEY=$" "$PROJECT_ROOT/.env"; then
        pass "DISTRIBB_API_KEY configured"
    else
        warn "DISTRIBB_API_KEY not set (get from distribb.io/settings)"
    fi
    
    # Check GSC credentials
    if grep -q "^GOOGLE_SEARCH_CONSOLE_CLIENT_ID=[^']\{20,\}" "$PROJECT_ROOT/.env"; then
        pass "GSC Client ID configured"
    else
        fail "GSC Client ID missing"
    fi
    
    if grep -q "^GOOGLE_SEARCH_CONSOLE_REFRESH_TOKEN=[^']\{20,\}" "$PROJECT_ROOT/.env"; then
        pass "GSC Refresh Token configured"
    else
        fail "GSC Refresh Token missing"
    fi
    
    # Check INDEXNOW
    if grep -q "^INDEXNOW_ENABLED=true" "$PROJECT_ROOT/.env"; then
        pass "IndexNow enabled"
    else
        warn "IndexNow not enabled in .env"
    fi
}

# ---- Phase 2: Production Config Sync ----
phase2_production() {
    info "Phase 2: Production Configuration Sync"
    
    # Check .env.production.template has credentials
    if [ -f "$PROJECT_ROOT/.env.production.template" ]; then
        if grep -q "^GOOGLE_SEARCH_CONSOLE_CLIENT_ID=[^']\{20,\}" "$PROJECT_ROOT/.env.production.template"; then
            pass "Production GSC credentials present"
        else
            warn "Production GSC credentials missing - sync needed"
        fi
    fi
}

# ---- Phase 3: Distribb API Health Check ----
phase3_distribb() {
    info "Phase 3: Distribb API Health Check"
    
    if [ -z "${DISTRIBB_API_KEY:-}" ]; then
        DISTRIBB_API_KEY=$(grep "^DISTRIBB_API_KEY=" "$PROJECT_ROOT/.env" 2>/dev/null | cut -d'=' -f2)
    fi
    
    if [ -n "${DISTRIBB_API_KEY:-}" ]; then
        info "Testing Distribb API connection..."
        RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" \
            -H "Authorization: Bearer $DISTRIBB_API_KEY" \
            "https://api.distribb.io/v1/projects" 2>/dev/null || echo "000")
        
        if [ "$RESPONSE" = "200" ] || [ "$RESPONSE" = "201" ]; then
            pass "Distribb API connected (HTTP $RESPONSE)"
        else
            warn "Distribb API returned HTTP $RESPONSE - check API key validity"
        fi
    else
        warn "DISTRIBB_API_KEY not available - skipping API test"
        info "Get your API key at: https://distribb.io/settings"
    fi
}

# ---- Phase 4: Site Health Check ----
phase4_site() {
    info "Phase 4: Site Health Check"
    
    SITE_URL="${SITE_URL:-https://housechefcyprus.com}"
    
    # HTTP check
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$SITE_URL" 2>/dev/null || echo "000")
    if [ "$HTTP_CODE" = "200" ]; then
        pass "Site responds (HTTP $HTTP_CODE)"
    else
        fail "Site not responding (HTTP $HTTP_CODE)"
    fi
    
    # Sitemap check
    SITEMAP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$SITE_URL/sitemap.xml" 2>/dev/null || echo "000")
    if [ "$SITEMAP_CODE" = "200" ]; then
        pass "Sitemap accessible (HTTP $SITEMAP_CODE)"
    else
        fail "Sitemap not accessible (HTTP $SITEMAP_CODE)"
    fi
    
    # robots.txt check
    ROBOTS_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$SITE_URL/robots.txt" 2>/dev/null || echo "000")
    if [ "$ROBOTS_CODE" = "200" ]; then
        pass "robots.txt accessible (HTTP $ROBOTS_CODE)"
    else
        fail "robots.txt not accessible (HTTP $ROBOTS_CODE)"
    fi
    
    # JSON-LD check on recipe pages
    JSONLD=$(curl -s "$SITE_URL/tarifler" 2>/dev/null | grep -c "application/ld+json" || echo "0")
    if [ "$JSONLD" -gt 0 ] 2>/dev/null; then
        pass "Recipe JSON-LD schema found ($JSONLD instances)"
    else
        warn "No JSON-LD schema found on recipe listing"
    fi
    
    # Hreflang check
    HAS_HREFLANG=$(curl -s "$SITE_URL/sitemap.xml" 2>/dev/null | grep -c "xhtml:link\|xmlns:xhtml" || echo "0")
    if [ "$HAS_HREFLANG" -gt 0 ] 2>/dev/null; then
        pass "Hreflang implemented ($HAS_HREFLANG alternate links)"
    else
        warn "Hreflang not found in sitemap"
    fi
}

# ---- Phase 5: Distribb Slash Commands Check ----
phase5_commands() {
    info "Phase 5: Distribb Slash Commands"
    
    COMMANDS_DIR="$PROJECT_ROOT/.claude/commands"
    if [ -d "$COMMANDS_DIR" ]; then
        COUNT=$(ls "$COMMANDS_DIR"/distribb*.md 2>/dev/null | wc -l | tr -d ' ')
        if [ "$COUNT" -ge "2" ]; then
            pass "Distribb commands installed ($COUNT files)"
        else
            warn "Not all Distribb commands installed"
        fi
    else
        fail ".claude/commands directory missing"
    fi
}

# ---- Phase 6: SEO Services Check ----
phase6_services() {
    info "Phase 6: SEO Services Architecture"
    
    SERVICES_DIR="$PROJECT_ROOT/app/Services/Seo"
    if [ -d "$SERVICES_DIR" ]; then
        SERVICE_COUNT=$(ls "$SERVICES_DIR"/*.php 2>/dev/null | wc -l | tr -d ' ')
        pass "SEO Services: $SERVICE_COUNT services active"
    else
        warn "Services/Seo directory not found"
    fi
    
    if [ -d "$PROJECT_ROOT/app/Services/Seo/Distribb" ]; then
        pass "DistribbIntegrationService active"
    else
        warn "DistribbIntegrationService not found"
    fi
}

# ---- Phase 7: Schema Validation ----
phase7_schema() {
    info "Phase 7: Structured Data Validation"
    
    SITE_URL="${SITE_URL:-https://housechefcyprus.com}"
    
    # Check a recipe page
    RECIPE_URL="$SITE_URL/tarifler"
    JSONLD_COUNT=$(curl -s "$RECIPE_URL" 2>/dev/null | grep -c "application/ld+json" || echo "0")
    
    if [ "$JSONLD_COUNT" -gt "0" ]; then
        pass "Recipe schema active on site"
    else
        warn "Recipe schema may not be rendering - check recipe pages"
    fi
}

# ---- Main Execution ----
echo ""

if [ "$VERIFY_ONLY" = true ]; then
    phase1_config
    phase4_site
    phase5_commands
    phase6_services
    phase7_schema
else
    phase1_config
    phase2_production
    phase3_distribb
    phase4_site
    phase5_commands
    phase6_services
    phase7_schema
fi

echo ""
echo "╔═══════════════════════════════════════════════════════════╗"
echo "║  Deployment check complete                               ║"
echo "║  Next: Add DISTRIBB_API_KEY to start full automation     ║"
echo "╚═══════════════════════════════════════════════════════════╝"
