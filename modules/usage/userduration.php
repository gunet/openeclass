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
 * @file userduration.php
 * @brief Shows logins made by a user or all users of a course, during a specific period.
 * Takes data from table 'logins'
 */
$require_current_course = true;
$require_course_admin = true;
$require_help = true;
$helpTopic = 'Usage';
$require_login = true;

require_once '../../include/baseTheme.php';
require_once 'duration_query.php';
require_once 'modules/group/group_functions.php';
require_once 'statistics_tools_bar.php';

if (isset($_GET['format']) and $_GET['format'] == 'csv') {
    $format = 'csv';

    if (isset($_GET['enc']) and $_GET['enc'] == '1253') {
        $charset = 'Windows-1253';
    } else {
        $charset = 'UTF-8';
    }
    $crlf = "\r\n";

    header("Content-Type: text/csv; charset=$charset");
    header("Content-Disposition: attachment; filename=usersduration.csv");

    echo join(';', array_map("csv_escape", array($langSurnameName, $langAm, $langGroup, $langDuration))),
    $crlf, $crlf;
} else {
    $format = 'html';
    $toolName = $langUsage;
    $pageName = $langUserDuration;
    $navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langUsage);
    statistics_tools($course_code, "userduration");

    // display number of users
    $tool_content .= "
        <div class='alert alert-info'>
           <b>$langDumpUserDurationToFile: </b>1. <a href='userduration.php?course=$course_code&amp;format=csv'>$langcsvenc2</a>
                2. <a href='userduration.php?course=$course_code&amp;format=csv&amp;enc=1253'>$langcsvenc1</a>
          </div>";

    $tool_content .= "
        <table class='table-default'>
        <tr>
          <th>$langSurname $langName</th>
          <th>$langAm</th>
          <th>$langGroup</th>
          <th>$langDuration</th>
        </tr>";
}

$result = user_duration_query($course_id);
if (count($result) > 0) {
    $i = 0;
    foreach ($result as $row) {
        $i++;
        $grp_name = user_groups($course_id, $row->id, $format);
        if ($format == 'html') {
            if ($i % 2 == 0) {
                $tool_content .= "<tr class='even'>";
            } else {
                $tool_content .= "<tr class='odd'>";
            }
            $tool_content .= "<td class='bullet'>" . display_user($row->id) . "</td>
                                <td class='center'>$row->am</td>
                                <td class='center'>$grp_name</td>
                                <td class='center'>" . format_time_duration(0 + $row->duration) . "</td>
                                </tr>";
        } else {
            echo csv_escape($row->surname . ' ' . $row->givenname), ';',
            csv_escape($row->am), ';',
            csv_escape($grp_name), ';',
            csv_escape(format_time_duration(0 + $row->duration)), $crlf;
        }
    }
    if ($format == 'html') {
        $tool_content .= "</table>";
    }
}

if ($format == 'html') {
    draw($tool_content, 2);
}
