<?php
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
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
$tool_content = "";

$tool_content .= "
<p>$introcontact</p>

<table width=\"80%\" style=\"border: 1px solid #edecdf;\">
<thead>
<tr>
  <td>

  <table width=\"500\" class=\"FormData\">
  <thead>
  <tr>
    <th class='left' width='220'>$langPostMail</th>
    <td width='300'>
        $Institution<br>
        $postaddress
    </td>
  </tr>
  <tr>
	<th class='left'>$langPhone:</th>
    <td width='300'>$telephone</td>
  </tr>
  <tr>
	<th class='left'>$langFax</th>
    <td width='300'>$fax</td>
  </tr>
  <tr>
    <th class='left'>$langEmail:</th>
    <td width='300'>".mailto($emailAdministrator, str_replace('@', ' &lt;at> ', $emailAdministrator))."
    </td>
  </tr>
  </thead>
  </table>

  </td>
</tr>
</thead>
</table>";

if (isset($uid) and $uid) {
        draw($tool_content, 1);
} else {
        draw($tool_content, 0);
}
