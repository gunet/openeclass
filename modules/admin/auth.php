<?php
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

/*===========================================================================
	auth.php
	@last update: 27-06-2006 by Stratos Karatzidis
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================
	@Description: Platform Authentication Methods and their settings

 	This script displays the alternative methods of authentication
	and their settings.

 	The admin can: - choose a method and define its settings

==============================================================================
*/

$require_admin = TRUE;
include '../../include/baseTheme.php';
include_once '../auth/auth.inc.php';
$nameTools = $langUserAuthentication;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
$tool_content = "";

$auth = isset($_GET['auth'])?$_GET['auth']:"";
$active = isset($_GET['active'])?$_GET['active']:"";

if((!empty($auth)) && (!empty($active))) {
	$s = get_auth_settings($auth);
	$settings = $s['auth_settings'];

	switch($active) {
		case 'yes': $q = empty($settings)?'0':'1';
		break;
		case 'no': $q = '0';
		break;
		default: $q = '0';
		break;
	}
	$qry = "UPDATE auth SET auth_default=".$q." WHERE auth_id='".mysql_real_escape_string($auth)."'";
	if(!empty($qry)) {
		$sql = mysql_query($qry,$db); // do the update as the default method
	}
}
$auth_methods = get_auth_active_methods();


if(empty($auth)) {
	$tool_content .= '<p>' . $langMethods . '</p>';
	if(!empty($auth_methods)) {
		$tool_content .= "<ul>";
		foreach($auth_methods as $k=>$v) {
			$tool_content .= "<li>".get_auth_info($v) . "</li>";
		}
		$tool_content .= "</ul>";
	}
} else {
	if(empty($settings)) {
		$tool_content .= "<p class=\"success\">";
		$tool_content .= "$langErrActiv $langActFailure";
		$tool_content .= "</p>";
	} else {
		if($active == 'yes') {
			$tool_content .= "<p class=\"success\">";
			$tool_content .= "$langActSuccess" . get_auth_info($auth);
			$tool_content .= "</p>";
		} else {
			$tool_content .= "<p class=\"success\">";
			$tool_content .= "$langDeactSuccess" . get_auth_info($auth);
			$tool_content .= "</p>";
		}
	}
}

$tool_content .= "<table width='100%' class='tbl_alt'>
<tr>
<th colspan='3'>$langChooseAuthMethod</th>
</tr><tr width='50'><td>POP3:</td><td>[";

$tool_content .= in_array("2",$auth_methods)? "<a class=\"add\" href=\"auth.php?auth=2&amp;active=no\">".$langDeactivate."</a>]":"<a class=\"revoke\"  href=\"auth.php?auth=2&amp;active=yes\">".$langActivate."</a>]";

$tool_content .= "</td><td><div align=\"right\">";

$tool_content .= "&nbsp;&nbsp;<a href=\"auth_process.php?auth=2\">$langAuthSettings</a>";
$tool_content .= "</div></td></tr>
<tr><td>IMAP:</td><td>[";

$tool_content .= in_array("3",$auth_methods)? "<a class=\"add\" href=\"auth.php?auth=3&amp;active=no\">".$langDeactivate."</a>]":"<a class=\"revoke\" href=\"auth.php?auth=3&amp;active=yes\">".$langActivate."</a>]";
$tool_content .= "</td><td><div align=\"right\">";

$tool_content .= "&nbsp;&nbsp;<a href=\"auth_process.php?auth=3\">$langAuthSettings</a>";
$tool_content .= "</div></td></tr><tr><td>LDAP:</td><td>[";

$tool_content .= in_array("4",$auth_methods)? "<a class=\"add\" href=\"auth.php?auth=4&amp;active=no\">".$langDeactivate."</a>]":"<a class=\"revoke\" href=\"auth.php?auth=4&amp;active=yes\">".$langActivate."</a>]";
$tool_content .= "</td><td><div align=\"right\">";

$tool_content .= "&nbsp;&nbsp;<a href=\"auth_process.php?auth=4\">$langAuthSettings</a>";	
$tool_content .= "</div></td></tr><tr><td>External DB:</td><td>[";

$tool_content .= in_array("5",$auth_methods)? "<a class=\"add\" href=\"auth.php?auth=5&amp;active=no\">".$langDeactivate."</a>]":"<a class=\"revoke\" href=\"auth.php?auth=5&amp;active=yes\">".$langActivate."</a>]";
$tool_content .= "</td><td><div align=\"right\">";

$tool_content .= "<a href=\"auth_process.php?auth=5\">$langAuthSettings</a>";

$tool_content .= "</div></td></tr><tr><td>Shibboleth:</td><td>[";

$tool_content .= in_array("6",$auth_methods)? "<a class=\"add\" href=\"auth.php?auth=6&amp;active=no\">".$langDeactivate."</a>]":"<a class=\"revoke\" href=\"auth.php?auth=6&amp;active=yes\">".$langActivate."</a>]";
$tool_content .= "</td><td><div align=\"right\">";

$tool_content .= "<a href=\"auth_process.php?auth=6\">$langAuthSettings</a>";
$tool_content .= "</div></td></tr><tr><td>CAS:</td><td>[";

$tool_content .= in_array("7",$auth_methods)? "<a class=\"add\" href=\"auth.php?auth=7&amp;active=no\">".$langDeactivate."</a>]":"<a class=\"revoke\" href=\"auth.php?auth=7&amp;active=yes\">".$langActivate."</a>]";
$tool_content .= "</td><td><div align=\"right\">";

$tool_content .= "<a href=\"auth_process.php?auth=7\">$langAuthSettings</a>";	
$tool_content .= "</div></td></tr></tbody></table><br />";

draw($tool_content, 3);
?>
