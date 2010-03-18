<?
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
* =========================================================================*/

define('SUFFIX_LEN', 4);

$require_admin = TRUE;
include '../../include/baseTheme.php';
include '../../include/sendMail.inc.php';

$nameTools = $langMultiRegUser;
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);
$tool_content = "";

$error = '';
$acceptable_fields = array('first', 'last', 'email', 'id', 'phone', 'username');

if (isset($_POST['submit'])) {
        $send_mail = isset($_POST['send_mail']) && $_POST['send_mail'];
        $unparsed_lines = '';
        $new_users_info = array();
        $newstatut = ($_POST['type'] == 'prof')? 1: 5;
        $facid = intval($_POST['facid']);
        $am = $_POST['am'];
        $fields = preg_split('/[ \t,]+/', $_POST['fields'], -1, PREG_SPLIT_NO_EMPTY);
        foreach ($fields as $field) {
                if (!in_array($field, $acceptable_fields)) {
                        $tool_content = "<p class='caution_small'>$langMultiRegFieldError <b>$field</b></p>";
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
                                                                            $facid,
                                                                            $nom,
                                                                            $prenom,
                                                                            $_POST['prefix']);
                                }
                                $new = create_user($newstatut,
                                                   $info['username'],
                                                   @$info['last'],
                                                   @$info['first'],
                                                   @$info['email'],
                                                   $facid,
                                                   @$info['id'],
                                                   @$info['phone'],
                                                   $_POST['lang'],
                                                   $send_mail);
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
        $req = db_query("SELECT id, name FROM faculte order by id");
        while ($n = mysql_fetch_array($req)) {
                $facs[$n['id']] = $n['name'];
        }
        $tool_content .= "$langMultiRegUserInfo
<form method='post' action='$_SERVER[PHP_SELF]'>
<table class='FormData'>
<tr><th>$langMultiRegFields</th>
    <td><input type='text' name='fields' size='50' value='first last id email phone' /></td>
<tr><th>$langUsersData</th>
    <td><textarea class='auth_input' name='user_info' rows='10' cols='60'></textarea></td>
</tr>
<tr><th>$langMultiRegType</th>
    <td><select name='type'>
        <option value='stud'>$langsOfStudents</option>
        <option value='prof'>$langOfTeachers</option></select></td>
</tr>
<tr><th>$langMultiRegPrefix</th>
    <td><input type='text' name='prefix' size='10' value='user' /></td>
</tr>
<tr><th>$langFaculty</th>
    <td>" . selection($facs, 'facid') . "</td>
</tr>
<tr><th>$langAm</th>
    <td><input type='text' name='am' size='10' /></td>
</tr>
<tr><th>$langLanguage</th>
    <td>" . lang_select_options('lang') . "</td>
</tr>
<tr><th>$langInfoMail</th>
    <td><input name='send_mail' type='checkbox' />
        $langMultiRegSendMail</td>
</tr>
<tr><th>&nbsp;</th>
    <td><input type='submit' name='submit' value='$langSubmit' /></td>
</tr>
</table>
</form>";
}

draw($tool_content,3,'admin');


function create_user($statut, $uname, $nom, $prenom, $email, $depid, $am, $phone, $lang, $send_mail)
{
        global $charset, $mysqlMainDb, $langAsUser, $langAsProf,
               $langYourReg, $siteName, $langDestination, $langYouAreReg,
               $langSettings, $langPass, $langAddress, $langIs, $urlServer,
               $langProblem, $administratorName, $administratorSurname,
               $langManager, $langTel, $telephone, $langEmail,
               $emailAdministrator, $emailhelpdesk, $profsuccess, $usersuccess,
               $durationAccount;

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
                $GLOBALS['error'] = "$GLOBALS[l_invalidname] ($uname)";
                return false;
        }

        $password = create_pass();
        $registered_at = time();
        $expires_at = time() + $durationAccount;
        $password_encrypted = md5($password);

        $req = db_query("INSERT INTO user
                                (nom, prenom, username, password, email, statut, department, registered_at, expires_at, lang, am, phone)
                        VALUES (" .
				autoquote($nom) . ', ' .
				autoquote($prenom) . ', ' .
				autoquote($uname) . ", '$password_encrypted', " .
				autoquote($email) .
				", $statut, $depid, " .
                                "$registered_at, $expires_at, '$lang', " .
                                autoquote($am) . ', ' .
                                autoquote($phone) . ')');
        $id = mysql_insert_id();

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

function create_username($statut, $depid, $nom, $prenom, $prefix)
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
        $req = db_query("SELECT code, cours_id FROM cours WHERE code=$code OR fake_code=$code");
        if ($req and mysql_num_rows($req) > 0) {
                list($code, $cid) = mysql_fetch_row($req);
                db_query("INSERT INTO cours_user SET cours_id = $cid, user_id = $uid, statut = 5,
                                                     team = 0, tutor = 0, reg_date = NOW()");
                return true;
        }
        return false;
}

