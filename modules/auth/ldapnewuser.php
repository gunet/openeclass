<?php
/**=============================================================================
       	GUnet eClass 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".
        
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

$authmethods = get_auth_active_methods();

$msg = get_auth_info($auth);
$settings = get_auth_settings($auth);
if(!empty($msg)) $nameTools = "$langConfirmUser ($msg)";

@$tool_content .= "
<table width=\"99%\" class='FormData' align='left'>
<thead>
<tr>
<td>			
<form method=\"POST\" action=\"ldapsearch.php\">
  <table width=\"100%\" align='left'>
  <tbody>
  <tr>
    <th class='left' width='25%'>$langAuthUserName</th>
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
		<br/><br/>
    ".$settings['auth_instructions']."
	</td>
  </tr>	
  </tbody>
  </table>
	</form></td></tr></thead></table>";

draw($tool_content,0,'auth');
?>
