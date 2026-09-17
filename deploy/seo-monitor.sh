#!/usr/bin/env bash
# HouseChef Cyprus — SEO Monitoring Script
# Run weekly via cron: 0 9 * * 1 /path/to/seo-monitor.sh

SITE_URL="https://housechefcyprus.com"
DATE=$(date +%Y-%m-%d)
REPORT_FILE="/tmp/housechef-seo-report-$DATE.txt"

echo "=== HouseChef Cyprus SEO Report: $DATE ===" > "$REPORT_FILE"
echo "URL: $SITE_URL" >> "$REPORT_FILE"
echo "Date: $(date)" >> "$REPORT_FILE"
echo "" >> "$REPORT_FILE"

# Site health
echo "--- SITE HEALTH ---" >> "$REPORT_FILE"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$SITE_URL")
echo "HTTP Status: $HTTP_CODE" >> "$REPORT_FILE"

# Sitemap
echo "--- SITEMAP ---" >> "$REPORT_FILE"
curl -s "$SITE_URL/sitemap.xml" | grep -c '<url>' | xargs echo "Total URLs:" >> "$REPORT_FILE"
curl -s "$SITE_URL/sitemaps/recipes.xml" | grep -c '<url>' | xargs echo "Recipe URLs:" >> "$REPORT_FILE"
curl -s "$SITE_URL/sitemaps/blog.xml" | grep -c '<url>' | xargs echo "Blog URLs:" >> "$REPORT_FILE"

# Schema
echo "--- SCHEMA ---" >> "$REPORT_FILE"
curl -s "$SITE_URL/tarifler" | grep -o 'application/ld+json' | wc -l | xargs echo "JSON-LD instances:" >> "$REPORT_FILE"

# IndexNow
echo "--- INDEXNOW ---" >> "$REPORT_FILE"
curl -s -o /dev/null -w "%{http_code}" "$SITE_URL/.well-known/key.txt" | xargs echo "Key.txt:" >> "$REPORT_FILE"

# English
echo "--- ENGLISH ---" >> "$REPORT_FILE"
curl -s -o /dev/null -w "%{http_code}" "$SITE_URL/en/" | xargs echo "English site:" >> "$REPORT_FILE"

# GSC verification files
echo "--- GSC VERIFICATION ---" >> "$REPORT_FILE"
ls /Users/kaganbahar/Documents/Yemek\ Projesi/public/google*.html 2>/dev/null | xargs -I{} basename {} >> "$REPORT_FILE"

# Distribb config
echo "--- DISTRIBB ---" >> "$REPORT_FILE"
grep "DISTRIBB_API_KEY" /Users/kaganbahar/Documents/Yemek\ Projesi/.env 2>/dev/null | sed 's/=.*/=***HIDDEN***/' >> "$REPORT_FILE"
grep "DISTRIBB_API_ENABLED" /Users/kaganbahar/Documents/Yemek\ Projesi/.env 2>/dev/null >> "$REPORT_FILE"

# SEO services
echo "--- SEO SERVICES ---" >> "$REPORT_FILE"
ls /Users/kaganbahar/Documents/Yemek\ Projesi/app/Services/Seo/*.php 2>/dev/null | wc -l | xargs echo "Services count:" >> "$REPORT_FILE"

echo "" >> "$REPORT_FILE"
echo "=== Report saved to $REPORT_FILE ==="
cat "$REPORT_FILE"
