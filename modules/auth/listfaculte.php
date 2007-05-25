<?php
/*
+----------------------------------------------------------------------+
| CLAROLINE version 1.3.0 $Revision$                            |
+----------------------------------------------------------------------+
| $Id$          |
+----------------------------------------------------------------------+
| Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
+----------------------------------------------------------------------+
|    This program is free software; you can redistribute it and/or     |
|    modify it under the terms of the GNU General Public License       |
|    as published by the Free Software Foundation; either version 2    |
|   of the License, or (at your option) any later version.             |
|                                                                      |
|   This program is distributed in the hope that it will be useful,    |
|   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
|   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
|   GNU General Public License for more details.                       |
|                                                                      |
|   You should have received a copy of the GNU General Public License  |
|   along with this program; if not, write to the Free Software        |
|   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
|   02111-1307, USA. The GPL license is also available through the     |
|   world-wide-web at http://www.gnu.org/copyleft/gpl.html             |
+----------------------------------------------------------------------+
| Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
|          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
|          Christophe Gesche <gesche@ipm.ucl.ac.be>                    |
+----------------------------------------------------------------------+
*/

$langFiles = 'opencours';
include '../../include/baseTheme.php';
$nameTools = $listfac;

$tool_content = "";

$tool_content .= "<div class='td_main_left'>";

$result=mysql_query("SELECT id, name, code FROM faculte ORDER BY name");
$numrows = mysql_num_rows($result);

if (isset($result))  {

$tool_content .= "<script type='text/javascript' src='sorttable.js'></script>
        <table width='95%' class='sortable' id='t1' cellspacing='0' cellpadding='10' border='0' 
				style='border: 1px solid $table_border'>
        <tr><th style='text-align: left; background: #E6EDF5; color: #4F76A3;' height=25>
					<b>$m[department]</b></th></tr>";

 while ($fac = mysql_fetch_array($result)) {
	$tool_content .= "<tr onMouseOver=\"this.style.backgroundColor='#F1F1F1'\" onMouseOut=\"this.style.backgroundColor='transparent'\">";
  $tool_content .= "<td class='kk' height=25>&nbsp;<img src='../../images/arrow_blue.gif'>
	&nbsp;<a href='opencourses.php?fc=$fac[id]' class='mainpage'>$fac[name] </a>
	<small><font color=#4175B9>($fac[code])</font></small>";

     $n=mysql_query("SELECT COUNT(*) FROM cours_faculte WHERE faculte='$fac[name]'");
     $r=mysql_fetch_array($n);

    $tool_content .= "<small><font color=#AAAAAA>($r[0]  "
      .  ($r[0] == 1? $langAvCourse: $langAvCourses) . ")</font><small>
              </td>
        		</tr>\n";
        }
      $tool_content .= "</table>";
  }

$tool_content .= "</div>";

draw($tool_content, 0, 'auth');
?>
