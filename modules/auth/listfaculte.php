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
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/

include '../../include/baseTheme.php';
$nameTools = $langListFac;
$result=mysql_query("SELECT id, name, code FROM faculte ORDER BY name");
$numrows = mysql_num_rows($result);

$tool_content = "";
if (isset($result))  {
	$tool_content .= "
    <script type='text/javascript' src='sorttable.js'></script>
    <table width='99%' class='sortable' id='t1'>
    <thead><tr><th class='left'>$m[department]</th></tr></thead>\n";

   while ($fac = mysql_fetch_array($result)) {
	$tool_content .= "<tbody>
    	<tr onMouseOver=\"this.style.backgroundColor='#fbfbfb'\" onMouseOut=\"this.style.backgroundColor='transparent'\">\n";
  	$tool_content .= "
    	<td>&nbsp;<img src='../../images/arrow_blue.gif'>&nbsp;<a href='opencourses.php?fc=$fac[id]'>$fac[name]</a>&nbsp;
    	<small>($fac[code])</small>&nbsp;";

     	$n=mysql_query("SELECT COUNT(*) FROM cours_faculte WHERE faculte='$fac[name]'");
     	$r=mysql_fetch_array($n);

    	$tool_content .= "<small><font color=\"#aaaaaa\">($r[0]  ".  ($r[0] == 1? $langAvCours: $langAvCourses) . ")</font><small></td></tr>\n";
	}
   $tool_content .= "</tbody>\n</table>";
  }
draw($tool_content, 0);
?>
