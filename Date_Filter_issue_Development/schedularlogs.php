<?php
error_reporting(1);
session_start();
include "../config.php";
if (!isset($_SESSION["email"])) {
    header("Location: ../logout");
    exit();
}


$query = "select report_name,code,start_time,end_time,status,createdAt,run_by from SCHEDULER_LOGS order by start_time,createdAt desc limit 100";
$result = mysqli_query($conn, $query);


?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Pipeway |Schedular Log</title>
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
  .w-20{width:30% !important;}
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
              <label>Report Status<span class="mandatory">*</span></label>
              <select class="form-control" name="status[]" multiple="multiple" id="status-multiselect" >
                <?php
              $status ="SELECT DISTINCT status FROM SCHEDULER_LOGS  ORDER BY (status);";
              $resstatus = mysqli_query($conn, $status); //exit;
             while($statusoutput = mysqli_fetch_array($resstatus)){
              if($statusoutput['status']==0){
                $value="No Data available";
              }else if($statusoutput['status']==1){
                $value="Success";
              }
                ?>
              
                <option value="<?php echo ($statusoutput['status']);?>"  selected><?php echo $value;?></option>
              <?php } ?>
                
              </select>
            </div>
          </div>
         
          <div class="col-sm-6 col-md-2">
            <div class="form-group multiselect-option clearfix">
              <label>Recepient</label>
              <input type="hidden" id="usertype" name="usertype" value="<?php echo $_SESSION[
                  "userType"
              ]; ?>"> 
              <select class="form-control" name="Recepient[]" multiple="multiple" id="Recepient-multiselect" >
              <?php
              $strQuery2 =
                  "SELECT DISTINCT code FROM SCHEDULER_LOGS  ORDER BY (code);";
              $results2 = mysqli_query($conn, $strQuery2); //exit;
              $code = mysqli_num_rows($results2);
              if ($code < 1) {
                  echo '<span class="error" id="firmError">No Recepient Available</span>';
              } elseif ($code == 1) {

                  $rows = mysqli_fetch_array($results2);
                  $code = $rows["code"];
                  $codevalue = $rows["code"];
                  if($code=='ALL'){
                    $code='AACA';
                  }else{
                    $code=$code;
                  }
                  $codeString = $code;
                  ?>
                      <option value="<?php echo $codevalue; ?>" selected ><?php echo $code; ?></option>
                      <?php
              } else {
                  while ($rows = mysqli_fetch_array($results2)) {
                      $code = $rows["code"];
                      if ($code != "") {
                         $codevalue = $rows["code"];
                        if($code=='ALL'){
                          $code='AACA';
                        }else{
                          $code=$code;
                        }
                          $codeString = $codeString . "," . $code; ?>
                        <option value="<?php echo $codevalue; ?>" selected ><?php echo $code; ?></option>
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
              <label>Run By</label>
              <select class="form-control" name="runby[]"   multiple="multiple" id="runby">
                <?php
                $strQuery1 ="SELECT DISTINCT run_by FROM SCHEDULER_LOGS where run_by!=''  ORDER BY (run_by);";
                $results1 = mysqli_query($conn, $strQuery1);
                while($rows = mysqli_fetch_array($results1)){
                    
                    $run_by = $rows["run_by"];
                  
                    ?> 
                    <option value="<?php echo $run_by; ?>"  selected><?php echo $run_by; ?></option>
                 <?php } ?>
               </select>
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
         <div class="table-responsive" id="tables-wrapper">
 
   
    
      <table class="display nowrap table table-bordered table-striped table-hover table-condensed demo" id="dataTable" width="100%" cellspacing="0" >
      <div id="dataTable_wrapper1" style="position: relative;top: 30px;"></div>
      <thead>
        <tr>
        
        
          <th class="text-center w-20">Report Name</th>
          <th class="text-center w-5">Recipients</th>
          <th class="text-center w-5">Start Time</th>
          <th class="text-center w-5">End Time</th> 
          <th class="text-center">Report Status</th>  
          <th class="text-center">Run By</th> 
          <th class="text-center w-10">Created On</th>  
          
        </tr>
        </thead>
        <tbody class="postList">
        <?php if (mysqli_num_rows($result)) {
          $counter=0;
            while ($row = mysqli_fetch_assoc($result)) {
               $counter++;  
               if($row["status"]==0){
                $status="No Data available";
               }else{
                  $status="Success";
               }if($row["code"]=='ALL'){
                $code="AACA";
               }else{
                   $code=$row["code"];
               }
               ?>
                                 <tr>
                             
                                 <td class="text-left w-20"><?php echo $row["report_name"]; ?></td>
                                 <td class="text-left w-5"><?php echo $code; ?></td>
                                 <td class="text-left w-5"><?php echo $row["start_time"]; ?></td>
                                 <td class="text-left w-5"><?php echo $row["end_time"]; ?></td> 
                                 <td class="text-left"><?php echo $status; ?></td>
                                  <td class="text-left"><?php echo $row["run_by"]; ?></td>
                                 <td class="text-left w-10" ><?php echo date('m-d-Y',strtotime($row['createdAt'])); ?></td>     
                               </tr>
                                 <?php } } else {?>
                                 	  <tr ><td colspan="9" >No Records found!, Please modify your filters</td></tr>
                      
                              <?php } ?>
                         
                        </tbody>
                    </table>
        
                    </div>
    
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

$('#runby').multiselect({
         includeSelectAllOption: true ,
         enableCaseInsensitiveFiltering: true,
         nonSelectedText: 'None Selected',
         selectAllValue: 'multiselect-all',
         maxHeight: '200',
         buttonWidth: '150',
          numberDisplayed:1,
         selectAll:true
        });

$('#Recepient-multiselect').multiselect({
  
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

order: [[6, 'desc'], [2, "desc" ]],
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
     $( "#dataTable_wrapper1" ).html( "<p><b> (Displaying Latest  100 Entries...)</b>" );
     
$("#viewbutton").click(function(e){
      
            var recepient     = $("#Recepient-multiselect").val();
            var runby        = $("#runby").val();
            var status       = $("#status-multiselect").val();
            var duration     = $("#duration").val();//$(this).data('duration');
            
           
            
            $('#loader').show();
            var action = "reloadTable";    
                       
            $.ajax({
              url:"schedularlogAjax.php",
              type:"POST",
             
              data:{recepient:recepient, runby:runby, status:status,duration:duration, action:action},
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

  $("#resetbutton1").on('click',function(){
      $("#status-multiselect").multiselect("clearSelection");
      $("#status-multiselect").multiselect( 'refresh' );
      $("#Recepient-multiselect").multiselect("clearSelection");
      $("#Recepient-multiselect").multiselect( 'refresh' );
      $("#duration option:selected").prop("selected", false);
      $("#runby option:selected").prop("selected", false)
    
     
     
     });
</script>

</html>