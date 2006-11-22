<?php
session_start();
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
 * Open courses component
 * 
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * 
 * @abstract This component shows a list of courses
 *
 */

$langFiles = 'opencours';
include '../../include/baseTheme.php';
$nameTools = $opencours;
$navigation[] = array ("url"=>"listfaculte.php", "name"=> $listfac);

//parse the faculte id in a session
//This is needed in case the user decides to switch language.

if (isset($fc)) {
	$_SESSION['fc_memo'] = $fc;
}

if (!isset($fc)) {
	$fc = $_SESSION['fc_memo'];
}
$fc = intval($fc);
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
2 => "<img src=\"../../template/classic/img/OpenCourse.gif\" alt=\"\">",
1 => "<img src=\"../../template/classic/img/Registration.gif\" alt=\"\">",
0 => "<img src=\"../../template/classic/img/ClosedCourse.gif\" alt=\"\">"
);

// get the different course types available for this faculte
$typesresult = mysql_query("SELECT DISTINCT cours.type types FROM cours WHERE cours.faculte = '$fac' ORDER BY cours.type");

// count the number of different types
$numoftypes = mysql_num_rows($typesresult);
// output the nav bar only if we have more than 1 types of courses

if ( $numoftypes > 1) {
	//	$tool_content .= "<font class=\"courses\">";
	$counter = 1;
	$tool_content .= "<p>";
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
	
	$tool_content .= "</p>";
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
                		AND cours_faculte.facid='$fc'
		                ORDER BY cours.intitule, cours.titulaires");

	if (mysql_num_rows($result) == 0) {
		continue;
	}

	$tool_content .= "
	<table width=\"99%\">
		<thead>
			<tr>
				<th colspan=\"4\"><a name=\"$type\">$message</th>
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
	//	$tool_content .= "<div class=\"courses\" align=\"right\"><a href=\"#top\">αρχή</a></div>";
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
