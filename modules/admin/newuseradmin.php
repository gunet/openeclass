<?php

/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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

$require_usermanage_user = true;

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
    print_a($_REQUEST);
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) {
        csrf_token_error();
    }
    $rid = intval($_POST['rid']);
    if (isset($_POST['type']) and $_POST['type'] == 'prof') { // change user rights
        $q = Database::get()->query("UPDATE user SET status = " . USER_TEACHER . " WHERE id = ?d", $_POST['u']);
        $depid = intval($_POST['department'] ?? 0);
        validateNode($depid, isDepartmentAdmin());
        $user->refresh($_POST['u'], array(intval($depid)));
        // send email
        $emailsubject = "$langRequestApproved";
        $emailheader = "
                <!-- Header Section -->
                <div id='mail-header'>
                    <br>
                    <div>
                        <div id='header-title'>$langRequestApproved</div>
                    </div>
                </div>";

        $emailmain = "
            <!-- Body Section -->
            <div id='mail-body'>
                <br>                
                <div id='mail-body-inner'>
                    <ul id='forum-category'>                        
                        <li><span><strong>$langWithCourseCreationRightsInfo $langInPlatform: </strong></span><span><a href='$urlServer'>$urlServer</a></span></li>
                    </ul>
                </div>
                <div>
                <br>
                    <p>$langProblem</p><br>" . get_config('admin_name') . "
                    <ul id='forum-category'>
                        <li>$langManager: $siteName</li>
                        <li>$langTel: " . get_config('phone') . "</li>
                        <li>$langEmail: " . get_config('email_helpdesk') . "</li>
                    </ul></p>
                </div>
            </div>";

        $emailbody = $emailheader . $emailmain;
        $emailbodyplain = html2text($emailbody);
        send_mail_multipart('', '', '', uid_to_email($_POST['u']), $emailsubject, $emailbodyplain, $emailbody);
        // close request
        Database::get()->query("UPDATE user_request set state = 2, date_closed = " . DBHelper::timeAfter() . " WHERE id = ?d", $rid);

        Session::flash('message', "$langUserRightsChanged");
        Session::flash('alert-class', 'alert-success');
    } else {
        $requiredFields = array('auth_form', 'surname_form',
            'givenname_form', 'language_form', 'department', 'pstatus');
        if (get_config('am_required') and $_POST['pstatus'] == USER_STUDENT) {
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
        $v->addRule('usernameFree', function ($field, $value, array $params) {
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
            //redirect_to_home_page('modules/admin/newuseradmin.php?id=' . $_POST['rid'] . '&auth=' . $_POST['auth_form']);
        } else {
            // register user
            $depid = intval($_POST['department'] ?? 0);
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
                'submit' => true));

            if ($auth_form == 1) { // eclass authentication
                validateNode(intval($depid), isDepartmentAdmin());
                $password_encrypted = password_hash($_POST['password'], PASSWORD_DEFAULT);
            } else {
                $password_encrypted = $auth_ids[$_POST['auth_form']];
            }

            if (isset($_POST['enable_course_registration'])) {
                $disable_course_registration = 0;
            } else {
                $disable_course_registration = 1;
            }

            if (isset($_POST['user_date_expires_at'])) {
                $expires_at = DateTime::createFromFormat("d-m-Y H:i", $_POST['user_date_expires_at']);
                $user_expires_at = $expires_at->format("Y-m-d H:i");
                $user_date_expires_at = $expires_at->format("d-m-Y H:i");
            } else {
                $expires_at = DateTime::createFromFormat("Y-m-d H:i", date('Y-m-d H:i', strtotime("now + 1 year")));
                $user_expires_at = $expires_at->format("Y-m-d H:i");
            }
            $uid = Database::get()->query("INSERT INTO user
                    (surname, givenname, username, password, email, status, phone, am, registered_at, expires_at, lang, description, verified_mail, whitelist, disable_course_registration)
                    VALUES (?s, ?s, ?s, ?s, ?s, ?d, ?s, ?s, " . DBHelper::timeAfter() . ", ?t, ?s, '', ?s, '', ?d)",
                $surname_form, $givenname_form, $uname_form,
                $password_encrypted, $email_form,
                $pstatus, $phone_form, $am_form,
                $user_expires_at, $language_form, $verified_mail, $disable_course_registration)->lastInsertID;
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
                $reqtype = '';
            } else {
                $reqtype = '?type=user';
            }

            // send email
            $emailsubject = "$langYourReg $siteName";
            $emailheader = "
                <!-- Header Section -->
                <div id='mail-header'>
                    <br>
                    <div>
                        <div id='header-title'>$langYouAreReg $siteName $langWithSuccess</div>
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
                        <li>$langTel: " . get_config('phone') . "</li>
                        <li>$langEmail: " . get_config('email_helpdesk') . "</li>
                    </ul></p>
                </div>
            </div>";

            $emailbody = $emailheader . $emailmain;
            $emailbodyplain = html2text($emailbody);
            send_mail_multipart('', '', '', $email_form, $emailsubject, $emailbodyplain, $emailbody);

            Session::flash('message', "$langTheU \"" . q($givenname_form) . " " . q($surname_form) . "\" $langWithSuccess");
            Session::flash('alert-class', 'alert-success');
        }
    }

    if ($rid) {
        $req_type = Database::get()->querySingle('SELECT status FROM user_request WHERE id = ?d', $rid)->status;
        redirect_to_home_page('modules/admin/listreq.php' . ($req_type == USER_STUDENT? '?type=user': ''));
    } else {
        redirect_to_home_page('modules/admin/newuseradmin.php' . $reqtype);
    }
}


$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
if (isset($_GET['id']) and isset($_GET['type']) and $_GET['type'] == 'prof') { // creating course request
    $toolName = $langCourseCreate;
} else {
    $toolName = $langCreateAccount;
}

// javascript
load_js('jstree3');
load_js('pwstrength.js');
load_js('bootstrap-datetimepicker');

$reqtype = '';
$data['existing_user'] = $existing_user = false;

if (isset($_GET['id'])) {
    $data['action_bar'] = action_bar(array(
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
        $backlink = 'index.php';
    }
    $data['action_bar'] = action_bar(array(
        array('title' => $langBack,
              'url' => $backlink,
              'class' => 'back_btn',
              'icon' => 'fa-reply',
              'level' => 'primary')));
}

$lang = false;
$expirationDate = DateTime::createFromFormat("Y-m-d H:i", date('Y-m-d H:i', strtotime("now") + get_config('account_duration')));
$data['expirationDatevalue'] = $expirationDate->format("d-m-Y H:i");
$data['ext_uid'] = $ext_uid = null;
$data['ps'] = $data['pn'] = $data['pu'] = $data['pe'] = $data['pam'] = $data['pphone'] = $data['pcom'] = $data['pdate'] = '';
$depid = Session::has('department')? intval(Session::get('department')): null;
$data['pv'] = Session::has('verified_mail_form')? Session::get('verified_mail_form'): '';
$data['auth'] = $data['u'] = '';
if (isset($_GET['auth'])) {
    $data['auth'] = $auth = intval($_GET['auth']);
}
if (isset($_GET['type'])) {
    $data['type'] = $_GET['type'];
} else {
    $data['type'] = '';
}

$data['id'] = $id = intval($_GET['id']);
$res = Database::get()->querySingle("SELECT givenname, surname, username, password, email, faculty_id, phone, am,
                        comment, lang, date_open, status, verified_mail FROM user_request WHERE id = ?d", $id);
if ($res) {
    $data['ext_uid'] = $ext_uid = Database::get()->querySingle('SELECT *
            FROM user_request_ext_uid WHERE user_request_id = ?d', $id);
    $data['ps'] = $res->surname;
    $data['pn'] = $res->givenname;
    $data['pu'] = $res->username;
    $data['password'] = $res->password;
    $data['pe'] = $res->email;
    $data['pv'] = intval($res->verified_mail);
    $depid = intval($res->faculty_id);
    $data['pam'] = $res->am;
    $data['pphone'] = $res->phone;
    $data['pcom'] = $res->comment;
    $data['language'] = $res->lang;
    if ($res->faculty_id) {
        validateNode($depid, isDepartmentAdmin());
    }
}

if (isset($_GET['id']) and isset($_GET['type']) and $_GET['type'] == 'prof') { // creating course request
    if ($res) {
        $u = Database::get()->querySingle("SELECT * FROM user WHERE BINARY username = ?s", $res->username);
        if ($u) {
            $data['existing_user'] = $existing_user = true;
            $data['u'] = $u->id;
        }
        $data['pstatus'] = $pstatus = USER_STUDENT;
        $cpf_context = array('origin' => 'student_register');
        $data['params'] = $params = '';
        $data['prof_selected'] = '';
        $data['user_selected'] = 'checked';
        $data['pdate'] = format_locale_date(strtotime($res->date_open), 'short', false);
        // faculty id validation

        $data['cpf_context'] = array('origin' => 'teacher_register', 'pending' => true, 'user_request_id' => $id);
    } else {
        $data['cpf_context'] = array('origin' => 'teacher_register');
    }
} else { // user account request
    $data['pstatus'] =  $pstatus = USER_STUDENT;
    $data['cpf_context'] = array('origin' => 'student_register');
    $data['params'] =  $params = '';
    $data['prof_selected'] = '';
    $data['user_selected'] = 'checked';
}

$active_auth_methods = get_auth_active_methods();
$data['eclass_method_unique'] = $eclass_method_unique = count($active_auth_methods) == 1 && $active_auth_methods[0] == 1;
$data['verified_mail_data'] =  array(0 => $langMailVerificationPendingU, 1 => $langYes, 2 => $langNo);

$nodePickerParams = array(
    'params' => 'name="department"',
    'defaults' => $depid,
    'tree' => null,
    'multiple' => false);
if (isDepartmentAdmin()) {
    $nodePickerParams['allowables'] = $user->getAdminDepartmentIds($uid);
}
list($tree_js, $tree_html) = $tree->buildUserNodePicker($nodePickerParams);
$head_content .= $tree_js;
$data['tree_html'] = $tree_html;

if (!$eclass_method_unique) {
    $data['auth_m'] = $auth_m = array();
    foreach ($active_auth_methods as $m) {
        $data['auth_m'][$m] = $auth_m[$m] = get_auth_info($m);
    }
}

if ($pstatus == USER_STUDENT) { // only for students
    if (get_config('am_required')) {
        $am_message = $langCompulsory;
    } else {
        $am_message = $langOptional;
    }
}

if ($ext_uid) {
    $data['auth_ids'] = $auth_ids;
    $data['authFullName'] = $authFullName;
}

view('admin.users.newuseradmin', $data);

/**
 * @param $name
 * @param $default
 * @return string
 */
function getValue($name, $default=''): string
{
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
