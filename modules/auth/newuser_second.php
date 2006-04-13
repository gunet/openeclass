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
include('../../include/init.php');
include('../../include/sendMail.inc.php');

$nameTools = $reguser;
$navigation[]= array ("url"=>"newuser.php", "name"=> $langRegistration);

// This file registers only students
$statut=5;

begin_page();

if ($submit) {

// --------------------------------------
// check if there are empty fields
// --------------------------------------

	if (empty($nom_form) or empty($prenom_form) or empty($password1) or empty($password) or empty($uname)) {
			echo "<tr bgcolor=\"$color2\" height=\"400\">
			<td bgcolor=\"$color2\" colspan=\"3\" valign=\"top\">
			<br>
			<font size=\"2\" face=\"arial, helvetica\">$langEmptyFields</font>
			</td></tr></table>";
			exit();
	}

// -----------------------------------
// check if the username exist
// ----------------------------------

	$username_check=mysql_query("SELECT username
		FROM `$mysqlMainDb`.user WHERE username='$uname'");
	if ($myusername = mysql_fetch_array($username_check)) {

		echo "<tr bgcolor=\"$color2\" height=\"400\">
   			<td colspan=\"3\" valign=\"top\">
		    <font size=\"2\" face=\"arial, helvetica\">
				<br>$langUserFree</font>
				</td></tr></table>";
		exit();

		}
										
	// added by adia for LDAP authentication
	if (isset($institut) and ($institut > 0)) {
			$password = $password1 = "LDAP user";
			$uname = $email;
	} else {
			$institut = 0;
	}


//-----------------------------
// check if the passwd is not the same
// -------------------------------

	if($password1 !== $password) {
		echo "<tr bgcolor=\"$color2\" height=\"400\">
		<td colspan=\"3\" valign=\"top\">
		<font size=\"2\" face=\"arial, helvetica\">
		<br>
		$langPassTwice
		</font>
		</td></tr>";
		exit();
	}
//----------------------------------
// check if the passwd is too easy
// ----------------------------------
	elseif (
	(strtoupper($password1) == strtoupper($uname)) || (strtoupper($password1) == strtoupper($nom_form))
	|| (strtoupper($password1) == strtoupper($prenom_form)) || (strtoupper($password1) == strtoupper($email))) 
{
	echo "<tr bgcolor=\"$color2\" height=\"400\">
		<td colspan=\"3\" valign=\"top\" align=\"center\">
		<font face=\"arial, helvetica\" size=\"2\">
		<br>
		$langPassTooEasy : 
		<strong>".substr(md5(date("Bis").$_SERVER['REMOTE_ADDR']),0,8)."</strong>
		<br>
		<br>
		<a href=\"./newuser.php\">$langAgain</a>
		</font>
		</td></tr></table>";
		exit();
	}

// ------------------------------------
// check if the user email is valid
// -----------------------------------
 
if (!empty($email)) {
			// Don't worry about figuring this regular expression out quite yet...
			// It will test for address@domainname and address@ip
			$regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,4})$";
			$emailtohostname = substr($email, (strrpos($email, "@") +1));
 		
		if (!eregi($regexp, $email)) {
        echo "<tr bgcolor=\"$color2\" height=\"400\">
        <td bgcolor=\"$color2\" colspan=\"3\" valign=\"top\" align=\"center\">
				<font face=\"arial, helvetica\" size=\"2\">
				<br>$langEmailWrong.<br><br>
				<a href=\"newuser.php\">".$langAgain."</a>
				</font>
				</td>
				</tr></table>";
        exit();
		}
}

// ----------------------------------
// registration accepted
// ---------------------------------

		$emailsubject = "$langYourReg $siteName";

		if (@($institut > 0)) {
			$emailbody = "
$langDestination $prenom_form $nom_form

$langYouAreReg$siteName $langSettings $uname
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

$langYouAreReg$siteName $langSettings $uname
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

		send_mail($siteName, $emailAdministrator, '', $email,
			$emailsubject, $emailbody, $charset);
 
// ---------------------------------------

		//added by adia to department kai to institut 
		$inscr_user=mysql_query("INSERT INTO `$mysqlMainDb`.user
			(user_id, nom, prenom, username, password, email, statut, department, inst_id, am)
			VALUES ('NULL', '$nom_form', '$prenom_form', '$uname', '$password', '$email','$statut',
				'$department','$institut','$am')");
		$last_id=mysql_insert_id();
		$result=mysql_query("SELECT user_id, nom, prenom FROM `$mysqlMainDb`.user WHERE user_id='$last_id'");
		while ($myrow = mysql_fetch_array($result)) {
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


// ---------------------------
// registration form
// ---------------------------

	echo "<tr bgcolor=\"$color2\">
		<td colspan=\"3\">
		<p><font size=\"3\" face=\"arial, helvetica\">$langDear $prenom $nom,</font></p>
		<p>$langPersonalSettings</p>

<form action=\"inscription_third.php\" method=\"post\">";
		$result=mysql_query("SELECT 
			cours_faculte.faculte f, 
			cours.code k, 
			cours.fake_code c,
			cours.intitule i,
			cours.titulaires t
			FROM `$mysqlMainDb`.cours_faculte, `$mysqlMainDb`.cours, `$mysqlMainDb`.faculte 
			WHERE cours.code=cours_faculte.code 
			AND cours_faculte.faculte=faculte.name
			AND (cours.visible='1' OR cours.visible='2')
			ORDER BY faculte.number, cours.code");	

			$facOnce = '';
			$codeOnce = '';
			while ($mycours = mysql_fetch_array($result)) {	
				if($mycours['f'] != $facOnce) {
					echo "
			<hr noshade size=\"1\">
			<h3>$langDepartment: <em><font color=\"#f0741e\">$mycours[f]</font></em></h3>
			<br>";
				}
				$facOnce = $mycours['f'];

				if($mycours['k'] != $codeOnce) {
					echo "<input type='checkbox' name='course[]' value='$mycours[k]'>
						<font color=\"navy\">$mycours[c]</font> 
						<b>$mycours[i]</b> ($mycours[t])<br>\n";
				}
				$codeOnce=$mycours['k'];
			}
			echo "
			<br>
			<input type=\"submit\" name=\"submit\" value=\"$langRegistration\" >
</form>
			<hr noshade size=1></td></tr>";
}	// end of registration accepted

$already_second=1;
?>
</table>
</body>
</html>
