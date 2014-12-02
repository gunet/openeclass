<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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

$require_help = TRUE;
$helpTopic = 'PersonalStats';
include '../../include/baseTheme.php';
require_once 'modules/graphics/plotter.php';

$require_valid_uid = TRUE;

check_uid();
check_guest();

$nameTools = $langPersonalStats;

$totalHits = 0;
$totalDuration = 0;

$result = Database::get()->queryArray("SELECT a.code code, a.title title
                                        FROM course AS a LEFT JOIN course_user AS b
                                             ON a.id = b.course_id
                                        WHERE b.user_id = ?d
                                        AND a.visible != " . COURSE_INACTIVE . "
                                        ORDER BY a.title", $uid);

if (count($result) > 0) {  // found courses ?    
    foreach ($result as $row) {
        $course_codes[] = $row->code;
        $course_names[$row->code] = $row->title;
    }  
    foreach ($course_codes as $code) {                        
        $cid = course_code_to_id($code);                
        $row = Database::get()->querySingle("SELECT SUM(hits) AS cnt FROM actions_daily
                                WHERE user_id = ?d
                                AND course_id =?d", $uid, $cid);
        if ($row) {
            $totalHits += $row->cnt;
            $hits[$code] = $row->cnt;
        }
        $result = Database::get()->querySingle("SELECT SUM(duration) AS duration FROM actions_daily
                                        WHERE user_id = ?d
                                        AND course_id = ?d", $uid, $cid);
        $duration[$code] = $result->duration;
        $totalDuration += $duration[$code];        
    }

    $chart = new Plotter(600, 300);
    $chart->setTitle($langCourseVisits);
    foreach ($hits as $code => $count) {
        if ($count > 0) {
            $chart->addPoint($course_names[$code], $count);
            $chart->modDimension(7, 0);
        }
    }
    $tool_content .= $chart->plot();

    $totalDuration = format_time_duration(0 + $totalDuration);
    $tool_content .= "<fieldset>
                <legend>$langPlatformGenStats</legend>
                <table class='table-default'>
                <tr>
                <th>$langTotalVisitsCourses:</th>
                <td>$totalHits</td>
                </tr>
                <tr>
                <th>$langDurationVisits:</th>
                <td>$totalDuration</td>
                </tr>
                <tr>
                <th valign='top'>$langDurationVisitsPerCourse:</th>
                <td>
                <table class='table-default' width='550'>
                <tr>
                <th>$langCourseTitle</th>                   
                <th width='160'>$langDuration</th>
                </tr>";    
    foreach ($duration as $code => $time) {        
        $tool_content .= "                
                <td>" . q(course_code_to_title($code)) . "</td>
                <td width='140'>" . format_time_duration(0 + $time) . "</td>
                </tr>";
    }
    $tool_content .= "</table></td></tr>";
}
// End of chart display; chart unlinked at end of script.

$tool_content .= "<tr><th>$langLastVisits:</th><td>";
$tool_content .= "<table class='table-default' width='550'>
            <tr>
              <th>$langDate</th>
              <th width='140'>$langAction</th>
            </tr>";
$act["LOGIN"] = "<font color='#008000'>$langLogIn</font>";
$act["LOGOUT"] = "<font color='#FF0000'>$langLogout</font>";
$q = Database::get()->queryArray("SELECT * FROM loginout
                        WHERE id_user = ?d ORDER by idLog DESC LIMIT 10", $uid);

foreach ($q as $result) {
    $when = $result->when;
    $action = $result->action;
    
    $tool_content .= "        
        <td>" . strftime("%d/%m/%Y (%H:%M:%S) ", strtotime($when)) . "</td>
        <td>" . $act[$action] . "</td>
        </tr>";
}
$tool_content .= "</table>";
$tool_content .= "</td></tr></table></fieldset>";

draw($tool_content, 1, null, $head_content);
