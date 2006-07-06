<?php
session_start();
/*
+----------------------------------------------------------------------+
| E-Class - based on CLAROLINE version 1.3.0 $Revision$      |
+----------------------------------------------------------------------+
|  $Id$   |
+----------------------------------------------------------------------+
| Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
| Copyright 2003 GUnet                                                 |
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
| e-Class changes by Alexandros Diamantidis <adia@noc.uoa.gr>          |
|                    Yannis Exidaridis <jexi@noc.uoa.gr>               |
|                    Costas Tsibanis <costas@noc.uoa.gr>               |
+----------------------------------------------------------------------+
*/

$langFiles = 'opencours';
include '../../include/baseTheme.php';
$nameTools = $opencours;
$navigation[] = array ("url"=>"listfaculte.php", "name"=> $listfac);

//parse the faculte id in a session
//This is needed in case the user decides to switch language.
//echo $fc;
if (isset($fc)) {
	$_SESSION['fc_memo'] = $fc;
}

if (!isset($fc)) {
	$fc = $_SESSION['fc_memo'];
}
$fac = mysql_fetch_row(mysql_query("SELECT name FROM faculte WHERE id = ".$fc));
if (!($fac = $fac[0])) {
	die("ERROR: no faculty with id $fc");
} 

//begin_page();
$tool_content = "";
$tool_content .= "
	<table width=\"99%\">
		<thead>
			<tr>
				<th width=150>$m[department]:</th>
				<td>$fac</td>
			</tr>
		</thead>
	</table>
	<br>";



// upatras.gr patch begin, atkyritsis@upnet.gr, daskalou@upnet.gr
// use the following array for the legend icons
$icons = array(
2 => "<img src=\"../../images/gunet/OpenCourse.gif\" alt=\"\">",
1 => "<img src=\"../../images/gunet/Registration.gif\" alt=\"\">",
0 => "<img src=\"../../images/gunet/ClosedCourse.gif\" alt=\"\">"
);

// get the different course types available for this faculte
$typesresult = mysql_query("SELECT DISTINCT cours.type types FROM cours WHERE cours.faculte = '$fac' ORDER BY cours.type");

// count the number of different types
$numoftypes = mysql_num_rows($typesresult);
// output the nav bar only if we have more than 1 types of courses

if ( $numoftypes > 1) {
	//	$tool_content .= "<font class=\"courses\">";
	$counter = 1;
	while ($typesArray = mysql_fetch_array($typesresult)) {
		$t = $typesArray['types'];
		// make the plural version of type (eg pres, posts, etc)
		// this is for fetching the proper translations
		// just concatenate the s char in the end of the string
		$ts = $t."s";
		//type the seperator in front of the types except the 1st
		if ($counter != 1) $tool_content .= " | ";
		$tool_content .= "<a href=\"#".$t."\">".$m["$ts"]."</a>";
		$counter++;
	}
}

// now output the legend
$tool_content .= "
	<table width=\"99%\">
		<thead>
			<tr>
				<th width=150>".$m['legend'].":</th>
				<td>".$icons[2]." ".$m['legopen']."</td>
				<td>".$icons[1]." ".$m['legrestricted']."</td>
				<td>".$icons[0]." ".$m['legclosed']."</td>
			</tr>
		</thead>
	</table>
	<br>";

// changed this foreach statement a bit
// this way we sort by the course types
// then we just select visible
// and finally we do the secondary sort by course title and but teacher's name
foreach (array("pre" => $m['pres'],
"post" => $m['posts'],
"other" => $m['others']) as $type => $message) {
	$result=mysql_query("SELECT
						cours.code k,
						cours.fake_code c,
						cours.intitule i,
						cours.visible visible,
						cours.titulaires t
			        FROM cours_faculte, cours
			        WHERE cours.code = cours_faculte.code
							      AND cours.type = '$type'
                		AND cours_faculte.faculte='$fac'
		                ORDER BY cours.intitule, cours.titulaires");

	if (mysql_num_rows($result) == 0) {
		continue;
	}

	$tool_content .= "
	<table width=\"99%\">
		<thead>
			<tr>
				<th colspan=\"4\">$message</th>
			</tr>
			<tr>
				<th>".$m['type']."</th>
				<th>".$m['name']."</th>
				<th>".$m['code']."</th>
				<th>".$m['prof']."</th>
			</tr>
		</thead>
		<tbody>
	";
	// We changed the style a bit here and we output types as the title
	//	$tool_content .= "<a name=\"$type\" class=\"largeorange\">fgdfg$message</a>\n";

	// output a top href link if necessary
	//	if ( $numoftypes > 1)
	//	$tool_content .= "<div class=\"courses\" align=\"right\"><a href=\"#top\">áñ÷Þ</a></div>";
	//	// or a space for beautifying reasons
	//	else
	//	$tool_content .=  "<div class=\"courses\" align=\"right\">&nbsp;</div>";
	$i=0;
	while ($mycours = mysql_fetch_array($result)) {
		// changed the variable because of the previous change in the select argument
		if ($mycours['visible'] == 2) {
			$codelink = "<a href='../../courses/$mycours[k]/'>$mycours[c]</a>";
		} else {
			$codelink = $mycours['c'];
		}

		// output each course as a table for beautifying reasons

		if ($i%2==0) $tool_content .=  '<tr>';
		else $tool_content .= '<tr class="odd">';
		// show the necessary access icon
		foreach ( $icons as $visible => $image) {
			if ( $visible == $mycours['visible'] ) {
				$tool_content .= "<td>$image</td>";
			}
		}

		$tool_content .= "<td>$mycours[i]</td>
		<td>".$codelink."</td>";

		$tool_content .= "<td>$mycours[t]</td>
							</tr>";
		$i++;
	}

	$tool_content .= "</tbody></table>";
	// that's it!
	// upatras.gr patch end here, atkyritsis@upnet.gr, daskalou@upnet.gr
}

draw($tool_content, 0);
?>