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

/**
  @file: userstats.php
  @brief: user statistics
 */
$require_usermanage_user = true;
require_once '../../include/baseTheme.php';
require_once 'modules/graphics/plotter.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

$tree = new Hierarchy();
$user = new User();

$toolName = $langUserStats;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
$navigation[] = array("url" => "listusers.php", "name" => $langListUsers);

$tool_content .= action_bar(array(
            array('title' => $langBack,
                'url' => "listusers.php",
                'icon' => 'fa-reply',
                'level' => 'primary-label')
                ));

$u = isset($_REQUEST['u']) ? intval($_REQUEST['u']) : '';

if (isDepartmentAdmin())
    validateUserNodes(intval($u), true);

if (!empty($u)) {
    $info = Database::get()->querySingle("SELECT username FROM user WHERE id =?d", $u)->username;
    $tool_content .= "<h4>$langStudentParticipation " . q($info) . "</h4>";

    // display user courses (if any)
    $foundUsers = false;   
    Database::get()->queryFunc("SELECT DISTINCT a.code, a.title, b.status, a.id
                           FROM course AS a
                           JOIN course_department ON a.id = course_department.course
                           JOIN hierarchy ON course_department.department = hierarchy.id
                           LEFT JOIN course_user AS b ON a.id = b.course_id
                           WHERE b.user_id = ?d
                           ORDER BY b.status, hierarchy.name", function($logs) use (&$foundUsers, &$tool_content, $langCourseCode, $langProperty, $langTeacher, $langStudent, $langVisitor ) {
        if (!$foundUsers) {
            $foundUsers = true;
            $tool_content .= "<div class='table-responsive'>
		<table class='table-default'>
		<tr>
		  <th class='text-left'>&nbsp;&nbsp;$langCourseCode</th>
		  <th>$langProperty</th>
		</tr>";
        }
        
        $tool_content .= "<tr>";
        $tool_content .= "<td align=''>" . htmlspecialchars($logs->code) . " (" . htmlspecialchars($logs->title) . ")</td>
				<td class='text-left'>";
        switch ($logs->status) {
            case USER_TEACHER:
                $tool_content .= $langTeacher;
                break;
            case USER_STUDENT:
                $tool_content .= $langStudent;
                break;
            default:
                $tool_content .= $langVisitor;
                break;
        }        
    }, $u);
    if ($foundUsers) {
        $tool_content .= "</td></tr></table></div>";
    } else {
        $tool_content .= "<p class='alert alert-info'>$langNoStudentParticipation</p>";
    }

    $tool_content .= "<p><b>$langTotalVisits</b>: ";
    $totalHits = 0;
    $course_code_result = Database::get()->queryArray("SELECT DISTINCT a.code, a.title, b.status, a.id
                                     FROM course AS a
                                     JOIN course_department ON a.id = course_department.course
                                     JOIN hierarchy ON course_department.department = hierarchy.id
                                LEFT JOIN course_user AS b ON a.id = b.course_id
                                    WHERE b.user_id = ?d
                                 ORDER BY b.status, hierarchy.name", $u);
    $hits = array();
    if (sizeof($course_code_result) > 0) {
        foreach ($course_code_result as $row) {
            $course_codes[] = $row->code;
            $course_names[$row->code] = $row->title;
        }
        $course_code_result = null;

        foreach ($course_codes as $code) {
            Database::get()->queryFunc("SELECT SUM(hits) AS cnt FROM actions_daily
                                       WHERE user_id = ?d AND course_id =?d", function($row) use (&$totalHits, &$hits, $code) {
                $totalHits += $row->cnt;
                $hits[$code] = $row->cnt;
            }, $u, course_code_to_id($code));
        }
    }
    $tool_content .= "<b>$totalHits</b></p>";
    $chart = new Plotter(220, 200);
    $chart->setTitle($langCourseVisits);
    foreach ($hits as $code => $count) {
        $chart->growWithPoint(q($course_names[$code]), $count);
    }
    $tool_content .= $chart->plot();
    // End of chart display; chart unlinked at end of script.
    $tool_content .= "<p>$langLastUserVisits " . q($info) . "</p>";
    $tool_content .= "<div class='table-responsive'><table class='table-default'>
	      <tr>
		<th class='text-left'>&nbsp;&nbsp;$langDate</th>
		<th class='text-center'>$langAction</th>
	      </tr>";
    $Action["LOGIN"] = "<font color='#008000'>$langLogIn</font>";
    $Action["LOGOUT"] = "<font color='#FF0000'>$langLogout</font>";
    
    Database::get()->queryFunc("SELECT * FROM loginout WHERE id_user = '$u' ORDER by idLog DESC LIMIT 15", function($r) use (&$i, &$tool_content, $Action) {
        $when = $r->when;
        $action = $r->action;       
        $tool_content .= "<tr>";        
        $tool_content .= "<td>" . strftime("%d/%m/%Y (%H:%M:%S) ", strtotime($when)) . "</td>
			<td class='text-center'>" . $Action[$action] . "</td></tr>";
    });

    $tool_content .= "</table></div>";
} else {
    $tool_content .= "<div class='alert alert-danger'>$langNoUserSelected</div>";
    draw($tool_content, 3);
    exit();
}

draw($tool_content, 3, null, $head_content);
