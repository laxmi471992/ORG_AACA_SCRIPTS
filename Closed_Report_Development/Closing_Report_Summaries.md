# Closing Report Script Summaries

## Index
- [ClosingReportClients_KNT.php](#closingreportclients_kntphp)
- [ClosingReportClientsWeeklySelectClients_KNT.php](#closingreportclientsweeklyselectclients_kntphp)
- [ClosingReportClientsWeeklyMYD_KNT.php](#closingreportclientsweeklymyd_kntphp)
- [ClosingReportClientstoLibrary_KNT.php](#closingreportclientstolibrary_kntphp)
- [ClosingReportFCOSIFPIFtoLibrary_KNT.php](#closingreportfcosifpiftolibrary_kntphp)
- [ClosingReportFCOSIFPIF_KNT.php](#closingreportfcosifpif_kntphp)
- [ClosingReportClients vs ClosingReportClientstoLibrary — Key Differences](#closingreportclients_kntphp-vs-closingreportclientstolibrary_kntphp--key-differences)

## ClosingReportClients_KNT.php

**Purpose**
- Generate a monthly closing-clients XLSX report per company path.

**Key inputs**
- `$path`, `$code_name`, `$reportName`, `$reportBasePath`, `$mailNotification`, `$userType`, `$userReportName`.

**Data source**
- `MASTER_DATA_DB` (last month closed accounts, excluding `CLIENT_CDE = 'FRIC'`).

**Outputs**
- XLSX file saved to `$reportBasePath . $companyPath`.
- Status updates via `ifDataPresent()` / `ifDataNotPresent()`.

**Distinct steps**
- Parse paths and initialize status tracking.
- Resolve company code and company status per path.
- Query last-month closing data for the company.
- Build an XLSX workbook with standard headers and styles.
- Write the file to the report path and record file size.
- Send notification emails when data exists.
- Return consolidated status for the run.

---

## ClosingReportClientsWeeklySelectClients_KNT.php

**Purpose**
- Generate a weekly closing-clients XLSX report for selected clients.

**Key inputs**
- `$path`, `$code_name`, `$reportName`, `$reportBasePath`, `$mailNotification`, `$userType`.

**Data source**
- `HSFLCLNTWF` (last 7 days, `CURR_STS_CD` like `9%`, per company code).

**Outputs**
- XLSX file saved to `$reportBasePath . $companyPath`.
- Status updates via `ifDataPresent()` / `ifDataNotPresent()`.

**Distinct steps**
- Parse paths and initialize status tracking.
- Resolve company code and status for each path.
- Query weekly closing data for the specific company code.
- Build XLSX headers and write rows to the worksheet.
- Save the file and capture file size.
- Send notification emails for data-present runs.
- Return consolidated status for the run.

---

## ClosingReportClientsWeeklyMYD_KNT.php

**Purpose**
- Generate a weekly MYD closing report in XLSX format per company path.

**Key inputs**
- `$path`, `$reportName`, `$reportBasePath`, `$mailNotification`, `$userType`.

**Data source**
- `RMAACABHS` (last 7 days, `RMSTRANCDE = '1A'`, per company code).

**Outputs**
- XLSX file saved to `$reportBasePath . $companyPath`.
- Status updates via `ifDataPresent()` / `ifDataNotPresent()`.

**Distinct steps**
- Parse paths and initialize status tracking.
- Resolve company code and status for each path.
- Query weekly MYD transactions for the company.
- Build the worksheet with standardized headers and styles.
- Write the report to the output folder and capture file size.
- Send notifications when data is present.
- Return consolidated status for the run.

---

## ClosingReportClientstoLibrary_KNT.php

**Purpose**
- Generate a monthly closing-clients XLSX report across all eligible clients and place it in each company library folder.

**Key inputs**
- `$path`, `$reportName`, `$reportBasePath`, `$mailNotification`, `$userType`.

**Data source**
- `MASTER_DATA_DB` (last month closed accounts, excluding `CLIENT_CDE = 'FRIC'`).

**Outputs**
- XLSX file saved to `$reportBasePath . $companyPath`.
- Status updates via `ifDataPresent()` / `ifDataNotPresent()`.

**Distinct steps**
- Parse paths and initialize status tracking.
- Resolve company folder name and status.
- Query last-month closing data for all eligible clients.
- Build XLSX headers and write the results.
- Save the file in each company library folder.
- Notify recipients when data is present.
- Return consolidated status for the run.

---

## ClosingReportFCOSIFPIFtoLibrary_KNT.php

**Purpose**
- Generate a monthly FCOS/IFP/IF closing report in XLSX format per company path and place it in the library.

**Key inputs**
- `$path`, `$reportName`, `$reportBasePath`, `$mailNotification`, `$userType`.

**Data source**
- `HSFLCLNTWF` (last month, closing codes `994`, `995`, `99J`).

**Outputs**
- XLSX file saved to `$reportBasePath . $companyPath`.
- Status updates via `ifDataPresent()` / `ifDataNotPresent()`.

**Distinct steps**
- Parse paths and initialize status tracking.
- Resolve company code and status for each path.
- Query prior-month FCOS/IFP/IF closing records.
- Build the XLSX worksheet with standard headers and styling.
- Write the report to the output folder and capture file size.
- Send notifications when data is present.
- Return consolidated status for the run.

---

## ClosingReportClients_KNT.php vs ClosingReportClientstoLibrary_KNT.php — Key Differences

| # | Area | ClosingReportClients_KNT | ClosingReportClientstoLibrary_KNT |
|---|------|--------------------------|-----------------------------------|
| 1 | **SQL scope** | Filters by `CLIENT_CDE = '<companyName>'` (per-company) | No company filter — fetches all clients excluding `FRIC` |
| 2 | **XLSXWriter instantiation** | Created inside `foreach` loop — fresh writer per company | Created outside the loop — single shared writer |
| 3 | **`$code_names` variable** | Parses `$code_name` into `$code_names` array | Not parsed; `$code_name` accepted but unused |
| 4 | **Helper function** | `getExcelPrefix42()` | `getExcelPrefix43()` |
| 5 | **Sheet name** | `ClosingReportClient` | `closingReportClientToLibrary` |
| 6 | **Column header style** | Spaced names: `'Closing Date'`, `'Client Code'`, `'Process Date'` | Concatenated names: `'ClosingDate'`, `'ClientCode'`, `'ProcessDate'` |
| 7 | **Column widths** | `[20, 30, 20, 30, 20, 30, 30, 30, 20, 30]` — narrower | `[30, 30, 35, 40, 35, 40, 35, 35, 20, 45]` — wider |
| 8 | **Extra `$style1`** | Defined in `getExcelPrefix42()` (font-size 10.5, height 16.5) | Not present in `getExcelPrefix43()` |

**In summary:** `ClosingReportClients` generates a report **per individual company**, while `ClosingReportClientstoLibrary` generates a **single all-clients report** written to each company library folder.

---

## ClosingReportFCOSIFPIF_KNT.php

**Purpose**
- Generate monthly FCOS/IFP/IF closing reports per client code and distribute them based on company status.

**Key inputs**
- `$path`, `$code_name`, `$reportName`, `$userType`, `$mailNotification`, `$run_by`.

**Data source**
- `HSFLCLNTWF` (prior month, closing codes `994`, `995`, `99J`).

**Outputs**
- XLSX files saved under `/var/www/html/bi/dist/<path>` and optionally copied to SFTP destinations.
- Scheduler logs via `scheduler_logs()` and email notifications based on company status.

**Distinct steps**
- Initialize status buckets (active, pending, inactive, terminated).
- For each client code, ensure the output folder exists.
- Build a timestamped filename and query closing records.
- Create the XLSX file with standardized headers and styles.
- Route files based on company status:
  - Active/Inactive: copy to client path via SFTP.
  - Pending: copy to CP download path.
  - Terminated: copy to TRM download path.
- Record file size and log scheduler status.
- Send no-data emails for each status bucket when applicable.
- Return a consolidated status array for the run.
