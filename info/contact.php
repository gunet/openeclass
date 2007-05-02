<?php
/*
=============================================================================
           GUnet e-Class 2.0
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

$langFiles = array('registration','gunet');
$path2add=2;
include '../include/baseTheme.php';

$nameTools = $contactpoint;
$tool_content = "";
//$tool_content .= <<<tCont
$tool_content .= "
	<div align=justify class=td_main>$introcontact</div>
        <blockquote>
        <table>
        <tr>
          <td valign=top>
          <img src='../images/env.gif'>&nbsp;&nbsp;
          </td>
          <td>
          $langPostMail<br>
          $Institution<br>
          $postaddress<br>
     </td>
      </tr>
      <tr><td colspan=2>&nbsp;</td></tr>
    <tr>
    <td valign=top><img src='../images/phone.gif'>&nbsp;&nbsp;</td>
    <td>$langPhone
         $telephone<br>
         $langFax
         $fax<br>
    </td></tr>
    <tr><td colspan=2>&nbsp;</td></tr>
        <tr>
          <td><img src=../images/email.gif>&nbsp;&nbsp;</td>
          <td>
          <br>
          <b>$langEmail : </b>".mailto($emailAdministrator, str_replace('@', ' &lt;at> ', $emailAdministrator))."
          </td>
        </tr>
        </table></blockquote>";
//tCont;
draw($tool_content, 0);
?>
