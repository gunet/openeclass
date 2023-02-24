<?php

/* ========================================================================
 * Open eClass 2.14
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

/*
 * Display course page
 */

$require_current_course = true;
require_once '../../include/baseTheme.php';

if ($is_editor) {
    $page = Database::get()->querySingle('SELECT * FROM page
        WHERE course_id = ?d AND id = ?d', $course_id, $_GET['id']);
} else {
    $page = Database::get()->querySingle('SELECT * FROM page
        WHERE visible = 1 AND course_id = ?d AND id = ?d', $course_id, $_GET['id']);
}
if (!$page) {
    redirect_to_home_page("courses/$course_code/");
}
$content = standard_text_escape(file_get_contents("courses/$course_code/page/{$page->path}"));
$toolName = $page->title;

$tool_content = action_bar([
    [ 'title' => $langEdit,
      'show' => $is_admin,
      'url' => "{$urlAppend}modules/course_tools/?course=$course_code&amp;page={$page->id}&amp;return=true",
      'icon' => 'fa-edit',
      'level' => 'primary-label' ],
    [ 'title' => $langBack,
      'url' => "{$urlAppend}courses/$course_code/",
      'icon' => 'fa-reply',
      'level' => 'primary-label' ],
    ], false) . "
    <div class='row'>
        <div class='col-xs-12'>
            $content
        </div>
    </div>";

draw($tool_content, 2, null, $head_content);
