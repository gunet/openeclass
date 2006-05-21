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
$require_help = TRUE;
$helpTopic = 'Profile';
//include ('../../include/init.php');
include '../../include/baseTheme.php';
$require_valid_uid = TRUE;
$tool_content = "";

check_uid();

$nameTools = $langModifProfile;

check_guest();

//begin_page();

if (isset($submit)) {
	$regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,4})$";

// check if username exists

	$username_check=mysql_query("SELECT username FROM user WHERE username='$username_form'");
	while ($myusername = mysql_fetch_array($username_check)) {
		$user_exist=$myusername[0];
	}

// check if passwds are the same

	if ($password_form1 !== $password_form) {
		$tool_content .= "<tr bgcolor=\"$color2\" height=\"400\">
		<td valign=\"top\" align=\"center\">
		<font face=\"arial, helvetica\" size=\"2\">
		<br>
		$langPassTwo.
		<br><br>
		<center><a href=\"$_SERVER[PHP_SELF]\">$langAgain</a></center>
		</font>
		</td>
		</tr>
		</table>";
		draw($tool_content, 1);
		exit();
	}

// check if passwd is too easy

	elseif ((strtoupper($password_form1) == strtoupper($username_form))
		|| (strtoupper($password_form1) == strtoupper($nom_form))
		|| (strtoupper($password_form1) == strtoupper($prenom_form))
		|| (strtoupper($password_form1) == strtoupper($email_form))) {
	$tool_content .= "<tr bgcolor=\"$color2\" height=\"400\">
		<td valign=\"top\" align=\"center\">
		<font face=\"arial, helvetica\" size=\"2\">
		<br>
		$langPassTooEasy: <strong>".substr(md5(date("Bis").$_SERVER['REMOTE_ADDR']),0,8)."</strong> 
		<br>
		<br>
		<center><a href=\"$_SERVER[PHP_SELF]\">$langAgain</a></center>
			</font>
		</td></tr></table>";
	draw($tool_content, 1);
		exit();
	}

// check if there are empty fields

	elseif (empty($nom_form) OR empty($prenom_form) OR empty($password_form1)
		OR empty($password_form) OR empty($username_form) OR empty($email_form)) {
	
	$tool_content .=  "<tr bgcolor=\"$color2\" height=\"400\">
		<td bgcolor=\"$color2\" valign=\"top\" align=\"center\">
			<font face=\"arial, helvetica\" size=\"2\">
				$langFields.
				<br><br>
				<center><a href=\"$_SERVER[PHP_SELF]\">$langAgain</a></center>
			</font>
			</td>
		</tr>
		</table>";
	draw($tool_content, 1);
		exit();
	}

// check if username is free

	elseif(isset($user_exist) AND ($username_form==$user_exist) AND ($username_form!=$uname)) {
		$tool_content .= "<tr bgcolor=\"$color2\" height=\"400\"><td valign=\"top\" align=\"center\">
		<font face=\"arial, helvetica\" size=\"2\"><br>
		$langUserTaken.<br><br><center><a href=\"$_SERVER[PHP_SELF]\">$langAgain</a></center>
		</font>
		</td>
	</tr></table>";
		draw($tool_content, 1);
	exit();
	}

// check if user email is valid

	elseif (!eregi($regexp, $email_form)) {
		$tool_content .= "<tr bgcolor=\"$color2\" height=\"400\">
		<td valign=\"top\" align=\"center\">
		<font face=\"arial, helvetica\" size=\"2\">
		$langEmailWrong.<br><br>
		<center><a href=\"$_SERVER[PHP_SELF]\">".$langAgain."</a></center>
		</font>
		</td></tr></table>";
		draw($tool_content, 1);
		exit();
	}

// everything is ok 

	mysql_query("UPDATE user 
		SET nom='$nom_form', prenom='$prenom_form', 
		username='$username_form', password='$password_form', email='$email_form', am='$am_form'
		WHERE user_id='".$_SESSION["uid"]."'");
	$tool_content .= "<font face=\"arial, helvetica\" size=\"2\">
	$langProfileReg
	<br>
	<a href='$urlServer'>$langHome</a>
	<br>
	<hr size=\"1\" noshade>";

}	// if submit

 /**************************************************************************************/
// inst_id added by adia for LDAP users
$sqlGetInfoUser ="SELECT nom, prenom, username, password, email, inst_id, am
	FROM user WHERE user_id='".$uid."'";
$result=mysql_query($sqlGetInfoUser);
$myrow = mysql_fetch_array($result);

$nom_form = $myrow['nom'];
$prenom_form = $myrow['prenom'];
$username_form = $myrow['username'];
$password_form = $myrow['password'];
$email_form = $myrow['email'];
$am_form = $myrow['am'];

session_unregister("uname"); 
session_unregister("pass"); 
session_unregister("nom"); 
session_unregister("prenom"); 

$uname=$username_form;
$pass=$password_form;
$nom=$nom_form;
$prenom=$prenom_form;

session_register("uname"); 
session_register("pass"); 
session_register("nom"); 
session_register("prenom"); 

// if LDAP user - added by adia
if ($myrow['inst_id'] > 0) {		// LDAP user:
	$tool_content .= "<table width=\"99%\"><tr>
		<td valign=\"top\">
		<font face=\"arial, helvetica\" size=\"2\">$langName</font>
		</td>
		<td colspan=\"2\">$prenom_form</td>
		 </tr>
		<tr><td valign=\"top\">
		<font face=\"arial, helvetica\" size=\"2\">$langSurname</font>
		</td>
		<td colspan=\"2\">$nom_form</td>
		 </tr>
		<tr>
		<td valign=\"top\">
		<font face=\"arial, helvetica\" size=\"2\">$langUsername</font>
		</td>
		<td colspan=\"2\">$username_form</td>
		</tr>
		<tr>
		<td valign=\"top\">
		<font face=\"arial, helvetica\" size=\"2\">$langEmail</font>
		</td>
		<td colspan=\"2\">$email_form</td>
		</tr>
		<tr>
		<td colspan=\"3\">$langLDAPUser</td>
		</tr>
		</table>";
} else {		// Not LDAP user:
	if (!isset($urlSecure)) {
		$sec = $urlServer.'modules/profile/profile.php';
	} else {
		$sec = $urlSecure.'modules/profile/profile.php';
}
$tool_content .= "<form method=\"post\" action=\"$sec?submit=yes\">
	<table width=\"99%\">
	<thead>
	<tr>
		<th width=\"150\">
			$langName
		</th>
		<td>
			<input type=\"text\" size=\"40\" name=\"prenom_form\" value=\"$prenom_form\">
		</td>
	</tr>
	<tr>
		<th width=\"150\">
			$langSurname
		</th>
		<td>
			<input type=\"text\" size=\"40\" name=\"nom_form\" value=\"$nom_form\">
		</td>
	</tr>
	<tr>
		<th width=\"150\">
			$langUsername
		</th>
		<td>
			<input type=\"text\" size=\"40\" name=\"username_form\" value=\"$username_form\">
		</td>
	</tr>
	<tr>
		<th width=\"150\">
			$langPass
		</th>
		<td>
			<input type=\"password\" size=\"40\" name=\"password_form\" value=\"$password_form\">
		</td>
	</tr>
	<tr>
		<th width=\"150\">
			$langConfirmation
		</th>
		<td>
			<input type=\"password\" size=\"40\" name=\"password_form1\" value=\"$password_form\">
		</td>
	</tr>
	<tr>
		<th width=\"150\">
			$langEmail
		</th>
		<td>
			<input type=\"text\" size=\"40\" name=\"email_form\" value=\"$email_form\">
		</td>
	<tr>
		<th width=\"150\">
			$langAm
		</th>
		<td>
			<input type=\"text\" size=\"20\" name=\"am_form\" value=\"$am_form\">
		</td>
	</tr>
	</thead>
	</table>
	<br>
	<input type=\"Submit\" name=\"submit\" value=\"$langChange\">
	</form>
	<br>
	<p><a href='../unreguser/unreguser.php'>$langUnregUser</a></p>
	<br>
	";
}		// End of LDAP user added by adia
#############################################################
//$tool_content .=  "</td></tr><tr><td><br><hr noshade size=\"1\">";

$sql = "SELECT * FROM loginout 
	WHERE id_user = '".$_SESSION["uid"]."' ORDER by idLog DESC LIMIT 15";

$leResultat = mysql_query($sql);
$tool_content .= "
	<table width=\"99%\">
		<thead>
			<tr>
				<th>$langLastVisits</th>
			</tr>
		</thead>
	</table>
	<br>

	<table width=\"99%\">
		<thead>
			<tr> 
				<th>$langDate</th>
				<th>$langAction</th>
			</tr>
		</thead>
		<tbody>
			";
$i = 0;	
//$color[]=$color1;
//$color[]=$color2;

$nomAction["LOGIN"] = "<font color=\"#008000\">$langLogIn</font>";
$nomAction["LOGOUT"] = "<font color=\"#FF0000\">$langLogOut</font>";
while ($leRecord = mysql_fetch_array($leResultat)) {
   $when = $leRecord["when"];
   $action = $leRecord["action"];
   if ($i%2==1) {
   	 $tool_content .= "<tr>";
   } else {
   	$tool_content .= "<tr class=\"odd\">";
   }
   $tool_content .= "
	<td>
		".strftime("%Y-%m-%d %H:%M:%S ", strtotime($when))."
	</td>
	<td>".$nomAction[$action]."</td>
	</tr>";
}

$tool_content .= "</tbody></table>";
//end_page();
draw($tool_content, 1);
?>
