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
                        
        $('#auth_selection').change(function() {
            var state = $(this).attr('value')!='1';            
            $('#pass_form').prop('disabled', state);             
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

if (isset($_GET['id'])) {
    $tool_content .= action_bar(array(
        array('title' => $langBack,
              'url' => "../admin/index.php",
              'icon' => 'fa-reply',
              'level' => 'primary-label'),
        array('title' => $langBackRequests,
              'url' => "../admin/listreq.php$reqtype",
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
            'url' => "../admin/listreq.php$reqtype",
            'icon' => 'fa-reply',
            'level' => 'primary-label',
            'show' => (isset($submit) and $success))));
}
    
if ($submit) {
    // register user
    $depid = intval(isset($_POST['department']) ? $_POST['department'] : 0);
    $proflanguage = $session->validate_language_code(@$_POST['language']);
    $verified_mail = isset($_REQUEST['verified_mail_form']) ? intval($_REQUEST['verified_mail_form']) : 2;

    $auth_methods_form = isset($_POST['auth_methods_form']) ? $_POST['auth_methods_form'] : 1;
    // check if user name exists
    $user_exist = Database::get()->querySingle("SELECT username FROM user WHERE username=?s", $uname);

    // check if there are empty fields
    if (!$all_set) {
        $tool_content .= "<div class='alert alert-danger'>$langFieldsMissing <br /><a href='$backlink'>$langAgain</a></div>";        
    } elseif ($user_exist) {
        $tool_content .= "<div class='alert alert-danger'>$langUserFree <br /><a href='$backlink'>$langAgain</a></div>";
    } elseif (!email_seems_valid($email_form)) {
        $tool_content .= "<div class='alert alert-danger'>$langEmailWrong <br /><a href='$backlink'>$langAgain</a></div>";        
    } else {        
        if ($auth_methods_form == 1) { // eclass authentication
            validateNode(intval($depid), isDepartmentAdmin());
            $hasher = new PasswordHash(8, false);
            $password_encrypted = $hasher->HashPassword($password);
            $mail_message = $password;
        } else {
            $password_encrypted = $auth_ids[$auth_methods_form];
            $mail_message = $langPassSameAuth;
        }        
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
        $success = TRUE;
        $tool_content .= "<div class='alert alert-success'>$message</div><br><br><p align='pull-right'>";
        // send email
        $telephone = get_config('phone');
        $emailsubject = "$langYourReg $siteName $type_message";
        $emailbody = "
$langDestination $givenname_form $surname_form

$langYouAreReg $siteName $type_message, $langSettings $uname
$langPass : $mail_message
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
    $ps = $pn = $pu = $pe = $pt = $pam = $pphone = $pcom = $language = $pdate = '';
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
            $pt = intval($res->faculty_id);
            $pam = $res->am;
            $pphone = $res->phone;
            $pcom = $res->comment;
            $language = $res->lang;
            $pstatus = intval($res->status);
            $pdate = nice_format(date('Y-m-d', strtotime($res->date_open)));
            // faculty id validation
            if ($res->faculty_id) {            
                validateNode($pt, isDepartmentAdmin());
            }
        }

        // display actions toolbar        
    } elseif (@$_GET['type'] == 'user') {
        $pstatus = 5;
    } else {
        $pstatus = 1;
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
        <fieldset>
        <div class='form-group'>
        <label for='Name' class='col-sm-2 control-label'>$langName:</label>
            <div class='col-sm-10'>
              <input class='form-control' id='Name' type='text' name='givenname_form' value='" . q($pn) . "' placeholder='$langName'>
            </div>
        </div>
        <div class='form-group'>
        <label for='Sur' class='col-sm-2 control-label'>$langSurname:</label>
            <div class='col-sm-10'>
              <input class='form-control' id='Sur' type='text' name='surname_form' value='" . q($ps) . "' placeholder='$langSurname'>
            </div>
        </div>
        <div class='form-group'>
        <label for='Username' class='col-sm-2 control-label'>$langUsername:</label>
            <div class='col-sm-10'>
                <input class='form-control' id='Username' type='text' name='uname' value='" . q($pu) . "' autocomplete='off' placeholder='$langUsername'>
            </div>
        </div>";
        
        $eclass_method_unique = TRUE;        
        $auth = get_auth_active_methods();
        foreach ($auth as $methods) {
            if ($methods != 1) {
                $eclass_method_unique = FALSE;
            }
        }
        
        if (!$eclass_method_unique) {
            $auth_m = array();
            $tool_content .= "<div class='form-group'>
            <label for='passsword' class='col-sm-2 control-label'>$langMethods</label>
            <div class='col-sm-10'>";        
        
            foreach ($auth as $methods) { 
                $auth_text = get_auth_info($methods);
                $auth_m[$methods] = $auth_text;            
            }
            $tool_content .= selection($auth_m, "auth_methods_form", '', "id = 'auth_selection' class='form-control'");
            $tool_content .= "</div></div>";

            $tool_content .= "<div class='form-group' id='pass_form'>
            <label for='passsword' class='col-sm-2 control-label'>$langPass:</label>
                <div class='col-sm-10'>
                  <input class='form-control' type='text' name='password' value='" . genPass() . "' id='password' autocomplete='off'  placeholder='$langPass'/><span id='result'></span>
                </div>
            </div>";
            
        } else {
        
        $tool_content .= "<div class='form-group'>
        <label for='passsword' class='col-sm-2 control-label'>$langPass:</label>
            <div class='col-sm-10'>
              <input class='form-control' type='text' name='password' value='" . genPass() . "' id='password' autocomplete='off'  placeholder='$langPass'/><span id='result'></span>
            </div>
        </div>";
        }
        
        $tool_content .= "
        <div class='form-group'>
        <label for='email' class='col-sm-2 control-label'>$langEmail:</label>
            <div class='col-sm-10'>
              <input class='form-control' id='email' type='text' name='email_form' value='" . q($pe) . "' placeholder='$langEmail'>
            </div>
        </div>
        <div class='form-group'>
          <label for='emailverified' class='col-sm-2 control-label'>$langEmailVerified:</label>
            <div class='col-sm-10'>";
        $verified_mail_data = array(0 => $m['pending'], 1 => $m['yes'], 2 => $m['no']);
        if (isset($pv)) {
            $tool_content .= selection($verified_mail_data, "verified_mail_form", $pv, "class='form-control'");
        } else {
            $tool_content .= selection($verified_mail_data, "verified_mail_form", '', "class='form-control'");
        }
        $tool_content .= "</div></div>
        <div class='form-group'>
        <label for='phone' class='col-sm-2 control-label'>$langPhone:</label>
            <div class='col-sm-10'>            
                <input class='form-control' id='phone' type='text' name='phone' value='" . q($pphone) . "' placeholder='$langPhone'>
            </div>
        </div>
        <div class='form-group'>
        <label for='faculty' class='col-sm-2 control-label'>$langFaculty:</label>
            <div class='col-sm-10'>";
        $depid = (isset($pt)) ? $pt : null;
        if (isDepartmentAdmin()) {
            list($js, $html) = $tree->buildNodePicker(array('params' => 'name="department"', 'defaults' => $depid, 'tree' => null, 'useKey' => 'id', 'where' => "AND node.allow_user = true", 'multiple' => false, 'allowables' => $user->getDepartmentIds($uid)));
        } else {
            list($js, $html) = $tree->buildNodePicker(array('params' => 'name="department"', 'defaults' => $depid, 'tree' => null, 'useKey' => 'id', 'where' => "AND node.allow_user = true", 'multiple' => false));
        }
        $head_content .= $js;
        $tool_content .= $html;
        $tool_content .= "</div></div>
        <div class='form-group'>
        <label for='am' class='col-sm-2 control-label'>$langAm:</label>
           <div class='col-sm-10'>
               <input class='form-control' id='am' type='text' name='am' value='" . q($pam) . "' placeholder='$langOptional'>
           </div>
        </div>
        <div class='form-group'>
        <label for='lang' class='col-sm-2 control-label'>$langLanguage:</label>
        <div class='col-sm-10'>";
        $tool_content .= lang_select_options('language', "class='form-control'", $language);
        $tool_content .= "</div></div>";
        if (isset($_GET['id'])) {
            @$tool_content .= "<div class='form-group'><label for='comments' class='col-sm-2 control-label'>$langComments</label>
                                <div class='col-sm-10'>" . q($pcom) . "</div>
                            </div>
                            <div class='form-group'><label for='date' class='col-sm-2 control-label'>$langDate</label>
                                <div class='col-sm-10'>" . q($pdate) . "</div></div>";            
            $tool_content .= "<input type='hidden' name='rid' value='$id' />";
        }
        $tool_content .= "<div class='col-sm-offset-2 col-sm-10'>                   
                            <input class='btn btn-primary' type='submit' name='submit' value='$langRegistration'>
                        </div>              
        <input type='hidden' name='pstatus' value='$pstatus' />
        <input type='hidden' name='auth' value='1' />
        </fieldset>
        </form>
        </div>";
    if ($pstatus == 5) {
        $reqtype = '?type=user';
    } else {
        $reqtype = '';
    }    
}

draw($tool_content, 3, null, $head_content);
