<?php
/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*				Network Operations Center, University of Athens,
*				Panepistimiopolis Ilissia, 15784, Athens, Greece
*				eMail: eclassadmin@gunet.gr
============================================================================*/

include '../../include/baseTheme.php';
$nameTools = $langListFac;
$result=mysql_query("SELECT id, name, code FROM faculte ORDER BY name");
$numrows = mysql_num_rows($result);

$tool_content = "";
if (isset($result))  {
	$tool_content .= "
<script type='text/javascript' src='sorttable.js'></script>
<table width=\"99%\" style=\"border: 1px solid #edecdf;\">
<tr>
  <td>

  <table class='sortable' id='t1' align=\"left\" width=\"100%\">
  <thead>
  <tr>
    <th class='left' colspan='2' style=\"border: 1px solid #E1E0CC;\">$m[department]</th>
  </tr>
  </thead>
  <tbody>";

   $k = 0;
   while ($fac = mysql_fetch_array($result)) {
   if ($k%2==0) {
	              $tool_content .= "\n  <tr>";
	            } else {
	              $tool_content .= "\n  <tr class=\"odd\">";
	            }
	$tool_content .= "
    <td width='1'><img src='../../images/arrow_blue.gif'></td>
    <td><a href='opencourses.php?fc=$fac[id]'>$fac[name]</a>&nbsp;&nbsp;<small><font style=\"color: #a33033;\">($fac[code])</font>";

     	$n=mysql_query("SELECT COUNT(*) FROM cours_faculte WHERE faculte='$fac[name]'");
     	$r=mysql_fetch_array($n);

    $tool_content .= "<font style=\"color: #CAC3B5;\">&nbsp;&nbsp;-&nbsp;&nbsp;$langThereAre $r[0]&nbsp;".  ($r[0] == 1? $langAvCours: $langAvCourses) . "</small></td>
  </tr>";
  $k++;
	}
   $tool_content .= "
  </tbody>
  </table>

  </td>
</tr>
</table>";
  }
draw($tool_content, 0, 'auth');
?>
