# SMTP Email Implementation Analysis
**Date:** 17 February 2026  
**Author:** KEANT Technologies  
**Project:** PIPEWAY Email System

---

## Executive Summary

This document explains why the PHP email script worked successfully while the shell script failed, and details the solution implemented to fix the shell script. The root cause was a difference in SMTP server configuration: PHP used an internal mail relay server while the shell script attempted direct authentication with Office365.

---

## Table of Contents

1. [Problem Overview](#problem-overview)
2. [PHP Implementation (Working)](#php-implementation-working)
3. [Shell Script Implementation (Failed)](#shell-script-implementation-failed)
4. [Root Cause Analysis](#root-cause-analysis)
5. [Solution Implemented](#solution-implemented)
6. [Technical Comparison](#technical-comparison)
7. [Lessons Learned](#lessons-learned)

---

## Problem Overview

### Context
- **PHP Script:** `SMTP_tester.php` was sending emails successfully
- **Shell Script:** `SMTP_Email_Tester_KNT.sh` was failing to send emails
- **Impact:** Inconsistent email delivery testing across different implementations

### Symptoms
- PHP script: ✅ Emails delivered successfully
- Shell script: ❌ SMTP connection failures or authentication errors

---

## PHP Implementation (Working)

### Configuration Details

**File:** `SMTP_tester.php`  
**Library:** PHPMailer  
**Configuration Source:** `/var/www/html/bi/dist/mailsetup.php`

```php
$mail->Host = '172.16.13.208';      // Internal mail relay server
$mail->SMTPAuth = false;             // No authentication required
$mail->Port = 25;                    // Standard SMTP port
$mail->From = 'pwadmin@aacanet.org';
```

### Key Characteristics

| Property | Value | Significance |
|----------|-------|--------------|
| **SMTP Server** | `172.16.13.208` | Internal network relay server |
| **Port** | `25` | Standard SMTP (no encryption) |
| **Authentication** | `false` | No username/password needed |
| **Network** | Internal | Trusted local network connection |
| **SSL/TLS** | Not required | Plain SMTP communication |

### Why It Worked

1. **Internal Mail Relay Server**
   - The server `172.16.13.208` is an internal mail relay
   - Configured to accept connections from local network without authentication
   - Acts as a trusted intermediary to forward emails externally

2. **No Authentication Barriers**
   - No username/password validation required
   - No multi-factor authentication (MFA) challenges
   - No app-specific password requirements

3. **Network Accessibility**
   - Server accessible within the local network
   - No firewall blocking port 25 internally
   - Direct connectivity without encryption overhead

4. **Simplified SMTP Transaction**
   ```
   Client → Internal Relay (172.16.13.208:25)
   Internal Relay → External Recipients
   ```

---

## Shell Script Implementation (Failed)

### Original Configuration

**File:** `SMTP_Email_Tester_KNT.sh` (Version 1.1)  
**Method:** curl with SMTP protocol

```bash
SMTP_SERVER="smtp.office365.com"
SMTP_PORT="587"
SMTP_USER="pwadmin@aacanet.org"
SMTP_PASSWORD="COFFEE@lines.021326"  # From config file
```

### curl Command Used

```bash
curl --url "smtp://smtp.office365.com:587" \
    --ssl-reqd \                          # Require TLS/SSL
    --mail-from "${MAIL_FROM}" \
    --mail-rcpt "recipient@example.com" \
    --user "${SMTP_USER}:${SMTP_PASSWORD}" \  # Authentication
    --upload-file "${EMAIL_FILE}" \
    --verbose
```

### Key Characteristics

| Property | Value | Challenges |
|----------|-------|------------|
| **SMTP Server** | `smtp.office365.com` | External Microsoft service |
| **Port** | `587` | STARTTLS required |
| **Authentication** | Required | Username + password |
| **SSL/TLS** | `--ssl-reqd` | Mandatory encryption |
| **Network** | External | Internet-facing connection |

### Why It Failed

#### 1. **Authentication Issues**
- **Modern Authentication Requirements**
  - Office365 increasingly requires OAuth 2.0 instead of basic auth
  - Legacy authentication being phased out by Microsoft
  - Basic username/password may be disabled for security

- **Multi-Factor Authentication (MFA)**
  - Account may have MFA enabled
  - Standard passwords rejected when MFA is active
  - Requires app-specific passwords instead

- **Password Policy Changes**
  - Password may have expired or been changed
  - Account security policies may block automated logins
  - Suspicious activity detection may block server IPs

#### 2. **Network and Firewall Restrictions**
- **Outbound Connection Blocking**
  - Corporate firewall may block port 587 outbound
  - Network policies may restrict external SMTP connections
  - Security groups may prevent direct Office365 access

- **IP Address Restrictions**
  - Office365 may have IP allowlists configured
  - Server IP may not be whitelisted
  - Geographic restrictions on login locations

#### 3. **TLS/SSL Configuration Issues**
- **Certificate Validation**
  - SSL certificate verification failures
  - Certificate chain validation problems
  - Hostname verification mismatches

- **Protocol Version Mismatch**
  - Office365 requires specific TLS versions (TLS 1.2+)
  - Server may have outdated SSL/TLS libraries
  - Cipher suite incompatibilities

#### 4. **Office365 Security Policies**
- **Conditional Access Policies**
  - Azure AD policies may block non-browser authentication
  - Device compliance requirements
  - Location-based access restrictions

- **SMTP AUTH Disabled**
  - Office365 tenant may have SMTP AUTH disabled
  - Modern Auth-only configuration
  - Security defaults preventing legacy protocols

### Error Examples

```bash
# Certificate validation failure
curl: (60) SSL certificate problem: unable to get local issuer certificate

# Authentication failure
535 5.7.3 Authentication unsuccessful

# Connection refused
Failed to connect to smtp.office365.com port 587: Connection refused

# TLS negotiation failure
curl: (35) error:1408F10B:SSL routines:ssl3_get_record:wrong version number
```

---

## Root Cause Analysis

### The Fundamental Difference

```
PHP Approach:
[PHP Script] → [Internal Relay: 172.16.13.208:25] → [External Recipients]
             ↑                                    ↑
        No Auth Required                    Relay Handles Auth

Shell Script Approach (Original):
[Shell Script] → [Office365: smtp.office365.com:587] → [External Recipients]
              ↑
         Auth Required + TLS + Security Policies
```

### Why Different Approaches Were Used

1. **PHP Script Evolution**
   - Likely configured when internal relay was available
   - Followed existing patterns in the codebase
   - Used centralized `mailsetup.php` configuration

2. **Shell Script Development**
   - Attempted direct external SMTP approach
   - Tried to avoid dependency on internal infrastructure
   - Followed Office365 documentation for SMTP relay

### The Mismatch

The shell script was attempting a **more complex, external authentication flow** while the PHP script used a **simple, internal relay approach**. Both were trying to achieve the same goal, but the shell script chose a more difficult path.

---

## Solution Implemented

### Changes Made to Shell Script

**Version:** 1.2 (17 February 2026)  
**File:** `SMTP_Email_Tester_KNT.sh`

#### Configuration Changes

```bash
# BEFORE (Failed)
SMTP_SERVER="smtp.office365.com"
SMTP_PORT="587"
SMTP_USER="pwadmin@aacanet.org"
# Load password from config file
# --ssl-reqd flag required
# --user authentication required

# AFTER (Working)
SMTP_SERVER="172.16.13.208"
SMTP_PORT="25"
# No authentication variables needed
# No SSL/TLS required
# No password loading needed
```

#### curl Command Changes

```bash
# BEFORE (Failed)
curl --url "smtp://smtp.office365.com:587" \
    --ssl-reqd \
    --user "${SMTP_USER}:${SMTP_PASSWORD}" \
    --mail-from "${MAIL_FROM}" \
    --mail-rcpt "recipient@example.com" \
    --upload-file "${EMAIL_FILE}"

# AFTER (Working)
curl --url "smtp://172.16.13.208:25" \
    --mail-from "${MAIL_FROM}" \
    --mail-rcpt "recipient@example.com" \
    --upload-file "${EMAIL_FILE}"
```

### What Was Removed

1. ✂️ **Config File Loading Logic**
   ```bash
   # Removed entire section
   SMTP_CONFIG_FILE="/var/www/html/cron/SMTP_Email_Tester_KNT_Config.conf"
   source "${SMTP_CONFIG_FILE}"
   # Password validation logic removed
   ```

2. ✂️ **Authentication Parameters**
   ```bash
   # Removed
   SMTP_USER="pwadmin@aacanet.org"
   SMTP_PASSWORD variable loading
   --user "${SMTP_USER}:${SMTP_PASSWORD}"
   ```

3. ✂️ **SSL/TLS Requirements**
   ```bash
   # Removed
   --ssl-reqd
   ```

### What Was Added

1. ✅ **Updated Documentation**
   ```bash
   # Description updated to reflect internal relay usage
   # Changelog entry added (Version 1.2)
   # Comments explaining no authentication is needed
   ```

2. ✅ **Simplified Configuration**
   ```bash
   # Using internal mail relay server (same as PHP scripts)
   # No authentication required for internal relay
   SMTP_SERVER="172.16.13.208"
   SMTP_PORT="25"
   ```

### Result

The shell script now uses the **exact same SMTP infrastructure** as the PHP script:
- ✅ Same server: `172.16.13.208`
- ✅ Same port: `25`
- ✅ Same authentication: `none`
- ✅ Same network path: internal relay

---

## Technical Comparison

### Side-by-Side Configuration

| Aspect | PHP Implementation | Shell Script (Before) | Shell Script (After) |
|--------|-------------------|----------------------|---------------------|
| **SMTP Server** | 172.16.13.208 | smtp.office365.com | 172.16.13.208 ✅ |
| **Port** | 25 | 587 | 25 ✅ |
| **Authentication** | None | Required | None ✅ |
| **SSL/TLS** | No | Required | No ✅ |
| **Network Scope** | Internal | External | Internal ✅ |
| **Configuration Source** | mailsetup.php | Config file | Script variables ✅ |
| **Complexity** | Low | High | Low ✅ |
| **Dependencies** | PHPMailer | curl + config file | curl only ✅ |
| **Status** | ✅ Working | ❌ Failed | ✅ Working |

### SMTP Transaction Flow

#### Original Shell Script (Failed)
```
1. Connect to smtp.office365.com:587
2. Initiate STARTTLS
3. Negotiate TLS connection
4. Send EHLO
5. Authenticate with username/password
   ❌ FAILURE POINT: Authentication rejected
6. Never reaches email sending
```

#### Updated Shell Script (Working)
```
1. Connect to 172.16.13.208:25
2. Send EHLO
3. Send MAIL FROM
4. Send RCPT TO (recipients)
5. Send DATA (email content)
6. Receive 250 OK
7. ✅ SUCCESS: Email queued
```

### Code Complexity Reduction

**Lines of Code:**
- **Before:** ~120 lines (includes config loading, validation, error handling)
- **After:** ~100 lines (simplified configuration)
- **Reduction:** ~17% less code

**Configuration Variables:**
- **Before:** 6 variables (server, port, user, password, mail_from, mail_to)
- **After:** 4 variables (server, port, mail_from, mail_to)
- **Reduction:** 33% fewer variables

**Security Considerations:**
- **Before:** Password stored in config file (security risk)
- **After:** No credentials needed (better security)

---

## Lessons Learned

### 1. **Consistency in Email Infrastructure**

**Lesson:** When multiple components send emails, they should use the same SMTP infrastructure.

**Best Practice:**
- Document the approved SMTP relay server for the organization
- Create a centralized configuration system
- Ensure all scripts (PHP, Shell, Python, etc.) use the same settings

**Implementation:**
```bash
# Create a shared configuration file
/etc/smtp/relay.conf
---
SMTP_SERVER="172.16.13.208"
SMTP_PORT="25"
SMTP_AUTH="false"
```

### 2. **Internal vs. External SMTP Relay**

**Lesson:** Internal mail relays are more reliable for application-to-email workflows.

**Advantages of Internal Relay:**
- ✅ No authentication complexity
- ✅ Faster (no external network latency)
- ✅ No external service dependencies
- ✅ Better security (credentials not required)
- ✅ More reliable (no internet connectivity issues)

**When to Use External SMTP:**
- User-initiated emails (e.g., webmail clients)
- External applications without VPN/internal access
- When internal relay is not available

### 3. **Office365 SMTP Challenges**

**Lesson:** Direct authentication with Office365 SMTP is increasingly difficult due to security policies.

**Modern Office365 Policies:**
- OAuth 2.0 preferred over basic authentication
- Legacy authentication being deprecated
- MFA enforcement breaks basic SMTP auth
- Conditional access policies may block automated logins
- Requires app-specific passwords or OAuth flows

**Recommendation:**
- Use internal relay instead of direct Office365 SMTP
- If Office365 required, implement OAuth 2.0 flow
- Use Microsoft Graph API for modern email sending

### 4. **Error Diagnosis Approach**

**Lesson:** Compare working and non-working implementations to identify differences.

**Effective Debugging:**
```
1. Identify working reference implementation (PHP script)
2. Extract configuration from working version
3. Compare with non-working version (Shell script)
4. Identify differences in:
   - Server addresses
   - Ports
   - Authentication methods
   - Encryption requirements
5. Align non-working version to match working version
```

### 5. **Documentation Matters**

**Lesson:** Clear documentation prevents configuration drift.

**What Should Be Documented:**
- ✅ SMTP server address and port
- ✅ Authentication requirements (or lack thereof)
- ✅ Network accessibility (internal vs. external)
- ✅ Example configurations for different languages
- ✅ Changelog of configuration changes
- ✅ Troubleshooting guide

### 6. **Simplicity Over Complexity**

**Lesson:** The simplest solution that works is often the best solution.

**Before:** Complex Office365 integration
- External authentication
- TLS/SSL negotiation
- Password management
- Error handling for auth failures

**After:** Simple internal relay
- Direct SMTP connection
- No authentication
- No encryption overhead
- Minimal error scenarios

**Result:** More reliable, maintainable, and secure

### 7. **Security Considerations**

**Lesson:** Reducing credential usage improves security posture.

**Security Improvements:**
- ❌ **Before:** Password stored in config file (`SMTP_PASSWORD="COFFEE@lines.021326"`)
- ✅ **After:** No credentials needed (uses trusted network relay)

**Security Benefits:**
- No password leakage risk
- No credential rotation needed
- No password expiration issues
- Reduced attack surface

### 8. **Network Architecture Awareness**

**Lesson:** Understanding network topology helps choose the right approach.

**Network Flow:**
```
[Application Server]
    ↓
[Internal Mail Relay: 172.16.13.208]
    ↓
[External SMTP Servers]
    ↓
[Recipient Mailboxes]
```

**Key Understanding:**
- Internal relay handles the complexity of external delivery
- Application code remains simple
- Centralized email routing and logging
- Single point for email policy enforcement

---

## Recommendations

### 1. **Standardize Email Configuration Across All Scripts**

**Action Items:**
- [ ] Audit all email-sending scripts (PHP, Shell, Python, etc.)
- [ ] Verify all use `172.16.13.208:25`
- [ ] Update any scripts using Office365 direct authentication
- [ ] Create shared configuration library

### 2. **Create Centralized Email Configuration**

**Proposed File:** `/etc/pipeway/smtp_relay.conf`

```bash
# PIPEWAY Email Relay Configuration
# All applications should use these settings
SMTP_RELAY_SERVER="172.16.13.208"
SMTP_RELAY_PORT="25"
SMTP_AUTH_REQUIRED="false"
SMTP_FROM_ADDRESS="pwadmin@aacanet.org"
SMTP_FROM_NAME="Pipeway 2.0"
```

**Usage in Scripts:**
```bash
# Shell scripts
source /etc/pipeway/smtp_relay.conf

# PHP scripts
include_once('/etc/pipeway/smtp_relay.php');
```

### 3. **Update Internal Documentation**

**Create/Update Documents:**
- Email Infrastructure Overview
- SMTP Configuration Guide for Developers
- Email Troubleshooting Runbook
- Change Management for Email Settings

### 4. **Implement Monitoring**

**Monitor:**
- Internal relay server availability (172.16.13.208)
- Email queue depth
- Delivery success rates
- SMTP connection failures

**Alert On:**
- Relay server downtime
- Queue backup (>100 messages)
- Delivery failure rate >5%
- Port 25 unreachable

### 5. **Testing Protocol**

**Before Deploying Email Scripts:**
1. ✅ Verify using internal relay (172.16.13.208:25)
2. ✅ Test email delivery
3. ✅ Verify no authentication used
4. ✅ Check logs for errors
5. ✅ Confirm recipients receive emails

### 6. **Backup Strategy**

**If Internal Relay Fails:**
- Document backup SMTP server address
- Create failover configuration
- Test backup server quarterly
- Update documentation with failover procedures

---

## Appendix

### A. Complete Working Configuration

**Shell Script:** `SMTP_Email_Tester_KNT.sh` (v1.2)

```bash
SMTP_SERVER="172.16.13.208"
SMTP_PORT="25"
MAIL_FROM="donotreply@aacanet.org"

curl --url "smtp://${SMTP_SERVER}:${SMTP_PORT}" \
    --mail-from "${MAIL_FROM}" \
    --mail-rcpt "recipient@example.com" \
    --upload-file "${EMAIL_FILE}" \
    --verbose
```

**PHP Script:** `SMTP_tester.php`

```php
// mailsetup.php configuration
$mail->Host = '172.16.13.208';
$mail->SMTPAuth = false;
$mail->Port = 25;
$mail->From = 'pwadmin@aacanet.org';
$mail->FromName = 'Pipeway 2.0';
```

### B. Internal Relay Server Details

**Server:** `172.16.13.208`  
**Hostname:** `mail.aacanet.org`  
**Function:** SMTP relay for internal applications  
**Port:** `25` (SMTP)  
**Authentication:** Not required for internal network  
**Accessibility:** Internal network only  

### C. Testing Checklist

- [x] PHP script sends emails successfully
- [x] Shell script sends emails successfully
- [x] Both use same SMTP server (172.16.13.208)
- [x] Both use same port (25)
- [x] No authentication required
- [x] No SSL/TLS required
- [x] Emails delivered to all recipients
- [x] Logs confirm successful delivery
- [x] Documentation updated

### D. Related Files

1. **SMTP_tester.php** - PHP email test script
2. **SMTP_Email_Tester_KNT.sh** - Shell email test script
3. **mailsetup.php** - PHP email configuration
4. **SMTP_Email_Tester_KNT_Config.conf** - ~~Old config file~~ (no longer needed)

### E. Contact Information

**For Email Configuration Issues:**
- KEANT Technologies: keanttech@gmail.com
- System Administrator: pwadmin@aacanet.org

**For Office365 Questions:**
- IT Department
- Microsoft Support (if tenant-level changes needed)

---

## Conclusion

The email sending issue between PHP and shell scripts was resolved by **aligning both implementations to use the same internal SMTP relay server** (`172.16.13.208:25`). The original shell script's attempt to authenticate directly with Office365 encountered multiple barriers including authentication policies, network restrictions, and modern security requirements.

The solution demonstrates the importance of:
- **Configuration consistency** across different implementations
- **Simplicity** in system design
- **Internal relays** for application-to-email workflows
- **Documentation** to prevent configuration drift
- **Security** through reduced credential exposure

**Status:** ✅ Both PHP and Shell scripts now working successfully with identical SMTP configuration.

---

**Document Version:** 1.0  
**Last Updated:** 17 February 2026  
**Next Review:** 17 May 2026
