<?
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2009  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
* =========================================================================*/

$require_admin = TRUE;
include '../../include/baseTheme.php';
include '../../include/sendMail.inc.php';

$nameTools = 'Μαζική δημιουργία λογαριασμών χρηστών.';
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
                        if (count($user) == 3) {
                                $info[] = create_user($newstatut,
                                                      $user[0],
                                                      $user[1],
                                                      $user[2],
                                                      $facid,
                                                      $_POST['lang'],
                                                      $send_mail);
                        } else {
                                $unparsed_lines .= $line;
                        }
                }
                $line = strtok("\n");
        }
        $tool_content .= "<table><tr><th>Επώνυμο</th><th>Όνομα</th><th>email</th><th>username</th><th>password</th></tr>\n";
        foreach ($info as $n) {
                $tool_content .= "<tr><td>$n[1]</td><td>$n[2]</td><td>$n[3]</td><td>$n[4]</td><td>$n[5]</td></tr>\n";
        }
        $tool_content .= "</table>\n";
} else {
        $req = db_query("SELECT id, name FROM faculte order by id");
        while ($n = mysql_fetch_array($req)) {
                $facs[$n['id']] = $n['name'];
        }
        $tool_content .= "
<p>Εισαγάγετε στο παρακάτω πεδίο έναν κατάλογο με τα στοιχεία των χρηστών, μία
γραμμή ανα χρήστη που επιθυμείτε να δημιουργηθεί, με τα στοιχεία στην εξής
σειρά: Επώνυμο, όνομα, διεύθυνση ηλεκτρονικού ταχυδρομείου.</p>

<form method='post' action='$_SERVER[PHP_SELF]'>
<table class='FormData'>
<tr><th>$langUsersData</th>
    <td><textarea class='auth_input' name='user_info' rows='10' cols='60'>
# $langSurname    $langName    e-mail</textarea></td>
</tr>
<tr><th>Δημιουργία λογαριασμών</th>
    <td><select name='type'>
        <option value='stud'>$langsOfStudents</option>
        <option value='prof'>$langOfTeachers</option></select></td>
</tr>
<tr><th>Τμήμα</th>
    <td>" . selection($facs, 'facid') . "</td>
</tr>
<tr><th>Γλώσσα</th>
    <td>" . lang_select_options('lang') . "</td>
</tr>
<tr><th>$langInfoMail</th>
    <td><input name='send_mail' type='checkbox' />
        Αποστολή των στοιχείων των χρηστών μέσω e-mail</td>
</tr>
<tr><th>&nbsp;</th>
    <td><input type='submit' name='submit' value='Αποστολή' /></td>
</tr>
</table>
</form>";
}

draw($tool_content,3,'admin');


function create_user($statut, $nom, $prenom, $email, $depid, $lang, $send_mail)
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

        $uname = create_username($statut, $depid, $nom, $prenom);
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

function create_username($statut, $depid, $nom, $prenom)
{
        $prefix = (($statut == 1)? 'instr': 'st') .
                  sprintf('%02d', $depid);
        $req = db_query("SELECT username FROM user
                         WHERE username LIKE '$prefix%'
                         ORDER BY username DESC LIMIT 1");
        if ($req and mysql_num_rows($req) > 0) {
                list($last_uname) = mysql_fetch_row($req);
                $lastid = 1 + str_replace($prefix, '', $last_uname);
        } else {
                $lastid = 1;
        }
        $suffix = sprintf("%04d", $lastid);
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

