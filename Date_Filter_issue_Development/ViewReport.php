  <?php
  session_start();
  error_reporting(0);
  require_once("../config.php");
  if (!isset($_SESSION['email'])) {
    header('Location: ../logout');
    exit();
  }

  $query = "select * from Batch_Report where IsDelete=0 ORDER BY BatchId DESC";
  $result = mysqli_query($conn, $query);

  ?>
  <!DOCTYPE html>

  <html>

  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Pipeway | Schedule Batch Reports</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta http-equiv="X-UA-Compatible" content="IE=11" />
    <meta http-equiv="X-UA-Compatible" content="IE=10" />
    <meta http-equiv="X-UA-Compatible" content="IE=9" />
    <meta http-equiv="X-UA-Compatible" content="IE=8" />
    <link rel="stylesheet" href="../css/PSnnect.min.css">
    <!--   <link rel="stylesheet" href="../css/PSdataTables.min.css"> -->
    <link rel="stylesheet" href="../css/PSPanel.css">
    <link rel="stylesheet" href="../css/PSdaterangepicker.css">
    <link rel="stylesheet" href="../css/sweetalert.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.13/css/jquery.dataTables.min.css">
    <!--Import jQuery before export.js-->
    <script src="../js/PSjquery.min.js"></script>
    <!-- <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script> -->
    <!--Data Table-->
    <script type="text/javascript" src=" https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
    <script src="../js/PSnnect.min.js"></script>
    <script src="../js/PSslimscroll.js"></script>
    <script src="../js/PSnnectPanel.js"></script>

    <script src="../js/PSjquery.dataTables.min.js"></script>
    <script src="../js/PSnnect.dataTables.min.js"></script>
    <script src="../js/sweetalert.min.js"></script>

    <!-- <script src="../js/autologout1.js"></script> -->


    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="../img/fevicon.ico">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="../img/fevicon.ico">
    <link rel="apple-touch-icon-precomposed" href="../img/fevicon.ico">
    <link rel="shortcut icon" href="../img/fevicon.ico">
    <style>
      #loader {
        position: fixed;
        width: 100%;
        height: 100vh;
        background: url('../img/loader.gif') no-repeat center center;
        z-index: 1000;
      }

      /*.TFtable td {padding: 5px 10px 5px 5px;}*/
      [data-title] {
        font-size: 12px;
        position: relative;
      }

      [data-title]:after {
        content: attr(data-title);
        background-color: transparent !important;
        box-shadow: 0px 0px 0px #222222 !important;
      }

      [data-title]:hover::before {
        content: attr(data-title);
        position: absolute;
        /* bottom: -73px; */
        padding: 10px;
        background: #000;
        color: #fff;
        font-size: 10px;
        /* white-space: nowrap; */
        word-break: break-all;
        width: 116%;
        z-index: 1;
        left: -7px;
        border-radius: 2px;
        margin-top: 27px;
      }

      [data-title]:hover::after {
        content: '';
        position: absolute;
        bottom: -12px;
        left: 8px;
        border: 8px solid transparent;
        border-bottom: 8px solid #000;
      }

      .table-responsive {
        /* min-height: .01%; */
        overflow-x: hidden;
      }

      #table-wrapper {
        position: relative;
        top: 10px;
      }

      #table-wrapper table {
        width: 100%;
      }

      #table-wrapper table * {}

      #table-wrapper table thead th .text {
        position: absolute;
        top: -20px;
        z-index: 2;
        height: 20px;
        width: 35%;
        border: 1px solid red;
      }

      .sidebar-collapse table {
        width: 100% !important;
      }

      .dataTables_scrollHeadInner {
        width: 100% !important;
      }

      .dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody {
        overflow: overlay !important;
      }

      /*div.dataTables_filter label {
    font-weight: normal;
    white-space: nowrap;
}*/
      .approved {
        background: #3c763d;
        color: #ffffff;
        padding: 2px 14px;
        font-size: 11px;
      }

      .pending {
        background: #337ab7;
        color: #ffffff;
        padding: 2px 14px;
        font-size: 11px;
      }

      .rejected {
        background: #f44336;
        color: #ffffff;
        padding: 2px 14px;
        font-size: 11px;
      }

      /*.modal-header {
  background:#0e2330;
}*/
      .file {
        font-size: 17px;
        margin-left: 21px;
      }

      .fa-link {
        font-size: 17px;
        margin-left: 21px;
      }

      .filename {
        margin-left: 3px;
      }

      h4 {
        text-align: center;
      }

      .fa-tag {
        font-size: 17px;
      }

      .inactive {
        pointer-events: none;
        cursor: default;
        color: #3c8dbc69;
      }

      .table-fixed {}

      /*.dataTables_scrollHead,.dataTables_scrollBody{
    overflow: auto!important;
	
}*/

      /*  #TblPagination_filter{float: left;
    margin-left: -669px;}*/
      /* .dataTables_wrapper {
     width: 852px;
        margin: 0 auto;
    }*/
      div::-webkit-scrollbar {
        width: 15px;
        height: 15px;
      }

      div::-webkit-scrollbar-track {
        -webkit-box-shadow: inset 0 0 3px rgba(0, 0, 0, 0.3);
      }

      div::-webkit-scrollbar-thumb {
        background-color: #28648a;
        outline: 1px solid slategrey;
        position: relative;
        right: 10px;
      }

      .dataTables_filter {
        float: right !important;
        margin-left: 0px !important;
      }

      .demo {
        width: 100% !important;
        table-layout: fixed;
      }

      .p0 {
        padding: 0px !important;
      }

      .box-header {
        padding: 10px 25px !important;
      }
    </style>
  </head>

  <body class="hold-transition skin-yellow sidebar-mini fixed">
    <div id="loader" style="display: none;"></div>

    <div class="wrapper">
      <?php include('../topnav.php'); ?>

      <aside class="main-sidebar">
        <?php include('../leftpane.php'); ?>
      </aside>

      <div class="content-wrapper">
        <section class="content-header">
          <!--   <h1>
        View FAQ
    </h1> -->
          <ol class="breadcrumb">
            <li><a href="../inventory_layout"><i class="fa fa-home"></i> Home</a></li>
            <!-- <li><a href="../reports.php"><i class="fa fa-file-text-o"></i>Reports</a></li> -->
            <li><i class="fa fa-clock-o"></i> Schedule Batch Reports</li>
            <li class="active"> View Scheduled Report</li>
          </ol>
        </section>

        <section class="content">
          <div class="row">
            <div class="col-xs-12">

              <div class="box">
                <div class="box-header with-border">
                  <div class="pull-right clearfix">
                    <!-- <button class="btn btn-lg btn-primary" type="button" onclick="location.href='../Report/scheduler_01.php';">Add2</button> -->
                    <button class="btn btn-lg btn-primary" type="button" onclick="location.href='scheduler_01';">Schedule A New Report</button>
                  </div>
                </div>
                <div class="box-body">
                  <div class="col-xs-12 p0">
                    <div class="table-responsive" id="tables-wrapper">
                      <table id="TblPagination" class="display nowrap table table-bordered table-striped table-hover table-condensed demo">
                        <thead>
                          <tr class="grey-bg">
                            <th class="text-center sr-no hidden-dropdown" style="width: 20px;">#</th>
                            <th class="text-center">Report Name</th>

                            <th class="text-center">Created By</th>
                            <th class="text-center" style="width:80px;">Start Date</th>
                            <th class="text-center" style="width:80px;">End Date</th>
                            <th class="text-center" style="width:50px;">Status</th>
                            <th class="text-center" style="width: 100px;">Acitivity</th>
                            <!--  <th class="text-center" style="width: 100px;">Action</th> -->
                          </tr>
                        </thead>
                        <tbody class="counter-reset" id="tbodyLeaveDetails">

                          <?php
                          if (mysqli_num_rows($result)) {
                            $counter = 0;
                            while ($row = mysqli_fetch_assoc($result)) {
                              $counter++;
                              $StartDate = date('m-d-Y', strtotime($row['StartDate']));

                              $Time      = $row['Time'];
                              $Day       = $row['Day'];
                              $expday    = explode(',', $Day);
                              $ReportName = $row['ReportName'];
                              $UserType  = $row['UserType'];
                              $UserName  = $row['UserName'];
                              $Subject   = $row['Subject'];
                              $Type      = $row['Type'];
                              $CreatedBy = $row['CreatedBy'];
                              $id = $row['BatchId'];
                              if ($UserType == 2) {
                                $usertypename = 'Firm';
                              } else if ($UserType == 3) {
                                $usertypename = 'Client';
                              } else if ($UserType == 4) {
                                $usertypename = 'Agency';
                              }
                              if ($row['EndDate'] != '') {
                                $EndDate = date('m-d-Y', strtotime($row['EndDate']));
                              } else {
                                $EndDate = 'No end date';
                              }
                              if ($row['schedule_status'] == 1) {
                                $schedule_status = 'On Hold';
                              } else {
                                $schedule_status = 'Active';
                              }



                          ?>
                              <tr>
                                <td class="text-center"><?php echo  $counter; ?></td>
                                <td class="text-left"> <?php echo $ReportName; ?></td>

                                <td class="text-left"><?php echo $CreatedBy; ?></td>
                                <td class="text-center"><?php echo $StartDate; ?></td>
                                <td class="text-center"><?php echo $EndDate; ?></td>
                                <td class="text-center"><?php echo $schedule_status; ?></td>


                                <td class="text-center">
                                  <a class="view-link reportinfo" title="View" data-id='<?php echo $id; ?>' data-toggle="modal" data-target="#myModal">
                                    <i class="fa fa-eye"></i>
                                  </a>

                                  <?php if ($row['schedule_status'] == 0) { ?>

                                    <span id="contents1" data-id="<?php echo $id; ?>">
                                      <a data-toggle="modal" class="view-link" title="On Hold" href="#"><i class="fa fa-pause" aria-hidden="true"></i></a></span>
                                  <?php } else { ?>

                                    <span id="contents2" data-id="<?php echo $id; ?>">
                                      <a data-toggle="modal" class="view-link" title="Active" href="#"><i class="fa fa-play" aria-hidden="true"></i></a></span>
                                  <?php } ?>

                                  <a data-toggle="modal" class="view-link scheduleNow" title="Run Now" href="#" data-id='<?php echo $id; ?>' data-target="#scheduleModal">
                                    <i class="fa fa-bolt"></i>
                                  </a>

                                </td>

                              </tr>
                          <?php }
                          } ?>


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

      <!--  <footer class="main-footer">
        <strong>&copy; <script>document.write(new Date().getFullYear());</script>. AACANet powered by <a href="http://armguardsolutions.com/" target="_blank">ARMGuard</a> Solutions, LLC
  </footer> -->

      <?php include('../footer.php'); ?>

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
          <div id="modal-body">


          </div>
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
    <!-- Modal -->
    <div class="modal fade" id="myModal" role="dialog">
      <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <!--  <button type="button" class="close" data-dismiss="modal">&times;</button> -->
            <h4 class="modal-title text-center">Schedular Batch Report Details</h4>
          </div>
          <div class="modal-body">

          </div>

          <!-- <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div> -->

          <div class="modal-footer">
            <div class="btn-group btn-group-justified" role="group" aria-label="group button">

              <div class="btn-group" role="group">
                <button type="button" id="saveImage" class="btn btn-default close-hover" data-dismiss="modal" role="button">
                  Close
                </button>
              </div>
            </div>
          </div>

        </div>

      </div>
    </div>
    <!--end mpodal------------------------>

    <!-- Modal{schedule} -->

    <div class="modal fade" id="scheduleModal" role="dialog">
      <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <!--  <button type="button" class="close" data-dismiss="modal">&times;</button> -->
            <h4 class="modal-title text-center">Run Now</h4>
          </div>
          <div class="modal-body">

          </div>

          <!-- <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div> -->

          <div class="modal-footer" style="border-top-color:#ffffff26;">
            <div class="btn-group btn-group-justified box-shd" style="margin-left:0px;" role="group" aria-label="group button">

              <div class="btn-group box-shd" role="group">
                <button type="submit" id="submit_btn" class="btn btn-default">
                  Run
                </button>
              </div>
              <div class="btn-group box-shd" role="group">
                <button type="button" class="btn btn-default" data-dismiss="modal" role="button">
                  Close
                </button>
              </div>

            </div>
          </div>

        </div>

      </div>
    </div>

    <!-- End Modal -->


    <script>
      $('.reportinfo').click(function(e) {

        e.preventDefault()
        var reportId = $(this).data('id');

        $.ajax({
          url: 'viewAjaxBatchReport',
          type: 'post',
          data: {
            reportId: reportId
          },
          success: function(response) {
            $('.modal-body').html(response);

          }
        });
      });


      $('.scheduleNow').click(function(e) {

        e.preventDefault()
        var reportId = $(this).data('id');

        $.ajax({
          url: 'schedule_now',
          type: 'post',
          data: {
            reportId: reportId
          },
          success: function(response) {
            $('.modal-body').html(response);

          }
        });
      });


      $('#submit_btn').click(function(e) {

        e.preventDefault();
        $('#scheduleModal').modal('hide');
        $('#loader').show();
        // console.log($('#reportingId').val());

        var reportingId = $('#reportingId').val();

        $.ajax({
          url: 'generateReport.php',
          type: 'post',
          data: {
            reportingId: reportingId
          },
          dataType: 'json',
          success: function(response) {
           // alert(response.status +'=='+response.status_msg);
            if (response.status == 1 && response.status_msg == 'generated') {
               $('#loader').hide();

              swal({
                title: "Generated!",
                text: "The report(s) was/were successfully created.",
                timer: 2000,
                showConfirmButton: false,
                type: 'success'
              });
            } else if (response.status == 0 && response.status_msg == 'login failed') {
              $('#loader').hide();

              swal({
                title: "Failed!",
                text: "The report(s) has/have failed due to the SFTP information being incorrect.",
                timer: 2000,
                showConfirmButton: false,
                type: 'error'
              });
            } 
            
            else if (response.status == 0 && response.status_msg == 'Failed') {
              $('#loader').hide();
              swal({
                title: "Failed!",
                text: "No Data Present",
                timer: 2000,
                showConfirmButton: false,
                type: 'error'
              });
            } 
            
            else if (response.status == 0 && response.status_msg == 'uploading error') {
              $('#loader').hide();

              swal({
                title: "Failed!",
                text: "The report(s) has/have failed due to the directory path being incorrect.",
                timer: 2000,
                showConfirmButton: false,
                type: 'error'
              });
            }

          }
        });

        // alert($('#reportingId').val());
      });
    </script>
    <script>
      $(function() {
        $('#TblPagination').DataTable({
          "pagingType": "full_numbers",
          "bPaginate": false,
          "fixedHeader": true,
          "scrollY": 550,
          "lengthChange": false,
          "searching": true,
          "ordering": false,
          "info": true,


        });

      });

      $(document).ready(function() {
        $('body').click(function() {
          // $("#ViewFaqModal").modal("hide"); 
          // $("#ApproveFaqModal").modal("hide"); 

        });
        $('.userinfo').click(function() {
          var FAQid = $(this).data('id');
          $.ajax({
            url: 'addAjax.php',
            type: 'post',
            data: {
              FAQid: FAQid
            },
            success: function(response) {
              $('#modal-body').html(response);

            }
          });
        });
      });
      $(document).ready(function() {
        $(".viewFaq").click(function() {
          event.preventDefault();
          var FAQids = $(this).data('id');
          $.ajax({
            url: 'viewAjax.php',
            type: 'post',
            data: {
              FAQids: FAQids
            },
            success: function(response) {
              //console.log(response);
              $('#View_faq_body').html(response);

            }
          });
        });
      });



      // Change Schedular status
      $(document).on('click', 'span#contents1', function() {
        var status_id = $(this).data('id');
        var status_type = "On_hold";
        swal({
            title: "Are you sure?",
            text: "You want to change the status?",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Yes, change it!",
            closeOnConfirm: false
          },
          function() {
            $.ajax({
              type: "POST",
              url: "changeSchedularStatus.php",
              data: {
                status_id: status_id,
                status_type: status_type
              },
              dataType: "json",
              success: function(data) {
                var result = JSON.parse(data['status']);
                if (result == 1) {
                  location.reload();
                }
              }
            });
            swal({
              title: "",
              text: "Status changed successfully",
              timer: 4000,
              showConfirmButton: false,
              type: 'success'
            });

          });

      })


      $(document).on('click', 'span#contents2', function() {
        var status_id = $(this).data('id');
        var status_type = "Active";
        swal({
            title: "Are you sure?",
            text: "You want to change the status?",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Yes, change it!",
            closeOnConfirm: false
          },
          function() {
            $.ajax({
              type: "POST",
              url: "changeSchedularStatus.php",
              data: {
                status_id: status_id,
                status_type: status_type
              },
              dataType: "json",
              success: function(data) {
                var result = JSON.parse(data['status']);
                if (result == 1) {
                  location.reload();
                }
              }
            });
            swal({
              title: "",
              text: "Status changed successfully",
              timer: 4000,
              showConfirmButton: false,
              type: 'success'
            });

          });

      })
    </script>

    <?php if (isset($_SESSION['success'])) { ?>
      <script>
        swal({
          title: "Submitted!",
          text: "Your imaginary file has been submitted.",
          timer: 4000,
          showConfirmButton: false,
          type: 'success'
        });
      </script>
    <?php }
    unset($_SESSION['success']);
    ?>
  </body>

  </html>