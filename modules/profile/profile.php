<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
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

$require_help = TRUE;
$require_login = true;
$helpTopic = 'Profile';
include '../../include/baseTheme.php';
include "../auth/auth.inc.php";
$require_valid_uid = TRUE;

$authmethods = array('imap', 'pop3', 'ldap', 'db', 'shibboleth');

check_uid();
$nameTools = $langModifProfile;
check_guest();
list($password) = mysql_fetch_row(db_query("SELECT password FROM user WHERE user_id = $uid"));
if (in_array($password, $authmethods)) {
        $allow_username_change = false; 
        $allow_password_change = false;
} else {
        $allow_username_change = !get_config('block-username-change');
        $allow_password_change = true;
}

function redirect_to_message($id) {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=' . $id);
        exit();
}

if (isset($_POST['submit'])) {
        // First do personalization and language changes
        $perso_status = ($_POST['persoStatus'] == 'yes')? 'yes': 'no';
        $old_language = $language;
        $language = $_SESSION['langswitch'] = langcode_to_name($_POST['userLanguage']);
        $langcode = langname_to_code($language);
        $old_perso_status = $_SESSION['user_perso_active'];
        $_SESSION['user_perso_active'] = $perso_status;
        db_query("UPDATE user SET perso = '$perso_status',
                                  lang = '$langcode'
                              WHERE user_id = $uid");
        $all_ok = register_posted_variables(array(
                'am_form' => false,
                'email_form' => check_prof(),
                'nom_form' => true,
                'prenom_form' => true,
                'username_form' => true,
		'department' => true), 'all');

	// check if there are empty fields
	if (!$all_ok) {
                redirect_to_message(4);
	}
                
        if (!$allow_username_change) {
                $username_form = $_SESSION['uname'];
        }

        if ($username_form != $_SESSION['uname']) {
                // check if username exists
                $username_check = db_query('SELECT username FROM user WHERE username = ' . autoquote($username_form));
                $user_exist = (mysql_num_rows($username_check) > 0);
        } else {
                $user_exist = false;
        }

        // TODO: Allow admin to configure allowed username format
	// if (strstr($username_form, "'") or strstr($username_form, '"') or strstr($username_form, '\\')){
	//	redirect_to_message(10);
	// }

	// check if username is free
	if ($user_exist and $username_form == $user_exist AND $username_form != $uname) {
		redirect_to_message(5);
	}

	// check if email is valid
	if (!email_seems_valid($email_form) and check_prof()) {
                redirect_to_message(6);
	}

	// everything is ok
        if (db_query("UPDATE user SET
                                nom = " . autoquote($nom_form) . ",
                                prenom = " . autoquote($prenom_form) . ",
                                username = " . autoquote($username_form) . ",
                                email = " . autoquote($email_form) . ",
                                am = " . autoquote($am_form) . ",
				department = $department
                        WHERE user_id = $_SESSION[uid]")) {
                $_SESSION['uname'] = $username_form;
                $_SESSION['nom'] = $nom_form;
                $_SESSION['prenom'] = $prenom_form;
                $_SESSION['email'] = $email_form;

                redirect_to_message(1);
        }
        if ($old_language != $language or $old_perso_status != $perso_status) {
                redirect_to_message(1);
        }
}

//Show message if exists
if (isset($_GET['msg'])) {
        $urlText = '';
        $type = 'caution_small';
        switch ($_GET['msg']) {
            case 1: //profile information changed successfully
                $message = $langProfileReg;
                $urlText = "<br /><a href='$urlServer'>$langHome</a>";
                $type = "success_small";
                break;
            case 3: //pass too easy
                $message = $langPassTooEasy.": <strong>" . create_pass() . "</strong>";
                break;
            case 4: // empty fields check
                $message = $langFields;
                break;
            case 5: //username already exists
                $message = $langUserFree;
                break;
            case 6: //email not valid
                $message = $langEmailWrong;
                break;
            case 10: // invalid characters
                $message = $langInvalidCharsUsername;
                break;
            default:
                header('Location: ' . $urlAppend . '/modules/profile/profile.php');
                exit;
        }
	$tool_content .=  "<p class='$type'>$message$urlText</p><br/>";
}

$result = db_query("SELECT nom, prenom, username, email, am, perso, lang, department, statut
                    FROM user WHERE user_id = $uid");
$myrow = mysql_fetch_array($result);

$nom_form = q($myrow['nom']);
$prenom_form = q($myrow['prenom']);
$username_form = q($myrow['username']);
$email_form = q($myrow['email']);
$am_form = q($myrow['am']);
$userLang = $myrow['lang'];

if ($myrow['perso'] == 'yes')  {
	$checkedClassic = " checked='yes'";
	$checkedPerso = '';
} else {
	$checkedClassic = '';
	$checkedPerso = " checked='yes'";
}

$sec = $urlSecure . 'modules/profile/profile.php';
$passurl = $urlSecure . 'modules/profile/password.php';

$tool_content .= "
    <div id='operations_container'>
     <ul id='opslist'>";
if ($allow_password_change) {
        $tool_content .= "
        <li><a href='$passurl'>$langChangePass</a></li>";
}
$tool_content .= "<li><a href='../unreguser/unreguser.php'>$langUnregUser</a></li>";
$tool_content .= "</ul></div>";
$tool_content .= "
   <form method='post' action='$sec'>
   <fieldset>
     <legend>$langUserData</legend>
        <table class='tbl'>
        <tr>
          <td>$langName</td>";

if (isset($_SESSION['shib_user'])) {
        $auth_text = "Shibboleth user";
        $tool_content .= "
          <td class='caution_small'>&nbsp;&nbsp;&nbsp;&nbsp;<b>$prenom_form</b> [$auth_text]
            <input type='hidden' name='prenom_form' value='$prenom_form' />
          </td>";
} else {
        $tool_content .= "
          <td><input type='text' size='40' name='prenom_form' value='$prenom_form' /></td>";
}

$tool_content .= "</tr><tr><td>$langSurname</td>";
if (isset($_SESSION['shib_user'])) {
        $auth_text = "Shibboleth user";
        $tool_content .= "
          <td class='caution_small'>&nbsp;&nbsp;&nbsp;&nbsp;<b>".$nom_form."</b> [".$auth_text."]
            <input type='hidden' name='nom_form' value='$nom_form' /></td>";
} else {
        $tool_content .= "<td><input type='text' size='40' name='nom_form' value='$nom_form' /></td>";
}
$tool_content .= "</tr>";

if ($allow_username_change) {
        $tool_content .= "
        <tr>
          <td>$langUsername</td>
          <td><input type='text' size='40' name='username_form' value='$username_form' /></td>
        </tr>";
} else {
        // means that it is external auth method, so the user cannot change this password
        switch($password) {
                case "pop3": $auth = 2; break;
                case "imap": $auth = 3; break;
                case "ldap": $auth = 4; break;
                case "db": $auth = 5; break;
                default: $auth = 1; break;
        }
        if (isset($_SESSION['shib_user'])) {
                $auth_text = 'Shibboleth user';
        } else {
                $auth_text = get_auth_info($auth);
        }
        $tool_content .= "
        <tr>
          <th class='left'>$langUsername</th>
          <td class='caution_small'>&nbsp;&nbsp;&nbsp;&nbsp;<b>$username_form</b> [$auth_text]
            <input type='hidden' name='username_form' value='$username_form' />
          </td>
        </tr>";
}

$tool_content .= "<tr><td>$langEmail</td>";

if (isset($_SESSION['shib_user'])) {
        $tool_content .= "
           <td class='caution_small'>&nbsp;&nbsp;&nbsp;&nbsp;<b>$email_form</b> [$auth_text]
             <input type='hidden' name='email_form' value='$email_form' />
           </td>";
} else {
        $tool_content .= "<td><input type='text' size='40' name='email_form' value='$email_form' /></td>";
}
$tool_content .= "
        </tr>
        <tr>
          <td>$langAm</td>
          <td><input type='text' size='40' name='am_form' value='$am_form' /></td>
        </tr>";
##[BEGIN personalisation modification]############
if (isset($_SESSION['perso_is_active'])) {
        $tool_content .= "
        <tr>
          <td>$langPerso</td>
          <td><input type=radio name='persoStatus' value='no'$checkedPerso />$langModern&nbsp;
              <input type=radio name='persoStatus' value='yes'$checkedClassic />$langClassic
          </td>
        </tr>";
}

if ($myrow['statut'] == 5) { // students can change their faculties
	$tool_content .= "<tr><td>$langFaculty</td><td>";
	$tool_content .= list_departments($myrow['department']);
	$tool_content .= "</td></tr>";
}

##[END personalisation modification]############
$tool_content .= "
        <tr>
          <td>$langLanguage</td>
          <td>" . lang_select_options('userLanguage') . "          </td>
        </tr>
	<tr>
          <td>&nbsp;</td>
          <td><input type='submit' name='submit' value='$langModify' /></td>
        </tr>
        </table>
        </fieldset>
        </form>";

draw($tool_content, 1);