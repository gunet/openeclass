<?php 
/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Á full copyright notice can be read in "/info/copyright.txt".
        
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
	ldapnewprof.php
	@last update: 31-05-2006 by Karatzidis Stratos
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================        
  @Description: Introductory file that displays a form, requesting 
  from the user/prof to enter the account settings and authenticate
  him/her against the predefined method of the platform

 	
==============================================================================
*/

//LANGFILES, BASETHEME, OTHER INCLUDES AND NAMETOOLS
$langFiles = array('registration', 'admin', 'gunet');
include '../../include/baseTheme.php';
require_once 'auth.inc.php';

$auth = isset($_GET['auth'])?$_GET['auth']:'';

//$auth = get_auth_id();
$msg = get_auth_info($auth);
$settings = get_auth_settings($auth);
if(!empty($msg)) $nameTools = $msg;
$navigation[] = array("url"=>"../admin/", "name"=> $admin);
$navigation[] = array("url"=>"newprof_info.php", "name"=> $regprof);

$tool_content = "";

$tool_content .= $settings['auth_instructions']."<br />
			<form method=\"POST\" action=\"ldapsearch_prof.php\">
				<table>
				<tr><td>Username</td>
					<td><input type=\"text\" name=\"ldap_email\" value=\"".@$m."\"></td>
				</tr>
				<tr><td>Password</td>
				<td><input type=\"password\" name=\"ldap_passwd\" value=\"".@$m."\"></td>
				</tr>
				<tr colspan=2>
					<td><br><input type=\"submit\" name=\"is_submit\" value=\"".$reg."\">
					<input type=\"hidden\" name=\"auth\" value=\"".$auth."\">
					<br /><br />
					</td>
				</tr>
			</table>
		</form><br />";
		
draw($tool_content,0);

?>
