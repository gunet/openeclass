<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*                       Yannis Exidaridis <jexi@noc.uoa.gr>
*                       Alexandros Diamantidis <adia@noc.uoa.gr>
*                       Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address:     GUnet Asynchronous eLearning Group,
*                       Network Operations Center, University of Athens,
*                       Panepistimiopolis Ilissia, 15784, Athens, Greece
*                       eMail: info@openeclass.org
* =========================================================================*/

/*===========================================================================
	ldapnewuser.php
* @version $Id$
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================
  @Description: Introductory file that displays a form, requesting
  from the user/prof to enter the account settings and authenticate
  him/her against the predefined method of the platform


==============================================================================
*/

include '../../include/baseTheme.php';
include 'auth.inc.php';

$navigation[]= array ("url"=>"registration.php", "name"=> "$langNewUser");

// Initialise $tool_content
$tool_content = "";

if (isset($_REQUEST['auth'])) {
	$auth = intval($_REQUEST['auth']);
	$_SESSION['u_tmp'] = $auth;
}
if(!isset($_REQUEST['auth'])) {
	$auth = 0;
	$auth = $_SESSION['u_tmp'];
}

$authmethods = get_auth_active_methods();

$msg = get_auth_info($auth);
$settings = get_auth_settings($auth);
if(!empty($msg)) $nameTools = "$langConfirmUser ($msg)";

if (isset($_GET['p']) and ($_GET['p'] == true)) {
	$tool_content .= "<form method='post' action='ldapsearch_prof.php'>";
} else {
	$tool_content .= "<form method='post' action='ldapsearch.php'>";
}
@$tool_content .= "
<fieldset>
<legend>".$settings['auth_instructions']."</legend>
  <table class='tbl'>
  <tr>
    <td>$langAuthUserName</td>
    <td><input type='text' name='ldap_email' value='$ldap_email'></td>
  </tr>
  <tr>
     <td>$langAuthPassword</td>
     <td><input type='password' name='ldap_passwd' value='$ldap_passwd'></td>
  </tr>
  <tr>
     <td>&nbsp;</td>
     <td>
       <input type='hidden' name='auth' value='".$auth."'>
       <input type='submit' name='is_submit' value='".$langSubmit."'>
     </td>
  </tr>
  </table>
</form>";

draw($tool_content, 0);
?>
