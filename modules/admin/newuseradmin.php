<?php

/* ========================================================================
 * Open eClass 3.0
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


$require_usermanage_user = TRUE;

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/phpass/PasswordHash.php';
require_once 'include/lib/pwgen.inc.php';
require_once 'hierarchy_validations.php';

$tree = new Hierarchy();
$user = new User();

$navigation[] = array("url" => "../admin/index.php", "name" => $langAdmin);

// javascript
load_js('jstree');
load_js('pwstrength.js');
$head_content .= <<<hContent
<script type="text/javascript">
/* <![CDATA[ */

    var lang = {
hContent;
$head_content .= "pwStrengthTooShort: '" . js_escape($langPwStrengthTooShort) . "', ";
$head_content .= "pwStrengthWeak: '" . js_escape($langPwStrengthWeak) . "', ";
$head_content .= "pwStrengthGood: '" . js_escape($langPwStrengthGood) . "', ";
$head_content .= "pwStrengthStrong: '" . js_escape($langPwStrengthStrong) . "'";
$head_content .= <<<hContent
    };

    $(document).ready(function() {
        $('#password').keyup(function() {
            $('#result').html(checkStrength($('#password').val()))
        });
    });

/* ]]> */
</script>
hContent;

$reqtype = '';
$all_set = register_posted_variables(array(
    'auth' => true,
    'uname' => true,
    'surname_form' => true,
    'givenname_form' => true,
    'email_form' => true,
    'verified_mail_form' => false,
    'language' => true,
    'department' => true,
    'am' => false,
    'phone' => false,
    'password' => true,
    'pstatus' => true,
    'rid' => false,
    'submit' => true));
$submit = isset($_POST['submit']) ? $_POST['submit'] : '';


if ($submit) {
    // register user
    $depid = intval(isset($_POST['department']) ? $_POST['department'] : 0);
    $proflanguage = $session->validate_language_code(@$_POST['language']);
    $verified_mail = isset($_REQUEST['verified_mail_form']) ? intval($_REQUEST['verified_mail_form']) : 2;

    $backlink = $_SERVER['SCRIPT_NAME'] .
            isset($rid) ? ('?id=' . intval($rid)) : '';

    // check if user name exists
    $user_exist = Database::get()->querySingle("SELECT username FROM user WHERE username=?s", $uname);

    // check if there are empty fields
    if (!$all_set) {
        $tool_content .= "<p class='caution'>$langFieldsMissing</p>
                        <br><br><p align='right'><a href='$backlink'>$langAgain</a></p>";
    } elseif ($user_exist) {
        $tool_content .= "<p class='caution'>$langUserFree</p>
                        <br><br><p align='right'><a href='$backlink'>$langAgain</a></p>";
    } elseif (!email_seems_valid($email_form)) {
        $tool_content .= "<p class='caution_small'>$langEmailWrong.</p>
                        <br /><br /><p align='right'><a href='$backlink'>$langAgain</a></p>";
    } else {
        validateNode(intval($depid), isDepartmentAdmin());
        $hasher = new PasswordHash(8, false);
        $password_encrypted = $hasher->HashPassword($password);
        $uid = Database::get()->query("INSERT INTO user
                                (surname, givenname, username, password, email, status, phone, am, registered_at, expires_at, lang, description, verified_mail, whitelist)
                                VALUES (?s, ?s, ?s, ?s, ?s, ?d, ?s, ?s , " . DBHelper::timeAfter() . "
                 , " . DBHelper::timeAfter(get_config('account_duration')) . "
                 , ?s, '', ?s, '')", $surname_form, $givenname_form, $uname, $password_encrypted, $email_form, $pstatus, $phone, $am, $proflanguage, $verified_mail)->lastInsertID;
        $user->refresh($uid, array(intval($depid)));

        // close request if needed
        if (!empty($rid)) {
            $rid = intval($rid);
            Database::get()->query("UPDATE user_request set state = 2, date_closed = NOW() WHERE id = ?d", $rid);
        }

        if ($pstatus == 1) {
            $message = $profsuccess;
            $reqtype = '';
            $type_message = $langAsProf;
        } else {
            $message = $usersuccess;
            $reqtype = '?type=user';
            $type_message = '';
            // $langAsUser;
        }
        $tool_content .= "<p class='success'>$message</p><br><br><p align='right'><a href='../admin/listreq.php$reqtype'>$langBackRequests</a></p>";

        // send email
        $telephone = get_config('phone');
        $emailsubject = "$langYourReg $siteName $type_message";
        $emailbody = "
$langDestination $givenname_form $surname_form

$langYouAreReg $siteName $type_message, $langSettings $uname
$langPass : $password
$langAddress $siteName $langIs: $urlServer
$langProblem

" . get_config('admin_name') . "
$langManager $siteName
$langTel $telephone
$langEmail : " . get_config('email_helpdesk') . "\n";
        send_mail('', '', '', $email_form, $emailsubject, $emailbody, $charset);
    }
} else {
    $lang = false;
    $ps = $pn = $pu = $pe = $pt = $pam = $pphone = $pcom = $language = '';
    if (isset($_GET['id'])) { // if we come from prof request
        $id = intval($_GET['id']);

        $res = Database::get()->querySingle("SELECT givenname, surname, username, email, faculty_id, phone, am,
                        comment, lang, date_open, status, verified_mail FROM user_request WHERE id =?d", $id);
        $ps = $res->surname;
        $pn = $res->givenname;
        $pu = $res->username;
        $pe = $res->email;
        $pv = intval($res->verified_mail);
        $pt = intval($res->faculty_id);
        $pam = $res->am;
        $pphone = $res->phone;
        $pcom = $res->comment;
        $language = $res->lang;
        $pstatus = intval($res->status);
        $pdate = nice_format(date('Y-m-d', strtotime($res->date_open)));

        // faculty id validation
        validateNode($pt, isDepartmentAdmin());

        // display actions toolbar
        $tool_content .= "<div id='operations_container'>
                <ul id='opslist'>
                <li><a href='listreq.php?id=$id&amp;close=1' onclick='return confirmation();'>$langClose</a></li>
                <li><a href='listreq.php?id=$id&amp;close=2'>$langRejectRequest</a></li>
                <li><a href='../admin/listreq.php$reqtype'>$langBackRequests</a></li>
                </ul></div>";
    } elseif (@$_GET['type'] == 'user') {
        $pstatus = 5;
    } else {
        $pstatus = 1;
    }

    if ($pstatus == 5) {
        $nameTools = $langUserDetails;
        $title = $langInsertUserInfo;
    } else {
        $nameTools = $langProfReg;
        $title = $langNewProf;
    }

    $tool_content .= "
        <form action='$_SERVER[SCRIPT_NAME]' method='post' onsubmit='return validateNodePickerForm();'>
        <fieldset>
        <legend>$title</legend>
        <table width='100%' align='left' class='tbl'>
          <tr><th class='left' width='180'><b>$langName:</b></th>
              <td class='smaller'><input class='FormData_InputText' type='text' name='givenname_form' value='" . q($pn) . "' />&nbsp;(*)</td></tr>
          <tr><th class='left'><b>$langSurname:</b></th>
              <td class='smaller'><input class='FormData_InputText' type='text' name='surname_form' value='" . q($ps) . "' />&nbsp;(*)</td></tr>
          <tr><th class='left'><b>$langUsername:</b></th>
              <td class='smaller'><input class='FormData_InputText' type='text' name='uname' value='" . q($pu) . "' autocomplete='off' />&nbsp;(*)</td></tr>
          <tr><th class='left'><b>$langPass:</b></th>
              <td><input class='FormData_InputText' type='text' name='password' value='" . genPass() . "' id='password' autocomplete='off'  />&nbsp;<span id='result'></span></td></tr>
          <tr><th class='left'><b>$langEmail:</b></th>
              <td class='smaller'><input class='FormData_InputText' type='text' name='email_form' value='" . q($pe) . "' />&nbsp;(*)</td></tr>
          <tr><th class='left'><b>$langEmailVerified:</b></th>
             <td>";
    $verified_mail_data = array(0 => $m['pending'], 1 => $m['yes'], 2 => $m['no']);
    if (isset($pv)) {
        $tool_content .= selection($verified_mail_data, "verified_mail_form", $pv);
    } else {
        $tool_content .= selection($verified_mail_data, "verified_mail_form");
    }
    $tool_content .= "</td></tr>
        <tr><th class='left'><b>$langPhone:</b></th>
            <td class='smaller'><input class='FormData_InputText' type='text' name='phone' value='" . q($pphone) . "' /></td></tr>
        <tr><th class='left'><b>$langFaculty:</b></th>
            <td>";
    $depid = (isset($pt)) ? $pt : null;
    if (isDepartmentAdmin()) {
        list($js, $html) = $tree->buildNodePicker(array('params' => 'name="department"', 'defaults' => $depid, 'tree' => null, 'useKey' => 'id', 'where' => "AND node.allow_user = true", 'multiple' => false, 'allowables' => $user->getDepartmentIds($uid)));
    } else {
        list($js, $html) = $tree->buildNodePicker(array('params' => 'name="department"', 'defaults' => $depid, 'tree' => null, 'useKey' => 'id', 'where' => "AND node.allow_user = true", 'multiple' => false));
    }
    $head_content .= $js;
    $tool_content .= $html;
    $tool_content .= "</td></tr>
        <tr><th class='left'><b>$langAm:</b></th>
            <td><input class='FormData_InputText' type='text' name='am' value='" . q($pam) . "' />&nbsp;</td></tr>
        <tr><th class='left'>$langLanguage:</th>
            <td>";
    $tool_content .= lang_select_options('language', '', $language);
    $tool_content .= "</td></tr>";
    if (isset($_GET['id'])) {
        $tool_content .="<tr><th class='left'><b>$langComments</b></th>
                                     <td>" . q($pcom) . "&nbsp;</td></tr>
                                 <tr><th class='left'><b>$langDate</b></th>
                                     <td>" . q($pdate) . "&nbsp;</td></tr>";
        $id_html = "<input type='hidden' name='rid' value='$id' />";
    } else {
        $id_html = '';
    }
    $tool_content .= "
        <tr><th>&nbsp;</th>
            <td class='right'><input type='submit' name='submit' value='$langRegistration' />
               </td></tr>
        </table>
      </fieldset><div class='right smaller'>$langRequiredFields</div>
        $id_html
        <input type='hidden' name='pstatus' value='$pstatus' />
        <input type='hidden' name='auth' value='1' />
        </form>";
    if ($pstatus == 5) {
        $reqtype = '?type=user';
    } else {
        $reqtype = '';
    }
    $tool_content .= "<p align='right'><a href='../admin/index.php'>$langBack</a></p>";
}

draw($tool_content, 3, null, $head_content);
