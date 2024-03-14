<?php
/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2016  Greek Universities Network - GUnet
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
 * ======================================================================== */


$require_current_course = true;
$require_help = true;
$helpTopic = 'announcements';
$guest_allowed = true;

require_once '../../include/baseTheme.php';
require_once 'modules/tags/eclasstag.class.php';

// Create or edit announcement
if (isset($_GET['modify'])) {
    $announce = Database::get()->querySingle("SELECT * FROM announcement WHERE id=?d", $_GET['modify']);
    if ($announce) {
        $data['announce_id'] = $announce->id;
        $contentToModify = Session::has('newContent') ? Session::get('newContent') : $announce->content;
        $data['titleToModify'] = Session::has('antitle') ? Session::get('antitle') : q($announce->title);
        if ($announce->start_display) {
            $startDate_obj = DateTime::createFromFormat('Y-m-d H:i:s', $announce->start_display);
            $data['showFrom'] = q($startDate_obj->format('d-m-Y H:i'));
        }
        if ($announce->stop_display) {
            $endDate_obj = DateTime::createFromFormat('Y-m-d H:i:s', $announce->stop_display);
            $data['showUntil'] = q($endDate_obj->format('d-m-Y H:i'));
        }
    }

    $langAdd = $pageName = $langModifAnn;
    $data['checked_public'] = $announce->visible ? 'checked' : '';
    $data['selected_email'] = '';
    if (!is_null($announce->start_display)) {
        // $showFrom is set earlier
        $data['start_checkbox'] = 'checked';
        $data['start_text_disabled'] = '';
        $data['end_disabled'] = "";
        if (!is_null($announce->stop_display)) {
            // $data['showUntil'] is set earlier
            $data['end_checkbox'] = 'checked';
            $data['end_text_disabled'] = '';
        } else {
            $data['showUntil'] = '';
            $data['end_checkbox'] = '';
            $end_text_disabled = 'disabled';
        }
    } else {
        $data['start_checkbox'] = '';
        $start_text_disabled = 'disabled';
        $data['end_checkbox'] = '';
        $data['end_disabled'] = 'disabled';
        $end_text_disabled = 'disabled';
        $data['showFrom'] = '';
        $data['showUntil'] = '';
    }

} else {
    $pageName = $langAddAnn;
    $data['selected_email'] = 'selected';
    $data['announce_id'] = '';
    $data['checked_public'] = 'checked';
    $data['start_checkbox'] = Session::has('startdate_active') ? 'checked' : '';
    $data['end_checkbox'] = Session::has('enddate_active') ? 'checked' : '';
    $data['showFrom'] = Session::has('startdate') ? Session::get('startdate') : '';
    $data['end_disabled'] = Session::has('startdate_active') ? '' : 'disabled';
    $data['showUntil'] = Session::has('enddate') ? Session::get('enddate') : '';
    $data['titleToModify'] = Session::has('antitle') ? Session::get('antitle') : '';
    $contentToModify = Session::has('newContent') ? Session::get('newContent') : '';
}
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langAnnouncements);

$antitle_error = Session::getError('antitle', "<span class='help-block Accent-200-cl'>:message</span>");
$data['startdate_error'] = Session::getError('startdate', "<span class='help-block Accent-200-cl'>:message</span>");
$data['enddate_error'] = Session::getError('enddate', "<span class='help-block Accent-200-cl'>:message</span>");

load_js('select2');
load_js('bootstrap-datetimepicker');

$data['antitle_error'] = ($antitle_error ? " has-error" : "");
$data['contentToModify'] = rich_text_editor('newContent', 4, 20, $contentToModify);

$data['course_users'] = Database::get()->queryArray("SELECT cu.user_id, CONCAT(u.surname, ' ', u.givenname) name, u.email
    FROM course_user cu
    JOIN user u ON cu.user_id=u.id
    WHERE cu.course_id = ?d
    AND u.email<>''
    AND u.email IS NOT NULL ORDER BY u.surname, u.givenname", $course_id);

$data['tags'] = eClassTag::tagInput(isset($announce)? $announce->id: null);
$data['startdate_error'] = $data['startdate_error'] ? " has-error" : "";
$data['enddate_error'] = $data['enddate_error'] ? " has-error" : "";
$data['submitUrl'] = $urlAppend . 'modules/announcements/submit.php?course=' . $course_code;

view('modules.announcements.create_edit', $data);
