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
?>
<script type="text/javascript" src="<?= $urlServer; ?>modules/sticky_notes/script.js"></script>
<?php

if (isset($_POST['topicTitle'])) {
    $title = canonicalize_whitespace($_POST['topicTitle']);
    $topicDescription = canonicalize_whitespace($_POST['topicDescription']);
    $allowEdit = isset($_POST['allow_edit']) ? 1 : 0;
    $allowDelete = isset($_POST['allow_delete']) ? 1 : 0;
    $hasCategories = isset($_POST['has_categories']) ? 1 : 0;
    $perPage = isset($_POST['per_page']) ? intval($_POST['per_page']) : 20;
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($title) {
        if ($isEdit) {
            $topic_id = intval($_POST['topic_id']);

            $result = Database::get()->query(
                'UPDATE sticky_notes_topic
                 SET title = ?s,
                     description = ?s,
                     allow_edit = ?d,
                     allow_delete = ?d,
                     has_categories = ?d,
                     per_page = ?d,
                     is_active = ?d
                 WHERE id = ?d AND course_id = ?d',
                $title,
                $topicDescription,
                $allowEdit,
                $allowDelete,
                $hasCategories,
                $perPage,
                $isActive,
                $topic_id,
                $course_id
            );

            if ($result) {
                if ($hasCategories && isset($_POST['category_title'])) {
                    _saveCategoriesForTopic($topic_id, $_POST);
                } elseif (!$hasCategories) {
                    Database::get()->query(
                        'DELETE FROM sticky_notes_category WHERE topic_id = ?d',
                        $topic_id
                    );
                    Database::get()->query(
                        'UPDATE sticky_notes_post SET category_id = NULL WHERE topic_id = ?d',
                        $topic_id
                    );
                }

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
                SET course_id = ?d,
                    title = ?s,
                    description = ?s,
                    allow_edit = ?d,
                    allow_delete = ?d,
                    has_categories = ?d,
                    per_page = ?d,
                    is_active = ?d,
                    created_at = NOW(),
                    created_by = ?d',
                $course_id,
                $title,
                $topicDescription,
                $allowEdit,
                $allowDelete,
                $hasCategories,
                $perPage,
                $isActive,
                $uid
            );

            if ($result) {
                $topic_id = $result->lastInsertID;

                if ($hasCategories && isset($_POST['category_title'])) {
                    _saveCategoriesForTopic($topic_id, $_POST);
                }

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

function _saveCategoriesForTopic(int $topic_id, array $post): void
{
    $titles = $post['category_title'] ?? [];
    $catIds = $post['category_id']    ?? [];
    $sorts = $post['category_sort'] ?? [];


    $submittedIds = array_filter(array_map('intval', $catIds));

    if (!empty($submittedIds)) {
        $placeholders = implode(',', array_fill(0, count($submittedIds), '?d'));
        Database::get()->query(
            "DELETE FROM sticky_notes_category
             WHERE topic_id = ?d AND id NOT IN ($placeholders)",
            $topic_id,
            ...$submittedIds
        );
    } else {
        Database::get()->query(
            'DELETE FROM sticky_notes_category WHERE topic_id = ?d',
            $topic_id
        );
    }

    foreach ($titles as $i => $catTitle) {
        $catTitle  = trim($catTitle);
        if (!$catTitle) continue;

        $sortOrder = isset($sorts[$i]) ? intval($sorts[$i]) : $i;
        $catId     = !empty($catIds[$i]) ? intval($catIds[$i]) : 0;

        if ($catId > 0) {
            Database::get()->query(
                'UPDATE sticky_notes_category
             SET title = ?s, sort_order = ?d
             WHERE id = ?d AND topic_id = ?d',
                $catTitle,
                $sortOrder,
                $catId,
                $topic_id
            );
        } else {
            Database::get()->query(
                'INSERT INTO sticky_notes_category (topic_id, title, sort_order, created_at)
             VALUES (?d, ?s, ?d, NOW())',
                $topic_id,
                $catTitle,
                $sortOrder
            );
        }
    }
}

$data['topic'] = null;
$data['categories'] = [];
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

    $data['categories'] = Database::get()->queryArray(
        'SELECT * FROM sticky_notes_category WHERE topic_id = ?d ORDER BY sort_order',
        $getTopicId
    );
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
