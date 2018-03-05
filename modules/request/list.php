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

// Datatables AJAX request
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $limit = isset($_GET['length'])? intval($_GET['length']): 0;
    $offset = isset($_GET['start'])? intval($_GET['start']): 0;

    $watcher_sql = 'AND (creator_id = ?d OR id IN (SELECT request_id FROM request_watcher WHERE user_id = ?d))';
    $watcher_params = [$uid, $uid];

    if (!isset($_GET['show_closed']) or $_GET['show_closed'] != 'true') {
        $watcher_sql .= ' AND state <> ' . REQUEST_STATE_CLOSED;
    }

    $data['recordsTotal'] = Database::get()->querySingle("SELECT COUNT(*) AS total
        FROM request WHERE course_id = ?d $watcher_sql", $course_id, $watcher_params)->total;
    if (isset($_GET['search']) and isset($_GET['search']['value']) and $_GET['search']['value'] !== '') {
        $search_sql = 'AND title LIKE ?s';
        $keyword = '%' . $_GET['search']['value'] . '%';
        $data['recordsFiltered'] = Database::get()->querySingle("SELECT COUNT(*) AS total
                FROM request WHERE course_id = ?d $search_sql $watcher_sql",
            $course_id, $keyword, $watcher_params)->total;
    } else {
        $search_sql = '';
        $keyword = [];
        $data['recordsFiltered'] = $data['recordsTotal'];
    }

    if ($limit > 0) {
        $extra_sql = 'LIMIT ?d, ?d';
        $extra_terms = array($offset, $limit);
    } else {
        $extra_sql = '';
        $extra_terms = array();
    }

    $order = "title DESC";
    if (isset($_GET['order'])) {
        $columns = ['title', 'state', 'open_date', 'change_date'];
        $order = '`' . $columns[$_GET['order'][0]['column']] . '`';
        if ($_GET['order'][0]['dir'] == 'desc') {
            $order .= ' DESC';
        }
    }

    $result = Database::get()->queryArray("SELECT * FROM request
        WHERE course_id = ?d $search_sql $watcher_sql
        ORDER BY $order $extra_sql",
        $course_id, $keyword, $watcher_params, $extra_terms);

    $data['data'] = array();
    foreach ($result as $request) {
        $data['data'][] = [
            '0' => "<a href='{$urlAppend}modules/request/?course=$course_code&amp;id={$request->id}'>" .
                standard_text_escape($request->title) . "</a>",
            '1' => $stateLabels[$request->state],
            '2' => format_ts($request->open_date),
            '3' => format_ts($request->change_date),
            '4' => '&nbsp;'];
    }

    echo json_encode($data, JSON_UNESCAPED_UNICODE);
}

function format_ts($datetime) {
    $ts = strtotime($datetime);
    $hour = 60 * 60;
    $day = 60 * 60 * 24;
    $output = '';
    $diff = time() - $ts;
    if ($diff > $day) {
        $days = intval($diff / $day);
        $output = sprintf($days == 1? trans('langDayAgo'): trans('langDaysAgo'), $days);
    } elseif ($diff > $hour) {
        $hours = ceil($diff / 60 / 60);
        $output = sprintf($hours == 1? trans('langHourAgo'): trans('langHoursAgo'), $hours);
    } elseif ($diff > 0) {
        $mins = ceil($diff / 60 / 60);
        $output = sprintf($mins == 1? trans('langMinuteAgo'): trans('langMinutesAgo'), $mins);
    }

    if ($output) {
        $output = '<br><span class="small">' . $output . '</span>';
    }

    return date('Y-m-d', $ts) . $output;
}
