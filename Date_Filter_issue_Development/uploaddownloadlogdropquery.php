<?php
error_reporting(0);
require_once("config.php");
session_start();

if(!empty($_POST["UserTypecode"])){
	$usertypecode=$_POST["UserTypecode"];

	$sendertype="select distinct UserCode from MY_UPLOADS where SenderType='".$usertypecode."' order by UserCode asc";
    $sendertyperes = mysqli_query($conn,$sendertype);
    while($rowsendertype=mysqli_fetch_array($sendertyperes)){?>
   <option value="<?php echo $rowsendertype["UserCode"]; ?>"><?php echo $rowsendertype["UserCode"]; ?></option>
<?php } }

if(!empty($_POST["companycode"])){
	$companycode=$_POST["companycode"];
	$companycode= "'" . implode("', '", $companycode) . "'";
  $UserTypecode2=$_POST["UserTypecode2"];

	$sendertype="SELECT DISTINCT (CASE WHEN A.UserCode = 'AACA' AND B.TYPE_SHORTVAL IS NULL THEN UPPER(CONCAT(T.fullName,' ',T.LastName))  ELSE B.Type END) as fullname,(CASE WHEN A.UserCode = 'AACA' AND B.TYPE_SHORTVAL IS NULL THEN A.Type ELSE B.TYPE_SHORTVAL END) as shortname
FROM MY_UPLOADS A
LEFT JOIN MYUPLOADDD B ON A.Type = B.TYPE_SHORTVAL
LEFT JOIN (SELECT email, fullName, LastName FROM tbl_login WHERE userType=1 AND bit_deleted_flag=0 GROUP BY email) T ON T.email = A.Type
WHERE A.UserCode IN (".$companycode.") AND A.SenderType= '".$UserTypecode2."'
ORDER BY 1";
    $sendertyperes = mysqli_query($conn,$sendertype);
    while($rowsendertype=mysqli_fetch_array($sendertyperes)){?>
   <option value="<?php echo $rowsendertype["shortname"]; ?>"><?php echo strtoupper($rowsendertype["fullname"]); ?></option>
<?php } 

}?>
