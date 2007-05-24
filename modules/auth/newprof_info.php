<?php
/**=============================================================================
GUnet e-Class 2.0
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
newuser_info.php
* @version $Id$

@authors list: Karatzidis Stratos <kstratos@uom.gr>
Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================
@Description: Display all the available auth methods for user registration

Purpose: TDisplay all the available auth methods for user registration

==============================================================================
*/

$langFiles = array('registration', 'admin', 'gunet');
include '../../include/baseTheme.php';

// added by jexi - adia
session_register("prof");
$prof=1;
include 'auth.inc.php';

$nameTools = $regprof;

$tool_content = "";

$auth = get_auth_active_methods();

if(!empty($auth))
{
	$tool_content .= "
<table border='0' cellspacing='0' cellpadding='0' align=center width='70%'>
<tr>
	<td colspan='2' style='border: 1px solid silver;' class=color1>".$langAuthenticateVia2."</td>
</tr>
<tr>
	<td style='border-left: 1px solid silver;'>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td style='border-left: 1px solid silver; border-bottom: 1px solid silver;'>&nbsp;</td>
	<td rowspan='2' style='border: 1px solid silver;' onMouseOver='this.style.backgroundColor=\"#F1F1F1\"'; onMouseOut='this.style.backgroundColor=\"transparent\"'><a href=\"newprof.php\">".$regprofnoldap."</a></td>
</tr>
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
			<tr>
	<td style='border-left: 1px solid silver;'>&nbsp;</td>
</tr>
<tr>
	<td style='border-left: 1px solid silver;'>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td style='border-left: 1px solid silver; border-bottom: 1px solid silver;'>&nbsp;</td>
	<td rowspan='2' style='border: 1px solid silver;' onMouseOver='this.style.backgroundColor=\"#F1F1F1\"'; onMouseOut='this.style.backgroundColor=\"transparent\"'>
    <a href=\"ldapnewprof.php?auth=".$v."\">$langAuthenticateVia ".$auth_method_settings['auth_name']."</a>
	</td>
</tr>	
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
	$tool_content .= "<table width=\"99%\">
			<tbody>
				<tr>
					<td class=\"caution\">
					<p>Η εγγραφή στην πλατφόρμα, πρός το παρόν δεν επιτρέπεται.</p>
							<p>Παρακαλούμε, ενημερώστε το διαχειριστή του συστήματος</p>
					</td>
				</tr>
			</tbody>
		</table>";
}

$tool_content .= "<tr><td>&nbsp;</td></tr></table>";
draw($tool_content,0,'auth');

?>
