<?php
/**
 * ============================================================================
 * FIRM COST CHECK DETAIL REPORT GENERATOR — IMPROVED
 * ============================================================================
 *
 * @author      Harsh Gavand
 * @description Generates Excel reports for Firm Cost transactions from the
 *              RMAACABHS database. Processes remittance transactions
 *              (RMSTRANCDE = '1A') and creates detailed financial breakdowns
 *              per client code (PYALORGCD).
 * WORKFLOW
 *   1. Accepts comma-separated company paths and an optional date override.
 *   2. For each company path, resolves the VENDORNUM (company name).
 *   3. Query 1 — SELECT DISTINCT PYALORGCD WHERE RMSTRANCDE='1A' AND BLPYFRTO > 0
 *   4. Query 2 — CTE detail query per PYALORGCD with LEFT JOIN RMSPMASTER
 *   5. Writes Excel file: header + data rows + subtotals + TOTAL row
 *   6. Returns ['status', 'status_msg', 'new_status']
 *
 * CHANGE LOG
 *
 * #1  Empty-file guard     — Query 1 checked before any filesystem operation;
 *                            zero rows → skip company entirely, no file written.
 * #2  BLPYFRTO > 0 filter  — Query 1 only returns PYALORGCD values where at
 *                            least one row has a non-zero Amount Paid To Firm.
 *                            All-zero vendors are skipped — no file created.
 * #3  Correct numeric types— Accumulators initialised as 0.0 (not string '0').
 *                            XLSXWriter writes numeric cells, not text cells.
 * #4  Variable typo fixed  — $FileSizeKB everywhere (never $$FileSizeKB).
 * #5  try/catch boundary   — Each company runs in try/catch; one failure does
 *                            not abort the rest of the batch.
 * #6  Date handling        — Optional $reportDate param; auto-calculates
 *                            Mon = -3 days, other days = -1 day when omitted.
 * ============================================================================
 */

require_once(__DIR__ . '/PHP_XLSXWriter-master/xlsxwriter.class.php');

function firmCostCheckDetailToMyDownload($path, $id, $reportName, $code_name, $userType, $userReportName, $outputName, $reportDescription, $mailNotification, $sftpId, $mode, $run_by, $reportBasePath, $reportDate = null)
{
	$report_start_time = date("H:i:s");
	$date = new DateTime();
	$fileName = filterReportName($date->format('Y-m-d'), $reportName);
	$paths = explode(",", $path);
	$dataPresents = array();
	$noDataPresents = array();
	$new_status = 3;
	$msg = 'No Data Present';
	$status_msg = 'Failed';
	$sftpStatus = 0;
	$status = 0;
	$FileSizeKB = 0;

	// DATE PARAMETER HANDLING
	/**
	 * Check if reportDate parameter is provided
	 * If provided, use it; otherwise auto-calculate based on current day
	 */
	if ($reportDate !== null && !empty($reportDate)) {
		$queryDate = $reportDate;
	} else {
		$currentDay = date('D');
		$queryDate  = ($currentDay == 'Mon')
			? date('Ymd', strtotime('-3 day'))
			: date('Ymd', strtotime('-1 day'));
	}

	// STEP 2 — Build filename and split paths
	$fileName = filterReportName($queryDate, $reportName);
	$paths    = explode(",", $path);

	// OLD CODE — Kept for reference (FirmCostCheckDetailToMyDownload_KNT.php)
	/*
	 * BUG #1 — $writer = new XLSXWriter() created before data check
	 *           → empty files generated for vendors with no transactions
	 * BUG #2 — $RemitAmount = '0' string not float
	 *           → XLSXWriter wrote text cells instead of numeric cells
	 * BUG #3 — No try/catch → one failure crashed the entire batch
	 * BUG #4 — No BLPYFRTO > 0 check → files generated even with all zero amounts
	 *
	 * foreach ($paths as $companyPath) {
	 *     $companyPath        = str_replace(["'", " "], "", $companyPath);
	 *     $RemitAmount        = '0';   // BUG: string not float
	 *     $PaymentAmount      = '0';   // BUG: string not float
	 *     $FeePaidToFirm      = '0';   // BUG: string not float
	 *     $FeeRequestedByFirm = '0';   // BUG: string not float
	 *     $AmountPaidToFirm   = '0';   // BUG: string not float
	 *     $writer             = new XLSXWriter(); // BUG: created before data check
	 *     $companyName        = createDirgetCompanyName($companyPath, $reportBasePath);
	 *     $companyStatus      = getCompanyStatus($companyName);
	 *     // No try/catch — one failure kills entire batch
	 * }
	 */

	// STEP 3 — Loop through each company
	foreach ($paths as $companyPath) {
		try {
			$companyPath = str_replace(["'", " "], "", $companyPath);
			$companyName = createDirgetCompanyName($companyPath, $reportBasePath);

			error_log('[FirmCost][INFO] Processing Cost report for company: ' . $companyName);

			// QUERY 1 — EMPTY-FILE GUARD (core fix)
			//
			// WHY BLPYFRTO > 0:
			//   BLPYFRTO is the Amount Paid To Firm column.
			//   Without this, Query 1 returned client codes even when all amounts
			//   were zero — causing useless empty files to be generated.
			//   With AND BLPYFRTO > 0, only vendors with real non-zero amounts
			//   get a file. If nobody got paid anything, nothing is reported.
			//
			// OLD QUERY (no BLPYFRTO filter — kept for reference):
			/*
			$query = "SELECT DISTINCT PYALORGCD from RMAACABHS
				WHERE RMSTRANCDE='1A'
				AND CAST(DTPRLT AS DATE) >= (case when weekday(CURRENT_DATE()) = 0
					then DATE_SUB(CURRENT_DATE(),INTERVAL 3 DAY)
					else DATE_SUB(CURRENT_DATE(), INTERVAL 1 DAY) end)
				AND VENDORNUM IN ('" . $companyName . "')
				group by PYALORGCD ORDER BY PYALORGCD";
			*/
			// UPDATED QUERY:
			$query = "SELECT DISTINCT PYALORGCD from RMAACABHS
				WHERE RMSTRANCDE = '1A'
				AND CAST(DTPRLT AS DATE) >= '" . $queryDate . "'
				AND VENDORNUM IN ('" . $companyName . "')
				AND BLPYFRTO > 0
				group by PYALORGCD ORDER BY PYALORGCD";

/** OldCode - writing the query results to excel without checking if data exists and no proper handling of numeric types and subtotals
 $results = getResult($query);
		if ($results['numRows'] > 0) {
			$excelPrefix = getExcelPrefix13();
			$writer->writeSheetHeader($excelPrefix['sheetName'], $excelPrefix['headers'], $excelPrefix['style']);
			$blankrow = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
			$writer->writeSheetRow($excelPrefix['sheetName'], $blankrow);
			foreach ($results['results'] as $result) {
 */


// updated code - writing the query results with checking if data exists and proper handling of numeric types and subtotals

			$results = getResult($query);

			if ($results['numRows'] <= 0) {
				error_log('[FirmCost][WARNING] No data for ' . $companyName . ' — file NOT generated.');
				echo "cost : No data present for company: " . $companyName . "\n";
				array_push($noDataPresents, array('paths' => $companyPath, 'filename' => $fileName, 'clientcode' => $companyName));
				continue;
			}

			// STEP 4 — Data exists, initialize Excel
			// XLSXWriter only created AFTER Query 1 confirms data exists
			$writer      = new XLSXWriter();
			$excelPrefix = getExcelPrefix13();
			$blankrow    = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');

			$writer->writeSheetHeader($excelPrefix['sheetName'], $excelPrefix['headers'], $excelPrefix['style']);
			$writer->writeSheetRow($excelPrefix['sheetName'], $blankrow);

			// Grand total accumulators — 0.0 float not string '0'
			$PaymentAmount      = 0.0;
			$RemitAmount        = 0.0;
			$FeeRequestedByFirm = 0.0;
			$FeePaidToFirm      = 0.0;
			$AmountPaidToFirm   = 0.0;

			// STEP 5 — Loop through each PYALORGCD and run Query 2
		
			foreach ($results['results'] as $result) {

				// QUERY 2: GET DETAILED TRANSACTION DATA FOR EACH CLIENT CODE			
				/**
				 * This query retrieves all transaction details for a specific
				 * client code (PYALORGCD) including account info, transaction
				 * dates, amounts, fees, and firm file numbers
				 * 
				 * Note: This query also uses $queryDate variable for consistency
				 */

				$query = "WITH FIRMCOSTFEE AS (
					select BLAAINNM, LEFT(BLAAINNM, 1) as TYPERT, CUROFFCRCD AS 'Client_Code', VENDORNUM AS 'Firm',
					RMSACCTNUM AS 'Acct_Number', RMSCORPNM1 AS 'Last_Name', RMSCORPNM2 AS 'First_Name',
					RMSTRANCDE AS 'TR_CD', RMSTRANDTE AS 'Transaction_Date', RMSTRANDSC AS 'Transaction_Description',
					IF(RMSTRANCDE = '1A', INVCLIENT, 0.00) AS 'Cost_Amount', COLLAM AS 'Payment_Amount', DUECLIENT AS 'Remit_Amount',
					FEESFR AS 'Fee_Requested_by_Firm', FEES AS 'Fee_Paid_to_Firm', INVOICENO AS 'Firm_Invoice_No',
					CHECKNO AS 'Firm_Check_Number', BLPYFRCK AS 'AACA_Check_Number', BLPYFRTO AS 'Amount_Paid_to_Firm',
					DTPRLT AS 'AACA_Check_Date', RMSFILENUM, PYALORGCD, PAIDDATE AS 'Firm_Invoice_Date'
					from RMAACABHS
					WHERE RMSTRANCDE = '1A'
					AND CAST(DTPRLT AS DATE) >= '" . $queryDate . "'
					AND VENDORNUM = '" . $companyName . "'
					AND PYALORGCD = '" . $result['PYALORGCD'] . "')
					SELECT Client_Code as 'Client Code', Acct_Number as 'Acct No.', Last_Name as 'Last Name', First_Name as 'First Name',
					TR_CD as 'TR CD', Transaction_Date as 'Transaction Date', Transaction_Description as 'Transaction Description',
					Payment_Amount AS 'Payment Amount', Remit_Amount AS 'Remit Amount',
					Fee_Requested_by_Firm AS 'Fee Requested By Firm',
					Fee_Paid_to_Firm as 'Fee Paid To Firm', Firm_Invoice_No as 'Firm Invoice No', Firm_Check_Number as 'Firm Check Number',
					Amount_Paid_to_Firm as 'Amount Paid To Firm',
					Firm_Invoice_Date as 'Firm Invoice Date', P.FILELOCATN AS 'Firm File No', PYALORGCD
					from FIRMCOSTFEE
					left join RMSPMASTER AS P ON FIRMCOSTFEE.RMSFILENUM = P.RMSFILENUM
					ORDER BY PYALORGCD, Client_Code, Firm_Invoice_Date, Firm_Invoice_No";
/**  Old Code 
 * not proper handling the numeric types and subtotals
 * and no try catch block to handle errors gracefully
				$results = getResult($query);

				if ($results['numRows'] > 0) {


					$PaymentAmountwise = '0';
					$RemitAmountwise = '0';
					$FeePaidToFirmwise = '0';
					$FeeRequestedByFirmwise = '0';
					$AmountPaidToFirmwise = '0';
					foreach ($results['results'] as $resultRow) {

						$PaymentAmount += $resultRow['Payment Amount'];
						$PaymentAmountwise += $resultRow['Payment Amount'];

						$RemitAmount += $resultRow['Remit Amount'];
						$RemitAmountwise += $resultRow['Remit Amount'];

						$FeeRequestedByFirm += $resultRow['Fee Requested By Firm'];
						$FeeRequestedByFirmwise += $resultRow['Fee Requested By Firm'];

						$FeePaidToFirm += $resultRow['Fee Paid To Firm'];
						$FeePaidToFirmwise += $resultRow['Fee Paid To Firm'];

						$AmountPaidToFirm += $resultRow['Amount Paid To Firm'];
						$AmountPaidToFirmwise += $resultRow['Amount Paid To Firm'];
						$writer->writeSheetRow($excelPrefix['sheetName'], $resultRow);
					}
					$newarray1 = array();
					$newarray1 = [$result['PYALORGCD'], '', '', '', '', '', '', $PaymentAmountwise, $RemitAmountwise, $FeeRequestedByFirmwise, $FeePaidToFirmwise, '', '', $AmountPaidToFirmwise, '', '', ''];
					$writer->writeSheetRow($excelPrefix['sheetName'], $newarray1, $excelPrefix['style1']);
					$blankrow = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
					$writer->writeSheetRow($excelPrefix['sheetName'], $blankrow);
				} else {

					// ifDataNotPresent($companyStatus, $companyName, $reportName, $report_start_time, $sftpStatus, $FileSizeKB, $path, $run_by);
					// array_push($DataPresents, array(
					// 	'paths' => $path,
					// 	'filename' => $fileName,
					// 	'clientcode' => $companyName
					// ));
				}
			}
			$newarray = array();
			$newarray = ['TOTAL', '', '', '', '', '', '', $PaymentAmount, $RemitAmount, $FeeRequestedByFirm, $FeePaidToFirm, '', '', $AmountPaidToFirm, '', '', ''];
			$writer->writeSheetRow($excelPrefix['sheetName'], $newarray, $excelPrefix['style1']);
			$writer->writeToFile(str_replace(__FILE__, $reportBasePath . $companyPath . '/' . $fileName, __FILE__));
			if (file_exists(str_replace(__FILE__, $reportBasePath . $companyPath . '/' . $fileName, __FILE__))) {
				$new_status = 2;
				$$FileSizeKB = 0;
				$FileSizeKB = getfileSize($reportBasePath . $companyPath . '/' . $fileName);
				// VK26JAN2026 mailNotifaction($mailNotification, $companyPath, $companyName, $userType, $userReportName, $reportDescription);


				array_push($dataPresents, array(
					'paths' => $companyPath,
					'filename' => $fileName,
					'clientcode' => $companyName
				));
				// VK26JAN2026 ifDataPresent($companyStatus, $companyName, $reportName, $report_start_time, $sftpStatus, $FileSizeKB, $companyPath, $run_by,$userType);
				echo "Cost report generated successfully for company: " . $companyName . "\n"; // VK26JAN2026	
			}
		} else {
			// VK26JAN2026 ifDataNotPresent($companyStatus, $companyName, $reportName, $report_start_time, $sftpStatus, $FileSizeKB, $companyPath, $run_by,$userType);
			echo "cost : No data present for company: " . $companyName . "\n"; // VK26JAN2026

			array_push($noDataPresents, array(
				'paths' => $companyPath,
				'filename' => $fileName,
				'clientcode' => $companyName
			));
		}
		if (!empty($dataPresents)) {
			$new_status = 2;
			$msg = 'Report generated successfully';
			$status_msg = 'generated';
			$status = 1;
		} else {
			$new_status = 3;
			$msg = 'No Data Present';
			$status_msg = 'Failed';
			$status = 0;
		}
	}

	return array('status' => $status, 'status_msg' => $status_msg, 'new_status' => $new_status);
}

*/


// Updated Code with proper handling of numeric types, subtotals, and try/catch blocks


				$results = getResult($query);

				if ($results['numRows'] > 0) {

					$PaymentAmountwise      = 0.0;
					$RemitAmountwise        = 0.0;
					$FeeRequestedByFirmwise = 0.0;
					$FeePaidToFirmwise      = 0.0;
					$AmountPaidToFirmwise   = 0.0;

					foreach ($results['results'] as $resultRow) {
						$PaymentAmountwise      += (float)$resultRow['Payment Amount'];
						$RemitAmountwise        += (float)$resultRow['Remit Amount'];
						$FeeRequestedByFirmwise += (float)$resultRow['Fee Requested By Firm'];
						$FeePaidToFirmwise      += (float)$resultRow['Fee Paid To Firm'];
						$AmountPaidToFirmwise   += (float)$resultRow['Amount Paid To Firm'];
						$writer->writeSheetRow($excelPrefix['sheetName'], $resultRow);
					}

					// Subtotal row for this PYALORGCD
					$newarray1 = array($result['PYALORGCD'], '', '', '', '', '', '', $PaymentAmountwise, $RemitAmountwise, $FeeRequestedByFirmwise, $FeePaidToFirmwise, '', '', $AmountPaidToFirmwise, '', '', '');
					$writer->writeSheetRow($excelPrefix['sheetName'], $newarray1, $excelPrefix['style1']);
					$writer->writeSheetRow($excelPrefix['sheetName'], $blankrow);

					// Roll into grand totals
					$PaymentAmount      += $PaymentAmountwise;
					$RemitAmount        += $RemitAmountwise;
					$FeeRequestedByFirm += $FeeRequestedByFirmwise;
					$FeePaidToFirm      += $FeePaidToFirmwise;
					$AmountPaidToFirm   += $AmountPaidToFirmwise;
				}
			}

			// Grand TOTAL row
			$newarray = array('TOTAL', '', '', '', '', '', '', $PaymentAmount, $RemitAmount, $FeeRequestedByFirm, $FeePaidToFirm, '', '', $AmountPaidToFirm, '', '', '');
			$writer->writeSheetRow($excelPrefix['sheetName'], $newarray, $excelPrefix['style1']);
	
			// STEP 6 — Write file to disk
			$outputDir  = rtrim($reportBasePath, '/\\') . DIRECTORY_SEPARATOR . trim($companyPath, '/\\');
			if (!is_dir($outputDir)) {
				mkdir($outputDir, 0777, true);
			}
			$outputFile = $outputDir . DIRECTORY_SEPARATOR . $fileName;
			$writer->writeToFile($outputFile);

			if (file_exists($outputFile)) {
				$FileSizeKB = getfileSize($outputFile); // single $ — never $$
				error_log('[FirmCost][INFO] Cost report generated: ' . $outputFile . ' (' . $FileSizeKB . ')');
				echo "Cost report generated successfully for company: " . $companyName . "\n";
				array_push($dataPresents, array('paths' => $companyPath, 'filename' => $fileName, 'clientcode' => $companyName));
			} else {
				error_log('[FirmCost][ERROR] File write failed: ' . $outputFile);
				array_push($noDataPresents, array('paths' => $companyPath, 'filename' => $fileName, 'clientcode' => $companyName));
			}

		} catch (Throwable $e) {
			error_log('[FirmCost][ERROR] Exception for path=\'' . $companyPath . '\': ' . $e->getMessage());
			array_push($noDataPresents, array('paths' => $companyPath, 'filename' => $fileName ?? '', 'clientcode' => 'UNKNOWN'));
		}
	}

	// STEP 7 — Return final status
	$generated = !empty($dataPresents);
	error_log('[FirmCost][INFO] Cost batch done — generated: ' . count($dataPresents) . ', skipped/failed: ' . count($noDataPresents));

	return array(
		'status'     => $generated ? 1 : 0,
		'status_msg' => $generated ? 'Report generated successfully' : 'No Data Present',
		'new_status' => $generated ? 2 : 3
	);
}



// EXCEL STRUCTURE

function getExcelPrefix13()
{
	$header = array(
		'Client Code'             => 'string',
		'Acct No.'                => 'string',
		'Last Name'               => 'string',
		'First Name'              => 'string',
		'TR CD'                   => 'string',
		'Transaction Date'        => 'string',
		'Transaction Description' => 'string',
		'Payment Amount'          => 'dollar',
		'Remit Amount'            => 'dollar',
		'Fee Requested By Firm'   => 'dollar',
		'Fee Paid To Firm'        => 'dollar',
		'Firm Invoice No'         => 'string',
		'Firm Check Number'       => 'string',
		'Amount Paid To Firm'     => 'dollar',
		'Firm Invoice Date'       => 'string',
		'Firm File No'            => 'string',
		'PYALORGCD'               => 'string',
	);
	$style = array(
		'font-style' => 'bold',
		'fill'       => '#eee',
		'halign'     => 'center',
		'border'     => 'left, right, top, bottom',
		'widths'     => [20, 20, 20, 20, 20, 20, 20, 30, 20, 20, 20, 20, 20, 20, 20, 20, 20],
	);
	$style1 = array(
		'font-style' => 'bold',
		'fill'       => '#eee',
		'font-size'  => '10.5',
		'height'     => '16.5',
	);
	return array(
		'headers'   => $header,
		'style'     => $style,
		'style1'    => $style1,
		'sheetName' => 'FirmcostCheckDetailToMyDownload',
	);
}