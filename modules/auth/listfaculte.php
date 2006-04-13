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
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
 */

$langFiles = 'opencours';
include('../../include/init.php');

$nameTools = $listfac;
$local_style = "
	p { line-height: 25pt; font-size: 11pt; padding: 1em; background: $color2; }
";
begin_page();

?><tr>
	<td>
	<p>
<?
	$result=mysql_query("SELECT id, name FROM faculte ORDER BY name");
  	$numrows = mysql_num_rows($result);
   	while ($fac = mysql_fetch_array($result)) {	
		 echo "<a href='opencourses.php?fc=$fac[id]'>$fac[name]</a>";
		 $n=mysql_query("SELECT COUNT(*) FROM cours_faculte WHERE faculte='$fac[name]'");
		 $r=mysql_fetch_array($n);
		 echo " <span style='font-size: 10pt'>($r[0] "
		 	. ($r[0] == 1? $avlesson: $avlessons) . ")</span><br>\n";
        }
?>
		</td>
	</tr>
</table>
</body>
</html>
