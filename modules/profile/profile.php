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


$require_help = TRUE;
$require_login = true;
$helpTopic = 'Profile';
include '../../include/baseTheme.php';
include "../auth/auth.inc.php";
$require_valid_uid = TRUE;

$authmethods = array('imap', 'pop3', 'ldap', 'db', 'shibboleth', 'cas');

check_uid();
$nameTools = $langModifProfile;
check_guest();
list($password) = mysql_fetch_row(db_query("SELECT password FROM user WHERE user_id = $uid"));
if (in_array($password, $authmethods)) {
        $allow_username_change = false; 
        $allow_password_change = false;
} else {
        $allow_username_change = !get_config('block_username_change');
        $allow_password_change = true;
}

function redirect_to_message($id) {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?msg=' . $id);
        exit();
}

if (isset($_POST['submit'])) {
        // First do personalization and language changes
	if (!file_exists($webDir."courses/userimg/")) {
		mkdir($webDir."courses/userimg/", 0775);
	}
	$image_path = $webDir."courses/userimg/".$_SESSION['uid'];
        $perso_status = (isset($_POST['persoStatus']) and $_POST['persoStatus'] == 'yes')? 'yes': 'no';
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
                'desc_form' => false,
                'email_form' => check_prof(),
                'nom_form' => true,
                'prenom_form' => true,
                'username_form' => true,
		'department' => true), 'all');
	
	// upload user picture
	if (isset($_FILES['userimage']) && is_uploaded_file($_FILES['userimage']['tmp_name'])) {
		$type = $_FILES['userimage']['type'];
		$image_file = $_FILES['userimage']['tmp_name'];

		if (!copy_resized_image($image_file, $type, IMAGESIZE_LARGE, IMAGESIZE_LARGE,
					$image_path . '_' . IMAGESIZE_LARGE . '.jpg')) {
			redirect_to_message(7);
		}
		if (!copy_resized_image($image_file, $type, IMAGESIZE_SMALL, IMAGESIZE_SMALL,
					$image_path . '_' . IMAGESIZE_SMALL . '.jpg')) {
			redirect_to_message(7);
		}
		db_query("UPDATE user SET has_icon = 1 WHERE user_id = $_SESSION[uid]");
	}
	if (isset($_POST['delimage'])) {
		@unlink($image_path . '_' . IMAGESIZE_LARGE . '.jpg');
		@unlink($image_path . '_' . IMAGESIZE_SMALL . '.jpg');
		db_query("UPDATE user SET has_icon = 0 WHERE user_id = $uid");
	}
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
	if ($user_exist and $username_form == $user_exist and $username_form != $_SESSION['uname']) {
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
                                description = " . autoquote($desc_form) . ",
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
        $type = 'caution';
        switch ($_GET['msg']) {
            case 1: //profile information changed successfully
                $message = $langProfileReg;
                $urlText = "<br /><a href='$urlServer'>$langHome</a>";
                $type = "success";
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
            case 7: //invalid image
                $message = $langInvalidPicture;
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

$result = db_query("SELECT nom, prenom, username, email, am, perso,
                           lang, department, statut, has_icon, description
                        FROM user WHERE user_id = $uid");
$myrow = mysql_fetch_array($result);

$nom_form = q($myrow['nom']);
$prenom_form = q($myrow['prenom']);
$username_form = q($myrow['username']);
$email_form = q($myrow['email']);
$am_form = q($myrow['am']);
$desc_form = q($myrow['description']);
$userLang = $myrow['lang'];
$icon = $myrow['has_icon'];

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
    <ul id='opslist'>
      <li><a href='display_profile.php'>$langDisplayProfile</a></li> ";
if ($allow_password_change) {
        $tool_content .= "
        <li><a href='$passurl'>$langChangePass</a></li> ";
}
$tool_content .= "
        <li><a href='../unreguser/unreguser.php'>$langUnregUser</a></li>
    </ul>
  </div>\n";
$tool_content .= "
   <form method='post' enctype='multipart/form-data' action='$sec'>
   <fieldset>
     <legend>$langUserData</legend>
        <table class='tbl' width='100%'>
        <tr>
          <th>$langName:</th>";

if (isset($_SESSION['shib_user'])) {
        $auth_text = "Shibboleth user";
        $tool_content .= "
          <td>&nbsp;&nbsp;&nbsp;&nbsp;<b>$prenom_form</b> [$auth_text]
            <input type='hidden' name='prenom_form' value='$prenom_form' />
          </td>";
} elseif (isset($_SESSION['cas_user'])) {
		$auth_text = "CAS user";
        $tool_content .= "
          <td><b>$prenom_form</b> [$auth_text]
            <input type='hidden' name='prenom_form' value='$prenom_form' />
          </td>";
} else {
        $tool_content .= "
          <td><input type='text' size='40' name='prenom_form' value='$prenom_form' /></td>";
}

$tool_content .= "
        </tr>
        <tr>
          <th>$langSurname:</th>";
if (isset($_SESSION['shib_user'])) {
        $auth_text = "Shibboleth user";
        $tool_content .= "
          <td><b>".$nom_form."</b> [".$auth_text."]
            <input type='hidden' name='nom_form' value='$nom_form' /></td>";
} elseif (isset($_SESSION['cas_user'])) {
        $auth_text = "CAS user";
        $tool_content .= "
          <td><b>".$nom_form."</b> [".$auth_text."]
            <input type='hidden' name='nom_form' value='$nom_form' /></td>";
} else {
        $tool_content .= "
          <td><input type='text' size='40' name='nom_form' value='$nom_form' /></td>";
}
$tool_content .= "
        </tr>";

if ($allow_username_change) {
        $tool_content .= "
        <tr>
          <th>$langUsername:</th>
          <td><input type='text' size='40' name='username_form' value='$username_form' /></td>
        </tr>";
} else {
        // means that it is external auth method, so the user cannot change this password
        switch($password) {
                case "pop3": $auth = 2; break;
                case "imap": $auth = 3; break;
                case "ldap": $auth = 4; break;
                case "db": $auth = 5; break;
                case "cas": $auth = 7; break;
                default: $auth = 1; break;
        }
        if (isset($_SESSION['shib_user'])) {
                $auth_text = 'Shibboleth user';
        } elseif (isset($_SESSION['cas_user'])) {
                $auth_text = 'CAS user';
		  } else {
                $auth_text = get_auth_info($auth);
        }
        $tool_content .= "
        <tr>
          <th class='left'>$langUsername:</th>
          <td><b>$username_form</b> [$auth_text]
            <input type='hidden' name='username_form' value='$username_form' />
          </td>
        </tr>";
}

$tool_content .= "
        <tr>
          <th>$langEmail:</th>";

if (isset($_SESSION['shib_user'])) {
        $tool_content .= "
           <td><b>$email_form</b> [$auth_text]
             <input type='hidden' name='email_form' value='$email_form' />
           </td>";
} else { // allow user to change his e-mail
        $tool_content .= "
          <td><input type='text' size='40' name='email_form' value='$email_form' /></td>";
}
$tool_content .= "
        </tr>
        <tr>
          <th>$langAm</th>
          <td><input type='text' size='40' name='am_form' value='$am_form' /></td>
        </tr>";
##[BEGIN personalisation modification]############
if (isset($_SESSION['perso_is_active'])) {
        $tool_content .= "
        <tr>
          <th>$langPerso:</th>
          <td><input type=radio name='persoStatus' value='no'$checkedPerso />$langModern&nbsp;
              <input type=radio name='persoStatus' value='yes'$checkedClassic />$langClassic
          </td>
        </tr>";
}


$tool_content .= "
        <tr>
          <th>$langFaculty:</th>
          <td>";
$tool_content .= list_departments($myrow['department']);
$tool_content .= "</td>
        </tr>";


##[END personalisation modification]############
$tool_content .= "
        <tr>
          <th>$langLanguage:</th>
          <td>" . lang_select_options('userLanguage') . "</td>
        </tr>";
if ($icon) {
	$message_pic = $langReplacePicture;
	$picture = profile_image($uid, IMAGESIZE_SMALL) . "&nbsp;&nbsp;";
	$delete = "
        <tr>
          <th>$langDeletePicture</th>
          <td><input type='checkbox' name='delimage'></td>
        </tr>";
} else {
	$picture = $delete = '';
	$message_pic = $langAddPicture;
}
$tool_content .= "
        <tr>
          <th>$message_pic</th>
          <td>$picture<input type='file' name='userimage' size='30'></td>
        </tr>
        $delete
        <tr>
          <th>$langDescription:</th>
          <td>" . rich_text_editor('desc_form', 5, 20, $desc_form) . "</td>
        </tr>
        <tr> 
          <td>&nbsp;</td>
          <td class='right'><input type='submit' name='submit' value='$langModify' /></td>
        </tr>
        </table>
        </fieldset>
        </form>";

draw($tool_content, 1, null, $head_content);
