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

// security check
$fc = intval($fc);
$fac = mysql_fetch_row(mysql_query("SELECT name FROM faculte WHERE id = ".$fc));
if (!($fac = $fac[0])) {
	die("ERROR: no faculty with id $fc");
} 

$tool_content = "";

// upatras.gr patch begin, atkyritsis@upnet.gr, daskalou@upnet.gr
// use the following array for the legend icons
/*
$icons = array(
2 => "<img src=\"../../template/classic/img/OpenCourse.gif\" alt=\"\">",
1 => "<img src=\"../../template/classic/img/Registration.gif\" alt=\"\">",
0 => "<img src=\"../../template/classic/img/ClosedCourse.gif\" alt=\"\">"
);
*/
$icons = array(
 2 => "<img src=\"../../images/OpenCourse.gif\" alt=\"".$m['legopen']."\" title=\"".$m['legopen']."\" width=\"25\" height=\"25\">",
 1 => "<img src=\"../../images/Registration.gif\" alt=\"".$m['legrestricted']."\" title=\"".$m['legrestricted']."\" width=\"25\" height=\"25\">",
0 => "<img src=\"../../images/ClosedCourse.gif\" alt=\"".$m['legclosed']."\" title=\"".$m['legclosed']."\" width=\"25\" height=\"25\">"
);

$tool_content .= "
            <table border=0' width=96% align=center cellspacing='0' cellpadding='0'>
            <tr>
            <td align='left' style='border-top: 0px solid $table_border; border-right: 0px solid $table_border; border-left: 0px solid $table_border;' class='td_NewDir'>
                <a name='top'>$m[department]:</a>
                <b><em>$fac</em></b>
                </td>
            </tr>
            <tr>
            <td align=right valign='middle' class=td_NewDir style='border-bottom: 0px solid $table_border; border-right: 0px solid $table_border; border-left: 0px solid $table_border;' height=20>&nbsp;";

// get the different course types available for this faculte
$typesresult = mysql_query("SELECT DISTINCT cours.type types FROM cours WHERE cours.faculte = '$fac' ORDER BY cours.type");

// count the number of different types
$numoftypes = mysql_num_rows($typesresult);
// output the nav bar only if we have more than 1 types of courses

if ( $numoftypes > 1) {
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
	
	$tool_content .= "</td></tr><tr><td align=right valign=middle>";
}

$tool_content .= "<div class='courses'>";

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

$tool_content .= "<br><script type='text/javascript' src='sorttable.js'></script>
          <table width='100%' border=0 cellpadding='0' cellspacing='0' align=center>
          <tr><th align=left style='background: #E6EDF5; color: #4F76A3;'><b>";

	// We changed the style a bit here and we output types as the title
$tool_content .= "<a name='$type'>$message</a></b></th>\n";

          // output a top href link if necessary
          if ( $numoftypes > 1)
            $tool_content .= "<th align=\"right\" style='background: #E6EDF5; color: #4F76A3;'>
								<a href=\"#top\" class='mainpage'>$m[begin]</a></th>";
          // or a space for beautifying reasons
          else
            $tool_content .= "<td class=kk align=\"right\">&nbsp;</td>";
	          $tool_content .= "</tr>";
						$tool_content .= "</table>";

		        $tool_content .= "<table border='0' bgcolor=white class=\"sortable\" id=\"t1\" cellspacing=\"1\" cellpadding=\"0\" style=\"border: 1px solid $table_border\">";
           $tool_content .= "<tr>";
	         $tool_content .= "<td class='td_small_HeaderRow' style=\"border: 1px solid $table_border\">$m[lessoncode]</td>";
           $tool_content .= "<td class='td_small_HeaderRow' style=\"border: 1px solid $table_border\">$m[professor]</td>";
					 $tool_content .= "<td class='td_small_HeaderRow' align='center' style=\"border: 1px solid $table_border\">Τύπος</td>";
					 $tool_content .=" </tr>";

		while ($mycours = mysql_fetch_array($result)) {
          // changed the variable because of the previous change in the select argument
            if ($mycours['visible'] == 2) {
              $codelink = "<a href='../../courses/$mycours[k]/' class='CourseLink'>$mycours[i]</a>&nbsp;<span class='explanationtext'><font color=#4175B9>(".$mycours['c'].")</font></span>";
            } else {
              $codelink = "<font color=#A9A9A9>$mycours[i]&nbsp;<span class='explanationtext'>(".$mycours['c'].")</font></span>";
            }

            // output each course as a table for beautifying reasons
            $tool_content .= "<tr onMouseOver=\"this.style.backgroundColor='#F5F5F5'\" onMouseOut=\"this.style.backgroundColor='transparent'\">";
            $tool_content .= "<td width='65%' class=kkk>".$codelink."</td>";
            $tool_content .= "<td class=kkk><span class='explanationtext'>$mycours[t]</span></td>";
            $tool_content .= "<td width='5%' align='center' class=kkk>";
            // show the necessary access icon
                      foreach ($icons as $visible => $image) {
                          if ($visible == $mycours['visible']) {
                              $tool_content .= $image;
                          }
                        }
            $tool_content .= "</td>";
            $tool_content .= "</tr>";
          }

				$tool_content .= "</table>";
        $tool_content .= "<br>";
          // that's it!
          // upatras.gr patch end here, atkyritsis@upnet.gr, daskalou@upnet.gr
        }
         if ($numoftypes == 0) {
             $tool_content .= "<br>";
             $tool_content .= "<div class=alert1>$m[nolessons]</div>";
         }

$tool_content .= "<br></td></tr></table></div>";

draw($tool_content, 0, 'auth');
?>
