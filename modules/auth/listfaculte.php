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

include '../../include/baseTheme.php';

$TBL_HIERARCHY = 'hierarchy';
require_once '../../include/lib/hierarchy.class.php';
$tree = new hierarchy();

$nameTools = $langSelectFac;


$query = "SELECT max(depth) FROM (SELECT  COUNT(parent.id) - 1 AS depth
            FROM $TBL_HIERARCHY AS node, $TBL_HIERARCHY AS parent 
           WHERE node.lft BETWEEN parent.lft AND parent.rgt 
        GROUP BY node.id 
        ORDER BY node.lft) AS hierarchydepth";
$maxdepth = mysql_fetch_array(db_query($query));

list($tree_array, $idmap, $depthmap, $codemap, $allowcoursemap, $allowusermap, $orderingmap) = $tree->build(array(), 'id', null, 'AND node.allow_course = true', false);


$tool_content .= "<table class='tbl_alt' width=\"100%\">";
$k = 0;

foreach ($tree_array as $key => $value)
{
    $trclass = ($k%2 == 0) ? 'even' : 'odd';
    $colspan = $maxdepth[0] - $depthmap[$key] + 1;
    $n = db_query("SELECT COUNT(*) 
                     FROM course, course_department 
                    WHERE course.id = course_department.course 
                      AND course_department.department = $key");
    $r = mysql_fetch_array($n);
        
    $tool_content .= "<tr class='$trclass'>";
    $tool_content .= "<th width='16'><img src='$themeimg/arrow.png' alt='arrow' /></th>";

    for ($i = 1; $i <= $depthmap[$key]-1; $i++) // extra -1 because we do not display root
        $tool_content .= "<td width='5'>&nbsp;</td>";

    $tool_content .= "<td colspan='$colspan'><a href='opencourses.php?fc=$key'>". $value ."</a>&nbsp;&nbsp;<small>(". $codemap[$key] .")";
    $tool_content .= "&nbsp;&nbsp;-&nbsp;&nbsp;$r[0]&nbsp;".  ($r[0] == 1? $langAvCours: $langAvCourses) . "</small></td></tr>";
    
    $k++;
}

$tool_content .= "</table>";

draw($tool_content, (isset($uid) and $uid)? 1: 0);
