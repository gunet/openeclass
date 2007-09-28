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

$result=mysql_query("SELECT id, name, code FROM faculte ORDER BY name");
$numrows = mysql_num_rows($result);

$tool_content = "";

if (isset($result))  {

$tool_content .= "
    <script type='text/javascript' src='sorttable.js'></script>
    <table width='99%' class='sortable' id='t1'>
    <thead>
    <tr>
	  <th class='left'>$m[department]</th>
    </tr>
	</thead>\n";

   while ($fac = mysql_fetch_array($result)) {
	$tool_content .= "
    <tbody>
    <tr onMouseOver=\"this.style.backgroundColor='#edecdf'\" onMouseOut=\"this.style.backgroundColor='transparent'\">\n";
  	$tool_content .= "
    <td>&nbsp;<img src='../../images/arrow_blue.gif'>&nbsp;<a href='opencourses.php?fc=$fac[id]'>$fac[name]</a>&nbsp;
    <small>($fac[code])</small>&nbsp;";

     $n=mysql_query("SELECT COUNT(*) FROM cours_faculte WHERE faculte='$fac[name]'");
     $r=mysql_fetch_array($n);

    $tool_content .= "
    <small><font color=\"#aaaaaa\">($r[0]  ".  ($r[0] == 1? $langAvCours: $langAvCourses) . ")</font><small></td>
    </tr>\n";
        }
      $tool_content .= "
     </tbody>\n
    </table>";
  }


draw($tool_content, 0);
?>
