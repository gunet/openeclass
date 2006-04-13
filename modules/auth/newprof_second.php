<?
 /*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                            |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   $Id$ |
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
	  |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
 */

$langFiles = array('registration', 'admin', 'gunet');
include('../../include/init.php');
include('../../include/sendMail.inc.php');

check_admin();

$nameTools = $regprof;
$navigation[]= array ("url"=>"../admin/", "name"=> $admin);

if (!isset($userMailCanBeEmpty))
{	
	$userMailCanBeEmpty = true;
} 

$statut=1;

begin_page();

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
		
		echo "<tr bgcolor=\"$color2\" height=\"200\">
		<td colspan=\"3\" valign=\"top\">
			<font face=\"arial, helvetica\" size=\"2\">
			<br>$langPassTooEasy : 
				<strong>".substr(md5(date("Bis").$_SERVER['REMOTE_ADDR']),0,8)."</strong>
			<br>
			<br><a href=\"./newprof.php\">$langAgain</a></font>
			</td>
		</tr>
		</table>";
		exit();
	}


// check if there are empty fields

	elseif (empty($nom_form) or empty($prenom_form) or empty($password)
		or empty($uname) or (empty($email_form) && !$userMailCanBeEmpty)) {
		echo "<tr bgcolor=\"$color2\" height=\"200\">
			<td bgcolor=\"$color2\" colspan=\"3\" valign=\"top\">
			<br>
			<font size=\"2\" face=\"arial, helvetica\">$langEmptyFields</font>
			</td>
		</tr>";
	}

	elseif(isset($user_exist) and $uname==$user_exist) {
		echo "<tr bgcolor=\"$color2\" height=\"200\">
			<td colspan=\"3\" valign=\"top\">
			<font size=\"2\" face=\"arial, helvetica\"><br>$langUserFree</font>
			</td>
			</tr>";
    }

// check if email syntax is valid
   
 elseif(!$userMailCanBeEmpty &&!eregi($regexp,$email)) {
        echo "<tr><td colspan=\"3\">
		<font face=\"arial, helvetica\" size=\"2\">$langEmailWrong.<br>
		<a href=\"$_SERVER[PHP_SELF]\">".$langAgain."</a>
		</font></td></tr>";
            exit();
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
	        echo "<tr valign='top' bgcolor=$color2>
                                        <td>
                                                <font size='2' face='arial, helvetica'>
  	                             		$profsuccess
						<br><br>
						<a href='../admin/listreq.php'>$langBackReq</a>
						<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>
		                       </font>
                                    </td>
                                </tr>";		
	}
}
?>
</table>
</body>
</html>
