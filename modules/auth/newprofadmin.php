<?
$require_admin = TRUE;
include '../../include/baseTheme.php';
$nameTools = $langProfReg;
$navigation[] = array("url" => "../admin/index.php", "name" => $langAdmin);

// Initialise $tool_content
$tool_content = "";
$submit = isset($_POST['submit'])?$_POST['submit']:'';
if($submit)
{
	// register user 
	$nom_form = isset($_POST['nom_form'])?$_POST['nom_form']:'';
	$prenom_form = isset($_POST['prenom_form'])?$_POST['prenom_form']:'';
	$uname = isset($_POST['uname'])?$_POST['uname']:'';
	$password = isset($_POST['password'])?$_POST['password']:'';
	$email_form = isset($_POST['email_form'])?$_POST['email_form']:'';
	$department = isset($_POST['department'])?$_POST['department']:'';
	
	// do not allow the user to have the characters: ',\" or \\ in username
	
	if ((strstr($uname, "'")) or (strstr($uname, '"')) or (strstr($uname, '\\')))
	{
		$tool_content .= "<tr bgcolor=\"".$color2."\">
		<td bgcolor=\"$color2\" colspan=\"3\" valign=\"top\">
		<br>$langCharactersNotAllowed<br /><br />
		<a href='$_SERVER[PHP_SELF]'>".$langAgain."</a></td></tr></table>";
	}
	else	// do the other checks
	{
		// check if user name exists
		$username_check=mysql_query("SELECT username FROM `$mysqlMainDb`.user WHERE username='".escapeSimple($uname)."'");
		while ($myusername = mysql_fetch_array($username_check)) 
		{
			$user_exist=$myusername[0];
		}
	
		// check if there are empty fields
		if (empty($nom_form) or empty($prenom_form) or empty($password) or empty($department) or empty($uname) or (empty($email_form))) 
		{
			$tool_content .= "<p>$langEmptyFields</p>
			<br><br><center><p><a href='$_SERVER[PHP_SELF]'>$langAgain</a></p></center>";
		}
		elseif(isset($user_exist) and $uname==$user_exist) 
		{
			$tool_content .= "<p>$langUserFree</p>
			<br><br><center><p><a href='$_SERVER[PHP_SELF]'>$langAgain</a></p></center>";
	  }
		elseif(!email_seems_valid($email_form)) // check if email syntax is valid
		{
      $tool_content .= "<p>$langEmailWrong.</p>
			<br><br><center><p><a href='$_SERVER[PHP_SELF]'>$langAgain</a></p></center>";
		}
		else
		{
			$s = mysql_query("SELECT id FROM faculte WHERE name='$department'");
			$dep = mysql_fetch_array($s);
			$registered_at = time();
	 		$expires_at = time() + $durationAccount;
			$password_encrypted = md5($password);
			$uname = escapeSimple($uname);
			$inscr_user=mysql_query("INSERT INTO `$mysqlMainDb`.user
				(user_id, nom, prenom, username, password, email, statut, department, registered_at, expires_at)
				VALUES ('NULL', '$nom_form', '$prenom_form', '$uname', '$password_encrypted', '$email_form','$statut','$dep[id]', '$registered_at', '$expires_at')");
			$last_id=mysql_insert_id();

		// close request
	  $rid = intval($_POST['rid']);
  	  db_query("UPDATE prof_request set status = '2',date_closed = NOW() WHERE rid = '$rid'");
	       $tool_content .= "<p>$profsuccess</p><br><br><center><p>
		<a href='../admin/listreq.php'>$langBackReq</a></p></center>";
		}
	}
}
else
{
$tool_content .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"post\">
	<table width=\"99%\"><caption>$langNewProf</caption><tbody>
	<tr valign=\"top\" bgcolor=\"".$color2."\">
	<th width=\"150\" class='left'><b>".$langSurname."</b></th>
	<td><input class='FormData_InputText' type=\"text\" name=\"nom_form\" value=\"".@$ps."\" >&nbsp;(*)</td>
	</tr>
	<tr bgcolor=\"".$color2."\">
	<th class='left'><b>".$langName."</b></th>
	<td>
	<input class='FormData_InputText' type=\"text\" name=\"prenom_form\" value=\"".@$pn."\">&nbsp;(*)</td>
	</tr>
	<tr bgcolor=\"".$color2."\">
	<th class='left'><b>".$langUsername."</b></th>
	<td><input class='FormData_InputText' type=\"text\" name=\"uname\" value=\"".@$pu."\">&nbsp;(*)</td>
	</tr>
	<tr bgcolor=\"".$color2."\">
	<th class='left'><b>".$langPass."&nbsp;:</b></th>
	<td><input class='FormData_InputText' type=\"text\" name=\"password\" value=\"".create_pass(5)."\"></td>
	</tr>
	<tr bgcolor=\"".$color2."\">
	<th class='left'><b>".$langEmail."</b></th>
	<td><input class='FormData_InputText' type=\"text\" name=\"email_form\" value=\"".@$pe."\">&nbsp;(*)</b></td>
	</tr>
	<tr bgcolor=\"".$color2."\">
        <th class='left'>".$langDepartment.":</th>
        <td><select name=\"department\">";
        $deps=mysql_query("SELECT name FROM faculte order by id");
        while ($dep = mysql_fetch_array($deps)) 
        {
        	$tool_content .= "<option value=\"$dep[0]\">$dep[0]</option>\n";
        }
        $tool_content .= "</select>
        </td>
        </tr>				
	<tr><td colspan=\"2\">".$langRequiredFields."</td></tr>
	<tr><td>&nbsp;</td>
	<td><input type=\"submit\" name=\"submit\" value=\"".$langOk."\" >
	<input type=\"hidden\" name=\"auth\" value=\"1\" >
	</td>
	</tr>
	<input type='hidden' name='rid' value='".@$id."'>	
	</tbody></table></form>";

$tool_content .= "<center><p><a href=\"../admin/index.php\">$langBack</p></center>";
}
draw($tool_content, 3, 'admin');
?>
