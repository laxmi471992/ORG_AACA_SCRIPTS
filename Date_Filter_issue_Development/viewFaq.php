  <?php 
session_start();
error_reporting(0);
require_once("../config.php");
if(!isset($_SESSION['email']))
{
  header('Location: ../logout');
  exit();
  }


$getId=base64_decode($_GET['id']);
if($getId!=''){
 $query = "select FaqId,QusTitle,Question,Answer,AddURL,UploadFile,createdAt,IsApprove,UserType,createdBy,updatedBy from MANAGE_FAQ where IsDelete=0 and ArchiveStatus=0 and FaqId=".$getId."
UNION ALL
select FaqId,QusTitle,Question,Answer,AddURL,UploadFile,createdAt,IsApprove,UserType,createdBy,updatedBy from MANAGE_FAQ where IsDelete=0 and ArchiveStatus=0 and FaqId!=".$getId." ";
$result = mysqli_query($conn,$query);
}else{
 $query = "select FaqId,QusTitle,Question,Answer,AddURL,UploadFile,createdAt,IsApprove,UserType,createdBy,updatedBy from MANAGE_FAQ where IsDelete=0 and ArchiveStatus=0 order by createdAt desc ";
$result = mysqli_query($conn,$query);
}

?>
<!DOCTYPE html>

<html >
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Pipeway | Administration</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <meta http-equiv="X-UA-Compatible" content="IE=11"/>
  <meta http-equiv="X-UA-Compatible" content="IE=10"/>
  <meta http-equiv="X-UA-Compatible" content="IE=9"/>
  <meta http-equiv="X-UA-Compatible" content="IE=8"/>
  <link rel="stylesheet" href="../css/PSnnect.min.css">
  <link rel="stylesheet" href="../css/sweetalert.css">
<!--<link rel="stylesheet" href="../css/PSdataTables.min.css"> -->
  <link rel="stylesheet" href="../css/PSPanel.css">
  <link rel="stylesheet" href="../css/PSdaterangepicker.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.10.13/css/jquery.dataTables.min.css">
<!--Import jQuery before export.js-->
  <script src="../js/PSjquery.min.js"></script>
<!--Data Table-->
  <script type="text/javascript"  src=" https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
  <script src="../js/PSnnect.min.js"></script>
  <script src="../js/PSslimscroll.js"></script>
  <script src="../js/PSnnectPanel.js"></script>
  <script src="../js/PSjquery.dataTables.min.js"></script>
  <script src="../js/PSnnect.dataTables.min.js"></script>

  <script src="../js/autologout1.js"></script>
  <script src="../js/sweetalert.min.js"></script>

  <link rel="apple-touch-icon-precomposed" sizes="114x114" href="../img/fevicon.ico">
  <link rel="apple-touch-icon-precomposed" sizes="72x72" href="../img/fevicon.ico">
  <link rel="apple-touch-icon-precomposed" href="../img/fevicon.ico">
  <link rel="shortcut icon" href="../img/fevicon.ico">
<style>
 .dataTables_wrapper .dataTables_paginate .paginate_button
  {padding: 0em 0em;margin-left: 0px;}
  .pagination{margin: 0px 0;}
  .dataTables_wrapper .dataTables_paginate .paginate_button:hover
  {border: 0px solid #111;
   background-color: #58585800;}
  #TblPagination_first,#TblPagination_last,#TblPagination_length{display:none;}
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
 
.dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody{overflow:overlay!important;}

.p0{padding:0px!important;}

.box-header {
    padding: 10px 11px;
}

div::-webkit-scrollbar {width: 15px;height:15px;}
div::-webkit-scrollbar-track {-webkit-box-shadow: inset 0 0 3px rgba(0,0,0,0.3);}
div::-webkit-scrollbar-thumb {background-color: #28648a;outline: 1px solid slategrey;position:relative;right:10px;}
.dataTables_filter {float: right!important;margin-left: 0px!important;}
.p0{padding:0px!important;}
.dataTables_wrapper .dataTables_scroll {overflow-x: auto;height: 375px;overflow-y: hidden;}
table.dataTable {border-collapse: collapse!important;width: 100%!important;}
.dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody {overflow: auto!important;height: 330px!important;position: relative!important;}
div::-webkit-scrollbar-thumb {background-color: #002e5b;}
.dataTables_scrollBody{max-height: 335px !important;top: 0px;}
table.dataTable{border-collapse: collapse!important;}
.dataTables_wrapper {padding: 0px !important;}
.dataTables_info {padding: 8px 10px !important;}
.table.dataTable.nowrap th, table.dataTable.nowrap td {white-space: nowrap !important;}

 #loader {
    position: fixed;
    width: 100%;
    height: 100vh;
    background: url('../img/loader.gif') no-repeat center center;
    z-index: 999999;
     }
    /*  .btn-hover-green:hover{
        color:#333333 !important;
        background: #002e5b !important;
     } */
.lft-login .btn, .btn:hover {

    background: #002e5b!important;
    color: #ffffff !important;
    font-size: 12px   
 opacity: 9;
}



</style>
</head>

<body class="hold-transition skin-yellow sidebar-mini fixed">
<div id="loader" style="display: none;"></div>

<div class="wrapper">
 <?php include('../topnav.php');?>
  
  <aside class="main-sidebar">
 <?php include('../leftpane.php');?>
  </aside>
  
  <div class="content-wrapper">
    <section class="content-header">
  <!--   <h1>
        View FAQ
    </h1> -->
    <ol class="breadcrumb">
        <li><a href="../inventory_layout"><i class="fa fa-home"></i> Home</a></li>
         <li><i class="fa fa-user-circle"></i> Administration</li>
        <li><i class="fa fa-question-circle"></i> Manage FAQ</li>
        <li class="active"><i class="fa fa-plus"></i> View FAQ</li>
    </ol>
</section>
    
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
              
          <div class="box">
                    <div class="box-header with-border">
                       <!--  <h3 class="box-title pull-left">FAQ</h3> -->
                        <div class="pull-right clearfix">
                         <button class="btn btn-lg btn-primary" type="button" onclick="location.href='addFaq';">Add FAQ</button>
                          <button class="btn btn-lg btn-primary" type="button" onclick="location.href='archiveFaq';">Archived FAQ</button>
                      </div>
                    </div>
                    <div class="box-body">
                        <div class="col-xs-12 p0">
                            <div class="table-responsive" id="tables-wrapper">
                                <table id="TblPagination" class="display nowrap table table-bordered table-striped table-hover table-condensed">
                                    <thead>
                                        <tr class="grey-bg">
                                            <th class="text-center sr-no hidden-dropdown">#</th>
                                            <th class="text-center">Title</th>
                                            <th class="text-center">Question</th>
                                            <th class="text-center">Assignee</th>
                                            <th class="text-center">Attachment</th>
                                            <th class="text-center">Status Date</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="counter-reset" id="tbodyLeaveDetails">
                                           
                                                <?php  if(mysqli_num_rows($result)){
                                                      $counter = 0;
                                                    while($row=mysqli_fetch_assoc($result)){ 
                                                       $counter++;
                                                       $linkUrl=$row['AddURL'];
                                                   if (!preg_match("~^(?:f|ht)tps?://~i", $linkUrl)) {
                                                        $linkUrl = "http://" . $linkUrl;
                                                    }
                                                      $filePath= $row['UploadFile'];
                                                      if($row['IsApprove']==0){
                                                        $class="pending";
                                                        $value="Pending";
                                                        // $inactive="";
                                                      }
                                                      else if($row['IsApprove']==1){
                                                        $class="approved";
                                                        $value="Approved";
                                                        // $inactive="inactive";
                                                      }
                                                      else{
                                                        $class="rejected";
                                                        $value="Rejected";
                                                       //  $inactive="";
                                                      }

                                                      $new_date   = date("m-d-Y",strtotime($row['createdAt']));
                                                      $FAQid      =$row['FaqId'];//echo $row['AACA'];exit;
                                                      $FAQids      =base64_encode($row['FaqId']);
                                                      $userType   =explode(',',$row['UserType']);
                                                      $role       =explode(',',$row['Role']);
                                                      $type       =array('1'=>"AACA",'2'=>'Firm','3'=>"Client",'4'=>"Agency");
                                                      $resultarr  =array_intersect_key($type, array_flip($userType));
                                                      $typeuser   = implode(', ', $resultarr);
                                                      // if($row['AACA']==1){
                                                      //   $assignee='AACA';
                                                      // }
                                                      // if($row['Firm']==2){
                                                      //   $assignee='Firm';
                                                      // }
                                                      //  if($row['Client']==3){
                                                      //   $assignee='Client';
                                                      // }
                                                      // if($row['Agency']==4){
                                                      //   $assignee='Agency';
                                                      // }
                                                      // if($row['AACA']==1 && $row['Firm']==2){
                                                      //   $assignee='AACA and Firm';
                                                      // }
                                                      // if($row['AACA']==1 && $row['Client']==3){
                                                      //   $assignee='AACA and Client';
                                                      // }
                                                      // if($row['AACA']==1 && $row['Agency']==4){
                                                      //   $assignee='AACA and Agency';
                                                      // }
                                                      // if($row['Firm']==2 && $row['Client']==3){
                                                      //   $assignee='Firm and Client';
                                                      // }
                                                      // if($row['Firm']==2 && $row['Agency']==4){
                                                      //   $assignee='Firm and Agency';
                                                      // }
                                                      // if($row['Agency']==4 && $row['Client']==3){
                                                      //   $assignee='Agency and Client';
                                                      // }
                                                      // if($row['AACA']==1 && $row['Firm']==2 && $row['Client']==3){
                                                      //   $assignee='AACA,Firm and Client';
                                                      // }
                                                      // if($row['AACA']==1 && $row['Firm']==2 && $row['Agency']==4){
                                                      //   $assignee='AACA,Firm and Agency';
                                                      // }
                                                      // if($row['AACA']==1 && $row['Client']==3 && $row['Agency']==4){
                                                      //   $assignee='AACA,Client and Agency';
                                                      // }
                                                      // if($row['Agency']==4 && $row['Firm']==2 && $row['Client']==3){
                                                      //   $assignee='Firm,Client and Agency';
                                                      // }
                                                      // if($row['Agency']==4 && $row['Firm']==2 && $row['Client']==3 && $row['AACA']==1){
                                                      //   $assignee='AACA,Firm,Client and Agency';
                                                      // }

                                                       if(($row['createdBy'] == $_SESSION['email'])){
                                                        $inactive="inactive";
                                                      }
                                                      else if(($row['IsApprove']==1)){
                                                        $inactive="inactive";
                                                      }else if($_SESSION['role']==4 ){
                                                        $inactive="";
                                                      }else if($_SESSION['role']==6){
                                                       $inactive="";
                                                      }else if($_SESSION['role']==5){
                                                        $inactive="inactive";
                                                      }else if($_SESSION['role']==7){
                                                     $inactive="inactive";
                                                      }
                                                      else {
                                                       $inactive="";
                                                      }
                                                      ?>
                                                <tr>
                                                <td class="text-center"><?php echo  $counter;?></td>
                                                <td class="text-left"><?php echo strlen($row['QusTitle']) > 40 ? substr($row['QusTitle'],0,40)."..." : $row['QusTitle'];?></td>
                                               <td class="text-left" ><?php echo strlen($row['Question']) > 40 ? htmlspecialchars_decode(substr($row['Question'],0,40))."..." : htmlspecialchars_decode($row['Question']);?></td>
                                                <td class="text-left"><?php echo $typeuser; ?></td> 
                                                <td class="text-left">
                                                   <?php if($row['AddURL']!=''){?>
                                                    <a class="questionlink removeatthref" href="<?php echo $linkUrl;?>" target="_blank" title="Link"><i class="fa fa-link" aria-hidden="true"></i></a>
                                                    <?php } ?>
                                                  <?php 

                                                  if($row['UploadFile']!='' && file_exists("/var/www/html/bi/dist/Mako/uploads_1/".$filePath)){?>
                                                  <a class ="viewfaqdoc questiondoc" data-id="<?php echo $filePath;?>" href="#" title="Document"><i class="fa fa-file file" aria-hidden="true"></i></a>
                                                <?php } ?>
                                                </td>
                                                <td class="text-center"><?php echo $new_date;?></td>
                                                <td class="text-center">
                                                    <span class="<?php echo $class;?>" ><?php echo $value;?></span>
                                                   
                                                </td>
                                                <td class="text-center ">
                                                   <a href="#" data-toggle="modal" data-target="#ApproveFaqModal" data-id='<?php echo $FAQid;?>' createdBy = "<?php echo $row['createdBy'];  ?>" statusCheck = "<?php echo $value;?>"  class="userinfo <?php echo  $inactive;?>" title="Status"><i class="fa fa-tag" aria-hidden="true"></i></a>
                                                   
                                                   <a href="" data-toggle="modal" data-target="#ViewFaqModal" class="viewFaq view-link " title="View"  data-id='<?php echo $FAQid;?>'><i class="fa fa-eye"></i></a>

                                                   <a href="editFaq?GetID=<?php echo $FAQids;?>" class="edit-link" title="Edit" ><i class="fa fa-pencil-square-o" ></i></a>

                                                   <?php if($row['IsApprove']!=1){?>
                                                     <span data-id="<?php echo $FAQid ?>" id="contents3">
                                                    <a data-toggle="modal" class="delete-link" title="Delete"   href="#"> <i class="fa fa-times"></i> </a>
                                                   
                                                  <?php } else{ ?>
                                                    <span data-id="<?php echo $FAQid ?>" id="contents4">
                                                  <a data-toggle="modal" class="delete-link" title="Archive"   href="#" style="color:#3c8dbc;">  <i class="fa fa-archive" aria-hidden="true"></i></a>
                                                  <?php } ?>
                                                </td>
                                                 </tr>
                                              <?php } }?>
                                           
                                          
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div> 
            </div>
        </div>
    </section>
  </div>
  
   <?php include('../footer.php');?>
  
  <div class="control-sidebar-bg"></div>
</div>
<!--Status update modal-->
<div class="modal fade" id="ApproveFaqModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title text-center" id="lineModalLabel">
                    Confirmation
                </h3>
            </div>
            <div id="modal-body"></div>
        </div>
    </div>
</div>
  <!--Status update modal end-->   
 <!--view modal-->
<!--  <div class="modal fade" id="ViewFaqModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content" style="width:933px; margin-left: -234px;">
            <div class="modal-header">
                <h3 class="modal-title text-center" id="lineModalLabel">
                   View FAQ 
                </h3>
            </div>
                <div id="View_faq_body">


              </div>
        </div>
    </div>
</div> -->
<div class="modal fade" id="ViewFaqModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog modal-lg">
  <div class="modal-content">
    <!--<div class="modal-header">
      <h3 class="modal-title text-center" id="lineModalLabel">View Client Guide Modal</h3>
    </div>-->
    <div class="modal-body clearfix" style="padding-top: 2px;">
            <div class="row">
                <div class="col-md-12" style="padding: 6px;">
               <div class="panel-group" id="accordion" style="margin-bottom: 0px;">
                    <div class="panel panel-default" id="View_faq_body"></div>
               </div>
               </div>
            </div>
    </div>
    <div class="modal-footer">
      <div class="btn-group btn-group-justified" role="group" aria-label="group button">
        <!--<div class="btn-group" role="group">
          <button type="button" class="btn btn-default" role="button" onclick="location.href='view-clientGuide.html';">Yes</button>
        </div>-->
        <div class="btn-group btn-delete hidden" role="group">
          <button type="button" id="delImage" class="btn btn-default btn-hover-red" data-dismiss="modal"  role="button">Delete</button>
        </div>
        <div class="btn-group" role="group">
          <button type="button" id="saveImage" class="btn btn-default close-hover" data-dismiss="modal" role="button">Close</button>
        </div>
      </div>
    </div>
  </div>
  </div>
</div>


<script>
  $(function () {
       $('#TblPagination').DataTable( {
        "pagingType": "full_numbers",
        "bPaginate": true,
        "fixedHeader": true,
        // "scrollY": 500,
        scrollY:        "100px",
       scrollX: true,
        'columnDefs':[{
          'targets':[7],
          'orderable':false,
        }]

       
        
    });
/* for document view of question doc*/
  $('.questiondoc').click(function() {
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
   
  /* for link  view of question doc*/ 
   $('.questionlink').click(function(e, params) {
     var link   =$(this).attr('href'); 
     var result =link.split('//');
     var folder =result[1].split('/');
     var extension=folder[1].split('.').pop().toLowerCase();

     if(folder[1]=='bi' && folder[2]=='dist'){
          window.open(link, '_blank');
         
     }else{
   var localParams = params || {};

        if (!localParams.send) {
            e.preventDefault();
        }
          swal({
              title: "Confirm Entry",
              text: "This link will take you outside Pipeway, do you want to continue?",
              type: "warning",
              showCancelButton: true,
              confirmButtonColor: "#6A9944",
              confirmButtonText: "Confirm",
              cancelButtonText: "Cancel",
              closeOnConfirm: true
            }, function(isConfirm){
              
         if (isConfirm) {
             window.open(link, '_blank');
                    $(e.currentTarget).trigger(e.type, { 'send': true });
                } else {}
    });
     }
   }) 
  });

  $(document).ready(function() {
   // $('.viewfaqdoc').click(function() {
   // var link   =$(this).data('id');
   // var url = "downloaddoc?file=" + encodeURIComponent(link) ;
   // window.location.href = url;
   // })  
         // var spinner = $('#loader');


  $('.userinfo').click(function(){

   var FAQid = $(this).data('id');
   var createdBy = $(this).attr('createdBy');
   var statusCheck = $(this).attr('statusCheck');

   $.ajax({
    url: 'addAjax.php',
    type: 'post',
    data: {statusCheck: statusCheck,createdBy: createdBy,FAQid: FAQid},
    success: function(response){ 
            // spinner.show();

      $('#modal-body').html(response);


    }
    });
  });
 });
    $(document).ready(function() {
  $(".viewFaq").click(function(){
     event.preventDefault();
   var FAQids = $(this).data('id');
   $.ajax({
    url: 'viewAjax.php',
    type: 'post',
    data: {FAQids: FAQids},
    success: function(response){
        // spinner.show();
      //console.log(response);
      $('#View_faq_body').html(response);

    }
    });
    });

/* start:Change log sweet alert  by puja kuamri on dt-061021 for delete faq*/

$(document).on('click', 'span#contents3', function(){

  var del = $(this).data('id');


swal({
  title: "Are you sure?",
  text: "You want to delete this record?",
  type: "warning",
  showCancelButton: true,
  confirmButtonClass: "btn-danger",
  confirmButtonText: "Yes, delete it!",
  closeOnConfirm: false
},
function(){
 if(del != ''){

  $.ajax({
        url: "../delete.php?FAQDel=" + del,
        type: 'GET',
        dataType: 'json', // added data type
        success: function(res) {
          var checkDeleteQuery = JSON.parse(res['status']);
            if (checkDeleteQuery == 1) {
              location.reload();

            }
        }
          
    });
  swal({title: "Deleted",
      text: "This record has been deleted successfully",
      timer: 4000,
      showConfirmButton: false,
      type: 'success'
    });

  }
});

})
/*End: Change log sweet alert  by puja kuamri on dt-061021 for delete faq*/

/*Start: Change log sweet alert  by puja kuamri on dt-061021 for archive faq*/

$(document).on('click', 'span#contents4', function(){

  var del = $(this).data('id');


swal({
  title: "Are you sure?",
  text: "You want to archive this record?",
  type: "warning",
  showCancelButton: true,
  confirmButtonClass: "btn-danger",
  confirmButtonText: "Yes, archive it!",
  closeOnConfirm: false
},
function(){
 if(del != ''){

  $.ajax({
        url: "../delete.php?FAQarch=" + del,
        type: 'GET',
        dataType: 'json', // added data type
        success: function(res) {
          var checkDeleteQuery = JSON.parse(res['status']);
            if (checkDeleteQuery == 1) {
              location.reload();

            }
        }
          
    });
  swal(
    {
        title: "Archived",
      text: "This record has been archived successfully",
      timer: 4000,
      showConfirmButton: false,
      type: 'success'
    });

  }
});

})
/* End:Change log sweet alert  by puja kuamri on dt-061021 for archive faq*/
});

</script>

<?php
/* Start:Change log sweet alert  by puja kuamri on dt-061021 for add faq*/
 if(isset($_SESSION['success']))
  {?>
   <script> swal({title: "",
                text: "Record added successfully",
                timer: 4000,
                showConfirmButton: false,
                type: 'success'
              });  </script>
 <?php }
 unset($_SESSION['success']);
 if(isset($_SESSION['success2']))
  {?>
   <script> swal({title: "",
                text: "Record updated successfully",
                timer: 4000,
                showConfirmButton: false,
                type: 'success'
              });  </script>
 <?php }
 unset($_SESSION['success2']);
 /*End: Change log sweet alert  by puja kuamri on dt-061021 faq*/
?>
</body>
    
</html>
