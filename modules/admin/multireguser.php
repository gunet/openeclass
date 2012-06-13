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


define('SUFFIX_LEN', 4);

$require_usermanage_user = true;
require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';

require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';

$tree = new hierarchy();
$userObj = new user();

load_js('jquery');
load_js('jquery-ui-new');
load_js('jstree');

$nameTools = $langMultiRegUser;
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);

$error = '';
$acceptable_fields = array('first', 'last', 'email', 'id', 'phone', 'username', 'password');

if (isset($_POST['submit'])) {
        register_posted_variables(array('perso' => true,
                                        'email_public' => true,
                                        'am_public' => true,
                                        'phone_public' => true),
                                  'all', 'intval');
        $perso = $perso? 'no': 'yes';
        $send_mail = isset($_POST['send_mail']) && $_POST['send_mail'];
        $unparsed_lines = '';
        $new_users_info = array();
        $newstatut = ($_POST['type'] == 'prof')? 1: 5;
        $departments = isset($_POST['facid']) ? $_POST['facid'] : array();;
        $am = $_POST['am'];
        $fields = preg_split('/[ \t,]+/', $_POST['fields'], -1, PREG_SPLIT_NO_EMPTY);
        foreach ($fields as $field) {
                if (!in_array($field, $acceptable_fields)) {
                        $tool_content = "<p class='caution'>$langMultiRegFieldError <b>$field</b></p>";
                        draw($tool_content, 3, 'admin');
                        exit;
                }
        }
        $numfields = count($fields);
        $line = strtok($_POST['user_info'], "\n");
        while ($line !== false) {
                $line = preg_replace('/#.*/', '', trim($line));
                if (!empty($line)) {
                        $user = preg_split('/[ \t]+/', $line);
                        if (count($user) >= $numfields) {
                                $info = array();
                                foreach ($fields as $field) {
                                        $info[$field] = array_shift($user);
                                }

                                if (!isset($info['email']) or
                                    !email_seems_valid($info['email'])) {
                                        $info['email'] = '';
                                }

                                if (!empty($am)) {
                                        if (!isset($info['id']) or empty($info['id'])) {
                                                $info['id'] = $am;
                                        } else {
                                                $info['id'] = $am . ' - ' . $info['id'];
                                        }
                                }

                                if (!isset($info['username'])) {
                                        $info['username'] = create_username($newstatut,
                                                                            $departments,
                                                                            $nom,
                                                                            $prenom,
                                                                            $_POST['prefix']);
                                }
                                if (!isset($info['password'])) {
                                        $info['password'] = create_pass();
                                }
                                $new = create_user($newstatut,
                                                   $info['username'],
                                                   $info['password'],
                                                   @$info['last'],
                                                   @$info['first'],
                                                   @$info['email'],
                                                   $departments,
                                                   @$info['id'],
                                                   @$info['phone'],
                                                   $_POST['lang'],
                                                   $send_mail,
                                                   $email_public, $phone_public, $am_public, $perso);
                                if ($new === false) {
                                        $unparsed_lines .= $line . "\n" . $error . "\n";
                                } else {
                                        $new_users_info[] = $new;

                                        // Now, the $user array should contain only course codes
                                        foreach ($user as $course_code) {
                                                if (!register($new[0], $course_code)) {
                                                        $unparsed_lines .=
                                                                sprintf($langMultiRegCourseInvalid . "\n",
                                                                        "$info[last] $info[first] ($info[username])",
                                                                        $course_code);
                                                }
                                        }
                                }
                        } else {
                                $unparsed_lines .= $line;
                        }
                }
                $line = strtok("\n");
        }
        if (!empty($unparsed_lines)) {
                $tool_content .= "<p><b>$langErrors</b></p><pre>$unparsed_lines</pre>";
        }
        $tool_content .= "<table><tr><th>$langSurname</th><th>$langName</th><th>e-mail</th><th>$langPhone</th><th>$langAm</th><th>username</th><th>password</th></tr>\n";
        foreach ($new_users_info as $n) {
                $tool_content .= "<tr><td>$n[1]</td><td>$n[2]</td><td>$n[3]</td><td>$n[4]</td><td>$n[5]</td><td>$n[6]</td><td>$n[7]</td></tr>\n";
        }
        $tool_content .= "</table>\n";
} else {
        $req = db_query("SELECT id, name FROM hierarchy WHERE allow_course = true ORDER BY name");
        while ($n = mysql_fetch_array($req)) {
                $facs[$n['id']] = $n['name'];
        }
        $access_options = array(ACCESS_PRIVATE => $langProfileInfoPrivate,
                                ACCESS_PROFS => $langProfileInfoProfs,
                                ACCESS_USERS => $langProfileInfoUsers);
        $profile_options = array(0 => $langModern,
                                 1 => $langClassic);
        $tool_content .= "<div class='noteit'>$langMultiRegUserInfo</div>
            <form method='post' action='$_SERVER[PHP_SELF]' onsubmit='return validateNodePickerForm();' >
            <fieldset>
  <legend>$langMultiRegUserData</legend>
<table class='tbl' width='100%'>
<tr><th>$langMultiRegFields:</th>
    <td><input type='text' name='fields' size='50' value='first last id email phone' /></td>
<tr><th>$langUsersData:</th>
    <td><textarea class='auth_input' name='user_info' rows='10' cols='60'></textarea></td>
</tr>
<tr><th>$langMultiRegType:</th>
    <td><select name='type'>
        <option value='stud'>$langsOfStudents</option>
        <option value='prof'>$langOfTeachers</option></select></td>
</tr>
<tr><th>$langMultiRegPrefix:</th>
    <td><input type='text' name='prefix' size='10' value='user' /></td>
</tr>
<tr><th>$langFaculty:</th>
    <td>"; 
        list($js, $html) = $tree->buildUserNodePicker(array('params' => 'name="facid[]"'));
        $head_content .= $js;
        $tool_content .= $html;
        $tool_content .= "</td>
</tr>
<tr><th>$langAm:</th>
    <td><input type='text' name='am' size='10' /></td>
</tr>
<tr><th>$langLanguage:</th>
    <td>" . lang_select_options('lang') . "</td>
</tr>
<tr><th>$langEmail</th>
    <td>" . selection($access_options, 'email_public', ACCESS_PRIVATE) . "</td></tr>
<tr><th>$langAm</th>
    <td>" . selection($access_options, 'am_public', ACCESS_PRIVATE) . "</td></tr>
<tr><th>$langPhone</th>
    <td>" . selection($access_options, 'phone_public', ACCESS_PRIVATE) . "</td></tr>
<tr><th>$langUserBriefcase</th>
    <td>" . selection($profile_options, 'perso', 0) . "</td></tr>
<tr><th>$langInfoMail:</th>
    <td><input name='send_mail' type='checkbox' />
        $langMultiRegSendMail</td>
</tr>
<tr><th>&nbsp;</th>
    <td class='right'><input type='submit' name='submit' value='$langSubmit' /></td>
</tr>
</table>
</fieldset>
</form>";
}

draw($tool_content,3,'admin', $head_content);


function create_user($statut, $uname, $password, $nom, $prenom, $email, $departments, $am, $phone, $lang, $send_mail,
                     $email_public, $phone_public, $am_public, $perso)
{
        global $charset, $mysqlMainDb, $langAsUser, $langAsProf,
               $langYourReg, $siteName, $langDestination, $langYouAreReg,
               $langSettings, $langPass, $langAddress, $langIs, $urlServer,
               $langProblem, $administratorName, $administratorSurname,
               $langManager, $langTel, $telephone, $langEmail,
               $emailAdministrator, $emailhelpdesk, $profsuccess, $usersuccess,
               $durationAccount, $userObj;

        if ($statut == 1) {
                $message = $profsuccess;
                $type_message = $langAsProf;
        } else {
                $message = $usersuccess;
                $type_message = '';
                // $langAsUser;
        }

        $req = db_query('SELECT * FROM user WHERE username = ' . autoquote($uname));
        if ($req and mysql_num_rows($req) > 0) {
                $GLOBALS['error'] = "$GLOBALS[langMultiRegUsernameError] ($uname)";
                return false;
        }

        $registered_at = time();
        $expires_at = time() + $durationAccount;
        $password_encrypted = md5($password);

        $req = db_query("INSERT INTO user
                               (nom, prenom, username, password, email, statut, registered_at, expires_at, lang, am, phone,
                                email_public, phone_public, am_public, perso)
                        VALUES (" .
				autoquote($nom) . ', ' .
				autoquote($prenom) . ', ' .
				autoquote($uname) . ", '$password_encrypted', " .
				autoquote(mb_strtolower(trim($email))) .
				", $statut, " .
                                "$registered_at, $expires_at, '$lang', " .
                                autoquote($am) . ', ' .
                                autoquote($phone) . ", $email_public, $phone_public, $am_public, '$perso')");
        $id = mysql_insert_id();
        $userObj->refresh($id, $departments);

        $emailsubject = "$langYourReg $siteName $type_message";
        $emailbody = "
$langDestination $prenom $nom

$langYouAreReg $siteName $type_message, $langSettings $uname
$langPass : $password
$langAddress $siteName $langIs: $urlServer
$langProblem

$administratorName $administratorSurname
$langManager $siteName
$langTel $telephone
$langEmail : $emailhelpdesk
";
        if ($send_mail) {
                send_mail('', '', '', $email, $emailsubject, $emailbody, $charset);
        }

        return array($id, $nom, $prenom, $email, $phone, $am, $uname, $password);
}

function create_username($statut, $departments, $nom, $prenom, $prefix)
{
        $wildcard = str_pad('', SUFFIX_LEN, '_');
        $req = db_query("SELECT username FROM user
                         WHERE username LIKE '$prefix$wildcard'
                         ORDER BY username DESC LIMIT 1");
        if ($req and mysql_num_rows($req) > 0) {
                list($last_uname) = mysql_fetch_row($req);
                $lastid = 1 + str_replace($prefix, '', $last_uname);
        } else {
                $lastid = 1;
        }
        do {
                $uname = $prefix . sprintf('%0'. SUFFIX_LEN . 'd', $lastid);
                $lastid++;
        } while (user_exists($uname));
        return $uname;
}


function register($uid, $course_code)
{
        $code = autoquote($course_code);
        $req = db_query("SELECT code, id FROM course WHERE code=$code OR public_code=$code");
        if ($req and mysql_num_rows($req) > 0) {
                list($code, $cid) = mysql_fetch_row($req);
                db_query("INSERT INTO course_user SET course_id = $cid, user_id = $uid, statut = 5,
                                                     team = 0, tutor = 0, reg_date = NOW()");
                return true;
        }
        return false;
}
