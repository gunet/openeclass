<?
 /**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Á full copyright notice can be read in "/info/copyright.txt".
        
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
	@last update: 07-06-2006 by Stratos Karatzidis
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================        
        @Description: Second step in new user registration

 	Purpose: The file checks for user provided information and after that makes 
 	the registration in the platform.

==============================================================================
*/

$langFiles = array('registration', 'opencours');
include '../../include/baseTheme.php';
include('../../include/sendMail.inc.php');
include 'auth.inc.php';
$nameTools = $reguser;

$tool_content = "";		// Initialise $tool_content

// Main body
$navigation[] = array("url"=>"newuser.php", "name"=> $langRegistration);

$statut=5;		// This file registers only students

// Get the incoming variables and initialize them
$submit = isset($_POST['submit'])?$_POST['submit']:'';
$auth = isset($_POST['auth'])?$_POST['auth']:'';
$uname = isset($_POST['uname'])?$_POST['uname']:'';
$password = isset($_POST['password'])?$_POST['password']:'';

if(!empty($submit))
{
	$tool_content .= "<table border=\"0\" width=\"99%\">";
/*	
	// do not allow the user to have the characters: ',\" or \\ in password
	$pw = array(); 	$nr = 0;
	while (isset($password{$nr})) // convert the string $password into an array $pw
	{
  	$pw[$nr] = $password{$nr};
    $nr++;
	}
	*/
  //if( (in_array("'",$pw)) || (in_array("\"",$pw)) || (in_array("\\",$pw)) )
  if( (strstr($password, "'")) or (strstr($password, '"')) or (strstr($password, '\\')) 
  or (strstr($uname, "'")) or (strstr($uname, '"')) or (strstr($uname, '\\')) )
	{
		$tool_content .= "<tr bgcolor=\"".$color2."\">
		<td bgcolor=\"$color2\" colspan=\"3\" valign=\"top\">
		<br>$langCharactersNotAllowed<br /><br />
		<a href=\"./newuser.php\">".$langAgain."</a></td></tr></table>";
	}
	else	// do the other checks
	{
		if (empty($nom_form) or empty($prenom_form) or empty($password) or empty($uname)) 	// check if there are empty fields
		{
			$tool_content .= "<tr bgcolor=\"".$color2."\" height=\"400\">
				<td bgcolor=\"$color2\" colspan=\"3\" valign=\"top\">
				<br>".$langEmptyFields."</td></tr></table>";
		}
		else
		{
			$q2 = "SELECT username FROM `$mysqlMainDb`.user WHERE username='".escapeSimple($uname)."'";
			$username_check=mysql_query($q2);	// check if the username exist
			if ($myusername = mysql_fetch_array($username_check)) 
			{
				$tool_content .= "<tr bgcolor=\"".$color2."\" height=\"400\">
	   			<td colspan=\"3\" valign=\"top\"><br />".$langUserFree."</td>
	   			</tr></table>";
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
					$tool_content .= "<tr bgcolor=\"".$color2."\" height=\"400\">
					<td colspan=\"3\" valign=\"top\" align=\"center\">
					<br />".$langPassTooEasy." : 
					<strong>".substr(md5(date("Bis").$_SERVER['REMOTE_ADDR']),0,8)."</strong>
					<br />
					<br />
					<a href=\"./newuser.php\">".$langAgain."</a>
					</td></tr></table>";
					//exit();
				}
				if (!empty($email)) 	// check if the user email is valid
				{
					// Don't worry about figuring this regular expression out quite yet...It will test for address@domainname and address@ip
					$regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,4})$";
					$emailtohostname = substr($email, (strrpos($email, "@") +1));
 					if (!eregi($regexp, $email)) 
					{	
        		$tool_content .= "<tr bgcolor=\"$color2\" height=\"400\">
        		<td bgcolor=\"$color2\" colspan=\"3\" valign=\"top\" align=\"center\">
						<br />".$langEmailWrong."<br /><br />
						<a href=\"newuser.php\">".$langAgain."</a>
						</td></tr></table>";
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
 				//$tool_content .= "POSTED values:<br>uname:".$uname."<br>password:".$password."<br>";
 				$uname = escapeSimple($uname);	// escape the characters: simple and double quote
 				//$tool_content .= "<br>Username after escape filter:<br>".$uname."<br>";
 				if(!in_array($auth,$authmethods))
 				{
 					$crypt = new Encryption;
 					$key = $encryptkey;
	 				$pswdlen = "20";
	 				//$password = escapeSimple($password);
	 				//$tool_content .= "<br>Password without escape filter<br>password: ".$password."<br>";
	 				$password_encrypted = $crypt->encrypt($key, $password, $pswdlen);
	 				//$tool_content .= "<br>Password after encryption<br>password: ".$password_encrypted."<br>";
	 			}
 				else
	 			{
	 				$password_encrypted = $password;
	 			}
 			
	 			$password_decrypted = $crypt->decrypt($key, $password_encrypted);
	 			$q1 = "INSERT INTO `$mysqlMainDb`.user
				(user_id, nom, prenom, username, password, email, statut, department, inst_id, am, registered_at, expires_at)
				VALUES ('NULL', '$nom_form', '$prenom_form', '$uname', '$password_encrypted', '$email','$statut',
					'$department','$institut','$am',".$registered_at.",".$expires_at.")";
			
				//$tool_content .= "<br>QUERY:<br>".$q1."<br>";
				
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
				$tool_content .= "<tr bgcolor=\"$color2\">
				<td colspan=\"3\">
				<p>$langDear $prenom $nom,</font></p>
				<p>$langPersonalSettings</p>
				<form action=\"inscription_third.php\" method=\"post\">";
				$result=mysql_query("SELECT cours_faculte.faculte f, cours.code k, cours.fake_code c,
				cours.intitule i,
				cours.titulaires t,
				cours.password p
				FROM `$mysqlMainDb`.cours_faculte, `$mysqlMainDb`.cours, `$mysqlMainDb`.faculte 
				WHERE cours.code=cours_faculte.code 
				AND cours_faculte.faculte=faculte.name
				AND (cours.visible='1' OR cours.visible='2')
				ORDER BY faculte.number, cours.code");	

				$facOnce = '';
				$codeOnce = '';
				while ($mycours = mysql_fetch_array($result)) 
				{	
					if($mycours['f'] != $facOnce) 
					{
						$tool_content .= "<hr noshade size=\"1\">
						<h3>$langDepartment: <em><font color=\"#f0741e\">$mycours[f]</font></em></h3>
						<br />";
					}
					$facOnce = $mycours['f'];
	
					if($mycours['k'] != $codeOnce) 
					{
						$tool_content .= "<input type='checkbox' name='course[]' value='$mycours[k]'>
							<font color=\"navy\">$mycours[c]</font> 
							<b>$mycours[i]</b> ($mycours[t])";
						if ($mycours['p']!='') {
							$tool_content .= " (".$m['code'].": <input type=\"password\" name=\"".$mycours['k']."\" value=\"\">)";
						}
						$tool_content .= "<br>\n";
					}
					$codeOnce=$mycours['k'];
				}
				$tool_content .= "<br />
				<input type=\"submit\" name=\"submit\" value=\"$langRegistration\" >
				</form>
					<hr noshade size=1></td></tr>";
				
			}	// end of registration accepted

			$already_second=1;
			$tool_content .= "</table>";
		}
	}
} // if submit
draw($tool_content,0);

?>
