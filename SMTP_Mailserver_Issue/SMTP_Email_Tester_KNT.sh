#!/bin/bash
set -euo pipefail

# ============================================================================
# SMTP EMAIL TESTER SCRIPT
# ============================================================================
# Author: Vishal Kulkarni           Date: 14 FEB 2026
# Description:
# - Tests SMTP email connectivity using internal mail relay server.
# - Sends a test email to verify SMTP server configuration.
# - Uses curl for SMTP communication (no authentication required).
# - Logs test results with timestamp and status.
# - Uses internal relay server at 172.16.13.208:25 (same as PHP scripts).
#
# CHANGELOG:
# ----------------------------------------------------------------------------
# Change Tag | Date       | Author              | Description
# ----------------------------------------------------------------------------
# 1.2        | 2026-02-17 | KEANT Technologies  | Switch to internal relay server
# 1.1        | 2026-02-14 | Vishal Kulkarni     | Use config file for password
# 1.0        | 2026-02-14 | Vishal Kulkarni     | Initial SMTP tester script
# ----------------------------------------------------------------------------

# ============================================================================
# SMTP CONFIGURATION
# ============================================================================

# Using internal mail relay server (same as PHP scripts)
# No authentication required for internal relay
SMTP_SERVER="172.16.13.208"
SMTP_PORT="25"
MAIL_FROM="donotreply@aacanet.org"
MAIL_TO="Vishalkul94@gmail.com, adnan.hajwani.tech@gmail.com, techassistance@aacanet.org"
MAIL_SUBJECT="SMTP Email TESTER Script"
MAIL_BODY="This is a test email sent from SMTP Email Tester Script.
Server: ${SMTP_SERVER}
Time: $(date '+%Y-%m-%d %H:%M:%S')
Purpose: Testing SMTP connectivity and authentication via shell script.

If you receive this email, the SMTP configuration is working correctly."

# ============================================================================
# MAINLINE SCRIPT STARTS HERE
# ============================================================================

echo "==================================================================="
echo "SMTP Email Tester"
echo "==================================================================="
echo "SMTP Server: ${SMTP_SERVER}:${SMTP_PORT}"
echo "From: ${MAIL_FROM}"
echo "To: ${MAIL_TO}"
echo "Subject: ${MAIL_SUBJECT}"
echo "==================================================================="

# Create temporary email file
EMAIL_FILE=$(mktemp)
trap "rm -f ${EMAIL_FILE}" EXIT

# Build email message
cat > "${EMAIL_FILE}" <<EOF
From: ${MAIL_FROM}
To: ${MAIL_TO}
Subject: ${MAIL_SUBJECT}
Content-Type: text/plain; charset=UTF-8

${MAIL_BODY}
EOF

# Send email via SMTP using curl
echo ""
echo "Sending test email..."
if curl --url "smtp://${SMTP_SERVER}:${SMTP_PORT}" \
    --mail-from "${MAIL_FROM}" \
    --mail-rcpt "Vishalkul94@gmail.com" \
    --mail-rcpt "adnan.hajwani.tech@gmail.com" \
    --mail-rcpt "techassistance@aacanet.org" \
    --upload-file "${EMAIL_FILE}" \
    --verbose 2>&1 | tee /tmp/smtp_test.log; then
    
    echo ""
    echo "==================================================================="
    echo "SUCCESS: Test email sent successfully!"
    echo "==================================================================="
    echo "Check the inbox at: ${MAIL_TO}"
    exit 0
else
    echo ""
    echo "==================================================================="
    echo "FAILED: Unable to send test email"
    echo "==================================================================="
    echo "Check /tmp/smtp_test.log for detailed error information"
    exit 1
fi

# ============================================================================
# END OF SCRIPT
# ============================================================================
