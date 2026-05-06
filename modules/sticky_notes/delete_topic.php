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
 */

$require_login = true;
$require_current_course = true;

require_once '../../include/baseTheme.php';

$backUrl = $urlAppend . 'modules/sticky_notes/index.php?course=' . $course_code;

// Validate id param
$topic_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($topic_id <= 0) {
    Session::flash('message', trans('$langStickyNotesTopicNotFound'));
    Session::flash('alert-class', 'alert-warning');
    redirect_to_home_page("modules/sticky_notes/index.php?course=$course_code");
}

// Verify the topic exists and belongs to this course
$topic = Database::get()->querySingle(
    'SELECT * FROM sticky_notes_topic WHERE id = ?d AND course_id = ?d',
    $topic_id,
    $course_id
);

if (!$topic) {
    Session::flash('message', trans('langStickyNotesTopicNotFound'));
    Session::flash('alert-class', 'alert-warning');
    redirect_to_home_page("modules/sticky_notes/index.php?course=$course_code");
}

// Delete posts belonging to the topic first, then the topic itself
Database::get()->query(
    'DELETE FROM sticky_notes_post WHERE topic_id = ?d',
    $topic_id
);

$result = Database::get()->query(
    'DELETE FROM sticky_notes_topic WHERE id = ?d AND course_id = ?d',
    $topic_id,
    $course_id
);

if ($result) {
    Session::flash('message', trans('langStickyNotesTopicDeleted'));
    Session::flash('alert-class', 'alert-success');
} else {
    Session::flash('message', trans('langStickyNotesTopicDeletionFailed'));
    Session::flash('alert-class', 'alert-error');
}

redirect_to_home_page("modules/sticky_notes/index.php?course=$course_code");
