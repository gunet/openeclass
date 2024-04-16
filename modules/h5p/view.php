<?php
/*
 * ========================================================================
 * Open eClass 3.12 - E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2021  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
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
 *
 * For a full list of contributors, see "credits.txt".
 */

$require_current_course = true;
$guest_allowed = true;
require_once '../../include/baseTheme.php';

$unit = isset($_GET['unit'])? intval($_GET['unit']): null;
$res_type = isset($_GET['res_type']);
$content = Database::get()->querySingle("SELECT * FROM h5p_content WHERE course_id = ?d AND id = ?d", $course_id, $_GET['id']);

if ($content) {
    if (showContent($_GET['id'])) {
        if (isset($unit)) {
            redirect($urlServer . 'modules/units/view.php?course=' . $course_code . '&res_type=h5p_show&unit=' . $unit . '&id=' . intval($_GET['id']));;
        } else {
            redirect($urlAppend . 'modules/h5p/show.php?course=' . $course_code . '&id=' . intval($_GET['id']));
        }
    }
} else {
    redirect($urlAppend . 'modules/h5p/?course=' . $course_code);
}

/**
 * @brief show h5p content
 * @param $contentId
 * @return bool
 */
function showContent($contentId): bool {
    global $course_code, $webDir;

    $content_dir = $webDir . "/courses/" . $course_code . "/h5p/content/" . $contentId;
    $workspace_dir = $content_dir . "/workspace";
    if (file_exists($workspace_dir)) {
        return true;
    } else {
        return false;
    }
}
