<?php 
 /*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                            |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      | $Id$        |
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
$require_login = TRUE;

$langFiles = 'announcements';
include '../../include/baseTheme.php';
include('../../include/lib/textLib.inc.php'); 
$nameTools = $langMyAnnouncements;
$tool_content = "";
$result = db_query("SELECT * FROM annonces,cours_user 
			WHERE annonces.code_cours=cours_user.code_cours 
			AND cours_user.user_id='$uid' 
			ORDER BY temps DESC",$mysqlMainDb) OR die("DB problem");

	$tool_content .= "<table width=\"99%\">
						<thead>
							<tr>
								<th width=\"200\">$langtheCourse</th>
								<th>$langAnn</th>
								<th>$langAnnouncement</th>
							</tr>
						</thead>
						<tbody>
	
	";
	$i=0;
	while ($myrow = mysql_fetch_array($result))
	{	
		$content = $myrow['contenu'];
		$content = make_clickable($content);
		$content = nl2br($content);
		$row = mysql_fetch_array(db_query("SELECT intitule,titulaires FROM cours WHERE code='$myrow[code_cours]'"));
		if($i%2 ==0) {
			$tool_content .= "<tr>";
		} else {
			$tool_content .= "<tr class=\"odd\">";
		}
		
		$tool_content .= "	
				<td>$row[intitule]</td>
				<td>".$myrow['temps']."</td>
				<td>$content</td>
			</tr>
		";
		$i++;

	}	// while loop
	$tool_content .= "
	</tbody></table>";
	draw($tool_content, 1);
?>
	
