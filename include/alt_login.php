<?
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

if (!defined('INDEX_START')) {
	die ("Action not allowed!");
}

// authenticate user via alternate defined methods 
switch($myrow["password"])
{
	case 'eclass': $auth = 1; break;
	case 'pop3': $auth = 2; break;
	case 'imap': $auth = 3; break;
	case 'ldap': $auth = 4; break;
	case 'db': $auth = 5; break;
	default: break;
}
$auth_method_settings = get_auth_settings($auth);
if($myrow['password'] == $auth_method_settings['auth_name']) {
	switch($auth) {
		case '2': $pop3host = str_replace("pop3host=","",$auth_method_settings['auth_settings']);
		break;
		case '3': $imaphost = str_replace("imaphost=","",$auth_method_settings['auth_settings']);
		break;
		case 4: $ldapsettings = $auth_method_settings['auth_settings'];
		$ldap = explode("|",$ldapsettings);
		$ldaphost = str_replace("ldaphost=","",$ldap[0]); //ldaphost
		$ldapbind_dn = str_replace("ldapbind_dn=","",$ldap[1]); //ldapbase_dn
		$ldapbind_user = str_replace("ldapbind_user=","",$ldap[2]); //ldapbind_user
		$ldapbind_pw = str_replace("ldapbind_pw=","",$ldap[3]); // ldapbind_pw
		break;
		case 5: $dbsettings = $auth_method_settings['auth_settings'];
		$edb = explode("|",$dbsettings);
		$dbhost = str_replace("dbhost=","",$edb[0]); //dbhost
		$dbname = str_replace("dbname=","",$edb[1]); //dbname
		$dbuser = str_replace("dbuser=","",$edb[2]); //dbuser
		$dbpass = str_replace("dbpass=","",$edb[3]); // dbpass
		$dbtable = str_replace("dbtable=","",$edb[4]); //dbtable
		$dbfielduser = str_replace("dbfielduser=","",$edb[5]);//dbfielduser
		$dbfieldpass = str_replace("dbfieldpass=","",$edb[6]);//dbfieldpass
		break;
		default:
		break;
	}
	$is_valid = auth_user_login($auth,$uname,$pass);
	if($is_valid) {
		$is_active = check_activity($myrow["user_id"]);
		if($myrow["user_id"] == 1) // always the admin is active
		{
			$is_active = 1;
		}
		if(!empty($is_active))
		{
			$auth_allow = 1;
		}
		else
		{
			$auth_allow = 3;
			$user = $myrow["user_id"];
		}
	} else {
		$auth_allow = 2;
	}
	if ($auth_allow == 1) {
		$uid = $myrow["user_id"];
		$nom = $myrow["nom"];
		$prenom = $myrow["prenom"];
		$statut = $myrow["statut"];
		$email = $myrow["email"];
		$userPerso = $myrow["perso"];
		$language = $_SESSION['langswitch'] = langcode_to_name($myrow["lang"]);
	}
	elseif($auth_allow == 2)
	{
		continue;
	}
	elseif($auth_allow == 3)
	{
		continue;
	}
	else
	{
		$tool_content .= $langLoginFatalError."<br />";
		continue;
	}
} else {
	$warning .= "<br>$langInvalidAuth<br>";
}
?>
