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

if (!isset($course_id)) {
    $course_id = 0;
}

require_once("class.thread.php");

if (isset($_GET['tid'])) {
    require_once("class.msg.php");
    
    $tid = intval($_GET['tid']);
    $thread = new Thread($tid, $uid);
    if (!$thread->error) {
        $msgs = $thread->getMsgs();
       
        $out = "<script>
                  $(document).ready(function() {
                    $('#loading').hide();
                  });
                </script>";
        $out .= "<div id=\"loading\" align=\"center\"><img src=\"".$themeimg."/ajax_loader.gif"."\" align=\"absmiddle\"/>".$langLoading."</div>";
        $out .= "<h2>$langSubject: $thread->subject</h2><br/>";
        $out .= "<table id=\"thread_table\">
                  <thead>
                    <tr>
                      <th>$langDate</th>
                      <th>$langSender</th>
                      <th>$langMessage</th>
                      <th>$langAttachedFile</th>
                      <th>$langDelete</th>
                    </tr>
                  </thead>
                  <tbody>";
        
        foreach ($msgs as $m) {
            $out .= "<tr id='$m->id'>
                       <td>".nice_format(date('Y-m-d H:i:s',$m->timestamp), true)."</td>
                       <td>".uid_to_name($m->author_id)."</td>
                       <td>".standard_text_escape($m->body)."</td>
                       <td>$m->real_filename</td>
                       <td><img src=\"".$themeimg.'/delete.png'."\" class=\"delete\"/></td>        
                     </tr>";
        }
        
        $out .= "  </tbody>
                 </table>";
        $out .= "<script>
                   $(document).ready(function() {
                     $('#thread_table').dataTable();
                   });
                 </script>";
        
        $out .= '<script>
                  $(function() {
                    $(".delete").click(function() {
                      $(\'#loading\').fadeIn();
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
                          $(\'#loading\').fadeOut();
                        }
                     });
                     return false;
                   });
                 });
                 </script>';
    }
} else {
    require_once("class.mailbox.php");
    
    $mbox = new Mailbox($uid, $course_id);
    
    $threads = $mbox->getInboxThreads();
    
    if (empty($threads)) {
        $out = "<p class='alert1'>$langTableEmpty</p>";
    } else {
        $out = "<script>
                  $(document).ready(function() {
                    $('#loading').hide();
                  });
                </script>";
        $out .= "<div id=\"loading\" align=\"center\"><img src=\"".$themeimg."/ajax_loader.gif"."\" align=\"absmiddle\"/>".$langLoading."</div>";
        $out .= "<table id=\"inbox_table\">
                  <thead>
                    <tr>
                      <th>$langSubject</th>
                      <th>$langParticipants</th>
                      <th>$langDelete</th>
                    </tr>
                  </thead>
                  <tbody>";
        
        foreach ($threads as $thread) {
            $participants = '';
            foreach ($thread->recipients as $r) {
                $participants .= uid_to_name($r).', ';
            }
            $participants = substr($participants, 0, strlen($participants)-2);
            $out .= "<tr id='$thread->id'>
                      <td><a href='inbox.php?tid=$thread->id'>$thread->subject</a></td>
                      <td>$participants</td>
                      <td><img src=\"".$themeimg.'/delete.png'."\" class=\"delete\"/></td>
                    </tr>";
        }
        
        $out .= "  </tbody>
                 </table>";
        $out .= "<script>
                   $(document).ready(function() {
                     $('#inbox_table').dataTable();
                   });
                 </script>";
        
        $out .= '<script>
                  $(function() {
                    $(".delete").click(function() {
                      $(\'#loading\').fadeIn();
                      var rowContainer = $(this).parent().parent();
                      var id = rowContainer.attr("id");
                      var string = \'tid=\'+ id ;

                      $.ajax({
                        type: "POST",
                        url: "delete.php",
                        data: string,
                        cache: false,
                        success: function(){
                          rowContainer.slideUp(\'slow\', function() {$(this).remove();});
                          $(\'#loading\').fadeOut();
                        }
                     });
                     return false;
                   });
                 });
                 </script>';
    }
}
echo $out;
    