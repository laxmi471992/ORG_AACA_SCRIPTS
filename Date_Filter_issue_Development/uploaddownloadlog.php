<?php 
session_start();
error_reporting(0);
include_once('config.php');
//include_once('mydownloadconn.php');

if(!isset($_SESSION['email']))
{
  header('Location: logout.php');
  exit();
}


  $UserCodequery="select distinct SenderType from MY_UPLOADS order by UsertType";
  $resUserCodequery=mysqli_query($conn,$UserCodequery);
 
    if($_SESSION['userType']==1 ||  $_SESSION['newuserType']==1){
      $folder="AACA";
    }


/*To show share icon to selected usertype*/
if($_SESSION['userType']==1 && $_SESSION['newuserType']==''){
  $usertype=$_SESSION['userType'];
}else if($_SESSION['userType']==1 && $_SESSION['newuserType']==1){
   $usertype=$_SESSION['newuserType'];
}

$submenuQuery="SELECT DISTINCT SUB_MENU_ITEM FROM USER_CONTROL_FILE ".$roleque."";//echo $submenuQuery;
$ressubmenushare=mysqli_query($conn,$submenuQuery);


/* to show drop down list for sharing from my uploads to my downloads*/
if($_SESSION['userType']==1){
  $doenloadslist="SELECT FILENAME,DISPLAYNAME from  mydownload_dropdown where BIT_DELETED_FLAG=0 order by DISPLAYNAME asc";
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

  <link rel="stylesheet" href="css/PSPanel.css">
  <link rel="stylesheet" href="css/PSdaterangepicker.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.10.13/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="css/Multiselect.css"> 

  <script src="js/PSjquery.min.js"></script>
<!--Data Table-->

  <script src="js/PSnnect.min.js"></script>
  <script src="js/PSslimscroll.js"></script>
  <script src="js/PSnnectPanel.js"></script>
  <script src="js/PSjquery.dataTables.min.js"></script>
  <script src="js/PSnnect.dataTables.min.js"></script>
  <script src="js/sweetalert.min.js"></script>
  <script type="text/javascript" src="js/MultiSelectJs.js"></script>

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
    .ml-25{margin-left: 25px;}

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
       .dataTables_wrapper .dataTables_paginate .paginate_button

{padding: 0em 0em;margin-left: 0px;}

.pagination{margin: 0px 0;}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover

{border: 0px solid #111;

 background-color: #58585800;}

#TblPagination_first,#TblPagination_last,#TblPagination_length{display:none;}

.multiselect-native-select .btn-group {display: block;width: 100% !important;}
   .multiselect-native-select .btn-group .btn {padding: 8px 12px;}
   div#showsize {position: absolute;right: 200px;}

.nav-tabs-custom .nav li {
    background: #002e5b;
  
    color:  #fff;
    /* border: 1px solid #ddd; */
    border-radius: 5px;
    /* margin-left: -1px; */
    position: relative;
    font-weight: bold;
    left: 1px;
}

.nav-tabs-custom > .nav-tabs > li > a {
    color: #fff;
    border-radius: 0;
}

.nav-tabs-custom > .nav-tabs > li.active {
    border-top-color: #002e5b;
}
.w-5 {width: 2% !important;}
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
                System Upload Log
            </li>        
      </ol>
    </section>
    
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-body">                       
              <div class="col-xs-12">
                <div class="clearfix">                               
                  <div class="tab-content">
                    <div class="tab-pane active" id="MyDownFile-multiselectloads">
                      <div class="col-sm-12">
                        <!-- <div class="row">
                          <h3 class="profile-label">System Upload Log/<a href="downloadlog">System Download Log</a></h3>
                        </div> -->
                        <div class="nav-tabs-custom clearfix ">
                               <ul class="nav nav-tabs pull-left col-md-8">
                                    <li class="active"><a href="#" >System Upload Log</a></li>
                                    <li ><a href="downloadlog"  class="Guide02">System Download Log</a></li>
                                    
                                 </ul>
                
                           </div>
                      </div>
                      
                      <div class="col-sm-6 col-md-2">
                        <div class="form-group multiselect-option clearfix">
                          <label>User Type:</label><br>
                          <select class="form-control" name="UserTypecode"  id="UserTypecode" >
                         <option value="">--Select-- </option>
                         <?php 
                              while($rowresUserCodequery=mysqli_fetch_assoc($resUserCodequery)){ 
                                // echo "<pre>";print_r($rowresUserCodequery);
                                ?>
                                  <option value="<?php echo $rowresUserCodequery['SenderType']; ?>"> <?php echo ($rowresUserCodequery['SenderType']); ?> </option>
                            <?php }?>
                          </select>             
                        </div>
                      </div>
                      <div class="col-sm-6 col-md-2">
                        <div class="form-group multiselect-option clearfix">
                          <label>User Code:</label><br>
                          <select class="form-control" name="company[]" multiple="multiple" id="company-multiselect" >
                          </select>
                           
                        </div>
                      </div>
                      <div class="col-sm-12 col-md-2">
                        <div class="form-group multiselect-option clearfix" >
                          <label>Document Type: </label><br>
                            <select class="form-control file" id="File-multiselect" name="File[]"  multiple="multiple"  >
                            
                              
                          </select><br>
                          <span class="error" id="fileError" style="display:none;">Please Select Document Type.</span>
                        </div>
                      </div>
                      <div class="col-sm-12 col-md-2">
                        <div class="form-group clearfix">
                          <label>Time Period:</label>
                          <select class="form-control" name="noofdays" id="noofdays">
                            <option  value="">All</option>
                            <option value="<7">Less Than 7 Days</option>
                            <option value="<15">Less Than 15 Days</option>
                            <option value="30">30 Days</option>
                            <option value=">30">Greater Than 30 Days</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-sm-12 col-md-2" style="position: relative;top: 22px;">
                       
                        <div class="form-group clearfix">
                            <label class="radio">
                                <input type="radio" name="choosentype" class="" id="choosentype" value="0">
                              <span class="checkround" ></span>
                            </label><label class="ml-25">Archived Files<br/><small>(Older than 90 days)</small><label>
                        </div>
                        
                      </div>
                      <div class="col-sm-12 col-md-2" style="position: relative;top: 20px;">
                        <button class="btn btn-lg btn-primary" type="button" onclick="showTable();" style="padding: 8px 35px;">Apply Filters</button>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="" id="showsize" style="display:none;">
                  <div class="form-group clearfix">
                    <label>Total selected size(KB): 
                      <input type="text" name="FileSize" placeholder="Size(KB)" class="form-control" value="" id="sizecheck" style="color: #286090; font-weight: bold;"/>
                    </label>
                  </div>
              </div>
              <div class="col-xs-12">
                  <div class="table-responsive" id="tabledownload">
             
               <span class='error' id='selrecordError'></span>
                <div id='newDownloadZip' style='display:none;'><form method='POST' id='zipdown'><button type='submit' name='zip' id='newZip' class='btn btn-primary btn-xs-100 mrg0 mrg20R' >Zip Download</button></form></div>

                    <!-- code end sujata -->
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
           <!--   <div class="col-sm-3 p0">
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
$("input[name='choosentype']").on("click",function (e) {
        var inp=$(this);
    if (inp.is(".theone")) {
        inp.prop("checked",false).removeClass("theone");
        $('#choosentype').val(0);
    } else {
        $("input:radio[name='"+inp.prop("name")+"'].theone").removeClass("theone");
        inp.addClass("theone");
        $('#choosentype').val(1);

    }

});
  $('#File-multiselect').multiselect({
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

  $(document).ready(function(){
   $('#UserTypecode').on('change',function(){
    var UserTypecode=$('#UserTypecode').val();
    $.ajax({
      type: 'POST',
      url: 'uploaddownloadlogdropquery.php',
      
      data: { UserTypecode:UserTypecode} ,
      success: function (data) {
       $("#company-multiselect").html(data);
       $('#company-multiselect').multiselect('rebuild');
     },
     error: function ()
     { alert('there is some error to get Rate'); }
   });
   })
   $('#company-multiselect').on('change',function(){
    var companycode=$('#company-multiselect').val();
    var UserTypecode2=$('#UserTypecode').val();
    $.ajax({
      type: 'POST',
      url: 'uploaddownloadlogdropquery.php',
      
      data: { companycode:companycode,UserTypecode2:UserTypecode2} ,
      success: function (data) {
       $("#File-multiselect").html(data);
       $('#File-multiselect').multiselect('rebuild');
     },
     error: function ()
     { alert('there is some error to get Rate'); }
   });
   })
       htm ="<p><b>(Displaying Latest 100 Entries...)</b>";
       $("#dataTable_wrapper1").html(htm);
  });
  
$(document).ready(function(){

    $('#File-multiselect').change(function(){
      $("#fileError").hide();
    })

       
});

$(document).ready(function(){

  var radval='';
  var type='';
  var noofdays='';
  var folder='';
  var inputValue = '0';


    $.ajax({
    url:"uploaddownloadlogAjax.php",
    type:"POST",
    data:{radval:radval,type:type,folder:folder,noofdays:noofdays},
    success:function(data)
    {
      
      $("#tabledownload").html(data);
  
    }
    })
  });
function showTable(){
  var radval=3;
  var UserTypecode=$('#UserTypecode').val();
  var companymultiselect=$('#company-multiselect').val();
  var type=$('#File-multiselect').val();
  var noofdays=$('#noofdays').val();
  var inputValue = $('#choosentype').val();
  // if(type ==''){
  //   $("#fileError").show();
  //   return false;
  // }
 // var folder='<?php //echo $folder;?>';

    $.ajax({
    url:"uploaddownloadlogAjax.php",
    type:"POST",
    data:{radval:radval,UserTypecode:UserTypecode,companymultiselect:companymultiselect,type:type,noofdays:noofdays,inputValue:inputValue},
    success:function(data)
    {
      $("#tabledownload").html('');
      $("#tabledownload").html(data);
  
    }
    })
}


 $(document).ready(function(){
 $('#share').on('click', function() {
      var radval=3;
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




function getSelectedRows() {
  getsize = [];
    $('.select-row:checked').each(function () {
    getsize.push($(this).data("id"));
  });
  return getsize;
}


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
