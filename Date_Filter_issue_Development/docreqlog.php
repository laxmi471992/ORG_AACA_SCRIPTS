<?php
error_reporting(0);
session_start();
include "../config.php";
if (!isset($_SESSION["email"])) {
    header("Location: ../logout");
    exit();
}

$userType = $_SESSION["userType"];
$role = $_SESSION["role"];
$firmCode = $_SESSION["firmCode"];
$clientCode = $_SESSION["clientCode"];

$usergroup = "'" . str_replace(",", "','", $_SESSION["UserGroup"]) . "'";
if ($_SESSION["userType"] == 2) {
    $submediaQuery =
        "WHERE a.FIRM_CODE='" .
        $_SESSION["firmCode"] .
        "' and b.REVWGRP IN(" .
        $usergroup .
        ") and a.REQUESTED_TO='Firm'";
} elseif ($_SESSION["userType"] == 4) {
    $submediaQuery =
        "WHERE a.AGENCY_CODE='" .
        $_SESSION["firmCode"] .
        "' and b.REVWGRP IN(" .
        $usergroup .
        ") and a.REQUESTED_TO='Agency'";
} elseif ($_SESSION["userType"] == 3) {
    $submediaQuery =
        "WHERE a.CLIENT_CODE='" .
        $_SESSION["clientCode"] .
        "' and b.REVWGRP IN(" .
        $usergroup .
        ") and a.REQUESTED_TO='Client'";
} else {
    $submediaQuery = "";
}
$query =
    "select a.MED_LOG_ID,a.ACCNTNMBR,a.FIRMFILENMBR,a.FIRM_CODE,a.CLIENT_CODE,a.DOC_REQ_CODE,a.DOC_DESC,a.REQUSTR_NOTE,a.REQUST_ID,a.FIRM_REQUST_DT,b.DOCDESC,a.FIRM_CLIENT,a.DOC_STATUS,a.REQUESTED_TO,a.REQUESTED_BY,a.STATUS_REASON,a.DOC_PATH,a.RE_REQUEST_REASON,a.COMMENT,a.SHARED_NOTE,a.COMMENT_TEXT,c.STATUS_DESC from  MEDIAMNGMNT_LOGS a LEFT JOIN MEDDOCCNTRLFILE b ON a.DOC_REQ_CODE = b.DB2CODE LEFT JOIN MEDIA_STATUS c ON a.DOC_STATUS=c.STATUS_ID " .
    $submediaQuery .
    " order by a.FIRM_REQUST_DT desc limit 100";
$result = mysqli_query($conn, $query);

$grparray = explode(",", $_SESSION["UserGroup"]);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Pipeway |Document Request Log</title>
<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
<meta http-equiv="X-UA-Compatible" content="IE=11"/>
<meta http-equiv="X-UA-Compatible" content="IE=10"/>
<meta http-equiv="X-UA-Compatible" content="IE=9"/>
<meta http-equiv="X-UA-Compatible" content="IE=8"/>
<link rel="apple-touch-icon-precomposed" sizes="114x114" href="../img/fevicon.ico">
<link rel="apple-touch-icon-precomposed" sizes="72x72" href="../img/fevicon.ico">
<link rel="apple-touch-icon-precomposed" href="../img/fevicon.ico">
<link rel="shortcut icon" href="../img/fevicon.ico">

 <link rel="stylesheet" href="../css/PSnnect.min.css">
  <link rel="stylesheet" href="../css/PSPanel.css">
  <link rel="stylesheet" href="../css/PSdaterangepicker.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.10.13/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="../css/sweetalert.css">
  <link rel="stylesheet" href="../css/Multiselect.css"> 
<!--Import jQuery before export.js-->
   <script src="../js/PSjquery.min.js"></script>
  <!--Data Table-->
  <script type="text/javascript"  src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script> 
  <!-- for date sort -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.js"></script>
<script src="https://cdn.datatables.net/plug-ins/1.10.19/sorting/datetime-moment.js"></script>
  <script src="../js/PSnnect.min.js"></script>
  <script src="../js/PSslimscroll.js"></script>
  <script src="../js/PSnnectPanel.js"></script>
  <script src="../js/PSjquery.dataTables.min.js"></script>
  <script src="../js/PSnnect.dataTables.min.js"></script>
  <script src="../js/sweetalert.min.js"></script>
<script type="text/javascript" src="../js/MultiSelectJs.js"></script>

<style>

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

.dataTables_wrapper {
        padding: 0px !important;
      }

#dataTable_first,#dataTable_last{display:none;}
.sidebar-collapse table{
width: 100% !important;
}
.dataTables_scrollHeadInner{
width: 100% !important;
}
#loader {
        position: fixed;
        width: 100%;
        height: 100vh;
        background: url('img/loader.gif') no-repeat center center;
        z-index: 1000;
      }
/*#dataTable_filter{float: left;
margin-left: -703px;}*/

.has-details {
  position: relative;
}

.details {
  position: absolute;
    top: -48px;
    transform: translateY(70%) scale(0);
    transition: transform 0.1s ease-in;
    transform-origin: left;
    display: inline;
    background: #002e5b;
    z-index: 20;
    min-width: 135%;
    padding: 0.4rem;
    border-radius: 2px;
}
.details>a{color:#fff!important;}
.details>a:hover{color: #bb133e!important;text-decoration: underline;font-weight: bold;}
.has-details:hover p a{color:#fff;}
.has-details:hover p {transform: translateY(70%) scale(1);}
div::-webkit-scrollbar {width: 5px;height:5px;}
 
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
.demo{
	width:100%;table-layout:fixed;
}
.multiselect-native-select .btn-group {
display: block;
width: 100% !important;

}
p {margin:0px 0px 2px 0px;}
.multiselect-native-select .btn-group .btn {padding:6px 12px;}


.fa-file-excel-o {
    position: relative;
    font-size: 24px;
    cursor: pointer;
    z-index: 1;
}

.dt-buttons {
    float: right !important;
    position: absolute;
    font-size: 12px;
    right: 210px;
    top: 2px;
    cursor: pointer;
    z-index: 1;
    color: #3c8dbc;
}

button.dt-button.buttons-excel.buttons-html5 {
	border: none;
	background: transparent;
}

table.dataTable thead .sorting {
	background-image: none;
}
div.dataTables_filter label {
    font-weight: normal;
    white-space: nowrap;
}
 /* PAGINATION CSS */
.dataTables_wrapper .dataTables_paginate .paginate_button
  {padding: 0em 0em;margin-left: 0px;}
  .pagination{margin: 0px 0;}
  .dataTables_wrapper .dataTables_paginate .paginate_button:hover
  {border: 0px solid #111;
   background-color: #58585800;}
  #TblPagination_first,#TblPagination_last,#TblPagination_length{display:none;}
  .w-20{width:20% !important;}
  .w-15{width:15% !important;}
  .w-10{width:10% !important;}
  .w-5{width:5% !important;}
  .w-4{width:4% !important;}
  .w-2{width:2% !important;}
  .px-10{padding-left: 10px !important;padding-right: 10px !important;}
  table.dataTable thead th, table.dataTable tbody td {padding: 10px 6px;}
</style>

</head>
<body class="hold-transition skin-yellow sidebar-mini fixed Loader">
<div id="loader" style="display: none;"></div>
<div class="wrapper">
<?php include "../topnav.php"; ?>
<aside class="main-sidebar">
<?php include "../leftpane.php"; ?>
</aside>
<!--main content here-->
  <div class="content-wrapper">
  <section class="content">
    
      
  <!-- <div class="row">
            <div class="col-xs-12">
              
          <div class="box">
          <div class="box-header with-border"> -->
    <form class="row" style="padding:15px 0px;">
          <div class="col-sm-6 col-md-2">
            <div class="form-group multiselect-option clearfix">
              <label>Document Status<span class="mandatory">*</span></label>
              <select class="form-control" name="status[]" multiple="multiple" id="status-multiselect" >
               <?php
               $mediastatus="SELECT STATUS_ID,STATUS_DESC from MEDIA_STATUS order by STATUS_DESC asc";
               $resmediastatus=mysqli_query($conn,$mediastatus);
               while($fetchresmediastatus=mysqli_fetch_assoc($resmediastatus)){?>
                <option value="<?php echo $fetchresmediastatus['STATUS_ID'];?>" selected><?php echo $fetchresmediastatus['STATUS_DESC'];?></option>

               <?php }
               ?>
              </select>
            </div>
          </div>
            <div class="col-sm-6 col-md-2">
            <div class="form-group multiselect-option clearfix">
              <label>Document Type<span class="mandatory">*</span></label>
              <select class="form-control" name="type[]" multiple="multiple" id="type-multiselect" >
                   <?php
                   $strQuery3 =
                       "SELECT DISTINCT a.DOC_REQ_CODE,b.DOCDESC FROM MEDIAMNGMNT_LOGS a LEFT JOIN MEDDOCCNTRLFILE b ON a.DOC_REQ_CODE = b.DB2CODE  ORDER BY (a.DOC_REQ_CODE);";
                   $results3 = mysqli_query($conn, $strQuery3); //exit;
                   $docno = mysqli_num_rows($results3);
                   if ($docno < 1) {
                       echo '<span class="error" id="firmError">No Document Available</span>';
                   } elseif ($docno == 1) {

                       $rows = mysqli_fetch_array($results3);
                       $DOC_REQ_CODE = $rows["DOC_REQ_CODE"];
                       $DOC_REQ_DESC = $rows["DOCDESC"];
                       ?>
                      <option value="<?php echo $DOC_REQ_CODE; ?>" selected ><?php echo $DOC_REQ_DESC; ?></option>
                      <?php
                   } else {
                       while ($rows = mysqli_fetch_array($results3)) {
                           $DOC_REQ_CODE = $rows["DOC_REQ_CODE"];
                           if ($DOC_REQ_CODE != "") {

                               $DOC_REQ_CODE = $rows["DOC_REQ_CODE"];
                               $DOC_REQ_DESC = $rows["DOCDESC"];
                               ?>
                        <option value="<?php echo $DOC_REQ_CODE; ?>" selected ><?php echo $DOC_REQ_DESC; ?></option>
                  <?php
                           }
                       }
                   }
                   ?>
              </select>
            </div>
          </div>
          <div class="col-sm-6 col-md-2">
            <div class="form-group multiselect-option clearfix">
              <label>Firm Code</label>
              <input type="hidden" id="usertype" name="usertype" value="<?php echo $_SESSION[
                  "userType"
              ]; ?>"> 
              <select class="form-control" name="firm[]" multiple="multiple" id="firm-multiselect" >
              <?php
              $strQuery2 =
                  "SELECT DISTINCT FIRM_CODE FROM MEDIAMNGMNT_LOGS  ORDER BY (FIRM_CODE);";
              $results2 = mysqli_query($conn, $strQuery2); //exit;
              $firmnumber = mysqli_num_rows($results2);
              if ($firmnumber < 1) {
                  echo '<span class="error" id="firmError">No firm Available</span>';
              } elseif ($firmnumber == 1) {

                  $rows = mysqli_fetch_array($results2);
                  $ATTY_CDE = $rows["FIRM_CODE"];
                  $firmString = $ATTY_CDE;
                  ?>
                      <option value="<?php echo $ATTY_CDE; ?>" selected ><?php echo $ATTY_CDE; ?></option>
                      <?php
              } else {
                  while ($rows = mysqli_fetch_array($results2)) {
                      $ATTY_CDE = $rows["FIRM_CODE"];
                      if ($ATTY_CDE != "") {
                          $firmString = $firmString . "," . $ATTY_CDE; ?>
                        <option value="<?php echo $ATTY_CDE; ?>" selected ><?php echo $ATTY_CDE; ?></option>
                  <?php
                      }
                  }
              }
              ?>
              </select>             
            </div>
          </div>
          <div class="col-sm-6 col-md-2">
            <div class="form-group multiselect-option clearfix">
              <label>Client Code</label>
              <select class="form-control" name="company[]" multiple="multiple" id="company-multiselect">
                <?php
                $strQuery1 =
                    "SELECT DISTINCT CLIENT_CODE FROM MEDIAMNGMNT_LOGS  ORDER BY (CLIENT_CODE);";

                $results1 = mysqli_query($conn, $strQuery1);
                $companyNumbers = mysqli_num_rows($results1);

                if ($companyNumbers < 1) {
                    echo '<span class="error" id="ClientError">No Client Available</span>';
                } elseif ($companyNumbers == 1) {

                    $rows = mysqli_fetch_array($results1);
                    $CLIENT_CDE = $rows["CLIENT_CODE"];
                    $companyString = $CLIENT_CDE;
                    ?> <option value="<?php echo $CLIENT_CDE; ?>"  selected><?php echo $CLIENT_CDE; ?></option>
                 <?php
                } else {
                    while ($rows = mysqli_fetch_array($results1)) {

                        $CLIENT_CDE = $rows["CLIENT_CODE"];
                        $companyString = $companyString . "," . $CLIENT_CDE;
                        ?>
                           <option value="<?php echo $CLIENT_CDE; ?>" selected><?php echo $CLIENT_CDE; ?></option>
                       <?php
                    }
                }
                ?> </select><?php if (
     $companyNumbers == 1
 ) { ?> <input type="hidden" id="firstFilter" name="firstFilter" value="Company"> <?php } elseif (
     $firmnumber == 1
 ) { ?> <input type="hidden" id="firstFilter" name="firstFilter" value="Firm"> <?php } else { ?> <input type="hidden" id="firstFilter" name="firstFilter" value=""> <?php } ?>
            </div>
          </div>   
          <div class="col-sm-6 col-md-2">
                <div class="form-group clearfix">
                    <label>Time Frame</label>
                    <select class="form-control" name="duration" id="duration" style="box-shadow: none;height: 32px;">
                        <option  selected value=''>All</option>
                        <option value="LT30" >Less than 30 days</option>
                        <option value="LT60" >Less than 60 days</option>
                        <option value="LT90">Less than 90 days</option>
                        <!-- <option value="GTE120">Greater than or equal 90 days</option> -->
                      
                    </select>
                      <span class="error" id="durationError"></span>
                  </div>
          </div>
          <div class="col-sm-6 col-md-1">
            <label class="invisible"></label>
            <a href="#" class="Hold" data-toggle="modal" data-userType= "<?php echo $userType; ?>" 
              data-role= "<?php echo $role; ?>"  data-firmCode= "<?php echo $firmCode; ?>" 
              data-clientCode= "<?php echo $clientCode; ?>"  class="userinfo"  title="Reset"  id="resetbutton">
              <button class="btn btn-sm btn-primary form-control"  type="button" id="resetbutton1" style="padding:6px 0px;margin-top:3px;">Reset</button>
              </a>
          </div>
          <div class="col-sm-6 col-md-1">
            <label class="invisible"></label>
            <button class="btn btn-sm btn-primary form-control" data-userType= "<?php echo $userType; ?>"  data-role= "<?php echo $role; ?>" data-firmCode= "<?php echo $firmCode; ?>" data-clientCode= "<?php echo $clientCode; ?>" type="button" id="viewbutton" style="padding:6px 0px;margin-top:3px;">Search</button>
          </div>
         
        </form>
              <!-- </div> -->
            <!-- <div class="box-body">
                        <div class="col-xs-12 p0">
                                       -->
                                       
    <div class="table-responsive" id="tables-wrapper">
    <!-- <div class="dt-buttons pull-right" id="dwnldIcon" style="<?php echo $buttonVisibility; ?>">
         <a href="<?php echo $pathVar; ?>"  target="_blank" id="download_link_rwk"  >
          <i class="fa fa-file-excel-o" title="Export to Excel" aria-hidden="true"  id="downloadbtn"></i>
         </a>
       
    </div> -->
   
    
      <table class="display nowrap table table-bordered table-striped table-hover table-condensed demo" id="dataTable" width="100%" cellspacing="0" >
      <div id="dataTable_wrapper1" style="position: relative;top: 30px;"></div>
      <thead>
        <tr>
        
	        
	        <th class="text-center w-10">Account Number</th>
	        <th class="text-center">Firm File No</th>
	        <th class="text-center">Firm Code</th>
	        <th class="text-center">Client Code</th> 
	        <th class="text-center w-10">Document Type </th>
	       <?php if ($_SESSION["userType"] == 1) { ?>
	        <th class="text-center">Request to</th>
	        <th class="text-center">Request by</th>
	       <?php } ?> 
	        <th class="text-center w-5">Attached Document</th>
	        <th class="text-center">Notes</th>
	        <th class="text-center">Status</th> 
	        <th class="text-center w-15">Requester Id</th>
	        <th class="text-center w-10">Last Action Date</th>
	        
        </tr>
        </thead>
        <tbody class="postList">
        <?php if (mysqli_num_rows($result)) {
            while ($row = mysqli_fetch_assoc($result)) {

                                                $status=$row['STATUS_DESC'];
                                                 if($row['DOC_STATUS']==0 ){
                                                  $title='';
                                                  $note=$row['REQUSTR_NOTE'];
                                                  
                                                 }else if($row['DOC_STATUS']==1 ){
                                                  $title='';
                                                  $note=$row['REQUSTR_NOTE'];
                                                  
                                                 }else if($row['DOC_STATUS']==2){
                                                  $title=$row['STATUS_REASON'];
                                                  $note=$row['STATUS_REASON'];
                                                  
                                                 }else if($row['DOC_STATUS']==3){
                                                  $title='';
                                                  $note=$row['REQUSTR_NOTE'];
                                                  
                                                 }else if($row['DOC_STATUS']==4){
                                                  $title='';
                                                  $note=$row['RE_REQUEST_REASON'];
                                                  
                                                 }else if($row['DOC_STATUS']==5){
                                                  $title='';
                                                  if($row['SHARED_NOTE']!=''){
                                                    $note=$row['SHARED_NOTE'];
                                                  }else{
                                                    $note=$row['REQUSTR_NOTE'];
                                                  }
                                                  
                                                 }else if($row['DOC_STATUS']==6){
                                                  $title='';
                                                  $note=$row['COMMENT_TEXT'];
    
    
                                                 }
                ?>
                                         <tr>
                                       
                                        
                                         <td class="text-left w-10"><?php echo $row[
                                             "ACCNTNMBR"
                                         ]; ?></td>
                                         <td class="text-left"><?php echo $row[
                                             "FIRMFILENMBR"
                                         ]; ?></td>
                                         <td class="text-left"><?php echo $row[
                                             "FIRM_CODE"
                                         ]; ?></td>
                                         <td class="text-left"><?php echo $row[
                                             "CLIENT_CODE"
                                         ]; ?></td> 
                                       
                                         <td class="text-left w-10"><?php if (
                                             $row["DOC_REQ_CODE"] != ""
                                         ) {
                                             echo $row["DOCDESC"];
                                         } else {
                                             echo "";
                                         } ?></td>
                                        
                                         <?php if (
                                             $_SESSION["userType"] == 1
                                         ) { ?>
                                          <td class="text-left"><?php echo $row[
                                              "REQUESTED_TO"
                                          ]; ?></td>
                                          <td class="text-left"><?php echo $row[
                                              "REQUESTED_BY"
                                          ]; ?></td> 
                                         <?php } ?> 
                                          <?php if ($row["DOC_PATH"] != "") { ?>
                                            <td class="w-5 text-center"><a class ="mediadoc" data-id="<?php echo $row[
                                                "DOC_PATH"
                                            ]; ?>" href="#" title="Document"><i class="fa fa-file file" aria-hidden="true"></i></a></td>
                                                <?php } else { ?>
                                          <td></td>
                                        <?php } ?>
                                         <td class="text-left"><?php echo $note; ?></td>
                                         <td class="text-left" title="<?php echo $title; ?>"><?php echo $status; ?></td>
                                         <td class="text-left w-15"><?php echo $row[
                                             "REQUST_ID"
                                         ]; ?></td>
                                         <td class="text-left w-10"><?php echo $row[
                                             "FIRM_REQUST_DT"
                                         ]; ?></td>
                                         

                                               
                                       </tr>

							          <?php
            }
        } else {
             ?>
							          <tr ><td colspan="9" >No Records found!, Please modify your filters</td></tr>
							        <?php
        } ?>
								        </tbody>
								    </table>
        
    </div>
    <!-- </div>
                       </div>
                      </div>
                    </div>
                  </div> -->
                </div>

</div>
<!--main content ends here -------------------------------------------------->
<?php include "footer.php"; ?>
<div class="control-sidebar-bg"></div>
</div>
</body>

<script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.2.10/semantic.min.js"></script>

<script>
/* new code by sujata */
$('#status-multiselect').multiselect({
         includeSelectAllOption: true ,
         enableCaseInsensitiveFiltering: true,
         nonSelectedText: 'None Selected',
         selectAllValue: 'multiselect-all',
         maxHeight: '200',
         buttonWidth: '150',
          numberDisplayed:1,
         selectAll:true
        });
$('#type-multiselect').multiselect({
         includeSelectAllOption: true ,
         enableCaseInsensitiveFiltering: true,
         nonSelectedText: 'None Selected',
         selectAllValue: 'multiselect-all',
         maxHeight: '200',
         buttonWidth: '150',
          numberDisplayed:1,
         selectAll:true
        });
$('#company-multiselect').multiselect({
         includeSelectAllOption: true ,
         enableCaseInsensitiveFiltering: true,
         nonSelectedText: 'None Selected',
         selectAllValue: 'multiselect-all',
         maxHeight: '200',
         buttonWidth: '150',
          numberDisplayed:1,
         selectAll:true
        });

$('#firm-multiselect').multiselect({
  
         includeSelectAllOption: true ,
         enableCaseInsensitiveFiltering: true,
         nonSelectedText: 'None Selected',
         selectAllValue: 'multiselect-all',
         maxHeight: '200',
         buttonWidth: '150',
          numberDisplayed:1,
         selectAll:true
        });
$(document).ready(function() {

  '<?php if ($_SESSION["userType"] == 1) { ?>'
    var noc=11;
  '<?php } else { ?>'
    var noc=9;
  '<?php } ?>'
  
  $.fn.dataTable.moment( 'MM-DD-YYYY' );
$('#dataTable').DataTable( {
"pagingType": "full_numbers",
"iDisplayLength": 560,
"pageLength": 100,
//"bPaginate": false,
"fixedHeader": true,
"scrollY": 550,
"lengthChange": false,
"searching": true,
"ordering": true,
"info": true,
"autoWidth": false,

order: [[noc, 'desc']],
dom: 'Bfrtip',
        buttons: [{
              extend:    'excelHtml5',
                text:      '<i class="fa fa-file-excel-o" title="Export to excel"></i>',
                titleAttr: 'Export to excel'
        }]
} );
 $('.sorting_desc').removeClass('sorting').addClass('');
} );
$(document).ready(function(){
  '<?php if (mysqli_num_rows($result)) {?>'
     $( "#dataTable_wrapper1" ).html( "<p><b> (Displaying Latest  100 Entries...)</b>" );
     '<?php } ?>'
     
var clicker=0;
    $("#company-multiselect").change(function(e){
     
      firstFilter = $("#firstFilter").val();
      if(firstFilter == ""){
        $("#firstFilter").val("Company");
      }
   
        clicker++;
        if(($("#firstFilter").val() =="Company")){
          console.log("in company if");
          var getFirms = $("#company-multiselect").val();
            var action = "firms";
                  
                    $.ajax({
                      url:"dropquerylog.php",
                      type:"POST",
                      
                      data:{getFirms:getFirms,action:action},
                    success:function(data)
                    {
                      console.log('firm loaded');
                        $('#firm-multiselect').html(data);
                        $('#firm-multiselect').multiselect('rebuild');

                    }
                    });
        }else{
          console.log('firm not loaded');
          console.log("in company else");
        }
     });    
     $("#firm-multiselect").change(function(e){
   

        clicker++;
        firstFilter = $("#firstFilter").val();
          if(firstFilter == ""){
            $("#firstFilter").val("Firm");
          }
         // alert($("#firstFilter").val());
          if( ($("#firstFilter").val() =="Firm")){
            console.log("in firm if");
            var firms = $("#firm-multiselect").val();
            var action = "getcompanies";
                   // $('.matchDate').attr('attrcode','1');
                    $.ajax({
                      url:"dropquerylog.php",
                      type:"POST",
                      // dataType: 'json',
                      data:{firms:firms,action:action},
                    success:function(data)
                    {
                      console.log('Company loaded');
                        $('#company-multiselect').html(data);
                        $('#company-multiselect').multiselect('rebuild');

                    }
                    });
           }else{
            console.log("in firm else");
            console.log('no Company loaded');
           }
     }); 
     


     $("#resetbutton").click(function(e){
      //$("#dwnldIcon").css("display", "none");

      var userType  = $(this).data("usertype");
      var role      = $(this).data('role');
      var firmCode  = $(this).data('firmcode');
      var clientCode= $(this).data('clientcode');
//alert(userType+'=='+firmCode+'=='+clientCode+'=='+role);
      resetDocReqStatus();
     
      if(userType == 1){

        $("#firstFilter").val('');
        resetCompanies(firmCode);
        resetFirms(clientCode);
       
      }else if(userType == 2){

        $("#firstFilter").val('Firm');
        resetCompanies(firmCode);

      }else if(userType == 3){

        $("#firstFilter").val('Company');
        resetFirms(clientCode);

      }else if(userType == 4){
        $("#firstFilter").val('Client');
      }
     
     });

     // $("#downloadbtn").click(function(e){ 
     //        var firms     = $("#firm-multiselect").val();
     //        var companies = $("#company-multiselect").val();
     //        var status    = $("#status-multiselect").val(); 
     //        var duration    = $("#duration").val(); 
     //        var action = "downloadTable";
     //        $.ajax({
     //            url:"manageDocumentDownload.php",
     //            type:"POST",
     //            // dataType: 'json',
     //            data:{firms:firms, companies:companies, status:status, action:action,duration:duration},
     //          success:function(data)
     //          {
                
     //            console.log(data);
     //          }
     //        });  
     //  });


$("#viewbutton").click(function(e){
      
            var firms     = $("#firm-multiselect").val();
            var companies = $("#company-multiselect").val();
            var status    = $("#status-multiselect").val();
            var duration  = $("#duration").val();//$(this).data('duration');
            var type      = $("#type-multiselect").val();
            if(status ==''){
              swal("Error!", "Please Select DocumentStatus.", "error");
              return false;
            } 
            
            $('#loader').show();
            var action = "reloadTable";    
                       
            $.ajax({
              url:"managemediadocAjax.php",
              type:"POST",
             
              data:{firms:firms, companies:companies, status:status,duration:duration, action:action,type:type},
            success:function(data)
            {//console.log(data);
              myArray = data.split("#");
              if(myArray[0] == "bigNumber"){
                swal("Error!", "Your search returned "+myArray[1]+" records.  Please modify your search and try again.", "error");
              }else{              
                  $('.table-responsive').html('');
                  $('.table-responsive').html(data);
                 

              } $( "#dataTable_wrapper1" ).html( "" );
              $('#loader').hide();
             }
            });  
     });
} );
function resetCompanies(firmCode){
  var action='resetCompanies';
  $.ajax({
        url:"dropquerylog.php",
        type:"POST",
        // dataType: 'json',
        data:{action:action,firmCode:firmCode},
      success:function(data)
      {
          $('#company-multiselect').html(data);
          $('#company-multiselect').multiselect('rebuild');
      }
      });
}
function resetFirms(clientCode){
  action = 'resetFirms';
  $.ajax({
            url:"dropquerylog.php",
            type:"POST",
            // dataType: 'json',
            data:{action:action,clientCode:clientCode},
          success:function(data)
          {
              $('#firm-multiselect').html(data);
              $('#firm-multiselect').multiselect('rebuild');

          }
          });
}
function resetDocReqStatus(){
 $("#status-multiselect").multiselect("clearSelection");
 $("#status-multiselect").multiselect( 'refresh' );
 $("#type-multiselect").multiselect("clearSelection");
 $("#type-multiselect").multiselect( 'refresh' );
  
}
/* for document view doc*/
  $('.mediadoc').click(function() {
    var link   =$(this).data('id');
    var extension=link.split('.').pop().toLowerCase();
        $.ajax({
        url: 'sessionstore.php',
        type: 'post',
        data: {link: link},
        success: function(response){ 
        window.open("viewdoc", '_blank');
        }
        });

   
  })
</script>

</html>