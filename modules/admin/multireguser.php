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
define('SUFFIX_LEN', 4);

$require_usermanage_user = true;
require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'include/lib/pwgen.inc.php';
require_once 'modules/auth/auth.inc.php';
require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'hierarchy_validations.php';

$tree = new Hierarchy();
$user = new User();

load_js('jstree3');

$toolName = $langMultiRegUser;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

$error = '';
$acceptable_fields = array('first', 'last', 'email', 'id', 'phone', 'username', 'password');

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    checkSecondFactorChallenge();
    register_posted_variables(array('email_public' => true,
        'am_public' => true,
        'phone_public' => true), 'all', 'intval');
    $send_mail = isset($_POST['send_mail']) && $_POST['send_mail'];
    $data['unparsed_lines'] = '';
    $data['new_users_info'] = array();
    $newstatus = ($_POST['type'] == 'prof') ? 1 : 5;
    $departments = isset($_POST['facid']) ? arrayValuesDirect($_POST['facid']) : array();
    $am = $_POST['am'];
    $auth_methods_form = isset($_POST['auth_methods_form']) ? $_POST['auth_methods_form'] : 1;
    $fields = preg_split('/[ \t,]+/', $_POST['fields'], -1, PREG_SPLIT_NO_EMPTY);
    
    if ($auth_methods_form != 1) {
        $acceptable_fields = array('first', 'last', 'email', 'id', 'phone', 'username');
    }
    
    foreach ($fields as $field) {
        if (!in_array($field, $acceptable_fields)) {           
            Session::Messages("$langMultiRegFieldError <b>$field)</b>", 'alert-danger');
            redirect_to_home_page('modules/admin/multireguser.php');
            exit;
        }
    }

    // validation for departments
    foreach ($departments as $dep) {
        validateNode($dep, isDepartmentAdmin());
    }

    $numfields = count($fields);
    $line = strtok($_POST['user_info'], "\n");
    while ($line !== false) {
        $line = preg_replace('/#.*/', '', trim($line));
        if (!empty($line)) {
            $userl = preg_split('/[ \t]+/', $line);
            if (count($userl) >= $numfields) {
                $info = array();
                foreach ($fields as $field) {
                    $info[$field] = array_shift($userl);
                }

                if (!isset($info['email']) or !Swift_Validate::email($info['email'])) {
                    $info['email'] = '';
                }

                if (!empty($am)) {
                    if (!isset($info['id']) or empty($info['id'])) {
                        $info['id'] = $am;
                    } else {
                        $info['id'] = $am . ' - ' . $info['id'];
                    }
                }
                $surname = isset($info['last']) ? $info['last'] : '';
                $givenname = isset($info['first']) ? $info['first'] : '';
                if (!isset($info['username'])) {
                    $info['username'] = create_username($newstatus, $departments, $surname, $givenname, $_POST['prefix']);
                }
                if (!isset($info['password'])) {
                    $info['password'] = genPass();
                }
                $new = create_user($newstatus, $info['username'], $info['password'], $surname, $givenname, @$info['email'], $departments, @$info['id'], @$info['phone'], $_POST['lang'], $send_mail, $email_public, $phone_public, $am_public);
                if ($new === false) {
                    $data['unparsed_lines'] .= q($line . "\n" . $error . "\n");
                } else {
                    $data['new_users_info'][] = $new;

                    // Now, the $userl array should contain only course codes
                    foreach ($userl as $ccode) {
                        if (!register($new[0], $ccode)) {
                            $data['unparsed_lines'] .=
                                    sprintf($langMultiRegCourseInvalid . "\n", q("$info[last] $info[first] ($info[username])"), q($ccode));
                        }
                    }
                }
            } else {
                $data['unparsed_lines'] .= $line;
            }
        }
        $line = strtok("\n");
    }

    $view = 'admin.users.multireguser_result';
} else {
    Database::get()->queryFunc("SELECT id, name FROM hierarchy WHERE allow_course = true ORDER BY name", function($n) use(&$facs) {
        $facs[$n->id] = $n->name;
    });
    $data['access_options'] = array(ACCESS_PRIVATE => $langProfileInfoPrivate,
        ACCESS_PROFS => $langProfileInfoProfs,
        ACCESS_USERS => $langProfileInfoUsers);
    $data['action_bar'] = action_bar(array(
                array('title' => $langBack,
                    'url' => "index.php",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')
                ), false);

    $data['eclass_method_unique'] = TRUE;        
    $auth = get_auth_active_methods();
    $data['auth_m'] = array();
    foreach ($auth as $methods) {
        if ($methods != 1) {
            $data['eclass_method_unique'] = FALSE;
        }
        $auth_text = get_auth_info($methods);
        $data['auth_m'][$methods] = $auth_text;            
    }

    if (isDepartmentAdmin()) {
        list($js, $html) = $tree->buildUserNodePickerIndirect(array('params' => 'name="facid[]"',
            'allowables' => $user->getDepartmentIds($uid)));
    } else {
        list($js, $html) = $tree->buildUserNodePickerIndirect(array('params' => 'name="facid[]"'));
    }
    $head_content .= $js;
    $data['html'] = $html;
    
    $view = 'admin.users.multireguser';
}
$data['menuTypeID'] = 3;
view($view, $data);

function create_user($status, $uname, $password, $surname, $givenname, $email, $departments, $am, $phone, $lang, $send_mail, $email_public, $phone_public, $am_public) {
    global $charset, $langAsProf,
    $langYourReg, $siteName, $langDestination, $langYouAreReg,
    $langSettings, $langPass, $langAddress, $langIs, $urlServer,
    $langProblem, $langPassSameAuth,
    $langManager, $langTel, $langEmail,
    $profsuccess, $usersuccess, $langWithSuccess,
    $user, $langUserCodename, $uname_form, $auth_ids, $auth_methods_form;

    if ($status == 1) {
        $message = $profsuccess;
        $type_message = ' ' . $langAsProf;
    } else {
        $message = $usersuccess;
        $type_message = '';
    }

    if (Database::get()->querySingle('SELECT * FROM user WHERE username = ?s', $uname)) {
        $GLOBALS['error'] = "$GLOBALS[langMultiRegUsernameError] ($uname)";
        return false;
    }
    if (empty($am)) {        
        $am = ' ';
    }
    if (empty($phone)) {
        $phone = ' ';
    }
    
    if ($auth_methods_form != 1) { // other authentication methods
        $password_encrypted = $auth_ids[$auth_methods_form];
        $mail_message = $langPassSameAuth;
    } else {
        $hasher = new PasswordHash(8, false);
        $password_encrypted = $hasher->HashPassword($password);
        $mail_message = $password;
    }
    
    $id = Database::get()->query("INSERT INTO user
                (surname, givenname, username, password, email,
                 status, registered_at, expires_at, lang, am, phone,
                 email_public, phone_public, am_public, description, verified_mail, whitelist)
                VALUES (?s,?s,?s,?s,?s,?d," . DBHelper::timeAfter() . "," . DBHelper::timeAfter(get_config('account_duration')) . ",?s,?s,?s,?d,?d,?d,'',".EMAIL_VERIFIED.",'')"
                    , $surname, $givenname, $uname, $password_encrypted, mb_strtolower(trim($email)), $status, $lang, $am, $phone, $email_public, $phone_public, $am_public)->lastInsertID;
    // update personal calendar info table
    // we don't check if trigger exists since it requires `super` privilege
    Database::get()->query("INSERT IGNORE INTO personal_calendar_settings(user_id) VALUES (?d)", $id);
    $user->refresh($id, $departments);
    user_hook($id);
    $telephone = get_config('phone');
    $administratorName = get_config('admin_name');
    $emailhelpdesk = get_config('email_helpdesk');
    $emailsubject = "$langYourReg $siteName $type_message"; 

    $emailHeader = "
    <!-- Header Section -->
            <div id='mail-header'>
                <br>
                <div>
                    <div id='header-title'>$langYouAreReg $siteName $type_message $langWithSuccess</div>
                </div>
            </div>";

    $emailMain = "
    <!-- Body Section -->
        <div id='mail-body'>
            <br>
            <div>$langSettings</div>
            <div id='mail-body-inner'>
                <ul id='forum-category'>
                    <li><span><b>$langUserCodename: </b></span> <span>$uname</span></li>
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

    $emailbody = $emailHeader.$emailMain;

    $emailPlainBody = html2text($emailbody);
    if ($send_mail) {
        send_mail_multipart('', '', '', $email, $emailsubject, $emailPlainBody, $emailbody, $charset);
    }

    return array($id, $surname, $givenname, $email, $phone, $am, $uname, $password);
}

/**
 * @brief create username for new user
 * @param type $status
 * @param type $departments
 * @param type $nom
 * @param type $givenname
 * @param type $prefix
 * @return string
 */
function create_username($status, $departments, $nom, $givenname, $prefix) {
    
    $wildcard = str_pad('', SUFFIX_LEN, '_');
    $req = Database::get()->querySingle("SELECT username FROM user WHERE username LIKE ?s ORDER BY username DESC LIMIT 1", $prefix . $wildcard);
    if ($req) {
        $last_uname = $req->username;        
        $lastid = 1 + str_replace($prefix, '', $last_uname);
    } else {
        $lastid = 1;
    }
    do {
        $uname = $prefix . sprintf('%0' . SUFFIX_LEN . 'd', $lastid);
        $lastid++;
    } while (user_exists($uname));
    return $uname;
}

/**
 * @brief register user to course
 * @param type $uid
 * @param type $course_code
 * @return boolean
 */
function register($uid, $course_code) {
    
    $result = Database::get()->querySingle("SELECT code, id FROM course WHERE code = ?s OR public_code = ?s", $course_code, $course_code);
    if ($result) {
        Database::get()->query("INSERT INTO course_user
                                 SET course_id = ?d, user_id = ?d, status = "  . USER_STUDENT . ",
                                     reg_date = " . DBHelper::timeAfter() . ", 
                                     document_timestamp = " . DBHelper::timeAfter() . "", $result->id, $uid);
        return true;
    }
    return false;
}
