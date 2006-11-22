<?php 
/**===========================================================================
*              GUnet e-Class 2.0
*       E-learning and Course Management Program
* ===========================================================================
*	Copyright(c) 2003-2006  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
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

/**
 * My Announcements Component
 * 
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * 
 * @abstract This component shows a list of all the announcements in all the lessons
 * the user is enrolled in.
 *
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
	
