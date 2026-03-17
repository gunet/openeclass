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

$isEdit = isset($_POST['topic_id']) && intval($_POST['topic_id']) > 0;
$toolName = $isEdit ? $langEditTopic : $langNewTopic;

if (isset($_POST['topicTitle'])) {
    $title = canonicalize_whitespace($_POST['topicTitle']);
    $topicDescription = canonicalize_whitespace($_POST['topicDescription']);
    $allowEdit = isset($_POST['allow_edit']) ? 1 : 0;
    $allowDelete = isset($_POST['allow_delete']) ? 1 : 0;

    if ($title) {
        if ($isEdit) {
            $topic_id = intval($_POST['topic_id']);

            $result = Database::get()->query(
                'UPDATE sticky_notes_topic
                SET
                    title = ?s,
                    description = ?s,
                    allow_edit = ?d,
                    allow_delete = ?d
                WHERE id = ?d AND course_id = ?d',
                $title,
                $topicDescription,
                $allowEdit,
                $allowDelete,
                $topic_id,
                $course_id
            );

            if ($result) {
                Session::flash('message', trans('langStickyNotesTopicUpdated'));
                Session::flash('alert-class', 'alert-success');
                redirect_to_home_page("modules/sticky_notes/index.php?course=$course_code");
            } else {
                Session::flash('message', trans('langStickyNotesTopicFailed'));
                Session::flash('alert-class', 'alert-error');
            }
        } else {
            $result = Database::get()->query(
                'INSERT INTO sticky_notes_topic
                SET
                    course_id = ?d,
                    title = ?s,
                    description = ?s,
                    allow_edit = ?d,
                    allow_delete = ?d,
                    created_at = NOW(),
                    created_by = ?d',
                $course_id,
                $title,
                $topicDescription,
                $allowEdit,
                $allowDelete,
                $uid
            );

            if ($result) {
                $rid = $result->lastInsertID;

                Session::flash('message', trans('langStickyNotesTopicCreated'));
                Session::flash('alert-class', 'alert-success');
                redirect_to_home_page("modules/sticky_notes/index.php?course=$course_code");
            } else {
                Session::flash('message', trans('langStickyNotesTopicFailed'));
                Session::flash('alert-class', 'alert-error');
            }
        }
    } else {
        Session::flash('message', trans('langFieldsRequ'));
        Session::flash('alert-class', 'alert-warning');
    }
}

// Pre-populate form data when editing via GET ?id=
$data['topic'] = null;
$getTopicId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!isset($_POST['topicTitle']) && $getTopicId > 0) {
    $data['topic'] = Database::get()->querySingle(
        'SELECT * FROM sticky_notes_topic WHERE id = ?d AND course_id = ?d',
        $getTopicId,
        $course_id
    );

    if (!$data['topic']) {
        Session::flash('message', trans('langStickyNotesTopicNotFound'));
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page("modules/sticky_notes/index.php?course=$course_code");
    }
}

$backUrl = $urlAppend . 'modules/sticky_notes/index.php?course=' . $course_code;
$navigation[] = array('url' => $backUrl, 'name' => $langStickyNotesTopics);

$data['action_bar'] = action_bar(
    [
        [
            'title' => $langBack,
            'url' => $backUrl,
            'icon' => 'fa-reply',
            'level' => 'primary'
        ]
    ],
    false
);

$data['isEdit'] = $isEdit || $getTopicId > 0;
$data['creatorName'] = $_SESSION['givenname'] . ' ' . $_SESSION['surname'];
$data['backUrl'] = $backUrl;
$data['targetUrl'] = $urlAppend . 'modules/sticky_notes/new_topic.php?course=' . $course_code;

view('modules.sticky_notes.new_topic', $data);
