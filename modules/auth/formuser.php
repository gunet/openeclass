<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*                       Yannis Exidaridis <jexi@noc.uoa.gr>
*                       Alexandros Diamantidis <adia@noc.uoa.gr>
*                       Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address:     GUnet Asynchronous eLearning Group,
*                       Network Operations Center, University of Athens,
*                       Panepistimiopolis Ilissia, 15784, Athens, Greece
*                       eMail: info@openeclass.org
* =========================================================================*/

include '../../include/baseTheme.php';
include '../../include/sendMail.inc.php';

$tool_content = "";

$lang = langname_to_code($language);

$nameTools = $langUserRequest;
$navigation[] = array('url' => 'registration.php', 'name' => $langNewUser);

// security - show error instead of form if user registration is open
if (!isset($close_user_registration) or $close_user_registration == false) {
        $tool_content .= "<div class='td_main'>$langForbidden</div></td></tr></table>";
        draw($tool_content, 0, 'auth');
        exit;
}

$all_set = register_posted_variables(array(
                'usercomment' => true,
                'name' => true,
                'surname' => true,
                'username' => true,
                'userphone' => false,
                'usermail' => false,
                'am' => false,
                'department' => true));

if (!email_seems_valid($usermail)) {
        $all_set = false;
}

if (isset($_POST['submit']) and !$all_set) {

        // form submitted but required fields empty
        $tool_content .= "<p class='alert1'>$langFieldsMissing</p>";

}

if ($all_set) {

        // register user request
        db_query("INSERT INTO user_request
                        (name, surname, uname, email,
                         faculty_id, phone, am, status, date_open,
                         comment, lang, statut, ip_address)
                  VALUES (".
                  autoquote($name) .', '.
                  autoquote($surname) .', '.
                  autoquote($username) .', '.
                  autoquote($usermail) .', '.
                  intval($department) .', '.
                  autoquote($userphone) .', '.
                  autoquote($am) .', 1, NOW(), '.
                  autoquote($usercomment) .", '$lang', 5, inet_aton('$_SERVER[REMOTE_ADDR]'))");

        //----------------------------- Email Message --------------------------
        $department = find_faculty_by_id($department);
        $MailMessage = $mailbody1 . $mailbody2 . "$name $surname\n\n" .
                $mailbody3 . $mailbody4 . $mailbody5 . "$mailbody8\n\n" .
                "$langFaculty: $department\n$langComments: $usercomment\n" .
                "$langAm: $am\n" .
                "$langProfUname : $username\n$langProfEmail : $usermail\n" .
                "$contactphone : $userphone\n\n\n$logo\n\n";

        if (!send_mail('', $emailhelpdesk, '', $emailhelpdesk, $mailsubject2, $MailMessage, $charset)) {
                $tool_content .= "
                         <p class='alert1'>$langMailErrorMessage&nbsp; <a href='mailto:$emailhelpdesk' class='mainpage'>$emailhelpdesk</a>.</p>";
        }

        // User Message
        $tool_content .= "<div class='success'>$langDearUser!<br />$success</div>
                <p>$infoprof<br /><br />$click <a href='$urlServer' class='mainpage'>$langHere</a> $langBackPage</p>";

        draw($tool_content, 0);
        exit();

} else {
        // display the form
        $tool_content .= "<p>$langInfoStudReq</p><br />
<form action='$_SERVER[PHP_SELF]' method='post'>
 <fieldset>
  <legend>$langUserData</legend>
  <table class='tbl'>
  <tr>
    <th>$langName</th>
    <td><input type='text' name='name' value='" . q($name) . "' size='33' />&nbsp;&nbsp;(*)</td>
  </tr>
  <tr>
    <th>$langSurname</th>
    <td><input type='text' name='surname' value='" . q($surname) . "' size='33' />&nbsp;&nbsp;(*)</td>
  </tr>
  <tr>
    <th>$langPhone</th>
    <td colspan='2'><input type='text' name='userphone' value='" . q($userphone) . "' size='33' /></td>
  <tr>
    <th>$langUsername</th>
    <td><input type='text' name='username' size='33' maxlength='20' value='" . q($username) . "' />&nbsp;&nbsp;(*)&nbsp;$langUserNotice</td>
  </tr>
  <tr>
    <th>$langProfEmail</th>
    <td><input type='text' name='usermail' value='" . q($usermail) . "' size='33' />&nbsp;&nbsp;(*)</td>
  </tr>
  <tr>
    <th>$langAm</th>
    <td colspan='2'><input type='text' name='am' value='" . q($am) . "' size='33' /></td>
  </tr>
  <tr>
    <th>$langComments</th>
    <td><textarea name='usercomment' cols='30' rows='4'>" . q($usercomment) . "</textarea>&nbsp;&nbsp;(*) $profreason</td>
  </tr>
  <tr>
    <th>$langFaculty&nbsp;</th>
    <td><select name='department'>";
        $deps = db_query("SELECT id, name FROM faculte order by name");
        while ($dep = mysql_fetch_array($deps)) {
                if ($dep['id'] == $department) {
                        $selected = ' selected="1"';
                } else {
                        $selected = '';
                }
                $tool_content .= "\n<option value='$dep[id]'$selected>" . q($dep['name']) . "</option>\n";
        }

	 $tool_content .= "\n</select>
     </td>
  </tr>
  <tr>
     <th>$langLanguage</th>
     <td>";
	$tool_content .= lang_select_options('localize');
	$tool_content .= "</td>
  </tr>
  <tr>
     <td>&nbsp;</td>
     <td class='right'><input type='submit' class='ButtonSubmit' name='submit' value='$langSubmitNew' /></td>
  </tr>
  </table>
  </fieldset>
  </form>
  <div class='right smaller'>$langRequiredFields</div>";
}

draw($tool_content, 0);
