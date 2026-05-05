<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include('../mydownloadconn.php');
include('../pdoconn.php');
function ClosingReportfcosifpif_1($path, $id, $reportName, $code_name, $userType, $userReportName, $outputName, $reportDescription, $mailNotification, $sftpId, $mode, $run_by)
{
$Active=false; $Pending=false; $Inactive=false; $Terminated=false;
    $Active_data=array(); $Pending_data=array(); $Inactive_data=array(); $Terminated_data=array(); 
  $report_start_time = date("H:i:s");
    global $pdo;
    global $sftp;
    global $connection;
    $dataPresents=array();
    $noDataPresents=array();
    if($userType==3){
        $report_start_time = date("H:i:s");
        $paths=explode(',', $path);
        $client_codes=explode(',', $code_name);
        $j=0;
        $checkDataPresent=false;
        foreach ($client_codes as $key => $client_code) {
            if (!is_dir('/var/www/html/bi/dist/'.$paths[$j])) {
                   mkdir('/var/www/html/bi/dist/'.$paths[$j], 0777, true);
                   chmod('/var/www/html/bi/dist/'.$paths[$j], 0777);
            }
            $new_row=$client_code;
            //filterReportName
            $filename=filterReportName_ClosingReportfcosifpif_1($reportName);
            //get company status as per client code
            $cmpstatus = getCompanyStatus_ClosingReportfcosifpif_1($client_code);
            //get query result
            $queryResult=getqueryResult_ClosingReportfcosifpif_1($client_code);
            $results=$queryResult['results'];

            if($queryResult['numRows']>0)
            {

                header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
                header('Content-Type: application/openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Transfer-Encoding: binary');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                //return sheet layout
                $return_header_style_Sheetname=return_header_style_Sheetname_ClosingReportfcosifpif_1();
                if($return_header_style_Sheetname)
                {
                    
                    $writer = new XLSXWriter();
                    $sheetName=$return_header_style_Sheetname['sheetName'];
                    $header=$return_header_style_Sheetname['header'];
                    $style=$return_header_style_Sheetname['style'];
                    $writer->writeSheetHeader($sheetName,$header,$style);

                    $checkduplicate=array();

                     foreach($results as $result)
                    {   
                        $writer->writeSheetRow($sheetName, $result );
                    }
                
                  //create files ,Sheet and write into file
                    $writer->writeToFile(str_replace(__FILE__,'/var/www/html/bi/dist/'.$paths[$j].'/'.$filename.'',__FILE__));
                  
                        //push array into  $DataPresent if file exist
                        array_push($dataPresents,array(
                            'paths'=>$paths[$j],
                            'filename'=>$filename,
                            'clientcode'=>$client_code
                        ));
                    }
                    
             //if cstatus 1 and $row>0 start
          if($cmpstatus==1 || $cmpstatus==3){
             $paths = explode(",",$path);
            // print_r($paths);
            // die;

            if (!is_dir('/var/www/html/bi/dist/'.$paths[$j])) {
           mkdir('/var/www/html/bi/dist/'.$paths[$j], 0777, true);
           chmod('/var/www/html/bi/dist/'.$paths[$j], 0777);
           }
        $writer->writeToFile(str_replace(__FILE__,'/var/www/html/bi/dist/'.$paths[$j].'/'.$filename.'',__FILE__));
          if (!file_exists('/var/www/html/bi/dist/'.$paths[$j].'/'.$filename))  
           {
                $status = 0;
            }
            else
            {
                $status = 1;
            ssh2_sftp_mkdir($sftp, '../../var/www/html/'.$paths[$j],0777, true);
                ssh2_sftp_chmod($sftp, '../../var/www/html/'.$paths[$j], 0777);
                ssh2_scp_send($connection,'/var/www/html/bi/dist/'.$paths[$j].'/'.$filename, '../../var/www/html/'.$paths[$j].'/'.$filename, 0644);

                $file = '/var/www/html/bi/dist/'.$paths[$j].'/'.$filename; 
            $fileSize = filesize($file);
            $FileSizeKB = round($fileSize/1024,2). "KB";
            // echo $FileSizeKB;
            // die;

                $msg = 'Report generated successfully';
            $status_msg = 'generated';
                $sftpStatus = 0;
                $status = 1;
            }
          scheduler_logs($status,$reportName,$report_start_time,$msg,$new_row,$sftpStatus,$FileSizeKB,$run_by);
                } 
         
        //if cstatus 1 and $row>0 end 

        //if cstatus 2 and $row>0 start
          if($cmpstatus==2){
            // echo "Maa";exit();
             $writer->writeToFile(str_replace(__FILE__,'/var/www/html/bi/dist/'.$paths[$j].'/'.$filename.'',__FILE__));
                 if(!file_exists('/var/www/html/bi/dist/'.$paths[$j].'/'.$filename))
                 {
                 $status = 0;
                 }
                 else
                 {
                 $status = 1;
                ssh2_scp_send($connection,'/var/www/html/bi/dist/'.$paths[$j].'/'.$filename, '../../var/www/html/MAKO/downloadfile/AACA/CP'.'/'.$filename, 0644);

                $file = '/var/www/html/bi/dist/'.$paths[$j].'/'.$filename; 
        $fileSize = filesize($file);
        $FileSizeKB = round($fileSize/1024,2). "KB";
            // echo $FileSizeKB;
            // die;

                $msg = 'Report generated successfully';
            $status_msg = 'generated';
                $sftpStatus = 0;
              
                } 
          }
       
         if($cmpstatus==4){
            // echo "Maa";exit();
                $writer->writeToFile(str_replace(__FILE__,'/var/www/html/bi/dist/'.$paths[$j].'/'.$filename.'',__FILE__));
                    if(!file_exists('/var/www/html/bi/dist/'.$paths[$j].'/'.$filename))  
                        {
                        $status = 0;
                        }
                    else
                        {
                $status = 1;
                ssh2_scp_send($connection,'/var/www/html/bi/dist/'.$paths[$j].'/'.$filename, '../../var/www/html/MAKO/downloadfile/AACA/TRM'.'/'.$filename, 0644);
            }

            $file = '/var/www/html/bi/dist/'.$paths[$j].'/'.$filename; 
            $fileSize = filesize($file);
            $FileSizeKB = round($fileSize/1024,2). "KB";
            // echo $FileSizeKB;
            // die;
            
                  $msg = 'Report generated successfully';
              $status_msg = 'generated';
              $sftpStatus = 0;
              
                scheduler_logs($status,$reportName,$report_start_time,$msg,$new_row,$sftpStatus,$FileSizeKB,$run_by);
            }


            }else
            {
                array_push($noDataPresents, array(
                        'paths'=>$paths[$j],
                        'filename'=>$filename,
                        'clientcode'=>$client_code
                    ));
            //if datapresent  or not
            if($cmpstatus==1){
            $Active=true;
            $ss=array_push($Active_data, $client_code);
                $new_status = 3;
                $msg = 'No Data available';
                $status_msg = 'failed';
                $sftpStatus = 0;
                scheduler_logs($status,$reportName,$report_start_time,$msg,$new_row,$sftpStatus,$FileSizeKB,$run_by);

               }
                    if($cmpstatus==2){
            $Pending=true;
            array_push($Pending_data, $client_code);

           $new_status = 3;
                  $msg = 'No Data available';
                $status_msg = 'failed';
                $sftpStatus = 0;
                scheduler_logs($status,$reportName,$report_start_time,$msg,$new_row,$sftpStatus,$FileSizeKB,$run_by);

            }
        if($cmpstatus==3){
            $Inactive=true;
            array_push($Inactive_data, $client_code);
            
            $new_status = 3;
                  $msg = 'No Data available';
                $status_msg = 'failed';
                $sftpStatus = 0;
                scheduler_logs($status,$reportName,$report_start_time,$msg,$new_row,$sftpStatus,$FileSizeKB,$run_by);

            }
        if($cmpstatus==4){
                $Terminated=true;
                array_push($Terminated_data, $client_code);

            $new_status = 3;
                  $msg = 'No Data available';
                $status_msg = 'failed';
                $sftpStatus = 0;
                scheduler_logs($status,$reportName,$report_start_time,$msg,$new_row,$sftpStatus,$FileSizeKB,$run_by);

              }
          }
            $j++;
        }//client codes end


if($Active==True){
            
        $at=implode(',', $Active_data);
       
            include('PHPMailer/mailcode.php');
                   $mail->addAddress("AACAMIS@aacanet.org");
                   $mail->Subject= $reportName;

                   $mail->Body= "<div style='margin-bottom:10px'>The firm ".$at." is currently in active status, but there is no data. <br>Thank you.</div>";
                        
                        if(!$mail->send()) {
                        // $result->status = 0;
                        echo "Email not sent." , $mail->ErrorInfo , PHP_EOL;
                    } else {
                        // $result->status = 1;
                        // echo "Email sent successfully";
                    }
               }

       if($Pending==True){
        //print_r($Pending_data);
        $pd=implode(',', $Pending_data);
        
             include('PHPMailer/mailcode.php');
                  $mail->addAddress("AACAMIS@aacanet.org");
                   $mail->Subject= $reportName;
                   $mail->Body= "<div style='margin-bottom:10px'>The firm ".$pd." is currently in pending status, but there is no data. <br>Thank you.</div>";
                  
                    if(!$mail->send()) {
                        // $result->status = 0;
                        echo "Email not sent." , $mail->ErrorInfo , PHP_EOL;
                    } else {
                        // $result->status = 1;
                        // echo "Email sent successfully";
                    }

       }
       if($Inactive==True){
        //print_r($Pending_data);
        $ind=implode(',', $Inactive_data);
        
                include('PHPMailer/mailcode.php');
                   $mail->addAddress("AACAMIS@aacanet.org");
                   $mail->Subject= $reportName;
                   $mail->Body= "<div style='margin-bottom:10px'>The firm ".$ind." is currently in inactive status, but there is no data. <br>Thank you.</div>";
                  
                    if(!$mail->send()) {
                        // $result->status = 0;
                        echo "Email not sent." , $mail->ErrorInfo , PHP_EOL;
                    } else {
                        // $result->status = 1;
                        // echo "Email sent successfully";
                    }

            }
                   if($Terminated==True){

                   $tr=implode(',', $Terminated_data);
        
             include('PHPMailer/mailcode.php');
                   $mail->addAddress("AACAMIS@aacanet.org");
                   $mail->Subject= $reportName;
                   $mail->Body= "<div style='margin-bottom:10px'>The firm ".$tr." is currently in terminated status, but there is no data. <br>Thank you.</div>";
                   
                    if(!$mail->send()) {
                        // $result->status = 0;
                        echo "Email not sent." , $mail->ErrorInfo , PHP_EOL;
                    } else {
                        // $result->status = 1;
                        // echo "Email sent successfully";
                    }
                }


            
      if(!empty($dataPresents))
{
$response=sendsuccessMsg_ClosingReportfcosifpif_1($dataPresents,$userType,$report_start_time,$reportName,$run_by,$connection,$mailNotification,$cmpstatus);
    if($dataPresents)
    {
           $status_msg = 'generated';
            $status=1;
            $new_status=2;

    }
}
    if(!empty($noDataPresents)){
        $mailresponse=sendmaildataNotPresent_ClosingReportfcosifpif_1($noDataPresents,$reportName,$report_start_time,$run_by);

}
        if(empty($dataPresents)){
            $status_msg = 'failed';
            $status=0;
            $new_status=3;
        }

            return array('status' => $status, 'status_msg' => $status_msg,'new_status'=>$new_status);
    }

}

function filterReportName_ClosingReportfcosifpif_1($reportName)
{
    $d = new DateTime();
    $current_date = $d->format("m-d-Y H:i:s.v"); 
    $c=trim($current_date);
    $reportName=str_replace("/"," ",$reportName);
    $reportName = trim(str_replace('-', '_', $reportName));
    $reportName = trim(str_replace(' ', '_', $reportName));

    $filename = $reportName."(".$current_date.").xlsx";
    return $filename;
}
function getCompanyStatus_ClosingReportfcosifpif_1($new_row)
{ 
    global $pdo;
    $checkCompanyStatus=$pdo->prepare("SELECT * FROM `CC_REGSTR` WHERE Isdeleted = 0 AND CCODE = '".$new_row."'");
        $checkCompanyStatus->execute();
        $data = $checkCompanyStatus->fetchAll();
        $cmpstatus = $data[0]['CSTATUS'];
        return $cmpstatus;
}
function getqueryResult_ClosingReportfcosifpif_1($recipient)
{
    global $pdo;
    $query="SELECT ACCT_NUM AS 'Acct Number', WFNAME AS Name, CURR_STS_CD AS 'Closing Code',

CURR_STS_DESC AS Description, DATE_FORMAT(LSTSTATCHG ,'%Y/%m/%d') AS 'Closing Date',

CURR_ATTY_NME AS Firm, CLT_CDE AS 'Client Code',

DATE_FORMAT(CURRENT_DATE(), '%Y%m%d') AS 'Process Date', ORGCODE AS 'Org Code',

WFORGNM AS 'Org Name'

FROM HSFLCLNTWF

WHERE CAST(LSTSTATCHG AS DATE) BETWEEN DATE_FORMAT(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH) ,'%Y-%m-01') AND LAST_DAY(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))

AND ORGCODE = '".$recipient."'

AND CURR_STS_CD IN ('994','995','99J')";

 $queryResult=$pdo->prepare($query);

   $queryResult->execute();
$rows = $queryResult->rowCount();
$queryResult=$queryResult->fetchAll(PDO::FETCH_ASSOC);

return ['numRows'=>$rows,'results'=>$queryResult];
}

function return_header_style_Sheetname_ClosingReportfcosifpif_1()
{


    $header = array(
                    'Acct Number'=>'string',
                    'Name'=>'string',
                    'Closing Code'=>'string',
                    'Description'=>'string',
                    'ClosingDate'=>'string',
                    'Firm'=>'string',
                    'ClientCode'=>'string',
                    'ProcessDate'=>'string',
                    'Org Code'=>'string',
                    'Org Name'=>'string'
                );
                $style = array(
                    'font-style' => 'bold' ,
                    'fill'=>'#eee', 
                    'halign'=>'center', 
                    'border'=>'left, right, top, bottom',
                    'widths'=>[15,20,15,20,15,40,15,15,20,30] 
                
                );
                $sheetName = 'ClosingReportClientsWeeklySelectClients';

                return ['header'=>$header,'style'=>$style,'sheetName'=>$sheetName];

}

function sendsuccessMsg_ClosingReportfcosifpif_1($dataPresents,$userType,$report_start_time,$reportName,$run_by,$connection,$mailNotification,$cmpstatus){

    if($dataPresents){
        $msg = 'Report generated successfully';
        $status=1;
        $sftpStatus=0;
        $recipients=array();
        foreach ($dataPresents as $value) {
            array_push($recipients,$value['clientcode']);
            
            $paths=$value['paths'];
            $filename=$value['filename'];
            $clientCode=$value['clientcode'];

            //if firm data is present call send generated msg
            if($cmpstatus==1 || $cmpstatus==3){
            	ssh2_scp_send($connection,'/var/www/html/bi/dist/'.$paths.'/'.$filename, '../../var/www/html/'.$paths.'/'.$filename, 0644);
            }if($cmpstatus==2 ){
            	ssh2_scp_send($connection,'/var/www/html/bi/dist/'.$paths.'/'.$filename, '../../var/www/html/MAKO/downloadfile/AACA/CP'.'/'.$filename, 0644);
            }if($cmpstatus==4){
            	ssh2_scp_send($connection,'/var/www/html/bi/dist/'.$paths.'/'.$filename, '../../var/www/html/MAKO/downloadfile/AACA/TRM'.'/'.$filename, 0644);
            }
           // ssh2_scp_send($connection,'/var/www/html/bi/dist/'.$paths.'/'.$filename, '../../var/www/html/MAKO/downloadfile/AACA/TRM'.'/'.$filename, 0644);
        
            $file = '/var/www/html/bi/dist/'.$paths.'/'.$filename; 
            $fileSize = filesize($file);
            $FileSizeKB = round($fileSize/1024,2). "KB";
            if($mailNotification == 1) {
                $split = explode("/", $paths);
                $fileName = $split[count($split)-1];
                $folderName = getFolderName(trim($fileName));
                $emailId = getEmailId($clientCode, $userType, $folderName, $paths);

                foreach($emailId as $email){
                    $outputName = $filename;
                    new_SMTP($userReportName, $outputName, $reportDescription, $folderName, $email);
                }
             }
        }
        $recipients=$value['clientcode'];//implode(",",$recipients);
        scheduler_logs($status,$reportName,$report_start_time,$msg,$recipients,$sftpStatus,$FileSizeKB,$run_by);
        return true;
    }
}
function sendmaildataNotPresent_ClosingReportfcosifpif_1($noDataPresents,$reportName,$report_start_time,$run_by)
{
    $msg = 'No Data available';
    $status_msg = 'failed';
    $sftpStatus = 0;
    $status=0;
     foreach ($noDataPresents as $key => $no_data) {
   
$FileSizeKB = '';
$recipients=$no_data['clientcode'];//implode(",",$recipients);
//echo $recipients;exit;
scheduler_logs($status,$reportName,$report_start_time,$msg,$recipients,$sftpStatus,$FileSizeKB,$run_by);
return true;
}
}
?>