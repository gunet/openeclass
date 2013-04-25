<?php

/* ========================================================================
 * Open eClass 2.6
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

$require_login = true;
$require_current_course = TRUE;
$require_course_admin = TRUE;

include '../../include/baseTheme.php';
include '../../include/lib/textLib.inc.php';
include '../../include/sendMail.inc.php';

$head_content .= '<script type="text/javascript">var langEmptyGroupName = "' .
			 $langEmptyAnTitle . '";</script>';

if (isset($_REQUEST['id'])) {
        $id = $_REQUEST['id'];
}
$nameTools = $langEmailToParent;
$navigation[] = array("url" => "user.php?course=$code_cours", "name" => $langAdminUsers);

$nameTools = $langContactWithParent;

if (isset($_POST['submit'])) {
        list($uid, $email) = mysql_fetch_row(db_query("SELECT user_id, parent_email FROM user WHERE user_id = $id"));

	$subject = $_POST['subject'];
        $body_html = $_POST['content'];
        $body_plain = html2text($_POST['content']);
        $from_name = "$_SESSION[nom] $_SESSION[prenom]";
        $from_address = $_SESSION['email'];    
        $to = "$langParentOf " . uid_to_name($uid);
        	
        if (!send_mail_multipart($from_name, $from_address, $to, $email, $subject, $body_plain, $body_html, $charset)) {
                $tool_content .= "<p class='alert1'>$langErrorSendingMessage</p>\n";
        } else {
                $tool_content .= "<div class='success'>$emailsuccess</div>";
        }
        db_query("INSERT INTO parents_announcements SET title = " .quote($_POST['subject']). ",
                                                        content = " .quote($_POST['content']). ",
                                                        date = NOW(),
                                                        sender_id = $_SESSION[uid],
                                                        recipient_id = $uid,
                                                        course_id = ".course_code_to_id($_GET['course'])."");
        

} else {
	$tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$code_cours' onsubmit=\"return checkrequired(this, 'subject');\">
	<fieldset>
	<legend>$langIntroMessage</legend>
	<table class='tbl' width='100%'>
	<tbody>
        <tr>
          <th>$langSubject</th>
        </tr>	
        <tr>
           <td><input type = 'text' name='subject' size='50' /></td>
        </tr>
        <tr>
          <th>$langEmailBody</th>
        </tr>
	<tr>
          <td>".@rich_text_editor('content', 4, 20, $emailcontent)."</td>
        </tr>
	<tr>
	  <td class='right'><input type='submit' name='submit' value='".q($langSendMessage)."' /></td>
	</tr>
        <input type = 'hidden' name='id' value='$id'>
	</tbody>
	</table>
	</fieldset>
	</form>";
}

$sql = db_query("SELECT title, content, `date` FROM parents_announcements 
                WHERE course_id = ".course_code_to_id($_GET['course'])."
                AND sender_id = $_SESSION[uid]
                AND recipient_id = $id");

$tool_content .= "<table width='100%' class='sortable'>";
$tool_content .= "<tr><th colspan='2'>$langSentItems ".uid_to_name($id)."</th></tr>";
$k = 0;
while ($r = mysql_fetch_array($sql)) {
        if ($k % 2 == 0) {
                $tool_content .= "<tr class='even'>";
        } else {
                $tool_content .= "<tr class='odd'>";
        }
        $tool_content .= "<td width='16' valign='top'>
			<img style='padding-top:3px;' src='$themeimg/arrow.png' alt=''></td>";
        $d = claro_format_locale_date($dateFormatLong, strtotime($r['date']));
        $tool_content .= "<td><b>$r[title]</b>
                <div class='smaller'>" . $d. "</div>
                ".standard_text_escape($r['content'])."</td></tr>";
        $k++;
}
$tool_content .= "</table>";
draw($tool_content, 2, null, $head_content);