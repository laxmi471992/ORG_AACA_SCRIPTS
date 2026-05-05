<?php 
error_reporting(0);
session_start();
if(!isset($_SESSION['email']))
{
  header('Location: logout.php');
  exit();
  }
include('config.php');
//include('connectother.php');
// $accountNo =$_GET['accNo'];
//$accountNo =base64_decode($_GET['accNo']);
$accountNo =base64_decode($_SESSION['accNo']);
$acc=str_replace("a","#",$accountNo );
$query="SELECT FULL_NAME, ACCOUNT_NUMBER, State FROM SEARCH_QUERY_DATA WHERE ACCOUNT_NUMBER='".$acc."'";
$result = mysqli_query($conn,$query);
$row=mysqli_fetch_assoc($result);
$query1="SELECT A.FILNUM,A.RFILELOC,A.IMFLNAME, A.RACTNM AS 'Acct#:', A.IMAGEID, A.ORGCODE, A.ROFFCD, A.RCURATTY, 
	(CASE WHEN A.DOCTYPESUB = '' THEN A.DOCTYPE ELSE CONCAT_WS('-',A.DOCTYPE, A.DOCTYPESUB) END) AS DocType, CAST(A.RDATEASG AS DATE) AS DocDtRcvd,
	A.ORGCODE AS ClntCde, CONCAT('quay.aacanet.org/aacanet/members/streamfile.aspx?fileID=') AS URL
	FROM WFAACAIMG A WHERE A.DOCTYPE IN ('PLACEMENT','placement','Placement') AND A.RACTNM = '".$acc."'";//echo $query1;exit;
$result1 = mysqli_query($conn,$query1);

?>
<!DOCTYPE html>

<html oncontextmenu="return false">
<head>
   <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <meta http-equiv="X-UA-Compatible" content="IE=11"/>
  <meta http-equiv="X-UA-Compatible" content="IE=10"/>
  <meta http-equiv="X-UA-Compatible" content="IE=9"/>
  <meta http-equiv="X-UA-Compatible" content="IE=8"/>
  <link rel="apple-touch-icon-precomposed" sizes="114x114" href="../img/fevicon.ico">
  <link rel="apple-touch-icon-precomposed" sizes="72x72" href="../img/fevicon.ico">
  <link rel="apple-touch-icon-precomposed" href="img/fevicon.ico">
  <link rel="shortcut icon" href="img/fevicon.ico">
  <link rel="stylesheet" href="css/dataTables.min.css">
  <link rel="stylesheet" href="css/PSnnect.min.css">
  <link rel="stylesheet" href="css/PSdataTables.min.css">
  <link rel="stylesheet" href="css/PSPanel.css">
  <link rel="stylesheet" href="css/PSdaterangepicker.css">
  </head>
  <style>
thead{background-color:#28648a;color:white;}
#radiobutton{font-weight: bold;}
.dataTables_filter {
display: none;
}
td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}
th, td { white-space: nowrap; font-weight:bold;}
.alignment{text-align:right;}
  th, td { white-space: nowrap; }
    div.dataTables_wrapper {
        margin: 0 auto;
    }
 
    div.container {
        width: 80%;
    }
  .dt-buttons{
  margin-left: 362px;
  margin-top: -35px;
}
.dataTables_info{display:none;}
  </style>

<body>
<table id="example" class="stripe row-border order-column" style="width:90%;margin-left:64px;" border="1">
<div class="card-header account" style="width:90%;margin-left:64px;background-color: #28648a;">
  <p id="accdetail" style="color:white;"><b style="font-size:19px;">Placement Document Information</b></p>
</div>
	<div class="card-header account" style="width:90%;margin-left:64px;background-color: #28648ad6;height: 37px;">
		<div class="col-sm-2">
		<div >
		 <p style="color:white;margin-top: 7px;text-align: left;"><b>
		  <?php 
		  $Name=str_replace(",","",$row['FULL_NAME'] );
		  echo $Name;?> 
		  </b></p>
		</div>
		</div>
		<div class="col-sm-2">
		<div >
		 <p style="color:white;margin-top: 7px;text-align: left;"><b><?php echo $row['ACCOUNT_NUMBER'];?></b></p>
		</div>
		</div>
		<div class="col-sm-2">
		<div >
		 <p style="color:white;margin-top: 7px;text-align: left;"><b><?php echo $row['State'];?></b></p>
		</div>
		</div>
	</div>
<thead>
<tr>
<th class="text-left">Date Doc Received</th>
<th class="text-left">Client Code</th>
<th class="text-left">Document Type</th>
</tr>
</thead>
<tbody>
  <?php
  if(mysqli_num_rows($result1)){
 while($row1=mysqli_fetch_assoc($result1)){
    $phrase  = htmlspecialchars($row1['RFILELOC']);
    $remotepath=substr($phrase, 19, -1);
    $reqpath=substr($remotepath, 0, -9);
 
    // if($remotepath=='BAY' || $remotepath=='bay'){
    //  $path="//bay/Judmedia/"; 
    // }else if($remotepath=='mak' || $remotepath=='MAK'){
    //   $path="//mako/Metafile/metafile/"; 
    // }else if($remotepath=='coh' || $remotepath=='COH'){
    //   $path="//coho/AACA/PDF Archive/"; 
    // }


    $strpath = str_replace('\\', '/', $reqpath);
    $path    ='/'.$strpath;
    $filelist=$row1['IMFLNAME'];
    $file="Report/downloadfile?nama=".urlencode($filelist)."&&remote=".urlencode($path);
    $filenew=urlencode($filelist)."&&remote=".urlencode($path);
    
?>
<tr>
	<td><?php echo $row1['DocDtRcvd'];?></td>
	<td><?php echo $row1['ClntCde'];?></td>
	<td class="docview" data-name="<?php echo  $filenew;?>" style="color:#00b8ff;cursor: pointer;"><u><?php echo $row1['DocType'];?></u></td>

</tr>
<?php } } else{
   $msg="No Placement Documents found";
   echo "<p style='color:red;text-align:center'>" . $msg . "</p>";
}?>
</tbody>
</table>
</body>
 <script src="js/PSjquery.min.js"></script>
  <script src="js/PSnnect.min.js"></script>
  <script src="js/PSslimscroll.js"></script>
  <script src="js/PSnnectPanel.js"></script>

  <script src="js/autologout.js"></script>
 <!-- <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script> -->
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
<script>
  $(document).ready(function(){
    $('.docview').on('click',function(){
       var nama =$(this).data('name');
       window.open('Report/downloadfile?nama=' + (nama) , '_blank');
    })
  })
  </script>
</html>