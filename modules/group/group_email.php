<?php
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/
/*
 * Groups Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This module is responsible for the user groups of each lesson
 *
 */

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Group';

include '../../include/baseTheme.php';
include('../../include/sendMail.inc.php');

$nameTools = $langGroupMail;
$navigation[]= array ("url"=>"group.php", "name"=> $langGroupSpace,
"url"=>"group_space.php?userGroupId=$userGroupId", "name"=>$langGroupSpace);

$userGroupId = intval($_REQUEST['userGroupId']);
list($tutor_id) = mysql_fetch_row(db_query("SELECT tutor FROM student_group WHERE id='$userGroupId'", $currentCourseID));
$is_tutor = ($tutor_id == $uid);
if (!$is_adminOfCourse and !$is_tutor) {
        header('Location: group_space.php?userGroupId=' . $userGroupId);
        exit;
}

$tool_content = "";
$currentCourse = $dbname;

if ($is_adminOfCourse or $is_tutor)  {
	if (isset($submit)) {
                $sender = mysql_fetch_array(db_query("SELECT email, nom, prenom FROM user WHERE user_id = $uid", $mysqlMainDb));
                $sender_name = $sender['prenom'] . ' ' . $sender['nom'];
                $sender_email = $sender['email'];
                $emailsubject = $intitule." - ".$subject;
                $emailbody = "$body_mail\n\n$l_poster: $sender[nom] $sender[prenom] <$sender[email]>\n$langProfLesson\n";

		$req = mysql_query("SELECT user FROM `$dbname`.user_group WHERE team = '$userGroupId'");
		while ($userid = mysql_fetch_array($req)) {
                        $r = db_query("SELECT email FROM user where user_id='$userid[0]'", $mysqlMainDb);
			list($email) = mysql_fetch_array($r);
			if (email_seems_valid($email) and
                            !send_mail($sender_name, $sender_email,
                                       '', $email,
                                       $emailsubject, $emailbody, $charset)) {
                                $tool_content .= "<h4>$langMailError</h4>";
			}
		}
		$tool_content .= "<p class='success_small'>$langEmailSuccess<br />";
		$tool_content .= "<a href='group.php'>$langBack</a></p>";
		$tool_content .= "<p>&nbsp;</p>";
	} else {

		$tool_content .= <<<tCont

  <form action="$_SERVER[PHP_SELF]" method="post">
  <input type="hidden" name="userGroupId" value="$userGroupId">
    <table width="99%" class="FormData">
    <thead>
    <tr>
      <th width="220">&nbsp;</th>
      <td><b>$langTypeMessage</b></td>
    </tr>
    <tr>
      <th class="left">$langMailSubject</th>
      <td><input type="text" name="subject" size="58" class="FormData_InputText"></input></td>
    </tr>
    <tr>
      <th class="left" valign="top">$langMailBody</th>
      <td><textarea name="body_mail" rows="10" cols="73" class="FormData_InputText"></textarea></td>
    </tr>
    <tr>
      <th>&nbsp;</th>
      <td><input type="submit" name="submit" value="$langSend"></input></td>
    </tr>
    </thead>
    </table>
   <br />
   </form>


tCont;
        }

}

draw($tool_content, 2, 'group');
