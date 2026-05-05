# Firm Cost & Fee Check Detail Reports - Execution Guide

**Document Version:** 1.0  
**Last Updated:** January 28, 2026  
**Author:** KEANT Technologies  
**Script Version:** 3.0  

---

## Table of Contents
1. [Overview](#overview)
2. [Script Location & Prerequisites](#script-location--prerequisites)
3. [What This Script Does](#what-this-script-does)
4. [Command Line Usage](#command-line-usage)
5. [Usage Examples](#usage-examples)
6. [Configuration Guide](#configuration-guide)
7. [Understanding the Output](#understanding-the-output)
8. [Troubleshooting](#troubleshooting)
9. [Advanced Usage](#advanced-usage)
10. [Important Notes](#important-notes)

---

## Overview

The `Wrapper_Firm_Cost_Fee_KNT.php` script is a unified wrapper that generates Excel reports for firm financial transactions. It can process two types of reports simultaneously or individually:

- **Firm Cost Check Detail Report** - Court cost transactions (RMSTRANCDE='1A')
- **Firm Fee Check Detail Report** - Remittance/fee transactions (RMSTRANCDE='50'-'59')

- **xlsx Output path**- 

/var/www/html/bi/dist/Mako/downloadfile/BRET/AC

### Key Features
✅ Single command to generate both reports  
✅ Selective execution (cost only, fee only, or both)  
✅ Custom date override support  
✅ Automatic date calculation based on business day logic  
✅ Processes multiple client codes in one run  
✅ Detailed execution summary with success/failure tracking  

---

## Script Location & Prerequisites

### Server Locations

**Development Environment:**
```
/Users/ashishdiwakar/ASHISH/KEANT_Technologies/Notepads/FirmFee_Cost_Development/
```

**Production Server (Pipewayweb):**
```
```
/var/www/html/bi/dist/Report/

### Required Files

The script requires these supporting files to be present in the same directory:

```
Wrapper_Firm_Cost_Fee_KNT.php                    # Main wrapper script
FirmCostCheckDetailToMyDownload_KNT.php          # Cost report generator
FirmFeeCheckDetailToMyDownload_KNT.php           # Fee report generator
/var/www/html/bi/dist/Report/generateReportManual.php  # Helper functions
```

### System Requirements

- **PHP Version:** 8.2 or higher (`/usr/bin/php8.2`)
- **Database Access:** RMAACABHS, RMSPMASTER tables
- **PHP Extensions:** PDO, MySQL, XLSXWriter
- **File Permissions:** Write access to `/var/www/html/bi/dist/Report/output/`

### Verifying Prerequisites

```bash
# Check PHP version
php8.2 --version

# Verify script exists
ls -la /var/www/html/bi/dist/Report/Wrapper_Firm_Cost_Fee_KNT.php

# Check output directory permissions
ls -ld /var/www/html/bi/dist/Report/output/
```

---

## What This Script Does

### Processing Flow

```
1. Parse command-line arguments (report type, date)
   ↓
2. Validate input parameters
   ↓
3. Load client codes from $path variable (55 firms by default)
   ↓
4. For each selected report type:
   a. Query database for transactions
   b. Generate Excel file per client code
   c. Save to client-specific directory
   d. Track success/failure
   ↓
5. Display execution summary
   ↓
6. Exit with status code (0=success, 1=failure)
```

### Report Types

#### 1. Firm Cost Check Detail Report
- **Transaction Code:** RMSTRANCDE='1A' (Court Costs)
- **Database:** RMAACABHS
- **Output:** Excel file with cost transaction details
- **Columns:** Client Code, Account Number, Names, Transaction Details, Cost Amounts, Invoice Information

#### 2. Firm Fee Check Detail Report
- **Transaction Code:** RMSTRANCDE='50' through '59' (Remittance/Fees)
- **Database:** RMAACABHS
- **Output:** Excel file with fee/remittance details
- **Columns:** Client Code, Account Number, Names, Transaction Details, Fee Amounts, Payment Information

### Date Logic

**Auto-Calculated Dates (when no date parameter provided):**
- **Monday:** Reports for 3 days ago (previous Friday)
- **Tuesday-Sunday:** Reports for 1 day ago (previous business day)

**Format:** YYYYMMDD (e.g., 20260128)

---

## Command Line Usage

### Basic Syntax

```bash
php Wrapper_Firm_Cost_Fee_KNT.php [REPORT_TYPE] [DATE]
```

### Parameters

| Parameter | Required | Values | Default | Description |
|-----------|----------|--------|---------|-------------|
| REPORT_TYPE | No | `cost`, `fee`, `both` | `both` | Which report(s) to generate |
| DATE | No | YYYYMMDD | Auto-calculated | Custom report date |

### Production Server Command 

# date type - 20250116 

```bash
/usr/bin/php8.2 /var/www/html/bi/dist/Report/Wrapper_Firm_Cost_Fee_KNT.php [REPORT_TYPE] [DATE]
```

---

## Usage Examples

### Example 1: Run Both Reports with Auto-Calculated Date

**Scenario:** Generate both Cost and Fee reports for previous business day

```bash
cd /var/www/html/bi/dist/Report
/usr/bin/php8.2 Wrapper_Firm_Cost_Fee_KNT.php
```

**Or explicitly:**
```bash
/usr/bin/php8.2 Wrapper_Firm_Cost_Fee_KNT.php both
```

**Expected Output:**
```
==========================================================================
        FIRM REPORT GENERATION PROCESS - KEANT Technologies
==========================================================================
Report Type: BOTH
Report Date: Auto-calculated (Monday: -3 days, Other: -1 day)
==========================================================================

--- EXECUTING FIRM COST CHECK DETAIL REPORT ---
Transaction Type: Court Cost (RMSTRANCDE='1A')
Processing 55 client codes...
Using auto-calculated date

Processing Company: DCON
Processing Company: WENR
...

--- FIRM COST CHECK DETAIL REPORT RESULTS ---
Status: Success
Message: generated
New Status: 2

--- EXECUTING FIRM FEE CHECK DETAIL REPORT ---
Transaction Type: Firm Fees (RMSTRANCDE='50' to '59')
Processing 55 client codes...
Using auto-calculated date

Processing Company: DCON
Processing Company: WENR
...

--- FIRM FEE CHECK DETAIL REPORT RESULTS ---
Status: Success
Message: generated
New Status: 2

==========================================================================
                    OVERALL EXECUTION SUMMARY
==========================================================================
Firm Cost Check Detail Report: SUCCESS
Firm Fee Check Detail Report:  SUCCESS

Total Reports Executed: 2
Successful Reports: 2
Failed Reports: 0
==========================================================================
```

---

### Example 2: Run Only Cost Report

**Scenario:** Generate only the Court Cost report for today's processing

```bash
/usr/bin/php8.2 Wrapper_Firm_Cost_Fee_KNT.php cost
```

**Expected Output:**
```
==========================================================================
        FIRM REPORT GENERATION PROCESS - KEANT Technologies
==========================================================================
Report Type: COST
Report Date: Auto-calculated (Monday: -3 days, Other: -1 day)
==========================================================================

--- EXECUTING FIRM COST CHECK DETAIL REPORT ---
...

--- SKIPPING FIRM FEE CHECK DETAIL REPORT ---
(Not selected in report type parameter)

==========================================================================
                    OVERALL EXECUTION SUMMARY
==========================================================================
Firm Cost Check Detail Report: SUCCESS

Total Reports Executed: 1
Successful Reports: 1
Failed Reports: 0
==========================================================================
```

---

### Example 3: Run Only Fee Report

**Scenario:** Generate only the Firm Fee report

```bash
/usr/bin/php8.2 Wrapper_Firm_Cost_Fee_KNT.php fee
```

---

### Example 4: Run Both Reports for Specific Date

**Scenario:** Re-run reports for January 15, 2026

```bash
/usr/bin/php8.2 Wrapper_Firm_Cost_Fee_KNT.php both 20260115
```

**Expected Output:**
```
==========================================================================
        FIRM REPORT GENERATION PROCESS - KEANT Technologies
==========================================================================
Report Type: BOTH
Report Date: 20260115 (Custom Override)
==========================================================================
...
```

---

### Example 5: Run Cost Report for Specific Historical Date

**Scenario:** Generate only Cost report for December 31, 2025

```bash
/usr/bin/php8.2 Wrapper_Firm_Cost_Fee_KNT.php cost 20251231
```

---

### Example 6: Run Fee Report for Specific Date

**Scenario:** Generate only Fee report for January 20, 2026

```bash
/usr/bin/php8.2 Wrapper_Firm_Cost_Fee_KNT.php fee 20260120
```

---

## Configuration Guide

### Modifying Client Codes

**Location:** Lines 155-159 in `Wrapper_Firm_Cost_Fee_KNT.php`

**Current Configuration (55 firms):**
```php
$path = "'downloadfile/DCON/AC','downloadfile/WENR/AC','downloadfile/MHBG/AC', ... ";
```

#### To Process All Firms:
Keep the default configuration (uncommented lines 155-159)

#### To Process Single Firm:
```php
// Comment out the multi-firm line
// $path = "'downloadfile/DCON/AC','downloadfile/WENR/AC', ...";

// Uncomment and modify single-firm line
$path = "'downloadfile/BRET/AC'";
```

#### To Process Specific Subset of Firms:
```php
$path = "'downloadfile/DCON/AC','downloadfile/WENR/AC','downloadfile/BRET/AC'";
```

#### Path Format Requirements:
✅ **Correct:** `'downloadfile/FIRMCODE/AC'`  
❌ **Incorrect:** `'FIRMCODE'`  
❌ **Incorrect:** `'downloadfile/FIRMCODE'`  

### Modifying Output Directory

**Location:** Line 186

```php
$reportBasePath = '/var/www/html/bi/dist/Report/output/';
```

**To change:**
```php
$reportBasePath = '/path/to/your/output/directory/';
```

### Enabling Email Notifications

**Location:** Line 181

**Default (Disabled):**
```php
$mailNotification = 0;
```

**To Enable:**
```php
$mailNotification = 1;
```

**Note:** Requires PHPMailer to be properly configured on the server.

---

## Understanding the Output

### Output File Structure

```
/var/www/html/bi/dist/Report/output/
├── downloadfile/
│   ├── DCON/
│   │   └── AC/
│   │       ├── FirmCostCheckDetail(2026-01-28 10:30:45).xlsx
│   │       └── FirmFeeCheckDetail(2026-01-28 10:30:50).xlsx
│   ├── WENR/
│   │   └── AC/
│   │       ├── FirmCostCheckDetail(2026-01-28 10:30:46).xlsx
│   │       └── FirmFeeCheckDetail(2026-01-28 10:30:51).xlsx
│   └── ... (other firms)
```

### Excel File Contents

#### Firm Cost Check Detail Report Columns:
1. Client Code
2. Account Number
3. Last Name
4. First Name
5. Transaction Code
6. Transaction Date
7. Transaction Description
8. Payment Amount
9. Remit Amount
10. Fee Requested By Firm
11. Fee Paid To Firm
12. Firm Invoice Number
13. Firm Check Number
14. Amount Paid To Firm
15. Firm Invoice Date
16. Firm File Number
17. PYALORGCD

#### Firm Fee Check Detail Report Columns:
Same structure as Cost report, but with fee-specific transaction data

### Exit Codes

| Exit Code | Meaning |
|-----------|---------|
| 0 | All reports completed successfully |
| 1 | At least one report failed |

---

## Troubleshooting

### Error: Invalid Report Type

**Error Message:**
```
ERROR: Invalid report type 'costs'
Valid options: cost, fee, both
```

**Solution:**
Use exactly: `cost`, `fee`, or `both` (lowercase)

**Correct:**
```bash
/usr/bin/php8.2 Wrapper_Firm_Cost_Fee_KNT.php cost
```

---

### Error: Invalid Date Format

**Error Message:**
```
ERROR: Invalid date format '2026-01-15'
Date must be in YYYYMMDD format (e.g., 20250116)
```

**Solution:**
Remove hyphens and ensure 8-digit format

**Incorrect:**
```bash
/usr/bin/php8.2 Wrapper_Firm_Cost_Fee_KNT.php both 2026-01-15
```

**Correct:**
```bash
/usr/bin/php8.2 Wrapper_Firm_Cost_Fee_KNT.php both 20260115
```

---

### Error: Invalid Date Value

**Error Message:**
```
ERROR: Invalid date '20261335'
Year: 2026, Month: 13, Day: 35
Please provide a valid date in YYYYMMDD format
```

**Solution:**
Ensure month (01-12) and day (01-31) are valid

---

### Error: Processing Company Shows Blank

**Output:**
```
Processing Company: 
```

**Cause:** Invalid $path format (missing 'downloadfile/' prefix)

**Solution:**
Ensure client codes follow format: `'downloadfile/FIRMCODE/AC'`

**Check Line 155-159:**
```php
$path = "'downloadfile/DCON/AC','downloadfile/WENR/AC', ... ";
```

---

### Error: No Data Present

**Output:**
```
Status: Failed
Message: Failed
New Status: 3
```

**Possible Causes:**
1. No transactions for the specified date
2. Database connection issues
3. Incorrect transaction code filtering

**Solutions:**
- Verify database has data for the date
- Check database connectivity
- Review transaction date range in query

---

### Error: Permission Denied

**Error:**
```
PHP Warning: fopen(/var/www/html/bi/dist/Report/output/...): failed to open stream: Permission denied
```

**Solution:**
```bash
# Check directory permissions
ls -ld /var/www/html/bi/dist/Report/output/

# Fix permissions if needed
chmod 755 /var/www/html/bi/dist/Report/output/
chown www-data:www-data /var/www/html/bi/dist/Report/output/
```

---

### Error: Required File Not Found

**Error:**
```
PHP Fatal error: require_once(): Failed opening required 'FirmCostCheckDetailToMyDownload_KNT.php'
```

**Solution:**
Ensure all required files are in the same directory:
```bash
cd /var/www/html/bi/dist/Report
ls -la Firm*KNT.php Wrapper*KNT.php
```

---

### Debugging Tips

#### Enable Detailed Output
Monitor execution in real-time:
```bash
/usr/bin/php8.2 Wrapper_Firm_Cost_Fee_KNT.php both | tee execution.log
```

#### Check PHP Errors
```bash
tail -f /var/log/php8.2-fpm.log
```

#### Verify Database Connection
```bash
/usr/bin/php8.2 -r "include '/var/www/html/bi/dist/pdoconn.php'; var_dump(\$pdo);"
```

#### Test Single Firm First
Modify $path to process one firm:
```php
$path = "'downloadfile/BRET/AC'";
```

---

## Advanced Usage

### Running as Scheduled Cron Job

**Daily Execution (Monday-Friday, 7:00 AM):**
```bash
# Edit crontab
crontab -e

# Add line:
0 7 * * 1-5 /usr/bin/php8.2 /var/www/html/bi/dist/Report/Wrapper_Firm_Cost_Fee_KNT.php both >> /var/log/firm_reports.log 2>&1
```

**Weekly Execution (Every Monday, 6:00 AM):**
```bash
0 6 * * 1 /usr/bin/php8.2 /var/www/html/bi/dist/Report/Wrapper_Firm_Cost_Fee_KNT.php both 
```

### Running with Logging

```bash
/usr/bin/php8.2 Wrapper_Firm_Cost_Fee_KNT.php both 2>&1 | tee -a firm_reports_$(date +%Y%m%d).log
```

### Batch Processing Multiple Dates

**Scenario:** Generate reports for last week

```bash
#!/bin/bash
for date in 20260120 20260121 20260122 20260123 20260124; do
    echo "Processing $date..."
    /usr/bin/php8.2 Wrapper_Firm_Cost_Fee_KNT.php both $date
done
```

### Running in Background

```bash
nohup /usr/bin/php8.2 Wrapper_Firm_Cost_Fee_KNT.php both &
```

Check status:
```bash
tail -f nohup.out
```

---

## Important Notes

### ⚠️ Data Integrity

1. **Date Ranges:** Reports use `>=` date comparison, pulling all transactions from specified date forward
2. **Duplicate Runs:** Running the script multiple times for the same date will create new files (timestamp in filename prevents overwrite)
3. **Database Load:** Processing 55 firms queries the database extensively - avoid running during peak hours

### 📅 Business Day Logic

**Auto-Calculated Dates:**
- **Monday:** Calculates date as Friday (current date - 3 days)
- **Tuesday-Sunday:** Calculates date as previous day (current date - 1 day)

**Override:** Always use explicit date parameter for historical or non-standard date processing

### 🔍 Data Validation

**Before Running:**
1. Verify database has transactions for target date
2. Confirm client codes are active in CC_REGSTR table
3. Check output directory has sufficient disk space

**After Running:**
1. Review execution summary for failures
2. Spot-check generated Excel files
3. Verify file counts match expected client codes with data

### 📊 Performance Considerations

**Processing Time:**
- Single firm: ~5-10 seconds
- All 55 firms: ~5-10 minutes (varies by data volume)

**Resource Usage:**
- Memory: ~256MB per execution
- Disk: Variable (depends on transaction volume)

### 🔒 Security Notes

1. **File Permissions:** Output files created with server user permissions
2. **Database Access:** Uses credentials from `/var/www/html/bi/dist/pdoconn.php`
3. **Sensitive Data:** Excel files contain financial transaction data - ensure proper access controls

---

## Quick Reference Card

### Most Common Commands

| Task | Command |
|------|---------|
| Run both reports (auto-date) | `/usr/bin/php8.2 Wrapper_Firm_Cost_Fee_KNT.php` |
| Run cost report only | `/usr/bin/php8.2 Wrapper_Firm_Cost_Fee_KNT.php cost` |
| Run fee report only | `/usr/bin/php8.2 Wrapper_Firm_Cost_Fee_KNT.php fee` |
| Run for specific date | `/usr/bin/php8.2 Wrapper_Firm_Cost_Fee_KNT.php both 20260115` |
| Run cost for specific date | `/usr/bin/php8.2 Wrapper_Firm_Cost_Fee_KNT.php cost 20260115` |

### Parameter Quick Reference

```
php Wrapper_Firm_Cost_Fee_KNT.php [REPORT_TYPE] [DATE]

REPORT_TYPE: cost | fee | both (default: both)
DATE: YYYYMMDD (optional, auto-calculates if omitted)
```

### Success Indicators

✅ "Status: Success" in output  
✅ "Successful Reports: N" matches executed count  
✅ Exit code 0  
✅ Excel files created in output directories  

### Failure Indicators

❌ "Status: Failed" in output  
❌ "Failed Reports: N" > 0  
❌ Exit code 1  
❌ Missing Excel files for some firms  

---

## Support & Maintenance

**For Issues or Questions:**
- Developer: KEANT Technologies
- Script Location: `/var/www/html/bi/dist/Report/`
- Log Location: Check `/var/log/` or execution output

**Before Reporting Issues:**
1. Note the exact command executed
2. Capture complete error output
3. Record date parameter used (if any)
4. Check if issue is date-specific or consistent

---

## Revision History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2026-01-28 | KEANT Technologies | Initial documentation for v3.0 script |

---

**End of Documentation**
