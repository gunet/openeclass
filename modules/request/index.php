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

    $data['request'] = $request;
    $data['watchers'] = getWatchers($id, REQUEST_WATCHER);
    $data['assigned'] = getWatchers($id, REQUEST_ASSIGNED);
    $data['backUrl'] = $backUrl;
    $data['targetUrl'] = $backUrl . '&id=' . $id;
    $can_modify = $is_editor || $request->creator_id == $uid ||
        in_array($uid, $data['assigned']);
    $can_comment = $can_modify || in_array($uid, $data['watchers']);

    if (isset($_POST['requestComment'])) {
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
