<?php
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
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

// for security
$auth = isset($_GET['auth'])?intval($_GET['auth']):0;

if (isset($_GET['auth']) or isset($_POST['auth']))
$_SESSION['u_tmp']=$auth;
if(!isset($_GET['auth']) or !isset($_POST['auth']))
$auth=$_SESSION['u_tmp'];

$authmethods = get_auth_active_methods();

$msg = get_auth_info($auth);
$settings = get_auth_settings($auth);
if(!empty($msg)) $nameTools = "$langConfirmUser ($msg)";

@$tool_content .= "
<form method=\"POST\" action=\"ldapsearch.php\">
<table width=\"99%\" style=\"border: 1px solid #edecdf;\">
<thead>
<tr>
  <td>

  <table width=\"99%\" class='FormData' align='left'>
  <thead>
  <thead>
  <tr>
    <th class='left' width='220'>$langAuthUserName</th>
    <td><input type='text' name='ldap_email' value='$ldap_email' class='FormData_InputText'></td>
  </tr>
  <tr>
    <th class='left'>$langAuthPassword</th>
    <td><input type='password' name='ldap_passwd' value='$ldap_passwd' class='FormData_InputText'></td>
  </tr>
  <tr>
    <th>&nbsp;</th>
    <td>
    <input type=\"hidden\" name=\"auth\" value=\"".$auth."\">
    <input type=\"submit\" name=\"is_submit\" value=\"".$langSubmit."\">
	</td>
  </tr>
  </thead>
  </table>
     <div align=\"right\"><small>".$settings['auth_instructions']."</small></div>
  </td>
</tr>
</thead>
</table>
</form>";

draw($tool_content,0,'auth');
?>
