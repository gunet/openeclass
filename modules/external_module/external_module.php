<?php 
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                             |
      +----------------------------------------------------------------------+
      | $Id$       |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
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
      |   02111-1307, USA. The GNU GPL license is also available through     |
      |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
 */
/*******************************************************
*               EXTERNAL MODULE / LINK                 *
********************************************************

GOALS
*****
Add link to external site directly form Home page main menu
************************************************************/

$require_current_course = TRUE;
$langFiles = 'external_module';
$require_help = TRUE;
$helpTopic = 'Module';
include('../../include/init.php');

$nameTools = $langLinkSite;
begin_page();

if ($is_adminOfCourse) 
{ 

echo "<tr><td><font face=\"arial, helvetica\" size=\"2\">$langSubTitle</td>
        <td valign=\"top\">&nbsp;</td></tr>";

	if(isset($submit)) 
	{
		if (($link == "http://") or ($link == "ftp://") or empty($link))  {
			echo "<td>	
				$langInvalidLink<br><br>
				<a href=\"../../$currentCourseID/index.php\">$langHome</a></td>";
			exit();
		}
		
		$sql = 'SELECT MAX(`id`) FROM `accueil` ';
		$res = mysql_query($sql);
		while ($maxID = mysql_fetch_row($res)) {
			$mID = $maxID[0];
		}
		echo $mID . "  ";
		if($mID<101) $mID = 101;
		else $mID = $mID+1;
		echo $mID;
		
		mysql_query("INSERT INTO accueil VALUES ($mID,
					'$name_link',
					'$link \"target=_blank',
					'../../../images/travaux.png',
					'1',
					'0',
					'$link',
					''
					)");
		echo "<tr><td><font face=\"arial, helvetica\" size=\"2\">
			$langAdded
			<br>
			<a href=\"../../courses/$currentCourseID/index.php\">$langHome</a>
			<br>
			</font>
			</td></tr>";
	} 
	else 
	{  // display form
		echo "<tr><td><font face=\"arial, helvetica\" size=\"2\">
			<form method=\"post\" action=\"$_SERVER[PHP_SELF]?submit=yes\">
			<table>
				<tr>
					<td>
						<font face=\"arial, helvetica\" size=\"2\">
							$langLink&nbsp;:
					</td>
					<td><font face=\"arial, helvetica\" size=\"2\">
						<input type=\"text\" name=\"link\" size=\"50\" value=\"http://\">
					</td>
				</tr>
				<tr>
					<td><font face=\"arial, helvetica\" size=\"2\">
						<font face=\"arial, helvetica\" size=\"2\">
							$langName&nbsp;:
					</td>
					<td><font face=\"arial, helvetica\" size=\"2\">
						<input type=\"Text\" name=\"name_link\" size=\"50\">
					</td>
				</tr>
				<tr>
					<td colspan=\"2\"><font face=\"arial, helvetica\" size=\"2\">	
					<input type=\"Submit\" name=\"submit\" value=\"$langAdd\">
					</td>
				</tr>
			</form>
			</table></td>
			</tr>";
	}
} else // student view 
	{
		echo "<tr><td colspan=\"2\">$langNotAllowed<br><br>
		<a href=\"../../courses/$currentCourseID/index.php\">$langHome</a>
		</td></tr></table>";
	}

echo "<tr><td colspan=\"2\"><hr noshade size=\"1\"></td></tr></table>";
?>
</body>
</html>

