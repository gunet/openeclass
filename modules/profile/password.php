<?PHP
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
/**
 * Index
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract Password change component
 *
 */
$require_login = true;
$helpTopic = 'Profile';
$require_valid_uid = TRUE;

include '../../include/baseTheme.php';

$nameTools = $langChangePass;
$navigation[]= array ("url"=>"../profile/profile.php", "name"=> $langModifProfile);

check_uid();
$tool_content = "";
$passurl = $urlSecure.'modules/profile/password.php';

if (isset($submit) && isset($changePass) && ($changePass == "do")) {

	if (empty($_REQUEST['password_form']) || empty($_REQUEST['password_form1']) || empty($_REQUEST['old_pass'])) {
		header("location:". $passurl."?msg=3");
		exit();
	}

	if ($_REQUEST['password_form1'] !== $_REQUEST['password_form']) {
		header("location:". $passurl."?msg=1");
		exit();
	}

	// check if passwd is too easy
	$sql = "SELECT `nom`,`prenom` ,`username`,`email`,`am` FROM `user`WHERE `user_id`=".$_SESSION["uid"]." ";
	$result = db_query($sql, $mysqlMainDb);
	$myrow = mysql_fetch_array($result);

	if ((strtoupper($_REQUEST['password_form1']) == strtoupper($myrow['nom']))
	|| (strtoupper($_REQUEST['password_form1']) == strtoupper($myrow['prenom']))
	|| (strtoupper($_REQUEST['password_form1']) == strtoupper($myrow['username']))
	|| (strtoupper($_REQUEST['password_form1']) == strtoupper($myrow['email']))
	|| (strtoupper($_REQUEST['password_form1']) == strtoupper($myrow['am']))) {
		header("location:". $passurl."?msg=2");
		exit();
	}

	//all checks ok. Change password!
	$sql = "SELECT `password` FROM `user` WHERE `user_id`=".$_SESSION["uid"]." ";
	$result = db_query($sql, $mysqlMainDb);
	$myrow = mysql_fetch_array($result);

	$old_pass = md5($_REQUEST['old_pass']) ;
	$old_pass_db = $myrow['password'];
	$new_pass = md5($_REQUEST['password_form']);

	if($old_pass == $old_pass_db) {

		$sql = "UPDATE `user` SET `password` = '$new_pass' WHERE `user_id` = ".$_SESSION["uid"]."";
		db_query($sql, $mysqlMainDb);
		header("location:". $passurl."?msg=4");
		exit();
	} else {
		header("location:". $passurl."?msg=5");
		exit();
	}

}

//Show message if exists
if(isset($msg)) {

	switch ($msg){

		case 1: {//passwords do not match
			$message = $langPassTwo;
			$urlText = "";
			$type = "caution";
			break;
		}

		case 2: { //pass too easy
			$message = $langPassTooEasy .": <strong>".substr(md5(date("Bis").$_SERVER['REMOTE_ADDR']),0,8)."</strong>";
			$urlText = "";
			$type = "caution";
			break;
		}

		case 3: { // admin tools
			$message = $langFields;
			$urlText = "";
			$type = "caution";
			break;
		}

		case 4: {//password successfully changed
			$message = $langPassChanged;
			$urlText = $langHome;
			$type = "success";
			break;
		}

		case 5: {//wrong old password entered
			$message = $langPassOldWrong;
			$urlText = "";
			$type = "caution";
			break;
		}

		case 6: {//not acceptable characters in password
			$message = $langInvalidCharsPass;
			$urlText = "";
			$type = "caution";
			break;
		}

		default:die("invalid message id");

	}

	$tool_content .= "<table width=\"99%\">
			<tbody><tr><td class=\"$type\">$message<br>
		    <a href=\"$urlServer\">$urlText</a>
					</td></tr></tbody>
			</table><br/>";
}

if (!isset($changePass)) {
	$tool_content .= "
<form method=\"post\" action=\"$passurl?submit=yes&changePass=do\">
  <table width=\"99%\">
  <tbody>
  <tr>
    <th width=\"220\" class='left'>$langOldPass</th>
    <td><input class='FormData_InputText' type=\"password\" size=\"40\" name=\"old_pass\" value=\"\"></td>
    </tr>
   <tr>
     <th class='left'>$langNewPass1</th>
     <td>";

	$tool_content .= "<input class='FormData_InputText' type=\"password\" size=\"40\" name=\"password_form\" value=\"\"></td>
   </tr>
   <tr>
     <th width=\"150\" class='left'>$langNewPass2</th>
     <td><input class='FormData_InputText' type=\"password\" size=\"40\" name=\"password_form1\" value=\"\"></td>
    </tr>
	<tr>
      <th>&nbsp;</th>
      <td><input type=\"Submit\" name=\"submit\" value=\"$langModify\"></td>
    </tr>
	</tbody>
    </table>

</form>
   ";
}

draw($tool_content, 1);
?>
