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
use Hautelook\Phpass\PasswordHash;

$require_usermanage_user = TRUE;

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/pwgen.inc.php';
require_once 'modules/auth/auth.inc.php';
require_once 'hierarchy_validations.php';
require_once 'modules/admin/custom_profile_fields_functions.php';

$tree = new Hierarchy();
$user = new User();

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    checkSecondFactorChallenge();
    $requiredFields = array('auth_form', 'surname_form',
        'givenname_form', 'language_form', 'department', 'pstatus');        
    if (get_config('am_required') and @$_POST['pstatus'] == 5) {
        $requiredFields[] = 'am_form';
    }
    if (get_config('email_required')) {
        $requiredFields[] = 'email_form';
    }
    if (isset($_POST['auth_form']) && $_POST['auth_form'] == 1) {
        $requiredFields[] = 'password';
    }
    augment_registered_posted_variables_arr($requiredFields, true);
    $fieldLabels = array_combine($requiredFields, array_fill(0, count($requiredFields), $langTheField));
    $v = new Valitron\Validator($_POST);
    $v->labels($fieldLabels);
    $v->addRule('usernameFree', function($field, $value, array $params) {
        return !user_exists($value);
    }, $langUserFree);
    $v->rule('required', $requiredFields);
    $v->rule('usernameFree', 'uname_form', $langUserFree);
    $v->rule('required', 'uname_form')->message($langTheFieldIsRequired)->label('');
    $v->rule('in', 'language_form', $session->active_ui_languages);
    $v->rule('in', 'auth_form', get_auth_active_methods());
    $v->rule('email', 'email_form');
    
    cpf_validate_format_valitron($v);
    
    if (!$v->validate()) {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
    } else {
        // register user
        $depid = intval(isset($_POST['department']) ? getDirectReference($_POST['department']) : 0);
        $verified_mail = intval($_POST['verified_mail_form']);
        $all_set = register_posted_variables(array(
            'auth_form' => true,
            'uname_form' => true,
            'surname_form' => true,
            'givenname_form' => true,
            'email_form' => true,
            'language_form' => true,
            'am_form' => false,
            'phone_form' => false,
            'password' => true,
            'pstatus' => true,
            'rid' => false,
            'submit' => true));

        if ($auth_form == 1) { // eclass authentication
            validateNode(intval($depid), isDepartmentAdmin());
            $hasher = new PasswordHash(8, false);
            $password_encrypted = $hasher->HashPassword($_POST['password']);
        } else {
            $password_encrypted = $auth_ids[$_POST['auth_form']];
        }
        $uid = Database::get()->query("INSERT INTO user
                (surname, givenname, username, password, email, status, phone, am, registered_at, expires_at, lang, description, verified_mail, whitelist)
                VALUES (?s, ?s, ?s, ?s, ?s, ?d, ?s, ?s, " . DBHelper::timeAfter() . ", " .
                        DBHelper::timeAfter(get_config('account_duration')) . ", ?s, '', ?s, '')",
             $surname_form, $givenname_form, $uname_form, $password_encrypted, $email_form, $pstatus, $phone_form, $am_form, $language_form, $verified_mail)->lastInsertID;
        // update personal calendar info table
        // we don't check if trigger exists since it requires `super` privilege
        Database::get()->query("INSERT IGNORE INTO personal_calendar_settings(user_id) VALUES (?d)", $uid);
        $user->refresh($uid, array(intval($depid)));
        user_hook($uid);
        //process custom profile fields values
        process_profile_fields_data(array('uid' => $uid));
        
        // close request if needed
        if ($rid) {
            $rid = intval($rid);
            Database::get()->query("UPDATE user_request set state = 2, date_closed = NOW() WHERE id = ?d", $rid);
            // copy Hybrid Auth external uid if available
            Database::get()->query('INSERT INTO user_ext_uid (user_id, auth_id, uid)
                SELECT ?d, auth_id, uid FROM user_request_ext_uid
                    WHERE user_request_id = ?d',
                $uid, $rid);
        }

        if ($pstatus == 1) {
            $message = $profsuccess;
            $reqtype = '';
            $type_message = $langAsProf;
        } else {
            $message = $usersuccess;
            $reqtype = '?type=user';
            $type_message = '';
        }

        // send email
        $telephone = get_config('phone');
        $emailsubject = "$langYourReg $siteName $type_message";

        $emailheader = "
            <!-- Header Section -->
            <div id='mail-header'>
                <br>
                <div>
                    <div id='header-title'>$langYouAreReg $siteName $type_message $langWithSuccess</div>
                </div>
            </div>";

        $emailmain = "
        <!-- Body Section -->
        <div id='mail-body'>
            <br>
            <div>$langSettings</div>
            <div id='mail-body-inner'>
                <ul id='forum-category'>
                    <li><span><b>$langUserCodename: </b></span> <span>$uname_form</span></li>
                    <li><span><b>$langPass: </b></span> <span>$password</span></li>
                    <li><span><b>$langAddress $siteName $langIs: </b></span> <span><a href='$urlServer'>$urlServer</a></span></li>
                </ul>
            </div>
            <div>
            <br>
                <p>$langProblem</p><br>" . get_config('admin_name') . "
                <ul id='forum-category'>
                    <li>$langManager: $siteName</li>
                    <li>$langTel: $telephone</li>
                    <li>$langEmail: " . get_config('email_helpdesk') . "</li>
                </ul></p>
            </div>
        </div>";


        $emailbody = $emailheader.$emailmain;

        $emailbodyplain = html2text($emailbody);

        send_mail_multipart('', '', '', $email_form, $emailsubject, $emailbodyplain, $emailbody);

        Session::Messages(array($message,
            "$langTheU \"$givenname_form $surname_form\" $langAddedU" .
            ((isset($auth) and $auth == 1)? " $langAndP": '')), 'alert-success');
        if ($rid) {
            $req_type = Database::get()->querySingle('SELECT status FROM user_request WHERE id = ?d', $rid)->status;
            redirect_to_home_page('modules/admin/listreq.php' .
                ($req_type == USER_STUDENT? '?type=user': ''));
        } else {
            redirect_to_home_page('modules/admin/newuseradmin.php' . $reqtype);
        }
    }    
}

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

// javascript
load_js('jstree3');
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

        $('#auth_selection').change(function() {
            var state = $(this).find(':selected').attr('value') != '1';
            $('#password').prop('disabled', state);
        }).change();
    });

/* ]]> */
</script>
hContent;

$reqtype = '';

if (isset($_GET['id'])) {
    $data['action_bar'] = action_bar(array(
        array('title' => $langBack,
              'url' => 'index.php',
              'icon' => 'fa-reply',
              'level' => 'primary-label'),
        array('title' => $langBackRequests,
              'url' => "listreq.php$reqtype",
              'icon' => 'fa-reply',
              'level' => 'primary'),
        array('title' => $langRejectRequest,
              'url' => "listreq.php?id=$_GET[id]&amp;close=2",
              'icon' => 'fa-ban',
              'level' => 'primary'),
        array('title' => $langClose,
              'url' => "listreq.php?id=$_GET[id]&amp;close=1",
              'icon' => 'fa-close',
              'level' => 'primary')));
} else {
    if (isset($rid) and $rid) {
        $backlink = "$_SERVER[SCRIPT_NAME]?id=$rid";
    } else {
        $backlink = $_SERVER['SCRIPT_NAME'];
    }

    $data['action_bar'] = action_bar(array(
        array('title' => $langBack,
              'class' => 'back_btn',
              'icon' => 'fa-reply',
              'level' => 'primary-label')));
}

$lang = false;
$data['ext_uid'] = $ext_uid = null;
$data['ps'] = $data['pn'] = $data['pu'] = $data['pe'] = $data['pam'] = $data['pphone'] = $data['pcom'] = $data['pdate'] = '';
$depid = Session::has('department')? intval(Session::get('department')): null;
$data['pv'] = Session::has('verified_mail_form')? Session::get('verified_mail_form'): '';
if (isset($_GET['id'])) { // if we come from prof request
    $data['id'] = $id = intval($_GET['id']);

    $res = Database::get()->querySingle("SELECT givenname, surname, username, email, faculty_id, phone, am,
                        comment, lang, date_open, status, verified_mail FROM user_request WHERE id =?d", $id);
    if ($res) {
        $data['ext_uid'] = $ext_uid = Database::get()->querySingle('SELECT *
            FROM user_request_ext_uid WHERE user_request_id = ?d', $id);
        $data['ps'] = $res->surname;
        $data['pn'] = $res->givenname;
        $data['pu'] = $res->username;
        $data['pe'] = $res->email;
        $data['pv'] = intval($res->verified_mail);
        $depid = intval($res->faculty_id);
        $data['pam'] = $res->am;
        $data['pphone'] = $res->phone;
        $data['pcom'] = $res->comment;
        $data['language'] = $res->lang;
        $data['pstatus'] = intval($res->status);
        $data['pdate'] = nice_format(date('Y-m-d', strtotime($res->date_open)));
        // faculty id validation
        if ($res->faculty_id) {
            validateNode($depid, isDepartmentAdmin());
        }
        $data['cpf_context'] = array('origin' => 'teacher_register', 'pending' => true, 'user_request_id' => $id);
    } else {
        $data['cpf_context'] = array('origin' => 'teacher_register');
    }
    $data['params'] = $params = '';
} elseif (@$_GET['type'] == 'user') {
    $data['pstatus'] =  $pstatus = USER_STUDENT;
    $data['cpf_context'] =  array('origin' => 'student_register');
    $pageName = $langUserDetails;
    $title = $langInsertUserInfo;
    $data['params'] =  $params = "?type=user";
} else {
    $data['pstatus'] =  $pstatus = USER_TEACHER;
    $data['cpf_context'] = array('origin' => 'teacher_register');
    $pageName = $langProfReg;
    $title = $langNewProf;
    $data['params'] =  $params = "?type=";
}


$active_auth_methods = get_auth_active_methods();
$data['eclass_method_unique'] = $eclass_method_unique = count($active_auth_methods) == 1 && $active_auth_methods[0] == 1;

$data['verified_mail_data'] =  array(0 => $m['pending'], 1 => $langYes, 2 => $langNo);

$nodePickerParams = array(
    'params' => 'name="department"',
    'defaults' => $depid,
    'tree' => null,
    'where' => "AND node.allow_user = true",
    'multiple' => false);
if (isDepartmentAdmin()) {
    $nodePickerParams['allowables'] = $user->getDepartmentIds($uid);
}
list($tree_js, $tree_html) = $tree->buildNodePickerIndirect($nodePickerParams);
$head_content .= $tree_js;
$data['tree_html'] = $tree_html;

if (!$eclass_method_unique) {
    $data['auth_m'] = $auth_m = array();
    foreach ($active_auth_methods as $m) {
        $data['auth_m'][$m] = $auth_m[$m] = get_auth_info($m);
    }
}

if ($ext_uid) {
    $data['auth_ids'] = $auth_ids;
    $data['authFullName'] = $authFullName;
}

$data['menuTypeID'] = 3;
view('admin.users.newuseradmin', $data);     

function getValue($name, $default='') {
    if (Session::has($name)) {
        $value = Session::get($name);
    } else {
        $value = $default;
    }
    if ($value !== '') {
        return q($value);
    } else {
        return '';
    }
}
