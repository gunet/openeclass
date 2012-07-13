<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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


/**===========================================================================
	contactadmin.php
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================        
  @Description: Contact the admin with an e-mail message
  when an account has been deactivated

  This script allows a user the send an e-mail to the admin, requesting
  the re-activation of his/her account
==============================================================================
*/

require_once '../../include/baseTheme.php';
require_once '../../include/sendMail.inc.php';
$nameTools = $langContactAdmin;

$userid = isset($_GET['userid'])? intval($_GET['userid']): 0;

if ($userid and isset($_GET['h']) and
    token_validate("userid=$userid", $_GET['h'])) {
	$q = db_query("SELECT * FROM user WHERE user_id = ".$userid);
        if ($q and mysql_num_rows($q) > 0) {
                $info = mysql_fetch_assoc($q);
                $firstname = $info['prenom'];
                $lastname = $info['nom'];
                $email = $info['email'];
        } else {
                $firstname = $lastname = $email = '';
        }
	
        if (isset($_POST['submit'])) {
                $body = isset($_POST['body'])? $_POST['body']: '';
                $tool_content .= "<table width='99%'><tbody><tr><td>";
                $to = get_config('email_helpdesk');
                $emailsubject = $langAccountActivate;
                $emailbody = "$langAccountActivateMessage\n\n$firstname $lastname\ne-mail: $email\n" .
                        "{$urlServer}modules/admin/edituser.php?u=$userid\n\n$m[comments]: $body\n";
                if (!send_mail('', '', '', $to,	$emailsubject, $emailbody, $charset)) {
                        $tool_content .= "<div class='caution'>$langEmailNotSend ".q($to)."!</div>";
                } else {
                        $tool_content .= "<div class='success'>$emailsuccess</div>";
                }
                $tool_content .= "</td></tr><tbody></table><br />";
        } else {
                $tool_content .= "
    <form action='contactadmin.php?userid=$userid&amp;h=$_GET[h]' method='post'>
      <fieldset>
        <legend>$langForm</legend>
        <table width='99%'>
          <tbody>
            <tr><td width='3%' nowrap valign='top'><b>$langName:</b></td><td>".q($firstname)."</td></tr>
            <tr><td width='3%' nowrap valign='top'><b>$langSurname:</b></td><td>".q($lastname)."</td></tr>
            <tr><td width='3%' nowrap valign='top'><b>Email:</b></td><td>".q($email)."</td></tr>
            <tr><td width='3%' nowrap valign='top'><b>$langComments:</b></td>
                <td><textarea rows='6' cols='40' name='body'>$langActivateAccount</textarea></td></tr>
            <tr><td width='3%' nowrap valign='top'>&nbsp;</td>
                <td><input type='submit' name='submit' value='$langSend'></td></tr>
          </tbody>
        </table>
      </fieldset>
    </form>";
	}
}

$tool_content .= "<center><p><a href='$urlAppend'>$langBackHome</a></p></center>";

draw($tool_content, 0);
