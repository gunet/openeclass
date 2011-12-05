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


$require_help = true;
$require_login = true;
$helpTopic = 'Profile';
include '../../include/baseTheme.php';
include '../auth/auth.inc.php';
$require_valid_uid = TRUE;

check_uid();
$nameTools = $langModifyProfile;
check_guest();

$result = db_query("SELECT nom, prenom, username, email, am, phone, perso,
                           lang, department, statut, has_icon, description,
                           email_public, phone_public, am_public, password
                        FROM user WHERE user_id = $uid");
$myrow = mysql_fetch_assoc($result);

$password = $myrow['password'];
$auth = array_search($password, $auth_ids);
if (!$auth) {
	$auth = 1;
}
$auth_text = get_auth_info($auth);

if ($auth != 1) {
        $allow_username_change = false; 
        $allow_password_change = false;
} else {
        $allow_username_change = !get_config('block_username_change');
        $allow_password_change = true;
}

if (in_array($password, array('shibboleth', 'cas', 'ldap'))) {
	$allow_name_change = false;
} else {
	$allow_name_change = true;
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
        $subscribe = (isset($_POST['subscribe']) and $_POST['subscribe'] == 'yes')? '1': '0';
        $old_language = $language;
        $language = $_SESSION['langswitch'] = langcode_to_name($_POST['userLanguage']);
        $langcode = langname_to_code($language);
        $old_perso_status = $_SESSION['user_perso_active'];
        $_SESSION['user_perso_active'] = $persoIsActive && $perso_status == 'no';
        db_query("UPDATE user SET perso = '$perso_status',
                                  lang = '$langcode'
                              WHERE user_id = $uid");
                
        $all_ok = register_posted_variables(array(
                'am_form' => false,
                'desc_form' => false,
                'phone_form' => false,
                'email_form' => get_config('email_required'),
                'nom_form' => true,
                'prenom_form' => true,
                'username_form' => true,
                'department' => true,
                'email_public' => false, 
                'phone_public' => false, 
                'am_public' => false), 'all');

        $email_public = valid_access($email_public);
        $phone_public = valid_access($phone_public);
        $am_public = valid_access($am_public);
	
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

	// check if email is valid
	if ((get_config('email_required') | get_config('email_verification_required')) 
                        and !email_seems_valid($email_form)) {
		redirect_to_message(6);
	}

	// check if there are empty fields
	if (!$all_ok) {
		redirect_to_message(4);
	}

	if (!$allow_username_change) {
		$username_form = $_SESSION['uname'];
	}

	$username_form = canonicalize_whitespace($username_form);
	// If changing username check if the new one is free
	if ($username_form != $_SESSION['uname']) {
		// check if username exists
		$username_check = db_query('SELECT username FROM user WHERE username = ' . autoquote($username_form));
		if (mysql_num_rows($username_check) > 0) {
			redirect_to_message(5);
		}
	}

  	// TODO: Allow admin to configure allowed username format
	// if (strstr($username_form, "'") or strstr($username_form, '"') or strstr($username_form, '\\')){
	//	redirect_to_message(10);
	// }
	if (!empty($email_form) && ($email_form != $_SESSION['email']) 
                && get_config('email_verification_required')) {
		$verified_mail = EMAIL_UNVERIFIED;                	                
	} else {
		$verified_mail = EMAIL_VERIFIED;                
	}
	// everything is ok
	$email_form = mb_strtolower(trim($email_form));

	if (db_query("UPDATE user SET nom = " . autoquote($nom_form) . ",
						prenom = " . autoquote($prenom_form) . ",
						username = " . autoquote($username_form) . ",
						email = " . autoquote($email_form) . ",
						am = " . autoquote($am_form) . ",
						phone = " . autoquote($phone_form) . ",
						description = " . autoquote($desc_form) . ",
						department = $department,
						email_public = $email_public,
						phone_public = $phone_public,                
						verified_mail = $verified_mail,
                                                receive_mail = $subscribe,
						am_public = $am_public
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
                $message = $langFieldsMissing;
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
                //header('Location: ' . $urlAppend . '/modules/profile/profile.php');
                exit;
        }
	$tool_content .=  "<p class='$type'>$message$urlText</p><br/>";
}

$nom_form = q($myrow['nom']);
$prenom_form = q($myrow['prenom']);
$username_form = q($myrow['username']);
$email_form = q($myrow['email']);
$am_form = q($myrow['am']);
$phone_form = q($myrow['phone']);
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
$tool_content .= "<li><a href='emailunsubscribe.php'>$langEmailUnsubscribe</a></li>
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

if ($allow_name_change) {
        $tool_content .= "
          <td><input type='text' size='40' name='prenom_form' value='$prenom_form' /></td>";
} else {
        $tool_content .= "
          <td><b>$prenom_form</b>
            <input type='hidden' name='prenom_form' value='$prenom_form' />
          </td>";
}

$tool_content .= "
        </tr>
        <tr>
          <th>$langSurname:</th>";
if ($allow_name_change) {
        $tool_content .= "
          <td><input type='text' size='40' name='nom_form' value='$nom_form' /></td>";
} else {
        $tool_content .= "
          <td><b>".$nom_form."</b>
            <input type='hidden' name='nom_form' value='$nom_form' /></td>";
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
        $tool_content .= "
        <tr>
          <th class='left'>$langUsername:</th>
          <td><b>$username_form</b> [$auth_text]
            <input type='hidden' name='username_form' value='$username_form' />
          </td>
        </tr>";
}

$access_options = array(ACCESS_PRIVATE => $langProfileInfoPrivate,
                        ACCESS_PROFS => $langProfileInfoProfs,
                        ACCESS_USERS => $langProfileInfoUsers);

$tool_content .= "
        <tr>
          <th>$langEmail:</th>";

//if ($allow_name_change) {
        $tool_content .= "
          <td><input type='text' size='40' name='email_form' value='$email_form' />";
//} else {
//        $tool_content .= "
//           <td><b>$email_form</b> [$auth_text]
//               <input type='hidden' name='email_form' value='$email_form' /> ";
//}
$tool_content .= selection($access_options, 'email_public', $myrow['email_public']) . "</td>
        </tr>
        <tr><th>$langAm</th>
            <td><input type='text' size='40' name='am_form' value='$am_form' /> " .
                selection($access_options, 'am_public', $myrow['am_public']) . "</td></tr>
        <tr><th>$langPhone</th>
            <td><input type='text' size='40' name='phone_form' value='$phone_form' /> " .
                selection($access_options, 'phone_public', $myrow['phone_public']) . "</td></tr>";

##[BEGIN personalisation modification]############
if (isset($persoIsActive)) {
        $tool_content .= "
        <tr>
          <th>$langPerso:</th>
          <td><input type='radio' name='persoStatus' id='persoStatus_no' value='no'$checkedPerso /><label for='persoStatus_no'>$langModern</label>&nbsp;
              <input type='radio' name='persoStatus' id='persoStatus_yes' value='yes'$checkedClassic /><label for='persoStatus_yes'>$langClassic</label>
          </td>
        </tr>";
}

if (get_user_email_notification_from_courses($uid)) {
        $selectedyes = 'checked';
        $selectedno = '';
} else {
        $selectedyes = '';
        $selectedno = 'checked';
}
$tool_content .= "<tr><th>$langEmailFromCourses:</th>
                  <td><input type='radio' name='subscribe' value='yes' $selectedyes />$langYes&nbsp;
                  <input type='radio' name='subscribe' value='no' $selectedno />$langNo&nbsp;
                  </td></tr>";
if (get_config('email_verification_required')) {        
        $user_email_status = get_mail_ver_status($uid);
        switch($user_email_status) {
                case EMAIL_VERIFICATION_REQUIRED:
                        $link = "<a href = '../auth/mail_verify_change.php?from_profile=TRUE'>$langHere</a>.";
                        $message = "<div class='alert1'>$langMailNotVerified $link</div>";                        
                        break;
                case EMAIL_VERIFIED: 
                        $message = "<img src='$themeimg/tick_1.png' title='$langMailVerificationYesU' />";
                        break;
                case EMAIL_UNVERIFIED:                        
                        $link = "<a href = '../auth/mail_verify_change.php?from_profile=TRUE'>$langHere</a>.";
                        $message = "<div class='alert1'>$langMailNotVerified $link</div>";                                                
                default:	
                        break;
                
        }
        $tool_content .= "<tr><th>$langVerifiedMail</th><td>$message</td>";
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


function valid_access($val)
{
        $val = intval($val);
        if (in_array($val, array(ACCESS_PRIVATE, ACCESS_PROFS, ACCESS_USERS))) {
                return $val;
        } else {
                return 0;
        }
}
