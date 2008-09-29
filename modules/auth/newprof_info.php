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


/**===========================================================================
newuser_info.php
* @version $Id$

@authors list: Karatzidis Stratos <kstratos@uom.gr>
Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================
@Description: Display all the available auth methods for user registration

Purpose: TDisplay all the available auth methods for user registration

==============================================================================
*/

include '../../include/baseTheme.php';

// added by jexi - adia
session_register("prof");
$prof=1;
include 'auth.inc.php';

$nameTools = $langProfReg;

$tool_content = "";

$auth = get_auth_active_methods();

if(!empty($auth))
{
	$tool_content .= "
$langAuthenticateVia2


<br>
	<a href=\"newprof.php\">$langRegistration ".get_auth_info(1)."</a>
";
	
	foreach($auth as $k=>$v)
	{
		if($v==1)		// bypass the eclass auth method, as it has already been displayed
		{
			continue;
		}
		else
		{
			$auth_method_settings = get_auth_settings($v);
			
			$tool_content .= "
			    <a href=\"ldapnewprof.php?auth=".$v."\">$langAuthenticateVia ".$auth_method_settings['auth_name']."</a>
	
";

			if(!empty($auth_method_settings))
			{
				$tool_content .= "<br />".$auth_method_settings['auth_instructions'];
			}		
		}
	}
}
else
{
	$tool_content .= "
					<p> $langCannotUseAuthMethods </p>
							
					";
}

$tool_content .= "";
draw($tool_content,0,'auth');

?>
