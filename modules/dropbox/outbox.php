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
        $out .= "<div id='del_msg'></div><div id='msg_area'><table>";
        $out .= "<tr><td>$langSubject:</td><td>".q($msg->subject)."</td></tr>";
        $out .= "<tr id='$msg->id'><td>$langDelete:</td><td><img src=\"".$themeimg.'/delete.png'."\" class=\"delete\"/></td></tr>";
        if ($msg->course_id != 0 && $course_id == 0) {
            $out .= "<tr><td>$langCourse:</td><td><a class=\"outtabs\" href=\"index.php?course=".course_id_to_code($msg->course_id)."\">".course_id_to_title($msg->course_id)."</a></td></tr>";
        }
        $out .= "<tr><td>$langDate:</td><td>".nice_format(date('Y-m-d H:i:s',$msg->timestamp), true)."</td></tr>";
        $out .= "<tr><td>$langSender:</td><td>".display_user($msg->author_id)."</td></tr>";
        
        $recipients = '';
        foreach ($msg->recipients as $r) {
            if ($r != $msg->author_id) {
                $recipients .= display_user($r).'<br/>';
            }
        }
        
        $out .= "<tr><td>$langRecipients:</td><td>".$recipients."</td></tr>";
        $out .= "<tr><td>$langMessage:</td><td>".standard_text_escape($msg->body)."</td></tr>";

        if ($msg->filename != '') {
            $out .= "<tr><td>$langAttachedFile</td><td><a href=\"dropbox_download.php?course=".course_id_to_code($msg->course_id)."&amp;id=$msg->id\" class=\"outtabs\" target=\"_blank\">$m->real_filename</a></td></tr>";
        }
        
        $out .= "</table><br/>";

        $out .= '<script>
        $(function() {
        $(".delete").click(function() {
            if (confirm("' . $langConfirmDelete . '")) {
            var rowContainer = $(this).parent().parent();
                    var id = rowContainer.attr("id");
                    var string = \'mid=\'+ id ;
    
                    $.ajax({
                    type: "POST",
                       url: "delete.php",
                       data: string,
                              cache: false,
                              success: function(){
                                $("#msg_area").slideUp(\'fast\', function() {$(this).remove();});
                                $("#del_msg").html("<p class=\'success\'>'.$langMessageDeleteSuccess.'</p>");
                              }
                           });
                           return false;
                         }
                       });
                     });
                 </script>';
    }
} else {
    
    require_once("class.mailbox.php");
    
    $mbox = new Mailbox($uid, $course_id);
    
    $out_msgs = $mbox->getOutboxMsgs();
    
    if (empty($out_msgs)) {
        $out = "<p class='alert1'>$langTableEmpty</p>";
    } else {
        $out = "<div class=\"loading\" align=\"center\"><img src=\"".$themeimg."/ajax_loader.gif"."\" align=\"absmiddle\"/>".$langLoading."</div>";
        $out .= "<div id='del_msg'></div><div id='outbox'>";
        $out .= "<p>$langDeleteAllMsgs: <img src=\"".$themeimg.'/delete.png'."\" class=\"delete_all\"/></p><br/>";
        $out .= "<table id=\"outbox_table\">
                   <thead>
                     <tr>";
        if ($course_id == 0) {
            $out .= "<th>$langCourse</th>";
        }
        $out .= "      <th>$langSubject</th>
                       <th>$langRecipients</th>
                       <th>$langDate</th>
                       <th>$langDelete</th>
                     </tr>
                   </thead>
                   <tbody>";
        
        foreach ($out_msgs as $m) {
            $urlstr = '';
            if ($course_id != 0) {
                $urlstr = "&amp;course=".$course_code;
            }
            $recipients = '';
            foreach ($m->recipients as $r) {
                if ($r != $m->author_id) {
                    $recipients .= display_user($r).'<br/>';
                }
            }
            $out .= "<tr id='$m->id'>";
            if ($course_id == 0) {
                if ($m->course_id != 0) {
                    $out .= "<td><a class=\"outtabs\" href=\"index.php?course=".course_id_to_code($m->course_id)."\">".course_id_to_title($m->course_id)."</a></td>";
                } else {
                    $out .= "<td></td>";
                }
            }
             $out .= " <td><a href='outbox.php?mid=$m->id".$urlstr."'>".q($m->subject)."</a></td>
                       <td>$recipients</td>
                       <td>".nice_format(date('Y-m-d H:i:s',$m->timestamp), true)."</td>
                       <td><img src=\"".$themeimg.'/delete.png'."\" class=\"delete\"/></td>
                     </tr>";
        }
        
        $out .= "  </tbody>
                 </table></div>";
        $out .= "<script>
                   $(document).ready(function() {
                     $('div.loading').hide();
                     $('#outbox_table').dataTable({
                        'bStateSave' : true,
                        'bProcessing': true,
                        'sDom': '<\"top\"pfl<\"clear\">>rt<\"bottom\"ip<\"clear\">>',
                        'aLengthMenu': [
                           [10, 15, 20 , -1],
                           [10, 15, 20, '$langAllOfThem'] // change per page values here
                         ],
                        'sPaginationType': 'full_numbers',
                        'bSort': false,
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
                        });
                   });
                 </script>";
        
            $out .= '<script>
                       $(function() {
                         $(".delete").click(function() {
                           if (confirm("' . $langConfirmDelete . '")) {
                             $(\'div.loading\').fadeIn();
                             var rowContainer = $(this).parent().parent();
                             var id = rowContainer.attr("id");
                             var string = \'mid=\'+ id ;
            
                             $.ajax({
                               type: "POST",
                               url: "delete.php",
                               data: string,
                               cache: false,
                               success: function(){
                                 rowContainer.slideUp(\'slow\', function() {$(this).remove();});
                                 $(\'div.loading\').fadeOut();
                               }
                             });
                             return false;
                           }
                         });
                                   
                         $(".delete_all").click(function() {
                          if (confirm("' . $langConfirmDeleteAllMsgs . '")) {
                            var string = \'all_outbox=1&course_id=\'+'.$course_id.' ;
    
                            $.ajax({
                              type: "POST",
                              url: "delete.php",
                              data: string,
                              cache: false,
                              success: function(){
                                $("#outbox").slideUp(\'fast\', function() {$(this).remove();});
                                $("#del_msg").html("<p class=\'success\'>'.$langMessageDeleteAllSuccess.'</p>");
                              }
                           });
                           return false;
                         }
                       });
                                    
                       });
                     </script>';
    }
}
echo $out;