<?php
/*
* @version $Id$
*/
$langFiles = array('registration', 'admin', 'gunet');
include '../../include/baseTheme.php';
include('../../include/sendMail.inc.php');
require_once 'auth.inc.php';
$nameTools = $regprof;

// Initialise $tool_content
$tool_content = "";
// Main body

$statut=1;

$submit = isset($_POST['submit'])?$_POST['submit']:'';
$uname = isset($_POST['uname'])?$_POST['uname']:'';
$password = isset($_POST['password'])?$_POST['password']:'';
$email_form = isset($_POST['email_form'])?$_POST['email_form']:'';
$nom_form = isset($_POST['nom_form'])?$_POST['nom_form']:'';
$prenom_form = isset($_POST['prenom_form'])?$_POST['prenom_form']:'';
$usercomment = isset($_POST['usercomment'])?$_POST['usercomment']:'';
$department = isset($_POST['department'])?$_POST['department']:'';
$userphone = isset($_POST['userphone'])?$_POST['userphone']:'';

if(!empty($submit)) 
{
	// do not allow the user to have the characters: ',\" or \\ in password
	//$pw = array(); 	$nr = 0;
	//while (isset($password{$nr})) // convert the string $password into an array $pw
	//{
  //	$pw[$nr] = $password{$nr};
  //  $nr++;
	//}
  //if( (in_array("'",$pw)) || (in_array("\"",$pw)) || (in_array("\\",$pw)) )
  if( (strstr($password, "'")) or (strstr($password, '"')) or (strstr($password, '\\')) 
  or (strstr($uname, "'")) or (strstr($uname, '"')) or (strstr($uname, '\\')) )
	{
	
		$tool_content .= "
		<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"caution\">
							<p>$langCharactersNotAllowed</p>
							
							<p><a href=\"./newprof.php\">$langAgain</a></p>
						</td>
					</tr>
				</tbody>
			</table>";
	}
	else	// do the other checks
	{
		if (isset($email_form)) 
		{
			// Don't worry about figuring this regular expression out quite yet...// It will test for address@domainname and address@ip
			$regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,4})$";
		}
		// check if user name exists
		$q1 = "SELECT username FROM `$mysqlMainDb`.user WHERE username='".escapeSimple($uname)."'";
		$username_check=mysql_query($q1);
		while ($myusername = mysql_fetch_array($username_check)) 
		{
			$user_exist=$myusername[0];
		}
	
		// check if passwd is too easy
		if ((strtoupper($password) == strtoupper($uname)) || (strtoupper($password) == strtoupper($nom_form))
			|| (strtoupper($password) == strtoupper($prenom_form))) 
		{
				$tool_content .= "
				<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"caution\">
							<p>$langPassTooEasy: <strong>".substr(md5(date("Bis").$_SERVER['REMOTE_ADDR']),0,8)."</strong></p>
							<p><a href=\"./newprof.php\">$langAgain</a></p>
						</td>
					</tr>
				</tbody>
			</table>";
		}
		// check if there are empty fields
		elseif (empty($nom_form) or empty($prenom_form) or empty($password) or empty($usercomment) or empty($department) or empty($uname) or (empty($email_form))) 
		{
			$tool_content .= "
			<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"caution\">
							<p>$langEmptyFields</p>
							
							<p><a href=\"./newprof.php\">$langAgain</a></p>
						</td>
					</tr>
				</tbody>
			</table>";
		}
		elseif(isset($user_exist) and $uname==$user_exist) 
		{
			$tool_content .= "
			<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"caution\">
							<p>$langUserFree</p>
							
							<p><a href=\"./newprof.php\">$langAgain</a></p>
						</td>
					</tr>
				</tbody>
			</table>";
	  }
		elseif(!eregi($regexp,$email_form)) // check if email syntax is valid
		{
	        $tool_content .= "
	        <table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"caution\">
							<p>$langEmailWrong</p>
							
							<p><a href=\"./newprof.php\">$langAgain</a></p>
						</td>
					</tr>
				</tbody>
			</table>";
		}
		else 		// registration is ok
		{
			$uname = escapeSimple($uname);	// escape the characters: simple and double quote
			// ------------------- Update table prof_request ------------------------------
			$username = $uname;
			$auth = $_POST['auth'];
			if($auth!=1)
			{
				switch($auth)
				{
					case '2': $password = "pop3";
						break;
					case '3': $password = "imap";
						break;
					case '4':	$password = "ldap";
						break;
					case '5': $password = "db";
						break;
					default:	$password = "";
						break;
				}
			}
			
			$usermail = $email_form;
			$surname = $nom_form;
			$name = $prenom_form;
			
			mysql_select_db($mysqlMainDb,$db);
			$sql = "INSERT INTO prof_request(profname,profsurname,profuname,profpassword,
			profemail,proftmima,profcomm,status,date_open,comment) VALUES(
			'$name','$surname','$username','$password','$usermail','$department','$userphone','1',NOW(),'$usercomment')";
			$upd=mysql_query($sql,$db);
			//----------------------------- Email Message --------------------------
		    $MailMessage = $mailbody1 . $mailbody2 . "$name $surname\n\n" . $mailbody3 
		    . $mailbody4 . $mailbody5 . "$mailbody6\n\n" . "$langDepartment: $department\n$profcomment: $usercomment\n" 
		    . "$profuname : $username\n$profemail : $usermail\n" . "$contactphone : $userphone\n\n\n$logo\n\n";
		if (!send_mail($gunet, $emailhelpdesk, '', $emailhelpdesk, $mailsubject, $MailMessage, $charset)) 
			{
				$tool_content .= "
				<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"caution\">
							<p>$langMailErrorMessage</p>
							
							<p><a href=\"mailto:$emailhelpdesk\">$emailhelpdesk</a></p>
						</td>
					</tr>
				</tbody>
			</table>";
				draw($tool_content,0);
				exit();
			}
	
			//------------------------------------User Message ----------------------------------------
			$tool_content .= "
			<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"success\">
							<p>$dearprof</p><p>$success</p><p>$infoprof</p>
							
							<p>$click <a href=\"$urlServer\">$here</a> $backpage</p>
						</td>
					</tr>
				</tbody>
			</table>";
		} 
	}
}
else 
{
	$tool_content .= "<br />$langRegistrationError<br>";
}

draw($tool_content,0);
?>
