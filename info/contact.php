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

$path2add=2;
include '../include/baseTheme.php';
$nameTools = $contactpoint;

if (!empty($postaddress)) {
    $message = "| ".$postaddress;
} else {
    $message = '';
}


$tool_content .= "<table class='tbl1' width='99%'>
<tr><td class='title1' colspan='3'>$langContactInfo</td><td width='150' rowspan='3' id='contact'>&nbsp;</td></tr>
<tr><td valign='top' width='200'>$langPostMail</td><td> $Institution $message </td><td>&nbsp;</td></tr>
<tr><td valign='top' width='200'><b>$langPhone:</b></td><td> $telephone</td><td>&nbsp;</td></tr>
<tr><td>$langFax</td><td> $fax</td><td>&nbsp;</td></tr>
<tr><td><b>$langEmail</b></td><td>".mailto($emailhelpdesk, str_replace('@', ' &lt;at> ', $emailhelpdesk))."</td>
<td>&nbsp;</td>
</tr></table>";

if (isset($uid) and $uid) {
        draw($tool_content, 1);
} else {
        draw($tool_content, 0);
}
