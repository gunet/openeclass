<?php

/* ========================================================================
 * Open eClass 3.14
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2023  Greek Universities Network - GUnet
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

$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/usage/usage.lib.php';

$toolName = $langMonthlyReport;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array("url" => "../usage/index.php?t=a", "name" => $langUsage);

$tool_content .= action_bar(array(
                array('title' => $langBack,
                    'url' => "../usage/index.php?t=a",
                    'icon' => 'fa-reply',
                    'level' => 'primary-label')));

$tool_content .= "<div class='alert alert-info'>$langMonthlyReportInfo</div>";
$tool_content .= "<table class='table-default'><tbody>";
$tool_content .= "<th class='list-header'>$langMonth</th>
                    <th class='list-header text-center'>$langTeachers</th>
                    <th class='list-header text-center'>$langStudents</th>
                    <th class='list-header text-center'>$langGuests</th>
                    <th class='list-header text-center'>$langCourses</th>";

$monthly_data = get_monthly_archives();

foreach ($monthly_data as $data) {
    $formatted_data = date_format(date_create($data[0]), "n / Y");
    $tool_content .= "<tr>";
    $tool_content .= "<td>$formatted_data</td>";
    $tool_content .= "<td class='text-center'>$data[1]</td>";
    $tool_content .= "<td class='text-center'>$data[2]</td>";
    $tool_content .= "<td class='text-center'>$data[3]</td>";
    $tool_content .= "<td class='text-center'>$data[4]</td>";
    $tool_content .= "</tr>";
}

$tool_content .= "</tbody></table>";

draw($tool_content, 3, 'admin');
