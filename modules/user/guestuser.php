<?
/**===========================================================================
*              GUnet e-Class 2.0
*       E-learning and Course Management Program
* ===========================================================================
*	Copyright(c) 2003-2006  Greek Universities Network - GUnet
*	Α full copyright notice can be read in "/info/copyright.txt".
*
*  Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/

$langFiles = array('registration','guest');
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'User';

include '../../include/baseTheme.php';

$nameTools = $langAddGuest;
$navigation[] = array ("url"=>"user.php", "name"=> $langUsers);

$tool_content = "";
// IF PROF ONLY
if($is_adminOfCourse)
{

	// Create guest account
	function createguest($c,$p) {

		global $langGuestUserName,$langGuestSurname,$langGuestName, $mysqlMainDb;

		// guest account user name
		$guestusername=$langGuestUserName.$c;
		// Guest account created...
		mysql_select_db($mysqlMainDb);

		$q=mysql_query("SELECT user_id FROM user WHERE username='$guestusername'");
		if (mysql_num_rows($q) > 0) {
			$s = mysql_fetch_array($q);

			mysql_query("UPDATE user SET password='$p' WHERE user_id='$s[0]'")
			or die ($langGuestFail);

			mysql_query("INSERT INTO cours_user (code_cours,user_id,statut,role)
			VALUES ('$c','$s[0]','10','Επισκέπτης')")
			or die ($langGuestFail);

		} else {
			mysql_query("INSERT INTO user (nom,prenom,username,password,statut)
			VALUES ('$langGuestName','$langGuestSurname','$guestusername','$p','10')")
			or die ($langGuestFail);

			mysql_query("INSERT INTO cours_user (code_cours,user_id,statut,role)
			VALUES ('$c','".mysql_insert_id()."','10','Επισκέπτης')")
			or die ($langGuestFail);
		}
	}


	// Checking if Guest account exists....
	function guestid($c) {
		global $mysqlMainDb;

		mysql_select_db($mysqlMainDb);
		$q1=mysql_query("SELECT user_id  from cours_user WHERE statut='10' AND code_cours='$c'");
		if (mysql_num_rows($q1) == 0) {
			return FALSE;
		} else {
			$s=mysql_fetch_array($q1);
			return $s[0];
		}
	}

	if (isset($createguest) and (!guestid($currentCourseID))) {

		createguest($currentCourseID,$guestpassword);
		$tool_content .= "<tr><td>$langGuestSuccess</td></tr>";
	} elseif (isset($changepass)) {

		$g=guestid($currentCourseID);
		$uguest=mysql_query("UPDATE user SET password='$guestpassword' WHERE user_id='$g'")
		or die($langGuestFail);
		$tool_content .= "<p>$langGuestChange</p>";
	} else {
		$id = guestid($currentCourseID);
		if ($id) {
			$tool_content .=  "<p>$langGuestExist</p>";
			
			$q1=mysql_query("SELECT nom,prenom,username,password FROM user where user_id='$id'");
			$s=mysql_fetch_array($q1);
			
			$tool_content .=  "<form method=\"post\" action=\"$_SERVER[PHP_SELF]\">";
			$tool_content .=  "<table>";
			$tool_content .= "<thead>";
			$tool_content .=  "<tr><th>$langName:</th><td>$s[nom]</td></tr>";
			$tool_content .=  "<tr><th>$langSurname:</th><td>$s[prenom]</td></tr>";
			$tool_content .=  "<tr><th>$langUsername:</th><td>$s[username]</td></tr>";
			$tool_content .=  "<tr><th>$langPass:</th><td><input type=\"text\" name=\"guestpassword\" value=\"".
			htmlspecialchars($s['password'])."\"></td></tr>";
			$tool_content .= "</thead>";
			$tool_content .=  "</table>";
			$tool_content .= "<br>";
			$tool_content .=  "<input type=\"submit\" name=\"changepass\" value=\"$langChangeGuestPasswd\">";
			$tool_content .=  "</form>";

		} else {

	$tool_content .="
		<p>$langAskGuest</p>
		
		<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">";
		
	$tool_content .= <<<tCont
		<table>	
		<thead>
		<tr><th>$langName:</th><td>$langGuestName</td></tr>
		<tr><th>$langSurname:</th><td>$langGuestSurname</td></tr>
		<tr><th>$langUsername:</th><td>$langGuestUserName$currentCourseID</td></tr>
		<tr><th>$langPass:</th><td><input type="text" name="guestpassword"></td></tr>
		</thead>
		</table>
		<br>
		<input type="submit" name="createguest" value="$langGuestAdd">
		</form>
tCont;
		}
	}

$tool_content .= <<<tCont2

<p><a href="user.php">$langBackUser</a><p>

tCont2;

draw($tool_content, 2);	 }

?>
