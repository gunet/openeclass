<?
$langFiles = array('registration', 'admin', 'gunet');
include '../../include/baseTheme.php';
include 'auth.inc.php';
check_admin();
$nameTools = "Εγγραφή Καθηγητή";
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
	$usercomment = isset($_POST['usercomment'])?$_POST['usercomment']:'';
	$department = isset($_POST['department'])?$_POST['department']:'';
	$institut = isset($_POST['institut'])?$_POST['institut']:'NULL';
	
	// do not allow the user to have the characters: ',\" or \\ in password
	/*
	$pw = array(); 	$nr = 0;
	while (isset($password{$nr})) // convert the string $password into an array $pw
	{
  	$pw[$nr] = $password{$nr};
    $nr++;
	}
  if( (in_array("'",$pw)) || (in_array("\"",$pw)) || (in_array("\\",$pw)) )
	{
	*/
	if( (strstr($password, "'")) or (strstr($password, '"')) or (strstr($password, '\\')) 
  or (strstr($uname, "'")) or (strstr($uname, '"')) or (strstr($uname, '\\')) )
	{
		$tool_content .= "<tr bgcolor=\"".$color2."\">
		<td bgcolor=\"$color2\" colspan=\"3\" valign=\"top\">
		<br>$langCharactersNotAllowed<br /><br />
		<a href=\"./newuser.php\">".$langAgain."</a></td></tr></table>";
	}
	else	// do the other checks
	{
		// Don't worry about figuring this regular expression out quite yet...// It will test for address@domainname and address@ip
		$regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,4})$";
		$emailtohostname = substr($email, (strrpos($email, "@") +1));
		
		// check if user name exists
		$username_check=mysql_query("SELECT username FROM `$mysqlMainDb`.user WHERE username='".escapeSimple($uname)."'");
		while ($myusername = mysql_fetch_array($username_check)) 
		{
			$user_exist=$myusername[0];
		}
	
		// check if there are empty fields
		if (empty($nom_form) or empty($prenom_form) or empty($password) or empty($usercomment) or empty($department) or empty($uname) or (empty($email_form) && !$userMailCanBeEmpty)) 
		{
			$tool_content .= "<p>$langEmptyFields</p>
			<br><br><center><p><a href=\"./newprof.php\">$langAgain</a></p></center>";
		}
		elseif(isset($user_exist) and $uname==$user_exist) 
		{
			$tool_content .= "<p>$langUserFree</p>
			<br><br><center><p><a href=\"./newprof.php\">$langAgain</a></p></center>";
	  }
		elseif(!$userMailCanBeEmpty &&!eregi($regexp,$email)) // check if email syntax is valid
		{
	        $tool_content .= "<p>$langEmailWrong.</p>
			<br><br><center><p><a href=\"./newprof.php\">$langAgain</a></p></center>";
		}
		else
		{
		
			$s = mysql_query("SELECT id FROM faculte WHERE name='$department'");
			$dep = mysql_fetch_array($s);
			$registered_at = time();
	 		$expires_at = time() + $durationAccount;
	 		switch($password)
					{
						case 'pop3': $auth = 2; break;
						case 'imap': $auth = 3; break;
						case 'ldap': $auth = 4; break;
						case 'db': $auth = 5; break;
						default: $auth=1; break;
					}
	 		
	 		if($auth==1)
	 		{		
				$crypt = new Encryption;
				$key = $encryptkey;
				$pswdlen = "20";
				$password_encrypted = $crypt->encrypt($key, $password, $pswdlen);
			}
			else
			{
				$password_encrypted = $password;
			}
			$uname = escapeSimple($uname);
			$inscr_user=mysql_query("INSERT INTO `$mysqlMainDb`.user
				(user_id, nom, prenom, username, password, email, statut, department, inst_id, registered_at, expires_at)
				VALUES ('NULL', '$nom_form', '$prenom_form', '$uname', '$password_encrypted', '$email_form','$statut','$dep[id]', '$institut', '$registered_at', '$expires_at')");
			$last_id=mysql_insert_id();
		        $tool_content .= "<p>$profsuccess</p>
							<br><br>
							<center><p><a href='../admin/listreq.php'>$langBackReq</a></p></center>";
		}
	
	}
	
}
else
{
$tool_content .= "	<form action=\"newprofadmin.php\" method=\"post\">
	<table width=\"99%\"><caption>Εισαγωγή Στοιχείων Καθηγητή</caption><tbody>
	<tr valign=\"top\" bgcolor=\"".$color2."\">
	<td width=\"3%\" nowrap><b>".$langSurname."</b></td>
	<td><input type=\"text\" name=\"nom_form\" value=\"".@$ps."\" >&nbsp;(*)</td>
	</tr>
	<tr bgcolor=\"".$color2."\">
	<td width=\"3%\" nowrap><b>".$langName."</b></td>
	<td>
	<input type=\"text\" name=\"prenom_form\" value=\"".@$pn."\">&nbsp;(*)</td>
	</tr>
	<tr bgcolor=\"".$color2."\">
	<td width=\"3%\" nowrap><b>".$langUsername."</b></td>
	<td><input type=\"text\" name=\"uname\" value=\"".@$pu."\">&nbsp;(*)</td>
	</tr>
	<tr bgcolor=\"".$color2."\">
	<td width=\"3%\" nowrap><b>".$langPass."&nbsp;:</b></td>
	<td><input type=\"text\" name=\"password\" value=\"".create_pass(5)."\"></td>
	</tr>
	<tr bgcolor=\"".$color2."\">
	<td width=\"3%\" nowrap><b>".$langEmail."</b></td>
	<td><input type=\"text\" name=\"email_form\" value=\"".@$pe."\">&nbsp;(*)</b></td>
	</tr>
	<tr bgcolor=\"".$color2."\">
        <td>".$profcomment."<br><font size=\"1\">".$profreason."
       	</td>
	<td>
        <textarea name=\"usercomment\" COLS=\"35\" ROWS=\"4\" WRAP=\"SOFT\">".@$usercomment."</textarea>
	<font size=\"1\">&nbsp;(*)</font>
        </td>
        </tr>
	<tr bgcolor=\"".$color2."\">
        <td>".$langDepartment.":</td>
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
</tbody></table></form>
";

$tool_content .= "<center><p><a href=\"../admin/index.php\">Επιστροφή</p></center>";
}
draw($tool_content,3,'admin');

// creating passwords automatically
function create_pass($length) {
	$res = "";
	$PASSCHARS="abcdefghijklmnopqrstuvwxyz023456789";
	$PASSL = 35;
	srand ((double) microtime() * 1000000);
	for ($i = 1; $i<=$length ; $i++ ) {
		$res .= $PASSCHARS[rand(0,$PASSL-1)];
	}
	return $res;
}
?>