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

require_once("class.msg.php");
require_once("class.mailbox.php");

$mbox = new Mailbox($uid, $course_id);

$out_msgs = $mbox->getOutboxMsgs();

if (empty($out_msgs)) {
    $out = "<p class='alert1'>$langTableEmpty</p>";
} else {
    $out = "<div class=\"loading\" align=\"center\"><img src=\"".$themeimg."/ajax_loader.gif"."\" align=\"absmiddle\"/>".$langLoading."</div>";
    $out .= "<table id=\"outbox_table\">
               <thead>
                 <tr>
                   <th>$langSubject</th>
                   <th>$langRecipients</th>
                   <th>$langDate</th>
                   <th>$langDelete</th>
                 </tr>
               </thead>
               <tbody>";
    
    foreach ($out_msgs as $m) {
        $recipients = '';
        foreach ($m->recipients as $r) {
            $recipients .= uid_to_name($r).', ';
        }
        $recipients = substr($recipients, 0, strlen($recipients)-2);
        $out .= "<tr id='$m->id'>
                   <td>".q($m->subject)."</td>
                   <td>$recipients</td>
                   <td>".nice_format(date('Y-m-d H:i:s',$m->timestamp), true)."</td>
                   <td><img src=\"".$themeimg.'/delete.png'."\" class=\"delete\"/></td>
                 </tr>";
    }
    
    $out .= "  </tbody>
             </table>";
    $out .= "<script>
               $(document).ready(function() {
                 $('div.loading').hide();
                 $('#outbox_table').dataTable({
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
                                 'sPrevious': '$langPrevious',
                                 'sNext':     '$langNext',
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
                   });
                 </script>';
}

echo $out;