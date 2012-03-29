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

include '../../include/baseTheme.php';

$TBL_HIERARCHY = 'hierarchy';

$nameTools = $langSelectFac;

$query = "SELECT node.id, node.lft AS lft, node.code as code, node.name,
                 COUNT(parent.id) - 1 AS depth
            FROM ". $TBL_HIERARCHY ." AS node, ". $TBL_HIERARCHY ." AS parent 
           WHERE node.lft BETWEEN parent.lft AND parent.rgt 
             AND node.allow_course = true
        GROUP BY node.id 
        ORDER BY node.lft";
$result = db_query($query);


$query = "SELECT max(depth) FROM (SELECT  COUNT(parent.id) - 1 AS depth
            FROM $TBL_HIERARCHY AS node, $TBL_HIERARCHY AS parent 
           WHERE node.lft BETWEEN parent.lft AND parent.rgt 
        GROUP BY node.id 
        ORDER BY node.lft) AS hierarchydepth";
$maxdepth = mysql_fetch_array(db_query($query));

if (isset($result))  {
	$tool_content .= "<script type='text/javascript' src='sorttable.js'></script>
<table class='tbl_alt' width=\"100%\">
	";
	$k = 0;
	while ($fac = mysql_fetch_array($result)) {
		if ($k%2 == 0) {
			$tool_content .= "\n  <tr class='even'>";
		} else {
		        $tool_content .= "\n  <tr class='odd'>";
		}
		$tool_content .= "<th width='16'>
		<img src='$themeimg/arrow.png' alt='arrow'></th>";
                
                for ($i = 1; $i <= $fac['depth']-1; $i++) // extra -1 because we do not display root
                    $tool_content .= "<td width='5'>&nbsp;</td>";
                $colspan = $maxdepth[0] - $fac['depth'] + 1;
                
		$tool_content .= "<td colspan='$colspan'><a href='opencourses.php?fc=$fac[id]'>$fac[name]</a>&nbsp;&nbsp;<small>
		($fac[code])";
		$n = db_query("SELECT COUNT(*) FROM cours, course_department 
                        WHERE cours.cours_id = course_department.course AND course_department.department = $fac[id]");
		$r = mysql_fetch_array($n);
		$tool_content .= "&nbsp;&nbsp;-&nbsp;&nbsp;$r[0]&nbsp;".  ($r[0] == 1? $langAvCours: $langAvCourses) . "</small></td>
		</tr>";
		$k++;
	}
   $tool_content .= "</table>";
}
draw($tool_content, (isset($uid) and $uid)? 1: 0);
