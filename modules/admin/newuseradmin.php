<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

$tree = new hierarchy();
$userObj = new user();

load_js('jquery');
load_js('jquery-ui-new');
load_js('jstree');

$navigation[] = array("url" => "../admin/index.php", "name" => $langAdmin);

$reqtype = '';
$all_set = register_posted_variables(array(
        'auth' => true,
        'uname' => true,
        'nom_form' => true,
        'prenom_form' => true,
        'email_form' => true,
        'verified_mail_form' => false,
        'language' => true,
        'department' => true,
        'am' => false,
        'phone' => false,
        'password' => true,
        'pstatut' => true,
        'rid' => false,
        'submit' => true));
$submit = isset($_POST['submit'])?$_POST['submit']:'';


if($submit) {
        // register user
        $depid = intval(isset($_POST['department'])? $_POST['department']: 0);
        $proflanguage = isset($_POST['language'])? $_POST['language']: '';
        if (!isset($native_language_names[$proflanguage])) {
                //$proflanguage = langname_to_code($language);
                $proflanguage = $language;
        }
        $verified_mail = isset($_REQUEST['verified_mail_form'])?intval($_REQUEST['verified_mail_form']):2;

        $backlink = $_SERVER['PHP_SELF'] .
                    isset($rid)? ('?id=' . intval($rid)): '';

        // check if user name exists
        $username_check = db_query("SELECT username FROM `$mysqlMainDb`.user 
                        WHERE username=".autoquote($uname));
        $user_exist = (mysql_num_rows($username_check) > 0);

        // check if there are empty fields
        if (!$all_set) {
                $tool_content .= "<p class='caution'>$langFieldsMissing</p>
                        <br><br><p align='right'><a href='$backlink'>$langAgain</a></p>";
        } elseif ($user_exist) {
                $tool_content .= "<p class='caution'>$langUserFree</p>
                        <br><br><p align='right'><a href='$backlink'>$langAgain</a></p>";
        } elseif(!email_seems_valid($email_form)) {
                $tool_content .= "<p class='caution_small'>$langEmailWrong.</p>
                        <br /><br /><p align='right'><a href='$backlink'>$langAgain</a></p>";
        } else {
                $registered_at = time();
                $expires_at = time() + get_config('account_duration');
                $password_encrypted = md5($password);
                $inscr_user = db_query("INSERT INTO user
                                (nom, prenom, username, password, email, statut, phone, am, registered_at, expires_at, lang, description, verified_mail)
                                VALUES (" .
                                autoquote($nom_form) . ', '.
                                autoquote($prenom_form) . ', '.
                                autoquote($uname) . ", '$password_encrypted', ".
                                autoquote($email_form) .
                                ", $pstatut, ".autoquote($phone).", ".autoquote($am).", $registered_at, $expires_at, '$proflanguage', '', $verified_mail)");
                $uid = mysql_insert_id();
                $userObj->refresh($uid, array(intval($depid)));

                // close request if needed
                if (!empty($rid)) {
                        $rid = intval($rid);
                        db_query("UPDATE user_request set status = 2, date_closed = NOW() WHERE id = $rid");
                }

                if ($pstatut == 1) {
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
$langDestination $prenom_form $nom_form

$langYouAreReg $siteName $type_message, $langSettings $uname
$langPass : $password
$langAddress $siteName $langIs: $urlServer
$langProblem

$administratorName $administratorSurname
$langManager $siteName
$langTel $telephone
$langEmail : $emailhelpdesk
";
                send_mail('', '', '', $email_form, $emailsubject, $emailbody, $charset);
        }

} else {
        $lang = false;
        $ps = $pn = $pu = $pe = $pt = $pam = $pphone = $pcom = $language = '';
        if (isset($_GET['id'])) { // if we come from prof request
                $id = $_GET['id'];
                // display actions toolbar
                $tool_content .= "<div id='operations_container'>
                <ul id='opslist'>
                <li><a href='listreq.php?id=$id&amp;close=1' onclick='return confirmation();'>$langClose</a></li>
                <li><a href='listreq.php?id=$id&amp;close=2'>$langRejectRequest</a></li>";
                if (isset($_GET['id'])) {
                        $tool_content .= "
                        <li><a href='../admin/listreq.php$reqtype'>$langBackRequests</a></li>";
                }
                $tool_content .= "</ul></div>";
                $res = mysql_fetch_array(db_query("SELECT name, surname, uname, email, faculty_id, phone, am,
                        comment, lang, date_open, statut, verified_mail FROM user_request WHERE id = $id"));
                $ps = $res['surname'];
                $pn = $res['name'];
                $pu = $res['uname'];
                $pe = $res['email'];
                $pv = intval($res['verified_mail']);
                $pt = intval($res['faculty_id']);
                $pam = $res['am'];
                $pphone = $res['phone'];
                $pcom = $res['comment'];
                $language = $res['lang'];
                $pstatut = intval($res['statut']);
                $pdate = nice_format(date('Y-m-d', strtotime($res['date_open'])));
        } elseif (@$_GET['type'] == 'user') {
                $pstatut = 5;
        } else {
                $pstatut = 1;
        }

        if ($pstatut == 5) {
                $nameTools = $langUserDetails;
                $title = $langInsertUserInfo;
        } else {
                $nameTools = $langProfReg;
                $title = $langNewProf;
        }

        $tool_content .= "
        <form action='$_SERVER[PHP_SELF]' method='post' onsubmit='return validateNodePickerForm();'>
        <fieldset>
        <legend>$title</legend>  
        <table width='100%' align='left' class='tbl'>
        <tr><th class='left' width='180'><b>$langName:</b></th>
        <td class='smaller'><input class='FormData_InputText' type='text' name='prenom_form' value='".q($pn)."' />&nbsp;(*)</td></tr>
        <tr><th class='left'><b>$langSurname:</b></th>
        <td class='smaller'><input class='FormData_InputText' type='text' name='nom_form' value='".q($ps)."' />&nbsp;(*)</td></tr>
        <tr><th class='left'><b>$langUsername:</b></th>
        <td class='smaller'><input class='FormData_InputText' type='text' name='uname' value='".q($pu)."' />&nbsp;(*)</td></tr>
        <tr><th class='left'><b>$langPass:</b></th>
        <td><input class='FormData_InputText' type='text' name='password' value='".create_pass()."' /></td></tr>
        <tr><th class='left'><b>$langEmail:</b></th>
        <td class='smaller'><input class='FormData_InputText' type='text' name='email_form' value='".q($pe)."' />&nbsp;(*)</td></tr>
        <tr><th class='left'><b>$langEmailVerified:</b></th>
        <td>";
        $verified_mail_data = array();
        $verified_mail_data[0] = $m['pending'];
        $verified_mail_data[1] = $m['yes'];
        $verified_mail_data[2] = $m['no'];
        if (isset($pv)) {
                $tool_content .= selection($verified_mail_data,"verified_mail_form",$pv);
        } else {
                $tool_content .= selection($verified_mail_data,"verified_mail_form");
        }
        $tool_content .= "</td></tr>
        <tr><th class='left'><b>$langPhone:</b></th>
            <td class='smaller'><input class='FormData_InputText' type='text' name='phone' value='".q($pphone)."' /></td></tr>
        <tr><th class='left'><b>$langFaculty:</b></th>
            <td>";
        $depid = (isset($pt)) ? $pt : null;
        list($js, $html) = $tree->buildNodePicker(array('params' => 'name="department"', 'defaults' => $depid, 'tree' => null, 'useKey' => 'id', 'where' => "AND node.allow_user = true", 'multiple' => false));
        $head_content .= $js;
        $tool_content .= $html;
        $tool_content .= "</td></tr>
        <tr><th class='left'><b>$langAm:</b></th>
            <td><input class='FormData_InputText' type='text' name='am' value='".q($pam)."' />&nbsp;</td></tr>
        <tr><th class='left'>$langLanguage:</th>
            <td>";
        $tool_content .= lang_select_options('language', '', $language);
        $tool_content .= "</td></tr>";
        if (isset($_GET['id'])) {
                $tool_content .="<tr><th class='left'><b>$langComments</b></th>
                                     <td>".q($pcom)."&nbsp;</td></tr>
                                 <tr><th class='left'><b>$langDate</b></th>
                                     <td>".q($pdate)."&nbsp;</td></tr>";
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
        <input type='hidden' name='pstatut' value='$pstatut' />
        <input type='hidden' name='auth' value='1' />
        </form>";
        if ($pstatut == 5) {
                $reqtype ='?type=user';
        } else {
                $reqtype ='';
        }
        $tool_content .= "<p align='right'><a href='../admin/index.php'>$langBack</a></p>";
}

draw($tool_content, 3, null, $head_content);
