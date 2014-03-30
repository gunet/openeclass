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
        $urlstr = '';
        if ($course_id != 0) {
            $urlstr = "?course=".$course_code;
        }
        $out .= "<div style=\"float:right;\"><a href=\"inbox.php".$urlstr."\">$langBack</a></div>";
        $out .= "<h2>$langSubject: $thread->subject</h2><br/>";
        if ($thread->course_id != 0 && $course_id == 0) {
            $out .= "<p class=\"tags\"><span class=\"st_tag\"><a class=\"outtabs\" href=\"index.php?course=".course_id_to_code($thread->course_id)."\">".course_id_to_title($thread->course_id)."</a></span></p><br/>";
        }
        $out .= "<table id=\"thread_table\">
                  <thead>
                    <tr>
                      <th>$langDate</th>
                      <th>$langSender</th>
                      <th>$langMessage</th>";
        if ($course_id != 0) {
            $out .= " <th>$langAttachedFile</th>";
        }
        $out .= "     <th>$langDelete</th>
                    </tr>
                  </thead>
                  <tbody>";
        
        foreach ($msgs as $m) {
            $out .= "<tr id='$m->id'>
                       <td>".nice_format(date('Y-m-d H:i:s',$m->timestamp), true)."</td>
                       <td>".uid_to_name($m->author_id)."</td>
                       <td>".standard_text_escape($m->body)."</td>";
            if ($course_id != 0) {
                $out .= "<td><a href=\"dropbox_download.php?course=$course_code&amp;id=$m->id\" class=\"outtabs\" target=\"_blank\">$m->real_filename</a></td>";
            }
            $out .= "  <td><img src=\"".$themeimg.'/delete.png'."\" class=\"delete\"/></td>        
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
                    <tr>";
        if ($course_id != 0) {
            $out .= "<th>$langCourse</th>";
        }
        $out .= "     <th>$langSubject</th>
                      <th>$langParticipants</th>
                      <th>$langDelete</th>
                    </tr>
                  </thead>
                  <tbody>";
        
        foreach ($threads as $thread) {
            $participants = '';
            foreach ($thread->recipients as $r) {
                $participants .= uid_to_name($r).'<br/>';
            }
            $participants = substr($participants, 0, strlen($participants)-5);
            $urlstr = '';
            if ($course_id != 0) {
                $urlstr = "&amp;course=".$course_code;
            }
            $out .= "<tr id='$thread->id'>";
            if ($course_id != 0) {
                $out .= "<td><a class=\"outtabs\" href=\"index.php?course=".course_id_to_code($thread->course_id)."\">".course_id_to_title($thread->course_id)."</a></td>";
            }
            $out .= " <td><a href='inbox.php?tid=$thread->id".$urlstr."'>$thread->subject</a></td>
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
    