<?php 

session_start();
include_once('config.php');
//include_once('mydownloadconn.php');
//print_r($_POST);exit;
$radval            =$_POST['radval'];
$UserTypecode      =$_POST['UserTypecode'];
$companymultiselect=$_POST['companymultiselect'];
$type              = $_POST['type'];
$folder            = $_POST['folder'];
$noofdays          =$_POST['noofdays'];
$inputValue        =$_POST['inputValue'];
if($noofdays=='<7'){
$noofdaysQuery="AND DATE(U.DateTime) < DATE_SUB(CURDATE(), INTERVAL 7 DAY )";
}else if($noofdays=='<15'){
$noofdaysQuery="AND DATE(U.DateTime) < DATE_SUB(CURDATE(), INTERVAL 15 DAY )";
}else if($noofdays=='30'){
$noofdaysQuery="AND (DATE(U.DateTime) >= CURDATE() - INTERVAL 30 DAY AND DATE(U.DateTime) <= CURDATE() - INTERVAL 1 DAY)";
}else if($noofdays=='>30'){
$noofdaysQuery="AND DATE(U.DateTime) > DATE_SUB(CURDATE(), INTERVAL 30 DAY )";


}else{
   $noofdaysQuery=''; 
}
if($inputValue=='1'){
  $archive="AND U.archiveFlag = 1";
}else{
  $archive="AND U.archiveFlag = 0";
}

 if($radval==''){
    $subQuery1='';
    $subQuery2='';
    $subQuery3='';
    $limit="limit 100";

}else if($radval==3){
    $subQuery = "";
    $limit="";
    if($UserTypecode != ''){
        $subQuery1 = " AND U.SenderType IN ('$UserTypecode')";
    }
    if($companymultiselect != ''){
        $companymultiselect = "'".implode("','",$_POST['companymultiselect'])."'";
        $subQuery2 = " AND U.UserCode IN ( $companymultiselect)";
    }
    if($type != ''){
        $types = "'".implode("','",$_POST['type'])."'";
        $subQuery3 = " AND U.Type IN ( $types)";
    }
}
    $fetchQuery = "SELECT DISTINCT U.UploadId, U.UserCode, U.Size,(CASE WHEN D.Subject IS NULL  THEN 'AACANet' ELSE D.Subject END) AS Subject,
    (CASE WHEN U.UsertType = 1 AND U.Subject = 'AACA' AND D.Type IS NULL THEN UPPER(CONCAT(T.fullName,' ',T.LastName)) ELSE D.TYPE END) AS Type,
    U.FileName, U.DateTime, U.UploadedBy, U.SenderName, U.otherDesc, U.DocPath, U.IsDelete, U.archiveFlag,
    (CASE WHEN U.UserCode = 'AACA' THEN 'AACANet' ELSE R.CNAME END) AS CompanyName
    FROM MY_UPLOADS U
    LEFT JOIN CC_REGSTR R ON U.UserCode = R.CCODE
    LEFT JOIN MYUPLOADDD D ON D.TYPE_SHORTVAL = U.Type AND  D.SUBJECT_SHORTVAL = U.Subject
    LEFT JOIN (SELECT email, fullName, LastName FROM tbl_login WHERE userType=1 AND bit_deleted_flag=0 GROUP BY email) T ON T.email = U.Type
    WHERE U.IsDelete = 0  ".$subQuery1." ".$subQuery2." ".$subQuery3." ".$noofdaysQuery."
    ".$archive."
    ORDER BY U.DateTime DESC ".$limit."";
    // echo $fetchQuery;
     $result = mysqli_query($conn,$fetchQuery);
     $row = mysqli_num_rows($result);
        $response .='<table id="example" class="display nowrap table table-bordered table-striped table-hover table-condensed">';
        $response .='<thead><div id="kbmbdiv"><input type="hidden" name="kbmb" id="kbmb" value=""></div>';
        $response .= '<tr style="background-color:#0f0f6c;color:white;">';
        $response .= "<th class='text-center w-5'>Select</th>";
        $response .= "<th class='text-center w-5'>Download</th>";
        $response .= "<th class='text-center'>File</th>";
        $response .= "<th class='text-center'>Size</th>";
        $response .= "<th class='text-center'>Subject</th>";
        $response .= "<th class='text-center'>File Type</th>";
        $response .= "<th class='text-center w-5'>User Code</th>";
        $response .= "<th class='text-center'>Company Name</th>";
        $response .= "<th class='text-center'>Status</th>";
        $response .= "<th class='text-center'>Uploaded Date</th>";
        $response .= "<th class='text-center'>Uploaded Time</th>";
        $response .= "<th class='text-center'>Uploaded By</th>";
        $response .= "</tr>";
        $response .= "</thead>";
        $response .= "<tbody class='counter-reset' id='tbodyDownload'>";

        if($row != 0){
        foreach ($result as $value) {

            $UploadedBy=$value['UploadedBy'];
            $SenderName=$value['SenderName'];
            $date = $value['DateTime'];
            $time= substr($date, 11, 8);
            $dt = new DateTime($date);
            $date = $dt->format('m-d-Y');
            $mainfolder='';
            $path =$value['DocPath'];
            $find = ['#', '&'];
            $replacement = ['a8', 'b8'];
            if($value['archiveFlag']==1){
              $path=str_replace("MYUPLOADS","MYUPLOADSARCHIVE",$path);
            }else{
              $path=$path;
            }
            $downloadpath='mydownloadfiledownload?name='.str_replace($find, $replacement, $path);
            $filename_without_extension = pathinfo($value['FileName'], PATHINFO_FILENAME);
            if($value['archiveFlag']==0){
              $status="Active";
            }else{
              $status="Archived";
            }
            $response .= "<tr>";
            $response .="<td class='text-center w-5'><input type='checkbox' id='getsize' name='getsize' value='".$value['Size']."'  data-id='".$path."' class='select-row checkcheckbox'><input type='hidden' id='sizename'  value='".$value['Size']."K'></td>";
            $response .="<td class='text-center w-5'>
                        <a href='".$downloadpath."' title='Download' target='_blank'><i class='fa fa-download icon-size' aria-hidden='true' style='color: #428BCA;''></i></a>
                        </td>";

            $fname = substr($filename_without_extension, 0, 22);
            $star='****';
            $fname_last = substr($filename_without_extension, -4);

            $response .="<td class='text-center'><a class='DocPath' title='".$filename_without_extension."' data-id = '".$path."' href='#'>".$fname.$star.$fname_last."</a></td>"; 

            // $response .="<td class='text-left'>".$filename_without_extension."</td>";  
            $response .="<td class='text-left' id='sizefield' name='sizefield'>".$value['Size']."K</td>";
            $response .="<td class='text-left' id='' name=''>".$value['Subject']."</td>";
            $response .="<td class='text-left' id='file_type' name='file_type'>".$value['Type']."</td>";
            
            $response .="<td class='text-left w-5' id='' name=''>".$value['UserCode']."</td>";
            $response .="<td class='text-left' id='' name=''>".$value['CompanyName']."</td>";
            $response .="<td class='text-left'>".$status."</td>";
            $response .="<td class='text-left' >".$date."</td>";
            $response .="<td class='text-left' >".$time."</td>";
            // $response .="<td class="text-left break"><a href="#" title='$value['SenderName']>< $value['UploadedBy']</a></td>.";
            $response .="<td class='text-center'><a href='#' title='$SenderName'>".$value['UploadedBy']."</a></td>"; 
            $response .="</tr>";
            }
        }else{
        $msg= "No record found";
        $response .= "<tr>";
        $response .= "<td colspan='3' style='text-align: center;'>".$msg."</td>"; 
        $response .= "</tr>"; 
        }
        $response .= "</tbody>";
        $response .= "</table><span class='error' id='selrecordError'></span>";
        $response .= "<div id='downloadzipdiv' style='display:none;'><form method='POST' id='zipdown'><button type='submit' name='zip' id='zip' class='btn btn-primary btn-xs-100 mrg0 mrg20R' >Zip Download</button></form></div>";
        echo $response;
    
?>
<script type="text/javascript">

    $(document).ready( function () {
    '<?php if($row != 0){?>'
     $.fn.dataTable.moment( 'MM-DD-YYYY' );
    
    $('#example').DataTable({
      "pagingType": "full_numbers",
        "bPaginate": true,
        "fixedHeader": true,
         scrollY: "100px",
         scrollX: true,
         "order": [[ 8, "desc" ]],
        columnDefs: [ {
        'targets': [0,1],
        'orderable': false, 
         "defaultContent": "-",
   

    }],
    })
   '<?php }?>'
})

$(document).ready(function(){
$('#shareBtn').on('click', function(e) {
   //alert('test');
   e.preventDefault();

   if(validate()){
   $('#loader').show();
   getsize = checkboxValues;//getSelectedRows()
   var formData = new FormData(); 

   var choosetype=$('input[name="choosentype"]:checked').val();
   var selectedFile = $('#selectedFile').val();
   //var radval = $('input[name=radfile]:checked').val()


   if(choosetype == 1)
   {
      if(selectedFile == 'MYDOWNLOADS')
      {
        formData.append('selectedFile', selectedFile);
        formData.append('username', $('#username').val());
      }
      else
      {
        formData.append('selectedFile', selectedFile);
      }
   }
   else if(choosetype == 2)
   {
      if(selectedFile == 'MYDOWNLOADS')
      {
        formData.append('selectedFile', selectedFile);
        formData.append('firmCode', $('#Firm').val());
        formData.append('username', $('#username').val());
      }
      else
      {
        formData.append('selectedFile', selectedFile);
        formData.append('firmCode', $('#Firm').val());
      }
   }
   else if(choosetype == 3)
   {
      if(selectedFile == 'MYDOWNLOADS')
      {
        formData.append('selectedFile', selectedFile);
        formData.append('clientCode', $('#Client').val());
        formData.append('username', $('#username').val());
      }
      else
      {
        formData.append('selectedFile', selectedFile);
        formData.append('clientCode', $('#Client').val());
      }
   }
   else if(choosetype == 4)
   {
      if(selectedFile == 'MYDOWNLOADS')
      {
        formData.append('selectedFile', selectedFile);
        formData.append('agencyCode', $('#Agency').val());
        formData.append('username', $('#username').val());
      }
      else
      {
        formData.append('selectedFile', selectedFile);
        formData.append('agencyCode', $('#Agency').val());
      }
   }
        formData.append('getsize', getsize);
        formData.append('choosentype', choosetype);
        formData.append('radval', 3);
        formData.append('sizename', $('#sizename').val());

   $.ajax({
      type: 'POST',
      url: 'ajaxShareDocs.php',
      data: formData,
      processData: false,
      contentType: false,
      success: function (data) {
        var json = JSON.parse(data);
        
        if(json['status'] == 1)
        {
          $('#myPopup').modal('hide');
          $('#loader').hide();
          swal({title: "Shared!",
                text: "Documents has been shared successfully",
                timer: 2500,
                showConfirmButton: false,
                type: 'success'
              });
         window.location.reload();
        }
 
     
     },
     error: function ()
     { alert('there is some error to get Rate'); }
   });
 }
})

  $('#zip').on('click', function() {
    var folder='';
        var selval='';
        var radval=3;//'<?php// echo $_POST['radval'];?>';

       window.open('zipDownloadFile.php?folder=' + encodeURIComponent(folder) + '&selval=' + encodeURIComponent(selval) + '&radval=' + encodeURIComponent(radval)+ '&filearray=' + (checkboxValues), '_blank');
  });

 var checkboxValues = [];
 var exactfilename=[];
$('#example').on("change", ".checkcheckbox", function (event) {
  //$('input.checkcheckbox').bind('change.myChange', function() {alert('ppp')
     $('#showsize').show();
     if (this.checked==true) {
     var attrName = $(this).attr('data-id').replace("#", "ddd").replace(",", "ccc");
        checkboxValues.push(attrName);
        exactfilename.push($(this).attr('data-id'));
           var sizein=$('#kbmb').val();
        var sizecheck=$('#sizecheck').val();
        var countCheckedCheckboxes = $('[name=getsize]:checked').filter(':checked').length;
        if(sizecheck==''){
         var newsizecheck=0;
        }else{
          var newsizecheck=(parseFloat($('#sizecheck').val()));
        }
        var size1=(parseFloat($(this).val()));
        if(sizein=='MB'){
          $('#kbmb').val('MB');
          var totsize= (size1/1024) + newsizecheck;
          var sizeinmb=totsize;
           var text="Total selected size(MB):";
        }else if(sizein=='KB'){
          var totsize= (size1) + newsizecheck;
          if(totsize>=1024){
             var sizeinmb=totsize/1024;
             var text="Total selected size(MB):";
             $('#kbmb').val('MB');
          }else{
            var sizeinmb=totsize;
            var text="Total selected size(KB):";
            $('#kbmb').val('KB');
          }
             
        }else{
        var totsize= size1 + newsizecheck;
        var sizeinmb=totsize;//Math.round((totsize + Number.EPSILON) * 100) / 100;//Math.round(totsize);//.toFixed(2); 
        if(sizeinmb>=1024 ){
         var text="Total selected size(MB):";
          sizeinmb=sizeinmb/1024;
           $('#kbmb').val('MB'); 
        }else{
          sizeinmb=sizeinmb;
           var text="Total selected size(KB):";
            $('#kbmb').val('KB');
            
        }
      }
       
     
       
        $('#showsize').html('<div class="form-group row"><label class="col-sm-6 col-form-label" style="padding-top: 6px;">'+text+'</label><div class="col-sm-4 pl0"><input type="text" name="FileSize" class="form-control input-sm" value="'+sizeinmb.toFixed(2)+'" id="sizecheck" style="color: #286090; font-weight: bold;"/></div></div>');
       // alert(sizeinmb +'=='+countCheckedCheckboxes+'=='+sizein);//126==2==KB
        if(sizeinmb <=30720  && countCheckedCheckboxes>1 && sizein=='KB'){
          $('#downloadzipdiv').show();
        }else if(sizeinmb <=30  && countCheckedCheckboxes>1 && sizein=='MB'){
          $('#downloadzipdiv').show();
        }else if(sizeinmb >30720  && countCheckedCheckboxes>1 && sizein=='KB'){
          $('#downloadzipdiv').hide();
          $('#downloadzipdiv').html('<span style="color:red">Unable to zip because file size is greater than 30 MB</span>').css('display','block');
        }else if(sizeinmb >30  && countCheckedCheckboxes>1 && sizein=='MB'){
          $('#downloadzipdiv').hide();
          $('#downloadzipdiv').html('<span style="color:red">Unable to zip because file size is greater than 30 MB</span>').css('display','block');
        }
      }if(this.checked==false){
         var attrName = $(this).attr('data-id').replace("#", "ddd").replace(",", "ccc");
         checkboxValues.splice( $.inArray(attrName, checkboxValues), 1 );
         exactfilename.splice( $.inArray(($(this).attr('data-id')), exactfilename), 1 );
         var newsizecheck=parseFloat($('#sizecheck').val());
         var sizein=$('#kbmb').val();
         if(sizein=='MB'){
           var size1       = (parseFloat($(this).val()))/1024;
           //alert(newsizecheck+'======'+size1);
           var sizeinmb     =newsizecheck-size1;
           //alert(sizeinmb);
           if(sizeinmb>1){
            sizeinmb=sizeinmb;
            var text="Total selected size(MB):";
            $('#kbmb').val('MB');
           }else{
            sizeinmb=sizeinmb*1024;
            var text="Total selected size(KB):";
            $('#kbmb').val('KB');
           }
          // var text="Total selected size(MB)";
         }else if(sizein=='KB'){
         
          var size1       = parseFloat($(this).val());
          var sizeinmb     =newsizecheck-size1;
           var text="Total selected size(KB):";
         }
   
        var countCheckedCheckboxes = $('[name=getsize]:checked').filter(':checked').length;
       
        // $('#showsize').html('<div class="row"><label class="col-xs-12">'+text+'</label></div><input type="text" name="FileSize" class="form-control input-sm" value="'+sizeinmb.toFixed(2)+'" id="sizecheck" style="color: #286090; font-weight: bold;"/>');
        $('#showsize').html('<div class="form-group row"><label class="col-sm-6 col-form-label" style="padding-top: 6px;">'+text+'</label><div class="col-sm-4 pl0"><input type="text" name="FileSize" class="form-control input-sm" value="'+sizeinmb.toFixed(2)+'" id="sizecheck" style="color: #286090; font-weight: bold;"/></div></div>');
        // alert(sizeinmb +'=='+sizein);
       if(countCheckedCheckboxes==1){
           $('#downloadzipdiv').hide();
           
        }
         else if(sizeinmb <=30720  && countCheckedCheckboxes>1 && sizein=='KB'){
           $('#downloadzipdiv').html('');
           $('#downloadzipdiv').html("<form method='POST' id='zipdown'><button type='submit' name='zip' id='zip' class='btn btn-primary btn-xs-100 mrg0 mrg20R' >Zip Download</button></form>");
        }else if(sizeinmb <=30  && countCheckedCheckboxes>1 && sizein=='MB'){
           $('#downloadzipdiv').html('');
           $('#downloadzipdiv').html("<form method='POST' id='zipdown'><button type='submit' name='zip' id='zip' class='btn btn-primary btn-xs-100 mrg0 mrg20R' >Zip Download</button></form>");
        }else if(sizeinmb >30720  && countCheckedCheckboxes>1 && sizein=='KB'){
          $('#downloadzipdiv').hide();
          $('#downloadzipdiv').html('<span style="color:red">Unable to zip because file size is greater than 30 MB</span>').css('display','block');
        }else if(sizeinmb >30  && countCheckedCheckboxes>1 && sizein=='MB'){
          $('#downloadzipdiv').hide();
          $('#downloadzipdiv').html('<span style="color:red">Unable to zip because file size is greater than 30 MB</span>').css('display','block');
        }
         $('#zip').on('click', function() {
         
        var folder='';
        var selval='';
        var radval=3;//'<?php //echo $_POST['radval'];?>';
    
       window.open('zipDownloadFile.php?folder=' + encodeURIComponent(folder) + '&selval=' + encodeURIComponent(selval) + '&radval=' + encodeURIComponent(radval)+ '&filearray=' + (checkboxValues), '_blank');
   
        });
      
      }

   });
  });


</script>