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
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
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
        $depid = intval(isset($_POST['department']) ? $_POST['department'] : 0);
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
        $telephone = get_config('phone');
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
                    <li>$langTel: $telephone</li>
                    <li>$langEmail: " . get_config('email_helpdesk') . "</li>
                </ul></p>
            </div>
        </div>";


        $emailbody = $emailheader.$emailmain;

        $emailbodyplain = html2text($emailbody);

        send_mail_multipart('', '', '', $email_form, $emailsubject, $emailbodyplain, $emailbody);

        Session::Messages(array($langWithSuccess, "$langTheU \"$givenname_form $surname_form\" $langAddedU"), 'alert-success');
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
$toolName = $langCreateAccount;

// javascript
load_js('jstree3');
load_js('pwstrength.js');
load_js('bootstrap-datetimepicker');
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

$head_content .= "<script type='text/javascript'>
        $(function() {
            $('#user_date_expires_at').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '".$language."',
                minuteStep: 10,
                autoclose: true
            });
        });
    </script>";

$reqtype = '';

if (isset($_GET['id'])) {
    $tool_content .= action_bar(array(
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
        $backlink = 'index.php';
    }

    $tool_content .= action_bar(array(
        array('title' => $langBack,
              'url' => $backlink,
              'class' => 'back_btn',
              'icon' => 'fa-reply',
              'level' => 'primary-label')));
}

$lang = false;
$ext_uid = null;
$ps = $pn = $pu = $pe = $pam = $pphone = $pcom = $pdate = '';
$depid = Session::has('department')? intval(Session::get('department')): null;
$pv = Session::has('verified_mail_form')? Session::get('verified_mail_form'): '';
if (isset($_GET['id'])) { // if we come from prof request
    $id = intval($_GET['id']);

    $res = Database::get()->querySingle("SELECT givenname, surname, username, email, faculty_id, phone, am,
                        comment, lang, date_open, status, verified_mail FROM user_request WHERE id = ?d", $id);
    if ($res) {
        $ext_uid = Database::get()->querySingle('SELECT *
            FROM user_request_ext_uid WHERE user_request_id = ?d', $id);
        $ps = $res->surname;
        $pn = $res->givenname;
        $pu = $res->username;
        $pe = $res->email;
        $pv = intval($res->verified_mail);
        $depid = intval($res->faculty_id);
        $pam = $res->am;
        $pphone = $res->phone;
        $pcom = $res->comment;
        $language = $res->lang;
        $pstatus = intval($res->status);
        if ($pstatus == USER_TEACHER) {
            $cpf_context = array('origin' => 'teacher_register');
            $params = "?type=prof";
            $prof_selected = 'checked';
            $user_selected = '';
        } else {
            $pstatus = USER_STUDENT;
            $cpf_context = array('origin' => 'student_register');
            $pageName = $langCreateAccount;
            $params = '';
            $prof_selected = '';
            $user_selected = 'checked';
        }
        $pdate = format_locale_date(strtotime($res->date_open), 'short', false);
        // faculty id validation
        if ($res->faculty_id) {
            validateNode($depid, isDepartmentAdmin());
        }
        $cpf_context = array('origin' => 'teacher_register', 'pending' => true, 'user_request_id' => $id);
    } else {
        $cpf_context = array('origin' => 'teacher_register');
    }
    $params = '';
} else { // new user
    $pstatus = USER_STUDENT;
    $cpf_context = array('origin' => 'student_register');
    $params = '';
    $prof_selected = '';
    $user_selected = 'checked';
}
$tool_content .= "<div class='form-wrapper'>
        <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]$params' method='post' onsubmit='return validateNodePickerForm();'>
        <fieldset>";
formGroup('givenname_form', $langName,
    "<input class='form-control' id='givenname_form' type='text' name='givenname_form'" .
        getValue('givenname_form', $pn) . " placeholder='$langName'>");
formGroup('surname_form', $langSurname,
    "<input class='form-control' id='surname_form' type='text' name='surname_form'" .
        getValue('surname_form', $ps) . " placeholder='$langSurname'>");
formGroup('uname_form', $langUsername,
    "<input class='form-control' id='Username' type='text' name='uname_form'" .
        getValue('uname_form', $pu) . " autocomplete='off' placeholder='$langUsername'>");

$active_auth_methods = get_auth_active_methods();
$eclass_method_unique = count($active_auth_methods) == 1 && $active_auth_methods[0] == 1;

$verified_mail_data = array(0 => $m['pending'], 1 => $langYes, 2 => $langNo);

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

if ($eclass_method_unique) {
    $tool_content .= "<input type='hidden' name='auth_form' value='1'>";
} else {
    $auth_m = array();
    foreach ($active_auth_methods as $m) {
        $auth_m[$m] = get_auth_info($m);
    }
    formGroup('auth_selection', $langEditAuthMethod,
        selection($auth_m, 'auth_form', '', "id='auth_selection' class='form-control'"));
}

formGroup('passsword_form', $langPass,
    "<input class='form-control' type='text' name='password'" .
        getValue('password', choose_password_strength()) . " id='password' autocomplete='off' placeholder='" . q($langPass) . "'><span id='result'></span>");
if (get_config('email_required')) {
    $email_message = "$langEmail $langCompulsory";
} else {
    $email_message = "$langEmail $langOptional";
}
formGroup('email_form', $langEmail,
    "<input class='form-control' id='email_form' type='text' name='email_form'" .
    getValue('email_form', $pe) . " placeholder='" . q($email_message) . "'>");
formGroup('verified_mail_form', $langEmailVerified,
    selection($verified_mail_data, "verified_mail_form", $pv, "class='form-control'"));
formGroup('phone_form', $langPhone,
    "<input class='form-control' id='phone_form' type='text' name='phone_form'" .
    getValue('phone_form', $pphone) . " placeholder='" . q($langPhone) . "'>");
formGroup('faculty', $langFaculty, $tree_html);

formGroup('user_rights_form', $langUserPermissions,
    "<div class='col-sm-10'>
        <div class='radio'>
            <input type='radio' name='pstatus' value='" . USER_STUDENT . "' id='norights-option' $user_selected>$langWithNoCourseCreationRights
        </div>
        <div class='radio'>
            <input type='radio' name='pstatus' value='" . USER_TEACHER . "' id='rights-option' $prof_selected>$langWithCourseCreationRights
        </div>
        <div class='checkbox'>
            <input type='checkbox' name='enable_course_registration' value='1' checked>$langInfoEnableCourseRegistration
        </div>
    </div>
");

if ($pstatus == USER_STUDENT) { // only for students
    if (get_config('am_required')) {
        $am_message = $langCompulsory;
    } else {
        $am_message = $langOptional;
    }
    formGroup('am_form', $langAm,
        "<input class='form-control' id='am_form' type='text' name='am_form'" .
        getValue('am_form', $pam) . " placeholder='" . q($am_message) . "'>");
}

if (get_config('block_duration_account')) {
    $tool_content .= "<div class='input-append date form-group'>
                        <label class='col-sm-2 control-label'>$langExpirationDate:</label>
                        <div class='col-sm-10'>
                            <span class='help-block'>$lang_message_block_duration_account</span>
                        </div>
                    </div>";
} else {
    $expirationDate = DateTime::createFromFormat("Y-m-d H:i", date('Y-m-d H:i', strtotime("now") + get_config('account_duration')));
    $tool_content .= "<div class='input-append date form-group'>
                        <label class='col-sm-2 control-label'>$langExpirationDate:</label>
                        <div class='col-sm-10'>
                            <div class='input-group'>
                                <input class='form-control' id='user_date_expires_at' name='user_date_expires_at' type='text' value='" . $expirationDate->format("d-m-Y H:i") . "'>
                                <span class='input-group-addon'><i class='fa fa-calendar'></i></span>
                            </div>
                        </div>
                    </div>";
}

formGroup('language_form', $langLanguage,
    lang_select_options('language_form', "class='form-control'",
        Session::has('language_form')? Session::get('language_form'): $language));

if ($ext_uid) {
    $provider_icon = $themeimg . '/' . $auth_ids[$ext_uid->auth_id] . '.png';
    $provider_full_name = $authFullName[$ext_uid->auth_id];
    formGroup('provider', $langProviderConnectWith,
        "<p class='form-control-static'>
           <img src='$provider_icon' alt=''>&nbsp;" . q($provider_full_name) .
           "<br /><small>$langProviderConnectWithTooltip</small></p>");
}

if (isset($_GET['id'])) {
    formGroup('comments', $langComments, '<p class="form-control-static">' . q($pcom) . '</p>');
    formGroup('date', $langDate, '<p class="form-control-static">' . q($pdate) . '</p>');
    $tool_content .= "<input type='hidden' name='rid' value='$id'>";
}
if (isset($pstatus)) {
    $tool_content .= "<input type='hidden' name='pstatus' value='$pstatus'>";
}

// add custom profile fields input
$tool_content .= render_profile_fields_form($cpf_context, true);

$tool_content .= "
        <div class='col-sm-offset-2 col-sm-10'>
          <input class='btn btn-primary' type='submit' name='submit' value='$langRegistration'>
        </div>
      </fieldset>
      ". generate_csrf_token_form_field() ."
    </form>
  </div>";

draw($tool_content, 3, null, $head_content);

function formGroup($name, $label, $input) {
    global $tool_content;
    if (Session::hasError($name)) {
        $form_class = 'form-group has-error';
        $help_block = '<span class="help-block">' . Session::getError($name) . '</span>';
    } else {
        $form_class = 'form-group';
        $help_block = '';
    }
    $tool_content .= "
      <div class='$form_class'>
        <label for='$name' class='col-sm-2 control-label'>" . q($label) . ":</label>
        <div class='col-sm-10'>$input$help_block</div>
      </div>";
}

function getValue($name, $default='') {
    if (Session::has($name)) {
        $value = Session::get($name);
    } else {
        $value = $default;
    }
    if ($value !== '') {
        return " value='" . q($value) . "'";
    } else {
        return '';
    }
}
