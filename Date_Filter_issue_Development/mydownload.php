<?php 

session_start();
if($_SESSION['urlloc']==''){
  $getval=base64_decode($_GET['urlloc']);
  $_SESSION['urlloc']=$getval;
}


//print_r($_SESSION);
error_reporting(1);
include_once('config.php');
if($_SESSION['urlloc']!='' && $_SESSION['email']==''){
   header('Location: login.php');
}else if($_SESSION['urlloc']=='' && $_SESSION['email']==''){
  header('Location: logout.php');
}
?>

  
  <?php
if($_SESSION['userType']==1 ||  $_SESSION['newuserType']==1){
  $folder="AACA";
} if($_SESSION['userType']==2 || $_SESSION['userType']==4){
   $folder=$_SESSION['firmCode'];
} if($_SESSION['userType']==3){
   $folder=$_SESSION['clientCode'];
} if($_SESSION['userType']==1 && ($_SESSION['newuserType']==2 || $_SESSION['newuserType']==4)){
   $folder=$_SESSION['newattycode'];
 } if($_SESSION['userType']==1 && ($_SESSION['newuserType']==3)){
   $folder=$_SESSION['newclientcode'];
} if($_SESSION['userType']==1 && ($_SESSION['newuserType']==1)){
   $folder='AACA';
}



$files = array();

$dirHandle = opendir("Mako/downloadfile/".$folder);
 
// Properly scan through the directory for files, ignoring directory indexes (. & ..)
while (false !== ($file = readdir($dirHandle))) {
    if ($file != '.' && $file != '..') {
        $firstarray[] = $file;
    }
}
//print_r($firstarray);exit;
//$particularuser=$_SESSION['fullName'];
if($_SESSION['userType']==1){
  $RUTYPE='AACA';
  $pipQue="";
  $foldernamevalidate="Mako/downloadfile/AACA";
}if($_SESSION['userType']==2){
  $RUTYPE='Firm';
  $pipQue="AND RFCODE='".$_SESSION['firmCode']."'";
  $foldernamevalidate="Mako/downloadfile/".$_SESSION['firmCode'];
}if($_SESSION['userType']==3){
  $RUTYPE='Organization';
  $pipQue="AND ROCODE='".$_SESSION['clientCode']."'";
  $foldernamevalidate="Mako/downloadfile/".$_SESSION['clientCode'];
}if($_SESSION['userType']==4){
  $RUTYPE='Firm';
  $pipQue="AND RFCODE='".$_SESSION['firmCode']."'";
  $foldernamevalidate="Mako/downloadfile/".$_SESSION['firmCode'];
}
  $pipewayQuery="SELECT RGUSER from WSREGUSR WHERE RUTYPE='".$RUTYPE."' ".$pipQue." and RGEMAIL='".$_SESSION['email']."'";//echo  $pipewayQuery;
  $respipeway  =mysqli_query($conn,$pipewayQuery);
  $fetchusername=mysqli_fetch_assoc($respipeway);
  $folderpipewayname=$fetchusername['RGUSER'];
  if($folderpipewayname!=''){
    $particularuser=$folderpipewayname;
  }else{
    $particularuser=$_SESSION['email'];
  }

$secondarray=array("UD", "AC", "AD", "AF", "DP", "RM", "CS", "CO", "CP", "CM", "RD", "FT",  "KH", "JC", "JD", "KP","IT","PH","PA","PL","RA","RC","RJ","RO","WS","MD","TR");

$finalarray=array("UD"=>"ACCOUNT UPDATES", "AC"=>"ACCOUNTING ", "AD"=>"ADMINISTRATION ", 
  "AF"=>"AFFIDAVITS",  "DP"=>"BALANCE/ADJUSTMENT NOTIFICATION ",
  "RM"=>"CLIENT REMITTANCE ",  "CS"=>"CLIENT SKIP RESULTS ",
  "CO"=>"COMPANY ",  "CP"=>"COMPLIANCE", 
  "CM"=>"CONTRACT MASTER ",  "RD"=>"DENIED RECALLS ",
  "FT"=>"FINANCIAL TRANSACTION",  "KH"=>"HOLD LIST REVIEW", 
  "JC"=>"JUDGMENT INFORMATION FILE ",  "JD"=>"JUDGMENT REPORT ", 
  "KP"=>"KEEPER LIST REQUEST ","IT"=>"MIS DEPT",
  "PH"=>"PHONE REPORT",  "PA"=>"PLACEMENT ALLOCATION ",
  "PL"=>"PLACEMENTS ",  "RA"=>"REASSIGNED PLACEMENTS", 
  "RC"=>"RECALL",  "RJ"=>"REJECT",
  "RO"=>"REOPENED PLACEMENTS",  "WS"=>"WEB SERVICES ","MD"=>"MEDIA","TR"=>"Test Report");  
if(!empty($firstarray)){
  $result =array_intersect($firstarray, $secondarray);
}


/*To show share icon to selected usertype*/
if($_SESSION['userType']==1 && $_SESSION['newuserType']==''){
  $usertype=$_SESSION['userType'];
}else if($_SESSION['userType']==1 && $_SESSION['newuserType']==1){
   $usertype=$_SESSION['newuserType'];
}else if($_SESSION['userType']==2 && $_SESSION['newuserType']==''){
    $usertype=$_SESSION['userType'];
}else if($_SESSION['userType']==1 && $_SESSION['newuserType']==2){
   $usertype=$_SESSION['newuserType'];
} else if($_SESSION['userType']==3 && $_SESSION['newuserType']==''){
    $usertype=$_SESSION['userType'];
}else if($_SESSION['userType']==1 && $_SESSION['newuserType']==3){
   $usertype=$_SESSION['newuserType'];
}else if($_SESSION['userType']==4 && $_SESSION['newuserType']==''){
    $usertype=$_SESSION['userType'];
}else if($_SESSION['userType']==1 && $_SESSION['newuserType']==4){
   $usertype=$_SESSION['newuserType'];
}  
if($_SESSION['role']==6){
    $roleque="WHERE ADMIN='Y' AND USERCODE='".$usertype."'";
}else if($_SESSION['role']==8){
    $roleque="WHERE EXECUTIVE='Y' AND USERCODE='".$usertype."'";
}else if($_SESSION['role']==4){
    $roleque="WHERE MANAGER='Y' AND USERCODE='".$usertype."'";
}else if($_SESSION['role']==7){
    $roleque="WHERE ACCT_USER='Y' AND USERCODE='".$usertype."'";
}else if($_SESSION['role']==5){
    $roleque="WHERE USER='Y' AND USERCODE='".$usertype."'";
}else if($_SESSION['role']==9){
    $roleque="WHERE COLLECTOR='Y' AND USERCODE='".$usertype."'";
}
$submenuQuery="SELECT DISTINCT SUB_MENU_ITEM FROM USER_CONTROL_FILE ".$roleque."";//echo $submenuQuery;
$ressubmenushare=mysqli_query($conn,$submenuQuery);
$ressubmenuarchive=mysqli_query($conn,$submenuQuery);
 //print_r($ressubmenu);exit;
/*To show share icon to selected usertype*/


/* to show drop down list for sharing from my uploads to my downloads*/
if($_SESSION['userType']==1){
  $doenloadslist="SELECT FILENAME,DISPLAYNAME from  mydownload_dropdown where BIT_DELETED_FLAG=0 order by DISPLAYNAME asc";
}
if($_SESSION['userType']==2){
  $doenloadslist="SELECT FILENAME,DISPLAYNAME from  mydownload_dropdown where BIT_DELETED_FLAG=0 and (FIRM='YES' OR FIRM='BOTH') and CLIENT='BOTH' order by DISPLAYNAME asc";
}
if($_SESSION['userType']==3){
  $doenloadslist="SELECT FILENAME,DISPLAYNAME from  mydownload_dropdown where BIT_DELETED_FLAG=0 and (CLIENT='YES' OR CLIENT='BOTH') and FIRM='BOTH' order by DISPLAYNAME asc";
}
if($_SESSION['userType']==4){
  $doenloadslist="SELECT FILENAME,DISPLAYNAME from  mydownload_dropdown where BIT_DELETED_FLAG=0 and (FIRM='YES' OR FIRM='BOTH') and CLIENT='BOTH' order by DISPLAYNAME asc";
}
$resultmydropdown=mysqli_query($conn,$doenloadslist);

/* to show drop down list for sharing from my uploads to my downloads ends here*/


/* query to get firm and client code*/
$newqry ="SELECT  DISTINCT CCODE as firmCode FROM CC_REGSTR WHERE UTYPE=2 and Isdeleted=0 order by CCODE asc";
$newres= mysqli_query($conn,$newqry);
$newqry1 ="SELECT  DISTINCT CCODE as clientCode FROM CC_REGSTR WHERE UTYPE=3 and Isdeleted=0 order by CCODE asc";
$newres1= mysqli_query($conn,$newqry1);
$newqry2 ="SELECT  DISTINCT CCODE as agencyCode FROM CC_REGSTR WHERE UTYPE=4 and Isdeleted=0 order by CCODE asc";
$newres2= mysqli_query($conn,$newqry2);
/* query to get firm and client code ends*/
?>
<!DOCTYPE html>

<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Pipeway | Document Transfer</title>
   <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <meta http-equiv="X-UA-Compatible" content="IE=11"/>
  <meta http-equiv="X-UA-Compatible" content="IE=10"/>
  <meta http-equiv="X-UA-Compatible" content="IE=9"/>
  <meta http-equiv="X-UA-Compatible" content="IE=8"/>
  <link rel="stylesheet" href="css/PSnnect.min.css">
  <link rel="stylesheet" href="css/sweetalert.css">
  <!-- <link rel="stylesheet" href="css/PSdataTables.min.css"> -->
  <link rel="stylesheet" href="css/PSPanel.css">
  <link rel="stylesheet" href="css/PSdaterangepicker.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.10.13/css/jquery.dataTables.min.css">
<!--Import jQuery before export.js-->
  <script src="js/PSjquery.min.js"></script>
<!--Data Table-->
  <!-- <script type="text/javascript"  src=" https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script> -->
  <script src="js/PSnnect.min.js"></script>
  <script src="js/PSslimscroll.js"></script>
  <script src="js/PSnnectPanel.js"></script>
  <script src="js/PSjquery.dataTables.min.js"></script>
  <script src="js/PSnnect.dataTables.min.js"></script>
  <script src="js/sweetalert.min.js"></script>

  <link rel="apple-touch-icon-precomposed" sizes="114x114" href="img/fevicon.ico">
  <link rel="apple-touch-icon-precomposed" sizes="72x72" href="img/fevicon.ico">
  <link rel="apple-touch-icon-precomposed" href="img/fevicon.ico">
  <link rel="shortcut icon" href="img/fevicon.ico">
    <!-- for date sorting datatable=============================-->
 <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.js"></script>
<script src="https://cdn.datatables.net/plug-ins/1.10.19/sorting/datetime-moment.js"></script> 
  
  <style>
    th{background-color: #002e5b;color:white;}
    .pl0{padding-left:0px;}
    .pr0{padding-right:0px;}
    .label_height{height:12px;}
    .radio{padding-left: 0px!important;}
    .m_lr{margin-left: 25px;margin-right: 10px;}

    .table-responsive {
    /* min-height: .01%; */
   overflow-x: hidden;
}
#table-wrapper {
  position:relative;
  top:10px;
}

#table-wrapper table {
  width:100%;
    }
  
#table-wrapper table * {

}
#table-wrapper table thead th .text {
  position:absolute;   
  top:-20px;
  z-index:2;
  height:20px;
  width:35%;
  border:1px solid red;
}

  .sidebar-collapse table{
    width: 100% !important;
  }
  

  div::-webkit-scrollbar {
    width: 15px;
    height:15px;
}
 
div::-webkit-scrollbar-track {
    -webkit-box-shadow: inset 0 0 3px rgba(0,0,0,0.3);
}
 
div::-webkit-scrollbar-thumb {
  background-color: #28648a;
  outline: 1px solid slategrey;
  position:relative;
  right:10px;
}
.dataTables_filter {
    float: right!important;
    margin-left: 0px!important;
}

.p0{padding:0px!important;}

.dataTables_wrapper .dataTables_scroll {
    overflow-x: auto;
    height: 375px;
    overflow-y: hidden;
}
table.dataTable {
    border-collapse: collapse!important;
}
.dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody {
    overflow: auto!important;
    height: 330px!important;
    position: relative!important;
}

div::-webkit-scrollbar-thumb {
    background-color: #002e5b;
}
.dataTables_scrollBody {
    max-height: 335px !important;
    top: 0px;
}
table.dataTable{border-collapse: collapse!important;}
.dataTables_wrapper {padding: 0px !important;}

      .dataTables_info {
        padding: 8px 10px !important;
      }
      .table.dataTable.nowrap th, table.dataTable.nowrap td {
    white-space: nowrap !important;
}

.form-group {
    margin-bottom: 20px;
}
.radio_sel{background: #ccc;color: #fff;padding: 1px 0px;text-align: center;}
.radiobtn>label{display:block;font-size: 14px;}
.radiobtn>label>input{height:15px;}
.radiobtn>label>span{font-size: 13px;font-weight: 500;}

#loader {
         position: fixed;
         width: 100%;
         height: 100vh;
         background: url('img/loader.gif') no-repeat center center;
         z-index: 999999;
       }
#reloader{
         position: fixed;
         width: 100%;
         height: 100vh;
         background: url('img/loader.gif') no-repeat center center;
        /* z-index: 999999;*/
    
       }  

       /* PAGINATION CSS */
       .dataTables_wrapper .dataTables_paginate .paginate_button
  {padding: 0em 0em;margin-left: 0px;}
  .pagination{margin: 0px 0;}
  .dataTables_wrapper .dataTables_paginate .paginate_button:hover
  {border: 0px solid #111;
   background-color: #58585800;}
  #TblPagination_first,#TblPagination_last,#TblPagination_length{display:none;}
  .w-5 {width: 5% !important;}
   .w-10{width:10% !important;}

  </style>
</head>

<body class="hold-transition skin-yellow sidebar-mini fixed">
    
<div class="wrapper">
 <?php include('topnav.php');?>
  
  <aside class="main-sidebar">
   <?php include('leftpane.php');?>  
  </aside>
  
  <div class="content-wrapper">
    <section class="content-header">
      <h1>
        <!--My Uploads/My Downloads-->
      </h1>
         <ol class="breadcrumb">
        <li><a href="inventory_layout"><i class="fa fa-home"></i> Home</a></li>
         <li><i class="fa fa-files-o"></i> Document Transfer</li>
        <li class="active"><img src="img/downloadblack.png" alt="My Downloads" style="width: 12px;">
            My Downloads
        </li>
        
      </ol>
    </section>
    
    <section class="content">
        <div class="row">
            <div class="col-xs-12">

              <div class="box">
                    <div class="box-body">
                    <!--  <marquee><h2 style="color:red;">Work In Progress</h2></marquee> -->
                    
                        <div class="col-xs-12">
                         <?php while($fetchsubmenuarchive=mysqli_fetch_assoc($ressubmenuarchive)){ 
                          if($fetchsubmenuarchive['SUB_MENU_ITEM']=='Archive'){?>
                          <div class="text-right clearfix">
                            <button class="btn btn-lg btn-primary" type="button" onclick="location.href='mydownloadarchive';">View Archived Files</button>
                           
                           </div>
                         <?php } }?>
                            <div class="clearfix">
                               
                                <div class="tab-content">
                                  <div class="tab-pane active" id="MyDownloads">
                                      <div class="col-sm-12">
                                          <div class="row">
                                            <h3 class="profile-label">My Downloads</h3>
                                          </div>
                                      </div>
                                      <div class="col-sm-12 col-md-4 pl0">
                                          <div class="form-group clearfix">
                                            <label>Please Select Files: </label>
                                          <select class="form-control" id="File" name="File">
                                               <?php 
                                                $dirHandle=scandir($foldernamevalidate);
                                               $items_count = count($dirHandle);
                                              $dir = $foldernamevalidate;
                                             $n = 0;
                                             $dh = opendir($dir);
                                            while (($file = readdir($dh)) !== false) {
                                                if ($file != "." && $file != ".." && is_dir($dir . DIRECTORY_SEPARATOR . $file)) {
                                                    $n++;
                                                }
                                            }
                                              $subdir=$n+2;
                                             
                                           if ($items_count-$subdir >0){?>
                                                <option value="CO" selected="selected">COMPANY</option>
                                           <?php }

                                          foreach($finalarray as $key=>$value){
                                     
                                        if(in_array($key,$result)){
                                           $dirHandle=scandir('Mako/downloadfile/'.$folder.'/'.$key);
                                           $items_count = count($dirHandle);
                                         if ($items_count > 2){ ?>
                                                <option value="<?php echo $key;?>"><?php echo $value;?></option>
                                           <?php }   }  }
                                       
                                    if(in_array($particularuser, $firstarray)){ //echo $folder.'/'.$particularuser;
                                   
                                     $dirHandle=scandir('Mako/downloadfile/'.$folder.'/'.$particularuser);  

                                      $items_count = count($dirHandle);
                                         if ($items_count >= 2){?>
                                                <option value="<?php echo $particularuser;?>">MY DOWNLOADS</option>
                                           <?php }
                                     } 
                                    
                                   ?>
                                    </select>
                                          </div>
                                      </div>
                                      <div class="col-sm-12 col-md-4"style="position: relative;top: 12px;">
                                          <div class="form-group clearfix col-md-6 pl0" style="display: inline;line-height: 0px;margin-bottom: 6px;">
                                          <label class="radio" style="margin-bottom:0px;">
                                            <input type="radio" name="radfile" class="visible-hidden AssigneeCheckRadio" id="currentFile" onclick="getList(this.value);" value="1" checked>
                                              <span class="checkround"></span>
                                            </label><label class="m_lr">Current Files &nbsp;&nbsp;&nbsp;&nbsp;<label>
                                        </div>

                                        <!-- Visible only for MIS/IT Group -->
                                          <?php
                                            if(!empty($_SESSION['UserGroup']))
                                            {
                                              $getUserGroup = $_SESSION['UserGroup'];
                                              $array = explode(',', $getUserGroup);

                                              if(in_array("MIS/IT", $array) && $_SESSION['userType']==1)
                                            {
                                          ?>

                                        <div class="form-group clearfix col-md-6 pl0" style="display: inline;line-height: 0px;margin-bottom: 12px;">
                                          <label class="radio" style="margin-bottom:0px;">
                                            <input type="radio" name="radfile" class="visible-hidden AssigneeCheckRadio" id="viewUpload" onclick="getList(this.value);" value="3">
                                              <span class="checkround"></span>
                                            </label>
                                             <label class="m_lr">View Upload &nbsp;&nbsp;&nbsp;&nbsp;</label>
                                          </div>
                                          <?php } } ?>

                                        <div class="form-group clearfix">
                                            <label class="radio">
                                               <input type="radio" name="radfile" class="visible-hidden AssigneeCheckRadio" id="archiveFile" value="2" onclick="getList(this.value);">
                                              <span class="checkround"></span>
                                            </label><label class="m_lr">Archive Files (Older than 90 days) &nbsp;&nbsp;&nbsp;&nbsp;<label>

                                        </div>
                                      </div>

                                      <div class="col-sm-12 col-md-4 pr0" style="display:none;" id="selectesizediv">
                                          <div class="form-group clearfix">
                                            <div class="col-sm-12 col-md-4 pull-right pr0" id="showsize"> 
                                                 <div class="row"><label class="col-xs-12">Total selected size(KB)</label><!--<label class="label_height"></label>--></div><input type="text" name="FileSize" placeholder="Size(MB)" class="form-control" value="" id="sizecheck" style="color: #286090; font-weight: bold;"/>
                                              </div>
                                         </div>
                                      </div>

                                  </div>
        
                                </div>
                              </div>
                        </div>
                        <div class="col-xs-12">
                            <div class="table-responsive" id="tabledownload">
                              
                            </div>
                        </div>
                         <?php 
                         while($fetchsubmenushare=mysqli_fetch_assoc($ressubmenushare)){ 
                         if($fetchsubmenushare['SUB_MENU_ITEM']=='Share'){?>
                          <div class="col-xs-12" style="margin-top:6px;">
                           <!--  <a href="myuploadreport.php"><button type="submit" id="history" class="btn btn-primary btn-xs-100 mrg0 mrg20R">History</button></a> -->
                              <a href="#"><button type="submit" id="share" class="btn btn-primary btn-xs-100 mrg0 mrg20R">Share</button></a>
                        </div>
                      <?php } }?>
                    </div>
              </div>
                
            </div>
        </div>
    </section>
  </div>
  
   <?php include('footer.php');?>
  
  <div class="control-sidebar-bg"></div>
</div>

<!-- Share Popup -->
<div class="modal fade" id="myPopup" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
         <!--  <button type="button" class="close" data-dismiss="modal">&times;</button> -->
          <h4 class="modal-title text-center">Share Document</h4>
        </div>
        <div class="col-md-12 modal-body">
      
                                         

            <div class="form-group clearfix" style="position: relative;top: 20px;margin-bottom: 32px!important;">
           <label class="control-label col-sm-2 p0">Choose Type</label>
            <div class="col-sm-2">
            <label class="radio" style="padding-left: 27px!important;">
            <input type="radio" id='choosentype' name="choosentype" checked="checked" value="1" onclick='gettypebasedonfirmclient()'>AACA<span class="checkround"></span>               
            </label>
             </div>
            <div class="col-sm-1 p0">
            <label class="radio" style="padding-left: 19px!important;display: inline;">
            <input type="radio" id='choosentype' name="choosentype" value="2" onclick='gettypebasedonfirmclient()'>Firm<span class="checkround" style="left: -6px;"></span>                  
            </label>
             </div>
             <div class="col-sm-2 pr0">
             <label class="radio" style="padding-left: 27px!important;">
            <input type="radio" id='choosentype' name="choosentype" value="3" onclick='gettypebasedonfirmclient()'>Client<span class="checkround"></span>                  
            </label>
             </div>
             <div class="col-sm-2 pl0">
             <label class="radio" style="padding-left: 27px!important;">
            <input type="radio" id="choosentype" name="choosentype" value="4" onclick='gettypebasedonfirmclient()'>Agency<span class="checkround"></span>                  
            </label>
             </div>
         <!--     <div class="col-sm-3 p0">
             <label class="radio" style="padding-left: 27px!important;">
            <input type="radio" id="choosentype" name="choosentype" value="5" onclick='gettypebasedonfirmclient()'>AACA Internal<span class="checkround"></span>                 
            </label>
             </div> -->
             </div>

            


            <div id="firmdiv" class="desc" style="display: none;">
            <div class="form-group" >
            <label>Select Firm: </label>
            <select class="form-control" id="Firm" name="Firm" onchange='gettypebasedonfirmclient()'>
            <option value="">--Select--</option>
            <?php while($fetchdatanew = mysqli_fetch_array($newres)){ ?>
            <option value="<?php echo $fetchdatanew['firmCode']; ?>"><?php echo $fetchdatanew['firmCode']; ?></option>
            <?php } ?>
          </select>
          <span class='error' id='FirmError'></span>
            </div>
          </div>

      <div id="clientdiv" class="desc" style="display: none;">
        <div class="form-group">
            <label>Select Client: </label>
            <select class="form-control" id="Client" name="Client" onchange='gettypebasedonfirmclient()'>
            <option value="">--Select--</option>
            <?php while($fetchdatanew1 = mysqli_fetch_array($newres1)){  ?>
            <option value="<?php echo $fetchdatanew1['clientCode']; ?>"><?php echo $fetchdatanew1['clientCode']; ?></option>
            <?php } ?>
          </select>
          <span class='error' id='ClientError'></span>
        </div>
      </div>

      <div id="agencydiv" class="desc" style="display: none;">
        <div class="form-group">
            <label>Select Agency: </label>
            <select class="form-control" id="Agency" name="Agency" onchange='gettypebasedonfirmclient()'>
            <option value="">--Select--</option>
            <?php while($fetchdatanew2 = mysqli_fetch_array($newres2)){ ?>
            <option value="<?php echo $fetchdatanew2['agencyCode']; ?>"><?php echo $fetchdatanew2['agencyCode']; ?></option>
            <?php } ?>
          </select>
          <span class='error' id='AgencyError'></span>
        </div>
      </div>

      <div id="client4" class="desc">
        <div class="form-group">
            <label>Select Type: </label>
            <select class="form-control File" id="selectedFile" name="selectedFile" onchange="getuserlist(this);">
                <?php while($dropdownrow=mysqli_fetch_assoc($resultmydropdown)){
            $FILENAME=$dropdownrow['FILENAME'];
            $DISPLAYNAME=$dropdownrow['DISPLAYNAME']; ?>
            <option value="<?php echo $FILENAME; ?>"><?php echo $DISPLAYNAME; ?></option>
            <?php } ?>
            <option value="MYDOWNLOADS">MY DOWNLOADS</option>
              </select>
              <span class='error' id='TypeError'></span>
         </div>
      </div>

      <div id="userlistname" class="desc" style="display: none;"> 
            <div class="form-group" >
            <label>User list: </label>
            <select class="form-control" id='username' name='username'>
              <option value=''>--Select--</option>
            </select>
            <span class='error' id='UserError'></span>
            </div>
            </div>

    </div>
             
          <div class="modal-footer">
            <div class="btn-group btn-group-justified" role="group" aria-label="group button">
                <div class="btn-group" role="group">
                  <button type="button" name="shareBtn" id="shareBtn" class="btn btn-default btm-right-radius" role="button" style="padding: 12px; border: none; border-right: 1px solid #ccc; height: 40px; border-bottom-right-radius: 0px;">Share</button>
                  </a>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-default btm-left-radius" data-dismiss="modal" role="button">Close</button>
                </div>
            </div>
          </div>


      </div>
      
    </div>
  </div>

  <!-- Share Popup -->

 

<script>
  $(function () {

    
       $('#example').DataTable( {
        "pagingType": "full_numbers",
        "bPaginate": true,
        "fixedHeader": true,
         scrollY:        "100px",
         scrollX: true,
    });
  });

  $(document).ready(function(){
 $('#firmdiv').hide();
  $('#clientdiv').hide();
  $('#agencydiv').hide();
    $('input[name=choosentype]').change(function(){
     var inputValue = $("input[name='choosentype']:checked"). val();
     var File   =$('#File').val();
     if(inputValue==1){
       $('#firmdiv') .css('display','none');
       $('#clientdiv').css('display','none');
       $('#agencydiv').css('display','none');
       $("#File").val('CO'); 
       $('#username').val('select');
       if(File=='MYDOWNLOADS'){
        $('#username').show();
       }
       //$('#username').hide();
     }else if(inputValue==2){
       $('#firmdiv').css('display','block');
       $('#clientdiv').css('display','none');
       $('#agencydiv').css('display','none');
       $("#File").val('CO'); 
       $('#username').val('select');
        if(File=='MYDOWNLOADS'){ 
        $('#username').show();
       }
       
     }else if(inputValue==3){
       $('#firmdiv').css('display','none');
       $('#clientdiv').css('display','block');
       $('#agencydiv').css('display','none');
       $("#File").val('CO'); 
      
     }
     else if(inputValue == 4)
     {
      $('#firmdiv').css('display','none');
        $('#clientdiv').css('display','none');
      $('#agencydiv').css('display','block');
      $("#File").val('CO'); 
        $('#username').val('select');
        if(File=='MYDOWNLOADS'){ 
        $('#username').show();
       }
     }
  });
    $('#Firm').change(function(){
      $("#File").val('CO'); 
       $('#username').val('select');
    })
 });
  
$(document).ready(function(){

    $('#File').change(function(){
    $('#sizecheck').val('');
    if ($("input").is(':checked') ){
    var radval=$('input[name="radfile"]:checked').val()
    
    var selval=$('#File').val();
    var folder='<?php echo $folder;?>';

    if(radval == 3)
    {
      getType();
    }
    else
    {
    $.ajax({
    type: 'POST',
    url: 'myupdownAjax.php',
    data: { radval:radval,selval:selval,folder:folder} ,
    success: function (data) {
    $("#tabledownload").html(data);

    }
   
    });
    }
    }
    })

    if ($("input").is(':checked') ){
    var radval=$('input[name="radfile"]:checked').val();
      <?php if($_SESSION['urlloc']!=''){?>
     var selval='<?php echo  $_SESSION['urlloc'];?>';
    <?php } else { ?>
    var selval=$('#File').val();
  <?php } ?>
    var folder='<?php echo $folder;?>';

    $.ajax({
    type: 'POST',
    url: 'myupdownAjax.php',
    data: { radval:radval,selval:selval,folder:folder} ,
    success: function (data) {
    $('#File [value="' + selval + '"]').attr('selected', 'true');
    $("#tabledownload").html(data);

    },
    error: function ()
    { alert('there is some error to get Rate'); }
    });
    }
       
});
 function getList(radio_val){
  var radvalfordropdown=radio_val;
   var folder=$('#File').val();
   
   $.ajax({
      type: 'POST',
      url: 'dropdowncheck.php',
      data: { radvalfordropdown:radvalfordropdown,folder:folder} ,
      success: function (data) {
      $("#File").html(data);
     setTimeout( function(){ 
          getRecord();
     }  , 1000 );
   }
     
   });
 }

function getRecord(){
  var radval=$('input[name=radfile]:checked').val();
  var selval=$('#File').val();
  var folder='<?php echo $folder;?>';
   if(radval == 3)
  {
    getType();
  }
  else
  {
     
   $.ajax({
      type: 'POST',
      url: 'myupdownAjax.php',
      data: { radval:radval,selval:selval,folder:folder} ,
      success: function (data) {
       $("#tabledownload").html(data);
     
     }
   });
  }
}

function getType(){
var radval=$('input[name=radfile]:checked').val();
var type=$('#File').val();
var folder='<?php echo $folder;?>';
  $.ajax({
  url:"getTypesRecord.php",
  type:"POST",
  data:{radval:radval,type:type,folder:folder},
  success:function(data)
  {
    //$("#displayType").css("display", "block");
    //jQuery('#type').html(data);
    $("#tabledownload").html(data);
    // document.getElementById("share").classList.remove("myUploadShare");
    // document.getElementById("share").classList.add("myUploadShare");
   }
  })
}

/*get by default value of company by puja*/
$(document).ready(function () {

    

  });


 $(document).ready(function(){
 $('#share').on('click', function() {
      var radval=$('input[name="radfile"]:checked').val();
      var selval=$('#File').val();
      var folder='<?php echo $folder;?>';

      if(radval == 3)
      {
        if (!$('.checkcheckbox').is(':checked')) {
         $('#selrecordError').text('Select at least one file');
         $('#selrecordError').css('display', 'block');
      //flag = false;
         }
         else 
         {
           $('#selrecordError').css('display', 'none');
           $('#myPopup').modal('show');
         } 
      }
      else
      {
        // window.open('sharedoc?radval=' + encodeURIComponent(radval) + '&selval=' + encodeURIComponent(selval) + '&folder='+ encodeURIComponent(folder),'_parent');
        if (!$('.checkcheckbox').is(':checked')) {
         $('#selrecordError2').text('Select at least one file');
         $('#selrecordError2').css('display', 'block');
      //flag = false;
         }
         else
         {
           $('#selrecordError2').css('display', 'none');
           $('#myPopup').modal('show');
         }
    }
  })

       
 });


function getuserlist(sel){
  var File  = sel.value;
  var username='MYDOWNLOADS';
 
  if(File==username){
    $('#userlistname').show();
  }else{
     $('#userlistname').hide();
  }
  var usertype='<?php echo $_SESSION['userType']?>';
  if(usertype==1){
  var choosetype1=$('input[name="choosentype"]:checked').val();
  if(choosetype1==2){
    var CODE=$('#Firm').val();
    var choosetype=2;
  }else if(choosetype1==3){
    var CODE=$('#Client').val();
     var choosetype=3;
     
  }else if(choosetype1==4){
     var CODE=$('#Agency').val();
     var choosetype=4;
     
  }else if(choosetype1==1){
     var CODE='';
     var choosetype=1;
     
  }else {
     var CODE='';
      var choosetype=1;
     
  }
  }if(usertype==2){
   var choosetype=2;
    var CODE='<?php echo $_SESSION['firmCode']?>'
  }if(usertype==3){
   var choosetype=3;
    var CODE='<?php echo $_SESSION['clientCode']?>'
  }
  $.ajax({
      type: 'POST',
      url: 'myupsharedocAjax.php',
      data: { CODE:CODE,choosetype:choosetype} ,
      success: function (data) {
       
        $("#username").html(data);
     
     },
     error: function ()
     { alert('there is some error to get Rate'); }
   });

}

function gettypebasedonfirmclient(){
  var inputValue = $("input[name='choosentype']:checked"). val();
    $.ajax({
      type: 'POST',
      url: 'showdplist.php',
      data: { inputValue:inputValue} ,
      success: function (data) {
        $(".File").html(data);
        $('#userlistname').hide();
     
     },
     error: function ()
     { alert('there is some error to get Rate'); }
   });
}




// function getSelectedRows() {
//   getsize = [];
//     $('.select-row:checked').each(function () {
//     getsize.push($(this).data("id"));
//   });
//   return getsize;
// }


function validate(){
  var flag = true;
  $('#FirmError').css('display', 'none');
  $('#ClientError').css('display', 'none');
  $('#TypeError').css('display', 'none');
  $('#UserError').css('display', 'none');

  var File         =$('#selectedFile').val();
  var choosetype1  =$('input[name="choosentype"]:checked').val();
  var usertype     ='<?php echo $_SESSION['userType']?>';
  var username     =$('#username').val();
  var firm         =$('#Firm').val();
  var Client      =$('#Client').val();
  // if(FromFile==''){
  //    swal("Missing Information Alert!", "From can not be left blank", "error");
  //   //alert('From can not be left blank');
  //   flag = false;
  // }
   if(choosetype1==2 && usertype==1){
     if(firm==''){
     //swal("Missing Information Alert!", "Firm can not be left blank", "error");
     //alert('Firm can not be left blank');
      $('#FirmError').text('Firm can not be left blank');
     $('#FirmError').css('display', 'block');
     $("#Firm").addClass("makeRed");
     flag = false;
   }
  } 
   if(choosetype1==3 && usertype==1){
      if(Client==''){
     //swal("Missing Information Alert!", "Client can not be left blank", "error");
     //alert('Client can not be left blank');
     $('#ClientError').text('Client can not be left blank');
     $('#ClientError').css('display', 'block');
     $("#Client").addClass("makeRed");
     flag = false;
   }
  } if(File=='MYDOWNLOADS' && username==''){
     //swal("Missing Information Alert!", "User list can not be left blank", "error");
     //alert('User list can not be left blank');
     $('#UserError').text('User name can not be left blank');
     $('#UserError').css('display', 'block');
     $("#username").addClass("makeRed");
    flag = false;
   
  }

  if (flag) {
    return true;
 }else{
    return false;
 }
}

</script>
<?php
/* Start:Change log sweet alert  by puja kuamri on dt-061121 */
 if(isset($_SESSION['success']))
  {?>
   <script> swal({title: "",
                text: "Document shared successfully",
                timer: 4000,
                showConfirmButton: false,
                type: 'success'
              });  </script>
 <?php }
 unset($_SESSION['success']);

 if(isset($_SESSION['mydwnldshare']))
  {?>
   <script> swal({title: "",
                text: "Document shared successfully",
                timer: 4000,
                showConfirmButton: false,
                type: 'success'
              });  </script>
 <?php }
 unset($_SESSION['mydwnldshare']);
 /*End: Change log sweet alert  by puja kuamri on dt-061121 */
?>
</body>
</html>
