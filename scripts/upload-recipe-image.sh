#!/usr/bin/env bash
set -euo pipefail

# scripts/upload-recipe-image.sh
# Safely processes a generated image to meet House Chef Cyprus image quality rules:
# 1. Scales and converts to 1920x1620 PNG using macOS sips
# 2. Uploads directly to production media-import/recipes/
# 3. Verifies on server
# 4. Cleans up local copies (AGENTS.md rule)

if [ "$#" -ne 2 ]; then
    echo "Usage: $0 <local_image_path> <recipe_slug>" >&2
    exit 1
fi

LOCAL_INPUT="$1"
SLUG="$2"
SSH_TARGET="u584361532@147.93.93.105"
SSH_PORT="65002"
REMOTE_DIR="/home/u584361532/domains/housechefcyprus.com/shared/storage/app/media-import/recipes"
TMP_PNG="/tmp/recipe--${SLUG}.png"

if [ ! -f "$LOCAL_INPUT" ]; then
    echo "Error: Input file not found: $LOCAL_INPUT" >&2
    exit 1
fi

# Step 1: Scale and convert to 1920x1620 PNG
sips -s format png -z 1620 1920 "$LOCAL_INPUT" --out "$TMP_PNG" >/dev/null 2>&1

# Verify dimensions
WIDTH=$(sips -g pixelWidth "$TMP_PNG" 2>/dev/null | awk '/pixelWidth:/ {print $2}')
HEIGHT=$(sips -g pixelHeight "$TMP_PNG" 2>/dev/null | awk '/pixelHeight:/ {print $2}')

if [ "$WIDTH" -lt 1600 ] || [ "$HEIGHT" -lt 1350 ]; then
    rm -f "$TMP_PNG"
    echo "Error: Scaled dimensions (${WIDTH}x${HEIGHT}) below minimum (1600x1350)" >&2
    exit 2
fi

# Step 2: Upload to production
COPYFILE_DISABLE=1 scp -q -P "$SSH_PORT" "$TMP_PNG" "${SSH_TARGET}:${REMOTE_DIR}/"

# Step 3: Verify on production
if ssh -p "$SSH_PORT" "$SSH_TARGET" "[ -s \"${REMOTE_DIR}/recipe--${SLUG}.png\" ]"; then
    # Step 4: Clean up local copies immediately (AGENTS.md rule)
    rm -f "$TMP_PNG"
    rm -f "$LOCAL_INPUT"
    echo "{\"success\": true, \"slug\": \"${SLUG}\", \"filename\": \"recipe--${SLUG}.png\", \"width\": ${WIDTH}, \"height\": ${HEIGHT}}"
    exit 0
else
    rm -f "$TMP_PNG"
    echo "Error: Verification failed on remote server for recipe--${SLUG}.png" >&2
    exit 3
fi
