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
require_once 'modules/auth/auth.inc.php';
require_once 'hierarchy_validations.php';
require_once 'modules/admin/custom_profile_fields_functions.php';

$tree = new Hierarchy();
$user = new User();

if (isset($_POST['submit'])) {
    $requiredFields = array('auth_form', 'email_form', 'surname_form',
        'givenname_form', 'language_form', 'department', 'pstatus');
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
        $user->refresh($uid, array(intval($depid)));
        user_hook($uid);
        //process custom profile fields values
        process_profile_fields_data(array('uid' => $uid));

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
        $success = TRUE;
        // send email
        $telephone = get_config('phone');
        $emailsubject = "$langYourReg $siteName $type_message";
        $emailbody = "
$langDestination $givenname_form $surname_form

$langYouAreReg $siteName $type_message, $langSettings $uname_form
$langPass : $password
$langAddress $siteName $langIs: $urlServer
$langProblem

" . get_config('admin_name') . "
$langManager $siteName
$langTel $telephone
$langEmail : " . get_config('email_helpdesk') . "\n";
        send_mail('', '', '', $email_form, $emailsubject, $emailbody, $charset);
        Session::Messages(array($message,
            "$langTheU \"$givenname_form $surname_form\" $langAddedU" .
            ((isset($auth) and $auth == 1)? " $langAndP": '')), 'alert-success');
    }
    redirect_to_home_page('modules/admin/newuseradmin.php');
}

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

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
        $backlink = $_SERVER['SCRIPT_NAME'];
    }

    $tool_content .= action_bar(array(
        array('title' => $langBack,
              'class' => 'back_btn',
              'icon' => 'fa-reply',
              'level' => 'primary-label'),
        array('title' => $langBackRequests,
            'url' => "listreq.php$reqtype",
            'icon' => 'fa-reply',
            'level' => 'primary-label',
            'show' => (isset($submit) and $success))));
}

$lang = false;
$ps = $pn = $pu = $pe = $pam = $pphone = $pcom = $pdate = '';
$depid = Session::has('department')? intval(Session::get('department')): null;
$pv = Session::has('verified_mail_form')? Session::get('verified_mail_form'): '';
if (isset($_GET['id'])) { // if we come from prof request
    $id = intval($_GET['id']);

    $res = Database::get()->querySingle("SELECT givenname, surname, username, email, faculty_id, phone, am,
                        comment, lang, date_open, status, verified_mail FROM user_request WHERE id =?d", $id);
    if ($res) {
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
        $pdate = nice_format(date('Y-m-d', strtotime($res->date_open)));
        // faculty id validation
        if ($res->faculty_id) {
            validateNode($depid, isDepartmentAdmin());
        }
        $cpf_context = array('origin' => 'teacher_register', 'pending' => true, 'user_request_id' => $id);
    } else {
        $cpf_context = array('origin' => 'teacher_register');
    }
} elseif (@$_GET['type'] == 'user') {
    $pstatus = 5;
    $cpf_context = array('origin' => 'student_register');
} else {
    $pstatus = 1;
    $cpf_context = array('origin' => 'teacher_register');
}

if ($pstatus == 5) {
    $pageName = $langUserDetails;
    $title = $langInsertUserInfo;
} else {
    $pageName = $langProfReg;
    $title = $langNewProf;
}

$tool_content .= "<div class='form-wrapper'>
        <form class='form-horizontal' role='form' action='$_SERVER[SCRIPT_NAME]' method='post' onsubmit='return validateNodePickerForm();'>
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

$verified_mail_data = array(0 => $m['pending'], 1 => $m['yes'], 2 => $m['no']);

$nodePickerParams = array(
    'params' => 'name="department"',
    'defaults' => $depid,
    'tree' => null,
    'useKey' => 'id',
    'where' => "AND node.allow_user = true",
    'multiple' => false);
if (isDepartmentAdmin()) {
    $nodePickerParams['allowables'] = $user->getDepartmentIds($uid);
}
list($tree_js, $tree_html) = $tree->buildNodePicker($nodePickerParams);
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
        getValue('password', genPass()) . " id='password' autocomplete='off' placeholder='" . q($langPass) . "'><span id='result'></span>");
formGroup('email_form', $langEmail,
    "<input class='form-control' id='email_form' type='text' name='email_form'" .
    getValue('email_form', $pe) . " placeholder='" . q($langEmail) . "'>");
formGroup('verified_mail_form', $langEmailVerified,
    selection($verified_mail_data, "verified_mail_form", $pv, "class='form-control'"));
formGroup('phone_form', $langPhone,
    "<input class='form-control' id='phone_form' type='text' name='phone_form'" .
    getValue('phone_form', $pphone) . " placeholder='" . q($langPhone) . "'>");
formGroup('faculty', $langFaculty, $tree_html);
formGroup('am_form', $langAm, 
    "<input class='form-control' id='am_form' type='text' name='am_form'" .
    q('am_form', $pam) . " placeholder='$langOptional'>");
formGroup('language_form', $langLanguage,
    lang_select_options('language_form', "class='form-control'",
        Session::has('language_form')? Session::get('language_form'): $language));

if (isset($_GET['id'])) {
    formGroup('comments', $langComments, q($pcom));
    formGroup('date', $langDate, q($pdate));
    $tool_content .= "<input type='hidden' name='rid' value='$id'>";
}

//add custom profile fields input
$tool_content .= render_profile_fields_form($cpf_context, true);

$tool_content .= "
        <div class='col-sm-offset-2 col-sm-10'>
          <input class='btn btn-primary' type='submit' name='submit' value='$langRegistration'>
        </div>
        <input type='hidden' name='pstatus' value='$pstatus'>
      </fieldset>
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
