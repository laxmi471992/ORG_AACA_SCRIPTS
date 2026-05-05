#!/usr/bin/env pwsh
Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

# ============================================================================
# SMTP EMAIL TESTER SCRIPT
# ============================================================================
# Author: Vishal Kulkarni           Date: 14 FEB 2026
# Description:
# - Tests SMTP email connectivity using internal mail relay server.
# - Sends a test email to verify SMTP server configuration.
# - Uses internal relay server at 172.16.13.208:25 (same as PHP scripts).
# - Logs test results with timestamp and status.
#
# CHANGELOG:
# ----------------------------------------------------------------------------
# Change Tag | Date       | Author              | Description
# ----------------------------------------------------------------------------
# 1.3        | 2026-02-28 | KEANT Technologies  | PowerShell version
# 1.2        | 2026-02-17 | KEANT Technologies  | Switch to internal relay server
# 1.1        | 2026-02-14 | Vishal Kulkarni     | Use config file for password
# 1.0        | 2026-02-14 | Vishal Kulkarni     | Initial SMTP tester script
# ----------------------------------------------------------------------------

# ============================================================================
# SMTP CONFIGURATION
# ============================================================================

# Using internal mail relay server (same as PHP scripts)
# No authentication required for internal relay
$SMTP_SERVER = '172.16.13.208'
$SMTP_PORT = 25
$MAIL_FROM = 'donotreply@aacanet.org'
$MAIL_TO = 'Vishalkul94@gmail.com, adnan.hajwani.tech@gmail.com, techassistance@aacanet.org'
$MAIL_TO_LIST = @(
    'Vishalkul94@gmail.com'
    'adnan.hajwani.tech@gmail.com'
    'techassistance@aacanet.org'
)
$MAIL_SUBJECT = 'SMTP Email TESTER Script'
$MAIL_BODY = @"
This is a test email sent from SMTP Email Tester Script.
Server: $SMTP_SERVER
Time: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')
Purpose: Testing SMTP connectivity and authentication via shell script.

If you receive this email, the SMTP configuration is working correctly.
"@

# ============================================================================
# MAINLINE SCRIPT STARTS HERE
# ============================================================================

Write-Host '==================================================================='
Write-Host 'SMTP Email Tester'
Write-Host '==================================================================='
Write-Host "SMTP Server: $SMTP_SERVER`:$SMTP_PORT"
Write-Host "From: $MAIL_FROM"
Write-Host "To: $MAIL_TO"
Write-Host "Subject: $MAIL_SUBJECT"
Write-Host '==================================================================='

$EMAIL_FILE = [System.IO.Path]::GetTempFileName()
$LOG_FILE = Join-Path ([System.IO.Path]::GetTempPath()) 'smtp_test.log'

try {
    # Build email message
    $emailContent = @"
From: $MAIL_FROM
To: $MAIL_TO
Subject: $MAIL_SUBJECT
Content-Type: text/plain; charset=UTF-8

$MAIL_BODY
"@
    Set-Content -Path $EMAIL_FILE -Value $emailContent -Encoding UTF8

    Write-Host ''
    Write-Host 'Sending test email...'

    $mailMessage = New-Object System.Net.Mail.MailMessage
    $mailMessage.From = $MAIL_FROM
    foreach ($recipient in $MAIL_TO_LIST) {
        [void]$mailMessage.To.Add($recipient)
    }
    $mailMessage.Subject = $MAIL_SUBJECT
    $mailMessage.Body = $MAIL_BODY
    $mailMessage.IsBodyHtml = $false
    $mailMessage.BodyEncoding = [System.Text.Encoding]::UTF8
    $mailMessage.SubjectEncoding = [System.Text.Encoding]::UTF8

    $smtpClient = New-Object System.Net.Mail.SmtpClient($SMTP_SERVER, $SMTP_PORT)
    $smtpClient.DeliveryMethod = [System.Net.Mail.SmtpDeliveryMethod]::Network
    $smtpClient.EnableSsl = $false
    $smtpClient.UseDefaultCredentials = $false

    $smtpClient.Send($mailMessage)

    "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] SUCCESS: Test email sent successfully to $MAIL_TO" |
        Set-Content -Path $LOG_FILE -Encoding UTF8

    Write-Host ''
    Write-Host '==================================================================='
    Write-Host 'SUCCESS: Test email sent successfully!'
    Write-Host '==================================================================='
    Write-Host "Check the inbox at: $MAIL_TO"
    exit 0
}
catch {
    $errorMessage = $_.Exception.Message
    "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] FAILED: Unable to send test email" |
        Set-Content -Path $LOG_FILE -Encoding UTF8
    "Error: $errorMessage" | Add-Content -Path $LOG_FILE -Encoding UTF8

    Write-Host ''
    Write-Host '==================================================================='
    Write-Host 'FAILED: Unable to send test email'
    Write-Host '==================================================================='
    Write-Host "Check $LOG_FILE for detailed error information"
    exit 1
}
finally {
    if (Test-Path -Path $EMAIL_FILE) {
        Remove-Item -Path $EMAIL_FILE -Force
    }
}

# ============================================================================
# END OF SCRIPT
# ============================================================================
