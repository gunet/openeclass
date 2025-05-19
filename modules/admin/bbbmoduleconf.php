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
$require_help = true;
$helpTopic = 'external_tools';
$helpSubTopic = 'big_blue_button';
require_once '../../include/baseTheme.php';
require_once 'modules/tc/functions.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/course.class.php';

$toolName = $langBBBConf;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);

$tree = new Hierarchy();
$course = new Course();

load_js('tools.js');
load_js('validation.js');
load_js('select2');
load_js('datatables');

if (isset($_GET['delete_tc_course']) and $_GET['list']) {
    Database::get()->querySingle("DELETE FROM course_external_server
                                          WHERE course_id = ?d
                                          AND external_server = ?d", $_GET['delete_tc_course'], $_GET['list']);
    Session::flash('message', $langBBBDeleteCourseSuccess);
    Session::flash('alert-class', 'alert-success');
}
if (isset($_POST['code_to_assign'])) {
    $course_id_to_assign = course_code_to_id($_POST['code_to_assign']);
    if (empty($course_id_to_assign)) {
        Session::flash('message', $langBBBAddCourseFail);
        Session::flash('alert-class', 'alert-warning');
    } else {
        $q = Database::get()->querySingle("SELECT course_id from course_external_server
                                           WHERE course_id = ?d", $course_id_to_assign);
        if (empty($q->course_id)) {
            Database::get()->query("INSERT INTO course_external_server SET course_id = ?d, external_server = ?d",
                $course_id_to_assign, $_POST['tc_server']);
            Session::flash('message', $langBBBAddCourseSuccess);
            Session::flash('alert-class', 'alert-success');
        } else {
            Session::flash('message', $langBBBAddCourseFailExits);
            Session::flash('alert-class', 'alert-success');
        }
    }
}

if (isset($_GET['add_course_to_tc'])) {

    $data['tc_server'] = $tc_server = $_GET['tc_server'];
    view('admin.other.extapps.bbb.add_course_to_tc', $data);

} else if (isset($_GET['list'])) { // list of courses with specific bbb server
    $pageName = $langOtherCourses;
    $data['action_bar'] = action_bar(array(
        array('title' => $langBBBAddCourse,
            'url' => "$_SERVER[SCRIPT_NAME]?tc_server=$_GET[list]&amp;add_course_to_tc",
            'icon' => 'fa-plus-circle',
            'level' => 'primary-label',
            'button-class' => 'btn-success'),
        array('title' => $langBack,
            'url' => "$_SERVER[SCRIPT_NAME]",
            'icon' => 'fa-reply',
            'level' => 'primary-label')
    ));

    $bbb = $_GET['list'];
    $q = Database::get()->queryArray("SELECT id, course_id FROM course_external_server WHERE external_server = ?d", $bbb);
    $tbl_cnt = '';
    foreach ($q as $course_data) {
        // ger course full path
        $departments = $course->getDepartmentIds($course_data->course_id);
        $i = 1;
        $dep = '';
        foreach ($departments as $department) {
            $br = ($i < count($departments)) ? '<br/>' : '';
            $dep .= $tree->getFullPath($department) . $br;
            $i++;
        }
        $code = course_id_to_code($course_data->course_id);
        $tbl_cnt .= "<tr>";
        $tbl_cnt .= "<td><a href='{$urlServer}courses/$code/' target='_blank' aria-label='$langOpenNewTab'>" . course_id_to_title($course_data->course_id) . "</a>
                                    &nbsp;<small>(" . course_id_to_code($course_data->course_id). ")</small>
                                    <div style='margin-top: 5px;'><small>". course_id_to_prof($course_data->course_id) . "</small></div>
                          </td>";
        $tbl_cnt .= "<td>". $dep ."</td>";
        $tbl_cnt .= "<td class='option-btn-cell'>".
            action_button(array(
                array('title' => $langDelete,
                    'url' => "$_SERVER[SCRIPT_NAME]?list=$bbb&delete_tc_course=$course_data->course_id",
                    'icon' => 'fa-times',
                    'class' => 'delete',
                    'confirm' => $langConfirmDelete))) .
            "</td>";
        $tbl_cnt .= "</tr>";
    }

    $data['tbl_cnt']   = $tbl_cnt;
    view('admin.other.extapps.bbb.list', $data);
}

if (isset($_GET['delete_server'])) {
    $id = $_GET['delete_server'];
    Database::get()->querySingle("DELETE FROM tc_servers WHERE id=?d", $id);
    // Display result message
    Session::flash('message',$langFileUpdatedSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page('modules/admin/bbbmoduleconf.php');
} else if (isset($_POST['submit'])) {
    // Save new config
    $api_url = $_POST['api_url_form'];
    if (!preg_match('/\/$/', $api_url)) { // append '/' if it doesn't exist
        $api_url = $api_url . '/';
    }
    $key = $_POST['key_form'];
    if (isset($_POST['hostname_form'])) {
        $hostname = $_POST['hostname_form'];
    }
    if (!isset($hostname) or !($hostname = trim($hostname))) {
        $hostname = parse_url($api_url, PHP_URL_HOST);
    }
    $max_rooms = $_POST['max_rooms_form'];
    $max_users = $_POST['max_users_form'];
    $enable_recordings = $_POST['enable_recordings'];
    $enabled = $_POST['enabled'];
    $weight = $_POST['weight'];
    $tc_courses = $_POST['tc_courses'];
    if (in_array(0, $tc_courses)) {
        $allcourses = 1; // tc server is assigned to all courses
    } else {
        $allcourses = 0; // tc server is assigned to specific courses
    }
    if (isset($_POST['id_form'])) {
        $id = getDirectReference($_POST['id_form']);
        Database::get()->querySingle("UPDATE tc_servers SET
                server_key = ?s,
                hostname = ?s,
                api_url = ?s,
                max_rooms =?s,
                max_users =?s,
                enable_recordings =?s,
                enabled = ?s,
                weight = ?d,
                all_courses = ?d
                WHERE id =?d", $key, $hostname, $api_url, $max_rooms, $max_users, $enable_recordings, $enabled, $weight, $allcourses, $id);
        Database::get()->query("DELETE FROM course_external_server WHERE external_server = ?d", $id);
        if ($allcourses == 0) {
            foreach ($tc_courses as $tc_data) {
                Database::get()->query("INSERT INTO course_external_server SET course_id = ?d, external_server = ?d", $tc_data, $id);
                update_bbb_session($tc_data, $id); // update existing tc_sessions
            }
        }
    } else {
        $q = Database::get()->query("INSERT INTO tc_servers (`type`, hostname, ip, server_key, api_url, max_rooms, max_users, enable_recordings, enabled, weight, all_courses) VALUES
        ('bbb', ?s, '', ?s, ?s, ?s, ?s, ?s, ?s, ?d, ?d)", $hostname, $key, $api_url, $max_rooms, $max_users, $enable_recordings, $enabled, $weight, $allcourses);
        $tc_id = $q->lastInsertID;
        if ($allcourses == 0) {
            foreach ($tc_courses as $tc_data) {
                Database::get()->query("INSERT INTO course_external_server SET course_id = ?d, external_server = ?d", $tc_data, $tc_id);
                update_bbb_session($tc_data, $tc_id); // update existing tc_sessions
            }
        }
    }
    // Display result message
    Session::flash('message',$langFileUpdatedSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/admin/bbbmoduleconf.php");

} else if (isset($_POST['submit_config'])) { // submit bbb config
    $bbb_max_duration = isset($_POST['bbb_max_duration']) ? intval($_POST['bbb_max_duration']) : '';
    $bbb_max_part_per_room = isset($_POST['bbb_max_part_per_room']) ? intval($_POST['bbb_max_part_per_room']) : '';
    $bbb_lb_weight_part = isset($_POST['bbb_lb_weight_part']) ? intval($_POST['bbb_lb_weight_part']) : '';
    $bbb_lb_weight_mic = isset($_POST['bbb_lb_weight_mic']) ? intval($_POST['bbb_lb_weight_mic']) : '';
    $bbb_lb_weight_camera = isset($_POST['bbb_lb_weight_camera']) ? intval($_POST['bbb_lb_weight_camera']) : '';
    $bbb_lb_weight_room = isset($_POST['bbb_lb_weight_room']) ? intval($_POST['bbb_lb_weight_room']) : '';
    $bbb_recording = isset($_POST['bbb_recording']) ? 1 : 0;
    $bbb_muteOnStart = isset($_POST['bbb_muteOnStart']) ? 1 : 0;
    $bbb_DisableCam = isset($_POST['bbb_DisableCam']) ? 1 : 0;
    $bbb_webcamsOnlyForModerator = isset($_POST['bbb_webcamsOnlyForModerator']) ? 1 : 0;
    $bbb_DisableMic = isset($_POST['bbb_DisableMic']) ? 1 : 0;
    $bbb_DisablePrivateChat = isset($_POST['bbb_DisablePrivateChat']) ? 1 : 0;
    $bbb_DisablePublicChat = isset($_POST['bbb_DisablePublicChat']) ? 1 : 0;
    $bbb_DisableNote = isset($_POST['bbb_DisableNote']) ? 1 : 0;
    $bbb_HideUserList = isset($_POST['bbb_HideUserList']) ? 1 : 0;
    $bbb_hideParticipants = isset($_POST['bbb_hideParticipants']) ? 1 : 0;

    set_config('bbb_max_duration', $bbb_max_duration);
    set_config('bbb_max_part_per_room', $bbb_max_part_per_room);
    set_config('bbb_lb_weight_part', $bbb_lb_weight_part);
    set_config('bbb_lb_weight_mic', $bbb_lb_weight_mic);
    set_config('bbb_lb_weight_camera', $bbb_lb_weight_camera);
    set_config('bbb_lb_weight_room', $bbb_lb_weight_room);
    set_config('bbb_lb_algo', $_POST['bbb_lb_algo']);
    set_config('bbb_recording', $bbb_recording);
    set_config('bbb_muteOnStart', $bbb_muteOnStart);
    set_config('bbb_DisableMic', $bbb_DisableMic);
    set_config('bbb_DisableCam', $bbb_DisableCam);
    set_config('bbb_webcamsOnlyForModerator', $bbb_webcamsOnlyForModerator);
    set_config('bbb_DisablePrivateChat', $bbb_DisablePrivateChat);
    set_config('bbb_DisablePublicChat', $bbb_DisablePublicChat);
    set_config('bbb_DisableNote', $bbb_DisableNote);
    set_config('bbb_HideUserList', $bbb_HideUserList);
    set_config('bbb_hideParticipants', $bbb_hideParticipants);

    // Display result message
    Session::flash('message',$langFileUpdatedSuccess);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("modules/admin/bbbmoduleconf.php");

} else if (isset($_GET['edit_config'])) { // bbb config form

    $pageName = $langBBBConfig;
    $data['bbb_lb_wo_checked'] = $data['bbb_lb_wlv_checked'] = $data['bbb_lb_wlm_checked'] = '';
    $data['bbb_lb_wlc_checked'] = $data['bbb_lb_wll_checked'] = $data['bbb_lb_wlr_checked'] = '';
    $data['bbb_max_duration'] = $bbb_max_duration = get_config('bbb_max_duration', 0);
    $data['bbb_max_part_per_room'] = $bbb_max_part_per_room = get_config('bbb_max_part_per_room', 0);
    $data['bbb_lb_weight_part'] = $bbb_lb_weight_part = get_config('bbb_lb_weight_part', 1);
    $data['bbb_lb_weight_mic'] = $bbb_lb_weight_mic = get_config('bbb_lb_weight_mic', 2);
    $data['bbb_lb_weight_camera'] = $bbb_lb_weight_camera = get_config('bbb_lb_weight_camera', 2);
    $data['bbb_lb_weight_camera'] = $bbb_lb_weight_camera = get_config('bbb_lb_weight_camera', 2);
    $data['bbb_lb_weight_room'] = $bbb_lb_weight_room = get_config('bbb_lb_weight_room', 50);
    $data['bbb_lb_algo'] = $bbb_lb_algo = get_config('bbb_lb_algo', 'wo');
    $data['checked_recording'] = $checked_recording = get_config('bbb_recording', 1) ? 'checked' : '';
    $data['checked_muteOnStart'] = $checked_muteOnStart = get_config('bbb_muteOnStart', 0) ? 'checked' : '';
    $data['checked_DisableMic'] = $checked_DisableMic = get_config('bbb_DisableMic', 0) ? 'checked' : '';
    $data['checked_DisableCam'] = $checked_DisableCam = get_config('bbb_DisableCam', 0) ? 'checked' : '';
    $data['checked_webcamsOnlyForModerator'] = $checked_webcamsOnlyForModerator = get_config('bbb_webcamsOnlyForModerator', 0) ? 'checked' : '';
    $data['checked_DisablePrivateChat'] = $checked_DisablePrivateChat = get_config('bbb_DisablePrivateChat', 0) ? 'checked' : '';
    $data['checked_DisablePublicChat'] = $checked_DisablePublicChat = get_config('bbb_DisablePublicChat', 0) ? 'checked' : '';
    $data['checked_DisableNote'] = $checked_DisableNote = get_config('bbb_DisableNote', 0) ? 'checked' : '';
    $data['checked_HideUserList'] = $checked_HideUserList = get_config('bbb_HideUserList', 0) ? 'checked' : '';
    $data['checked_hideParticipants'] = $checked_hideParticipants = get_config('bbb_hideParticipants', 0) ? 'checked' : '';

    if ($bbb_lb_algo == 'wll') {
        $data['bbb_lb_wll_checked'] = $bbb_lb_wll_checked = "checked='true'";
    } else if ($bbb_lb_algo == 'wlr') {
        $data['bbb_lb_wlr_checked'] = $bbb_lb_wlr_checked = "checked='true'";
    } else if ($bbb_lb_algo == 'wlc') {
        $data['bbb_lb_wlc_checked'] = $bbb_lb_wlc_checked = "checked='true'";
    } else if ($bbb_lb_algo == 'wlm') {
        $data['bbb_lb_wlm_checked'] = $bbb_lb_wlm_checked = "checked='true'";
    } else if ($bbb_lb_algo == 'wlv') {
        $data['bbb_lb_wlv_checked'] = $bbb_lb_wlv_checked = "checked='true'";
    } else {
        $data['bbb_lb_wo_checked'] = $bbb_lb_wo_checked = "checked='true'"; // default
    }

    $data['action_bar'] = action_bar(array(
        array('title' => $langBack,
            'url' => "bbbmoduleconf.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));

    view('admin.other.extapps.bbb.config', $data);

} else if (isset($_GET['add_server']) || isset($_GET['edit_server'])) { // edit server form
    $pageName = isset($_GET['add_server']) ? $langAddServer : $langEdit;
    $toolName = $langBBBConf;
    $navigation[] = array('url' => 'bbbmoduleconf.php', 'name' => $langBBBConf);
    $data['action_bar'] = action_bar([
                [
                    'title' => $langBack,
                    'url' => "bbbmoduleconf.php",
                    'icon' => 'fa-reply',
                    'level' => 'primary'
                ]
            ]);
    $data['enabled_recordings'] = true;
    $data['enabled'] = true;

    if (isset($_GET['add_server'])) {
        $courses_list = Database::get()->queryArray("SELECT id, code, title FROM course 
                                            WHERE id NOT IN (SELECT course_id FROM course_external_server) 
                                            AND visible != " . COURSE_INACTIVE . "
                                            ORDER BY title");
        $data['listcourses'] = "<option value='0' selected><h2>$langToAllCourses</h2></option>";
        foreach ($courses_list as $c) {
            $data['listcourses'] .= "<option value='$c->id'>" . q($c->title) . " (" . q($c->code) . ")</option>";
        }
    } else {
        $data['bbb_server'] = $_GET['edit_server'];
        $data['server'] = Database::get()->querySingle("SELECT * FROM tc_servers WHERE id = ?d", $data['bbb_server']);
        if ($data['server']->enable_recordings == "false") {
            $data['enabled_recordings'] = false;
        }
        if ($data['server']->enabled == "false") {
            $data['enabled'] = false;
        }

        $courses_list = Database::get()->queryArray("SELECT id, code, title FROM course WHERE id 
                                                        NOT IN (SELECT course_id FROM course_external_server) 
                                                        AND visible != " . COURSE_INACTIVE . "
                                                    ORDER BY title");
        $listcourses = '';
        if ($data['server']->all_courses == '1') {
            $listcourses .= "<option value='0' selected><h2>$langToAllCourses</h2></option>";
        } else {
            $tc_courses_list = Database::get()->queryArray("SELECT id, code, title FROM course WHERE id 
                                        IN (SELECT course_id FROM course_external_server WHERE external_server = ?d) 
                                        ORDER BY title", $data['bbb_server']);
            if (count($tc_courses_list) > 0) {
                foreach($tc_courses_list as $c) {
                    $listcourses .= "<option value='$c->id' selected>" . q($c->title) . " (" . q($c->code) . ")</option>";
                }
                $listcourses .= "<option value='0'><h2>$langToAllCourses</h2></option>";
            }
        }
        foreach($courses_list as $c) {
            $listcourses .= "<option value='$c->id'>" . q($c->title) . " (" . q($c->code) . ")</option>";
        }
        $data['listcourses'] = $listcourses;
    }
    view('admin.other.extapps.bbb.create', $data);
} else {    // Display config edit form

    //display available BBB servers and running meetings
    $data['action_bar'] = action_bar(array(
        array('title' => $langBack,
            'url' => "extapp.php",
            'icon' => 'fa-reply',
            'level' => 'primary'),
        array('title' => $langAddServer,
            'url' => "bbbmoduleconf.php?add_server",
            'icon' => 'fa-plus-circle',
            'level' => 'primary-label',
            'button-class' => 'btn-success'),
        array('title' => $langConfig,
            'url' => "bbbmoduleconf.php?edit_config",
            'icon' => 'fa-gear',
            'level' => 'primary-label')
        ));

    $tc_cron_enabled = $tc_cron_running = false;
    $tc_cron_ts = Database::get()->querySingle("SELECT value FROM config WHERE `key` = 'tc_cron_ts'");
    if ($tc_cron_ts) {
        if ($tc_cron_ts->value) {
            $tc_cron_enabled = true;
        }
        $tc_cron_ts = DateTime::createFromFormat('Y-m-d H:i', $tc_cron_ts->value)->add(new DateInterval('PT10M'));
        $now = new DateTime();
        if ($tc_cron_ts > $now) { // If cron timestamp is in last 10 minutes
            $tc_cron_running = true;
        }
    }
    if ($tc_cron_running) {
        $tc_cron_icon = 'fa-check-circle';
        $tc_cron_message = $langBBBCronRunning;
        $tc_cron_class = 'alert-success';
    } elseif ($tc_cron_enabled) {
        $tc_cron_icon = 'fa-exclamation-triangle';
        $tc_cron_message = $langBBBCronStopped;
        $tc_cron_class = 'alert-danger';
    } else {
        $tc_cron_icon = 'fa-info-circle';
        $tc_cron_message = $langBBBCronEnable;
        $tc_cron_class = 'alert-info';
    }
    if (!$tc_cron_running) {
        $langBBBCronEnableInstructions = str_replace(['{webRoot}', '{cronURL}'],
            [$webDir, $urlServer . 'modules/tc/tc_cron_attendance.php'],
            $langBBBCronEnableInstructions);
        $tc_cron_message = preg_replace('/\{(.*)\}/',
            "<p class='text-center' style='padding-top: 5px'><button class='btn btn-default' data-bs-toggle='modal' data-bs-target='#bbbCronInfoModal'>\\1</button></p>",
            $tc_cron_message);

        $data['tc_cron_icon'] = $tc_cron_icon;
        $data['tc_cron_class'] = $tc_cron_class;
        $data['tc_cron_running'] = $tc_cron_running;
        $data['tc_cron_message'] = $tc_cron_message;
    }

    $data['q'] = $q = Database::get()->queryArray("SELECT * FROM tc_servers WHERE `type` = 'bbb' ORDER BY weight");
    $bbb_cnt = '';
    if (count($q)>0) {
        $t_connected_users = $t_listeners = $t_mics = $t_cameras = 0;
        $t_active_rooms = 0;
        $t_max_users = 0;
        $t_max_rooms = 0;

        // get load and metrics of enabled servers
        $servers = get_bbb_servers_load_by_id();
        foreach ($q as $srv) {
            $enabled_bbb_server = ($srv->enabled == 'true')? $langYes : $langNo;
            $courses_note = $connected_users = $active_rooms = $server_load = $mics = $cameras = '';
            if ($srv->enabled == "true") {
                $server_load = $servers[$srv->id]['load'];
                $connected_users = $servers[$srv->id]['participants'];
                $listeners = $servers[$srv->id]['listeners'];
                $mics = $servers[$srv->id]['voice'];
                $cameras = $servers[$srv->id]['video'];
                $active_rooms = $servers[$srv->id]['rooms'];
                $t_connected_users += $connected_users;
                $t_listeners += $listeners;
                $t_mics += $mics;
                $t_cameras += $cameras;
                $t_active_rooms += $active_rooms;
                $t_max_users += $srv->max_users;
                $t_max_rooms += $srv->max_rooms;
                if ($srv->all_courses) {
                    $courses_note = $langToAllCourses;
                } else {
                    $num_of_tc_courses = Database::get()->querySingle("SELECT COUNT(*) AS cnt
                            FROM course_external_server WHERE external_server = ?d", $srv->id)->cnt;
                    $courses_note = "<a href='$_SERVER[SCRIPT_NAME]?list=$srv->id'>" .
                        ($num_of_tc_courses == 0? $langToNoCourses: "$langIn $num_of_tc_courses $langsCourses") .
                        "</a>";
                }
                if ($courses_note) {
                    $courses_note = " <small>($courses_note)</small>";
                }
                $bbb_cnt .= "<tr>" .
                    "<td>$srv->hostname</td>" .
                    "<td>$enabled_bbb_server$courses_note</td>" .
                    "<td>$connected_users / $srv->max_users</td>" .
                    "<td>$active_rooms / $srv->max_rooms</td>" .
                    "<td>$mics / $cameras</td>" .
                    "<td>$srv->weight / $server_load</td>";
            } else {
                $bbb_cnt .= "<tr>" .
                    "<td>$srv->hostname</td>" .
                    "<td>$enabled_bbb_server$courses_note</td>" .
                    "<td>&mdash;</td>" .
                    "<td>&mdash;</td>" .
                    "<td>&mdash;</td>" .
                    "<td>&mdash;</td>";
            }
            $bbb_cnt .= "<td class='option-btn-cell'>" .
                action_button(array(
                    array('title' => $langEditChange,
                        'url' => "$_SERVER[SCRIPT_NAME]?edit_server=$srv->id",
                        'icon' => 'fa-edit'),
                    array('title' => $langDelete,
                        'url' => "$_SERVER[SCRIPT_NAME]?delete_server=$srv->id",
                        'icon' => 'fa-times',
                        'class' => 'delete',
                        'confirm' => $langConfirmDelete))) . "</td>" .
                "</tr>";
        }
        $users_p = $t_max_users? (' (' . number_format($t_connected_users*100/$t_max_users, 0) . '%)'): '';
        $rooms_p = $t_max_rooms? (' (' . number_format($t_active_rooms*100/$t_max_rooms, 0) . '%)'): '';
        $bbb_lb_algo = get_config('bbb_lb_algo', 'wo');

        if ($bbb_lb_algo == 'wll') {
            $bbb_lb_algo_info = $langBBBLBMethodWLL;
        } else if ($bbb_lb_algo == 'wlr') {
            $bbb_lb_algo_info = $langBBBLBMethodWLR;
        } else if ($bbb_lb_algo == 'wlc') {
            $bbb_lb_algo_info = $langBBBLBMethodWLC;
        } else if ($bbb_lb_algo == 'wlm') {
            $bbb_lb_algo_info = $langBBBLBMethodWLM;
        } else if ($bbb_lb_algo == 'wlv') {
            $bbb_lb_algo_info = $langBBBLBMethodWLV;
        } else {
            $bbb_lb_algo_info = $langBBBLBMethodWO;
        }

        $bbb_cnt .= "<tr>" .
            "<td><strong>$langTotal:</strong></td>" .
            "<td>&nbsp;</td>" .
            "<td class>$t_connected_users / $t_max_users</td>" .
            "<td class>$t_active_rooms / $t_max_rooms</td>" .
            "<td class>$t_mics / $t_cameras</td>" .
            "<td class>$bbb_lb_algo_info</td>" .
            "</tr>";
    }
    $data['bbb_cnt'] = $bbb_cnt;

    // Enabled rooms
    $html_enabled_rooms = '';
    if (count($q)>0) {
        $html_enabled_rooms .= "<div class='text-heading-h3'>$langActiveRooms</div>";
        $html_enabled_rooms .= "<div class='table-responsive mt-2'>";
        $html_enabled_rooms .= "<table class='table-default'>
            <thead>
            <tr><th>$langServer</th>
                <th>$langCourse</th>
                <th>$langTitle</th>
                <th>$langUsers</th>
                <th>$langBBBMics / $langBBBCameras</th>
                <th>$langStart</th>
            </thead>";
        foreach ($q as $srv) {
            $meetings = get_active_rooms_details($srv->server_key, $srv->api_url);
            foreach ($meetings as $meeting) {
                $meeting_id = $meeting['meetingId'];
                if ($meeting_id != null) {
                    $course = Database::get()->querySingle("SELECT code, course.title, tc_session.title AS mtitle
                        FROM course LEFT JOIN tc_session ON course.id = tc_session.course_id
                        WHERE tc_session.meeting_id = ?s", $meeting_id);
                    // don't list meetings from other APIs
                    if (!$course) {
                        continue;
                    }
                    $createstamp = $meeting['createTime'];
                    $createDate = date('d/m/Y H:i:s', $createstamp/1000);
                    $recording = $meeting['recording'];
                    $mod_pw = $meeting['moderatorPw'];
                    $att_pw = $meeting['attendeePw'];
                    $mparticipants = $meeting['participantCount'];
                    $mvoicecount = $meeting['voiceParticipantCount'];
                    $mvideocount = $meeting['videoCount'];
                    $course_code = $course->code;
                    $course_title = $course->title;
                    // meeting name without course code
                    $mtitle = $course->mtitle;
                    // meeting name with course code
                    $title = $meeting['meetingName'];
                    $courseLink = "<a href='/modules/tc/?course=$course_code'>" . q($course_title) . "</a>";
                    $joinLink = "<a href='/modules/tc/index.php?course=$course_code&amp;choice=do_join&amp;meeting_id=" . urlencode($meeting_id) . "&amp;title=".urlencode($title)."&amp;att_pw=".urlencode($att_pw)."&amp;mod_pw=".urlencode($mod_pw)."' target='_blank'>" . q($mtitle) . "</a>";

                    $html_enabled_rooms .= "<tr>" .
                        "<td>$srv->hostname</td>" .
                        "<td>$courseLink ($course_code)</td>" .
                        "<td>$joinLink</td>" .
                        "<td>$mparticipants</td>" .
                        "<td>$mvoicecount / $mvideocount</td>" .
                        "<td>$createDate</td>" .
                        "</tr>";
                }
            }
        }
        $html_enabled_rooms .= "</table></div>";
    }
    $data['html_enabled_rooms'] = $html_enabled_rooms;

    view('admin.other.extapps.bbb.index', $data);
}

/**
 * @brief update existing bbb session with new bbb server
 * @param type $course_id
 * @param type $bbb_server_id
 */
function update_bbb_session($course_id, $bbb_server_id) {

    $q = Database::get()->queryArray("SELECT id FROM tc_session JOIN tc_servers
            ON running_at = tc_servers.id
               WHERE tc_servers.type = 'bbb'
            AND course_id = ?d", $course_id);
    if ($q) {
        foreach ($q as $data) {
            Database::get()->query("UPDATE tc_session SET running_at = ?d WHERE id = ?d", $bbb_server_id, $data->id);
        }
    }
}
