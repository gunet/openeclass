<?php session_start();
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*                       Yannis Exidaridis <jexi@noc.uoa.gr>
*                       Alexandros Diamantidis <adia@noc.uoa.gr>
*                       Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address:     GUnet Asynchronous eLearning Group,
*                       Network Operations Center, University of Athens,
*                       Panepistimiopolis Ilissia, 15784, Athens, Greece
*                       eMail: info@openeclass.org
* =========================================================================*/


/*
 * Open courses component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This component shows a list of courses
 *
 */

include '../../include/baseTheme.php';
$nameTools = $langListCourses;
$navigation[] = array ("url"=>"listfaculte.php", "name"=> $langListFac);

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
*/
$icons = array(
 2 => "<img src='../../template/classic/img/OpenCourse.gif'   alt='".$m['legopen']."' title='".$m['legopen']."' width='16' height='16'>",
 1 => "<img src='../../template/classic/img/Registration.gif' alt='".$m['legrestricted']."' title='".$m['legrestricted']."' width='16' height='16'>",
 0 => "<img src='../../template/classic/img/ClosedCourse.gif' alt='".$m['legclosed']."' title='".$m['legclosed']."' width='16' height='16'>"
);

$tool_content .= "
    <table width=99% class='DepTitle'>
    <tr>
        <th><a name='top'>&nbsp;</a>$m[department]:&nbsp;<b>$fac</b></th>
        <td><div align='right'>";
// get the different course types available for this faculte
$typesresult = db_query("SELECT DISTINCT cours.type types FROM cours WHERE cours.faculteid = $fc ORDER BY cours.type");

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
		$tool_content .= "<a href=\"#".$t."\">".$m["$ts"]."</a>&nbsp;";
		$counter++;
	}
	$tool_content .= "</div></td>
    </tr>
    </table>";
} else {
	$tool_content .= "&nbsp;</div></td>
    </tr>
    </table>";
}


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

$tool_content .= "\n    <br />\n<table width=99%><tr><td>";
	 // We changed the style a bit here and we output types as the title
	 $tool_content .= "<a name='$type'>&nbsp;</a><b><font color=\"#a33033\">$message</font></b></td>";
          // output a top href link if necessary
          if ( $numoftypes > 1)
       $tool_content .= "\n        <td align=\"right\"><a href=\"#top\" class='mainpage'>$m[begin]</a>&nbsp;</td>";
          // or a space for beautifying reasons
          else
       $tool_content .= "\n        <td>&nbsp;</td>";
	   $tool_content .= "\n    </tr>";
	   $tool_content .= "\n    </table>\n";

     $tool_content .= "
    <script type='text/javascript' src='sorttable.js'></script>
    <table width='99%' style='border: 1px solid #edecdf;'>
    <tr>
        <td>
        <table width=100% class='sortable' id='t1'>
        <thead>
        <tr>
            <th class='left' style='border: 1px solid #E1E0CC;' colspan='2'>$m[lessoncode]</th>
            <th class='left' style='border: 1px solid #E1E0CC;' width='200'>$m[professor]</th>
            <th style='border: 1px solid #E1E0CC;' width='30'>$langType</th>
        </tr>
        </thead>
        <tbody>";
        $k = 0;
	while ($mycours = mysql_fetch_array($result)) {
		if ($mycours['visible'] == 2) {
			$codelink = "<a href='../../courses/$mycours[k]/'>$mycours[i]</a>&nbsp;<small>
			<font style='color: #a33033;'>(".$mycours['c'].")</font></small>";
		} else {
			$codelink = "<font color='#CAC3B5'>$mycours[i]&nbsp;<small>(".$mycours['c'].")</small></font>";
		}
			if ($k%2==0) {
				$tool_content .= "\n        <tr>";
			} else {
				$tool_content .= "\n        <tr class='odd'>";
			}
		$tool_content .= "\n            <td width='1%'><img style='border:0px;' src='${urlServer}/template/classic/img/arrow_grey.gif' title='bullet'></td>";
		$tool_content .= "\n            <td>".$codelink."</td>";
		$tool_content .= "\n            <td><small>$mycours[t]</small></td>";
		$tool_content .= "\n            <td align='center'>";
		// show the necessary access icon
			foreach ($icons as $visible => $image) {
				if ($visible == $mycours['visible']) {
					$tool_content .= $image;
				}
			}
		$tool_content .= "\n</td>";
		$tool_content .= "\n</tr>";
		$k++;
          }
	 $tool_content .= "\n        </tbody>\n        </table></td></tr></table>\n<br />\n";
        $tool_content .= "";
          // that's it!
          // upatras.gr patch end here, atkyritsis@upnet.gr, daskalou@upnet.gr
        }
         if ($numoftypes == 0) {
             $tool_content .= "\n    <br/>";
             $tool_content .= "\n    <br/>\n    <p class='alert1'>$m[nolessons]</p>";
         }

$tool_content .= "\n    <br>";

draw($tool_content, (isset($uid) and $uid)? 1: 0, 'auth');
