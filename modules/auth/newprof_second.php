<?php
$langFiles = array('registration', 'admin', 'gunet');
include '../../include/baseTheme.php';
include('../../include/sendMail.inc.php');
@include "check_admin.inc";
$nameTools = $regprof;

// Initialise $tool_content
$tool_content = "";
// Main body

if (!isset($userMailCanBeEmpty))
{	
	$userMailCanBeEmpty = true;
} 

$statut=1;

if($submit)
{
	// Don't worry about figuring this regular expression out quite yet...
	// It will test for address@domainname and address@ip
	$regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,4})$";
	$emailtohostname = substr($email, (strrpos($email, "@") +1));

// check if user name exists

	$username_check=mysql_query("SELECT username FROM `$mysqlMainDb`.user WHERE username='$uname'");
	while ($myusername = mysql_fetch_array($username_check)) 
	{
		$user_exist=$myusername[0];
	}

// check if passwd is too easy

	if ((strtoupper($password) == strtoupper($uname)) || (strtoupper($password) == strtoupper($nom_form))
		|| (strtoupper($password) == strtoupper($prenom_form))
		|| (strtoupper($password) == strtoupper($email))) {
		
		$tool_content .= "<p>$langPassTooEasy : 
				<strong>".substr(md5(date("Bis").$_SERVER['REMOTE_ADDR']),0,8)."</strong></p>
			<br><br><center><p><a href=\"./newprof.php\">$langAgain</a></p></center>";
	}


// check if there are empty fields

	elseif (empty($nom_form) or empty($prenom_form) or empty($password)
		or empty($uname) or (empty($email_form) && !$userMailCanBeEmpty)) {
		$tool_content .= "<p>$langEmptyFields</p>
		<br><br><center><p><a href=\"./newprof.php\">$langAgain</a></p></center>";
	}

	elseif(isset($user_exist) and $uname==$user_exist) {
		$tool_content .= "<p>$langUserFree</p>
		<br><br><center><p><a href=\"./newprof.php\">$langAgain</a></p></center>";
    }

// check if email syntax is valid
   
 elseif(!$userMailCanBeEmpty &&!eregi($regexp,$email)) {
        $tool_content .= "<p>$langEmailWrong.</p>
		<br><br><center><p><a href=\"./newprof.php\">$langAgain</a></p></center>";
	}


/**************** REGISTRATION ACCEPTED **************************/
	else {
		$emailsubject = "$langYourReg $siteName, $langAsProf";

		if (isset($institut) and ($institut > 0)) {
			$emailbody = "
$langDestination $prenom_form $nom_form

$langYouAreReg$siteName, $langAsProf, $langSettings $uname
$langPassSameLDAP
$langAddress $siteName $langIs: $urlServer
$langProblem

$langFormula,

$administratorName $administratorSurname
$langManager $siteName
$langTel $telephone
$langEmail : $emailAdministrator
";
		} else {
			$emailbody = "
$langDestination $prenom_form $nom_form

$langYouAreReg$siteName, $langAsProf, $langSettings $uname
$langPass : $password
$langAddress $siteName $langIs: $urlServer
$langProblem

$langFormula,

$administratorName $administratorSurname
$langManager $siteName
$langTel $telephone
$langEmail : $emailAdministrator
";
			}

send_mail($siteName, $emailAdministrator, '', $email_form, $emailsubject, $emailbody, $charset);

// register user 

		if (!isset($institut)) {
			$institut = "NULL";
		}
		$s = mysql_query("SELECT id FROM faculte WHERE name='$department'");
		$dep = mysql_fetch_array($s);
		$inscr_user=mysql_query("INSERT INTO `$mysqlMainDb`.user
			(user_id, nom, prenom, username, password, email, statut, department, inst_id)
			VALUES ('NULL', '$nom_form', '$prenom_form', '$uname', '$password', '$email_form','$statut','$dep[id]', '$institut')");
		$last_id=mysql_insert_id();
	        $tool_content .= "<p>$profsuccess</p>
						<br><br>
						<center><p><a href='../admin/listreq.php'>$langBackReq</a></p></center>";
	}
}

draw($tool_content,3,'admin');
?>