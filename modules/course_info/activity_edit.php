<?php
/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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
 * @file: activity_edit.php
 * @brief: edit page for activity-type courses
 */
$require_current_course = true;
$require_editor = true;
require_once '../../include/baseTheme.php';
$toolName = $langActivityEdit;

$course_info = Database::get()->querySingle('SELECT view_type FROM course WHERE id = ?d', $course_id);

if ($course_info->view_type != 'activity') {
    Session::Messages($langGeneralError, 'alert-danger');
    redirect_to_home_page('courses/' . $course_code . '/');
}

if (isset($_POST['submit']) and isset($_POST['content'])) {
    foreach ($_POST['content'] as $id => $content) {
        echo "$id<br>";
        $content = purify($content);
        Database::get()->query('INSERT INTO activity_content
            (heading_id, course_id, content) VALUES (?d, ?d, ?s)
            ON DUPLICATE KEY UPDATE content = VALUES(content)',
            $id, $course_id, $content);
    }
    Session::Messages($langFaqEditSuccess, 'alert-success');
    redirect_to_home_page('courses/' . $course_code . '/');
}

$courseHome = "{$urlAppend}courses/$course_code/";
$items = Database::get()->queryArray("SELECT activity_content.id, activity_heading.id AS heading_id, heading, content, required
    FROM activity_heading
        LEFT JOIN activity_content
            ON activity_heading.id = activity_content.heading_id
    WHERE course_id = ?d OR course_id IS NULL ORDER BY `order`", $course_id);

$tool_content .= action_bar(array(
    array('title' => $langBack,
    'url' => $courseHome,
    'icon' => 'fa-reply',
    'level' => 'primary-label')), false);

$tool_content .= "
<form method='post' action='activity_edit.php?course=$course_code'>
    <div class='col-md-12'>
        <div class='row'>
            <div class='col-md-12'>";
foreach ($items as $item) {
    $tool_content .= "
                <div class='panel clearfix'>
                    <div class='panel-heading h4'>" . q(getSerializedMessage($item->heading)) . "</div>
                    <div class='panel-body'>" .
                        rich_text_editor("content[{$item->heading_id}]", 5, 40, $item->content, true) . "</div>";
    $resources = Database::get()->queryArray("SELECT * FROM unit_resources
            WHERE unit_id = ?d AND `order` >= 0 ORDER BY `order`", $item->id);
    if (count($resources > 0)) {
        $tool_content .= "
                    <div class='table-responsive'>
                        <table class='table table-striped table-hover'>
                            <tbody>";
        foreach ($resources as $info) {
            $info->comments = standard_text_escape($info->comments);
            show_resourceWeek($info);
        }
        $tool_content .= "
                            </tbody>
                        </table>
                    </div>";
    }
    $tool_content .= "
                </div>";
}
$tool_content .= "
            </div>
        </div>
        <div class='row'>
            <div class='col-sm-12'>
                <input class='btn btn-primary' type='submit' name='submit' value='" . q($langSubmit) . "'>
                <a href='" . q($courseHome) . "' class='btn btn-default'>" . q($langCancel) . "</a>
            </div>
        </div>
    </div>
</form>
";

draw($tool_content, 2, null, $head_content);

