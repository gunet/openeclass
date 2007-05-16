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

$langFiles = 'index';
include '../../include/baseTheme.php';

//mysql_select_db($dbname);

$nameTools = $langReg;

$tool_content = "";
$tool_content .= "
<table border='0' cellspacing='0' cellpadding='0' width='70%' align='center'>
<tr>
	<td width='100%' colspan='4' align='center' class='color1' style='border: 1px solid silver;'>".$langSelection."</td>
</tr>
<tr>
	<td width='25%'>&nbsp;</td>
	<td width='25%' style='border-right: 1px solid silver; border-bottom: 1px solid silver;'>&nbsp;</td>
	<td width='25%' style='border-bottom: 1px solid silver;'>&nbsp;</td>
	<td width='25%'>&nbsp;</td>
</tr>
<tr>
	<td width='25%' class='b-right'>&nbsp;</td>
	<td width='25%'>&nbsp;</td>
	<td width='25%' style='border-right: 1px solid silver;'>&nbsp;</td>
	<td width='25%'>&nbsp;</td>
</tr>
<tr>
	<td width='50%' colspan='2' align='center'>
	
	<table width='100%' border='0' cellspacing='0' cellpadding='0' align='center'>
    <tr>
	   <td width='5%'>&nbsp;</td>
	   <td width='90%' align='center' class='tidy' style='border: 1px solid silver;' onMouseOver='this.style.backgroundColor=\"#F1F1F1\"'; onMouseOut='this.style.backgroundColor=\"transparent\"'>
       <a href=\"newuser_info.php\">".$langNewUser."</a></td>
	   <td width='5%'>&nbsp;</td>
    </tr>
    </table>

	</td>
	<td width='50%' colspan='2' align='center'>

	<table width='100%' border='0' cellspacing='0' cellpadding='0' align='center'>
    <tr>
	   <td width='5%'>&nbsp;</td>
	   <td width='90%' align='center' class='tidy' style='border: 1px solid silver;' onMouseOver='this.style.backgroundColor=\"#F1F1F1\"'; onMouseOut='this.style.backgroundColor=\"transparent\"'>
       <a href=\"newprof_info.php\">".$langProfReq."</a></td>
	   <td width='5%'>&nbsp;</td>
    </tr>
    </table>
	
	</td>
</tr>
</table>

";
	 
	 
draw($tool_content, 0, 'auth');

?>
