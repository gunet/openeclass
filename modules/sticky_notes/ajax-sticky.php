<?php
$require_login = true;
$require_current_course = true;
require_once '../../include/baseTheme.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$input  = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

if ($action === 'move_post') {
    if (!$is_editor) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    $postId     = intval($input['post_id'] ?? 0);
    $categoryId = isset($input['category_id']) && $input['category_id'] !== null
        ? intval($input['category_id'])
        : null;

    if (!$postId) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid post_id']);
        exit;
    }

    $post = Database::get()->querySingle(
        'SELECT p.id FROM sticky_notes_post p
         JOIN sticky_notes_topic t ON t.id = p.topic_id
         WHERE p.id = ?d AND t.course_id = ?d',
        $postId,
        $course_id
    );

    if (!$post) {
        echo json_encode(['status' => 'error', 'message' => 'Post not found']);
        exit;
    }

    if ($categoryId === null) {
        Database::get()->query(
            'UPDATE sticky_notes_post SET category_id = NULL, updated_at = NOW() WHERE id = ?d',
            $postId
        );
    } else {
        Database::get()->query(
            'UPDATE sticky_notes_post SET category_id = ?d, updated_at = NOW() WHERE id = ?d',
            $categoryId,
            $postId
        );
    }

    echo json_encode(['status' => 'ok']);
    exit;
}

if ($action === "delete_post") {
    $postId = intval($input['post_id'] ?? 0);

    if (!$postId) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid post_id']);
        exit;
    }

    $post = Database::get()->querySingle(
        'SELECT p.*, t.allow_delete, t.course_id
         FROM sticky_notes_post p
         JOIN sticky_notes_topic t ON t.id = p.topic_id
         WHERE p.id = ?d AND t.course_id = ?d',
        $postId,
        $course_id
    );

    if (!$post) {
        echo json_encode(['status' => 'error', 'message' => 'Not found']);
        exit;
    }

    if (!$is_editor && !$post->allow_delete) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    if (!$is_editor && $post->user_id != $uid) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }

    Database::get()->query('DELETE FROM sticky_notes_post WHERE id = ?d', $postId);

    echo json_encode(['status' => 'ok']);
    exit;
}


echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
