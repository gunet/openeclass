<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */


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
$navigation[] = array ('url' => 'listfaculte.php', 'name' => $langSelectFac);
if (isset($_GET['fc'])) {
    $fc = intval($_GET['fc']);
}
// parse the faculte id in a session
// This is needed in case the user decides to switch language.
if (isset($fc)) {
    $_SESSION['fc_memo'] = $fc;
}
if (!isset($fc)) {
    $fc = $_SESSION['fc_memo'];
}

$fac = mysql_fetch_row(db_query("SELECT name FROM faculte WHERE id = " . $fc));
if (!($fac = $fac[0])) {
    die("ERROR: no faculty with id $fc");
}

// upatras.gr patch begin, atkyritsis@upnet.gr, daskalou@upnet.gr
// use the following array for the legend icons
/*
*/
$icons = array(2 => "<img src='$themeimg/lock_open.png'   alt='" . $m['legopen'] . "' title='" . $m['legopen'] . "' width='16' height='16' />",
    1 => "<img src='$themeimg/lock_registration.png' alt='" . $m['legrestricted'] . "' title='" . $m['legrestricted'] . "' width='16' height='16' />",
    0 => "<img src='$themeimg/lock_closed.png' alt='" . $m['legclosed'] . "' title='" . $m['legclosed'] . "' width='16' height='16' />"
    );

$tool_content .= "
  <table width=100% class='tbl_border'>
  <tr>
    <th><a name='top'></a>$langFaculty:&nbsp;<b>$fac</b></th>
    <th><div align='right'>";
// get the different course types available for this faculte
$typesresult = db_query("SELECT DISTINCT cours.type types FROM cours WHERE cours.faculteid = $fc ORDER BY cours.type");
// count the number of different types
$numoftypes = mysql_num_rows($typesresult);
// output the nav bar only if we have more than 1 types of courses
if ($numoftypes > 0) {
    $counter = 1;
    while ($typesArray = mysql_fetch_array($typesresult)) {
        $t = $typesArray['types'];
        // make the plural version of type (eg pres, posts, etc)
        // this is for fetching the proper translations
        // just concatenate the s char in the end of the string
        $ts = $t . "s";
        // type the seperator in front of the types except the 1st
        if ($counter != 1) {
            $tool_content .= " | ";
        }
        $tool_content .= "<a href='#$t'>" . ${'lang'.$ts} . "</a>";
        $counter++;
    }
    $tool_content .= "</div></th>
    </tr>
    </table>\n\n";
    // changed this foreach statement a bit
    // this way we sort by the course types
    // then we just select visible
    // and finally we do the secondary sort by course title and but teacher's name
    $tid = 0;
    foreach (array("pre" => $langpres,
            "post" => $langposts,
            "other" => $langothers) as $type => $message) {
        $result = db_query("SELECT cours.code k,
                                   cours.fake_code c,
                                   cours.intitule i,
                                   cours.visible visible,
                                   cours.titulaires t
                            FROM cours
                            WHERE cours.faculteid = $fc 
                            AND cours.type = '$type'
                            AND cours.visible != ".COURSE_INACTIVE."
                            ORDER BY cours.intitule, cours.titulaires");
    
        if (mysql_num_rows($result) == 0) {
            continue;
        }
        $tool_content .= "\n\n\n
           <table width=100% class='tbl_course_type'>
           <tr>
            <td>";
        // We changed the style a bit here and we output types as the title
        $tool_content .= "<a name='$type'></a><b>$message</b></td>\n";
        // output a top href link if necessary
        $tool_content .= "\n<td align='right'><a href='#top'>$m[begin]</a></td>";
        $tool_content .= "</tr>\n";
        $tool_content .= "</table>\n\n";
        $tool_content .= "
    
        <script type='text/javascript' src='sorttable.js'></script>
            <table width='100%' class='sortable' id='t$tid'>
            <tr>
                <th class='left' colspan='2'>$m[lessoncode]</th>
                <th class='left' width='200'>$m[professor]</th>
                <th width='30'>$langType</th>
            </tr>";
    
        $k = 0;
        while ($mycours = mysql_fetch_array($result)) {
            if ($mycours['visible'] == 2) {
                    $codelink = "<a href='../../courses/$mycours[k]/'>" .
                            q($mycours['i'])."</a>&nbsp;<small>(" . $mycours['c'] . ")</small>";
            } else {
                $codelink = "$mycours[i]&nbsp;<small>(" . $mycours['c'] . ")</small>";
            }
    
            if ($k%2 == 0) {
                $tool_content .= "\n<tr class='even'>";
            } else {
                $tool_content .= "\n<tr class='odd'>";
            }
            $tool_content .= "\n<td width='16'><img src='$themeimg/arrow.png' title='bullet'></td>";
            $tool_content .= "\n<td>" . $codelink . "</td>";
            $tool_content .= "\n<td>$mycours[t]</td>";
            $tool_content .= "\n<td align='center'>";
            // show the necessary access icon
            foreach ($icons as $visible => $image) {
                if ($visible == $mycours['visible']) {
                    $tool_content .= $image;
                }
            }
            $tool_content .= "</td>\n";
            $tool_content .= "</tr>";
            $k++;
            // that's it!
            // upatras.gr patch end here, atkyritsis@upnet.gr, daskalou@upnet.gr
        }
        $tool_content .= "\n</table>\n";
        $tid++;
    } // end of foreach
} else {
    $tool_content .= "&nbsp;</div></th></tr></table>\n\n";
    $tool_content .= "
    <p class='alert1'>$m[nolessons]</p>";
}

$tool_content .= "\n<br>";

draw($tool_content, (isset($uid) and $uid)? 1: 0);
