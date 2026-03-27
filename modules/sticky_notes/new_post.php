<?php

$require_login = true;
$require_current_course = true;

require_once '../../include/baseTheme.php';

$backUrl  = $urlAppend . 'modules/sticky_notes/index.php?course=' . $course_code;
$isEdit   = isset($_POST['post_id']) && intval($_POST['post_id']) > 0;
$toolName = $isEdit ? $langEditStickyNote : $langNewStickyNote;
?>
<link rel="stylesheet" type="text/css" href="<?= $urlServer; ?>/modules/sticky_notes/style.css" />
<script type="text/javascript" src="<?= $urlServer; ?>modules/sticky_notes/script.js"></script>
<?php

$availableColors = [
    '#fff9c4' => $langStickyColorYellow,
    '#c8f7c5' => $langStickyColorGreen,
    '#aed6f1' => $langStickyColorBlue,
    '#f9d5d3' => $langStickyColorRed,
    '#e8daef' => $langStickyColorPurple,
    '#fce5cd' => $langStickyColorOrange,
    '#f5f5f5' => $langStickyColorWhite,
    '#d7ccc8' => $langStickyColorBrown,
];

$topicId = isset($_POST['topic_id']) ? intval($_POST['topic_id']) : (isset($_GET['topic']) ? intval($_GET['topic']) : 0);

if (!$topicId) {
    redirect_to_home_page($backUrl, true);
}

$topic = Database::get()->querySingle(
    'SELECT * FROM sticky_notes_topic WHERE id = ?d AND course_id = ?d',
    $topicId,
    $course_id
);

if (!$topic) {
    redirect_to_home_page($backUrl, true);
}

$topicUrl = $backUrl . '&topic=' . $topicId;

if (isset($_POST['content'])) {
    $content    = canonicalize_whitespace($_POST['content']);
    $color      = isset($_POST['color']) && array_key_exists(trim($_POST['color']), $availableColors)
        ? trim($_POST['color'])
        : '#fff9c4';
    $categoryId = isset($_POST['category_id']) && intval($_POST['category_id']) > 0
        ? intval($_POST['category_id'])
        : null;

    if ($content) {
        if ($isEdit) {
            $postId = intval($_POST['post_id']);

            $existing = Database::get()->querySingle(
                'SELECT * FROM sticky_notes_post WHERE id = ?d AND topic_id = ?d',
                $postId,
                $topicId
            );

            if (!$existing || (!$is_editor && $existing->user_id != $uid)) {
                Session::flash('message', trans('langUnauthorized'));
                Session::flash('alert-class', 'alert-danger');
                redirect_to_home_page($topicUrl, true);
            }

            if (!$is_editor && !$topic->allow_edit) {
                Session::flash('message', trans('langUnauthorized'));
                Session::flash('alert-class', 'alert-danger');
                redirect_to_home_page($topicUrl, true);
            }

            if ($categoryId === null) {
                Database::get()->query(
                    'UPDATE sticky_notes_post
                     SET content = ?s, color = ?s, category_id = NULL, updated_at = NOW()
                     WHERE id = ?d',
                    $content,
                    $color,
                    $postId
                );
            } else {
                Database::get()->query(
                    'UPDATE sticky_notes_post
                     SET content = ?s, color = ?s, category_id = ?d, updated_at = NOW()
                     WHERE id = ?d',
                    $content,
                    $color,
                    $categoryId,
                    $postId
                );
            }

            Session::flash('message', trans('langStickyNotesPostUpdated'));
            Session::flash('alert-class', 'alert-success');
        } else {
            if ($categoryId === null) {
                Database::get()->query(
                    'INSERT INTO sticky_notes_post (topic_id, category_id, content, user_id, color, created_at, updated_at)
                     VALUES (?d, NULL, ?s, ?d, ?s, NOW(), NOW())',
                    $topicId,
                    $content,
                    $uid,
                    $color
                );
            } else {
                Database::get()->query(
                    'INSERT INTO sticky_notes_post (topic_id, category_id, content, user_id, color, created_at, updated_at)
                     VALUES (?d, ?d, ?s, ?d, ?s, NOW(), NOW())',
                    $topicId,
                    $categoryId,
                    $content,
                    $uid,
                    $color
                );
            }

            Session::flash('message', trans('langStickyNotesPostCreated'));
            Session::flash('alert-class', 'alert-success');
        }

        redirect_to_home_page($topicUrl, true);
    } else {
        Session::flash('message', trans('langFieldsRequ'));
        Session::flash('alert-class', 'alert-warning');
    }
}

$data['post'] = null;
$getPostId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!isset($_POST['content']) && $getPostId > 0) {
    $data['post'] = Database::get()->querySingle(
        'SELECT * FROM sticky_notes_post WHERE id = ?d AND topic_id = ?d',
        $getPostId,
        $topicId
    );

    if (!$data['post']) {
        Session::flash('message', trans('langStickyNotesPostNotFound'));
        Session::flash('alert-class', 'alert-warning');
        redirect_to_home_page($topicUrl, true);
    }

    if (!$is_editor && $data['post']->user_id != $uid) {
        Session::flash('message', trans('langUnauthorized'));
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page($topicUrl, true);
    }

    if (!$is_editor && !$topic->allow_edit) {
        Session::flash('message', trans('langUnauthorized'));
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page($topicUrl, true);
    }
}

$data['categories'] = $topic->has_categories
    ? Database::get()->queryArray(
        'SELECT * FROM sticky_notes_category WHERE topic_id = ?d ORDER BY sort_order',
        $topicId
    )
    : [];

$navigation[] = array('url' => $backUrl,  'name' => $langStickyNotes);
$navigation[] = array('url' => $topicUrl, 'name' => $topic->title);

$data['action_bar'] = action_bar([
    [
        'title' => $langBack,
        'url'   => $topicUrl,
        'icon'  => 'fa-reply',
        'level' => 'primary'
    ]
], false);

$data['isEdit']          = $isEdit || $getPostId > 0;
$data['topic']           = $topic;
$data['topicId']         = $topicId;
$data['availableColors'] = $availableColors;
$data['creatorName']     = $_SESSION['givenname'] . ' ' . $_SESSION['surname'];
$data['backUrl']         = $backUrl;
$data['targetUrl']       = $urlAppend . 'modules/sticky_notes/new_post.php?course=' . $course_code . '&topic=' . $topicId;

view('modules.sticky_notes.new_post', $data);
