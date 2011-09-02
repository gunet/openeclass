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


include '../../include/baseTheme.php';
include '../../include/sendMail.inc.php';

$lang = langname_to_code($language);

$navigation[] = array('url' => 'registration.php', 'name' => $langNewUser);

$prof = isset($_REQUEST['p'])? intval($_REQUEST['p']): 0;
$nameTools = $prof? $langReqRegProf: $langUserRequest;

// eclass native registration method disabled for students
$disable_eclass_stud_reg = get_config('disable_eclass_stud_reg');
if (!$prof and $disable_eclass_stud_reg) {
	$tool_content .= "<div class='td_main'>$langForbidden</div></td></tr></table>";
	draw($tool_content, 0);
	exit;
}

// eclass native registration method disabled for profs
$disable_eclass_prof_reg = get_config('disable_eclass_prof_reg');
if ($prof and $disable_eclass_prof_reg) {
	$tool_content .= "<div class='td_main'>$langForbidden</div></td></tr></table>";
	draw($tool_content, 0);
	exit;
}

$am_required = !$prof && get_config('am_required');
$errors = array();

// security - show error instead of form if user registration is open
if (!$prof and (!isset($close_user_registration) or $close_user_registration == false)) {
        $tool_content .= "<div class='td_main'>$langForbidden</div></td></tr></table>";
        draw($tool_content, 0);
        exit;
}

$all_set = register_posted_variables(array(
                'usercomment' => true,
                'name' => true,
                'surname' => true,
                'username' => true,
                'userphone' => $prof,
                'usermail' => true,
                'am' => $am_required,
                'department' => true,
                'captcha_code' => false));

if (!$all_set) {
        $errors[] = $langFieldsMissing;
}

if (!email_seems_valid($usermail)) {
        $errors[] = $langEmailWrong;
        $all_set = false;
}

if (get_config("display_captcha")) {
        // captcha check
        require_once '../../include/securimage/securimage.php';
        $securimage = new Securimage();
        if ($securimage->check($captcha_code) == false) {
                $errors[] = $langCaptchaWrong;
                $all_set = false;
        }	
}

if (isset($_POST['submit'])) {
        foreach ($errors as $message) {
                $tool_content .= "<p class='alert1'>$message</p>";
        }
}

if ($all_set) {
        // register user request
        $statut = $prof? 1: 5;
        db_query('INSERT INTO user_request SET
                         name = ' . autoquote($name). ',
                         surname = ' . autoquote($surname). ',
                         uname = ' . autoquote($username). ',
                         email = ' . autoquote($usermail). ',
                         faculty_id = ' . intval($department). ',
                         phone = ' . autoquote($userphone). ",
                         status = 1,
                         statut = $statut,
                         date_open = NOW(),
                         comment = " . autoquote($usercomment). ',
                         lang = ' . quote(langname_to_code($language)). ",
                         ip_address = inet_aton('$_SERVER[REMOTE_ADDR]')",
                     $mysqlMainDb);


        //----------------------------- Email Message --------------------------
        $department = find_faculty_by_id($department);
        $subject = $prof? $mailsubject: $mailsubject2;
        $MailMessage = $mailbody1 . $mailbody2 . "$name $surname\n\n" .
                $mailbody3 . $mailbody4 . $mailbody5 . 
                ($prof? $mailbody6: $mailbody8) .
                "\n\n$langFaculty: $department\n$langComments: $usercomment\n" .
                "$langAm: $am\n" .
                "$langProfUname: $username\n$langProfEmail : $usermail\n" .
                "$contactphone: $userphone\n\n\n$logo\n\n";

        if (!send_mail('', $emailhelpdesk, '', $emailhelpdesk, $subject, $MailMessage, $charset)) {
                $tool_content .= "
                         <p class='alert1'>$langMailErrorMessage&nbsp; <a href='mailto:$emailhelpdesk' class='mainpage'>$emailhelpdesk</a>.</p>";
        }

        // User Message
        $tool_content .= "<div class='success'>" .
                         ($prof? $langDearProf: $langDearUser) .
                         "!<br />$success</div>
                <p>$infoprof<br /><br />$click <a href='$urlServer' class='mainpage'>$langHere</a> $langBackPage</p>";
        draw($tool_content, 0);
        exit();

} else {
        // display the form
        $phone_star = $prof? '&nbsp;&nbsp;(*)': '';
        $tool_content .= "<p>$langInfoStudReq</p><br />
        <form action='$_SERVER[PHP_SELF]' method='post'>
         <input type='hidden' name='p' value='$prof'>
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
            <td colspan='2'><input type='text' name='userphone' value='" . q($userphone) . "' size='33' />$phone_star</td>
          <tr>
            <th>$langUsername</th>
            <td><input type='text' name='username' size='33' maxlength='20' value='" . q($username) . "' />&nbsp;&nbsp;<small>(*)&nbsp;$langUserNotice</small></td>
          </tr>
          <tr>
            <th>$langProfEmail</th>
            <td><input type='text' name='usermail' value='" . q($usermail) . "' size='33' />&nbsp;&nbsp;(*)</td>
          </tr>";
        if (!$prof) {
                $tool_content .= "
          <tr>
            <th>$langAm</th>
            <td colspan='2'><input type='text' name='am' value='" . q($am) . "' size='33' />" .
                ($am_required? '&nbsp;&nbsp;(*)': '') . "</td>
          </tr>";
        }
        $tool_content .= "
          <tr>
            <th>$langComments</th>
            <td><textarea name='usercomment' cols='30' rows='4'>" . q($usercomment) . "</textarea>&nbsp;&nbsp;<small>(*) $profreason</small></td>
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
        </tr>";
        if (get_config("display_captcha")) {
		$tool_content .= "<tr>
		<th class='left'><img id='captcha' src='../../include/securimage/securimage_show.php' alt='CAPTCHA Image' /></th>
		<td colspan='2'><input type='text' name='captcha_code' maxlength='6' class='FormData_InputText' />&nbsp;&nbsp;<small>(*)&nbsp;$langTipCaptcha</small></td>
		</tr>";
	}
        $tool_content .= "
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
