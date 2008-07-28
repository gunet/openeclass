<?
/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
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
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'User';
include '../../include/baseTheme.php';

$nameTools = $langAddGuest;
$navigation[] = array ("url"=>"user.php", "name"=> $langUsers);

$tool_content = "";
// IF PROF ONLY
if($is_adminOfCourse) {
	$tool_content .= "
    <div id=\"operations_container\">
      <ul id=\"opslist\">
        <li><a href=\"user.php\">$langBackUser</a></li>
      </ul>
    </div>";

	if (isset($createguest) and (!guestid($currentCourseID)))
	{
		// encrypt the password
		createguest($currentCourseID,md5($guestpassword));
		$tool_content .= "
    <table width=\"99%\">
    <thead>
    <tr>
      <td class=\"success\">$langGuestSuccess</td>
    </tr>
    </thead>
    </table>";
	} elseif (isset($changepass))
	{
		$g=guestid($currentCourseID);

		// encrypt the password
	 	$guestpassword_encrypted = md5($guestpassword);
		$uguest=mysql_query("UPDATE user SET password='$guestpassword_encrypted' WHERE user_id='$g'")
		or die($langGuestFail);
		$tool_content .= "
    <table width=\"99%\">
    <thead>
    <tr>
      <td class=\"success\">$langGuestChange</td>
    </tr>
    </thead>
    </table>";
	} else {
		$id = guestid($currentCourseID);
		if ($id) {
		$tool_content .= "
    <table width=\"99%\">
    <thead>
    <tr>
      <td class=\"success\">$langGuestExist</td>
    </tr>
    </thead>
    </table>";

			$q1=mysql_query("SELECT nom,prenom,username FROM user where user_id='$id'");
			$s=mysql_fetch_array($q1);

			$tool_content .=  "
    <form method=\"post\" action=\"$_SERVER[PHP_SELF]\">

    <table width=\"99%\" class=\"FormData\">
    <tbody>
    <tr>
      <th width=\"220\">&nbsp;</th>
      <td><b>$langUserData</b></td>
      <td align=\"right\"><small>$langAskGuest</small></td>
    </tr>
    <tr>
      <th class=\"left\">$langName:</th>
      <td>$s[nom]</td>
      <td align=\"right\"><small></small></td>
    </tr>
    <tr>
      <th class=\"left\">$langSurname:</th>
      <td>$s[prenom]</td>
      <td align=\"right\"><small></small></td>
    </tr>
    <tr>
      <th class=\"left\">$langUsername:</th>
      <td>$s[username]</td>
      <td align=\"right\"><small></small></td>
    </tr>
    <tr>
      <th class=\"left\">$langPass:</th>
      <td><input type=\"text\" name=\"guestpassword\" value=\"\"  class=\"FormData_InputText\"></td>
      <td align=\"right\"><small></small></td>
    </tr>
    <tr>
      <th>&nbsp;</th>
      <td><input type=\"submit\" name=\"changepass\" value=\"$langModify\"></td>
      <td align=\"right\"><small></small></td>
    </tr>
    </thead>
    </table>
    <br />

    </form>";

		} else {

	$tool_content .="
    <form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">";
	$tool_content .= <<<tCont


    <table width="99%" class="FormData">
    <tbody>
    <tr>
      <th width="220">&nbsp;</th>
      <td><b>$langUserData</b></td>
      <td align="right"><small>$langAskGuest</small></td>
    </tr>
    <tr>
      <th class="left">$langName:</th>
      <td>$langGuestName</td>
      <td align="right"><small></small></td>
    </tr>
    <tr>
      <th class="left">$langSurname:</th>
      <td>$langGuestSurname</td>
      <td align="right"><small></small></td>
    </tr>
    <tr>
      <th class="left">$langUsername:</th>
      <td>$langGuestUserName$currentCourseID</td>
      <td align="right"><small></small></td>
    </tr>
    <tr>
      <th class="left">$langPass:</th>
      <td><input type="text" name="guestpassword" class="FormData_InputText"></td>
      <td align="right"><small></small></td>
    </tr>
    <tr>
      <th>&nbsp;</th>
      <td><input type="submit" name="createguest" value="$langAdd"></td>
      <td align="right"><small></small></td>
    </tr>
    </thead>
    </table>
    <br>

    </form>
tCont;
		}
	}

draw($tool_content, 2, 'user');

}

// Create guest account
function createguest($c,$p)
{
	global $langGuestUserName, $langGuestSurname, $langGuestName, $mysqlMainDb;

	// guest account user name
	$guestusername=$langGuestUserName.$c;
	// Guest account created...
	mysql_select_db($mysqlMainDb);

	$q=mysql_query("SELECT user_id FROM user WHERE username='$guestusername'");

	if (mysql_num_rows($q) > 0) {
		$s = mysql_fetch_array($q);

		mysql_query("UPDATE user SET password='$p' WHERE user_id='$s[0]'")
		or die ($langGuestFail);

		mysql_query("INSERT INTO cours_user (code_cours,user_id,statut,reg_date)
			VALUES ('$c','$s[0]','10',CURDATE())")
		or die ($langGuestFail);
	}  else {
	$regtime = time();
	$exptime = 126144000 + $regtime;
	mysql_query("INSERT INTO user (nom,prenom,username,password,statut,registered_at,expires_at)
		VALUES ('$langGuestName','$langGuestSurname','$guestusername','$p','10',$regtime,$exptime)")
	or die ($langGuestFail);

	mysql_query("INSERT INTO cours_user (code_cours,user_id,statut,reg_date)
	VALUES ('$c','".mysql_insert_id()."','10',CURDATE())")
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
?>
