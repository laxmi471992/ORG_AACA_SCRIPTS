<?php
/** ----------------------------------------------------------------------------*
 * Filename: ClosingReportClientsWeeklyMYD_KNT.php
 * @author      KEANT Technologies
 * @description
 * - Generates a weekly MYD closing report in XLSX format per company path.
 * - Derives the company code for each path and queries RMAACABHS for recent
 *   RMSTRANCDE='1A' transactions in the last 7 days.
 * - Writes results with standardized headers and styling to the report base
 *   path and tracks file size and notification status.
 * - Records success or no-data outcomes via ifDataPresent/ifDataNotPresent and
 *   returns a summary status array.
 * ----------------------------------------------------------------------------
 * CHANGELOG:
 * ----------------------------------------------------------------------------
 * Version | Date       | Author              | Description
 * ----------------------------------------------------------------------------
 * 1.0     | 2026-02-10 | KEANT Technologies  | Initial version with developer
 *         |            |                     | documentation and changelog
 * ----------------------------------------------------------------------------
 */
// XLSX writer library used for report generation.
include('PHP_XLSXWriter/xlsxwriter.class.php');
use XLSXWriter;

function closingreportclientsweeklyMYD($path, $id, $reportName, $code_name, $userType, $userReportName, $outputName, $reportDescription, $mailNotification, $sftpId, $mode, $run_by, $reportBasePath)
{
    // Report timing and file naming.
    $report_start_time = date("H:i:s");
    $date = new DateTime();
    $fileName = filterReportName($date, $reportName);

    // Normalize inputs and initialize status tracking.
    $paths = explode(",", $path);
    $dataPresents = array();
    $noDataPresents = array();
    $new_status = 3;
    $msg = 'No Data Present';
    $status_msg = 'Failed';
    $sftpStatus = 0;
    $status = 0;
    $FileSizeKB = 0;

    // Process each company path independently.
    foreach ($paths as $companyPath) {
        $companyPath = str_replace(["'", " "], "", $companyPath);
        $writer = new XLSXWriter();
        $companyName = createDirgetCompanyName($companyPath, $reportBasePath);
        $companyStatus = getCompanyStatus($companyName);

        // Pull weekly MYD transactions for the company.
        $query = "SELECT BLAAINNM, LEFT(BLAAINNM, 1) as TYPERT, CUROFFCRCD AS 'Client Code', VENDORNUM AS 'Firm',
            RMSACCTNUM AS 'Acct Number',RMSCORPNM1 AS 'Last Name', RMSCORPNM2 AS 'First Name',
            RMSTRANCDE AS 'TR CD', RMSTRANDTE AS 'Transaction Date', RMSTRANDSC AS 'Transaction Description',
            (case when RMSTRANCDE = '1A' then INVCLIENT else 0.00 end ) AS 'Cost Amount', COLLAM AS 'Payment Amount', DUECLIENT AS 'Remit Amount',
            FEESFR AS 'Fee Requested by Firm', FEES AS 'Fee Paid to Firm', INVOICENO AS 'Firm Invoice No',
            CHECKNO AS 'Firm Check Number', BLPYFRCK AS 'AACA Check Number',BLPYFRTO AS 'Amount Paid to Firm',
            DTPRLT AS 'AACA Check Date', RMSFILENUM, PYALORGCD, PAIDDATE  AS 'Firm Invoice Date', PYALTRNM
            FROM RMAACABHS
            WHERE RMSTRANCDE = '1A' AND 
            CAST(DTPRLT AS DATE) >=DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)
            AND PYALORGCD ='" . $companyName . "'";

        $results = getResult($query);
        // Build the Excel file if data exists; otherwise log as no data.
        if ($results['numRows'] > 0) {
            $excelPrefix = getExcelPrefix38();
            $writer->writeSheetHeader($excelPrefix['sheetName'], $excelPrefix['headers'], $excelPrefix['style']);
            foreach ($results['results'] as $result) {
                $writer->writeSheetRow($excelPrefix['sheetName'], $result);
            }

            // Save the report file and record success metadata.
            $writer->writeToFile(str_replace(__FILE__, $reportBasePath . $companyPath . '/' . $fileName, __FILE__));
            if (file_exists(str_replace(__FILE__, $reportBasePath . $companyPath . '/' . $fileName, __FILE__))) {
                $FileSizeKB = getfileSize($reportBasePath . $companyPath . '/' . $fileName);
                mailNotifaction($mailNotification, $companyPath, $companyName, $userType, $userReportName, $reportDescription);

                array_push($dataPresents, array(
                    'paths' => $companyPath,
                    'filename' => $fileName,
                    'clientcode' => $companyName
                ));
                ifDataPresent($companyStatus, $companyName, $reportName, $report_start_time, $sftpStatus, $FileSizeKB, $companyPath, $run_by,$userType);
            }
        } else {
            // Record the no-data case for this company.
            ifDataNotPresent($companyStatus, $companyName, $reportName, $report_start_time, $sftpStatus, $FileSizeKB, $companyPath, $run_by,$userType);

            array_push($noDataPresents, array(
                'paths' => $companyPath,
                'filename' => $fileName,
                'clientcode' => $companyName
            ));
        }
        if (!empty($dataPresents)) {
            $new_status = 2;
            $status_msg = 'generated';
            $status = 1;
        } else {
            $new_status = 3;
            $status_msg = 'Failed';
            $status = 0;
        }
    }

    return array('status' => $status, 'status_msg' => $status_msg, 'new_status' => $new_status);
}


function getExcelPrefix38()
{
    // Define report column headers and data types.
    $header = array(
        'BLAAINNM' => 'string',
        'TYPERT' => 'string',
        'Client Code' => 'string',
        'Firm' => 'string',
        'Acct Number' => 'string',
        'Last Name' => 'string',
        'First Name' => 'string',
        'TR CD' => 'string',
        'Transaction Date' => 'string',
        'Transaction Description' => 'string',
        'Cost Amount' => 'dollar',
        'Payment Amount' => 'dollar',
        'Remit Amount' => 'dollar',
        'Fee Requested by Firm' => 'dollar',
        'Fee Paid to Firm' => 'dollar',
        'Firm Invoice No' => 'string',
        'Firm Check Number' => 'string',
        'AACA Check Number' => 'string',
        'Amount Paid to Firm' => 'dollar',
        'AACA Check Date' => 'string',
        'RMSFILENUM' => 'string',
        'PYALORGCD' => 'string',
        'Firm Invoice Date' => 'string',
        'PYALTRNM' => 'string'

    );

    // Header styling for the worksheet.
    $style = array(
        'font-style' => 'bold',
        'fill' => '#eee',
        'halign' => 'center',
        'border' => 'left, right, top, bottom',
        'widths' => [20, 20, 20, 20, 25, 20, 20, 20, 20, 30, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20]
    );

    // Worksheet name for the report.
    $sheetName = 'closingReportclientWeeklyMYD';

    // Return all sheet metadata for the XLSX writer.
    return ['headers' => $header, 'style' => $style, 'sheetName' => $sheetName];
}

