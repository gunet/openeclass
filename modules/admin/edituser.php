<? session_start();
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

/* *===========================================================================
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

$lang_editor = $lang_jscalendar = langname_to_code($language);

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
		$sql = mysql_query("SELECT nom, prenom, username, password, email, phone, department, registered_at, expires_at, statut, am FROM user WHERE user_id = '$u'");
		$info = mysql_fetch_array($sql);
		$tool_content .= "
  <div id=\"operations_container\">
    <ul id=\"opslist\">";
		if(!in_array($info['password'], $authmethods)) {
			$tool_content .= "
      <li><a href='password.php?userid=$u'>".$langChangePass."</a></li>";
		}
		$tool_content .= "
      <li><a href='./listusers.php'>$langBack</a></li>";
		$tool_content .= "
    </ul>
  </div>";
		$tool_content .= "
<form name='edituser' method='post' action='$_SERVER[PHP_SELF]'>
  <table class='FormData' width='99%' align='left'>
  <tbody>
  <tr>
    <th width='220'>&nbsp;</th>
    <td><b>$langEditUser ".q($info[2])."</b></td>
  </tr>
  <tr>
    <th class='left'>$langSurname:</th>
    <td><input type='text' name='lname' size='50' value='".q($info['nom'])."' /></td>
  </tr>
  <tr>
    <th class='left'>$langName:</th>
	<td><input type='text' name='fname' size='50' value='".q($info['prenom'])."' /></td>
  </tr>";

if(!in_array($info['password'], $authmethods)) {
		$tool_content .= "
  <tr>
    <th class='left'>$langUsername:</th>
    <td><input type='text' name='username' size='50' value='".q($info['username'])."' /></td>
  </tr>";
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
    $tool_content .= "
  <tr>
    <th class='left'>".$langUsername. "</th>
    <td class='caution_small'>&nbsp;&nbsp;&nbsp;&nbsp;<b>".q($info['username'])."</b> [".$auth_text."]
        <input type='hidden' name='username' value='".q($info['username'])."' />
    </td>
  </tr>";
 }

$tool_content .= "
  <tr>
    <th class='left'>e-mail: </th>
    <td><input type='text' name='email' size='50' value='".q($info['email'])."' /></td>
  </tr>
  <tr>
    <th class='left'>$langAm: </th>
    <td><input type='text' name='am' size='50' value='".q($info['am'])."' /></td>
  </tr>
 <tr>
   <th class='left'>$langTel: </th>
   <td><input type='text' name='phone' size='50' value='".q($info['phone'])."' /></td>
  </tr>";
	$tool_content .= "<tr>
    <th class='left'>$langFaculty:</th>
    <td>";
		if(!empty($info[6])) {
			$department_select_box = list_departments($info['department']);
		} else {
			$department_select_box = "";
		}

	$tool_content .= $department_select_box."</td></tr>";
	$tool_content .= "<tr><th class='left'>$langProperty:</th><td>";
	if ($info[9] == '10') { // if we are guest user do not display selection
		$tool_content .= selection(array(10 => $langGuest), 'newstatut', $info['statut']);
	} else {
		$tool_content .= selection(array(1 => $langTeacher, 5 => $langStudent), 'newstatut', $info['statut']);
	}
	$tool_content .= "</td>";

	$tool_content .= "<tr>
    <th class='left'>$langRegistrationDate:</th>
    <td><span style='color:green;font-weight:bold;'>".date("j/n/Y H:i",$info['registered_at'])."</span></td>
  </tr>
  <tr>
    <th class='left'>$langExpirationDate: </th>
    <td>";
        $dateregistration = date("j-n-Y", $info['expires_at']);
        $hour = date("H", $info['expires_at']);
        $minute = date("i", $info['expires_at']);

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
        $tool_content .= "<select name='minute'>
                          <option value='$minute'>$minute</option>
                          <option value='--'>--</option>";
        for ($m = 0; $m <= 55; $m = $m + 5)
                $tool_content .= "<option value='$m'>$m</option>";
        $tool_content .= "</select></td>";

	$tool_content .= "
  </tr>
  <tr>
    <th class='left'>$langUserID: </th>
    <td>$u</td>
  </tr>
  <tr>
    <th class='left'>&nbsp;</th>
    <td>
      <input type='hidden' name='u' value='$u' />
      <input type='hidden' name='u_submitted' value='1' />
      <input type='hidden' name='registered_at' value='".$info['registered_at']."' />
      <input type='submit' name='submit_edituser' value='$langModify' />
    </td>
  </tr>
  <tr>
    <td colspan='2'>&nbsp;</td>
  </tr>
  </tbody>
  </table>
</form>";

		$sql = mysql_query("SELECT nom, prenom, username FROM user WHERE user_id = '$u'");
		$sql = mysql_query("SELECT a.code, a.intitule, b.reg_date, b.statut, a.cours_id
			FROM cours AS a LEFT JOIN cours_user AS b ON a.cours_id = b.cours_id
			WHERE b.user_id = '$u' ORDER BY b.statut, a.faculte");

		// αν ο χρήστης συμμετέχει σε μαθήματα τότε παρουσίασε τη λίστα
		if (mysql_num_rows($sql) > 0)
		{
			$tool_content .= "<br />
			<table class='FormData' align='left'>
			<tbody>
			<tr>
			<th colspan='5'>$langStudentParticipation</th>
			</tr>
			<tr>
			<th>$langLessonCode</th>
			<th>$langLessonName</th>
			<th>$langCourseRegistrationDate</th><th>$langProperty</th><th>$langActions</th>
			</tr>";

			for ($j = 0; $j < mysql_num_rows($sql); $j++)
			{
				$logs = mysql_fetch_array($sql);
				$tool_content .= "<tr>
						<td>".htmlspecialchars($logs[0])."</td>
						<td>".htmlspecialchars($logs[1])."</td><td align='center'>";
				if ($logs[2] == '0000-00-00')
					 $tool_content .= $langUnknownDate;
				else
					$tool_content .= " ".nice_format($logs[2])." ";
				$tool_content .= "</td><td align='center'>";
				switch ($logs[3])
				{
					case 1:
						$tool_content .= $langTeacher;
						$tool_content .= "</td><td align=\"center\">---</td></tr>\n";
						break;
					case 5:
						$tool_content .= $langStudent;
						$tool_content .= "</td><td align='center'>
						<a href='unreguser.php?u=$u&amp;c=$logs[0]'>
						<img src='../../template/classic/img/delete.gif' title='$langDelete'></img></a></td>
  						</tr>\n";
						break;
					default:
						$tool_content .= $langVisitor;
						$tool_content .= "</td><td align='center'>
						<a href='unreguser.php?u=$u&amp;c=$logs[0]'>
						<img src='../../template/classic/img/delete.gif' title='$langDelete'></img></a></td></tr>\n";
						break;
				}
			}
			$tool_content .= "</tbody></table>\n";
		}
		else
		{
			$tool_content .= "<h4>$langNoStudentParticipation</h4>";
			if ($u > 1)
			{
				if (isset($logs))
					$tool_content .= "<center>
					<a href=\"unreguser.php?u=$u&c=$logs[0]\">$langDelete</a></center>";
				else
					$tool_content .= "<center>
					<a href=\"unreguser.php?u=$u&c=\">$langDelete</a></center>";
			}
			else
			{
				$tool_content .= $langCannotDeleteAdmin;
			}
		}
	}  else { // if the form was submitted then update user

		// get the variables from the form and initialize them
		$fname = isset($_POST['fname'])?$_POST['fname']:'';
		$lname = isset($_POST['lname'])?$_POST['lname']:'';

		// trim white spaces in the end and in the beginning of the word
		$username = preg_replace('/\ +/', ' ', trim(isset($_POST['username'])?$_POST['username']:''));
		$email = isset($_POST['email'])?$_POST['email']:'';
		$phone = isset($_POST['phone'])?$_POST['phone']:'';
		$am = isset($_POST['am'])?$_POST['am']:'';
		$department = isset($_POST['department'])?$_POST['department']:'NULL';
		$newstatut = isset($_POST['newstatut'])?$_POST['newstatut']:'NULL';
		$registered_at = isset($_POST['registered_at'])?$_POST['registered_at']:'';
		$date = isset($_POST['date'])?$_POST['date']:'';
		$hour = isset($_POST['hour'])?$_POST['hour']:'';
		$minute = isset($_POST['minute'])?$_POST['minute']:'';
		$date = explode("-",  $date);
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
		} else {
			if ($u=='1') $department = 'NULL';
			$sql = "UPDATE user SET nom = ".autoquote($lname).", prenom = ".autoquote($fname).",
				username = ".autoquote($username).", email = ".autoquote($email).", 
				statut = ".intval($newstatut).", phone=".autoquote($phone).",
				department = ".intval($department).", expires_at=".$expires_at.",
                                am = ".autoquote($am)." WHERE user_id = ".intval($u);
			$qry = db_query($sql);
                        if (!$qry) {
                                $tool_content .= "$langNoUpdate:".$u."!";
                        } else {
                                $num_update = mysql_affected_rows();
                                if ($num_update == 1) {
                                        $tool_content .= "<center><br /><b>$langSuccessfulUpdate</b><br><br />";
                                } else {
                                        $tool_content .= "<center><br /><b>$langUpdateNoChange</b><br><br />";
                                }
                        }
                        $tool_content .= "<a href='listusers.php'>$langBack</a></center>";
                }
	}
}
else
{
	// Αλλιώς... τι γίνεται;
	$tool_content .= "<h1>$langError</h1>\n<p><a href=\"listcours.php\">$back</p>\n";
}

draw($tool_content, 3, null, $head_content);
