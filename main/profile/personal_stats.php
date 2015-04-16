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

$pageName = $langPersonalStats;

$totalHits = 0;
$totalDuration = 0;

$result = Database::get()->queryArray("SELECT SUM(hits) AS cnt, SUM(duration) AS duration, course.code, course.title
                                        FROM course
                                            LEFT JOIN course_user ON course.id = course_user.course_id
                                            LEFT JOIN actions_daily
                                                ON actions_daily.user_id = course_user.user_id AND
                                                   actions_daily.course_id = course_user.course_id
                                        WHERE course_user.user_id = ?d
                                        AND course.visible != " . COURSE_INACTIVE . "
                                        GROUP BY course.id
                                        ORDER BY duration DESC", $uid);

if (count($result) > 0) {  // found courses ?
    foreach ($result as $item) {
        $totalHits += $item->cnt;
        $totalDuration += $item->duration;
        $hits[$item->code] = $item->cnt;
        $duration[$item->code] = $item->duration;
        $course_names[$item->code] = $item->title;
    }

    $chart = new Plotter(600, 300);
    $chart->setTitle($langCourseVisits);
    foreach ($hits as $code => $count) {
        if ($count > 0) {
            $chart->addPoint($course_names[$code], $count);
            $chart->modDimension(7, 0);
        }
    }
    $tool_content .= action_bar(array(
                                array('title' => $langBack,
                                      'url' => $urlServer,
                                      'icon' => 'fa-reply',
                                      'level' => 'primary-label',
                                      'button-class' => 'btn-default')
                            ), false);
    $tool_content .= "<div class='row'><div class='col-xs-12'><div class='panel'><div class='panel-body'>" .
        $chart->plot() .
        "</div></div></div></div>";

    $totalDuration = format_time_duration(0 + $totalDuration, 240);
    $tool_content .= "
                <h4>$langPlatformGenStats</h4>
                <div class='row margin-bottom-fat'>
                  <div class='col-md-4'>$langTotalVisitsCourses:</div>
                  <div class='col-md-8'>$totalHits</div>
                </div>
                <div class='row margin-bottom-fat margin-top-fat'>
                  <div class='col-md-4'>$langDurationVisits:</div>
                  <div class='col-md-8'>$totalDuration</div>
                </div>
                <div class='row margin-bottom-fat margin-top-fat'>
                  <div class='col-xs-12'>$langDurationVisitsPerCourse:</div>
                  <div class='col-xs-12'>
                    <ul class='list-group'>
                      <li class='list-group-item disabled'>
                        <div class='row'>
                          <div class='col-sm-8'><b>$langCourseTitle</b></div>
                          <div class='col-sm-4 text-muted'><b>$langDuration</b></div>
                        </div>
                      </li>";
    foreach ($duration as $code => $time) {
        $tool_content .= "
                      <li class='list-group-item'>
                        <div class='row'>
                          <div class='col-sm-8'><b>" . q(course_code_to_title($code)) . "</b></div>
                          <div class='col-sm-4 text-muted'>" . format_time_duration(0 + $time, 240) . "</div>
                        </div>
                      </li>";
    }
    $tool_content .= "
                    </ul>
                  </div>
                </div>";
}

$tool_content .= "
                <div class='row margin-bottom-fat margin-top-fat'>
                  <div class='col-xs-12'>$langLastVisits:</div>
                  <div class='col-xs-12'>
                    <ul class='list-group'>
                      <li class='list-group-item disabled'>
                        <div class='row'>
                          <div class='col-sm-8'><b>$langDate</b></div>
                          <div class='col-sm-4'><b>$langAction</b></div>
                        </div>
                      </li>";
$act["LOGIN"] = "<span class='text-success'>$langLogIn</span>";
$act["LOGOUT"] = "<span class='text-danger'>$langLogout</span>";
$q = Database::get()->queryArray("SELECT * FROM loginout
                        WHERE id_user = ?d ORDER by idLog DESC LIMIT 10", $uid);

foreach ($q as $result) {
    $when = $result->when;
    $action = $result->action;

    $tool_content .= "
                      <li class='list-group-item'>
                        <div class='row'>
                          <div class='col-sm-8'><b>" . strftime("%d/%m/%Y (%H:%M:%S) ", strtotime($when)) . "</b></div>
                          <div class='col-sm-4 text-muted'>" . $act[$action] . "</div>
                        </div>
                      </li>";
}
$tool_content .= "
                    </ul>
                  </div>
                </div>";

draw($tool_content, 1, null, $head_content);
