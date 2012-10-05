<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

/*
 * File exchange Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This is responsible for exchanging files between the users of a course
 *
 * Based on code by Jan Bols
 *
 */

include 'functions.php';
$nameTools = $langDropBox;
$diskUsed = dir_total_space($basedir);
/**** The following is added for statistics purposes ***/
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_DROPBOX);
/**************************************/
require_once('dropbox_class.inc.php');

if (isset($_GET['showQuota']) and $_GET['showQuota'] == TRUE) {
	$nameTools = $langQuotaBar;
	$navigation[]= array ("url"=>"$_SERVER[SCRIPT_NAME]?course=$course_code", "name"=> $langDropBox);
	$tool_content .= showquota($diskQuotaDropbox, $diskUsed);
	draw($tool_content, 2);
	exit;
}

$tool_content .="
<div id='operations_container'>
  <ul id='opslist'>
    <li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;upload=1'>$langDownloadFile</a></li>
    <li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;showQuota=TRUE'>$langQuotaBar</a></li>
  </ul>
</div>";


$dropbox_person = new Dropbox_Person($uid, $is_editor);
$dropbox_unid = md5(uniqid(rand(), true));	//this var is used to give a unique value to every
                                                //page request. This is to prevent resubmiting data
/*
 * ========================================
 * FORM UPLOAD FILE
 * ========================================
 */

if(isset($_REQUEST['upload']) && $_REQUEST['upload'] == 1) {
	$tool_content .= "<form method='post' action='dropbox_submit.php?course=$course_code' enctype='multipart/form-data' onsubmit='return checkForm(this)'>";
	$tool_content .= "
	<fieldset>
	<legend>$langDownloadFile</legend>
	<table width='100%' class='tbl'>
	<tr>
	  <th width='160'>$langFileName:</th>
	  <td><input type='file' name='file' size='35' />
	      <input type='hidden' name='dropbox_unid' value='$dropbox_unid' />
	  </td>
	</tr>";

	$tool_content .= "
	<tr>
	  <th>$langSender:</th>
	  <td>".q(uid_to_name($uid))."</td>
	</tr>
	<tr>
	  <th>$langDescription:</th>
	  <td><textarea name='description' cols='37' rows='2'></textarea></td>
	</tr>
	<tr>
	  <th>$langSendTo:</th>
	  <td>
	<select name='recipients[]' multiple='true' class='auth_input' id='select-recipients'>";

	/*
	*  if current user is a teacher then show all users of current course
	*/
	if ($dropbox_person -> isCourseAdmin or $dropbox_cnf["allowStudentToStudent"])  // RH: also if option is set
	{
		// select all users except yourself
		$sql = "SELECT DISTINCT u.user_id user_id, CONCAT(u.nom,' ', u.prenom) AS name
                                FROM user u, course_user cu
			WHERE cu.course_id = $course_id
                                AND cu.user_id = u.user_id
                                AND cu.statut != ".USER_GUEST."
                                AND u.user_id != $uid
                        ORDER BY UPPER(u.nom), UPPER(u.prenom)";
	}
	/*
	* if current user is student then show all teachers of current course
	*/
	else
	{
		// select all the teachers except yourself
		$sql = "SELECT DISTINCT u.user_id user_id, CONCAT(u.nom,' ', u.prenom) AS name
                                FROM user u, course_user cu
			WHERE cu.course_id = $course_id
                                AND cu.user_id = u.user_id
                                AND (cu.statut = ".USER_TEACHER." OR cu.tutor = 1)
                                AND u.user_id != $uid
                        ORDER BY UPPER(u.nom), UPPER(u.prenom)";
	}
	$result = db_query($sql);
	while ($res = mysql_fetch_array($result))
	{
		$tool_content .= "<option value=".$res['user_id'].">".q($res['name'])."</option>";
	}

	$tool_content .= "</select></td></tr>
	<tr>
	  <th>&nbsp;</th>
	  <td class='left'><input type='submit' name='submitWork' value='".q($langSend)."' />&nbsp;
	  $langMailToUsers<input type='checkbox' name='mailing' value='1' /></td>
	</tr>
        </table>
        </fieldset>
	<input type='hidden' name='authors' value='".q(uid_to_name($uid))."' />
        </form>
	<p class='right smaller'>$langMaxFileSize ".ini_get('upload_max_filesize')."</p>";
}

/*
 * --------------------------------------
 * RECEIVED FILES LIST:  TABLE HEADER
 * --------------------------------------
 */
if (!isset($_GET['mailing'])) {
        $numberDisplayed = count($dropbox_person -> receivedWork);
        $tool_content .= "<p class='sub_title1'>$langReceivedFiles";
        // check if there are received documents. If yes then display the icon deleteall
        $dr_unid = urlencode($dropbox_unid);
        if ($numberDisplayed > 0) {                
                $tool_content .= "&nbsp;<a href='dropbox_submit.php?course=$course_code&amp;deleteReceived=all&amp;dropbox_unid=$dr_unid' onClick=\"return confirmationall();\">
                <img src='$themeimg/delete.png' title='".q($langDelete)."' /></a>";
        }
        $tool_content .= "</p>";

 /*
 * --------------------------------------
 * RECEIVED FILES LIST
 * --------------------------------------
 */
        if ($numberDisplayed == 0) {  // RH
                $tool_content .= "<p class='alert1'>$langTableEmpty</p>";
        } else {
                $tool_content .= "
                <script type='text/javascript' src='../auth/sorttable.js'></script>
                <table width='100%' class='sortable' id='t1'>
                <tr>
                 <th colspan='2' class='left' width='200'>$langFileName</th>
                 <th width='130'>$langSender</th>
                 <th width='130'>$langDate</th>
                 <th width='20'>$langDelete</th>
                </tr>";
                $numberDisplayed = count($dropbox_person -> receivedWork);  // RH
                $i = 0;
                foreach ($dropbox_person -> receivedWork as $w) {
                        if ($w -> uploaderId == $uid)  // RH: justUpload
                        {
                                $numberDisplayed -= 1; 
                                continue;
                        }
                        if ($i%2==0) {
                                $tool_content .= "<tr>";
                        } else {
                                $tool_content .= "<tr class='odd'>";
                        }
                        $tool_content .= "<td width='16'>
                        <img src='$themeimg/inbox.png' title='".q($langReceivedFiles)."' /></td>
                        <td>";
                        $tool_content .= "<a href='dropbox_download.php?course=$course_code&amp;id=".urlencode($w->id)."' target=_blank>".$w->title."</a>";                        
                        $tool_content .= "<small>&nbsp;&nbsp;&nbsp;(".format_file_size($w->filesize).")</small><br />" .
                                         "<small>".q($w->description)."</small></td>" .
                                         "<td>$w->author</td><td>".$w->uploadDate;
                        if ($w->uploadDate != $w->lastUploadDate) {
                                $tool_content .= " (".$langlastUpdated." $w->lastUploadDate)";
                        }
                        $tool_content .= "</td><td class='center'>";
                        $tool_content .= "
                        <a href='dropbox_submit.php?course=$course_code&amp;deleteReceived=".urlencode($w->id)."&amp;dropbox_unid=".urlencode($dropbox_unid)."' onClick='return confirmation(\"$w->title\");'>
                        <img src='$themeimg/delete.png' title='".q($langDelete)."' /></a>";
                        $tool_content .= "</td></tr>";
                        $i++;
                } //end of foreach
                $tool_content .= "</table>";
        }
}

/*
 * --------------------------------------
 *SENT FILES LIST:  TABLE HEADER
 * --------------------------------------
 */

$numSent = count($dropbox_person -> sentWork);
$tool_content .= "<br /><p class='sub_title1'>";
$tool_content .= $langSentFiles;
// if the user has sent files then display the icon delete all
if ($numSent > 0) {
        $tool_content .= "&nbsp;<a href='dropbox_submit.php?course=$course_code&amp;deleteSent=all&amp;dropbox_unid=".urlencode($dropbox_unid)."'
        onClick=\"return confirmationall();\">
        <img src='$themeimg/delete.png' title='".q($langDelete)."' />
                </a>";
}
$tool_content .= "</p>";

/*
 * --------------------------------------
 * SENT FILES LIST
 * --------------------------------------
 */

if ($numSent == 0) {
        $tool_content .= "<p class='alert1'>$langTableEmpty</p>";
} else {
       $tool_content .= "
        <script type='text/javascript' src='../auth/sorttable.js'></script>
        <table width=100% class='sortable' id='t2'>
        <tr>
        <th colspan='2' class='left'>$langFileName</th>
        <th width='130'>$langDestination</th>
        <th width='130'>$langDate</th>
        <th width='20'>$langDelete</th>
        </tr>";
        $i = 0;
        foreach ($dropbox_person -> sentWork as $w) {
                $langSentTo .= '&nbsp;';
                $ahref = "dropbox_download.php?course=$course_code&amp;id=" . urlencode($w->id) ;
                $imgsrc = $themeimg . '/outbox.png';               
                if ($i%2 == 0) {
                        $tool_content .= "<tr class='even'>";
                } else {
                        $tool_content .= "<tr class='odd'>";
                }
                $tool_content .= "<td width='16'>
                                <img src='$themeimg/outbox.png' title='".q($w->title)."' /></td>
                                <td><a href='$ahref' target='_blank'>".q($w->title)."</a>
                                <small>&nbsp;&nbsp;&nbsp;(".format_file_size($w->filesize).")</small><br />
                                <small>".q($w->description)."</small></td>";

                $tool_content .= "<td>";
                $recipients_names = '';
                foreach($w -> recipients as $r) {
                        $recipients_names .= q($r['name']) . " <br />";
                }
                if (isset($_GET['d']) and $_GET['d'] == 'all') {
                        $tool_content .= $recipients_names;
                } else {
                        $tool_content .= ellipsize($recipients_names, 89, "<strong>&nbsp;...<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;d=all'> <span class='smaller'>[$langMore]</span></a></strong>");
                }
                $tool_content .= "</td>
                                <td class='center'>$w->uploadDate</td>
                                <td class='center'>
                                <div class='cellpos'>";                
                $tool_content .= "
                <a href='dropbox_submit.php?course=$course_code&amp;deleteSent=".urlencode($w->id)."&amp;dropbox_unid=".urlencode($dropbox_unid) ."'
                        onClick=\"return confirmation('".js_escape($w->title)."');\">
                        <img src='$themeimg/delete.png' title='".q($langDelete)."' /></a>";
                $tool_content .= "</div></td></tr>";

                // RH: Mailing: clickable images for examine and send
                if ($w -> uploadDate != $w->lastUploadDate) {
                        $tool_content .= "<tr><td colspan='2'>
                        <span class='dropbox_detail'>$langlastResent <span class='dropbox_date'>$w->lastUploadDate</span></span></td>
                        </tr>";
                }
                $i++;
        } //end of foreach
        $tool_content .= "</table>";
}
        

// display all user files sent and received (only to course admin)
if ($is_editor) {
        $num = count($dropbox_person->allsentWork);
        if ($num > 0) {                        
                $tool_content .= "<br /><p class='sub_title1'>";
                $tool_content .= $langOtherDropBoxFiles;                
                $tool_content .= "</p>";
                $tool_content .= "
                <script type='text/javascript' src='../auth/sorttable.js'></script>
                <table width=100% class='sortable' id='t2'>
                <tr>
                <th colspan='2' class='left'>$langFileName</th>
                <th width='65'>$langSender</th>
                <th width='65'>$langDestination</th>
                <th width='130'>$langDate</th>
                <th width='20'>$langDelete</th>
                </tr>";
                $i = 0;
                foreach ($dropbox_person -> allsentWork as $w) {                        
                        $langSentTo .= '&nbsp;';
                        $ahref = "dropbox_download.php?course=$course_code&amp;id=" . urlencode($w->id) ;
                        $imgsrc = $themeimg . '/outbox.png';                
                        if ($i%2 == 0) {
                                $tool_content .= "<tr class='even'>";
                        } else {
                                $tool_content .= "<tr class='odd'>";
                        }
                        $tool_content .= "<td width='16'>
                                        <img src='$themeimg/outbox.png' title='".q($w->title)."' /></td>
                                        <td><a href='$ahref' target='_blank'>".q($w->title)."</a>
                                        <small>&nbsp;&nbsp;&nbsp;(".format_file_size($w->filesize).")</small><br />
                                        <small>".q($w->description)."</small></td>";
                        $tool_content .= "<td>";
                        $tool_content .= "$w->uploaderName</td>";
                        $tool_content .= "<td>";
                        $recipients_names = '';                
                        foreach($w -> recipients as $r) {
                                $recipients_names .= q($r['name']) . " <br />";
                        }
                        if (isset($_GET['d']) and $_GET['d'] == 'all') {
                                $tool_content .= $recipients_names;
                        } else {
                                $tool_content .= ellipsize($recipients_names, 89, "<strong>&nbsp;...<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;d=all'> <span class='smaller'>[$langMore]</span></a></strong>");
                        }
                        $tool_content .= "</td>
                                        <td class='center'>$w->uploadDate</td>
                                        <td class='center'>
                                        <div class='cellpos'>";                
                        $tool_content .= "
                        <a href='dropbox_submit.php?course=$course_code&amp;AdminDeleteSent=".urlencode($w->id)."&amp;dropbox_unid=".urlencode($dropbox_unid) ."'
                                onClick='return confirmationpurge();'>
                                <img src='$themeimg/delete.png' title='".q($langDelete)."' /></a>";
                        $tool_content .= "</div></td></tr>";

                        // RH: Mailing: clickable images for examine and send
                        if ($w -> uploadDate != $w->lastUploadDate) {
                                $tool_content .= "<tr><td colspan='2'>
                                <span class='dropbox_detail'>$langlastResent <span class='dropbox_date'>$w->lastUploadDate</span></span></td>
                                </tr>";
                        }
                        $i++;
                } //end of foreach        
                $tool_content .= "</table>";
        }
}

load_js('jquery');
load_js('jquery-ui');
load_js('jquery.multiselect.min.js');
$head_content .= "<script type='text/javascript'>$(document).ready(function () {
        $('#select-recipients').multiselect({
                selectedText: '$langJQSelectNum',
                noneSelectedText: '$langJQNoneSelected',
                checkAllText: '$langJQCheckAll',
                uncheckAllText: '$langJQUncheckAll'
        });
});</script>
<link href='../../js/jquery-ui.css' rel='stylesheet' type='text/css'>
<link href='../../js/jquery.multiselect.css' rel='stylesheet' type='text/css'>";

draw($tool_content, 2, null, $head_content);
