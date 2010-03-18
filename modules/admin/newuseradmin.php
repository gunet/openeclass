<?
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*                       Yannis Exidaridis <jexi@noc.uoa.gr>
*                       Alexandros Diamantidis <adia@noc.uoa.gr>
*                       Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address:     GUnet Asynchronous eLearning Group,
*                       Network Operations Center, University of Athens,
*                       Panepistimiopolis Ilissia, 15784, Athens, Greece
*                       eMail: info@openeclass.org
* =========================================================================*/


$require_admin = true;
include '../../include/baseTheme.php';
include '../../include/sendMail.inc.php';
$navigation[] = array("url" => "../admin/index.php", "name" => $langAdmin);

// Initialise $tool_content
$tool_content = "";

$all_set = register_posted_variables(array(
        'auth' => true,
        'uname' => true,
        'nom_form' => true,
        'prenom_form' => true,
        'email_form' => true,
        'language' => true,
        'department' => true,
        'comment' => false,
        'password' => true,
        'pstatut' => true,
        'rid' => false,
        'submit' => true));

$submit = isset($_POST['submit'])?$_POST['submit']:'';

if($submit) {
	// register user
	$depid = intval(isset($_POST['department'])?$_POST['department']: 0);
	$proflanguage = isset($_POST['language'])?$_POST['language']:'';
	if (!isset($native_language_names[$proflanguage])) {
		$proflanguage = langname_to_code($language);
	}

	// check if user name exists
	$username_check = mysql_query("SELECT username FROM `$mysqlMainDb`.user 
			WHERE username=".autoquote($uname));
	$user_exist = (mysql_num_rows($username_check) > 0);

	// check if there are empty fields
	if (!$all_set) {
		$tool_content .= "<p class='caution_small'>$langEmptyFields</p>
			<br><br><p align='right'><a href='$_SERVER[PHP_SELF]'>$langAgain</a></p>";
	} elseif ($user_exist) {
		$tool_content .= "<p class='caution_small'>$langUserFree</p>
			<br><br><p align='right'><a href='$_SERVER[PHP_SELF]'>$langAgain</a></p>";
	} elseif(!email_seems_valid($email_form)) {
		$tool_content .= "<p class='caution_small'>$langEmailWrong.</p>
			<br><br><p align='right'><a href='$_SERVER[PHP_SELF]'>$langAgain</a></p>";
	} else {
                $registered_at = time();
		$expires_at = time() + $durationAccount;
		$password_encrypted = md5($password);
		$inscr_user = db_query("INSERT INTO `$mysqlMainDb`.user
				(nom, prenom, username, password, email, statut, department, am, registered_at, expires_at,lang)
				VALUES (" .
				autoquote($nom_form) . ', ' .
				autoquote($prenom_form) . ', ' .
				autoquote($uname) . ", '$password_encrypted', " .
				autoquote($email_form) .
				", $pstatut, $depid, " . autoquote($comment) . ", $registered_at, $expires_at, '$proflanguage')");

		// close request
	  	$rid = intval($_POST['rid']);
  	  	db_query("UPDATE prof_request set status = 2, date_closed = NOW() WHERE rid = '$rid'");

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
	       	$tool_content .= "<p class='success_small'>$message</p><br><br><p align='right'><a href='../admin/listreq.php$reqtype'>$langBackRequests</a></p>";
		
		// send email
		
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
	if (isset($id)) { // if we come from prof request
		$res = mysql_fetch_array(db_query("SELECT profname, profsurname, profuname, profemail, proftmima, comment, lang, statut 
			FROM prof_request WHERE rid='$id'"));
		$ps = $res['profsurname'];
		$pn = $res['profname'];
		$pu = $res['profuname'];
		$pe = $res['profemail'];
		$pt = $res['proftmima'];
		$pcom = $res['comment'];
		$lang = $res['lang'];
                $pstatut = $res['statut'];
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

	$tool_content .= "<form action='$_SERVER[PHP_SELF]' method='post'>
	<table width='99%' align='left' class='FormData'>
	<tbody><tr>
	<th width='220'>&nbsp;</th>
	<td><b>$title</b></td>
	</tr>
	<tr>
	<th class='left'><b>".$langSurname."</b></th>
	<td><input class='FormData_InputText' type='text' name='nom_form' value='".@$ps."' >&nbsp;(*)</td>
	</tr>
	<tr>
	<th class='left'><b>".$langName."</b></th>
	<td><input class='FormData_InputText' type='text' name='prenom_form' value='".@$pn."'>&nbsp;(*)</td>
	</tr>
	<tr>
	<th class='left'><b>$langUsername</b></th>
	<td><input class='FormData_InputText' type='text' name='uname' value='".@$pu."'>&nbsp;(*)</td>
	</tr>
	<tr>
	<th class='left'><b>$langPass</b></th>
	<td><input class='FormData_InputText' type='text' name='password' value='".create_pass()."'></td>
	</tr>
	<tr>
	<th class='left'><b>$langEmail</b></th>
	<td><input class='FormData_InputText' type='text' name='email_form' value='".@$pe."'>&nbsp;(*)</b></td>
	</tr>
	<tr>
	<th class='left'>$langFaculty</th>
	<td>";
	
	$dep = array();
	$deps = db_query("SELECT id, name FROM faculte order by id");
	while ($n = mysql_fetch_array($deps)) {
		$dep[$n['id']] = $n['name'];
	}
	if (isset($pt)) {
		$tool_content .= selection ($dep, 'department', $pt);
	} else {
		$tool_content .= selection ($dep, 'department');
	}
	$tool_content .= "</td>
	</tr>
	<tr>
	<th class='left'><b>$langComments</b></th>
	<td><input class='FormData_InputText' type='text' name='comment' value='".@q($pcom)."'>&nbsp;</b></td>
	</tr>
	<tr>
	<th class='left'>$langLanguage</th>
	<td>";
	$tool_content .= lang_select_options('language', '', $lang);
	$tool_content .= "</td></tr>
	<tr>
	<th>&nbsp;</th>
	<td><input type='submit' name='submit' value='$langSubmit' >
		<small>$langRequiredFields</small></td>
	</tr>
	</tbody>
	</table>
	<input type='hidden' name='rid' value='".@$id."'>
	<input type='hidden' name='pstatut' value='$pstatut'>
        <input type='hidden' name='auth' value='1' >
	</form>";
	$tool_content .= "
	<br />
	<p align='right'><a href='../admin/index.php'>$langBack</p>";
}

draw($tool_content, 3, 'auth');
