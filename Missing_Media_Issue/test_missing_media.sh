#!/bin/bash
set -euo pipefail

#===============================================================================
# Wrapper test for document_loading_KNT.sh and downloadfile_knt.php
# Author: KEANT Technologies
# Description: Runs a direct SFTP fetch and an HTTP download test using the same
#              remote path and local filename so you can validate both flows.
# Usage:
#   bash test_download_wrapper.sh        # Runs both SFTP and HTTP tests
#   bash test_download_wrapper.sh sftp   # Runs only the direct SFTP test
#   bash test_download_wrapper.sh http   # Runs only the HTTP download test
#   bash test_download_wrapper.sh both   # Runs both tests explicitly
#
# Expected output:
#   SFTP success: "[SFTP] Success: /var/www/html/bi/dist/BAYREADFILE/<file>"
#   SFTP failure: "[SFTP] Failed: file not found after download"
#   HTTP success: "[HTTP] Success: /tmp/<file>"
#   HTTP failure: "[HTTP] Failed: empty or missing file"
#===============================================================================


RAW_URL="https://pipeway.aacanet.org/bi/dist/Report/downloadfile_knt.php?nama=98035172_CONT_00000000_D07E5A96-0000-CF19-B944-141FAE52850E_20250425.PDF&&remote=%2F%2FBAY%2Fjudmedia%2F98035172_CONT_00000000_D07E5A96-0000-CF19-B944-141FAE52850E_20250425.PDF"

# Split the raw URL into base URL and query params
HTTP_URL="${RAW_URL%%\?*}"
QUERY="${RAW_URL#*\?}"
QUERY="${QUERY//&&/&}"

# Ensure the wrapper targets the KNT endpoint
if [ "${HTTP_URL##*/}" != "downloadfile_knt.php" ]; then
  echo "Error: RAW_URL must point to downloadfile_knt.php" >&2
  exit 2
fi

# Extract nama and remote (decode remote for SFTP and keep encoded for HTTP)
LOCAL_NAME="${QUERY#*nama=}";
LOCAL_NAME="${LOCAL_NAME%%&*}"
REMOTE_PARAM="${QUERY#*remote=}";
REMOTE_PARAM="${REMOTE_PARAM%%&*}"
REMOTE_PATH=$(printf '%b' "${REMOTE_PARAM//%/\\x}")

# Normalize BAY path to E: drive mapping when needed
if [ "${REMOTE_PATH#"/BAY/judmedia/"}" != "$REMOTE_PATH" ]; then
  REMOTE_PATH="/E:/media/judmedia/${REMOTE_PATH#"/BAY/judmedia/"}"
  REMOTE_PARAM="${REMOTE_PATH//:/%3A}"
  REMOTE_PARAM="${REMOTE_PARAM//\//%2F}"
fi

CRON_SCRIPT="/var/www/html/cron/document_loading_KNT.sh"
DEST_DIR="/var/www/html/bi/dist/BAYREADFILE"

# Usage helper for invalid mode input
usage() {
  echo "Usage: $0 [http|sftp|both]" >&2
  exit 2
}

# Parse mode (defaults to running both tests)
mode="${1:-both}"
if [ "$mode" != "http" ] && [ "$mode" != "sftp" ] && [ "$mode" != "both" ]; then
  usage
fi

# Direct SFTP test: runs the cron script and checks local output
if [ "$mode" = "sftp" ] || [ "$mode" = "both" ]; then
  echo "[SFTP] Running direct download test..."
  if [ ! -x "$CRON_SCRIPT" ]; then
    echo "[SFTP] Script not executable or missing: $CRON_SCRIPT" >&2
    exit 1
  fi

  "$CRON_SCRIPT" "$REMOTE_PATH" "$LOCAL_NAME"
  if [ -f "$DEST_DIR/$LOCAL_NAME" ]; then
    echo "[SFTP] Success: $DEST_DIR/$LOCAL_NAME"
  else
    echo "[SFTP] Failed: file not found after download" >&2
    exit 1
  fi
fi

# HTTP test: hits downloadfile.php and verifies the downloaded payload
if [ "$mode" = "http" ] || [ "$mode" = "both" ]; then
  echo "[HTTP] Running downloadfile_knt.php test..."
  curl -f -o "/tmp/$LOCAL_NAME" \
    "${HTTP_URL}?nama=${LOCAL_NAME}&remote=${REMOTE_PARAM}"

  if [ -s "/tmp/$LOCAL_NAME" ]; then
    echo "[HTTP] Success: /tmp/$LOCAL_NAME"
  else
    echo "[HTTP] Failed: empty or missing file" >&2
    exit 1
  fi
fi

echo "Done."
