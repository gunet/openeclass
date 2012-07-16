<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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
require_once 'hierarchy.inc.php';
require_once 'include/lib/hierarchy.class.php';

$tree = new hierarchy();

$nameTools = $langListCourses;
$navigation[] = array ('url' => 'listfaculte.php', 'name' => $langSelectFac);


if (isset($_GET['fc']))
    $fc = intval($_GET['fc']);


// parse the faculte id in a session
// This is needed in case the user decides to switch language.
if (isset($fc))
    $_SESSION['fc_memo'] = $fc;


if (!isset($fc))
    $fc = $_SESSION['fc_memo'];


$fac = mysql_fetch_row(db_query("SELECT name FROM hierarchy WHERE allow_course = true AND id = " . $fc));
if (!($fac = $fac[0]))
    die("ERROR: no faculty with id $fc");


// use the following array for the legend icons
$icons = array(
    2 => "<img src='$themeimg/lock_open.png'         alt='". $m['legopen']       ."' title='". $m['legopen']       ."' width='16' height='16' />",
    1 => "<img src='$themeimg/lock_registration.png' alt='". $m['legrestricted'] ."' title='". $m['legrestricted'] ."' width='16' height='16' />",
    0 => "<img src='$themeimg/lock_closed.png'       alt='". $m['legclosed']     ."' title='". $m['legclosed']     ."' width='16' height='16' />"
);


$tool_content .= "<table width=100% class='tbl_border'>
                    <tr>
                    <th><a name='top'></a>$langFaculty:&nbsp;<b>". $tree->getFullPath($fc, true, $_SERVER['SCRIPT_NAME'].'?fc=') ."</b></th>
                    </tr>
                  </table><br/>\n\n";

$tool_content .= departmentChildren($fc, 'opencourses');

$result = db_query("SELECT course.code k,
                           course.public_code c,
                           course.title i,
                           course.visible visible,
                           course.prof_names t
                      FROM course, course_department
                     WHERE course.id = course_department.course
                       AND course_department.department = $fc
                       AND course.visible != ".COURSE_INACTIVE."
                  ORDER BY course.title, course.prof_names");

if (mysql_num_rows($result) > 0) {
    $tool_content .= "
        <table width='100%' class='tbl_border'>
        <tr>
            <th class='left' colspan='2'>$m[lessoncode]</th>
            <th class='left' width='200'>$m[professor]</th>
            <th width='30'>$langType</th>
        </tr>";
} else {
    $tool_content .= "<p class='alert1'>$m[nolessons]</p>";
}


$k = 0;
while ($mycours = mysql_fetch_array($result)) {
    if ($mycours['visible'] == 2) {
        $codelink = "<a href='../../courses/$mycours[k]/'>". q($mycours['i']) ."</a>&nbsp;<small>(". $mycours['c'] .")</small>";
    } else {
        $codelink = "$mycours[i]&nbsp;<small>(" . $mycours['c'] . ")</small>";
    }

    if ($k%2 == 0) {
        $tool_content .= "\n<tr class='even'>";
    } else {
        $tool_content .= "\n<tr class='odd'>";
    }

    $tool_content .= "\n<td width='16'><img src='$themeimg/arrow.png' title='bullet'></td>";
    $tool_content .= "\n<td>". $codelink ."</td>";
    $tool_content .= "\n<td>". $mycours['t'] ."</td>";
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
}

if ($k > 0) {
    $tool_content .= "</table>";
}

draw($tool_content, (isset($uid) and $uid)? 1: 0);
