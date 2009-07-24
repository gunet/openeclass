<?
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2009  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
* =========================================================================*/

define('SUFFIX_LEN', 4);

$require_admin = TRUE;
include '../../include/baseTheme.php';
include '../../include/sendMail.inc.php';

$nameTools = $langMultiRegUser;
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);
$tool_content = "";

if (isset($_POST['submit'])) {
        $send_mail = isset($_POST['send_mail']) && $_POST['send_mail'];
        $unparsed_lines = '';
        $info = array();
        $newstatut = ($_POST['type'] == 'prof')? 1: 5;
        $facid = intval($_POST['facid']);
        $line = strtok($_POST['user_info'], "\n");
        while ($line !== false) {
                $line = preg_replace('/#.*/', '', trim($line));
                if (!empty($line)) {
                        $user = preg_split('/\s+/', $line);
                        if (count($user) >= 3) {
                                $uname = create_username($newstatut,
                                                         $facid,
                                                         $nom,
                                                         $prenom,
                                                         $_POST['prefix']);
                                $new = create_user($newstatut,
                                                   $uname,
                                                   $user[0],
                                                   $user[1],
                                                   $user[2],
                                                   $facid,
                                                   $_POST['lang'],
                                                   $send_mail);
                                $info[] = $new;
                                for ($i = 3; $i < count($user); $i++) {
                                        register($new[0], $user[$i]);
                                }
                        } else {
                                $unparsed_lines .= $line;
                        }
                }
                $line = strtok("\n");
        }
        $tool_content .= "<table><tr><th>$langSurname</th><th>$langName</th><th>e-mail</th><th>username</th><th>password</th></tr>\n";
        foreach ($info as $n) {
                $tool_content .= "<tr><td>$n[1]</td><td>$n[2]</td><td>$n[3]</td><td>$n[4]</td><td>$n[5]</td></tr>\n";
        }
        $tool_content .= "</table>\n";
} else {
        $req = db_query("SELECT id, name FROM faculte order by id");
        while ($n = mysql_fetch_array($req)) {
                $facs[$n['id']] = $n['name'];
        }
        $tool_content .= "<p>$langMultiRegUserInfo</p>
<form method='post' action='$_SERVER[PHP_SELF]'>
<table class='FormData'>
<tr><th>$langUsersData</th>
    <td><textarea class='auth_input' name='user_info' rows='10' cols='60'>
# $langSurname   $langName   e-mail   [$langLessonCode...]</textarea></td>
</tr>
<tr><th>$langMultiRegType</th>
    <td><select name='type'>
        <option value='stud'>$langsOfStudents</option>
        <option value='prof'>$langOfTeachers</option></select></td>
</tr>
<tr><th>$langMultiRegPrefix</th>
    <td><input type='text' name='prefix' size='10' value='user' /></td>
</tr>
<tr><th>$langFaculteDepartment</th>
    <td>" . selection($facs, 'facid') . "</td>
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


function create_user($statut, $uname, $nom, $prenom, $email, $depid, $lang, $send_mail)
{
        global $charset, $mysqlMainDb, $langAsUser, $langAsProf,
               $langYourReg, $siteName, $langDestination, $langYouAreReg,
               $langSettings, $langPass, $langAddress, $langIs, $urlServer,
               $langProblem, $administratorName, $administratorSurname,
               $langManager, $langTel, $telephone, $langEmail,
               $emailAdministrator, $profsuccess, $usersuccess,
               $durationAccount;

        if ($statut == 1) {
                $message = $profsuccess;
                $type_message = $langAsProf;
        } else {
                $message = $usersuccess;
                $type_message = $langAsUser;
        }

        $password = random_password();
        $registered_at = time();
        $expires_at = time() + $durationAccount;
        $password_encrypted = md5($password);

        $req = db_query("INSERT INTO user
                                (nom, prenom, username, password, email, statut, department, registered_at, expires_at,lang)
                        VALUES (" .
				autoquote($nom) . ', ' .
				autoquote($prenom) . ', ' .
				autoquote($uname) . ", '$password_encrypted', " .
				autoquote($email) .
				", $statut, $depid, " .
                                "$registered_at, $expires_at, '$lang')");
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
$langEmail : $emailAdministrator
";
        if ($send_mail) {
                send_mail($siteName, $emailAdministrator, '', $email, $emailsubject, $emailbody, $charset);
        }

        return array($id, $nom, $prenom, $email, $uname, $password);
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
        $suffix = sprintf('%0'. SUFFIX_LEN . 'd', $lastid);
        return $prefix . $suffix;
}

function random_password()
{
        $parts = array('a', 'ba', 'fa', 'ga', 'ka', 'la', 'ma', 'xa',
                       'e', 'be', 'fe', 'ge', 'ke', 'le', 'me', 'xe',
                       'i', 'bi', 'fi', 'gi', 'ki', 'li', 'mi', 'xi',
                       'o', 'bo', 'fo', 'go', 'ko', 'lo', 'mo', 'xo',
                       'u', 'bu', 'fu', 'gu', 'ku', 'lu', 'mu', 'xu',
                       'ru', 'bur', 'fur', 'gur', 'kur', 'lur', 'mur',
                       'sy', 'zy', 'gy', 'ky', 'tri', 'kro', 'pra');
        $max = count($parts) - 1;
        $num[0] = $num[1] = '';
        $num[rand(0,1)] = rand(10,499);
        return $num[0] . $parts[rand(0,$max)] .
               $parts[rand(0,$max)] . $num[1];
}

function register($uid, $course_code)
{
        $code = autoquote($course_code);
        $req = db_query("SELECT code FROM cours WHERE code=$code OR fake_code=$code");
        if ($req and mysql_num_rows($req) > 0) {
                list($code) = mysql_fetch_row($req);
                db_query("INSERT INTO cours_user SET code_cours='$code', user_id=$uid, statut=5, team=0, tutor=0, reg_date=NOW()");
                return true;
        }
        return false;
}

