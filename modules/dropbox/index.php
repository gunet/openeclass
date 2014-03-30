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
$require_help = TRUE;
$helpTopic = 'Dropbox';

include '../../include/baseTheme.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';

if (!isset($course_id)) {
    $course_id = 0;
}

if ($course_id != 0) {
    $dropbox_dir = $webDir . "/courses/" . $course_code . "/dropbox";
    if (!is_dir($dropbox_dir)) {
        mkdir($dropbox_dir);
    }
    
    // get dropbox quotas from database
    $d = Database::get()->querySingle("SELECT dropbox_quota FROM course WHERE code = ?s", $course_code);
    $diskQuotaDropbox = $d->dropbox_quota;
    $diskUsed = dir_total_space($dropbox_dir);
}

// javascript functions
$head_content = '<script type="text/javascript">
                    function checkForm (frm) {
                        if (frm.elements["recipients[]"].selectedIndex < 0) {
                                alert("' . $langNoUserSelected . '");
                                return false;
                        } else {
                                return true;
                        }
                    }
                </script>';

$nameTools = $langDropBox;

if ($course_id != 0) {
    $tool_content .="
    <div id='operations_container'>
      <ul id='opslist'>
        <li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;upload=1'>$langNewMessage</a></li>
        <li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;showQuota=TRUE'>$langQuotaBar</a></li>
      </ul>
    </div>";
} else {
    $tool_content .="
    <div id='operations_container'>
      <ul id='opslist'>
        <li><a href='$_SERVER[SCRIPT_NAME]?upload=1'>$langNewMessage</a></li>
      </ul>
    </div>";
}

if (isset($_GET['course']) and isset($_GET['showQuota']) and $_GET['showQuota'] == TRUE) {
    $nameTools = $langQuotaBar;
    $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langDropBox);
    $tool_content .= showquota($diskQuotaDropbox, $diskUsed);
    draw($tool_content, 2);
    exit;
}

load_js('jquery');
load_js('jquery-ui');

if (isset($_REQUEST['upload']) && $_REQUEST['upload'] == 1) {//new message form
    if ($course_id == 0) {
        $tool_content .= "<form method='post' action='dropbox_submit.php' enctype='multipart/form-data' onsubmit='return checkForm(this)'>";
    } else {
        $tool_content .= "<form method='post' action='dropbox_submit.php?course=$course_code' enctype='multipart/form-data' onsubmit='return checkForm(this)'>";
    }
    $tool_content .= "
	<fieldset>
	<table width='100%' class='tbl'>
        <tr>
	  <th>$langSender:</th>
	  <td>" . q(uid_to_name($uid)) . "</td>
	</tr>";
    @$tool_content .= "<tr>
        <th width='120'>" . $langTitle . ":</th>
        <td><input type='input' name='message_title' size='50' value='$message_title' />	      
        </td>
        </tr>";
    @$tool_content .= "<tr>
              <th>" . $langMessage . ":</th>
              <td>".rich_text_editor('body', 4, 20, $description)."
              <small>&nbsp;&nbsp;$langMaxMessageSize</small></td>           
            </tr>";
    $tool_content .= "<tr>
	  <th width='120'>$langFileName:</th>
	  <td><input type='file' name='file' size='35' />	     
	  </td>
	</tr>
	<tr>
	  <th>$langSendTo:</th>
	  <td>
	<select name='recipients[]' multiple='true' class='auth_input' id='select-recipients'>";

    if ($course_id != 0) {//course messages
        //select all users from this course except yourself
        $sql = "SELECT DISTINCT u.id user_id, CONCAT(u.surname,' ', u.givenname) AS name
                FROM user u, course_user cu
			    WHERE cu.course_id = ?d
                AND cu.user_id = u.id
                AND cu.status != ?d
                AND u.id != ?d
                ORDER BY UPPER(u.surname), UPPER(u.givenname)";
        $res = Database::get()->queryArray($sql, $course_id, USER_GUEST, $uid);
    } else {//personal messages
        //select all users that follow the same courses as you 
        $sql = "SELECT DISTINCT u.id user_id, CONCAT(u.surname,' ', u.givenname) AS name
                FROM user u, course_user cu
			    WHERE cu.course_id IN (SELECT course_id FROM course_user WHERE user_id = ?d)
                AND cu.user_id = u.id
                AND cu.status != ?d
                AND u.id != ?d
                ORDER BY UPPER(u.surname), UPPER(u.givenname)";
        $res = Database::get()->queryArray($sql, $uid, USER_GUEST, $uid);
    }
    foreach ($res as $r) {
        $tool_content .= "<option value=" . $r->user_id . ">" . q($r->name) . "</option>";
    }

    $tool_content .= "</select></td></tr>
	<tr>
	  <th>&nbsp;</th>
	  <td class='left'><input type='submit' name='submit' value='" . q($langSend) . "' />&nbsp;
	  $langMailToUsers<input type='checkbox' name='mailing' value='1' checked /></td>
	</tr>
        </table>
        </fieldset>	
        </form>
	<p class='right smaller'>$langMaxFileSize " . ini_get('upload_max_filesize') . "</p>";
    
    load_js('jquery.multiselect.min.js');
    $head_content .= "<script type='text/javascript'>$(document).ready(function () {
            $('#select-recipients').multiselect({
                selectedText: '$langJQSelectNum',
                noneSelectedText: '$langJQNoneSelected',
                checkAllText: '$langJQCheckAll',
                uncheckAllText: '$langJQUncheckAll'
            });
    });</script>
    <link href='../../js/jquery.multiselect.css' rel='stylesheet' type='text/css'>";
} else {//mailbox
    load_js('datatables');
    $head_content .= "<script>
		              $(function() {
		                $( \"#tabs\" ).tabs({
		                  collapsible: false,
                          //cache tab and avoid reload
                          beforeLoad: function( event, ui ) {
                            if ( ui.tab.data( \"loaded\" ) ) {
                              event.preventDefault();
                              return;
                            }
                            ui.jqXHR.success(function() {
                              ui.tab.data( \"loaded\", true );
                            });
                          },
                          //open links inside tabs
                          load: function(event, ui) {
                            $(\".ui-tabs-panel.ui-widget-content\").delegate('a', 'click', function(event) {
                              if (event.target.className != 'outtabs') {
                                event.preventDefault();
                                $(this).closest('.ui-tabs-panel.ui-widget-content').load(this.href);
                              }
                            });
                          }
                         })
                      })
                      </script>";
    if ($course_id == 0) {
        $tool_content .= "<div id=\"tabs\">
                           <ul>
                             <li><a href=\"inbox.php\">Inbox</a></li>
                             <li><a href=\"outbox.php\">Outbox</a></li>
                           </ul>
                         </div>";
    } else {
        $tool_content .= "<div id=\"tabs\">
                           <ul>
                             <li><a href=\"inbox.php?course=$course_code\">Inbox</a></li>
                             <li><a href=\"outbox.php?course=$course_code\">Outbox</a></li>
                           </ul>
                         </div>";
    }
}

if ($course_id == 0) {
    draw($tool_content, 1, null, $head_content);
} else {
    draw($tool_content, 2, null, $head_content);
}
