<?php
/** ----------------------------------------------------------------------------*
 * Filename: ClosingReportClientstoLibrary.php
 * @author      KEANT Technologies
 * @description
 * - Generates a monthly closing-clients XLSX report for all eligible clients.
 * - Uses the report base path and company folder to build output locations.
 * - Queries MASTER_DATA_DB for accounts closed in the last month
 *   (excluding CLIENT_CDE 'FRIC') and maps fields to a worksheet.
 * - Applies standard headers and styling before writing the XLSX file.
 * - Sends notification emails when data is present and logs report status.
 * - Returns a consolidated status array for scheduler tracking.
 * ----------------------------------------------------------------------------
 * CHANGELOG:
 * ----------------------------------------------------------------------------
 * Version | Date       | Author              | Description
 * ----------------------------------------------------------------------------
 * 1.0     | 2026-02-12 | KEANT Technologies  | Add documentation, changelog,
 *         |            |                     | and section comments
 * ----------------------------------------------------------------------------
 */
// XLSX writer library used for report generation.
require_once('PHP_XLSXWriter/xlsxwriter.class.php');
// use XLSXWriter;

function closingReportClientstoLibrary($path, $id, $reportName, $code_name, $userType, $userReportName, $outputName, $reportDescription, $mailNotification, $sftpId, $mode, $run_by, $reportBasePath)
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
    $writer = new XLSXWriter();

    // Process each company path independently.
    foreach ($paths as $companyPath) {
        $companyPath = str_replace(["'", " "], "", $companyPath);
        $companyName = createDirgetCompanyName($companyPath, $reportBasePath);
        $companyStatus = getCompanyStatus($companyName);

        // Pull closed accounts for the last month across eligible clients.
        $query = "SELECT RMSACCTNUM AS 'Acct Number', CONCAT_WS(' ',DEBTOR_LAST_NME, DEBTOR_FIRST_NME) AS 'Name',
            CUR_STATUS_CDE AS 'Closing Code', CUR_STATUS_CDE_DESC AS 'Description', DATE_FORMAT(CLOSED_DT,'%Y/%m/%d') AS 'ClosingDate',
            ATTY_NAME AS 'Firm', PORTFOLIO_CDE AS 'ClientCode', date_format(CURRENT_DATE(),'%Y%m%d') AS 'ProcessDate',
            CLIENT_CDE AS 'Org Code', CONCAT_WS(' ',CLIENT_NME,CLIENT_NME2) AS 'Org Name' 
            FROM MASTER_DATA_DB
            where CLIENT_CDE <> 'FRIC'
            and CLOSED_DT >= DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)
            ORDER BY CLIENT_CDE";

        $results = getResult($query);
        // Build the Excel file if data exists; otherwise log as no data.
        if ($results['numRows'] > 0) {
            $excelPrefix = getExcelPrefix43();
            $writer->writeSheetHeader($excelPrefix['sheetName'], $excelPrefix['headers'], $excelPrefix['style']);
            foreach ($results['results'] as $resultRow) {
                $writer->writeSheetRow($excelPrefix['sheetName'], $resultRow);
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
    }

    // Final summary status across all companies.
    if (!empty($dataPresents)) {
        $new_status = 2;
        $status_msg = 'generated';
        $status = 1;
    } else {
        $new_status = 3;
        $status_msg = 'Failed';
        $status = 0;
    }
    return array('status' => $status, 'status_msg' => $status_msg, 'new_status' => $new_status);
}
function getExcelPrefix43()
{
    // Define report column headers and data types.
    $header = array(
        'Acct Number' => 'string',
        'Name' => 'string',
        'Closing Code' => 'string',
        'Description' => 'string',
        'ClosingDate' => 'string',
        'Firm' => 'string',
        'ClientCode' => 'string',
        'ProcessDate' => 'string',
        'Org Code' => 'string',
        'Org Name' => 'string'
    );
    // Header styling for the worksheet.
    $style = array(
        'font-style' => 'bold',
        'fill' => '#eee',
        'halign' => 'center',
        'border' => 'left, right, top, bottom',
        'widths' => [30, 30, 35, 40, 35, 40, 35, 35, 20, 45]

    );

    // Worksheet name for the report.
    $sheetName = 'closingReportClientToLibrary';

    return (['headers' => $header, 'style' => $style, 'sheetName' => $sheetName]);
}

