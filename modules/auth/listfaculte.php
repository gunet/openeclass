<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
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

include '../../include/baseTheme.php';
$nameTools = $langListFac;
$result=mysql_query("SELECT id, name, code FROM faculte ORDER BY name");
$numrows = mysql_num_rows($result);

$tool_content = "";
if (isset($result))  {
	$tool_content .= "<script type='text/javascript' src='sorttable.js'></script>
	<table width=\"99%\" style=\"border: 1px solid #edecdf;\">
	<tr><td>
	<table class='sortable' id='t1' align=\"left\" width=\"100%\">
	<thead><tr>
	<th class='left' colspan='2' style='border: 1px solid #E1E0CC;'>$langFaculty</th>
	</tr></thead><tbody>";
	$k = 0;
	while ($fac = mysql_fetch_array($result)) {
		if ($k%2==0) {
			$tool_content .= "\n  <tr>";
		} else {
			$tool_content .= "\n  <tr class=\"odd\">";
		}
		$tool_content .= "<td width='1'>
		<img style='border:0px;' src='${urlServer}/template/classic/img/arrow_grey.gif' title='bullet'></td>
		<td><a href='opencourses.php?fc=$fac[id]'>$fac[name]</a>&nbsp;&nbsp;<small>
		<font style=\"color: #a33033;\">($fac[code])</font>";
		$n=mysql_query("SELECT COUNT(*) FROM cours_faculte WHERE facid=$fac[id]");
		$r=mysql_fetch_array($n);
		$tool_content .= "<font style='color: #CAC3B5;'>&nbsp;&nbsp;-&nbsp;&nbsp;$langThereAre $r[0]&nbsp;".  ($r[0] == 1? $langAvCours: $langAvCourses) . "</small></td>
		</tr>";
		$k++;
	}
   $tool_content .= "</tbody></table></td></tr></table>";
}
draw($tool_content, (isset($uid) and $uid)? 1: 0, 'auth');
