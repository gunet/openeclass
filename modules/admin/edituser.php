<?
/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Α full copyright notice can be read in "/info/copyright.txt".
        
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

// LANGFILES, BASETHEME, OTHER INCLUDES AND NAMETOOLS
$langFiles = array('admin','registration');
$require_admin = TRUE;
include '../../include/baseTheme.php';
include 'admin.inc.php';
include '../auth/auth.inc.php';
$nameTools = $langVersion;
// Initialise $tool_content
$tool_content = "";


$nameTools = $langEditUser;

$u = (string)isset($_GET['u'])?$_GET['u']:(isset($_POST['u'])?$_POST['u']:'');
$u_submitted = isset($_POST['u_submitted'])?$_POST['u_submitted']:'';
if((!empty($u)) && ctype_digit($u))	// validate the user id
{
	$u = (int)$u;
  if(empty($u_submitted)) // if the form was not submitted
  {
		$sql = mysql_query("
		SELECT nom, prenom, username, password, email, phone, department, registered_at, expires_at FROM user
		WHERE user_id = '$u'");
		if (!$sql) 
		{
	    die("Unable to query database (user_id='$u')!");
		}
		$info = mysql_fetch_array($sql);
		$password = $info[3];
		$authmethods = array("pop3","imap","ldap","db");
		if(!in_array($password,$authmethods))
		{
			$crypt = new Encryption;
			$key = $encryptkey;
			$password_decrypted = $crypt->decrypt($key, $info[3]);
		}
		else
		{
			$password_decrypted = $password;
		}
		$tool_content .= "<h4>Επεξεργασία χρήστη $info[2]</h4>";
		$tool_content .= "<form name=\"edituser\" method=\"post\" action=\"./edituser.php\">
	<table width=\"99%\" border=\"0\">
	<tr><td width=\"20%\">$langSurname: </td><td width=\"80%\"><input type=\"text\" name=\"lname\" size=\"40\" value=\"".$info[0]."\"</td></tr>
	<tr><td width=\"20%\">$langName: </td><td width=\"80%\"><input type=\"text\" name=\"fname\" size=\"40\" value=\"".$info[1]."\"</td></tr>
	<tr><td width=\"20%\">$langUsername: </td><td width=\"80%\"><input type=\"text\" name=\"username\" size=\"30\" value=\"".$info[2]."\"</td></tr>
	<tr><td width=\"20%\">$langPass: </td><td width=\"80%\"><input type=\"text\" name=\"password\" size=\"30\" value=\"".$password_decrypted."\"</td></tr>
	<tr><td width=\"20%\">E-mail: </td><td width=\"80%\"><input type=\"text\" name=\"email\" size=\"50\" value=\"".$info[4]."\"</td></tr>
	<tr><td width=\"20%\">$langTel: </td><td width=\"80%\"><input type=\"text\" name=\"phone\" size=\"30\" value=\"".$info[5]."\"</td></tr>";

	$tool_content .= "<tr><td width=\"20%\">$langDepartment: </td><td width=\"80%\">";
		if(!empty($info[6]))
		{
	    $department_select_box = list_departments($info[6]);
		}
		else
		{
	    $department_select_box = "";
		}
	
		$tool_content .= $department_select_box	    
		."</td></tr>";

		$tool_content .= "<tr><td width=\"20%\">$langRegistrationDate: </td><td width=\"80%\"><span style=\"color:green;font-weight:bold;\">".date("j/n/Y H:i",$info[7])."</span></td></tr>
		<tr><td width=\"20%\">$langExpirationDate: </td><td width=\"80%\">";
		
		$difference = abs($info[8]-$info[7]);		// Calculate the difference between registration and expiration
		
		$tool_content .= convert_time($difference)."<br /><br />";
		
		// format the drop-down menu for data
		$datetime = new DATETIME();
		$datetime->set_timename("hour", "min", "sec");
		$datetime->set_datetime_byvar2($info[8]);
		if ($datetime->get_date_error())
		{
	    $tool_content .= "<b><font color=red>".$datetime->get_date_error()."</font>";
		}
		else 
		{
			$tool_content .= "";
		}
		$tool_content .= $datetime->get_select_years("ldigit", "2002", "2029", "year")." "
	. $datetime->get_select_months(1, "sword", "month")." "
	. $datetime->get_select_days(1, "day")."&nbsp;&nbsp;&nbsp;"
	. $datetime->get_select_hours(1, 12, "hour")
	. $datetime->get_select_minutes(1, "min")
	. $datetime->get_select_seconds(1, "sec")
	. $datetime->get_select_ampm();		// end format date-menu
	
		$tool_content .= "</td></tr>
		<tr><td width=\"20%\">$langUserID: </td><td width=\"80%\">$u</td></tr>
		</table>
		<br /><input type=\"hidden\" name=\"u\" value=\"".$u."\">
		<input type=\"hidden\" name=\"u_submitted\" value=\"1\">
		<input type=\"hidden\" name=\"registered_at\" value=\"".$info[7]."\">
		<input type=\"submit\" name=\"submit_edituser\" value=\"$langUpdate\"><br /><br />
		</form>";
		
		$sql = mysql_query("
			SELECT nom, prenom, username FROM user
			WHERE user_id = '$u'");
		if (!$sql) 
		{
		    die("Unable to query database (user_id='$u')!");
		}
		
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
						$tool_content .= "</td><td align=\"center\"><a href=\"unreguser.php?u=$u&un=$info[2]&c=$logs[0]\">".
						"$langDelete</a></td></tr>\n";
						break;
					default:
						$tool_content .= $langVisitor;
						$tool_content .= "</td><td align=\"center\"><a href=\"unreguser.php?u=$u&un=$info[2]&c=$logs[0]\">".
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
			    $tool_content .= "<center><a href=\"unreguser.php?u=$u&un=$info[2]&c=$logs[0]\">$langDelete</a></center>";
				else 
			    $tool_content .= "<center><a href=\"unreguser.php?u=$u&un=$info[2]&c=\">$langDelete</a></center>";
			} 
		  else 
		  {
				$tool_content .= $langCannotDeleteAdmin;
		  }
		}
	}
	else	// if the form was submitted: DO THE UPDATE OF USER
	{
		// 1. get the variables from the form and initialize them
		$fname = isset($_POST['fname'])?$_POST['fname']:'';
		$lname = isset($_POST['lname'])?$_POST['lname']:'';
		$username = isset($_POST['username'])?$_POST['username']:'';
		$password = isset($_POST['password'])?$_POST['password']:'';
		$email = isset($_POST['email'])?$_POST['email']:'';
		$phone = isset($_POST['phone'])?$_POST['phone']:'';
		$department = isset($_POST['department'])?$_POST['department']:'NULL';
		$registered_at = isset($_POST['registered_at'])?$_POST['registered_at']:'';
		$datetime = new DATETIME();
		$datetime->set_timename("hour", "min", "sec");
		$datetime->set_datetime_byglobal("HTTP_POST_VARS");
		$expires_at = $datetime->get_timestamp_entered();
		$auth_methods = array('imap','pop3','ldap','db');
		// 2. do the database update
		// do not allow the user to have the characters: ',\" or \\ in password
		$pw = array(); 	$nr = 0;
		while (isset($password{$nr})) // convert the string $password into an array $pw
		{
	  	$pw[$nr] = $password{$nr};
	    $nr++;
		}
		if($registered_at>$expires_at)
		{
			$tool_content .= "<br >$langExpireBeforeRegister. Please <a href=\"edituser.php?u=".$u."\">try again</a><br />";
		}
		elseif( (in_array("'",$pw)) || (in_array("\"",$pw)) || (in_array("\\",$pw)) )
		{
			$tool_content .= "<tr bgcolor=\"".$color2."\">
			<td bgcolor=\"$color2\" colspan=\"3\" valign=\"top\">
			<br>Δεν επιτρέπονται στο password, οι χαρακτήρες: ',\" ή \\	<br /><br />
			<a href=\"./listusers.php\">".$langAgain."</a></td></tr></table>";
		}
		else
		{
			//if(($password!='imap') || ($password!='pop3') || ($password!='ldap') || ($password!='db'))
			if(!in_array($password,$auth_methods))
			{
				// encryption of password
				$crypt = new Encryption;
				$key = $encryptkey;
				$pswdlen = "20";
				$password_encrypted = $crypt->encrypt($key, $password, $pswdlen);
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
					$tool_content .= "<br /><br />$langSuccessfulUpdate:".$u."<br /><br />";
				}
			  else
				{
					$tool_content .= "$langUpdateNoChange<br />";
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

$tool_content .= "<center><p><a href=\"listusers.php\">$back</a></p></center>";
draw($tool_content,3);

?>


