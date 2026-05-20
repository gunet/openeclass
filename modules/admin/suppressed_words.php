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

$require_admin = true;
require_once '../../include/baseTheme.php';

load_js('tools.js');
load_js('datatables');
load_js('bootbox');

// Handle bulk import
if (isset($_POST['submit_bulk'])) {
    if (validate_csrf_token($_POST['token'])) {
        $bulk_words = $_POST['bulk_words'] ?? '';
        $words = explode("\n", $bulk_words);
        $count = 0;
        foreach ($words as $word) {
            $word = trim($word);
            if (!empty($word)) {
                // Check if word already exists
                $exists = Database::get()->querySingle("SELECT COUNT(*) as count FROM suppressed_words WHERE word = ?s", $word)->count;
                if (!$exists) {
                    Database::get()->query("INSERT INTO suppressed_words (word, added_by, created_at) VALUES (?s, ?d, NOW())", $word, $uid);
                    $count++;
                }
            }
        }
        if ($count > 0) {
            Session::Messages($langAddSuccess, 'alert-success');
        } else {
            Session::Messages($langNoResult, 'alert-warning');
        }
    } else {
        Session::Messages($langNoAuthorization, 'alert-danger');
    }
    redirect($_SERVER['SCRIPT_NAME']);
}

// Handle AJAX request from DataTables
if (isset($_GET['ajax'])) {
    $draw = intval($_POST['draw'] ?? 0);
    $start = intval($_POST['start'] ?? 0);
    $length = intval($_POST['length'] ?? 10);
    $search = $_POST['search']['value'] ?? '';

    $total_count = Database::get()->querySingle("SELECT COUNT(*) as count FROM suppressed_words")->count;

    $params = [];
    $where = "";
    if (!empty($search)) {
        $where = " WHERE sw.word LIKE ?s OR u.username LIKE ?s OR u.surname LIKE ?s OR u.givenname LIKE ?s";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $filtered_count = Database::get()->querySingle("
        SELECT COUNT(*) as count 
        FROM suppressed_words sw 
        LEFT JOIN user u ON sw.added_by = u.id 
        $where", $params)->count;

    $sql = "SELECT sw.id, sw.word, sw.created_at, u.username, u.givenname, u.surname
            FROM suppressed_words sw
            LEFT JOIN user u ON sw.added_by = u.id
            $where";
    
    // Ordering
    $columns = ['sw.word', 'u.surname', 'sw.created_at'];
    if (isset($_POST['order'][0]['column'])) {
        $col_idx = intval($_POST['order'][0]['column']);
        $col_dir = $_POST['order'][0]['dir'] === 'asc' ? 'ASC' : 'DESC';
        if (isset($columns[$col_idx])) {
            $sql .= " ORDER BY " . $columns[$col_idx] . " " . $col_dir;
        }
    } else {
        $sql .= " ORDER BY sw.created_at DESC";
    }

    if ($length != -1) {
        $sql .= " LIMIT ?d, ?d";
        $params[] = $start;
        $params[] = $length;
    }

    $results = Database::get()->queryArray($sql, $params);

    $aaData = [];
    foreach ($results as $row) {
        $added_by = !empty($row->username) ? 
            htmlspecialchars($row->givenname . " " . $row->surname) . " (" . htmlspecialchars($row->username) . ")" : 
            "-";

        $delete_url = $_SERVER['SCRIPT_NAME'] . "?delete=" . $row->id;
        $token = $_SESSION['csrf_token'];
        $delete_html = "
            <form action='$delete_url' method='post' class='m-0'>
                <input type='hidden' name='token' value='$token'>
                <a href='#' class='delete confirmAction' style='display: inline-block; text-align: center; min-width: 20px' 
                   data-title='" . htmlspecialchars($langConfirmDelete) . "' 
                   data-message='" . htmlspecialchars($langConfirmDelete) . "' 
                   data-cancel-txt='" . htmlspecialchars($langCancel) . "' 
                   data-action-txt='" . htmlspecialchars($langDelete) . "' 
                   data-action-class='deleteAdminBtn'>
                    <i class='fa-solid fa-xmark'></i>
                </a>
            </form>";

        $aaData[] = [
            htmlspecialchars($row->word),
            $added_by,
            format_locale_date(strtotime($row->created_at), 'short'),
            "<div class='text-end'>$delete_html</div>"
        ];
    }

    header('Content-Type: application/json');
    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => $total_count,
        "recordsFiltered" => $filtered_count,
        "aaData" => $aaData
    ]);
    exit;
}

// Handle deletion
if (isset($_GET['delete'])) {
    if (isset($_REQUEST['token']) && validate_csrf_token($_REQUEST['token'])) {
        $id = intval($_GET['delete']);
        Database::get()->query("DELETE FROM suppressed_words WHERE id = ?d", $id);
        Session::Messages($langSuppressedwordDeleteSuccess, 'alert-success');
    } else {
        Session::Messages($langNoAuthorization, 'alert-danger');
    }
    redirect($_SERVER['SCRIPT_NAME']);
}

$toolName = "Suppressed Words";
if (isset($langSuppressedWords)) {
    $toolName = $langSuppressedWords;
}

$navigation[] = ['url' => 'index.php', 'name' => $langAdmin];

view('admin.other.suppressed_words', [
    'toolName' => $toolName
]);
