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
$authmethods = array("imap","pop3","ldap","db");

$u_submitted = isset($_POST['u_submitted'])?$_POST['u_submitted']:'';
if((!empty($u)) && ctype_digit($u) )	// validate the user id
{
	$u = (int)$u;
	if(empty($u_submitted)) // if the form was not submitted
	{
		$sql = mysql_query("SELECT nom, prenom, username, password, email, phone, department, registered_at, expires_at FROM user WHERE user_id = '$u'");
		$info = mysql_fetch_array($sql);
		$tool_content .= "<div id=\"operations_container\"><ul id=\"opslist\">";
		if(!in_array($info['password'], $authmethods)) {
				$tool_content .= "<li><a href=\"./password.php\">".$langChangePass."</a></li>";
		}
		$tool_content .= " <li><a href='./listusers.php'>$langBack</a></li>";
		$tool_content .= "</ul></div>";
		$tool_content .= "<h4>$langEditUser $info[2]</h4>";
		$tool_content .= "<form name=\"edituser\" method=\"post\" action=\"$_SERVER[PHP_SELF]\">
	<table width=\"99%\" border=\"0\">
	<tr><th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%' width=\"20%\">$langSurname: </th>
	<td width=\"80%\"><input type=\"text\" name=\"lname\" size=\"40\" value=\"".$info[0]."\"</td></tr>
	<tr><th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%' width=\"20%\">$langName: </th>
	<td width=\"80%\"><input type=\"text\" name=\"fname\" size=\"40\" value=\"".$info[1]."\"</td></tr>";

if(!in_array($info['password'], $authmethods)) {
		$tool_content .= "<tr><th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%'  width=\"20%\">$langUsername: </th>
		<td width=\"80%\"><input type=\"text\" name=\"username\" size=\"30\" value=\"".$info[2]."\"</td></tr>";
	}
  else    // means that it is external auth method, so the user cannot change this password
  {
    switch($info['password'])
    {
      case "pop3": $auth=2;break;
      case "imap": $auth=3;break;
      case "ldap": $auth=4;break;
      case "db": $auth=5;break;
      default: $auth=1;break;
    }
    $auth_text = get_auth_info($auth);
    $tool_content .= "<tr><th width=\"150\" class='left'>".$langUsername. "</th>
      <td class=\"caution_small\">&nbsp;&nbsp;&nbsp;&nbsp;<b>".$info[2]."</b> [".$auth_text."]
        <input type=\"hidden\" name=\"username\" value=\"$info[2]\">
      </td></tr>";
 }

	$tool_content .= "<tr><th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%' width=\"20%\">E-mail: </th><td width=\"80%\"><input type=\"text\" name=\"email\" size=\"50\" value=\"".$info[4]."\"</td></tr>
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
	}  else {// if the form was submitted then update user
	
		// get the variables from the form and initialize them
		$fname = isset($_POST['fname'])?$_POST['fname']:'';
		$lname = isset($_POST['lname'])?$_POST['lname']:'';
		
		// trim white spaces in the end and in the beginning of the word
		$username = preg_replace('/\s+/', ' ', trim(isset($_POST['username'])?$_POST['username']:''));

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
		$user_exist= FALSE;
		// check if username is free
  		$username_check=mysql_query("SELECT username FROM user WHERE username='".escapeSimple($username)."'");
		$nums = mysql_num_rows($username_check);

if (mysql_num_rows($username_check) > 1) {
		    $user_exist = TRUE;
	  }

  // check if there are empty fields
  if (empty($fname) OR empty($lname) OR empty($username)) {
		$tool_content .= "<table width='99%'><tbody><tr>
           <td class='caution' height='60'><p>$langEmptyFields</p>
					<p><a href='$_SERVER[PHP_SELF]'>$langAgain</a></p></td></tr></tbody></table><br /><br />";
					draw($tool_content, 3, ' ', $head_content);
			    exit();
			}
 	 elseif(isset($user_exist) AND $user_exist == TRUE) {
					$tool_content .= "<table width='99%'><tbody><tr>
          	<td class='caution' height='60'><p>$langUserFree</p>
						<p><a href='$_SERVER[PHP_SELF]'>$langAgain</a></p></td></tr></tbody></table><br /><br />";
						draw($tool_content, 3, ' ', $head_content);
				    exit();
  }

		if($registered_at>$expires_at) {
				$tool_content .= "<center><br><b>$langExpireBeforeRegister<br><br><a href=\"edituser.php?u=".$u."\">$langAgain</a></b><br />";
		} else	{
			if ($u=='1') $department = 'NULL';
			$username = escapeSimple($username);
			$sql = "UPDATE user SET nom='".$lname."', prenom='".$fname."', 
				username='".$username."', email='".$email."', phone='".$phone."',
				department=".$department.", expires_at=".$expires_at." WHERE user_id = '".$u."'";
				$qry = mysql_query($sql);
				if (!$qry)
					$tool_content .= "$langNoUpdate:".$u."!";
				else
				{
					$num_update = mysql_affected_rows();
					if($num_update==1)
							$tool_content .= "<center><br><b>$langSuccessfulUpdate<br><br>";
					else
						$tool_content .= "<center>$langUpdateNoChange<br><br>";
				$tool_content .= "<a href='listusers.php'>$langBack</a></center>";	
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
