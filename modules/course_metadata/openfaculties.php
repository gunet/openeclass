<?php

/* ========================================================================
 * Open eClass 2.8
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2013  Greek Universities Network - GUnet
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

require_once '../../include/baseTheme.php';
require_once 'CourseXML.php';
$nameTools = $langSelectFac;

$result = db_query("SELECT id, name, code FROM faculte ORDER BY name");

if (isset($result)) {
    $tool_content .= "<script type='text/javascript' src='sorttable.js'></script>";
    $tool_content .= "<table class='tbl_alt' width=\"100%\">";

    $k = 0;
    while ($fac = mysql_fetch_array($result)) {
        if ($k % 2 == 0)
            $tool_content .= "\n  <tr class='even'>";
        else
            $tool_content .= "\n  <tr class='odd'>";

        $tool_content .= "<th width='16'>
		<img src='$themeimg/arrow.png' alt='arrow'></th>
		<td><a href='opencourses.php?fc=$fac[id]'>$fac[name]</a>&nbsp;&nbsp;<small>
		($fac[code])";
        
        // count open courses
        $numOpenCourses = 0;
        $res = db_query("SELECT cours_id, code FROM cours WHERE faculteid = " . intval($fac['id']), $mysqlMainDb);
        while ($course = mysql_fetch_assoc($res)) {
            if (CourseXMLElement::isCertified($course['cours_id'], $course['code']))
                $numOpenCourses++;
        }
        
        $tool_content .= "&nbsp;&nbsp;-&nbsp;&nbsp;$numOpenCourses&nbsp;" . ($numOpenCourses == 1 ? $langAvCours : $langAvCourses) . "</small></td>
		</tr>";
        $k++;
    }
    $tool_content .= "</table>";
}

draw($tool_content, (isset($uid) && $uid) ? 1 : 0);
