<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2013  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */

$require_login = TRUE;
if(isset($_GET['course'])) {//course messages
    $require_current_course = TRUE;
} else {//personal messages
    $require_current_course = FALSE;
}
$guest_allowed = FALSE;

include '../../include/baseTheme.php';
include 'include/lib/fileDisplayLib.inc.php';
require_once("class.msg.php");

if (!isset($course_id)) {
    $course_id = 0;
}

if (isset($_GET['mid'])) {

    $mid = intval($_GET['mid']);
    $msg = new Msg($mid, $uid, 'msg_view');
    if (!$msg->error) {
         
        $urlstr = '';
        if ($course_id != 0) {
            $urlstr = "?course=".$course_code;
        }
        $out = "<div style=\"float:right;\"><a href=\"outbox.php".$urlstr."\">$langBack</a></div>";
        $out .= "<div id='out_del_msg'></div><div id='out_msg_area'><table>";
        $out .= "<tr><td>$langSubject:</td><td>".q($msg->subject)."</td></tr>";
        $out .= "<tr id='$msg->id'><td>$langDelete:</td><td><img src=\"".$themeimg.'/delete.png'."\" class=\"delete\"/></td></tr>";
        if ($msg->course_id != 0 && $course_id == 0) {
            $out .= "<tr><td>$langCourse:</td><td><a class=\"outtabs\" href=\"index.php?course=".course_id_to_code($msg->course_id)."\">".course_id_to_title($msg->course_id)."</a></td></tr>";
        }
        $out .= "<tr><td>$langDate:</td><td>".nice_format(date('Y-m-d H:i:s',$msg->timestamp), true)."</td></tr>";
        $out .= "<tr><td>$langSender:</td><td>".display_user($msg->author_id, false, false, "outtabs")."</td></tr>";
        
        $recipients = '';
        foreach ($msg->recipients as $r) {
            if ($r != $msg->author_id) {
                $recipients .= display_user($r, false, false, "outtabs").'<br/>';
            }
        }
        
        $out .= "<tr><td>$langRecipients:</td><td>".$recipients."</td></tr>";
        $out .= "<tr><td>$langMessage:</td><td>".standard_text_escape($msg->body)."</td></tr>";

        if ($msg->filename != '') {
            $out .= "<tr><td>$langAttachedFile</td><td><a href=\"dropbox_download.php?course=".course_id_to_code($msg->course_id)."&amp;id=$msg->id\" class=\"outtabs\" target=\"_blank\">$msg->real_filename
            <img class='outtabs' src='$themeimg/save.png' /></a>&nbsp;&nbsp;(".format_file_size($msg->filesize).")</td></tr>";
        }
        
        $out .= "</table><br/>";

        $out .= '<script>
        $(function() {
        $(".delete").click(function() {
            if (confirm("' . $langConfirmDelete . '")) {
            var rowContainer = $(this).parent().parent();
                    var id = rowContainer.attr("id");
                    var string = \'mid=\'+ id;
    
                    $.ajax({
                       type: "POST",
                       url: "ajax_handler.php",
                       data: string,
                       cache: false,
                       success: function(){
                           $("#out_msg_area").slideUp(\'fast\', function() {
                                $(this).remove();
                                $("#out_del_msg").html("<p class=\'success\'>'.$langMessageDeleteSuccess.'</p>");
                           });
                       }
                    });
                    return false;
            }
        });
        });
        </script>';
    }
} else {
    
    $out = "<div id='out_del_msg'></div><div id='outbox'>";
    $out .= "<p>$langDeleteAllMsgs: <img src=\"".$themeimg.'/delete.png'."\" class=\"delete_all_out\"/></p><br/>";
    $out .= "<table id=\"outbox_table\">
               <thead>
                 <tr>
                    <th>$langSubject</th>";
    if ($course_id == 0) {
        $out .= "<th>$langCourse</th>";
    }
    $out .= "      <th>$langRecipients</th>
                   <th>$langDate</th>
                   <th>$langDelete</th>
                 </tr>
               </thead>
               <tbody>
               </tbody>
             </table></div>";
    
    $out .= "<script type='text/javascript'>
               $(document).ready(function() {
                 var oTable2 = $('#outbox_table').dataTable({
                    'bStateSave' : true,
                    'bProcessing': true,
                    'sDom': '<\"top\"pfl<\"clear\">>rt<\"bottom\"ip<\"clear\">>',
                    'bServerSide': true,
                    'sAjaxSource': 'ajax_handler.php?mbox_type=outbox&course_id=$course_id',
                    'aLengthMenu': [
                       [10, 15, 20 , -1],
                       [10, 15, 20, '$langAllOfThem'] // change per page values here
                     ],
                    'sPaginationType': 'full_numbers',
                    'bSort': false,
                    'bAutoWidth' : false,
                    'oLanguage': {
                            'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                            'sZeroRecords':  '".$langNoResult."',
                            'sInfo':         '$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults',
                            'sInfoEmpty':    '$langDisplayed 0 $langTill 0 $langFrom2 0 $langResults2',
                            'sInfoFiltered': '',
                            'sInfoPostFix':  '',
                            'sSearch':       '".$langSearch."',
                            'sUrl':          '',
                            'oPaginate': {
                                 'sFirst':    '&laquo;',
                                 'sPrevious': '&lsaquo;',
                                 'sNext':     '&rsaquo;',
                                 'sLast':     '&raquo;'
                            }
                        }
                    }).fnSetFilteringDelay(1000);
                    
                    $(document).on( 'click','.delete_out', function (e) {
                        e.preventDefault();
                        if (confirm('$langConfirmDelete')) {
                            var rowContainer = $(this).parent().parent();
                            var id = rowContainer.attr('id');
                            var string = 'mid='+ id ;
                            $.post('ajax_handler.php', string, function() {
                                var num_page_records = oTable2.fnGetData().length;
                                var per_page = oTable2.fnPagingInfo().iLength;
                                var page_number = oTable2.fnPagingInfo().iPage;
                                if(num_page_records==1){
                                    if(page_number!=0) {
                                        page_number--;
                                    }
                                }
                                $('#out_del_msg').html('<p class=\'success\'>$langMessageDeleteSuccess</p>');
                                $('.success').delay(3000).fadeOut(1500);
                                oTable2.fnPageChange(page_number);
                            }, 'json');
                         }
                     });
                     
                    $('.delete_all_out').click(function() {
                      if (confirm('$langConfirmDeleteAllMsgs')) {
                        var string = 'all_outbox=1';
                        $.ajax({
                          type: 'POST',
                          url: 'ajax_handler.php?course_id=$course_id',
                          data: string,
                          cache: false,
                          success: function(){
                            var num_page_records = oTable2.fnGetData().length;
                            var per_page = oTable2.fnPagingInfo().iLength;
                            var page_number = oTable2.fnPagingInfo().iPage;
                            if(num_page_records==1){
                              if(page_number!=0) {
                                page_number--;
                              }
                            }     
                            $('#out_del_msg').html('<p class=\'success\'>$langMessageDeleteAllSuccess</p>');
                            $('.success').delay(3000).fadeOut(1500);
                            oTable2.fnPageChange(page_number);
                          }
                       });
                       return false;
                     }
                   });
               
               });
             </script>";
}
echo $out;