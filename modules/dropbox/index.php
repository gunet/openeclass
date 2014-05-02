<?php
/* ========================================================================
 * Open eClass 2.10
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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


require_once("functions.php");
$nameTools = $langDropBox;
$basedir = $webDir . 'courses/' . $currentCourseID . '/dropbox';
$diskUsed = dir_total_space($basedir);
$displayall = false;
$display_outcoming = false;
$is_tutor = FALSE;

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_DROPBOX');
/**************************************/

require_once('dropbox_class.inc.php');

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

if (isset($_GET['showQuota']) and $_GET['showQuota'] == TRUE) {
	$nameTools = $langQuotaBar;
	$navigation[]= array ("url"=>"$_SERVER[SCRIPT_NAME]?course=$code_cours", "name"=> $langDropBox);
	$tool_content .= showquota($diskQuotaDropbox, $diskUsed);
	draw($tool_content, 2);
	exit;
}
if ($is_editor) {
    if (isset($_GET['other']) and $_GET['other']) {
        $displayall = true;    
        if (isset($_GET['id'])) {
            $messagebody = true;
            $r_message_id = intval($_GET['id']);
            $dropbox_person = new Dropbox_Person($uid, false);
        }
    }
}

if (isset($_GET['s']) and $_GET['s']) {
    $display_outcoming = true;
    $messagebody = false;
    $dropbox_person = new Dropbox_Person($uid);
    if (isset($_GET['sm_id'])) {
        $messagebody = true;
        $s_message_id = intval($_GET['sm_id']);
        $navigation[]= array ("url"=>"$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;s=1", "name"=> $dropbox_lang['sentTitle']);
        $dropbox_person = new Dropbox_Person($uid, true, false);
    }
} else {
    $messagebody = false;
    $dropbox_person = new Dropbox_Person($uid);
    if (isset($_GET['rm_id'])) {
        $messagebody = true;
        $r_message_id = intval($_GET['rm_id']);
        $navigation[]= array ("url"=>"$_SERVER[SCRIPT_NAME]?course=$code_cours", "name"=> $dropbox_lang['receivedTitle']);
        $dropbox_person = new Dropbox_Person($uid, false, true);
    }   
}

$tool_content .= "
<div id='operations_container'>
  <ul id='opslist'>
    <li><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;upload=1'>$dropbox_lang[uploadFile]</a></li>";
if ($display_outcoming) {
    $tool_content .= "<li><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours'>$dropbox_lang[receivedTitle]</a></li>";
} else {
    $tool_content .= "<li><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;s=1'>$dropbox_lang[sentTitle]</a></li>";
}
if ($is_editor) {      
        $tool_content .= "<li><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;other=1'>$langOtherDropBoxFiles</a></li>";       
}
$tool_content .= " <li><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;showQuota=TRUE'>$langQuotaBar</a></li>
  </ul>
</div>";

$dropbox_unid = md5(uniqid(crypto_rand_secure(), true)); //this var is used to give a unique value to every
                                                         //page request. This is to prevent resubmiting data
/*
 * ========================================
 * FORM UPLOAD FILE
 * ========================================
 */

if(isset($_REQUEST['upload']) && $_REQUEST['upload'] == 1) {
        if (isset($_GET['group_id'])) {            
            $group_id = intval($_GET['group_id']);            
            $tutor_id = db_query_get_single_value("SELECT is_tutor FROM group_members WHERE group_id = $group_id AND user_id = $uid", $mysqlMainDb);            
            $is_tutor = ($tutor_id == 1)?TRUE:FALSE;
        }
                
	$tool_content .= "<form method='post' action='dropbox_submit.php?course=$code_cours' enctype='multipart/form-data' onsubmit='return checkForm(this)'>";
	$tool_content .= "
            <fieldset>
            <legend>".$dropbox_lang['uploadFile']."</legend>
            <table width='100%' class='tbl'>
            <tr>
              <th>".$langSender.":</th>
              <td>".q(uid_to_name($uid))."</td>
            </tr>";
            @$tool_content .= "<tr>
              <th width='120'>".$langTitle.":</th>
              <td><input type='input' name='title' size='50' value='$title' />	      
              </td>
            </tr>";
            @$tool_content .= "<tr>
              <th>".$langMessage.":</th>
              <td>".rich_text_editor('description', 4, 20, $description)."
              <small>&nbsp;&nbsp;$langMaxMessageSize</small></td>
            </tr>
            <tr>
              <th width='120'>".$langFileName.":</th>
              <td><input type='file' name='file' size='35' />
                  <input type='hidden' name='dropbox_unid' value='$dropbox_unid' />
              </td>
            </tr>
            <tr>
              <th>".$langSend.":</th>
              <td>
            <select name='recipients[]' multiple='true' class='auth_input' id='select-recipients'>";
	
        if (isset($group_id) and ($is_editor or $is_tutor)) { // if we come from groups and user is tutor show only his group              
                $row = db_query_get_single_row("SELECT id, name FROM `group` WHERE course_id = $cours_id AND id = $group_id", $mysqlMainDb);
                $tool_content .= "<option value = '_$row[id]' selected>".q($row['name'])."</option>";
        } else {
            if ($is_editor or $dropbox_cnf["allowStudentToStudent"]) { // if user is a teacher then show all users of current course
		$sql = "SELECT DISTINCT u.user_id , CONCAT(u.nom,' ', u.prenom) AS name
			FROM user u, cours_user cu
			WHERE cu.cours_id = $cours_id
				AND cu.user_id = u.user_id 
				AND cu.statut != 10
				AND u.user_id != $uid
				ORDER BY UPPER(u.nom), UPPER(u.prenom)";
                // also select all course groups if exist
                $sql_g = "SELECT id, name FROM `group` WHERE course_id = $cours_id";                
                $result_g = db_query($sql_g, $mysqlMainDb);
                while ($res_g = mysql_fetch_array($result_g))
                {
                    $tool_content .= "<option value = '_$res_g[id]'>".q($res_g['name'])."</option>";
                }	                 
            } else {
                    // if user is tutor show its group
                    $s = db_query("SELECT group_id, is_tutor FROM group_members WHERE user_id = $uid", $mysqlMainDb);
                    while ($r = mysql_fetch_array($s)) {
                        if ($r['is_tutor'] == 1) {
                            $row = db_query_get_single_row("SELECT id, name FROM `group` WHERE course_id = $cours_id and id = $r[group_id]", $mysqlMainDb);
                            $tool_content .= "<option value = '_$row[id]'>".q($row['name'])."</option>";
                        }                        
                    }
                    // if user is student then show all teachers of current course
                    $sql = "SELECT DISTINCT u.user_id , CONCAT(u.nom,' ', u.prenom) AS name
                            FROM user u, cours_user cu
                            WHERE cu.cours_id = $cours_id
                                    AND cu.user_id = u.user_id
                                    AND (cu.statut <> 5 OR cu.tutor = 1)
                                    AND u.user_id != $uid
                                    ORDER BY UPPER(u.nom), UPPER(u.prenom)";
            }

            $result = db_query($sql, $mysqlMainDb);
            while ($res = mysql_fetch_array($result))
            {
                    $tool_content .= "<option value = ".$res['user_id'].">".q($res['name'])."</option>";
            }
        }	
	$tool_content .= "</select></td></tr>
	<tr>
	  <th>&nbsp;</th>
	  <td class='left'><input type='submit' name='submitWork' value='".q($langSend)."' />&nbsp;
	  $dropbox_lang[mailtousers]<input type='checkbox' name='mailing' value='1' checked /></td>
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
if (!$displayall) {
    if (!$display_outcoming) {    
            if (!isset($_GET['mailing'])) {
                    $numberDisplayed = count($dropbox_person -> receivedWork);
                    $tool_content .= "<p class='sub_title1'>".$dropbox_lang['receivedTitle'];
                    // check if there are received documents. If yes then display the icon deleteall
                    $dr_unid = urlencode($dropbox_unid);
                    if ($numberDisplayed > 0) {
                            $dr_lang_all = addslashes($dropbox_lang["all"]);
                            $tool_content .= "&nbsp;<a href='dropbox_submit.php?course=$code_cours&amp;deleteReceived=all&amp;dropbox_unid=$dr_unid' onClick=\"return confirmation();\">
                            <img src='$themeimg/delete.png' title='".q($langDelete)."' /></a>";
                    }
                    $tool_content .= "</p>";

             /*
             * --------------------------------------
             * RECEIVED FILES LIST
             * --------------------------------------
             */
                    if ($numberDisplayed == 0) {
                            $tool_content .= "<p class='alert1'>".$dropbox_lang['tableEmpty']."</p>";
                    } else {
                            $tool_content .= "
                            <script type='text/javascript' src='../auth/sorttable.js'></script>
                            <table width='100%' class='sortable' id='t1'>
                            <tr>
                             <th colspan='2' class='left' width='200'>$dropbox_lang[file]</th>
                             <th width='130'>$langSender</th>
                             <th width='100'>$langDate</th>
                             <th width='20'>$langDelete</th>
                            </tr>";
                            $numberDisplayed = count($dropbox_person -> receivedWork);
                            $i = 0;                    
                            foreach ($dropbox_person -> receivedWork as $w) {
                                    if ($w -> uploaderId == $uid)
                                    {
                                            $numberDisplayed -= 1; 
                                            continue;
                                    }
                                    if ($i%2==0) {
                                            $tool_content .= "<tr>";
                                    } else {
                                            $tool_content .= "<tr class='odd'>";
                                    }                                                        
                                    $tool_content .= "<td width='16'><img src='$themeimg/message.png' title='".q($w->title)."' /></td>";
                                    $tool_content .= "<td><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;rm_id=$w->id'>".q($w->title)."</a>";
                                    if (($w->filename != '') and ($w->filesize != 0)) {
                                            $tool_content .= "&nbsp;&nbsp;<a href='dropbox_download.php?course=$code_cours&amp;id=".urlencode($w->id)."' target=_blank>"
                                                          . "<img src='$themeimg/inbox.png' /><small>$langAttachedFile</small>"
                                                          . "</a>"
                                                          . "<span class='smaller'>&nbsp;&nbsp;&nbsp;(".format_file_size($w->filesize).")</span><br />";    
                                    }
                                    $tool_content .= "<br /><small>";
                                    if (!$messagebody) {
                                            $tool_content .= standard_text_escape(ellipsize_html($w->description, 50, "<strong>&nbsp;...<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;rm_id=$w->id'>[$langMore]</span></a></strong>"));
                                    } else {
                                            $tool_content .= standard_text_escape($w->description);
                                    }
                                    $tool_content .= "</small></td>";                            
                                    $tool_content .= "<td><small>".display_user($w->uploaderId, false, false)."</small></td>";
                                    $tool_content .= "<td><small>".$w->uploadDate;
                                    if ($w->uploadDate != $w->lastUploadDate) {
                                            $tool_content .= " (".$dropbox_lang['lastUpdated']." $w->lastUploadDate)";
                                    }
                                    $tool_content .= "</small></td><td class='center'>";
                                    $tool_content .= "
                                        <a href=\"dropbox_submit.php?course=$code_cours&amp;deleteReceived=".urlencode($w->id)."&amp;dropbox_unid=".urlencode($dropbox_unid)."\" onClick='return confirmation();'>
                                        <img src='$themeimg/delete.png' title='".q($langDelete)."' /></a>";
                                    $tool_content .= "</td></tr>";
                                    $i++;
                            } //end of foreach
                            $tool_content .= "</table>";
                    }
            }                  
        /*
         * --------------------------------------
         * SENT FILES LIST:  TABLE HEADER
         * --------------------------------------
         */
    } else {          
            $numSent = count($dropbox_person -> sentWork);
            $tool_content .= "<p class='sub_title1'>";
            $tool_content .= $dropbox_lang["sentTitle"];
            // if the user has sent files then display the icon deleteall
            if ($numSent > 0) {
                    $tool_content .= "&nbsp;<a href='dropbox_submit.php?course=$code_cours&amp;deleteSent=all&amp;dropbox_unid=".urlencode($dropbox_unid)."'
                    onClick=\"return confirmation();\"><img src='$themeimg/delete.png' title='".q($langDelete)."' /></a>";
            }
            $tool_content .= "</p>";

            /*
             * --------------------------------------
             * SENT FILES LIST
             * --------------------------------------
             */

            if ($numSent == 0) {
                    $tool_content .= "<p class='alert1'>".$dropbox_lang['tableEmpty']."</p>";
            } else {
                   $tool_content .= "
                    <script type='text/javascript' src='../auth/sorttable.js'></script>
                    <table width=100% class='sortable' id='t2'>
                    <tr>
                    <th colspan='2' class='left'>$dropbox_lang[file]</th>
                    <th width='130'>$dropbox_lang[col_recipient]</th>
                    <th width='100'>$langDate</th>
                    <th width='20'>$langDelete</th>
                    </tr>";

                    $i = 0;
                    foreach ($dropbox_person -> sentWork as $w) {
                            $langSentTo = $dropbox_lang["sentTo"] . '&nbsp;';  
                            $ahref = "dropbox_download.php?course=$code_cours&amp;id=" . urlencode($w->id) ;               
                            if ($i%2 == 0) {
                                    $tool_content .= "<tr class='even'>";
                            } else {
                                    $tool_content .= "<tr class='odd'>";
                            }
                            $tool_content .= "<td width='16'><img src='$themeimg/message.png' title='".q($w->title)."' /></td>";
                            $tool_content .= "<td><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;s=1&amp;sm_id=$w->id'>".q($w->title)."</a>";
                            if (($w->filename != '') and ($w->filesize != 0)) {
                                    $tool_content .= "&nbsp;&nbsp;<a href='$ahref' target='_blank'><img src='$themeimg/inbox.png' /><small>$langAttachedFile</small></a>
                                            <span class='smaller'>&nbsp;&nbsp;&nbsp;(".format_file_size($w->filesize).")</span><br />";
                            }
                            $tool_content .= "<br /><small>";
                            if (!$messagebody) {
                                    $tool_content .= standard_text_escape(ellipsize_html($w->description, 50, "<strong>&nbsp;...<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;s=1&amp;sm_id=$w->id'>[$langMore]</span></a></strong>"));
                            } else {
                                    $tool_content .= standard_text_escape($w->description);
                            }
                            $tool_content .= "</small></td>";
                            $tool_content .= "<td>";
                            $recipients_names = '';                
                            foreach($w -> recipients as $r) {
                                    $recipients_names .= display_user($r['id'], false, false) . " <br />";
                            }
                            if (isset($_GET['d']) and $_GET['d'] == 'all') {
                                    $tool_content .= "<small>$recipients_names</small>";
                            } else {
                                    $tool_content .= "<small>".ellipsize_html($recipients_names, 50, "<strong>&nbsp;...<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;d=all'> <span class='smaller'>[$langMore]</span></a></strong>")."</small>";
                            }
                            $tool_content .= "</td>
                                            <td><small>$w->uploadDate</small></td>
                                            <td class='center'>
                                            <div class='cellpos'>";
                            //<!--	Users cannot delete their own sent files -->

                            $tool_content .= "
                            <a href=\"dropbox_submit.php?course=$code_cours&amp;deleteSent=".urlencode($w->id)."&amp;dropbox_unid=".urlencode($dropbox_unid) ."\"
                                    onClick=\"return confirmation();\">
                                    <img src='$themeimg/delete.png' title='".q($langDelete)."' /></a>";
                            $tool_content .= "</div></td></tr>";

                            if ($w -> uploadDate != $w->lastUploadDate) {
                                    $tool_content .= "<tr><td colspan='2'>
                                    <span class='dropbox_detail'>".$dropbox_lang["lastResent"]." <span class=\"dropbox_date\">$w->lastUploadDate</span></span></td>
                                    </tr>";
                            }
                            $i++;
                    } //end of foreach
                    $tool_content .= "</table>";
            }           
        }  
} else { // display all user files sent and received (only to course admin)
    $num = count($dropbox_person->allsentWork);
    if ($num > 0) {                        
            $tool_content .= "<br /><p class='sub_title1'>";
            $tool_content .= $langOtherDropBoxFiles;                
            $tool_content .= "</p>";

            $tool_content .= "
            <script type='text/javascript' src='../auth/sorttable.js'></script>
            <table width=100% class='sortable' id='t2'>
            <tr>
            <th colspan='2' class='left'>$dropbox_lang[file]</th>
            <th width='65'>$langSender</th>
            <th width='65'>$dropbox_lang[col_recipient]</th>
            <th width='100'>$langDate</th>
            <th width='20'>$langDelete</th>
            </tr>";
            $i = 0;
            foreach ($dropbox_person -> allsentWork as $w) {                            
                    $langSentTo = $dropbox_lang["sentTo"] . '&nbsp;';
                    $ahref = "dropbox_download.php?course=$code_cours&amp;id=" . urlencode($w->id);
                    if ($i%2 == 0) {
                            $tool_content .= "<tr class='even'>";
                    } else {
                            $tool_content .= "<tr class='odd'>";
                    }
                    $tool_content .= "<td width='16'><img src='$themeimg/message.png' title='".q($w->title)."' /></td>";
                    $tool_content .= "<td><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;other=1&amp;a_id=$w->id'>".q($w->title)."</a>";
                    if (($w->filename != '') and ($w->filesize != 0)) {
                            $tool_content .= "&nbsp;&nbsp;<a href='$ahref' target='_blank'><small>$langAttachedFile</small><img src='$themeimg/inbox.png' /></a>
                                            <span class='smaller'>&nbsp;&nbsp;&nbsp;(".format_file_size($w->filesize).")</span><br />";
                    } 
                    $tool_content .= "<br /><small>";
                    if (!$messagebody) {
                        $tool_content .= standard_text_escape(ellipsize_html($w->description, 50, "<strong>&nbsp;...<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;other=1&amp;a_id=$w->id'>[$langMore]</span></a></strong>"));
                    } else {
                        $tool_content .= standard_text_escape($w->description);
                    }
                    $tool_content .= "</small></td>";
                    $tool_content .= "<td><small>".display_user($w->uploaderId, false, false)."</small></td>";
                    $tool_content .= "<td>";
                    $recipients_names = '';                
                    foreach($w -> recipients as $r) {                                
                            $recipients_names .= display_user($r['id'], false, false) . " <br />";
                    }
                    if (isset($_GET['d']) and $_GET['d'] == 'all') {
                            $tool_content .= "<small>$recipients_names</small>";
                    } else {
                            $tool_content .= "<small>".ellipsize_html($recipients_names, 50, "<strong>&nbsp;...<a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;d=all'> <span class='smaller'>[$langMore]</span></a></strong>")."</small>";
                    }
                    $tool_content .= "</td>
                                    <td><small>$w->uploadDate</small></td>
                                    <td class='center'>
                                    <div class='cellpos'>";                
                    $tool_content .= "
                    <a href=\"dropbox_submit.php?course=$code_cours&amp;AdminDeleteSent=".urlencode($w->id)."&amp;dropbox_unid=".urlencode($dropbox_unid) ."\"
                            onClick=\"return confirmationpurge();\">
                            <img src='$themeimg/delete.png' title='".q($langDelete)."' /></a>";
                    $tool_content .= "</div></td></tr>";
                    if ($w -> uploadDate != $w->lastUploadDate) {
                            $tool_content .= "<tr><td colspan='2'>
                            <span class='dropbox_detail'>".$dropbox_lang["lastResent"]." <span class='dropbox_date'>$w->lastUploadDate</span></span></td>
                            </tr>";
                    }
                    $i++;
            } //end of foreach        
            $tool_content .= "</table>";
    }
}
draw($tool_content, 2, null, $head_content);
