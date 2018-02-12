<?php
/* ========================================================================
 * Open eClass
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2018  Greek Universities Network - GUnet
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
 * ========================================================================
 */

$require_login = true;
$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'modules/request/functions.php';

$toolName = $langRequests;
$backUrl = $urlAppend . 'modules/request/?course=' . $course_code;

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $request = Database::get()->querySingle('SELECT * FROM request
        WHERE id = ?d AND course_id = ?d',
        $id, $course_id);
    if (!$request) {
        redirect_to_home_page($backUrl);
    }

    if ($request->type_id) {
        $data['type'] = Database::get()->querySingle('SELECT * FROM request_type
            WHERE id = ?d', $request->type_id);
        $data['field_data'] = Database::get()->queryArray('SELECT request_field.id AS field_id, name, data, datatype
            FROM request
                JOIN request_field ON request.type_id = request_field.type_id
                LEFT JOIN request_field_data ON request.id = request_field_data.request_id AND
                          request_field.id = request_field_data.field_id
            WHERE request.id = ?d ORDER BY sortorder', $request->id);
    } else {
        $data['field_data'] = null;
    }
    $data['request'] = $request;
    $data['watchers'] = getWatchers($id, REQUEST_WATCHER);
    $data['assigned'] = getWatchers($id, REQUEST_ASSIGNED);
    $data['backUrl'] = $backUrl;
    $data['targetUrl'] = $backUrl . '&id=' . $id;
    $can_modify = $is_editor || $request->creator_id == $uid ||
        in_array($uid, $data['assigned']);
    $can_comment = $can_modify || in_array($uid, $data['watchers']);
    $data['can_assign_to_self'] = !in_array($uid, $data['assigned']) &&
        ($is_editor ||
         (!$data['assigned'] && in_array($uid, $data['watchers'])));
    if (count($_POST) and !(isset($_POST['token']) and validate_csrf_token($_POST['token']))) {
        csrf_token_error();
    }

    if ($can_modify) {
        load_js('select2');
        $data['editUrl'] = $urlAppend . 'modules/request/edit.php?course=' . $course_code . '&id=' . $id;
        $data['course_users'] = Database::get()->queryArray("SELECT user_id,
                CONCAT(surname, ' ', givenname) name, email
            FROM course_user JOIN user ON user_id = user.id
            WHERE course_id = ?d
            ORDER BY surname, givenname", $course_id);
        $course_user_ids = array_map(function ($item) {
                return $item->user_id;
            }, $data['course_users']);

        if (isset($_POST['assignmentSubmit'])) {
            if (!isset($_POST['assignTo'])) {
                $_POST['assignTo'] = [];
            }
            if (array_diff($_POST['assignTo'], $course_user_ids)) {
                Session::Messages($langGeneralError, 'alert-danger');
                redirect_to_home_page($data['targetUrl']);
            }
            Database::get()->query('DELETE FROM request_watcher
                WHERE request_id = ?d AND type = ?d',
                $id, REQUEST_ASSIGNED);
            $args = array_map(function ($item) use ($id) {
                    return [$id, $item, REQUEST_ASSIGNED];
                }, $_POST['assignTo']);
            $placeholders = implode(', ', array_fill(0, count($_POST['assignTo']), '(?d, ?d, ?d)'));
            Database::get()->query("INSERT INTO request_watcher
                (request_id, user_id, type) VALUES $placeholders",
                $args);
            if ($request->state == REQUEST_STATE_ASSIGNED and count($args) == 0) {
                Database::get()->query('UPDATE request
                    SET state = ?d, change_date = NOW() WHERE id = ?d',
                    REQUEST_STATE_NEW, $id);
                $_POST['newState'] = REQUEST_STATE_NEW;
            } else {
                Database::get()->query('UPDATE request
                    SET change_date = NOW() WHERE id = ?d', $id);
            }
            $_POST['requestComment'] = sprintf(trans('langChangeAssignees'),
                formatUsers($_POST['assignTo']) . '<br>',
                formatUsers($data['assigned']));
        } elseif (isset($_POST['watchersSubmit'])) {
            if (!isset($_POST['watchers'])) {
                $_POST['watchers'] = [];
            }
            if (array_diff($_POST['watchers'], $course_user_ids)) {
                Session::Messages($langGeneralError, 'alert-danger');
                redirect_to_home_page($data['targetUrl']);
            }
            Database::get()->query('DELETE FROM request_watcher
                WHERE request_id = ?d AND type = ?d',
                $id, REQUEST_WATCHER);
            $args = array_map(function ($item) use ($id) {
                    return [$id, $item, REQUEST_WATCHER];
                }, $_POST['watchers']);
            $placeholders = implode(', ', array_fill(0, count($_POST['watchers']), '(?d, ?d, ?d)'));
            Database::get()->query("INSERT INTO request_watcher
                (request_id, user_id, type) VALUES $placeholders",
                $args);
            $_POST['requestComment'] = sprintf(trans('langChangeWatchers'),
                formatUsers($_POST['watchers']) . '<br>',
                formatUsers($data['watchers']));
        }
    }

    if ($data['can_assign_to_self'] and isset($_POST['assignToSelf'])) {
        Database::get()->query('DELETE FROM request_watcher
            WHERE request_id = ?d AND (user_id = ?d OR type = ?d)',
            $id, $uid, REQUEST_ASSIGNED);
        Database::get()->query('INSERT INTO request_watcher
            SET request_id = ?d, user_id = ?d, type = ?d',
            $id, $uid, REQUEST_ASSIGNED);
        if ($request->state == REQUEST_STATE_NEW) {
            Database::get()->query('UPDATE request
                SET state = ?d, change_date = NOW() WHERE id = ?d',
                REQUEST_STATE_ASSIGNED, $id);
        }
        $_POST['newState'] = REQUEST_STATE_ASSIGNED;
        $_POST['requestComment'] = sprintf(trans('langRequestTaken'),
            '<b>' . q("$_SESSION[givenname] $_SESSION[surname]") . '</b>');
    }

    if ($can_comment and isset($_POST['requestComment'])) {
        $comment = purify($_POST['requestComment']);
        $fileName = $filePath = null;
        if (isset($_FILES['requestFile']) and is_uploaded_file($_FILES['requestFile']['tmp_name'])) {
            validateUploadedFile($_FILES['requestFile']['name']);
            $workPath = $webDir . "/courses/" . $course_code . "/request";
            $filePath = safe_filename();
            if (!(is_dir($workPath) or make_dir($workPath))) {
                Session::Messages($langGeneralError, 'alert-danger');
                redirect_to_home_page($data['targetUrl']);
            }
            if (move_uploaded_file($_FILES['requestFile']['tmp_name'], "$workPath/$filePath")) {
                $fileName = $_FILES['requestFile']['name'];
            } else {
                Session::Messages($langGeneralError, 'alert-danger');
                redirect_to_home_page($data['targetUrl']);
            }
        }
        if (isset($_POST['newState']) and isset($stateLabels[$_POST['newState']])) {
            $newState = $_POST['newState'];
        } else {
            $newState = $request->state;
        }
        if ($comment or $fileName or $newState != $request->state) {
            Database::get()->query('INSERT INTO request_action
                SET request_id = ?d, user_id = ?d, ts = NOW(),
                    old_state = ?d, new_state = ?d,
                    filename = ?s, real_filename = ?s,
                    comment = ?s',
                $id, $uid, $request->state, $newState,
                $fileName, $filePath, $comment);
            redirect_to_home_page($data['targetUrl']);
        }
    }

    $data['action_bar'] = action_bar([
            [ 'title' => $langBack,
              'url' => $backUrl,
              'icon' => 'fa-reply',
              'level' => 'primary-label' ]
        ], false);
    $data['state'] = $stateLabels[$request->state];
    $data['can_modify'] = $can_modify;
    $data['can_comment'] = $can_comment;
    $data['commenterName'] = $_SESSION['givenname'] . ' ' . $_SESSION['surname'];
    $data['commentEditor'] = rich_text_editor('requestComment', 4, 20, '');
    $data['comments'] = Database::get()->queryArray('SELECT * FROM request_action
        WHERE request_id = ?d
        ORDER BY ts', $id);
    $data['states'] = $stateLabels;

    $navigation[] = array('url' => $backUrl, 'name' => $langRequests);
    $pageName = $request->title;
    enableCheckFileSize();

    view('modules.request.show', $data);
} else {
    load_js('datatables');
    load_js('datatables_bootstrap');
    $data['action_bar'] = action_bar([
            [ 'title' => $langNewRequest,
              'url' => 'new.php?course=' . $course_code,
              'icon' => 'fa-plus-circle',
              'button-class' => 'btn-success',
              'level' => 'primary-label' ]
        ], false);
    $data['listUrl'] = $urlAppend . 'modules/request/list.php?course=' . $course_code;

    view('modules.request.index', $data);
}

function formatUsers($userData) {
    if (!count($userData)) {
        return '<span class="not_visible"> - </span>';
    }
    return implode(', ', array_map(function ($user) {
            if (is_object($user)) {
                return '<b>' . $user->name . '</b>';
            } else {
                return '<b>' . uid_to_name($user) . '</b>';
            }
        }, $userData));
}
