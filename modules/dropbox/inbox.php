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
    $personal_msgs_allowed = get_config('dropbox_allow_personal_messages');
    
    $mid = intval($_GET['mid']);
    $msg = new Msg($mid, $uid, 'msg_view');
    if (!$msg->error) {
       
        $urlstr = '';
        if ($course_id != 0) {
            $urlstr = "?course=".$course_code;
        }
        $out = "<div style=\"float:right;\"><a href=\"inbox.php".$urlstr."\">$langBack</a></div>";
        $out .= "<div id='del_msg'></div><div id='msg_area'><table>";
        $out .= "<tr><td>$langSubject:</td><td>".q($msg->subject)."</td></tr>";
        $out .= "<tr id='$msg->id'><td>$langDelete:</td><td><img src=\"".$themeimg.'/delete.png'."\" class=\"delete\"/></td></tr>";
        if ($msg->course_id != 0 && $course_id == 0) {
            $out .= "<tr><td>$langCourse:</td><td><a class=\"outtabs\" href=\"index.php?course=".course_id_to_code($msg->course_id)."\">".course_id_to_title($msg->course_id)."</a></td></tr>";
        }
        $out .= "<tr><td>$langDate:</td><td>".nice_format(date('Y-m-d H:i:s',$msg->timestamp), true)."</td></tr>";
        $out .= "<tr><td>$langSender:</td><td>".display_user($msg->author_id, false, false)."</td></tr>";
        
        $recipients = '';
        foreach ($msg->recipients as $r) {
            if ($r != $msg->author_id) {
                $recipients .= display_user($r, false, false).'<br/>';
            }
        }
        
        $out .= "<tr><td>$langRecipients:</td><td>".$recipients."</td></tr>";
        $out .= "<tr><td>$langMessage:</td><td>".standard_text_escape($msg->body)."</td></tr>";

        if ($msg->filename != '') {
            $out .= "<tr><td>$langAttachedFile</td><td><a href=\"dropbox_download.php?course=".course_id_to_code($msg->course_id)."&amp;id=$msg->id\" class=\"outtabs\" target=\"_blank\">$m->real_filename</a></td></tr>";
        }
        
        $out .= "</table><br/>";
        
        /*****Reply Form****/
        if ($msg->course_id == 0 && !$personal_msgs_allowed) {
            //do not show reply form when personal messages are not allowed
        } else {
            if ($course_id == 0) {
                $out .= "<form method='post' action='dropbox_submit.php' enctype='multipart/form-data' onsubmit='return checkForm(this)'>";
                if ($msg->course_id != 0) {//thread belonging to a course viewed from the central ui
                    $out .= "<input type='hidden' name='course' value='".course_id_to_code($msg->course_id)."' />";
                }
            } else {
                $out .= "<form method='post' action='dropbox_submit.php?course=$course_code' enctype='multipart/form-data' onsubmit='return checkForm(this)'>";
            }
            //hidden variables needed in case of a reply
            foreach ($msg->recipients as $rec) {
                if ($rec != $uid) {
                    $out .= "<input type='hidden' name='recipients[]' value='$rec' />";
                }
            }
            $out .= "<fieldset>
                       <table width='100%' class='tbl'>
                         <caption><b>$langReply</b></caption>
                         <tr>
                           <th>$langSender:</th>
                           <td>" . q(uid_to_name($uid)) . "</td>
    	                 </tr>
                         <tr>
                           <th>$langSubject:</th>
                           <td><input type='text' name='message_title' value='".$langMsgRe.$msg->subject."' /></td>
    	                 </tr>";
            $out .= "<tr>
                      <th>" . $langMessage . ":</th>
                      <td>".rich_text_editor('body', 4, 20, '')."
                        <small><br/>$langMaxMessageSize</small></td>
                     </tr>";
            if ($course_id != 0) {
                $out .= "<tr>
                       <th width='120'>$langFileName:</th>
                       <td><input type='file' name='file' size='35' />
                       </td>
                     </tr>";
            }
            
            $out .= "<tr>
    	               <th>&nbsp;</th>
                       <td class='left'><input type='submit' name='submit' value='" . q($langSend) . "' />&nbsp;
                          $langMailToUsers<input type='checkbox' name='mailing' value='1' checked /></td>
                     </tr>
                   </table>
                 </fieldset>
               </form>
               <p class='right smaller'>$langMaxFileSize " . ini_get('upload_max_filesize') . "</p>";
    
             $out .= "<script type='text/javascript' src='{$urlAppend}js/jquery.multiselect.min.js'></script>\n";
             $out .= "<script type='text/javascript'>$(document).ready(function () {
                                  $('#select-recipients').multiselect({
                                    selectedText: '$langJQSelectNum',
                                    noneSelectedText: '$langJQNoneSelected',
                                    checkAllText: '$langJQCheckAll',
                                    uncheckAllText: '$langJQUncheckAll'
                                  });
                                });</script>
            <link href='../../js/jquery.multiselect.css' rel='stylesheet' type='text/css'>";
        }
        /******End of Reply Form ********/
        
        $out .= "</div>"; 
         
        $out .= '<script>
                  $(function() {
                    $(".delete").click(function() {
                      if (confirm("' . $langConfirmDelete . '")) {
                        var rowContainer = $(this).parent().parent();
                        var id = rowContainer.attr("id");
                        var string = \'mid=\'+ id ;
            
                        $.ajax({
                          type: "POST",
                          url: "ajax_handler.php",
                          data: string,
                          cache: false,
                          success: function(){
                            $("#msg_area").slideUp(\'fast\', function() {
                              $(this).remove();
                              $("#del_msg").html("<p class=\'success\'>'.$langMessageDeleteSuccess.'</p>");
                            });
                          }
                       });
                       return false;
                     }
                   });
                 });
                 </script>';
        //head content has the scripts necessary for tinymce as a result of calling rich_text_editor
        $out .= $head_content;
    }
} else {
    
    $out = "<div id='del_msg'></div><div id='inbox'>";
    $out .= "<p>$langDeleteAllMsgs: <img src=\"".$themeimg.'/delete.png'."\" class=\"delete_all\"/></p><br/>";
    
    $out .= "<table id=\"inbox_table\">
                  <thead>
                    <tr>";
    if ($course_id == 0) {
        $out .= "    <th>$langCourse</th>";
    }
    $out .= "         <th>$langSubject</th>
                      <th>$langSender</th>
                      <th>$langDate</th>
                      <th>$langDelete</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
              </table>";
    
    $out .= "<script>
               $(document).ready(function() {
                 var oTable = $('#inbox_table').dataTable({
                   'bStateSave' : true,
                   'bProcessing': true,
                   'sDom': '<\"top\"pfl<\"clear\">>rt<\"bottom\"ip<\"clear\">>',
                   'sAjaxSource': 'ajax_handler.php?mbox_type=inbox&course_id=$course_id',                   
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
                    },
                   'fnDrawCallback' : function( oSettings ) {
                       $('.delete').on('click', function() {
                           if (confirm('$langConfirmDelete')) {
                               var rowContainer = $(this).parent().parent();
                               var id = rowContainer.attr('id');
                               var string = 'mid='+ id ;
                               $.ajax({
                                   type: 'POST',
                                   url: 'ajax_handler.php',
                                   data: string,
                                   cache: false,
                                   success: reload()
                               });
                               return false;
                           }
                       }); 
                    }
                 }).fnSetFilteringDelay(1000);
                 
                 var reload = function() {
                    oTable.fnReloadAjax();
                    $('#del_msg').html('<p class=\'success\'>$langMessageDeleteSuccess</p>');
                    $('.success').delay(3000).fadeOut(1500);
                 };
                 
                 $('.delete_all').click(function() {
                  if (confirm('$langConfirmDeleteAllMsgs')) {
                    var string = 'all_inbox=1';
                    $.ajax({
                      type: 'POST',
                      url: 'ajax_handler.php?course_id=$course_id',
                      data: string,
                      cache: false,
                      success: function(){
                        oTable.fnReloadAjax();      
                        $('#del_msg').html('<p class=\'success\'>$langMessageDeleteAllSuccess</p>');
                        $('.success').delay(3000).fadeOut(1500);
                      }
                   });
                   return false;
                 }
               });
                 
               });
             </script>";
}
echo $out;
    