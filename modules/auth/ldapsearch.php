<? 
/*
      +----------------------------------------------------------------------+
      | e-class version 1.0                                                  |
      | based on CLAROLINE version 1.3.0 $Revision$		     |
      +----------------------------------------------------------------------+
      |   $Id$
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      | Copyright (c) 2003 GUNet                                             |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      |                                                                      |
      |   This program is distributed in the hope that it will be useful,    |
      |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
      |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
      |   GNU General Public License for more details.                       |
      |                                                                      |
      |   You should have received a copy of the GNU General Public License  |
      |   along with this program; if not, write to the Free Software        |
      |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
      |   02111-1307, USA. The GNU GPL license is also available through     |
      |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesche <gesche@ipm.ucl.ac.be>                    |
      |                                                                      |
      | e-class changes by: Costas Tsibanis <costas@noc.uoa.gr>              |
      |                     Yannis Exidaridis <jexi@noc.uoa.gr>              |
      |                     Alexandros Diamantidis <adia@noc.uoa.gr>         |
      +----------------------------------------------------------------------+
 */
$langFiles = array('registration','gunet');
include '../../include/baseTheme.php';
include 'auth.inc.php';
$nameTools = $langLDAPUser;
$navigation[]= array ("url"=>"newuser_info.php", "name"=> "$reguser");

// Initialise $tool_content
$tool_content = "";
// Main body

$found = 0;

// get the values from ldapnewuser.php
$ldap_email = isset($_POST['ldap_email'])?$_POST['ldap_email']:'';
$ldap_passwd = isset($_POST['ldap_passwd'])?$_POST['ldap_passwd']:'';
$is_submit = isset($_POST['is_submit'])?$_POST['is_submit']:'';

if (!isset($userMailCanBeEmpty)) 
{	
	$userMailCanBeEmpty = true;
} 

// change behaviour depending on $prof. (added by adia)
if (isset($prof) and $prof == 1) 
{
	// creating prof
	$lastpage = 'ldapnewprof.php';
	$userdescr = $langTheTeacher;
} 
else 
{
  // creating user
	$lastpage = 'ldapnewuser.php';
	$userdescr = $langTheUser;
}
//$tool_content .= "here...<br>";
$errormessage1 = "<tr valign=\"top\" align=\"center\" bgcolor=\"$color2\"><td><font size=\"2\" face=\"arial, helvetica\"><p>&nbsp;</p>";
$errormessage3 = "</font><p>&nbsp;</p><br><br><br></td></tr>";
$errormessage2 = "<p>$ldapback <a href=\"$lastpage\">$ldaplastpage</a></p>$errormessage3";
//if(($is_submit) && (!empty($auth_method_settings)))
if($is_submit)
{
	$regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,4})$";
	@$emailtohostname = substr( $email, ( strrpos( $email, "@" ) +1 ));
	$emailtohostname = substr( $ldap_email, ( strrpos( $ldap_email, "@" ) +1 ));
	//$tool_content .= "here...<br>";
	if (empty($ldap_email) or empty($ldap_passwd)) // check for empty username-password
	{
		$tool_content .= $errormessage1 . $ldapempty . $errormessage2;
	} 
	elseif (user_exists($ldap_email)) // check if the user already exists
	{
		$tool_content .= $errormessage1 . $ldapuserexists . $errormessage2;
	} 
	else 
	{
		// try to authenticate him
		$auth = get_auth_id();
		$auth_method_settings = get_auth_settings($auth);		// get the db settings of the authentication method defined
		switch($auth)			// now get the connection settings
		{
			case '2':	$pop3host = str_replace("pop3host=","",$auth_method_settings['auth_settings']);
							break;
			case '3':	$imaphost = str_replace("imaphost=","",$auth_method_settings['auth_settings']);
							break;
			case '4':	$ldapsettings = $auth_method_settings['auth_settings'];
					    $ldap = explode("|",$ldapsettings);
					    $ldaphost = str_replace("ldaphost=","",$ldap[0]);	//ldaphost
					    $ldapbind_dn = str_replace("ldapbind_dn=","",$ldap[1]);	//ldapbase_dn
					    $ldapbind_user = str_replace("ldapbind_user=","",$ldap[2]);	//ldapbind_user
					    $ldapbind_pw = str_replace("ldapbind_pw=","",$ldap[3]);		// ldapbind_pw
							break;
			case '5':	$dbsettings = $auth_method_settings['auth_settings'];
    					$edb = explode("|",$dbsettings);
    					$dbhost = str_replace("dbhost=","",$edb[0]);	//dbhost
    					$dbname = str_replace("dbname=","",$edb[1]);	//dbname
    					$dbuser = str_replace("dbuser=","",$edb[2]);//dbuser
    					$dbpass = str_replace("dbpass=","",$edb[3]);// dbpass
					    $dbtable = str_replace("dbtable=","",$edb[4]);//dbtable
					    $dbfielduser = str_replace("dbfielduser=","",$edb[5]);//dbfielduser
					    $dbfieldpass = str_replace("dbfieldpass=","",$edb[6]);//dbfieldpass
							break;
			default:
							break;
		}
		
		$is_valid = auth_user_login($auth,$ldap_email,$ldap_passwd);
		//$tool_content .= "is_valid: ".$is_valid."<br />";
		if($is_valid)
		{
			//$tool_content .= "<br />Successfully connected<br />";
			$auth_allow = 1;
		}
		else
		{
			$tool_content .= "<br />The connection does not seem to work!!<br />";
			$auth_allow = 0;
		}	
		if($auth_allow==1)
		{	
			//switch($auth)
			//{
			//	case '2':
			//			$tool_content .= "IT IS POP3<br />";
			//			break;
			//	case '3':
						//$tool_content .= "IT IS IMAP<br />";
						//$tool_content .= "NOW need to provide him with a form to fill his name,department e.t.c.<br />";
						$tool_content .= "<table><tr>
<td width=\"600\">
<form action=\"newuser_second.php\" method=\"post\">
<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" width=\"100%\" bgcolor=\"".$color2."\">
<tr valign=\"top\">
<td>".$langName."</td>
<td><input type=\"text\" name=\"prenom_form\"><font size=\"1\">&nbsp;(*)</font></td>
</tr>
<tr><td>".$langSurname."</td>
<td><input type=\"text\" name=\"nom_form\"><font size=\"1\">&nbsp;(*)</font></td>
</tr>
<tr>
<td>".$langEmail."</td>
<td><input type=\"text\" name=\"email\"></td>
</tr>
<tr><td>&nbsp;</td><td><font size=\"1\">".$langEmailNotice."</font></td></tr>
<tr><td>".$langAm."</td>
<td><input type=\"text\" name=\"am\"></td>
</tr>
<tr><td>".$langDepartment."</td>
<td>
<select name=\"department\">";
$deps=mysql_query("SELECT name, id FROM faculte ORDER BY id",$db);
while ($dep = mysql_fetch_array($deps)) 
		$tool_content .= "\n<option value=\"$dep[1]\">$dep[0]</option>";
$tool_content .= "</select></td></tr>
<tr><td>&nbsp;</td><td><input type=\"submit\" name=\"submit\" value=\"".$langRegistration."\"></td></tr>
<input type=\"hidden\" name=\"uname\" value=\"".$ldap_email."\">
<input type=\"hidden\" name=\"password\" value=\"".$ldap_passwd."\">
<input type=\"hidden\" name=\"auth\" value=\"".$auth."\">
</table>
</form>
</td>
</tr>
<tr><td  align='right'><font size=\"1\">".$langRequiredFields."</font>
</td></tr></table>";
//						break;
	//			default:
		//				break;
			//}
		
		}
		else
		{
			$tool_content .= "<br />NO VALID USER in the auth method.Cannot register him<br />";
		}
	}
		
	/*
	$regexp = "^[0-9a-z_\.-]+@(([0-9]{1,3}\.){3}[0-9]{1,3}|([0-9a-z][0-9a-z-]*[0-9a-z]\.)+[a-z]{2,4})$";
	@$emailtohostname = substr( $email, ( strrpos( $email, "@" ) +1 ));
	$emailtohostname = substr( $ldap_email, ( strrpos( $ldap_email, "@" ) +1 ));
	if ($ldap_server == '0') 
	{
		$tool_content .= $errormessage1 . $ldapchoice . $errormessage2;
	} 
	elseif (empty($ldap_email) or empty($ldap_passwd)) 
	{
		$tool_content .= $errormessage1 . $ldapempty . $errormessage2;
	} 
	elseif (user_exists($ldap_email)) 
	{
		$tool_content .= $errormessage1 . $ldapuserexists . $errormessage2;
	} 
	else 
	{
		$list = explode("_",$ldap_server);	
		$ldapServer = $list[0];
		$basedn = $list[1];
		$institut_id = $list[2];
	
		#########- tsipa -  ##############################
		$ds=ldap_connect($ldapServer);  //get the ldapServer, baseDN from the db
		if ($ds) 
		{ 
	  	$r=@ldap_bind($ds);     // this is an "anonymous" bind, typically read-only access
			if ($r) 
			{
	    	$mailadd=ldap_search($ds, $basedn, "mail=".$ldap_email);  
	    	$info = ldap_get_entries($ds, $mailadd);
				if ($info["count"] == 0) 	//Den vre8hke eggrafh
				{ 
	    		$tool_content .= "$errormessage1 $ldapnorecords $errormessage2";
					exit;
				}
				else if ($info["count"] == 1) 	// user found
				{ 
					$authbind=@ldap_bind($ds,$info[0]["dn"],$ldap_passwd);
					if ($authbind) 
					{
						$tool_content .= "$errormessage1 ${userdescr} $ldapfound";
						$cn = explode(" ", $info[0]["cn"][0]);
						$nomvar = $cn[0];
						$prenomvar = $cn[1];
						$emailvar = $info[0]["mail"][0];
						$passwordvar = $ldap_passwd;
						$found = 1;	
					} 
					else 
					{
						$tool_content .= $errormessage1.$ldapwrongpasswd.$errormessage2."
						      </table>";
					} 
				} // end of user found
			 	else 
			 	{ 
	    		$tool_content .= $errormessage1. $ldapproblem. $userdescr.
					      $ldapcontact. $errormessage2;
					exit;  
				}
				if ($info["count"] == 1) 
				{
					$tool_content .= "<tr><td>
						<form name=\"registration\" action=\"newuser_second.php\" method=\"post\">
						<input type=\"hidden\" name=\"institut\" value=\"".$institut_id."\">
						<input type=\"hidden\" name=\"nom_form\" value=\"".$nomvar."\">
						<input type=\"hidden\" name=\"prenom_form\" value=\"".$prenomvar."\">
						<input type=\"hidden\" name=\"email\" value=\"".$emailvar."\">
						<table cellpadding=\"3\" cellspacing=\"0\" border=\"0\" width=\"100%\">
						<tr valign=\"top\" bgcolor=\"".$color2."\">
						<td>".$ldapnamesur.":</td><td>".$nomvar $prenomvar."</td>
						</tr>
						<tr bgcolor=\"".$color2."\">
						<td>".$langEmail.":</td><td>".$info[0]["mail"][0]."</td>
						</tr>
						<tr bgcolor=\"".$color2."\">
						<td>&nbsp;</td><td><input type=\"submit\" name=\"submit\" value=\"".$langOk."\"></td>
						</tr></table>                        
						</form>
						</td></tr>";
				} // end of if info count
			}  // end of bind if
	    ldap_close($ds);
			$tool_content .= "<tr valign=\"top\" align=\"center\" bgcolor=\"$color2\"><td><br><h4>$ldaperror</h4>";
	    $tool_content .= "<h4>$ldapcontact</h4>";
	    $tool_content .= "<a href=\"../../index.php\">$back</a>";
	    $tool_content .= "<br><br></td></tr>";
		} 
		else 
		{
			$tool_content .= "<tr valign=\"top\" align=\"center\" bgcolor=\"$color2\"><td><br><h4>$ldaperror</h4>";
	    $tool_content .= "<h4>$ldapcontact</h4>";
	    $tool_content .= "<a href=\"../../index.php\">$back</a>";
	    $tool_content .= "<br><br></td></tr>";
		}
	}
	*/

}   // end of initial if

// Check if a user with usename $login already exists
function user_exists($login) 
{
	global $mysqlMainDb;
	$username_check = mysql_query("SELECT username FROM `$mysqlMainDb`.user WHERE username='$login'");
	if (mysql_num_rows($username_check) > 0) 
	{
		return TRUE;
	} 
	else 
	{
		return FALSE;
	}
}
/*
// get the db settings of the authentication method defined for this institution
function get_auth_settings()
{
	$qry = "SELECT * FROM auth WHERE auth_default = 1";
	$res = db_query($qry);
	if($res)
	{
		$row = mysql_fetch_array($res,MYSQL_ASSOC);
		if($row['auth_name']=='eclass')
		$methods = 1;
		else
		$methods = 2;
		$auth_method_settings = array();
		if(($methods==2) && (mysql_num_rows($res)==1))
		{
			return $row;
		}
		elseif($methods=1)
		{
			return 0;
		}
		else
		{
			return 0;
		}
	}
	else
	{
		return 0;
	}
}
*/

$tool_content .= "</table>";
$tool_content .= "<br />";

draw($tool_content,1);
?>