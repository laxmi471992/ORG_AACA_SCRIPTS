<?php
/** ----------------------------------------------------------------------------*
 * Filename: ClosingReportFCOSIFPIFtoLibrary_KNT.php
 * @author      KEANT Technologies
 * @description
 * - Generates a monthly FCOS/IFP/IF closing report in XLSX format per company
 *   path using HSFLCLNTWF data from the last month.
 * - Filters closing codes (994, 995, 99J) and formats results with standard
 *   headers and styling.
 * - Writes the report file to the report base path and sends notifications
 *   when data is present.
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
require_once('PHP_XLSXWriter/xlsxwriter.class.php');
// use XLSXWriter;

function closingReportFCOSIFPIFtoLibrary($path, $id, $reportName, $code_name, $userType, $userReportName, $outputName, $reportDescription, $mailNotification, $sftpId, $mode, $run_by, $reportBasePath)
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

        // Pull prior-month closing records for FCOS/IFP/IF codes.
        $query = "SELECT ACCT_NUM AS 'Acct Number', WFNAME AS Name, CURR_STS_CD AS 'Closing Code',
            CURR_STS_DESC AS Description, DATE_FORMAT(LSTSTATCHG ,'%Y/%m/%d') AS 'Closing Date',
            CURR_ATTY_NME AS Firm, CLT_CDE AS 'Client Code',
            DATE_FORMAT(CURRENT_DATE(), '%Y%m%d') AS 'Process Date', ORGCODE AS 'Org Code',
            WFORGNM AS 'Org Name'
            FROM HSFLCLNTWF
            WHERE CAST(LSTSTATCHG AS DATE)>=DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)
            AND CURR_STS_CD IN ('994','995','99J')";

        $results = getResult($query);

        // Build the Excel file if data exists; otherwise log as no data.
        if ($results['numRows'] > 0) {
            $excelPrefix = getExcelPrefix44();
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
function getExcelPrefix44()
{
    // Define report column headers and data types.
    $header = array(
        'Account Number' => 'string',
        'Name' => 'string',
        'Closing Code' => 'string',
        'Description' => 'string',
        'Closing Date' => 'string',
        'FIRM' => 'string',
        'Client Code' => 'string',
        'Process Date' => 'string',
        'Org Code' => 'string',
        'Org Name' => 'string'
    );

    // Header styling for the worksheet.
    $style = array(
        'font-style' => 'bold',
        'fill' => '#3366ff',
        'color' => '#fff',
        'halign' => 'center',
        'border' => 'left, right, top, bottom',
        'widths' => [20, 20, 30, 20, 20, 40, 20, 35, 20, 40]
    );

    // Worksheet name for the report.
    $sheetName = 'CLosingReportFCOSIFPIFToLibrary';

    // Return all sheet metadata for the XLSX writer.
    return (['headers' => $header, 'style' => $style, 'sheetName' => $sheetName]);
}

