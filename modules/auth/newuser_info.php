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
        @Description: Check the available platform authentication methods

 	Purpose: The file checks for the available authentication methods of the platform
 	and displays them for a user to select

==============================================================================
*/

$langFiles = array('registration','gunet','index');
include '../../include/baseTheme.php';
include 'auth.inc.php';
if(isset($already_second)) {
	session_register("uid");
	session_unregister("statut");
	session_unregister("prenom");
	session_unregister("nom");
	session_unregister("uname");
}

$nameTools = $reguser;

$tool_content = "";		// Initialise $tool_content

// Main body

$auth = get_auth_active_methods();
$e = 1;


$tool_content .= "
<table border='0' cellspacing='0' cellpadding='0' align=center width='50%'>
<tr>
	<td colspan='2' style='border: 1px solid silver;' class=color1>".$langSelection."</td>
</tr>
<tr>
	<td style='border-left: 1px solid silver;'>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td style='border-left: 1px solid silver; border-bottom: 1px solid silver;'>&nbsp;</td>
	<td rowspan='2' style='border: 1px solid silver;' onMouseOver='this.style.backgroundColor=\"#F1F1F1\"'; onMouseOut='this.style.backgroundColor=\"transparent\"'><a href=\"newuser.php\">$langAuthReg".get_auth_info($e)."</a></td>
</tr>

";

if(!empty($auth))
{
	foreach($auth as $k=>$v)
	{
		if($v!=1)
		{
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
	<td rowspan='2' style='border: 1px solid silver;' onMouseOver='this.style.backgroundColor=\"#F1F1F1\"'; onMouseOut='this.style.backgroundColor=\"transparent\"'><a href=\"ldapnewuser.php?auth=".$v."\">$langAuthReg".get_auth_info($v)."</a></td>
</tr>

			
";
		}
		else
		{
			continue;
		}
	}
}

$tool_content .= "

<tr>
	<td>&nbsp;</td>
</tr>
</table>

";

draw($tool_content,0,'auth');
?>
