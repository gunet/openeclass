<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*						Yannis Exidaridis <jexi@noc.uoa.gr>
*						Alexandros Diamantidis <adia@noc.uoa.gr>
*						Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  						Network Operations Center, University of Athens,
*  						Panepistimiopolis Ilissia, 15784, Athens, Greece
*  						eMail: info@openeclass.org
* =========================================================================*/

/*===========================================================================
	auth_process.php
	@last update: 27-06-2006 by Stratos Karatzidis
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================
        @Description: Platform Authentication Methods and their settings

 	This script tries to get the values of an authentication method, establish
 	a connectiond and with a test account successfully connect to the server.
 	Possible scenarios:
 	- The settings of the method are fine and the mechanism authenticates the
 	test account
 	- The settings of the method are fine, but the method does not work
 	with the test account
 	- The settings are wrong.

 	The admin can: - choose a method and define its settings

==============================================================================
*/

$require_admin = TRUE;
include '../../include/baseTheme.php';
include_once '../auth/auth.inc.php';
$nameTools = $langAuthSettings;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
$navigation[] = array("url" => "auth.php", "name" => $langUserAuthentication);
$tool_content = "";

// get the values
$step = isset($_POST['step'])?$_POST['step']:'';
if((!empty($step)) && ($step=='1')) {
	$auth = isset($_POST['auth'])?$_POST['auth']:'';
	$auth_submit = isset($_POST['auth_submit'])?$_POST['auth_submit']:'';
} else {
	$auth = isset($_GET['auth'])?$_GET['auth']:'';
}
$test_username = isset($_POST['test_username'])?$_POST['test_username']:'';
$test_password = isset($_POST['test_password'])?$_POST['test_password']:'';

if((!empty($auth_submit)) && ($auth_submit==1)) {
	$submit = isset($_POST['submit'])?$_POST['submit']:'';
	// if form is submitted
	if((array_key_exists('submit', $_POST)) && (!empty($submit))) {
		$tool_content .= "<br /><p>$langConnTest</p>";
		if ($auth == 6) {
			$test_username = $test_password = " ";
		}
		if((!empty($test_username)) && (!empty($test_password))) {
			$is_valid = auth_user_login($auth, $test_username, $test_password);
			if($is_valid) {
				$auth_allow = 1;
				$tool_content .= "<table width=\"99%\"><tbody><tr>
				<td class=\"success\">$langConnYes</td></tr></tbody></table><br /><br />";
			} else {
				$tool_content .= "<table width=\"99%\"><tbody><tr><td class=\"caution\">$langConnNo";
				if (isset($GLOBALS['auth_errors'])) {
					$tool_content .= "<p>$GLOBALS[auth_errors]</p>";
				}
				$tool_content .= "</td></tr></tbody></table><br /><br />";
				$auth_allow = 0;
			}
		} else {
			$tool_content .= "<table width=\"99%\"><tbody><tr>
			<td class=\"caution\">$langWrongAuth</td></tr></tbody></table><br /><br />";
			$auth_allow = 0;
		}

		// store the values - do the updates //
		if((!empty($auth_allow))&&($auth_allow==1)) {
			switch($auth) {
				case '1': $auth_default = 1;
					$auth_settings = "";
					$auth_instructions = "";
					break;
				case '2': $pop3host = isset($_POST['pop3host'])?$_POST['pop3host']:'';
					$auth_default = 2;
					$auth_settings = "pop3host=".$pop3host;
					$auth_instructions = isset($_POST['pop3instructions'])?$_POST['pop3instructions']:'';
					break;
				case '3': $imaphost = isset($_POST['imaphost'])?$_POST['imaphost']:'';
					$auth_default = 3;
					$auth_settings = "imaphost=".$imaphost;
					$auth_instructions = isset($_POST['imapinstructions'])?$_POST['imapinstructions']:'';
					break;
				case '4': $ldaphost = isset($_POST['ldaphost'])?$_POST['ldaphost']:'';
					$ldapbase_dn = isset($_POST['ldapbase_dn'])?$_POST['ldapbase_dn']:'';
					$ldapbind_user = isset($_POST['ldapbind_user'])?$_POST['ldapbind_user']:'';
					$ldapbind_pw = isset($_POST['ldapbind_pw'])?$_POST['ldapbind_pw']:'';
					$auth_default = 4;
					$auth_settings = "ldaphost=".$ldaphost."|ldapbind_dn=".$ldapbind_dn."|ldapbind_user=".$ldapbind_user."|ldapbind_pw=".$ldapbind_pw;
					$auth_instructions = isset($_POST['ldapinstructions'])?$_POST['ldapinstructions']:'';
					break;
				case '5': $dbhost = isset($_POST['dbhost'])?$_POST['dbhost']:'';
					$dbtype = isset($_POST['dbtype'])?$_POST['dbtype']:'';
					$dbname = isset($_POST['dbname'])?$_POST['dbname']:'';
					$dbuser = isset($_POST['dbuser'])?$_POST['dbuser']:'';
					$dbpass = isset($_POST['dbpass'])?$_POST['dbpass']:'';
					$dbtable = isset($_POST['dbtable'])?$_POST['dbtable']:'';
					$dbfielduser = isset($_POST['dbfielduser'])?$_POST['dbfielduser']:'';
					$dbfieldpass = isset($_POST['dbfieldpass'])?$_POST['dbfieldpass']:'';
					$auth_default = 5;
					$auth_settings = "dbhost=".$dbhost."|dbname=".$dbname."|dbuser=".$dbuser."|dbpass=".$dbpass."|dbtable=".$dbtable."|dbfielduser=".$dbfielduser."|dbfieldpass=".$dbfieldpass;
					$auth_instructions = isset($_POST['dbinstructions'])?$_POST['dbinstructions']:'';;
					break;
				case '6': $auth_instructions = isset($_POST['shibinstructions'])?$_POST['shibinstructions']:'';;
					if (isset($checkseparator) && $checkseparator == "on") {
						$auth_settings = $_POST['shibseparator'];
					} else {
						$auth_settings = 'shibboleth';
					}
					break;
				default:
					break;
			}

			$qry = "UPDATE auth SET auth_settings='".$auth_settings."',
				auth_instructions='".$auth_instructions."',auth_default=1 
				WHERE auth_id=".$auth;
			$sql2 = mysql_query($qry,$db); // do the update as the default method
			if($sql2) {
				if(mysql_affected_rows($db)==1) {
					$tool_content .= "<p class=\"alert1\">$langHasActivate</p>";
				} else {
					$tool_content .= "<p class=\"alert1\">$langAlreadyActiv</p>";
				}
			} else {
				$tool_content .= "<p class=\"alert1\">$langErrActiv</p>";
			}
		}
	}
}
else
{
	// Display the form 
	if(isset($auth) and $auth != 6) {
		$auth_data = get_auth_settings($auth);
	}
	$tool_content .= " <table width='99%' class='FormData' align='left'><tbody><tr>
	<th width='220'>
	<form name=\"authmenu\" method=\"post\" action=\"auth_process.php\">
	<input type=\"hidden\" name=\"auth_submit\" value=\"1\" />
	<input type=\"hidden\" name=\"auth\" value=\"".htmlspecialchars($auth)."\" />
	<input type=\"hidden\" name=\"step\" value=\"1\" />
	</th>
	<td><b>".get_auth_info($auth)."</b></td>
	</tr>";
	
	switch($auth) {
		case 2: include_once '../auth/methods/pop3form.php';
			break;
		case 3: include_once '../auth/methods/imapform.php';
			break;
		case 4: include_once '../auth/methods/ldapform.php';
			break;
		case 5: include_once '../auth/methods/db/dbform.php';
			break;
		case 6: include_once '../auth/methods/shibform.php';
			break;
		default:
			break;
	}

	if ($auth != 6) { 
		$tool_content .= "<tr><td colspan='2'>&nbsp;</td></tr>";
		$tool_content .= "<tr><th>&nbsp;</th><td>$langTestAccount</td></tr>
		<tr><th class='left'>$langUsername: </th>
		<td><input size='30' class='FormData_InputText' type='text' name='test_username' value='".$test_username."'></td></tr>
		<tr><th class='left'>$langPass: </th>
		<td><input size='30' class='FormData_InputText' type='password' name='test_password' value='".$test_password."'></td></tr>";
	}
	$tool_content .= "<tr><th>&nbsp;</th><td><input type='submit' name='submit' value='$langModify'></form></td></tr>";
	$tool_content .="<br /></table>";
}

draw($tool_content,3,'admin');
?>
