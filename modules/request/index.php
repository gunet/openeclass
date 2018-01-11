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
    $data['action_bar'] = action_bar([
            [ 'title' => $langBack,
              'url' => $backUrl,
              'icon' => 'fa-reply',
              'level' => 'primary-label' ]
        ], false);
    $data['request'] = $request;
    $data['backUrl'] = $backUrl;
    $data['targetUrl'] = $backUrl . '&id=' . $id;
    $data['watchers'] = getWatchers($id, REQUEST_WATCHER);
    $data['assigned'] = getWatchers($id, REQUEST_ASSIGNED);
    $data['state'] = $stateLabels[$request->state];
    $data['commenterName'] = $_SESSION['givenname'] . ' ' . $_SESSION['surname'];
    $data['commentEditor'] = rich_text_editor('requestComment', 4, 20, '');
    $data['comments'] = Database::get()->queryArray('SELECT * FROM request_action
        WHERE request_id = ?d
        ORDER BY ts', $id);

    $navigation[] = array('url' => $backUrl, 'name' => $langRequests);
    $pageName = $request->title;

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
