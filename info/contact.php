<?php
/*
=============================================================================
           GUnet eClass 2.0
        E-learning and Course Management Program
================================================================================
        Copyright(c) 2003-2006  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".

           Authors:     Costas Tsibanis <k.tsibanis@noc.uoa.gr>
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
==============================================================================
*/

$path2add=2;
include '../include/baseTheme.php';

$nameTools = $contactpoint;
$tool_content = "";

$tool_content .= "
  <p>$introcontact</p>
 <br/>
  <table width='500'>
  <tbody>
  <tr>
    <td class='left' width='20'><img src='../images/env.gif' align='absbottom'></img></td>
    <th class='left' width='180'>$langPostMail</th>
    <td width='300'>
        $Institution<br>
        $postaddress
    </td>
  </tr>
  <tr>
    <td class='left' width='20'><img src='../images/phone.gif' align='absbottom'></img></td>
	<th class='left' width='180'>$langPhone<br/>$langFax</th>
    <td width='300'>$telephone<br/>$fax</td>
  </tr>
  <tr>
    <td class='left' width='20'><img src='../images/email.gif' align='absbottom'></img></td>
    <th class='left' width='180'>$langEmail:</th>
    <td width='300'>".mailto($emailAdministrator, str_replace('@', ' &lt;at> ', $emailAdministrator))."
    </td>
  </tr></tbody></table>";

if (isset($uid) and $uid) {
        draw($tool_content, 1);
} else {
        draw($tool_content, 0);
}
