<?
/**=============================================================================
GUnet e-Class 2.0
E-learning and Course Management Program
================================================================================
Copyright(c) 2003-2006  Greek Universities Network - GUnet
A full copyright notice can be read in "/info/copyright.txt".

Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
Yannis Exidaridis <jexi@noc.uoa.gr>
Alexandros Diamantidis <adia@noc.uoa.gr>

For a full list of contributors, see "credits.txt".

This program is a free software under the terms of the GNU
(General Public License) as published by the Free Software
Foundation. See the GNU License for more details.
The full license can be read in "license.txt".

Contact address: GUnet Asynchronous Teleteaching Group,
Network Operations Center, University of Athens,
Panepistimiopolis Ilissia, 15784, Athens, Greece
eMail: eclassadmin@gunet.gr
==============================================================================*/

/**===========================================================================
newuser_second.php
* @version $Id$
@authors list: Karatzidis Stratos <kstratos@uom.gr>
Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================
@Description: Second step in new user registration

Purpose: The file checks for user provided information and after that makes
the registration in the platform.

==============================================================================
*/

include '../../include/baseTheme.php';
include('../../include/sendMail.inc.php');
include 'auth.inc.php';
$nameTools = $langRegistration;

$tool_content = "";		// Initialise $tool_content

// Main body
$navigation[] = array("url"=>"newuser.php", "name"=> $langAuthReg);

$statut=5;		// This file registers only students

// Get the incoming variables and initialize them
$submit = isset($_POST['submit'])?$_POST['submit']:'';
$auth = isset($_POST['auth'])?$_POST['auth']:'';
$uname = preg_replace('/\s+/', ' ', trim(isset($_POST['uname'])?$_POST['uname']:''));
$password = isset($_POST['password'])?$_POST['password']:'';

if(!empty($submit))
{
	$tool_content .= "<table border=\"0\" width=\"99%\">";

	if( (strstr($password, "'")) or (strstr($password, '"')) or (strstr($password, '\\'))
	or (strstr($uname, "'")) or (strstr($uname, '"')) or (strstr($uname, '\\')) )
	{
		$tool_content .= "
		<table width=\"99%\">
			<tbody>
				<tr>
					<td class=\"caution\" height='60'>
					<p>$langCharactersNotAllowed </p>
					<p><a href=\"./newuser.php\">".$langAgain."</a></p>
					</td>
				</tr>
			</tbody>
		</table>";
	}
	else	// do the other checks
	{
		if (empty($nom_form) or empty($prenom_form) or empty($password) or empty($uname)) 	// check if there are empty fields
		{
			$tool_content .= "
			<table width=\"99%\">
			<tbody>
				<tr>
					<td class=\"caution\" height='60'>
					<p>$langEmptyFields</p>
					<p><a href=\"./newuser.php\">".$langAgain."</a></p>
					</td>
				</tr>
			</tbody>
		</table>";
		}
		else
		{
			$q2 = "SELECT username FROM `$mysqlMainDb`.user WHERE username='".escapeSimple($uname)."'";
			$username_check=mysql_query($q2);	// check if the username exist
			if ($myusername = mysql_fetch_array($username_check))
			{
				$tool_content .= "
				<table width=\"99%\">
			<tbody>
				<tr>
					<td class=\"caution\" height='60'>
					<p>$langUserFree</p>
					<p><a href=\"./newuser.php\">".$langAgain."</a></p>
					</td>
				</tr>
			</tbody>
		</table>";
		
			}
			else
			{
				$auth_method_settings = get_auth_settings($auth);
				if((!empty($auth_method_settings)) && ($auth!=1))
				{
					$password = $auth_method_settings['auth_name'];
				}
				// check if the passwd is too easy
				elseif((strtoupper($password) == strtoupper($uname)) || (strtoupper($password) == strtoupper($nom_form))
				|| (strtoupper($password) == strtoupper($prenom_form)) || (strtoupper($password) == strtoupper($email)))
				{
					$tool_content .= "
					<table width=\"99%\">
			<tbody>
				<tr>
					<td class=\"caution\" height='60'>
					<p>$langPassTooEasy <strong>".substr(md5(date("Bis").$_SERVER['REMOTE_ADDR']),0,8)."</strong></p>
					<p><a href=\"./newuser.php\">".$langAgain."</a></p>
					</td>
				</tr>
			</tbody>
		</table>";

					//exit();
				}
				if (!empty($email)) 	// check if the user email is valid
				{
					// Don't worry about figuring this regular expression out quite yet...It will test for address@domainname and address@ip
					$regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,4})$";
					$emailtohostname = substr($email, (strrpos($email, "@") +1));
					if (!eregi($regexp, $email))
					{
						$tool_content .= "
        		<table width=\"99%\">
			<tbody>
				<tr>
					<td class=\"caution\" height='60'>
					<p>$langEmailWrong</p>
					<p><a href=\"./newuser.php\">".$langAgain."</a></p>
					</td>
				</tr>
			</tbody>
		</table>";
					}
				}

				// registration accepted
				$emailsubject = "$langYourReg $siteName";
				if((!empty($auth_method_settings)) && ($auth!=1))
				{
					$emailbody = "$langDestination $prenom_form $nom_form \n$langYouAreReg $siteName $langSettings $uname \n$langPassSameLDAP $langAddress $siteName $langIs: $urlServer $langProblem $langFormula $administratorName $administratorSurname $langManager $siteName \n$langTel $telephone \n$langEmail : $emailAdministrator";
				}
				else
				{
					$emailbody = "$langDestination $prenom_form $nom_form \n$langYouAreReg $siteName $langSettings $uname \n$langPass: $password $langAddress $siteName $langIs: $urlServer $langProblem	$langFormula $administratorName $administratorSurname $langManager $siteName \n$langTel $telephone \n$langEmail : $emailAdministrator";
				}

				send_mail($siteName, $emailAdministrator, '', $email,	$emailsubject, $emailbody, $charset);
				$registered_at = time();
				$expires_at = time() + $durationAccount;	//$expires_at = time() + 31536000;
				$institut = 0;

				// manage the store/encrypt process of password into database
				$authmethods = array("2","3","4","5");
				$uname = escapeSimple($uname);	// escape the characters: simple and double quote
				if(!in_array($auth,$authmethods))
				{
					$password_encrypted = md5($password);
				}
				else
				{
					$password_encrypted = $password;
				}

				
				$q1 = "INSERT INTO `$mysqlMainDb`.user
				(user_id, nom, prenom, username, password, email, statut, department, inst_id, am, registered_at, expires_at)
				VALUES ('NULL', '$nom_form', '$prenom_form', '$uname', '$password_encrypted', '$email','$statut',
					'$department','$institut','$am',".$registered_at.",".$expires_at.")";

				$inscr_user=mysql_query($q1);
				$last_id=mysql_insert_id();
				$result=mysql_query("SELECT user_id, nom, prenom FROM `$mysqlMainDb`.user WHERE user_id='$last_id'");
				while ($myrow = mysql_fetch_array($result))
				{
					$uid=$myrow[0];
					$nom=$myrow[1];
					$prenom=$myrow[2];
				}

				mysql_query("INSERT INTO `$mysqlMainDb`.loginout (loginout.idLog, loginout.id_user, loginout.ip, loginout.when, loginout.action) VALUES ('', '".$uid."', '".$REMOTE_ADDR."', NOW(), 'LOGIN')");
				session_register("uid");
				session_register("statut");
				session_register("prenom");
				session_register("nom");
				session_register("uname");

				// registration form
				$tool_content .= "
				<table width=\"99%\"><tbody><tr><td class=\"success\" height='60'>
				<p>$langDear $prenom $nom,</p>
				<p>$langPersonalSettings</p>
				</td></tr></tbody></table>
				<br/>
				<p>$langPersonalSettingsMore</p>
				";
								
			}	// end of registration accepted
		}
	}
} // if submit
draw($tool_content,0);
?>
