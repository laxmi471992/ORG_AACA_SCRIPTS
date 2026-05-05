# Invoked Scripts Map for `generateReport_KNT.php`

This document lists scripts invoked by `generateReport_KNT.php`, grouped by runtime purpose.

## 1) Bootstrap / Environment Includes

- `mailsetup.php`
- `pdoconn.php`

> Note: `PHPMailer/donotReply.php` is present as a commented include and is **not executed**.

## 2) Report Dispatcher-Invoked Scripts

These scripts are pulled in by wrapper functions called from `openInventoryReport(...)`.

### Core compliance / daily handlers

- `CostsWhereSuitNotAllowed.php`
- `CostPaymentTransHoldFile.php`
- `FirmSoftListToAACACompliance.php`
- `DirectPaysLoaded.php`
- `ClientRecallListToAACACompliance.php`
- `ClientRecallListReview.php`
- `CostPaymentTransHoldFileAll.php`
- `TransRejectReport.php`
- `ClientRecallsComplianceKeyedRejects.php`
- `FirmCostCheckDetailToMyDownload.php`
- `FirmFeeCheckDetailToMyDownload.php`
- `CollectionReportNCA.php`
- `ConvergenceCollectionsReport.php`
- `ClientDeniedRecallstoMyDownloads.php`
- `ClientDeniedRecallsNoticetoClient.php`
- `DpbalAdjustmentNoticeToFirmPersonnel.php`
- `ClientPlacementAcknowledgmentMYD.php`
- `AgencyFeeCheckDetailToMyDownload.php`
- `AgencyCostCheckDetailToMyDownload.php`
- `DPBALAdjustmentNoticeToAgencyPersonnel.php`

### Weekly / file inquiry / notice handlers

- `FileInquiryReportsforAACAAACAReport.php`
- `FileInquiryReportsForAACAAACARequest.php`
- `FileInquiryReportRequestToMyDownloadfirmrequest.php`
- `FileInquiryReportRequesttoMYDFirmReport.php`
- `FileInquiryReportRequesttoMYDClientRequest.php`
- `FileInquiryNoticetoFirmUse.php`
- `FileInquiryReportRequesttoMYDAgencyRequest.php`
- `FileInquiryReportRequesttoMYDAgencyReport.php`
- `FileInquiryNoticetoAgencyUse.php`
- `RemitingDeptMannualEntry.php`
- `PlacementAcctsHoldForDocsCompliance.php`
- `PlacementAcctsHoldNoticeClient.php`
- `FileInquiryReportLateNoticeCompliance.php`
- `ClosingReportClientsWeeklyMYD.php`
- `ClosingReportClientsWeeklySelectClients.php`
- `FileInquiryReportLateMyDownloads.php`
- `FileInquiryReportLateMyDownloadstoAgency.php`
- `FileInquiryLateNoticeToFirms.php`
- `FileInquiryLateNoticeToAgency.php`
- `FileInquiryReportNoticetoClientsUSE.php`
- `FileInquiryReportClienttoMyDownloads.php`

### Monthly / remittance / reminder / reconciliation handlers

- `ClosingReportClients.php`
- `UnifundMonthlyUpdate.php`
- `ClosingReportClientstoLibrary.php`
- `ClosingReportFCOSIFPIFtoLibrary.php`
- `ClosingReportFCOSIFPIF.php`
- `ComplaintLogLateNoticeFirms.php`
- `ComplaintLogLateNoticeAgency.php`
- `ReconresultsfirmMYD.php`
- `ReconResultsAgencyMYD.php`
- `PDARReportFirmMYD.php`
- `PDARReporttolibrary.php`
- `PDARReportAgencyMYD.php`
- `RemitScheduleMonthEndtofirmpersonnelcopytoAACAremitting.php`
- `RemitScheduleMonthEndtofirmPersonnel.php`
- `RemitScheduleMonthEndtoAgencyPersonnel.php`
- `ComplaintLogReminder.php`
- `CallLogReminder.php`
- `CallLogReminderAgency.php`
- `ComplaintLogReminderAgency.php`
- `PDARReportsCompliance.php`

### Fallback handler

- `MaintFirmSoftClose.php`

### Unifund bundle sub-report handlers

- `UnifundInventoryOpen.php`
- `UnifundInventoryClose.php`
- `UnifundAccountDetail.php`
- `UnifundConsumerDetail.php`
- `UnifundLegalCaseDetail.php`
- `UnifundPlacementDetail.php`
- `UnifundPaymentandreconDetail.php`

---

## 3) How these are invoked

1. Scheduler/manual trigger reaches `openInventoryReport(...)`.
2. `openInventoryReport(...)` routes by `reportName`.
3. Matched wrapper function `include/include_once/require_once` loads a script file.
4. The wrapper then calls a function from that script and returns status payload.
