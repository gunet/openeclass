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
require_once 'modules/request/functions.php';

$toolName = $langNewRequest;

$requestDescription = '';
if (isset($_POST['requestTitle'])) {
    $title = canonicalize_whitespace($_POST['requestTitle']);
    $requestDescription = purify($_POST['requestDescription']);
    if ($title) {
        $result = Database::get()->query('INSERT INTO request
            SET course_id = ?d, title = ?s, description = ?s,
                creator_id = ?d, state = ?d, type_id = NULL,
                open_date = NOW(), change_date = NOW(), close_date = NULL',
            $course_id, $title, $requestDescription,
            $uid, REQUEST_STATE_NEW);
        if ($result) {
            $rid = $result->lastInsertID;
            $watchers = [];
            $watchersSeen = [];
            if (isset($_POST['assignTo'])) {
                foreach ($_POST['assignTo'] as $user_id) {
                    $watchers[] = [$rid, $user_id, REQUEST_ASSIGNED, 1];
                    $watchersSeen[$user_id] = true;
                }
            }
            if (isset($_POST['requestWatchers'])) {
                foreach ($_POST['requestWatchers'] as $user_id) {
                    if (!isset($watchersSeen[$user_id])) {
                        $watchers[] = [$rid, $user_id, REQUEST_WATCHER, 1];
                    }
                }
            }
            if (count($watchers)) {
                $placeholders = implode(', ', array_fill(0, count($watchers), '(?d, ?d, ?d, ?d)'));
                Database::get()->query("INSERT INTO request_watcher
                    (request_id, user_id, type, notification) VALUES $placeholders",
                    $watchers);
            }
            Session::Messages(trans('langRequestCreated'), 'alert-success');
            redirect_to_home_page("modules/request/?course=$course_code&id=$rid");
        }
    } else {
        Session::Messages(trans('langFieldsRequ'), 'alert-warning');
    }
}

$backUrl = $urlAppend . 'modules/request/?course=' . $course_code;
$navigation[] = array('url' => $backUrl, 'name' => $langRequests);

$data['action_bar'] = action_bar(
        [
            [ 'title' => $langBack,
              'url' => $backUrl,
              'icon' => 'fa-reply',
              'level' => 'primary-label' ]
        ], false);

$data['descriptionEditor'] = rich_text_editor('requestDescription', 4, 20, $requestDescription);
$data['creatorName'] = $_SESSION['givenname'] . ' ' . $_SESSION['surname'];
$data['backUrl'] = $backUrl;
$data['targetUrl'] = $urlAppend . 'modules/request/new.php?course=' . $course_code;;
$data['course_users'] = Database::get()->queryArray("SELECT user_id,
        CONCAT(surname, ' ', givenname) name, email
    FROM course_user JOIN user ON user_id = user.id
    WHERE course_id = ?d AND user_id <> ?d
    ORDER BY surname, givenname", $course_id, $uid);
load_js('select2');
view('modules.request.new', $data);
