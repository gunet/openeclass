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
     <table width=99% align=center class='DepTitle'>
     <tr>
       <th><a name='top'>&nbsp;</a>$m[department]:&nbsp;<b>$fac</b></th>
       <th><div align='right'>";
// get the different course types available for this faculte
$typesresult = mysql_query("SELECT DISTINCT cours.type types FROM cours WHERE cours.faculte = '$fac' ORDER BY cours.type");

// count the number of different types
$numoftypes = mysql_num_rows($typesresult);
// output the nav bar only if we have more than 1 types of courses

if ($numoftypes > 1) {
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
	$tool_content .= "
       </div></th>
     </tr>
     </table>";
}

//$tool_content .= "<div class='courses'>";

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
     <br>
	 
     <table width=99% align='center' class='CourseListTitle'>
     <tr>
       <th>";
	 // We changed the style a bit here and we output types as the title
$tool_content .= "<a name='$type'>&nbsp;</a>$message</th>";

          // output a top href link if necessary
          if ( $numoftypes > 1)
       $tool_content .= "
       <td><a href=\"#top\" class='mainpage'>$m[begin]</a></td>";
          // or a space for beautifying reasons
          else
       $tool_content .= "
       <td>&nbsp;</td>";
	 $tool_content .= "
     </tr>";
	 $tool_content .= "
     </table>
	 ";

     $tool_content .= "
     <script type='text/javascript' src='sorttable.js'></script>
     <table width=99% align='center' class=\"sortable\" id=\"t1\">";
     $tool_content .= "
     <thead>
     <tr>";
	 $tool_content .= "
       <th class='left'>$m[lessoncode]</th>";
     $tool_content .= "
       <th class='left'>$m[professor]</th>";
     $tool_content .= "
       <th>Τύπος</th>";
     $tool_content .="
     </tr>
     </thead>";

		while ($mycours = mysql_fetch_array($result)) {
            if ($mycours['visible'] == 2) {
              $codelink = "&nbsp;<img src='../../images/arrow_blue.gif'>&nbsp;<a href='../../courses/$mycours[k]/'>$mycours[i]</a>&nbsp;<font color='#a9a9a9'>(".$mycours['c'].")</font>";
            } else {
              $codelink = "&nbsp;<img src='../../images/arrow_blue.gif'>&nbsp;<font color='#a9a9a9'>$mycours[i]&nbsp;(".$mycours['c'].")</font>";
            }

            // output each course as a table for beautifying reasons
            $tool_content .= "
     <tr onMouseOver=\"this.style.backgroundColor='#edecde'\" onMouseOut=\"this.style.backgroundColor='transparent'\">";
            // changed the variable because of the previous change in the select argument

       $tool_content .= "
       <td width='65%'>".$codelink."</td>";
       $tool_content .= "
       <td>$mycours[t]</td>";
       $tool_content .= "
       <td width='5%' align='center'>";
            // show the necessary access icon
                      foreach ($icons as $visible => $image) {
                          if ($visible == $mycours['visible']) {
                              $tool_content .= $image;
                          }
                        }
       $tool_content .= "</td>";
     $tool_content .= "
     </tr>";
          }
	 $tool_content .= "
     </table>
	 
     <br/>";
        $tool_content .= "";
          // that's it!
          // upatras.gr patch end here, atkyritsis@upnet.gr, daskalou@upnet.gr
        }
         if ($numoftypes == 0) {
             $tool_content .= "<br/>";
             $tool_content .= "
       </th>
     </tr>
     </table><br/><p class='alert1'>$m[nolessons]</p>";
         }

$tool_content .= "<br>";
//$tool_content .= "</div>";

draw($tool_content, 0);
?>
