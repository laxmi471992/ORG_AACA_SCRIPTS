<?php 
//include('../connectother.php');
session_start();
error_reporting(1);
// $name= base64_decode($_GET['nama']);
// $path= base64_decode($_GET['remote']);
//$string = rawurlencode(utf8_encode('//bay/Judmedia/LSC#0000454699-DOCS2.PDF'));
 // $string1 = str_replace("%23", "#", $string);
//$file ='ssh2.sftp://'.$sftp_fd.'//BAY/judmedia/JDG_LSC#0000454699_DE.PDF';

// $file=array();
// $dirHandle = opendir("ssh2.sftp://$sftp1//BAY/judmedia/test");
// while (false !== ($file = readdir($dirHandle))) {
//     if ($file != '.' && $file != '..') {
//         $firstarray[] = $file;
//     }
// }
// echo "<pre>";print_r($firstarray);


// $filename=urlencode('MemberListup.csv'); //echo $filename;
// $filepath='/../../var/www/html/MAKO/test/'.$filename;
// $files ='ssh2.sftp://'.$sftp_fd.$filepath;

// //ssh2_scp_recv($connection,'../../var/www/html/MAKO/test/'.$filename, 'test/MemberListup.csv');
// $check = fopen($files, 'r');
//   // Check if the file exists
//   if(!$check){
//     echo 'File does not exist';
//   }else{
//     echo 'File exists';
//   }
//   exit;







// ssh2_scp_recv($connection,'//BAY/Lincoln_Img/LSC196665 HOBBS.PDF', '../MYUPLOADS/LSC196665 HOBBS.PDF');
// ssh2_scp_recv($connection,'//bay/Lincoln_Img/LSC196665 HOBBS.PDF', '../MYUPLOADS/LSC196665 HOBBS.PDF');exit;
// echo $files ;

//$check = fopen($files, 'r');
  // Check if the file exists
  // if(!$check){
  //   echo 'File does not exist';
  // }else{
  //   echo 'File exists';
  // }
  // exit;

//  if(file_exists($files)){
//    echo "file exist";
// // header("Cache-Control: public"); 
// // header("Content-Description: File Transfer");
// // header('Content-Type:application/pdf, charset:utf-8'); 
// // header("Content-Disposition: attachment; filename=".basename($filepath));
// // header("Content-Transfer-Encoding: binary");
// // // ob_end_clean();
// //  readfile($filepath);
// exit;
// }else{
//  echo "no file";
// }


$name       = $_GET['nama'];
$path       = $_GET['remote'];
//$path =  escapeshellarg(urldecode($_GET['remote']));
//$name      = escapeshellarg(substr($path, strrpos($path, '/') + 1));
//$name       =substr($path, strrpos($path, '/') + 1);
$getextension= strtolower(pathinfo($name, PATHINFO_EXTENSION));
$username   = substr($name, 0, strrpos($name, '.'));
//$username   = str_replace(' ', '', $username);//echo $name;exit;
$filename   = $username.'.csv';
$list       = array(
    [$path, $name,$filename]

   
); 


$fp          = fopen('/var/www/html/bi/dist/BAYREADFILE/'.$filename, 'wb');
//$fp   = fopen("ssh2.sftp://".$sftp_fd.'//BAY/Judmedia/myCsv.txt', 'wb');
if( $fp == false ){
  echo "error";exit;
}else{
  foreach ($list as $fields) {
    fputcsv($fp, $fields,'|');
    fclose($fp);

} 

if($getextension=='jpg' || $getextension=='jpeg'){
  $type='image/jpeg';
}else if($getextension=='tif' || $getextension=='tiff'){
  $type='image/tiff';
}else if($getextension=='gif'){
  $type='image/gif';
}else if($getextension=='bmp'){
  $type='image/bmp';
}else if($getextension=='png'){
  $type='image/png';
}else if($getextension=='doc' || $getextension=='docx'){
  $type='application/msword';
}else if($getextension=='doc'){
  $type='application/msword';
}else if($getextension=='txt'){
  $type='text/plain';
}else if($getextension=='xls'){
  $type='application/vnd.ms-excel';
}else if($getextension=='docx'){
  $type='application/vnd.openxmlformats-officedocument.wordprocessingml.document';
}else if($getextension=='xlsx'){
  $type='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
}else if($getextension=='xlsb'){
  $type='application/vnd.ms-excel.sheet.binary.macroEnabled.12';
}else if($getextension=='xml'){
  $type='text/xml';
}else if($getextension=='htm'){
  $type='text/html';
}else if($getextension=='html'){
  $type='text/html';
}else if($getextension=='csv'){
  $type='application/octet-stream';
}else{
  $type ="application/pdf";
}

$file_path = '/var/www/html/bi/dist/BAYREADFILE/' . $name;
//$cmd = exec("'/var/www/html/cron/document_loading.sh' $path $name ");
// $scriptPath = '/var/www/html/cron/document_loading.sh';
// $cmd = "bash $scriptPath";
// exec("$cmd >/var/www/html/cron/error.txt 2>&1", $output, $return_var);exit;
$cmd = '/var/www/html/cron/document_loading.sh';
    if(exec("$cmd  2>&1", $output, $return_var)) {
    if (file_exists($file_path)) {
    // Get the MIME type, falling back to 'application/octet-stream' if it's unknown
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file_path);
    finfo_close($finfo);

    // Sanitize the file name and ensure it is properly quoted for the Content-Disposition header
    $encoded_filename = rawurlencode($name);
    $safe_filename = basename($name);

    // Set headers for the file download
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: attachment; filename="' . $safe_filename . '"; filename*=UTF-8\'\'' . $encoded_filename);
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file_path));

    // Clear output buffer to prevent any unwanted characters from affecting the file
    ob_clean();
    flush();

    // Output the file to the browser
    readfile($file_path);
    unlink($file_path);
    exit;
} else {
    // Handle file not found
    echo 'File not found.';
    exit;
}
  //   $remoteURL='/var/www/html/bi/dist/BAYREADFILE/'.$name;//echo $remoteURL;exit;
  //   if (file_exists($remoteURL)) { 
  //    header("Content-type:".$type.""); 
  //    header("Content-Disposition: attachment; filename=".basename($remoteURL));
  //   // ob_end_clean();
  //    readfile($remoteURL);
  //   //unlink('/var/www/html/bi/dist/BAYREADFILE/'.$name);
  // }else{
  //   echo "No file exists";
  // }
  } else{
    echo "Something went wrong!";
  }
}



// rmdir('/var/www/html/bi/dist/BAYREADFILE/'.$username);
// fwrite($fp,$path);
// fclose($fp);
//echo '../'.$_SESSION['email'].'.txt'.'==================='.'../../var/www/html/'.$_SESSION['email'].'.txt';exit;
// ssh2_scp_send($connection,'/var/www/html/bi/dist/MYUPLOADS/'.$username, '/../../var/www/html/'.$username, 0644);
//ssh2_scp_send($connection,'/var/www/html/bi/dist/MYUPLOADS/'.$username, '//BAY/judmedia/test/'.$username, 0644);
//unlink('/var/www/html/bi/dist/MYUPLOADS/'.$username);
// }
 
//$path=iconv('utf-8', 'cp1252', $path);
// $folder="//bay/Judmedia/";
// $file  ="LSC#0000454699-DOCS2.PDF";
// $full=$folder.htmlspecialchars('"'.$file.'"');
// $remoteURL = "ssh2.sftp://".$sftp_fd.$full;
// header("Content-type: application/x-file-to-save"); 
// header("Content-Disposition: attachment; filename=".basename($path));
// ob_end_clean();
// readfile($remoteURL);
?>
