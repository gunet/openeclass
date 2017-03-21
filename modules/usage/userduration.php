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
require_once 'modules/group/group_functions.php';
require_once 'include/lib/csv.class.php';

if (isset($_GET['format']) and $_GET['format'] == 'csv') {
    $format = 'csv';
    $csv = new CSV();
    if (isset($_GET['enc']) and $_GET['enc'] == 'UTF-8') {
        $csv->setEncoding('UTF-8');
    }
    $csv->filename = $course_code . '_user_duration.csv';

    $csv->outputRecord($langSurname, $langName, $langAm, $langGroup, $langDuration);
} else {
    $format = 'html';
    $toolName = $langUserDuration;

    $navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langUsage);

    $tool_content .= action_bar(array(
        array('title' => $langUsage,
            'url' => "index.php?course=$course_code",
            'icon' => 'fa-bar-chart',
            'level' => 'primary-label'),
        array('title' => $langGlossaryToCsv,
            'url' => "userduration.php?course=$course_code&amp;format=csv",
            'icon' => 'fa-download',
            'level' => 'primary-label'),
        array('title' => $langBack,
            'url' => "../../courses/{$course_code}/",
            'icon' => 'fa-reply',
            'level' => 'primary-label')
    ),false);

    $tool_content .= "
        <table class='table-default'>
        <tr>
          <th>$langSurnameName</th>
          <th>$langAm</th>
          <th>$langGroup</th>
          <th>$langDuration</th>
        </tr>";
}

$result = user_duration_query($course_id);
if (count($result) > 0) {
    foreach ($result as $row) {
        $grp_name = user_groups($course_id, $row->id, $format);
        if ($format == 'html') {
            $tool_content .= "<td class='bullet'>" . display_user($row->id) . "</td>
                                <td class='center'>$row->am</td>
                                <td class='center'>$grp_name</td>
                                <td class='center'>" . format_time_duration(0 + $row->duration) . "</td>
                                </tr>";
        } else {
            $csv->outputRecord($row->surname, $row->givenname,
                $row->am, $grp_name, format_time_duration(0 + $row->duration));
        }
    }
    if ($format == 'html') {
        $tool_content .= "</table>";
    }
}

if ($format == 'html') {
    draw($tool_content, 2);
}


/**
 * @brief Do the queries to calculate usage between timestamps $start and $end
 * @param type $course_id
 * @param type $start
 * @param type $end
 * @param type $group
 * @return returns a MySQL resource, where fetching rows results in duration, surname, givenname, user_id, am
 */
function user_duration_query($course_id, $start = false, $end = false, $group = false) {
    $terms = array();
    if ($start !== false AND $end !== false) {
        $date_where = 'AND actions_daily.day BETWEEN ?s AND ?s';
        $terms = array($start . ' 00:00:00',
                       $end . ' 23:59:59');
    } elseif ($start !== false) {
        $date_where = 'AND actions_daily.day > ?s';
        $terms[] = $start . ' 00:00:00';
    } elseif ($end !== false) {
        $date_where = 'AND actions_daily.day < ?s';
        $terms[] = $end . ' 23:59:59';
    } else {
        $date_where = '';
    }

    if ($group !== false) {
        $from = "`group_members` AS groups
                                LEFT JOIN user ON groups.user_id = user.id";
        $and = "AND groups.group_id = ?d";
        $terms[] = $group;
    } else {
        $from = " (SELECT
                            id, surname, givenname, username, password, email, parent_email, status, phone, am,
                            registered_at, expires_at, lang, description, has_icon, verified_mail, receive_mail, email_public,
                            phone_public, am_public, whitelist, last_passreminder
                          FROM user UNION (SELECT 0 as id,
                            '' as surname,
                            'Anonymous' as givenname,
                            null as username,
                            null as password,
                            null as email,
                            null as parent_email,
                            null as status,
                            null as phone,
                            null as am,
                            null as registered_at,
                            null as expires_at,
                            null as lang,
                            null as description,
                            null as has_icon,
                            null as verified_mail,
                            null as receive_mail,
                            null as email_public,
                            null as phone_public,
                            null as am_public,
                            null as whitelist,
                            null as last_passreminder)) as user ";
        $and = '';
    }

    return Database::get()->queryArray("SELECT SUM(actions_daily.duration) AS duration,
                                   user.surname AS surname,
                                   user.givenname AS givenname,
                                   user.id AS id,
                                   user.am AS am
                            FROM $from
                            LEFT JOIN actions_daily ON user.id = actions_daily.user_id
                            WHERE (actions_daily.course_id = ?d)
                            $and
                            $date_where
                            GROUP BY user.id, surname, givenname, am                          
                            ORDER BY surname, givenname",  $course_id, $terms);
}
