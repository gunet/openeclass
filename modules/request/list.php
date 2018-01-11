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

// Datatables AJAX request
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $limit = intval($_GET['iDisplayLength']);
    $offset = intval($_GET['iDisplayStart']);

    $watcher_sql = 'AND (creator_id = ?d OR id IN (SELECT request_id FROM request_watcher WHERE user_id = ?d))';
    $watcher_params = [$uid, $uid];

    $data['iTotalRecords'] = Database::get()->querySingle("SELECT COUNT(*) AS total
        FROM request WHERE course_id = ?d $watcher_sql", $course_id, $watcher_params)->total;
    if (isset($_GET['sSearch']) and $_GET['sSearch'] !== '') {
        $search_sql = 'AND title LIKE ?s';
        $keyword = '%' . $_GET['sSearch'] . '%';
        $data['iTotalDisplayRecords'] = Database::get()->querySingle("SELECT COUNT(*) AS total
                FROM request WHERE course_id = ?d $search_sql $watcher_sql",
            $course_id, $keyword, $watcher_params)->total;
    } else {
        $search_sql = '';
        $keyword = [];
        $data['iTotalDisplayRecords'] = $data['iTotalRecords'];
    }

    if ($limit > 0) {
        $extra_sql = 'LIMIT ?d, ?d';
        $extra_terms = array($offset, $limit);
    } else {
        $extra_sql = '';
        $extra_terms = array();
    }

    $result = Database::get()->queryArray("SELECT * FROM request
        WHERE course_id = ?d $search_sql $watcher_sql
        ORDER BY `title` DESC $extra_sql",
        $course_id, $keyword, $watcher_params, $extra_terms);

    $data['aaData'] = array();
    foreach ($result as $request) {
        $data['aaData'][] = [
            '0' => "<a href='{$urlAppend}modules/request/?course=$course_code&amp;id={$request->id}'>" .
                standard_text_escape($request->title) . "</a>",
            '1' => claro_format_locale_date($dateFormatLong, strtotime($request->open_date)),
            '2' => claro_format_locale_date($dateFormatLong, strtotime($request->change_date)),
            '3' => '&nbsp;'];
    }

    echo json_encode($data, JSON_UNESCAPED_UNICODE);
}
