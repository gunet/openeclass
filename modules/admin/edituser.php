<? session_start();
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
edituser.php
@last update: 27-06-2006 by Karatzidis Stratos
@authors list: Karatzidis Stratos <kstratos@uom.gr>
Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================
@Description: Edit user info (eclass version)

This script allows the admin to :
- edit the user information
- activate / deactivate a user account

==============================================================================
*/

// BASETHEME, OTHER INCLUDES AND NAMETOOLS
$require_admin = TRUE;
include '../../include/baseTheme.php';
include 'admin.inc.php';
include '../auth/auth.inc.php';
include '../../include/jscalendar/calendar.php';

if (isset($_GET['u']) or isset($_POST['u']))
$_SESSION['u_tmp']=$u;
if(!isset($_GET['u']) or !isset($_POST['u']))
$u=$_SESSION['u_tmp'];

$tool_content = $head_content = "";

if ($language == 'greek') {
    $lang_editor='gr';
    $lang_jscalendar = 'el';
}
  else {
    $lang_editor='en';
    $lang_jscalendar = $lang_editor;
}

$jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/', $lang_jscalendar, 'calendar-blue2', false);
$head_content .= $jscalendar->get_load_files_code();

// Initialise $tool_content
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
$navigation[] = array("url" => "listusers.php", "name" => $langListUsersActions);
$nameTools = $langEditUser;

$u_submitted = isset($_POST['u_submitted'])?$_POST['u_submitted']:'';
if((!empty($u)) && ctype_digit($u) )	// validate the user id
{
	$u = (int)$u;
	if(empty($u_submitted) && !isset($_REQUEST['changePass'])) // if the form was not submitted
	{
		$sql = mysql_query("SELECT nom, prenom, username, password, email, phone, department, registered_at, expires_at 
										FROM user	WHERE user_id = '$u'");
		$info = mysql_fetch_array($sql);

		$tool_content .= "<div id=\"operations_container\"><ul id=\"opslist\">";
		$tool_content .= "<li><a href=\"./password.php\">".$langChangePass."</a></li>";
		$tool_content .= " <li><a href='./listusers.php'>$langBack</a></li>";
		$tool_content .= "</ul></div>";
		$tool_content .= "<h4>$langEditUser $info[2]</h4>";
		$tool_content .= "<form name=\"edituser\" method=\"post\" action=\"$_SERVER[PHP_SELF]\">
	<table width=\"99%\" border=\"0\">
	<tr><th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%' width=\"20%\">$langSurname: </th><td width=\"80%\"><input type=\"text\" name=\"lname\" size=\"40\" value=\"".$info[0]."\"</td></tr>
	<tr><th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%' width=\"20%\">$langName: </th><td width=\"80%\"><input type=\"text\" name=\"fname\" size=\"40\" value=\"".$info[1]."\"</td></tr>
	<tr><th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%'  width=\"20%\">$langUsername: </th><td width=\"80%\"><input type=\"text\" name=\"username\" size=\"30\" value=\"".$info[2]."\"</td></tr>
	<tr><th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%' width=\"20%\">E-mail: </th><td width=\"80%\"><input type=\"text\" name=\"email\" size=\"50\" value=\"".$info[4]."\"</td></tr>
	<tr><th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%' width=\"20%\">$langTel: </th><td width=\"80%\"><input type=\"text\" name=\"phone\" size=\"30\" value=\"".$info[5]."\"</td></tr>";

		$tool_content .= "<tr><th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%' width=\"20%\">$langDepartment: </th><td width=\"80%\">";
		if(!empty($info[6])) {
			$department_select_box = list_departments($info[6]);
		} else {
			$department_select_box = "";
		}

		$tool_content .= $department_select_box."</td></tr>";
		$tool_content .= "<tr>
			<th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%' width=\"20%\">$langRegistrationDate: </th>
			<td width=\"80%\"><span style=\"color:green;font-weight:bold;\">".date("j/n/Y H:i",$info[7])."</span></td></tr>
			<tr><th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%' width=\"20%\">$langExpirationDate: </th>
			<td width=\"80%\">";

		$dateregistration = date("j-n-Y", $info[8]);
		$hour = date("H", $info[8]);
		$minute = date("i", $info[8]);
		
// -- jscalendar ------
		$start_cal = $jscalendar->make_input_field(
       array('showOthers' => true,
                'align' => 'Tl',
                 'ifFormat' => '%d-%m-%Y'),
       array('style' => 'width: 15em; color: #840; background-color: #ff8; border: 1px solid #000; text-align: center',
                 'name' => 'date',
                 'value' => $dateregistration));
		
		$tool_content .= $start_cal."&nbsp;&nbsp;&nbsp;";
		$tool_content .= "<select name='hour'>
        <option value='$hour'>$hour</option>
        <option value='--'>--</option>";
    for ($h=0; $h<=24; $h++)
			 $tool_content .= "<option value='$h'>$h</option>";
    $tool_content .= "</select>&nbsp;&nbsp;&nbsp;";
	  $tool_content .= "<select name=\"minute\">
	    <option value=\"$minute\">$minute</option>
  	  <option value=\"--\">--</option>";
    for ($m=0; $m<=55; $m=$m+5)
          $tool_content .= "<option value='$m'>$m</option>";
    $tool_content .= "</select></td>";

		$tool_content .= "</tr>
		<tr><th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%' width=\"20%\">$langUserID: </th><td width=\"80%\">$u</td></tr>
		</table>
		<br /><input type=\"hidden\" name=\"u\" value=\"".$u."\">
		<input type=\"hidden\" name=\"u_submitted\" value=\"1\">
		<input type=\"hidden\" name=\"registered_at\" value=\"".$info[7]."\">
		<input type=\"submit\" name=\"submit_edituser\" value=\"$langModify\"><br /><br />
		</form>";

		$sql = mysql_query("SELECT nom, prenom, username FROM user WHERE user_id = '$u'");
		$sql = mysql_query("SELECT a.code, a.intitule, b.statut, a.cours_id
			FROM cours AS a LEFT JOIN cours_user AS b ON a.code = b.code_cours
			WHERE b.user_id = '$u' ORDER BY b.statut, a.faculte");

		// αν ο χρήστης συμμετέχει σε μαθήματα τότε παρουσίασε τη λίστα
		if (mysql_num_rows($sql) > 0)
		{
			$tool_content .= "<h4>$langStudentParticipation</h4>\n".
			"<table border=\"1\">\n<tr><th>$langLessonCode</th><th>$langLessonName</th>".
			"<th>$langProperty</th><th>$langActions</th></tr>";

			for ($j = 0; $j < mysql_num_rows($sql); $j++)
			{
				$logs = mysql_fetch_array($sql);
				$tool_content .= "<tr><td>".htmlspecialchars($logs[0])."</td><td>".
				htmlspecialchars($logs[1])."</td><td align=\"center\">";
				switch ($logs[2])
				{
					case 1:
						$tool_content .= $langTeacher;
						$tool_content .= "</td><td align=\"center\">---</td></tr>\n";
						break;
					case 5:
						$tool_content .= $langStudent;
						$tool_content .= "</td><td align=\"center\"><a href=\"unreguser.php?u=$u&c=$logs[0]\">".
						"$langDelete</a></td></tr>\n";
						break;
					default:
						$tool_content .= $langVisitor;
						$tool_content .= "</td><td align=\"center\"><a href=\"unreguser.php?u=$u&c=$logs[0]\">".
						"$langDelete</a></td></tr>\n";
						break;
				}
			}
			$tool_content .= "</table>\n";
		}
		else
		{
			$tool_content .= "<h2>$langNoStudentParticipation</h2>";
			if ($u > 1)
			{
				if (isset($logs))
					$tool_content .= "<center><a href=\"unreguser.php?u=$u&c=$logs[0]\">$langDelete</a></center>";
				else
					$tool_content .= "<center><a href=\"unreguser.php?u=$u&c=\">$langDelete</a></center>";
			}
			else
			{
				$tool_content .= $langCannotDeleteAdmin;
			}
		}
	}
	elseif (isset($changePass) && ($changePass == 1)) {
		//Show message if exists
		if(isset($msg))
		{

			switch ($msg){

				case 2: {//passwords do not match
					$message = $langPassTwo;
					$urlText = "";
					$type = "caution";
					break;
				}

				case 3: { //pass too easy
					$message = $langPassTooEasy .": <strong>".substr(md5(date("Bis").$_SERVER['REMOTE_ADDR']),0,8)."</strong>";
					$urlText = "";
					$type = "caution";
					break;
				}

				case 4: { // admin tools
					$message = $langFields;
					$urlText = "";
					$type = "caution";
					break;
				}

				case 7: {//password successfully changed
					$message = $langPassChanged;
					$urlText = $langBack;
					$type = "success";
					break;
				}

				case 9: {//not acceptable characters in password
					$message = $langInvalidCharsPass;
					$urlText = "";
					$type = "caution";
					break;
				}

			}

			$tool_content .=  "
			<table width=\"99%\">
				<tbody>
					<tr>
						<td class=\"$type\">
						$message<br>
    <a href=\"./listusers.php\">$urlText</a>
					</td>
					</tr>
				</tbody>
			</table><br/>";

		}
		$tool_content .= "<form method=\"post\" action=\"$_SERVER[PHP_SELF]?submit=yes&changePass=do\">
    <table width=\"99%\">
    <thead>
    <tr>
       <th width=\"150\">
            $langNewPass1
        </th>
        <td>";

		$tool_content .= "<input type=\"password\" size=\"40\" name=\"password_form\" value=\"\">
					</td>
		</tr>
    <tr>
        <th width=\"150\">
            $langNewPass2
        </th>
        <td>       		
            <input type=\"password\" size=\"40\" name=\"password_form1\" value=\"\">
        </td>
    </tr>";
		$tool_content .= "
    </thead></table>
    <br><input type=\"Submit\" name=\"submit\" value=\"$langModify\">
    </form>
   ";
	} elseif (isset($changePass) && ($changePass == "do")) {

		if (empty($_REQUEST['password_form']) || empty($_REQUEST['password_form1'])) {
			header("location:". $_SERVER['PHP_SELF']."?changePass=1&msg=4");
			exit();
		}

		if ($_REQUEST['password_form1'] !== $_REQUEST['password_form']) {
			header("location:". $_SERVER['PHP_SELF']."?changePass=1&msg=2");
			exit();
		}

		//check for not acceptable characters in password
		if ((strstr($_REQUEST['password_form'], "'")) 
				or (strstr($_REQUEST['password_form'], '"')) 
				or (strstr($_REQUEST['password_form'], '\\'))
				or (strstr($_REQUEST['password_form1'], "'")) 
				or (strstr($_REQUEST['password_form1'], '"')) 
					or (strstr($_REQUEST['password_form1'], '\\')))
		{
			header("location:". $_SERVER['PHP_SELF']."?changePass=1&msg=9");
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
			header("location:". $_SERVER['PHP_SELF']."?changePass=1&msg=3");
			exit();
		}


		//all checks ok. Change password!
		$new_pass = md5($_REQUEST['password_form']);

		$sql = "UPDATE `user` SET `password` = '$new_pass' WHERE `user_id` = ".$u."";
		db_query($sql, $mysqlMainDb);
		header("location:". $_SERVER['PHP_SELF']."?changePass=1&msg=7");
		exit();

	} else {// if the form was submitted: DO THE UPDATE OF USER
	
		// 1. get the variables from the form and initialize them
		$fname = isset($_POST['fname'])?$_POST['fname']:'';
		$lname = isset($_POST['lname'])?$_POST['lname']:'';
		$username = isset($_POST['username'])?$_POST['username']:'';
		$password = isset($_POST['password'])?$_POST['password']:'';
		$email = isset($_POST['email'])?$_POST['email']:'';
		$phone = isset($_POST['phone'])?$_POST['phone']:'';
		$department = isset($_POST['department'])?$_POST['department']:'NULL';
		$registered_at = isset($_POST['registered_at'])?$_POST['registered_at']:'';
		$date = isset($_POST['date'])?$_POST['date']:'';
		$hour = isset($_POST['hour'])?$_POST['hour']:'';
		$minute = isset($_POST['minute'])?$_POST['minute']:'';
		
		$date = split("-",  $date);
    $day=$date[0];
    $year=$date[2];
    $month=$date[1];
		$expires_at = mktime($hour, $minute, 0, $month, $day, $year);
	
		$auth_methods = array('imap','pop3','ldap','db');
		// 2. do the database update
		// do not allow the user to have the characters: ',\" or \\ in password
		$pw = array(); 	
		$nr = 0;
		while (isset($password{$nr})) // convert the string $password into an array $pw
		{
			$pw[$nr] = $password{$nr};
			$nr++;
		}
		if($registered_at>$expires_at)
		{
			$tool_content .= "<br >$langExpireBeforeRegister <br><br>$langPlease <a href=\"edituser.php?u=".$u."\">$langAgain</a><br />";
		}
		elseif( (in_array("'",$pw)) || (in_array("\"",$pw)) || (in_array("\\",$pw)) )
		{
			$tool_content .= "<tr bgcolor=\"".$color2."\">
			<td bgcolor=\"$color2\" colspan=\"3\" valign=\"top\">
			<br>$langCharactersNotAllowed<br /><br />
			<a href=\"./listusers.php\">".$langAgain."</a></td></tr></table>";
		}
		else
		{

			if(!in_array($password,$auth_methods) && strlen($password > 3))
			{
				// encryption of password
				$password_encrypted = md5($password);
			}
			else
			{
				$password_encrypted = $password;
			}

			if($u=='1')
			{
				$department = 'NULL';
			}
			$username = escapeSimple($username);
			$sql = "UPDATE user
								SET nom='".$lname."', prenom='".$fname."', username='".$username."', password='".$password_encrypted."', email='".$email."', phone='".$phone."',department=".$department.", expires_at=".$expires_at.
				" WHERE user_id = '".$u."'";
				$qry = mysql_query($sql);
				if (!$qry)
				{
					$tool_content .= "$langNoUpdate:".$u."!";
				}
				else
				{
					$num_update = mysql_affected_rows();
					if($num_update==1)
					{
						$tool_content .= "<center><br><b>$langSuccessfulUpdate<br><br>";
						$tool_content .= "<a href='listusers.php'>$langBack</a></center>";	
					}
					else
					{
						$tool_content .= "<center>$langUpdateNoChange<br><br>";
						$tool_content .= "<a href='listusers.php'>$langBack</a></center>";	
					}
				}
		}
	}
}
else
{
	// Αλλιώς... τι γίνεται;
	$tool_content .= "<h1>$langError</h1>\n<p><a href=\"listcours.php\">$back</p>\n";
}

draw($tool_content, 3, ' ', $head_content);
?>
