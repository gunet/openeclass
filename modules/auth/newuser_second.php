<?

 /*
      +----------------------------------------------------------------------+
      | e-class version 1.0                                                  |
      | based on CLAROLINE version 1.3.0 $Revision$		     |
      +----------------------------------------------------------------------+
      |   $Id$
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      | Copyright (c) 2003 GUNet                                             |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      |                                                                      |
      |   This program is distributed in the hope that it will be useful,    |
      |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
      |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
      |   GNU General Public License for more details.                       |
      |                                                                      |
      |   You should have received a copy of the GNU General Public License  |
      |   along with this program; if not, write to the Free Software        |
      |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
      |   02111-1307, USA. The GNU GPL license is also available through     |
      |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesche <gesche@ipm.ucl.ac.be>                    |
      |                                                                      |
      | e-class changes by: Costas Tsibanis <costas@noc.uoa.gr>              |
      |                     Yannis Exidaridis <jexi@noc.uoa.gr>              |
      |                     Alexandros Diamantidis <adia@noc.uoa.gr>         |
      +----------------------------------------------------------------------+
 */

$langFiles = 'registration';
include '../../include/baseTheme.php';
include('../../include/sendMail.inc.php');
include 'auth.inc.php';
$nameTools = $reguser;
// Initialise $tool_content
$tool_content = "";
// Main body

$navigation[]= array ("url"=>"newuser.php", "name"=> $langRegistration);

// This file registers only students
$statut=5;

if ($submit) 
{
	$uname = isset($_POST['uname'])?$_POST['uname']:'';
	$password = isset($_POST['password'])?$_POST['password']:'';
	// check if there are empty fields
	//$tool_content .= "nom_form:$nom_form<br>prenom_form:$prenom_form<br>password:$password<br>uname:$uname<br>";
	$tool_content .= "<table border=0>";
	//if (empty($nom_form) or empty($prenom_form) or empty($password1) or empty($password) or empty($uname)) 
	if (empty($nom_form) or empty($prenom_form) or empty($password) or empty($uname)) 
	{
			$tool_content .= "<tr bgcolor=\"".$color2."\" height=\"400\">
			<td bgcolor=\"$color2\" colspan=\"3\" valign=\"top\">
			<br>".$langEmptyFields."</td></tr></table>";
			//exit();
	}
	else
	{
		// check if the username exist
		$username_check=mysql_query("SELECT username FROM `$mysqlMainDb`.user WHERE username='$uname'");
		if ($myusername = mysql_fetch_array($username_check)) 
		{
			$tool_content .= "<tr bgcolor=\"".$color2."\" height=\"400\">
	   			<td colspan=\"3\" valign=\"top\">
			    <br />".$langUserFree."</td></tr></table>";
			//exit();
		}
		else
		{
			$auth = isset($_POST['auth'])?$_POST['auth']:'';
			$auth_method_settings = get_auth_settings($auth);
			if((!empty($auth_method_settings)) && ($auth!=1))
			{
				//$password = $password1 = $auth_method_settings['auth_name'];
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
			
			// check if the user email is valid
			if (!empty($email)) 
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
        	//exit();
				}
			}
			
			// registration accepted
			$emailsubject = "$langYourReg $siteName";
			//if (@($institut > 0)) 
			if((!empty($auth_method_settings)) && ($auth!=1))
			{
				$emailbody = "$langDestination $prenom_form $nom_form
				$langYouAreReg$siteName $langSettings $uname
				$langPassSameLDAP
				$langAddress $siteName $langIs: $urlServer
				$langProblem
				$langFormula,$administratorName $administratorSurname
				$langManager $siteName
				$langTel $telephone
				$langEmail : $emailAdministrator";
			} 
			else 
			{
				$emailbody = "$langDestination $prenom_form $nom_form
				$langYouAreReg$siteName $langSettings $uname
				$langPass : $password
				$langAddress $siteName $langIs: $urlServer
				$langProblem
				$langFormula,$administratorName $administratorSurname
				$langManager $siteName
				$langTel $telephone
				$langEmail : $emailAdministrator";
			}
	
			send_mail($siteName, $emailAdministrator, '', $email,	$emailsubject, $emailbody, $charset);
 			//added by adia to department kai to institut 
 			$registered_at = time();
 			$expires_at = time() + 31536000;
			$inscr_user=mysql_query("INSERT INTO `$mysqlMainDb`.user
			(user_id, nom, prenom, username, password, email, statut, department, inst_id, am, registered_at, expires_at)
			VALUES ('NULL', '$nom_form', '$prenom_form', '$uname', '$password', '$email','$statut',
				'$department','$institut','$am',".$registered_at.",".$expires_at.")");
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
			cours.titulaires t
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
						<b>$mycours[i]</b> ($mycours[t])<br>\n";
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
} // if submit
draw($tool_content,0);

?>
