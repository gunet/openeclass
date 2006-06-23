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
//include('../../include/init.php');
include '../../include/baseTheme.php';
$nameTools = $langLinkSite;

$tool_content = "";
if ($is_adminOfCourse) 
{ 

$tool_content .=  "<p>$langSubTitle</p>";

	if(isset($submit)) 
	{
		if (($link == "http://") or ($link == "ftp://") or empty($link))  {
			$tool_content .= "
		<table>
			<tbody>
				<tr>
					<td class=\"caution\">
					<p>$langInvalidLink</p>
					<a href=\"../../courses/$currentCourseID/index.php\">$langHome</a>
					</td>
				</tr>
			</tbody>
		</table>
		";
			
			draw($tool_content, 2);
			exit();
		}
		
		$sql = 'SELECT MAX(`id`) FROM `accueil` ';
		$res = db_query($sql,$dbname);
		while ($maxID = mysql_fetch_row($res)) {
			$mID = $maxID[0];
		}
		
		if($mID<101) $mID = 101;
		else $mID = $mID+1;
		
		
		mysql_query("INSERT INTO accueil VALUES ($mID,
					'$name_link',
					'$link \"target=_blank',
					'external_link',
					'1',
					'0',
					'$link',
					''
					)");
		
		$tool_content .= "
		<table>
			<tbody>
				<tr>
					<td class=\"success\">
					<p>$langAdded</p>
					<a href=\"../../courses/$currentCourseID/index.php\">$langHome</a>
					</td>
				</tr>
			</tbody>
		</table>
		";
		
	} 
	else 
	{  // display form
		$tool_content .=  "
			<form method=\"post\" action=\"$_SERVER[PHP_SELF]?submit=yes\">
			<table>
				<thead>
				<tr>
					<th>
						
							$langLink&nbsp;:
					</th>
					<td>
						<input type=\"text\" name=\"link\" size=\"50\" value=\"http://\">
					</td>
				</tr>
				<tr>
					<th>
							$langName&nbsp;:
					</th>
					<td>
						<input type=\"Text\" name=\"name_link\" size=\"50\">
					</td>
				</tr>
				</thead></table>
				<br>
					<input type=\"Submit\" name=\"submit\" value=\"$langAdd\">
				
			</form>
			";
	}
} else // student view 
	{
		$tool_content .=  "<tr><td colspan=\"2\">$langNotAllowed<br><br>
		<a href=\"../../courses/$currentCourseID/index.php\">$langHome</a>
		</td></tr></table>";
	}

$tool_content .=  "<tr><td colspan=\"2\"></td></tr></table>";
draw($tool_content, 2);
?>

