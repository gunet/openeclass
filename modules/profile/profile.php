<?
/*
=============================================================================
GUnet e-Class 2.0
E-learning and Course Management Program
================================================================================
Copyright(c) 2003-2006  Greek Universities Network - GUnet
Α full copyright notice can be read in "/info/copyright.txt".

Authors:     Costas Tsibanis <k.tsibanis@noc.uoa.gr>
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
==============================================================================
*/

$langFiles = array('registration','usage');
$require_help = TRUE;
$helpTopic = 'Profile';
include '../../include/baseTheme.php';
include "../auth/auth.inc.php";
$require_valid_uid = TRUE;
$tool_content = "";

check_uid();

$nameTools = $langModifProfile;

check_guest();

//if (isset($submit) && ($ldap_submit != "ON")) {
if (isset($submit) && (!isset($ldap_submit))) 
{
	$password_form = isset($_POST['password_form'])?$_POST['password_form']:'';
	$password_form1 = isset($_POST['password_form1'])?$_POST['password_form1']:'';
	/*
	// do not allow the user to have the characters: ',\" or \\ in password
	$pw = array(); 	$nr = 0;	$pw1 = array();	$nr1 = 0;
	while (isset($password_form{$nr})) // convert the string $password_form1 into an array $pw
	{
  	$pw[$nr] = $password_form{$nr};
    $nr++;
	}
	while (isset($password_form1{$nr1})) // convert the string $password_form1 into an array $pw1
	{
  	$pw[$nr1] = $password_form1{$nr1};
    $nr1++;
	}
	
  if( (in_array("'",$pw)) || (in_array("\"",$pw)) || (in_array("\\",$pw)) || (in_array("'",$pw1)) || (in_array("\"",$pw1)) || (in_array("\\",$pw1)) )
	{
	*/
	if( (strstr($password_form, "'")) or (strstr($password_form, '"')) or (strstr($password_form, '\\')) 
	or (strstr($password_form1, "'")) or (strstr($password_form1, '"')) or (strstr($password_form1, '\\'))
  or (strstr($username_form, "'")) or (strstr($username_form, '"')) or (strstr($username_form, '\\')) )
	{
		$tool_content .= "<tr bgcolor=\"".$color2."\">
		<td bgcolor=\"$color2\" colspan=\"3\" valign=\"top\">
		<br>$langCharactersNotAllowed<br /><br />
		<a href=\"./newuser.php\">".$langAgain."</a></td></tr></table>";
	}
	else	// do the other checks
	{
	
		$regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,4})$";
	
		// check if username exists
	
		$username_check=mysql_query("SELECT username FROM user WHERE username='".escapeSimple($username_form)."'");
		while ($myusername = mysql_fetch_array($username_check)) 
		{
			$user_exist=$myusername[0];
		}
	
		// check if passwds are the same
	
		if ($password_form1 !== $password_form) {
			header("location:". $_SERVER['PHP_SELF']."?msg=2");
		}
	
		// check if passwd is too easy
	
		elseif ((strtoupper($password_form1) == strtoupper($username_form))
		|| (strtoupper($password_form1) == strtoupper($nom_form))
		|| (strtoupper($password_form1) == strtoupper($prenom_form))
		|| (strtoupper($password_form1) == strtoupper($email_form))) {
			header("location:". $_SERVER['PHP_SELF']."?msg=3");
		}
	
		// check if there are empty fields
	
		elseif (empty($nom_form) OR empty($prenom_form) OR empty($password_form1)
		OR empty($password_form) OR empty($username_form) OR empty($email_form)) {
			header("location:". $_SERVER['PHP_SELF']."?msg=4");
		}
	
		// check if username is free
		elseif(isset($user_exist) AND ($username_form==$user_exist) AND ($username_form!=$uname)) {
			header("location:". $_SERVER['PHP_SELF']."?msg=5");
		}
	
		// check if user email is valid
	
		elseif (!eregi($regexp, $email_form)) {
			header("location:". $_SERVER['PHP_SELF']."?msg=6");
		}
	
		// everything is ok
		else {
		##[BEGIN personalisation modification]############
		if (!isset($persoStatus) || $persoStatus == "") $persoStatus = "no";
		else  $persoStatus = "yes";
		$userLanguage = $_REQUEST['userLanguage'];
		
		// encrypt the password
		$crypt = new Encryption;
	 	$key = $encryptkey;
	 	$pswdlen = "20";
	 	$password_encrypted = $crypt->encrypt($key, $password_form, $pswdlen);
		$username_form = escapeSimple($username_form);
		if(mysql_query("UPDATE user
	        SET nom='$nom_form', prenom='$prenom_form',
	        username='$username_form', password='$password_encrypted', email='$email_form', am='$am_form',
	            perso='$persoStatus', lang='$userLanguage'
	        WHERE user_id='".$_SESSION["uid"]."'")){
		header("location:". $_SERVER['PHP_SELF']."?msg=1");
	        }
	        
		}
	}
}	// if submit

##[BEGIN personalisation modification - For LDAP users]############
if (isset($submit) && isset($ldap_submit) && ($ldap_submit == "ON")) {

	$userLanguage = $_REQUEST['userLanguage'];
	echo $userLanguage;
	if (!isset($persoStatus) || $persoStatus == "") $persoStatus = "no";
	else  $persoStatus = "yes";
	mysql_query(" UPDATE user SET perso = '$persoStatus', lang = '$userLanguage' WHERE user_id='".$_SESSION["uid"]."' ");
	if (session_is_registered("user_perso_active") && $persoStatus=="no") session_unregister("user_perso_active");
	if ($userLang == "el") {
		$_SESSION['langswitch'] = "greek";
		$_SESSION['langLinkText'] = "English";
		$_SESSION['langLinkURL'] = "?localize=en";
	} else {
		$_SESSION['langswitch'] = "english";
		$_SESSION['langLinkText'] = "Ελληνικά";
		$_SESSION['langLinkURL'] = "?localize=el";
	}

	header("location:". $_SERVER['PHP_SELF']."?msg=1");
}
##[END personalisation modification]############

//Show message if exists
if(isset($msg)) 
{

	switch ($msg){
		case 1: { //logged out
			$message = $langProfileReg;
			$urlText = $langHome;
			$type = "success";
			break;
		}

		case 2: {//logged in
			$message = $langPassTwo;
			$urlText = $langAgain;
			$type = "caution";
			break;
		}

		case 3: { //course home (lesson tools)
			$message = $langPassTooEasy .": <strong>".substr(md5(date("Bis").$_SERVER['REMOTE_ADDR']),0,8)."</strong>";
			$urlText = $langAgain;
			$type = "caution";
			break;
		}

		case 4: { // admin tools
			$message = $langFields;
			$urlText = $langAgain;
			$type = "caution";
			break;
		}

		case 5: {
			$message = $langUserTaken;
			$urlText = $langAgain;
			$type = "caution";
			break;
		}

		case 6: {
			$message = $langEmailWrong;
			$urlText = $langAgain;
			$type = "caution";
			break;
		}
	}

	$tool_content .=  "
			<table>
				<tbody>
					<tr>
						<td class=\"$type\">
						$message<br>
    <a href=\"../../index.php\">$urlText</a>
					</td>
					</tr>
				</tbody>
			</table><br/>";

}
/**************************************************************************************/
// inst_id added by adia for LDAP users
$sqlGetInfoUser ="SELECT nom, prenom, username, password, email, inst_id, am, perso, lang
    FROM user WHERE user_id='".$uid."'";
$result=mysql_query($sqlGetInfoUser);
$myrow = mysql_fetch_array($result);

$nom_form = $myrow['nom'];
$prenom_form = $myrow['prenom'];
$username_form = $myrow['username'];
$password_form = $myrow['password'];
$email_form = $myrow['email'];
$am_form = $myrow['am'];
##[BEGIN personalisation modification, added 'personalisation on SELECT]############
$persoStatus=	$myrow['perso'];
$userLang = $myrow['lang'];

if ($persoStatus == "yes") $checkedPerso = "checked";
else $checkedPerso = "";

if ($userLang == "el") {
	$checkedLangEl = "checked";
	$checkedLangEn = "";
} else {
	$checkedLangEl = "";
	$checkedLangEn = "checked";
}
##[END personalisation modification]############

session_unregister("uname");
session_unregister("pass");
session_unregister("nom");
session_unregister("prenom");

$uname = $username_form;
$pass = $password_form;
$nom = $nom_form;
$prenom = $prenom_form;

session_register("uname");
session_register("pass");
session_register("nom");
session_register("prenom");

##[BEGIN personalisation modification]############IT DOES NOT UPDATE THE DB!!!
if ($persoStatus=="yes" && session_is_registered("perso_is_active")) session_register("user_perso_active");
if ($persoStatus=="no" && session_is_registered("perso_is_active")) session_unregister("user_perso_active");

if ($userLang == "el") {
	$_SESSION['langswitch'] = "greek";
	$_SESSION['langLinkText'] = "English";
	$_SESSION['langLinkURL'] = "?localize=en";
} else {
	$_SESSION['langswitch'] = "english";
	$_SESSION['langLinkText'] = "Ελληνικά";
	$_SESSION['langLinkURL'] = "?localize=el";
}
##[END personalisation modification]############

/*
// if LDAP user - added by adia
if ($myrow['inst_id'] > 0) 	// LDAP user:
{		
	$tool_content .= "
    <form method=\"post\" action=\"$PHP_SELF?submit=yes\">
    <input type=hidden name=\"ldap_submit\" value=\"ON\">

    <table>
    <thead>
    <tr>
        <th>
        $langName
        </th>
        <td>$prenom_form</td>
    </tr>
    <tr>
        <th>
        $langSurname
        </th>
        <td>$nom_form</td>
    </tr>
        <tr>
        <th>
        $langUsername
        </th>
        <td>$username_form</td>
        </tr>
        <tr>
        <th>
        $langEmail
        </th>
        <td>$email_form</td>
        </tr>
        ";

	##[BEGIN personalisation modification]############

	if (session_is_registered("perso_is_active")) 
	{
		$tool_content .= "
        <tr>
            <th>eClass Personalised</th>
            <td>
                <input type=checkbox name='persoStatus' value=\"yes\" $checkedPerso>
            </td>
        </tr>";
	}
	##[END personalisation modification]############
	$tool_content .= "
        <tr>
            <th>$langLanguage</th>
            <td>
                <input type='radio' name='userLanguage' value='el' $checkedLangEl>$langGreek<br>
				<input type='radio' name='userLanguage' value='en'  $checkedLangEn>$langEnglish
            </td>
        </tr>";

	$tool_content .= "
        <tr>
        <td colspan=\"2\" class=\"caution\">$langLDAPUser</td>
        </tr>
        </thead></table><br>

        <input type=\"Submit\" name=\"submit\" value=\"$langChange\">

                </form><br><br>";


} 
else		// Not LDAP user: 
{		
*/
	if (!isset($urlSecure)) 
	{
		$sec = $urlServer.'modules/profile/profile.php';
	} 
	else 
	{
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
    </tr>";
    
    $authmethods = array("imap","pop3","ldap","db");
if(!in_array($password_form,$authmethods))
{
	$tool_content .= "
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
        <td>";
        	$crypt = new Encryption;
					$key = $encryptkey;
					$password_decrypted = $crypt->decrypt($key, $password_form);
					$tool_content .= "<input type=\"password\" size=\"40\" name=\"password_form\" value=\"$password_decrypted\">
					</td>
		</tr>
    <tr>
        <th width=\"150\">
            $langConfirmation
        </th>
        <td>       		
            <input type=\"password\" size=\"40\" name=\"password_form1\" value=\"$password_decrypted\">
        </td>
    </tr>";
}
else		// means that it is external auth method, so the user cannot change this password
{
    switch($password_form)
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
    <th width=\"150\">".$langUsername.
        "</th>
        <td class=\"caution\">".$username_form." &nbsp;&nbsp;[".$auth_text."]
        <input type=\"hidden\" name=\"password_form\" value=\"$password_form\">
        <input type=\"hidden\" name=\"password_form1\" value=\"$password_form\">
        <input type=\"hidden\" name=\"username_form\" value=\"$username_form\">
        </td>
    </tr>";
}
    
    $tool_content .= "<tr>
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
    </tr>";
	##[BEGIN personalisation modification]############
	if (session_is_registered("perso_is_active")) {

		$tool_content .="
                <tr>
                    <th width=\"150\">

                            eClass Personalised

                    </th>
                     <td>
                        <input type=checkbox name='persoStatus' value=\"yes\" $checkedPerso>
                    </td>
                </tr>";
	}
	##[END personalisation modification]############
	$tool_content .= "
        <tr>
            <th>$langLanguage</th>
            <td><input type='radio' name='userLanguage' value='el' $checkedLangEl>$langGreek<br>
				<input type='radio' name='userLanguage' value='en'  $checkedLangEn>$langEnglish
            </td>
        </tr>";
	$tool_content .= "
    </thead></table>
    <br><input type=\"Submit\" name=\"submit\" value=\"$langChange\">
    </form>
    <br><p><a href='../unreguser/unreguser.php'>$langUnregUser</a></p><br>";
//}		// End of LDAP user added by adia
#############################################################
//$tool_content .=  "</td></tr><tr><td><br><hr noshade size=\"1\">";


draw($tool_content, 1);



?>
