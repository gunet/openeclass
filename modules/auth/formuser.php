<?
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
$navigation[] = array("url"=>"registration.php", "name"=> $langNewUser);

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
        $tool_content .= "<table width='99%'><tbody><tr>
                <td class='caution' height='60'><p>$langFieldsMissing</p></td>
                </tr></tbody>
                </table><br /><br />";

}

if ($all_set) {

        // register user request
        db_query("INSERT INTO prof_request
                        (profname, profsurname, profuname, profemail,
                         proftmima, profcomm, status, date_open,
                         comment, lang, statut)
                  VALUES (".
                  autoquote($name) .', '.
                  autoquote($surname) .', '.
                  autoquote($username) .', '.
                  autoquote($usermail) .', '.
                  intval($department) .', '.
                  autoquote($userphone) .', 1, NOW(), '.
                  autoquote($usercomment) .", '$lang', 5)");

        //----------------------------- Email Message --------------------------
        $department = find_faculty_by_id($department);
        $MailMessage = $mailbody1 . $mailbody2 . "$name $surname\n\n" .
                $mailbody3 . $mailbody4 . $mailbody5 . "$mailbody8\n\n" .
                "$langFaculty: $department\n$langComments: $usercomment\n" .
                "$langProfUname : $username\n$langProfEmail : $usermail\n" .
                "$contactphone : $userphone\n\n\n$logo\n\n";

        if (!send_mail('', $emailhelpdesk, '', $emailhelpdesk, $mailsubject2, $MailMessage, $charset)) {
                $tool_content .= "<table width='99%'><tbody><tr>
                        <td class='caution' height='60'>
                        <p>$langMailErrorMessage&nbsp; <a href='mailto:$emailhelpdesk' class='mainpage'>$emailhelpdesk</a>.</p>
                        </td>
                        </tr></tbody></table><br /><br />";
        }

        // User Message
        $tool_content .= "<div class='well-done'><p>$langDearUser!</p><p>$success</p></div>
                <p>$infoprof</p><p>$click <a href='$urlServer' class='mainpage'>$langHere</a> $langBackPage</p>";

        draw($tool_content, 0);
        exit();

} else {
        // display the form

        $tool_content .= "
<p>$langInfoStudReq</p><br />
<form action='$_SERVER[PHP_SELF]' method='post'>
<table width='99%' style='border: 1px solid #edecdf;'>
<thead>
<tr>
  <td>
  <table width='99%' align='left' class='FormData'>
  <thead>
  <tr>
    <th class='left' width='220'>$langName</th>
    <td><input type='text' name='name' value='$name' class='FormData_InputText' size='33' />&nbsp;&nbsp;<small>(*)</small></td>
  </tr>
  <tr>
    <th class='left'>$langSurname</th>
    <td><input type='text' name='surname' value='$surname' class='FormData_InputText' size='33' />&nbsp;&nbsp;<small>(*)</small></td>
  </tr>
  <tr>
    <th class='left'>$langPhone</th>
    <td colspan='2'><input type='text' name='userphone' value='$userphone' class='FormData_InputText' size='33' /></td>
  <tr>
    <th class='left'>$langUsername</th>
    <td><input type='text' name='username' size='33' maxlength='20' value='$username' class='FormData_InputText' />&nbsp;&nbsp;<small>(*)&nbsp;$langUserNotice</small></td>
  </tr>
  <tr>
    <th class='left'>$langProfEmail</th>
    <td><input type='text' name='usermail' value='$usermail' class='FormData_InputText' size='33' />&nbsp;&nbsp;<small>(*)</small></td>
  </tr>
  <tr>
    <th class='left'>$langAm</th>
    <td colspan='2'><input type='text' name='am' value='$am' class='FormData_InputText' size='33' /></td>
  </tr>
  <tr>
    <th class='left'>$langComments<br /><small>$profreason</small></th>
    <td><textarea name='usercomment' cols='30' rows='4' class='FormData_InputText'>$usercomment</textarea>&nbsp;&nbsp;<small>(*)</small></td>
  </tr>
  <tr>
    <th class='left'>$langFaculty&nbsp;</th>
    <td><select name='department'>";

        $deps = db_query("SELECT id, name FROM faculte order by name");
        while ($dep = mysql_fetch_array($deps)) {
                if ($dep['id'] == $department) {
                        $selected = ' selected="1"';
                } else {
                        $selected = '';
                }
                $tool_content .= "\n<option value='$dep[id]'$selected>$dep[name]</option>\n";
        }

	 $tool_content .= "\n</select>
    </td>
  </tr>
	<tr>
      <th class='left'>$langLanguage</th>
      <td>";
	$tool_content .= lang_select_options('localize');
	$tool_content .= "</td>
    </tr>
  <tr>
    <th class='left'>&nbsp;</th>
    <td><input type='submit' class='ButtonSubmit' name='submit' value='$langSubmitNew' /></td>
  </tr>
  </table>
     <div align='right'><small>$langRequiredFields</small></div>
  </td>
</tr>
</thead>
</table>
</form>";
}   // end of form

draw($tool_content, 0);
