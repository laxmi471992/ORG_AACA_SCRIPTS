<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
session_start();
date_default_timezone_set('America/New_York');


include('PHPMailer/donotReply.php');
// chdir('/var/www/html/bi/clonedist');


require_once('/var/www/html/bi/dist/pdoconn.php');
$username = @$_SESSION['fullName'] . ' ' . @$_SESSION['LastName'];
$reportBasePath = "/var/www/html/bi/clonedist/Mako/downloadfile/";

if ($_POST['submit']) {
	if ($_POST['reportName'] && $_POST['firmCode'] && $_POST['date']) {

		$reportName = $_POST['reportName'];
		$firmCode = $_POST['firmCode'];
		$current_date=$_POST['date'];
		
		global $pdo;
		 if ($reportName == 'Firm Cost Check Detail To MyDownload') {
			$output = firm_Cost_Check_Detail_To_MyDownload_manual($firmCode, $reportName,$mainFolder = 'AC', $reportBasePath, $current_date);
		} else if ($reportName == 'Firm Fee Check Detail To MyDownload') {
		
			$output = firm_Fee_Check_Detail_To_MyDownload_manual($firmCode, $reportName,$mainFolder = 'AC', $reportBasePath, $current_date);
		}
		$_SESSION['status']=$output['status'];
		$_SESSION['status_msg']=$output['status_msg'];
		// echo json_encode(array('status' => $output['status'], 'status_msg' => $output['status_msg']));
		header('location:'.$_SESSION['getUrl']);
		exit();
	}
}
function firm_Cost_Check_Detail_To_MyDownload_manual($firmCode, $reportName, $mainFolder, $reportBasePath, $current_date)
{
	include_once('./Manual/FirmCostCheckDetailToMyDownload.php');
	$result = firmCostCheckDetailToMyDownload($firmCode, $reportName, $mainFolder, $reportBasePath, $current_date);
	return $result;
}
function firm_Fee_Check_Detail_To_MyDownload_manual($firmCode, $reportName,$mainFolder, $reportBasePath, $current_date)
{
	include_once('./Manual/FirmFeeCheckDetailToMyDownload.php');
	$result = firmFeeCheckDetailToMyDownloadManual($firmCode, $reportName,$mainFolder, $reportBasePath, $current_date);
	return $result;
}

//************************************************** Common Functions ******************************************//
function getFolderName($file)
{
	if ($file == 'UD') {
		$result = 'ACCOUNT UPDATES';
	} else if ($file == 'AC') {
		$result = 'ACCOUNTING';
	} else if ($file == 'AD') {
		$result = 'ADMINISTRATION';
	} else if ($file == 'AF') {
		$result = 'AFFIDAVITS';
	} else if ($file == 'DP') {
		$result = 'BALANCE/ADJUSTMENT NOTIFICATION';
	} else if ($file == 'RM') {
		$result = 'CLIENT REMITTANCE';
	} else if ($file == 'CS') {
		$result = 'CLIENT SKIP RESULTS';
	} else if ($file == 'CO') {
		$result = 'COMPANY';
	} else if ($file == 'CP') {
		$result = 'COMPLIANCE';
	} else if ($file == 'CM') {
		$result = 'CONTRACT MASTER';
	} else if ($file == 'RD') {
		$result = 'DENIED RECALLS';
	} else if ($file == 'FT') {
		$result = 'FINANCIAL TRANSACTION';
	} else if ($file == 'KH') {
		$result = 'HOLD LIST REVIEW';
	} else if ($file == 'JC') {
		$result = 'JUDGMENT INFORMATION FILE';
	} else if ($file == 'JD') {
		$result = 'JUDGMENT REPORT';
	} else if ($file == 'KP') {
		$result = 'KEEPER LIST REQUEST';
	} else if ($file == 'MD') {
		$result = 'MEDIA';
	} else if ($file == 'IT') {
		$result = 'MIS DEPT';
	} else if ($file == 'PH') {
		$result = 'PHONE REPORT';
	} else if ($file == 'PA') {
		$result = 'PLACEMENT ALLOCATION';
	} else if ($file == 'PL') {
		$result = 'PLACEMENTS';
	} else if ($file == 'RA') {
		$result = 'REASSIGNED PLACEMENTS';
	} else if ($file == 'RC') {
		$result = 'RECALL';
	} else if ($file == 'RJ') {
		$result = 'REJECT';
	} else if ($file == 'RO') {
		$result = 'REOPENED PLACEMENTS';
	} else if ($file == 'WS') {
		$result = 'WEB SERVICES';
	} else if ($file == 'DP') {
		$result = 'DIRECT PAYMENT';
	} else if ($file == 'TR') {
		$result = 'TEST REPORT';
	} else {
		$result = 'MY DOWNLOADS';
	}
	return $result;
}

function getEmailId($code, $userType, $folderName, $path)
{
	global $pdo;
	$result = [];
	if ($userType == 1) {
		if ($folderName == 'MY DOWNLOADS') {
			$split = explode("/", $path);
			$newFileName = $split[count($split) - 1];

			$fetchQuery = $pdo->prepare("SELECT DISTINCT RGEMAIL FROM WSREGUSR WHERE RGUSER = '" . $newFileName . "'");
			$fetchQuery->execute();
			$rows_data = $fetchQuery->fetchAll();

			if (count($rows_data)) {
				foreach ($rows_data as $row)
					$result[] = $row['RGEMAIL'];
			} else {

				$result[] = $newFileName;
			}
		} else {
			$statement = "SELECT DISTINCT email from tbl_login WHERE FIND_IN_SET('" . $folderName . "',UserGroup) AND userType ='" . $userType . "' AND company_status != 4 AND bit_deleted_flag = 0";

			$query = $pdo->prepare($statement);
			$query->execute();
			$rows = $query->fetchAll();

			if (count($rows)) {
				foreach ($rows as $row)
					$result[] = $row['email'];
			}
		}
	}

	if ($userType == 2) {
		if ($folderName == 'MY DOWNLOADS') {
			$split = explode("/", $path);
			$newFileName = $split[count($split) - 1];
			$fetchQuery = $pdo->prepare("SELECT DISTINCT RGEMAIL FROM WSREGUSR WHERE RGUSER = '" . $newFileName . "'");
			$fetchQuery->execute();
			$rows_data = $fetchQuery->fetchAll();
			if (count($rows_data)) {
				foreach ($rows_data as $row)
					$result[] = $row['RGEMAIL'];
			} else {
				$result[] = $newFileName;
			}
		} else {
			$statement = "SELECT DISTINCT email from tbl_login WHERE ((FIND_IN_SET('" . $code . "',firmCode))>0 OR firmCode='ALL') AND FIND_IN_SET('" . $folderName . "',UserGroup) AND userType ='" . $userType . "' AND company_status != 4 AND bit_deleted_flag = 0 ";

			$query = $pdo->prepare($statement);
			$query->execute();
			$rows = $query->fetchAll();

			if (count($rows)) {
				foreach ($rows as $row)
					$result[] = $row['email'];
			}
		}
	} else if ($userType == 3) {
		if ($folderName == 'MY DOWNLOADS') {
			$split = explode("/", $path);
			$newFileName = $split[count($split) - 1];
			$fetchQuery = $pdo->prepare("SELECT DISTINCT RGEMAIL FROM WSREGUSR WHERE RGUSER = '" . $newFileName . "'");
			$fetchQuery->execute();
			$rows_data = $fetchQuery->fetchAll();
			if (count($rows_data)) {
				foreach ($rows_data as $row)
					$result[] = $row['RGEMAIL'];
			} else {
				$result[] = $newFileName;
			}
		} else {
			$statement = "SELECT DISTINCT email from tbl_login  WHERE ((FIND_IN_SET('" . $code . "',clientCode))>0 OR clientCode='ALL') AND FIND_IN_SET('" . $folderName . "',UserGroup) AND userType ='" . $userType . "' AND company_status != 4 AND bit_deleted_flag = 0 ";
			$query = $pdo->prepare($statement);
			$query->execute();
			$rows = $query->fetchAll();

			if (count($rows)) {
				foreach ($rows as $row)
					$result[] = $row['email'];
			}
		}
	} else if ($userType == 4) {
		if ($folderName == 'MY DOWNLOADS') {
			$split = explode("/", $path);
			$newFileName = $split[count($split) - 1];


			$fetchQuery = $pdo->prepare("SELECT DISTINCT RGEMAIL FROM WSREGUSR WHERE RGUSER = '" . $newFileName . "'");
			$fetchQuery->execute();
			$rows_data = $fetchQuery->fetchAll();
			if (count($rows_data)) {
				foreach ($rows_data as $row)
					$result[] = $row['RGEMAIL'];
			} else {
				$result[] = $newFileName;
			}
		} else {
			$statement = "SELECT DISTINCT email from tbl_login  WHERE ((FIND_IN_SET('" . $code . "',firmCode))>0 OR firmCode='ALL') AND FIND_IN_SET('" . $folderName . "',UserGroup) AND userType ='" . $userType . "' AND company_status != 4 AND bit_deleted_flag = 0 ";

			$query = $pdo->prepare($statement);
			$query->execute();
			$rows = $query->fetchAll();

			if (count($rows)) {
				foreach ($rows as $row)
					$result[] = $row['email'];
			}
		}
	}
	return $result;
}

function update_report_status()
{
	global $pdo;
	$statement = "SELECT BatchId FROM Batch_Report WHERE DATE(NOW()) BETWEEN StartDate AND EndDate AND IsDelete = 0 ";
	$query = $pdo->prepare($statement);
	$query->execute();
	$rows = $query->fetchAll();
	$query->closeCursor();

	if (count($rows) != 0) {
		foreach ($rows as $row) {
			$id = $row['BatchId'];
			upload_data($id);
		}
	}
	$statement2 = "SELECT BatchId FROM Batch_Report WHERE EndDate = '' AND IsDelete = 0";
	$query2 = $pdo->prepare($statement2);
	$query2->execute();
	$rows2 = $query2->fetchAll();
	$query2->closeCursor();
	if (count($rows2) != 0) {
		foreach ($rows2 as $row) {
			$id = $row['BatchId'];
			upload_data($id);
		}
	}
}

function upload_data($id)
{
	global $pdo;
	$statement = "UPDATE `Batch_Report` SET status = 0 WHERE BatchId ='" . $id . "' ";
	$query = $pdo->prepare($statement);
	$query->execute();
	$query->closeCursor();
}

function compareDailyReport()
{
	global $pdo;
	$current_date = date("Y-m-d");
	$statement = "SELECT batchId FROM SCHEDULER_DAILY_REPORT_LOGS WHERE createdAt = '" . $current_date . "' ";
	$query = $pdo->prepare($statement);
	$query->execute();
	$rows = $query->fetchAll();
	if (count($rows) != 0) {
		foreach ($rows as $row) {
			$ids[] = $row['batchId'];
		}
	}

	if (isset($ids)) {
		$str = implode(',', $ids);
		$statement2 = "SELECT * FROM Batch_Report WHERE BatchId IN ($str) AND (SELECT count(1) FROM Batch_Report WHERE BatchId IN ($str)) = (SELECT count(1) FROM Batch_Report WHERE BatchId IN ($str) AND (status = 2 OR status = 3))";

		$query2 = $pdo->prepare($statement2);
		$query2->execute();
		$rows2 = $query2->fetchAll();

		if (count($rows2) != 0) {
			dailyReportMail();
		}
	}
}

function dailyReportMail()
{
	global $pdo;
	$current_date = date("Y-m-d");

	$statement = "SELECT * FROM SCHEDULER_LOGS WHERE createdAt = '" . $current_date . "' AND is_sent = 0";
	$query = $pdo->prepare($statement);
	$query->execute();
	$rows = $query->fetchAll();
	$query->closeCursor();
	echo "dailyReportMail inside ";

	if (count($rows) != 0) {

		$message = '<div>Hi,</div><br>';
		$message .= 'Please find the daily scheduled report status.<br><br>';
		$message .= '<table rules="all" style="border-color: #666; table-layout: fixed; width: 450px;" cellpadding="10">';
		$message .= "<tr style='background: #eee;'><th>Report Name:</th><th style='width: 78px;'>Recipients:</th><th>Start Time:</th><th>End Time:</th><th>Report Status:</th></tr>";

		foreach ($rows as $value) {
			if ($value['status'] == 1) {
				$status = "Success";
			} else {
				$status = "No Data available";
				//$status = "Failed";
			}

			if ($value['code'] == "ALL") {
				$code = "AACA";
			} else {
				$code = $value['code'];
			}
			$message .= "<tr style='background: #eee;'><td>" . $value['report_name'] . "</td><td>" . $code . "</td><td>" . $value['start_time'] . "</td><td>" . $value['end_time'] . "</td><td>" . $status . "</td></tr>";
		}
		$message .= "</table>";
		$message .= "Thank you.";
		$date = date("m/d/Y");
		$subject = 'Scheduled Report Status ' . $date . '';
		$contacts = array("pipeway.support@goolean.tech");
		foreach ($contacts as $contact) {
			Send_email($subject, $message, $contact);
		}
		$statement2 = "UPDATE SCHEDULER_LOGS SET is_sent = 1 WHERE createdAt = '" . $current_date . "' ";
		$query2 = $pdo->prepare($statement2);
		$query2->execute();
	}
}

function dailyReportDetails()
{
	global $pdo;
	$current_date = date('Y-m-d');
	$day = date('l', strtotime($current_date));

	if ($day == 'Monday') {
		$day = " AND FIND_IN_SET('1',Day)";
	} else if ($day == 'Tuesday') {
		$day = " AND FIND_IN_SET('2',Day)";
	} else if ($day == 'Wednesday') {
		$day = " AND FIND_IN_SET('3',Day)";
	} else if ($day == 'Thursday') {
		$day = " AND FIND_IN_SET('4',Day)";
	} else if ($day == 'Friday') {
		$day = " AND FIND_IN_SET('5',Day)";
	} else if ($day == 'Saturday') {
		$day = " AND FIND_IN_SET('6',Day)";
	} else if ($day == 'Sunday') {
		$day = " AND FIND_IN_SET('7',Day)";
	}

	// Daily if EndDate is null
	$statement = "SELECT * FROM Batch_Report WHERE EndDate = '' AND recurrence_pattern = 'Daily' AND IsDelete = 0 AND schedule_status = 0 AND status =0";
	$query2 = $pdo->prepare($statement);
	$query2->execute();
	$rows1 = $query2->fetchAll();
	if ($rows1 != 0) {
		foreach ($rows1 as $row) {
			$id = $row['BatchId'];
			$reportName = $row['ReportName'];
			insertReportData($id, $reportName);
		}
	}

	// Daily if both StartDate and EndDate are available
	$statement2 = "SELECT * FROM Batch_Report WHERE DATE(NOW()) BETWEEN StartDate AND EndDate AND recurrence_pattern = 'Daily' AND IsDelete = 0 AND schedule_status = 0 AND status = 0";
	$query2 = $pdo->prepare($statement2);
	$query2->execute();
	$rows2 = $query2->fetchAll();

	if (count($rows2) != 0) {
		foreach ($rows2 as $row) {
			$id = $row['BatchId'];
			;
			$reportName = $row['ReportName'];
			insertReportData($id, $reportName);
		}
	}

	// Weekly if EndDate is null
	$statement3 = "SELECT * FROM Batch_Report WHERE EndDate = '' AND recurrence_pattern = 'Weekly' AND IsDelete = 0 AND schedule_status = 0 AND status = 0 " . $day;
	$query3 = $pdo->prepare($statement3);
	$query3->execute();
	$rows3 = $query3->fetchAll();
	if (count($rows3) != 0) {
		foreach ($rows3 as $row) {
			$id = $row['BatchId'];
			$reportName = $row['ReportName'];
			insertReportData($id, $reportName);
		}
	}

	// Weekly if both StartDate and EndDate are available
	$statement4 = "SELECT * FROM Batch_Report WHERE DATE(NOW()) BETWEEN StartDate AND EndDate AND recurrence_pattern = 'Weekly' AND IsDelete = 0 AND schedule_status = 0 AND status = 0" . $day;
	$query4 = $pdo->prepare($statement4);
	$query4->execute();
	$rows4 = $query4->fetchAll();
	if (count($rows4) != 0) {
		foreach ($rows4 as $row) {
			$id = $row['BatchId'];
			$reportName = $row['ReportName'];
			insertReportData($id, $reportName);
		}
	}
	$current_month = fetchMonths($current_date);

	// Monthly if EndDate is null
	$statement5 = "SELECT * FROM Batch_Report WHERE EndDate = '' AND recurrence_pattern = 'Monthly' AND IsDelete = 0 AND status = 0 AND schedule_status = 0";
	$query5 = $pdo->prepare($statement5);
	$query5->execute();
	$rows5 = $query5->fetchAll();

	if (count($rows5) != 0) {
		foreach ($rows5 as $row) {
			$id = $row['BatchId'];
			$reportName = $row['ReportName'];
			$total_days = $row['monthly_days'];
			fetchDayNew($total_days, $id, $reportName);
		}
	}

	// Monthly if both StartDate and EndDate are available
	$statement6 = "SELECT * FROM Batch_Report WHERE DATE(NOW()) BETWEEN StartDate AND EndDate AND recurrence_pattern = 'Monthly' AND IsDelete = 0 AND schedule_status = 0 AND status = 0 ";
	$query6 = $pdo->prepare($statement6);
	$query6->execute();
	$rows6 = $query6->fetchAll();
	if (count($rows6) != 0) {
		foreach ($rows6 as $row) {
			$id = $row['BatchId'];
			$reportName = $row['ReportName'];
			$total_days = $row['monthly_days'];
			fetchDayNew($total_days, $id, $reportName);
		}
	}
	$current_month = fetchMonths($current_date);

	//Quarterly if EndDate is null
	$statement7 = "SELECT * FROM Batch_Report WHERE EndDate = '' AND recurrence_pattern = 'Quarterly' AND IsDelete = 0 AND status = 0 AND schedule_status = 0 " . $current_month;
	$query7 = $pdo->prepare($statement7);
	$query7->execute();
	$rows7 = $query7->fetchAll();

	if (count($rows7) != 0) {
		foreach ($rows7 as $row) {
			$id = $row['BatchId'];
			$reportName = $row['ReportName'];
			$total_days = $row['quarterly_days'];
			fetchDayNew($total_days, $id, $reportName);
		}
	}

	//****************Quarterly if both StartDate and EndDate are available**********************//
	$statement8 = "SELECT * FROM Batch_Report WHERE DATE(NOW()) BETWEEN StartDate AND EndDate AND recurrence_pattern = 'Quarterly' AND IsDelete = 0 AND schedule_status = 0 AND status = 0 " . $current_month;

	$query8 = $pdo->prepare($statement8);
	$query8->execute();
	$rows8 = $query8->fetchAll();
	if (count($rows8) != 0) {
		foreach ($rows8 as $row) {
			$id = $row['BatchId'];
			$reportName = $row['ReportName'];
			$total_days = $row['quarterly_days'];

			fetchDayNew($total_days, $id, $reportName);
		}
	}
	//Semi Monthly if EndDate is null
	$statement9 = "SELECT * FROM Batch_Report WHERE EndDate = '' AND recurrence_pattern = 'semi_monthly' AND IsDelete = 0 AND status = 0 AND schedule_status = 0 " . $current_month;

	$query9 = $pdo->prepare($statement9);
	$query9->execute();
	$rows9 = $query9->fetchAll();

	if (count($rows9) != 0) {
		foreach ($rows9 as $row) {
			$id = $row['BatchId'];
			$reportName = $row['ReportName'];
			$total_days = $row['semi_month_days'];
			fetchDayNew($total_days, $id, $reportName);
		}
	}
	//Semi Monthly if both StartDate and EndDate are available
	$statement10 = "SELECT * FROM Batch_Report WHERE DATE(NOW()) BETWEEN StartDate AND EndDate AND recurrence_pattern = 'semi_monthly' AND IsDelete = 0 AND schedule_status = 0 AND status = 0 " . $current_month;

	$query10 = $pdo->prepare($statement10);
	$query10->execute();
	$rows10 = $query10->fetchAll();

	if (count($rows10) != 0) {
		foreach ($rows10 as $row) {
			$id = $row['BatchId'];
			$reportName = $row['ReportName'];
			$total_days = $row['semi_month_days'];
			fetchDayNew($total_days, $id, $reportName);
		}
	}
	//Annually if EndDate is null
	$statement11 = "SELECT * FROM Batch_Report WHERE EndDate = '' AND recurrence_pattern = 'Annually' AND IsDelete = 0 AND schedule_status = 0 AND status = 0 " . $current_month;

	$query11 = $pdo->prepare($statement11);
	$query11->execute();
	$rows11 = $query11->fetchAll();

	if (count($rows11) != 0) {
		foreach ($rows11 as $row) {
			$id = $row['BatchId'];
			$reportName = $row['ReportName'];
			$total_days = $row['annually_days'];
			fetchDayNew($total_days, $id, $reportName);
		}
	}
	//Annually if both StartDate and EndDate are available
	$statement12 = "SELECT * FROM Batch_Report WHERE DATE(NOW()) BETWEEN StartDate AND EndDate AND recurrence_pattern = 'Annually' AND IsDelete = 0 AND schedule_status = 0 AND status = 0 " . $current_month;

	$query12 = $pdo->prepare($statement12);
	$query12->execute();
	$rows12 = $query12->fetchAll();

	if (count($rows12) != 0) {
		foreach ($rows12 as $row) {
			$id = $row['BatchId'];
			$reportName = $row['ReportName'];
			$total_days = $row['annually_days'];
			fetchDayNew($total_days, $id, $reportName);
		}
	}
}
function insertReportData($id, $report_name)
{

	global $pdo;
	$current_date = date("Y-m-d");
	$report_end_time = date("H:i:s");
	$statement = "SELECT * FROM SCHEDULER_DAILY_REPORT_LOGS WHERE batchId = '" . $id . "' AND createdAt = '" . $current_date . "' ";

	$query = $pdo->prepare($statement);
	$query->execute();
	$rows = $query->fetchColumn();
	if ($rows == 0) {
		$insert_logs = "INSERT INTO `SCHEDULER_DAILY_REPORT_LOGS` (reportName,batchId,createdAt) VALUES ('" . $report_name . "','" . $id . "','" . $current_date . "') ";

		$log_query = $pdo->prepare($insert_logs);
		$log_query->execute();
		$log_query->closeCursor();
	}
}
function fetchDayNew($total_days, $id, $reportName)
{
	$day = date('d');
	$exp = explode(",", $total_days);

	// Last day(int) of the current month
	$lastday = date('t', strtotime(date('Y-m-d')));
	if ($day == $lastday) {
		if (in_array("32", $exp)) {
			insertReportData($id, $reportName);
		} else {
			if (in_array($day, $exp)) {
				insertReportData($id, $reportName);
			}
		}
	} else {
		if (in_array($day, $exp)) {
			insertReportData($id, $reportName);
		}
	}
}

function fetchDay($total_days, $id, $path, $reportName, $code_name, $userType, $userReportName, $outputName, $reportDescription, $mailNotification, $sftpId, $mode, $run_by, $reportBasePath)
{

	$day = date('d');
	$exp = explode(",", $total_days);

	// Last day(int) of the current month
	$lastday = date('t', strtotime(date('Y-m-d')));
	if ($day == $lastday) {
		if (in_array("32", $exp)) {
			openInventoryReport($path, $id, $reportName, $code_name, $userType, $userReportName, $outputName, $reportDescription, $mailNotification, $sftpId, $mode, $run_by, $reportBasePath);
		} else {
			if (in_array($day, $exp)) {
				openInventoryReport($path, $id, $reportName, $code_name, $userType, $userReportName, $outputName, $reportDescription, $mailNotification, $sftpId, $mode, $run_by, $reportBasePath);
			}
		}
	} else {
		if (in_array($day, $exp)) {
			openInventoryReport($path, $id, $reportName, $code_name, $userType, $userReportName, $outputName, $reportDescription, $mailNotification, $sftpId, $mode, $run_by, $reportBasePath);
		}
	}
}

function fetchMonths($current_date)
{
	$month = date('M', strtotime($current_date));

	if ($month == 'Jan') {
		$month = " AND FIND_IN_SET('Jan',months)";
	} else if ($month == 'Feb') {
		$month = " AND FIND_IN_SET('Feb',months)";
	} else if ($month == 'Mar') {
		$month = " AND FIND_IN_SET('Mar',months)";
	} else if ($month == 'Apr') {
		$month = " AND FIND_IN_SET('Apr',months)";
	} else if ($month == 'May') {
		$month = " AND FIND_IN_SET('May',months)";
	} else if ($month == 'Jun') {
		$month = " AND FIND_IN_SET('Jun',months)";
	} else if ($month == 'Jul') {
		$month = " AND FIND_IN_SET('Jul',months)";
	} else if ($month == 'Aug') {
		$month = " AND FIND_IN_SET('Aug',months)";
	} else if ($month == 'Sep') {
		$month = " AND FIND_IN_SET('Sep',months)";
	} else if ($month == 'Oct') {
		$month = " AND FIND_IN_SET('Oct',months)";
	} else if ($month == 'Nov') {
		$month = " AND FIND_IN_SET('Nov',months)";
	} else if ($month == 'Dec') {
		$month = " AND FIND_IN_SET('Dec',months)";
	}

	return $month;
}

function fetchBusinessDay($total_days, $id, $path, $reportName, $custom_start_date, $code_name, $userType, $userReportName, $outputName, $reportDescription, $mailNotification, $sftpId, $mode, $run_by, $reportBasePath)
{
	try {


		$result = new stdClass();
		global $pdo;
		$days = explode(",", $total_days);

		foreach ($days as $row) {
			if ($row == 32) {
				$holidays_list = "SELECT * FROM HOLIDAYS_LIST";
				$query = $pdo->prepare($holidays_list);
				$query->execute();

				$rows = $query->fetchAll();
				$query->closeCursor();

				$lastdateofthemonth = date("Y-m-t");

				$lastworkingday = date('l', strtotime($lastdateofthemonth));

				if ($lastworkingday == "Saturday") {
					$newdate = strtotime('-1 day', strtotime($lastdateofthemonth));
					$lastworkingday = date('Y-m-j', $newdate);

					foreach ($rows as $row) {
						if ($lastworkingday == $row['DATE']) {
							$newdate = strtotime('-1 day', strtotime($lastworkingday));
							$lastworkingday = date('Y-m-j', $newdate);
						}
					}
				} elseif ($lastworkingday == "Sunday") {
					$newdate = strtotime('-2 day', strtotime($lastdateofthemonth));
					$lastworkingday = date('Y-m-j', $newdate);

					foreach ($rows as $row) {
						if ($lastworkingday == $row['DATE']) {
							$newdate = strtotime('-1 day', strtotime($lastworkingday));
							$lastworkingday = date('Y-m-j', $newdate);
						}
					}
				} else {
					foreach ($rows as $row) {
						if ($lastdateofthemonth == $row['DATE']) {
							$newdate = strtotime('-1 day', strtotime($lastdateofthemonth));
							$lastworkingday = date('Y-m-j', $newdate);
						}
					}

					$lastworkingday2 = date('l', strtotime($lastworkingday));
					if ($lastworkingday2 == "Saturday") {
						$newdate = strtotime('-1 day', strtotime($lastworkingday));
						$lastworkingday = date('Y-m-j', $newdate);
					} elseif ($lastworkingday2 == "Sunday") {
						$newdate = strtotime('-2 day', strtotime($lastworkingday));
						$lastworkingday = date('Y-m-j', $newdate);
					}
				}

				if (date('Y-m-d') == $lastworkingday) {

					openInventoryReport($path, $id, $reportName, $code_name, $userType, $userReportName, $outputName, $reportDescription, $mailNotification, $sftpId, $mode, $run_by, $reportBasePath);
				}
			} else {
				$statement = "SELECT BUSINESSDATES('" . $custom_start_date . "',$row) as business_date";
				$query = $pdo->prepare($statement);
				$query->execute();

				$rows = $query->fetchAll();
				$query->closeCursor();

				if ($rows[0]['business_date'] == date('Y-m-d')) {

					openInventoryReport($path, $id, $reportName, $code_name, $userType, $userReportName, $outputName, $reportDescription, $mailNotification, $sftpId, $mode, $run_by, $reportBasePath);
				}
			}
		}
	} catch (Exception $e) {
		echo 'Caught exception: ', $e->getMessage(), "\n";
		echo 'Line no.', $e->getLine();
	}
}

function new_SMTP($userReportName, $outputName, $reportDescription, $folderName, $email, $mailBody)
{
	global $pdo;
	global $mail;
	$mail->clearAddresses();
	$mail->addAddress($email);
	$mail->isHTML(true);
	$mail->Subject = $userReportName;

	if ($folderName == 'MY DOWNLOADS') {
		$mail->Body = "<div style='margin-bottom:10px'>A new report called " . $outputName . " has been placed in your " . $folderName . " folder. The report " . $reportDescription . ". Please proceed to Pipeway to retrieve this report.<br> Thank you.</div>";
	} else {
		if ($mailBody) {
			$mail->Body = $mailBody;
		} else {
			$mail->Body = "<div style='margin-bottom:10px'>A new report called " . $outputName . " has been placed in your my downloads under " . $folderName . " folder. The report " . $reportDescription . ". Please proceed to Pipeway to retrieve this report.<br> Thank you.</div>";
		}
	}

	if (!$mail->send()) {

		echo "Email not sent. ", $mail->ErrorInfo, PHP_EOL;
	} else {

		echo "Email sent successfully";
	}
	return $result;
}

function sftpCredentials($sftpId, $paths, $filename, $code, $status)
{
	global $pdo;

	$statement = "SELECT * FROM SCHEDULER_SFTP WHERE id IN ($sftpId)";
	$query = $pdo->prepare($statement);
	$query->execute();
	$rows = $query->fetchAll();
	if (count($rows) != 0) {
		foreach ($rows as $row) {
			if ($row['code'] == $code) {
				$conn = ssh2_connect($row['host_name'], 22);
				$sftp = ssh2_auth_password($conn, $row['username'], base64_decode($row['password']));

				if (!$sftp) {

					$msg = "Login failed for " . $row['host_name'] . " server, please check the credentials";
					$status = 0;
					$status_msg = "login failed";
					copy('/var/www/html/bi/dist/' . $paths . '/' . $filename, '/var/www/html/bi/dist/Mako/downloadfile/AACA/RF/' . $filename);
				} else {
					if (ssh2_scp_send($conn, "/var/www/html/bi/dist/" . $paths . "/" . $filename, $row['path'] . $filename, 0644)) {
						$msg = "Report generated successfully for " . $row['host_name'] . " server";
						$status = 1;
						$status_msg = "generated";
					} else {

						$msg = "Uploading error for " . $row['host_name'] . " server";
						$status = 0;
						$status_msg = "uploading error";

						copy('/var/www/html/bi/dist/' . $paths . '/' . $filename, '/var/www/html/bi/dist/Mako/downloadfile/AACA/RF/' . $filename);
					}
				}
				$result = array('msg' => $msg, 'status' => $status, 'status_msg' => $status_msg);
			} else {
				$msg = "Report generated successfully";
				$status = 1;
				$status_msg = "generated";
				$result = array('msg' => $msg, 'status' => $status, 'status_msg' => $status_msg);
			}
		}
	}
	return $result;
}

function Send_email($subject, $body, $email)
{

	global $donotreply;
	$donotreply->clearAddresses();
	$donotreply->addAddress($email);
	$donotreply->isHTML(true);
	$donotreply->Subject = $subject;
	$donotreply->Body = $body;

	if (!$donotreply->send()) {
		echo "Email not sent. ", $donotreply->ErrorInfo, PHP_EOL;
	}
}

function scheduler_logs($status, $reportName, $report_start_time, $msg, $new_row, $sftpStatus, $FileSizeKB, $filePath, $run_by)
{

	global $pdo;
	$current_date = date("Y-m-d");
	$report_end_time = date("H:i:s");
	$insert_logs = "INSERT INTO SCHEDULER_LOGS(report_name,start_time,end_time,status,message,code,file_path,sftp,createdAt,file_size,run_by)VALUES ('" . $reportName . "','" . $report_start_time . "','" . $report_end_time . "','" . $status . "','" . $msg . "','" . $new_row . "','" . $filePath . " ','" . $sftpStatus . "','" . $current_date . "','" . $FileSizeKB . "','" . $run_by . "') ";

	$log_query = $pdo->prepare($insert_logs);
	$log_query->execute();

	if ($status == 0) {
		if ($new_row == 'ALL') {
			$new_row = 'AACA';
		}
		$message = '<div>Hi,</div><br>';
		$message .= 'The report "' . $reportName . '" has been failed for ' . $new_row . ' recepients.<br>';
		$message .= 'Error message : "' . $msg . '" <br>';
		$message .= 'For further information please go through <b>/home/PipewayWeb/log/scheduler_cron.txt</b> <br>';
		$message .= "Thank you.";

		$subject = 'Scheduler Report Failure';
		$contacts = array("pipeway.support@goolean.tech");
		foreach ($contacts as $contact) {
			Send_email($subject, $message, $contact);
		}
	}
}




function getResult($query)
{
	global $pdo;
	$queryResult = $pdo->prepare($query);
	$queryResult->execute();
	$rows = $queryResult->rowCount();
	$queryResult = $queryResult->fetchAll(PDO::FETCH_ASSOC);
	return ['numRows' => $rows, 'results' => $queryResult];
}

function filterReportName($date, $reportName)
{

	$current_date = date("Y-m-d H:i:s", strtotime("$date"));
	$reportName = str_replace("/", " ", $reportName);
	$reportName = trim(str_replace('-', '_', $reportName));
	$reportName = trim(str_replace(' ', '_', $reportName));

	$filename = $reportName . "(" . $current_date . ").xlsx";
	return $filename;
}
function filterReportNameForTextExtension($date, $reportName)
{

	$current_date = $date->format("m-d-Y H:i:s.v");
	$reportName = str_replace("/", " ", $reportName);
	$reportName = trim(str_replace('-', '_', $reportName));
	$reportName = trim(str_replace(' ', '_', $reportName));

	$filename = $reportName . "(" . $current_date . ").txt";
	return $filename;
}


function createDirgetCompanyName(string $fileDir, $reportBasePath)
{
	if (!is_dir($reportBasePath . $fileDir)) {
		mkdir($reportBasePath . $fileDir, 0777, true);
		chmod($reportBasePath . $fileDir, 0777);
	}

	$parts = explode('downloadfile/', $fileDir);
	if (isset($parts[1])) {
		$getName = explode('/', $parts[1]);
		if (isset($getName[0])) {
			return $getName[0];
		}
	}
}
function checkCompanynameinResultArray($array, $firmName)
{
	foreach ($array as $item) {
		if (isset($item['FIRM']) && $item['FIRM'] === $firmName) {
			return true;
		}
	}
	return false;
}
function returnPath($array, $str)
{
	foreach ($array as $item) {
		if (strpos($item, $str) !== false) {
			$foundValue = $item;
			break; // Stop once we find the value
		}
	}
	if ($foundValue !== null) {
		return str_replace(["'", " "], "", $foundValue);
	}
	return false;
}
function ifDataNotPresent($companyStatus, $companyName, $reportName, $report_start_time, $sftpStatus, $FileSizeKB, $path, $run_by, $userType)
{
	ReportStatus($companyStatus, $companyName, $reportName, $userType);
	$msg = 'No Data Present';
	$sftpStatus = 0;
	$status = 0;
	scheduler_logs($status, $reportName, $report_start_time, $msg, $companyName, $sftpStatus, $FileSizeKB, $path, $run_by);
}

function ifDataPresent($companyStatus, $companyName, $reportName, $report_start_time, $sftpStatus, $FileSizeKB, $path, $run_by, $userType)
{
	$msg = 'Report generated successfully';
	$sftpStatus = 0;
	$status = 1;
	scheduler_logs($status, $reportName, $report_start_time, $msg, $companyName, $sftpStatus, $FileSizeKB, $path, $run_by);
}
function getCompanyStatus($companyName)
{
	global $pdo;
	$checkCompanyStatus = $pdo->prepare("SELECT * FROM `CC_REGSTR` WHERE Isdeleted = 0 AND CCODE = '" . $companyName . "'");
	$checkCompanyStatus->execute();
	$data = $checkCompanyStatus->fetchAll();
	$cmpstatus = $data[0]['CSTATUS'];
	return $cmpstatus;
}
function ReportStatus($companyStatus, $companyName, $reportName, $userType)
{

	global $mail;
	if (isset($companyName)) {
		$cmpStatus = '';
		if ($companyStatus == '1') {
			$cmpStatus = 'Acitve';
		}
		if ($companyStatus == '2') {
			$cmpStatus = 'Panding';
		}
		if ($companyStatus == '3') {
			$cmpStatus = 'Inactive';
		}
		if ($companyStatus == '4') {
			$cmpStatus = 'Terminate';
		}
		$mailer = "pipeway.support@goolean.tech";
		$mail->clearAddresses();
		$mail->addAddress($mailer);
		$mail->Subject = $reportName;
		if ($userType == '1') {
			$mail->Body = "<div style='margin-bottom:10px'>The " . $companyName . " has  no data. <br>Thank you.</div>";
		}
		if ($userType == '2') {
			$mail->Body = "<div style='margin-bottom:10px'>The Firm " . $companyName . " is currently in " . $cmpStatus . " status, but there is no data. <br>Thank you.</div>";
		}
		if ($userType == '3') {
			$mail->Body = "<div style='margin-bottom:10px'>The Client " . $companyName . " is currently in " . $cmpStatus . " status, but there is no data. <br>Thank you.</div>";
		}
		if ($userType == '4') {
			$mail->Body = "<div style='margin-bottom:10px'>The Agency " . $companyName . " is currently in " . $cmpStatus . " status, but there is no data. <br>Thank you.</div>";
		}
		$mail->send();
	}
}
function hitquerywithnoResult($query)
{
	global $pdo;
	$queryResult = $pdo->prepare($query);
	if ($queryResult->execute()) {
		return true;
	} else {
		return false;
	}
}
function mailNotifaction($mailNotification, $reportpath, $companyName, $userType, $userReportName, $reportDescription)
{

	if ($mailNotification == 1) {
		$split = explode("/", $reportpath);
		$fileName = $split[count($split) - 1];
		$folderName = getFolderName(trim($fileName));
		$emailId = getEmailId($companyName, $userType, $folderName, $reportpath);

		foreach ($emailId as $email) {
			$outputName = $fileName;
			// new_SMTP($userReportName, $outputName, $reportDescription, $folderName, $email);
		}
	}
}
function getDisFolder(string $fileDir, $reportBasePath)
{
	if (!is_dir($reportBasePath . $fileDir)) {

		mkdir($reportBasePath . $fileDir, 0777, true);
		chmod($reportBasePath . $fileDir, 0777);
	}

	$parts = explode('downloadfile/', $fileDir);
	if (isset($parts[1])) {
		$getName = explode('/', $parts[1]);
		if (isset($getName[1])) {
			return $getName[1];
		}
	}
}
function getfileSize(string $filepath)
{
	$fileSize = filesize($filepath);
	$FileSizeKB = round($fileSize / 1024) . "KB";
	return $FileSizeKB;
}


function CreateZipFile($dirPath, $basePath)
{
	date_default_timezone_set('America/New_York');
	$currentDate = date('m-d-Y');
	$directory = $basePath . $dirPath;
	if (!is_dir($directory)) {
		echo "Directory does not exist: " . $directory;
		return;
	}

	chdir($directory);

	$files = scandir($directory);

	$matchedFiles = [];

	foreach ($files as $file) {
		if ($file == '.' || $file == '..') {
			continue;
		}

		if (is_file($directory . DIRECTORY_SEPARATOR . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'txt') {

			if (strpos($file, $currentDate) !== false) {

				$matchedFiles[] = $file;
			}
		}
	}
	if (count($matchedFiles) > 0) {

		$zip = new ZipArchive();
		$zipFile = trim($basePath . $dirPath . DIRECTORY_SEPARATOR . 'UnifundMonthlyUpdate' . $currentDate . '.zip');


		if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {

			foreach ($matchedFiles as $file) {

				$zip->addFile($directory . '/' . $file, $file);
			}
			$zip->close();

			echo "Zip file created successfully: " . $zipFile;
		} else {
			echo "Failed to create zip file.";
		}
	} else {
		echo "No matching .txt files found for the current date.";
	}
	foreach ($files as $file) {
		if ($file == '.' || $file == '..') {
			continue;
		}
		if (is_file($directory . '/' . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'txt') {
			if (strpos($file, $currentDate) !== false) {
				unlink($file);
			}
		}
	}
}


function dailyReportStatus()
{
	date_default_timezone_set('America/New_York');
	$currentDate = date('m-d-Y');
	global $mail;
	global $pdo;
	$query = "SELECT report_name,start_time,end_time,code,status,message,run_by from SCHEDULER_LOGS  where createdAt='2025-01-06' order by createdAt desc";
	$queryResult = $pdo->prepare($query);
	$queryResult->execute();
	$totalReports = $queryResult->fetchAll(PDO::FETCH_ASSOC);

	$mailer = "pipeway.support@goolean.tech";
	$mail->clearAddresses();
	$mail->addAddress($mailer);
	$mail->Subject = 'Daily Report Status  : ' . $currentDate;

	$message = "
	<table class='table'>
    	<thead>
        	<tr>
            	<th scope='col'>#</th>
            	<th scope='col'>Report Name</th>
            	<th scope='col'>Start Time</th>
            	<th scope='col'>End Time</th>
				<th scope='col'>Code</th>
				<th scope='col'>Status</th>
				<th scope='col'>Run By</th>
        	</tr>
    	</thead>
    	<tbody>";
	foreach ($totalReports as $key => $report) {

		$message .= "<tr>
				<td>" . ($key + 1) . "</td>
				<td>" . $report['report_name'] . "</td>
				<td>" . $report['start_time'] . "</td>
				<td>" . $report['end_time'] . "</td>
				<td>" . $report['code'] . "</td>
				<td>" . $report['status'] . "</td>
				<td>" . $report['run_by'] . "</td>
			</tr>";
	}

	$message .= "
    	</tbody>

	</table>";
	$mail->Body = $message;
	$mail->send();
}




//*************************************************Common Functions end*****************************************//
