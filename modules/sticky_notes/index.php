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
?>
<link rel="stylesheet" type="text/css" href="<?= $urlServer; ?>/modules/sticky_notes/style.css" />
<script type="text/javascript" src="<?= $urlServer; ?>modules/sticky_notes/script.js"></script>
<?php

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
    $data['hasCategories'] = (bool) $topic->has_categories;

    if ($topic->has_categories) {
        $categories = Database::get()->queryArray(
            'SELECT * FROM sticky_notes_category WHERE topic_id = ?d ORDER BY sort_order',
            $id
        );

        foreach ($categories as $cat) {
            $cat->posts = Database::get()->queryArray(
                'SELECT p.*, u.givenname, u.surname
             FROM sticky_notes_post p
             LEFT JOIN user u ON u.id = p.user_id
             WHERE p.topic_id = ?d AND p.category_id = ?d
             ORDER BY p.created_at DESC',
                $id,
                $cat->id
            );
        }

        $data['categories'] = $categories;
        $data['uncategorized'] = Database::get()->queryArray(
            'SELECT p.*, u.givenname, u.surname
         FROM sticky_notes_post p
         LEFT JOIN user u ON u.id = p.user_id
         WHERE p.topic_id = ?d AND p.category_id IS NULL
         ORDER BY p.created_at DESC',
            $id
        );

        $data['posts']       = [];
        $data['totalPosts']  = 0;
        $data['totalPages']  = 1;
        $data['currentPage'] = 1;
    } else {
        $perPage     = $topic->per_page ?? 20;
        $currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $offset      = ($currentPage - 1) * $perPage;

        $totalPosts = Database::get()->querySingle(
            'SELECT COUNT(*) AS cnt FROM sticky_notes_post WHERE topic_id = ?d',
            $id
        )->cnt;

        $data['posts'] = Database::get()->queryArray(
            'SELECT p.*, u.givenname, u.surname
         FROM sticky_notes_post p
         LEFT JOIN user u ON u.id = p.user_id
         WHERE p.topic_id = ?d
         ORDER BY p.created_at DESC
         LIMIT ?d OFFSET ?d',
            $id,
            $perPage,
            $offset
        );

        $data['totalPosts']  = $totalPosts;
        $data['totalPages']  = ceil($totalPosts / $perPage);
        $data['currentPage'] = $currentPage;
        $data['categories']  = [];
        $data['uncategorized'] = [];
    }

    $data['paginationUrl'] = $backUrl . '&topic=' . $id;

    $data['action_bar'] = action_bar([
        [
            'title' => $langBack,
            'url'   => $backUrl,
            'icon'  => 'fa-reply',
            'level' => 'primary'
        ],
        [
            'title'        => $langNewStickyNote,
            'url'          => 'new_post.php?course=' . $course_code . '&topic=' . $id,
            'icon'         => 'fa-plus-circle',
            'button-class' => 'btn-success',
            'level'        => 'primary-label'
        ]
    ], false);

    $navigation[] = array('url' => $backUrl, 'name' => $langRequests);
    $pageName = $topic->title;

    $data['ajaxUrl'] = $urlAppend . 'modules/sticky_notes/ajax-sticky.php?course=' . $course_code;
    $data['isDraggable'] = $is_editor ? 'true' : 'false';

    $data['allowEdit']   = $is_editor || $topic->allow_edit;
    $data['allowDelete'] = $is_editor || $topic->allow_delete;

    $data['lang'] = [
        'moved'         => $langStickyNotesMoved,
        'moveError'     => $langStickyNotesMoveError,
        'connError'     => $langStickyNotesConnError,
        'deleted'       => $langStickyNotesDeleted,
        'deleteError'   => $langStickyNotesDeleteError,
        'confirmDelete' => $langStickyNotesConfirmDelete,
    ];

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
