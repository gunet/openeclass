<?php
/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

$require_login = true;
$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'modules/request/functions.php';

global $course_id;

$toolName = $langStickyNotes . ' - ' . $langStickyNotesTopics;
$backUrl = $urlAppend . 'modules/sticky_notes/index.php?course=' . $course_code;


if (isset($_GET['topic'])) {
    $id = intval($_GET['topic']);

    $topic = Database::get()->querySingle(
        'SELECT * FROM sticky_notes_topic
        WHERE id = ?d AND course_id = ?d',
        $id,
        $course_id
    );

    if (!$topic) {
        redirect_to_home_page($backUrl, true);
    }

    $data['topic'] = $topic;
    $data['backUrl'] = $backUrl;
    $data['targetUrl'] = $backUrl . '&id=' . $id;

    $data['action_bar'] = action_bar([
        [
            'title' => $langBack,
            'url' => $backUrl,
            'icon' => 'fa-reply',
            'level' => 'primary'
        ]
    ], false);

    $navigation[] = array('url' => $backUrl, 'name' => $langRequests);
    $pageName = $topic->title;

    view('modules.sticky_notes.show_topic', $data);
} else {
    load_js('datatables');
    $data['action_bar'] = action_bar([
        [
            'title' => $langNewTopic,
            'url' => 'new_topic.php?course=' . $course_code,
            'icon' => 'fa-plus-circle',
            'button-class' => 'btn-success',
            'level' => 'primary-label'
        ]
    ], false);

    $data['topics'] = $topics = Database::get()->queryArray("
        SELECT 
            t.id, 
            t.title, 
            t.description, 
            t.created_at, 
            COUNT(p.id) AS posts
        FROM sticky_notes_topic t
        LEFT JOIN sticky_notes_post p ON p.topic_id = t.id
        WHERE t.is_active = 1 AND t.course_id = ?d
        GROUP BY t.id, t.title, t.description, t.created_at
    ", $course_id);

    view('modules.sticky_notes.index', $data);
}
