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

$nameTools = $langReg;

$tool_content = "";

$tool_content .= "<ul class=\"listBullet\">";
$tool_content .= "<li><a href=\"newuser_info.php\">".$langNewUser."</a><br /></li><br>";
$tool_content .= "<li><a href=\"newprof_info.php\">".$langProfReq."</a><br /></li>";
$tool_content .= "</ul>";

draw($tool_content, 0);

?>
