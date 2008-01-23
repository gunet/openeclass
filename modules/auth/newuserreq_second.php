<?
$langFiles = array('registration', 'admin');

$require_admin = TRUE;
include '../../include/baseTheme.php';
include '../../include/sendMail.inc.php';

$nameTools = $reguser;
$navigation[]= array ("url"=>"../admin/", "name"=> $langAdmin);

$tool_content = "";
$tool_content .= "<table width=100% border='0' height=316 cellspacing='0' align=center cellpadding='0'>";
$tool_content .= "<tr>";
$tool_content .= "<td valign=top>";

$tool_content = "<table height=300 width='96%' align='center' class='admin'>
								<tr><td valign=top><br>";

if($submit) {
        // check if user name exists
	$username_check=mysql_query("SELECT username FROM `$mysqlMainDb`.user WHERE username='$uname'");
	while ($myusername = mysql_fetch_array($username_check)) {
		$user_exist=$myusername[0];
	}

// check if passwd is too easy

	if ((strtoupper($password) == strtoupper($uname)) 
			or (strtoupper($password) == strtoupper($nom_form))
			or (strtoupper($password) == strtoupper($prenom_form))
			or (strtoupper($password) == strtoupper($email))) {
			$tool_content .= error_screen($langPassTooEasy);
      $tool_content .= end_tables();
	}

// check if there are empty fields
	elseif (empty($nom_form) or empty($prenom_form) or empty($password)
				or empty($uname) or empty($email_form)) {
			$tool_content .= error_screen($langEmptyFields);	
      $tool_content .= end_tables();
	}

	elseif(isset($user_exist) and $uname==$user_exist) {
			$tool_content .= error_screen($langUserFree);	
  	  $tool_content .= end_tables();
 }

// check if email syntax is valid
   
 elseif(!email_seems_valid($email_form)) {
        $tool_content .= error_screen($langEmailWrong);
        $tool_content .= end_tables();
 }


// registration accepted

	else {
		$emailsubject = "$langYourReg $siteName $langAsUser";

			$emailbody = "
$langDestination $prenom_form $nom_form

$langYouAreReg$siteName $langAsUser, $langSettings $uname
$langPass : $password
$langAddress $siteName $langIs: $urlServer
$langProblem

$administratorName $administratorSurname
$langManager $siteName
$langTel $telephone
$langEmail : $emailAdministrator
";

send_mail($siteName, $emailAdministrator, '', $email_form, $emailsubject, $emailbody, $charset);

// register user 
		$registered_at = time();
    $expires_at = time() + $durationAccount;  

		$password_encrypted = md5($password);
		$s = mysql_query("SELECT id FROM faculte WHERE name='$department'");
		$dep = mysql_fetch_array($s);
		$inscr_user=mysql_query("INSERT INTO `$mysqlMainDb`.user
			(user_id, nom, prenom, username, password, email, statut, department, registered_at, expires_at)
			VALUES ('NULL', '$nom_form', '$prenom_form', '$uname', '$password_encrypted', '$email_form', '5', '$dep[id]', '$registered_at', '$expires_at')");
		
		// close request
        $rid = intval($_POST['rid']);
        db_query("UPDATE prof_request set status = '2',
         date_closed = NOW() WHERE rid = '$rid'");

    $tool_content .= "
		<tr><td valign='top' align='center' class=alert1>$usersuccess
		<br>
		<a href='../admin/listrequsers.php' class=mainpage>$langBackAdmin</a>
		<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>";
    $tool_content .= end_tables();
	}
}

draw($tool_content, 3);


// -----------------
// functions
// -----------------
function error_screen($message) {

global $langTryAgain;

return "<tr height='80'><td colspan='3' valign='top' align='center' class=alert1>$message</td></tr>
      <tr height='30' valign='top' align='center'><td align=center>
      <a href='newuserreq.php' class=mainpage>$langTryAgain</a></span></td></tr>";
}

function end_tables() {
global $langBackAdmin;

$retstring = "</td></tr><tr><td align=right valign=bottom height='180'>";
$retstring .= "<a href=\"../admin/index.php\" class=mainpage>$langBackAdmin&nbsp;</a>";
$retstring .= "</td></tr></table>
						  </td></tr>
							<tr><td>&nbsp;</td></tr>
						  </table>";

return $retstring;
}

?>
