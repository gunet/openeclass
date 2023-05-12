<?php

/* ========================================================================
 * Open eClass
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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

// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/tc/functions.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/course.class.php';

$toolName = $langBBBConf;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'extapp.php', 'name' => $langExtAppConfig);

$available_themes = active_subdirs("$webDir/template", 'theme.html');
$tree = new Hierarchy();
$course = new Course();

load_js('tools.js');
load_js('validation.js');
load_js('select2');
load_js('datatables');

$head_content .= "<script type='text/javascript'>
        $(document).ready(function() {
            $('#bbb_courses').DataTable ({
                'sPaginationType': 'full_numbers',
                'bAutoWidth': true,
                'searchDelay': 1000,
                'order' : [[1, 'desc']],
                'oLanguage': {
                   'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                   'sZeroRecords':  '" . js_escape($langNoResult) . "',
                   'sInfo':         '$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults',
                   'sInfoEmpty':    '$langDisplayed 0 $langTill 0 $langFrom2 0 $langResults2',
                   'sInfoFiltered': '',
                   'sInfoPostFix':  '',
                   'sSearch':       '',
                   'sUrl':          '',
                   'oPaginate': {
                       'sFirst':    '&laquo;',
                       'sPrevious': '&lsaquo;',
                       'sNext':     '&rsaquo;',
                       'sLast':     '&raquo;'
                   }
               }
            });
            $('.dataTables_filter input').attr({
                  class : 'form-control input-sm',
                  placeholder : '$langSearch...'
                });
        $('#select-courses').select2();
        $('#selectAll').click(function(e) {
            e.preventDefault();
            var stringVal = [];
            $('#select-courses').find('option').each(function(){
                stringVal.push($(this).val());
            });
            $('#select-courses').val(stringVal).trigger('change');
        });
        $('#removeAll').click(function(e) {
            e.preventDefault();
            var stringVal = [];
            $('#select-courses').val(stringVal).trigger('change');
        });
        $('#allCourses').click(function(e) {
            var sc = $('#select-courses');
            e.preventDefault();
            if (!sc.find('option[value=0]').length) {
                sc.prepend('<option value=\"0\">" . js_escape($langToAllCourses) . "</option>');
            }
            $('#select-courses').val(['0']).trigger('change');
        });
    });
</script>";

$bbb_server = isset($_GET['edit_server']) ? intval($_GET['edit_server']) : '';

if (isset($_GET['delete_tc_course']) and $_GET['list']) {
    Database::get()->querySingle("DELETE FROM course_external_server
                                          WHERE course_id = ?d
                                          AND external_server = ?d", $_GET['delete_tc_course'], $_GET['list']);
    Session::Messages($langBBBDeleteCourseSuccess, 'alert-success');
}
if (isset($_POST['code_to_assign'])) {
    $course_id_to_assign = course_code_to_id($_POST['code_to_assign']);
    if (empty($course_id_to_assign)) {
        Session::Messages($langBBBAddCourseFail, 'alert-warning');
    } else {
        $q = Database::get()->querySingle("SELECT course_id from course_external_server
                                           WHERE course_id = ?d", $course_id_to_assign);
        if (empty($q->course_id)) {
            Database::get()->query("INSERT INTO course_external_server SET course_id = ?d, external_server = ?d",
                                    $course_id_to_assign, $_POST['tc_server']);
            Session::Messages($langBBBAddCourseSuccess,'alert-success');
        } else {
            Session::Messages($langBBBAddCourseFailExits,'alert-warning');
        }
    }
}
if (isset($_GET['add_course_to_tc'])) {
    $tc_server = $_GET['tc_server'];
    $tool_content .= "<div class='form-wrapper'>";
        $tool_content .= "<form action='$_SERVER[SCRIPT_NAME]?list=$tc_server' method='post' class='form-horizontal' role='form'>
                        <div class='form-group'>
                            <label class='col-sm-3 control-label'>$langCourseCode :</label>
                            <div class='col-xs-3'>
                                <input type='text' class='form-control' name='code_to_assign'>
                            </div>
                        </div>
                        <div class='form-group'>
                            <div class='col-xs-offset-2 col-xs-10'>
                                <button class='btn btn-primary' type='submit'>$langAdd</button>&nbsp;&nbsp;
                                <a class='btn btn-default' href='$_SERVER[SCRIPT_NAME]'>$langBack</a>&nbsp;&nbsp;
                            </div>
                        </div>
                        <input type='hidden' name='tc_server' value='$tc_server'>
                    </form>";
    $tool_content .= "</div>";
}

// list of courses with specific bbb server
else if (isset($_GET['list'])) {
    $pageName = $langOtherCourses;
    $tool_content .= action_bar(array(
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
    $tool_content .= "<table class='table-default' id='bbb_courses'>";
    $tool_content .= "<thead>";
    $tool_content .= "<th>$langCourse</th>";
    $tool_content .= "<th>$langFaculty</th>";
    $tool_content .= "<th><span class='fa fa-cogs'></span></th>";
    $tool_content .= "</thead>";
    $tool_content .= "<tbody>";

    foreach ($q as $data) {
        // ger course full path
        $departments = $course->getDepartmentIds($data->course_id);
        $i = 1;
        $dep = '';
        foreach ($departments as $department) {
            $br = ($i < count($departments)) ? '<br/>' : '';
            $dep .= $tree->getFullPath($department) . $br;
            $i++;
        }
        $code = course_id_to_code($data->course_id);
        $tool_content .= "<tr>";
        $tool_content .= "<td><a href='{$urlServer}courses/$code/' target='_blank'>" . course_id_to_title($data->course_id) . "</a>
                                    &nbsp;<small>(" . course_id_to_code($data->course_id). ")</small>
                                    <div style='margin-top: 5px;'><small>". course_id_to_prof($data->course_id) . "</small></div>
                          </td>";
        $tool_content .= "<td>". $dep ."</td>";
        $tool_content .= "<td class='option-btn-cell'>".
                            action_button(array(
                                array('title' => $langDelete,
                                  'url' => "$_SERVER[SCRIPT_NAME]?list=$bbb&delete_tc_course=$data->course_id",
                                  'icon' => 'fa-times',
                                  'class' => 'delete',
                                  'confirm' => $langConfirmDelete))) .
                        "</td>";
        $tool_content .= "</tr>";
    }
    $tool_content .= "</tbody>";
    $tool_content .= "</table>";
}
else if (isset($_GET['add_server'])) {
    $pageName = $langAddServer;
    $toolName = $langBBBConf;
    $navigation[] = array('url' => 'bbbmoduleconf.php', 'name' => $langBBBConf);
    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "bbbmoduleconf.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));

    $tool_content .= "<div class='form-wrapper'>";
    $tool_content .= "<form class='form-horizontal' role='form' name='serverForm' action='$_SERVER[SCRIPT_NAME]' method='post'>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='api_url_form' class='col-sm-3 control-label'>API URL:</label>
            <div class='col-sm-9'><input class='form-control' type='text' id='api_url_form' name='api_url_form'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='key_form' class='col-sm-3 control-label'>$langPresharedKey:</label>
            <div class='col-sm-9'><input class='form-control' type='text' name='key_form'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='max_rooms_form' class='col-sm-3 control-label'>$langMaxRooms:</label>
            <div class='col-sm-9'><input class='form-control' type='text' id='max_rooms_for' name='max_rooms_form'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label for='max_rooms_form' class='col-sm-3 control-label'>$langMaxUsers:</label>
            <div class='col-sm-9'><input class='form-control' type='text' id='max_users_form' name='max_users_form'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label class='col-sm-3 control-label'>$langBBBEnableRecordings:</label>
            <div class='col-sm-9 radio'><label><input  type='radio' id='recordings_on' name='enable_recordings' value='true'>$langYes</label></div>
            <div class='col-sm-9 radio'><label><input  type='radio' id='recordings_off' name='enable_recordings' checked='true' value='false'>$langNo</label></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";

    $tool_content .= "<label class='col-sm-3 control-label'>$langActivate:</label>
            <div class='col-sm-9 radio'><label><input  type='radio' id='enabled_true' name='enabled' checked='true' value='true'>$langYes</label></div>
            <div class='col-sm-offset-3 col-sm-9 radio'><label><input  type='radio' id='enabled_false' name='enabled' value='false'>$langNo</label></div>
        </div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label class='col-sm-3 control-label'>$langBBBServerOrder:</label>
            <div class='col-sm-9'><input class='form-control' type='text' name='weight'></div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group' id='courses-list'>
                <label class='col-sm-3 control-label'>$langUseOfTc:&nbsp;&nbsp;
                <span class='fa fa-info-circle' data-toggle='tooltip' data-placement='right' title='$langToAllCoursesInfo'></span></label>
                <div class='col-sm-9'>
                    <select class='form-control' name='tc_courses[]' multiple class='form-control' id='select-courses'>";
                    $courses_list = Database::get()->queryArray("SELECT id, code, title FROM course
                                                        WHERE id NOT IN (SELECT course_id FROM course_external_server)
                                                        AND visible != " . COURSE_INACTIVE . "
                                                        ORDER BY title");
                    $tool_content .= "<option value='0' selected><h2>$langToAllCourses</h2></option>";
                    foreach($courses_list as $c) {
                        $tool_content .= "<option value='$c->id'>" . q($c->title) . " (" . q($c->code) . ")</option>";
                    }
        $tool_content .= "</select>
                    <a href='#' id='allCourses'>$langToAllCourses</a> | <a href='#' id='selectAll'>$langJQCheckAll</a> | <a href='#' id='removeAll'>$langJQUncheckAll</a>
                </div>
            </div>";
    $tool_content .= "<div class='form-group'><div class='col-sm-offset-3 col-sm-9'>";
    $tool_content .=    form_buttons(array(
                            array(
                                'text' => $langSave,
                                'name' => 'submit'
                            ),
                            array(
                                'href' => 'bbbmoduleconf.php'
                            )
                        ));
    $tool_content .= "</div></div></form></div>";

    $tool_content .='<script language="javaScript" type="text/javascript">
        //<![CDATA[
            var chkValidator  = new Validator("serverForm");
            chkValidator.addValidation("key_form","req","' . $langBBBServerAlertKey . '");
            chkValidator.addValidation("api_url_form","req","' . $langBBBServerAlertAPIUrl . '");
            chkValidator.addValidation("max_rooms_form","req","' . $langBBBServerAlertMaxRooms . '");
            chkValidator.addValidation("max_rooms_form","numeric","' . $langBBBServerAlertMaxRooms . '");
            chkValidator.addValidation("max_users_form","req","' . $langBBBServerAlertMaxUsers . '");
            chkValidator.addValidation("max_users_form","numeric","' . $langBBBServerAlertMaxUsers . '");
            chkValidator.addValidation("weight","req","' . $langBBBServerAlertOrder . '");
            chkValidator.addValidation("weight","numeric","' . $langBBBServerAlertOrder . '");
        //]]></script>';

} else if (isset($_GET['delete_server'])) {
    $id = $_GET['delete_server'];
    Database::get()->query("DELETE FROM tc_servers WHERE id=?d", $id);
    Database::get()->query("DELETE FROM course_external_server WHERE external_server=?d", $id);
    // Display result message
    Session::Messages($langFileUpdatedSuccess, 'alert-success');
    redirect_to_home_page('modules/admin/bbbmoduleconf.php');
}

// Save new config: edit/add tc server, course add/delete into tc server
else if (isset($_POST['submit'])) {
    $api_url = $_POST['api_url_form'];
    if (!preg_match('/\/$/', $api_url)) { // append '/' if doesn't exist
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
    if (isset($_POST['tc_courses'])) {
        $tc_courses = $_POST['tc_courses'];
    } else {
        $tc_courses = [];
    }
    if (in_array(0, $tc_courses)) {
        $allcourses = 1; // tc server is assigned to all courses
    } else {
        $allcourses = 0; // tc server is assigned to specific courses
    }
    if (isset($_POST['id_form'])) {
        $id = $_POST['id_form'];
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
    Session::Messages($langFileUpdatedSuccess,"alert-success");
    redirect_to_home_page("modules/admin/bbbmoduleconf.php");
} // end of if($submit): edit/add tc server, course add/delete into tc server

// submit bbb config
else if (isset($_POST['submit_config'])) {
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
    Session::Messages($langFileUpdatedSuccess,"alert-success");
    redirect_to_home_page("modules/admin/bbbmoduleconf.php");
}

// bbb config form
else if (isset($_GET['edit_config'])) {
    $pageName = $langBBBConfig;
    $bbb_max_duration = get_config('bbb_max_duration', 0);
    $bbb_max_part_per_room = get_config('bbb_max_part_per_room', 0);
    $bbb_lb_weight_part = get_config('bbb_lb_weight_part', 1);
    $bbb_lb_weight_mic = get_config('bbb_lb_weight_mic', 2);
    $bbb_lb_weight_camera = get_config('bbb_lb_weight_camera', 2);
    $bbb_lb_weight_room = get_config('bbb_lb_weight_room', 50);
    $bbb_lb_algo = get_config('bbb_lb_algo', 'wo');
    $bbb_lb_wo_checked = $bbb_lb_wll_checked = $bbb_lb_wlr_checked = $bbb_lb_wlc_checked =
    $bbb_lb_wlm_checked = $bbb_lb_wlv_checked = '';
    $checked_recording = get_config('bbb_recording', 1) ? 'checked' : '';
    $checked_muteOnStart = get_config('bbb_muteOnStart', 0) ? 'checked' : '';
    $checked_DisableMic = get_config('bbb_DisableMic', 0) ? 'checked' : '';
    $checked_DisableCam = get_config('bbb_DisableCam', 0) ? 'checked' : '';
    $checked_webcamsOnlyForModerator = get_config('bbb_webcamsOnlyForModerator', 0) ? 'checked' : '';
    $checked_DisablePrivateChat = get_config('bbb_DisablePrivateChat', 0) ? 'checked' : '';
    $checked_DisablePublicChat = get_config('bbb_DisablePublicChat', 0) ? 'checked' : '';
    $checked_DisableNote = get_config('bbb_DisableNote', 0) ? 'checked' : '';
    $checked_HideUserList = get_config('bbb_HideUserList', 0) ? 'checked' : '';
    $checked_hideParticipants = get_config('bbb_hideParticipants', 0) ? 'checked' : '';

    if ($bbb_lb_algo == 'wll') {
        $bbb_lb_wll_checked = "checked='true'";
    } else if ($bbb_lb_algo == 'wlr') {
        $bbb_lb_wlr_checked = "checked='true'";
    } else if ($bbb_lb_algo == 'wlc') {
        $bbb_lb_wlc_checked = "checked='true'";
    } else if ($bbb_lb_algo == 'wlm') {
        $bbb_lb_wlm_checked = "checked='true'";
    } else if ($bbb_lb_algo == 'wlv') {
        $bbb_lb_wlv_checked = "checked='true'";
    } else {
        $bbb_lb_wo_checked = "checked='true'"; // default
    }

    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "bbbmoduleconf.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));

    $tool_content .= "<div class='form-wrapper'>";
    $tool_content .= "<form class='form-horizontal' role='form' name='serverForm' action='$_SERVER[SCRIPT_NAME]' method='post'>";

    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label class='col-sm-3'>$langBBBLBMethod:</label>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<span class='col-sm-3 radio'><label><input type='radio' name='bbb_lb_algo' value='wo' $bbb_lb_wo_checked>$langBBBLBMethodWO</label></span>";
    $tool_content .= "<span class='fa fa-info-circle' data-toggle='tooltip' data-placement='right' title='$langBBBLBMethodWOInfo'></span></label>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<span class='col-sm-3 radio'><label><input type='radio' name='bbb_lb_algo' value='wll' $bbb_lb_wll_checked>$langBBBLBMethodWLL</label></span>";
    $tool_content .= "<span class='fa fa-info-circle' data-toggle='tooltip' data-placement='right' title='$langBBBLBMethodWLLInfo'></span></label>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<span class='col-sm-3 radio'><label><input type='radio' name='bbb_lb_algo' value='wlr' $bbb_lb_wlr_checked>$langBBBLBMethodWLR</label></span>";
    $tool_content .= "<span class='fa fa-info-circle' data-toggle='tooltip' data-placement='right' title='$langBBBLBMethodWLRInfo'></span></label>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<span class='col-sm-3 radio'><label><input type='radio' name='bbb_lb_algo' value='wlc' $bbb_lb_wlc_checked>$langBBBLBMethodWLC</label></span>";
    $tool_content .= "<span class='fa fa-info-circle' data-toggle='tooltip' data-placement='right' title='$langBBBLBMethodWLCInfo'></span></label>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<span class='col-sm-3 radio'><label><input type='radio' name='bbb_lb_algo' value='wlm' $bbb_lb_wlm_checked>$langBBBLBMethodWLM</label></span>";
    $tool_content .= "<span class='fa fa-info-circle' data-toggle='tooltip' data-placement='right' title='$langBBBLBMethodWLMInfo'></span></label>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<span class='col-sm-3 radio'><label><input type='radio' name='bbb_lb_algo' value='wlv' $bbb_lb_wlv_checked>$langBBBLBMethodWLV</label></span>";
    $tool_content .= "<span class='fa fa-info-circle' data-toggle='tooltip' data-placement='right' title='$langBBBLBMethodWLVInfo'></span></label>";
    $tool_content .= "</div>";

    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label class='col-sm-3'>$langBBBLBWeights:</label>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<span class='col-sm-3 control-label'>$langBBBLBWeightParticipant:</span>";
    $tool_content .= "<span class='col-sm-2'><label><input class='form-control' type='number' min='1' max='1000' step='1' pattern='\d+' id='bbb_lb_weight_part' name='bbb_lb_weight_part' value='$bbb_lb_weight_part'></label></span>";
    $tool_content .= "<span class='fa fa-info-circle' data-toggle='tooltip' data-placement='right' title='$langBBBLBWeightParticipantInfo'></span></label>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<span class='col-sm-3 control-label'>$langBBBLBWeightMic:</span>";
    $tool_content .= "<span class='col-sm-2'><label><input class='form-control' type='number' min='1' max='1000' step='1' pattern='\d+' id='bbb_lb_weight_mic' name='bbb_lb_weight_mic' value='$bbb_lb_weight_mic'></label></span>";
    $tool_content .= "<span class='fa fa-info-circle' data-toggle='tooltip' data-placement='right' title='$langBBBLBWeightMicInfo'></span></label>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<span class='col-sm-3 control-label'>$langBBBLBWeightCamera:</span>";
    $tool_content .= "<span class='col-sm-2'><label><input class='form-control' type='number' min='1' max='1000' step='1' pattern='\d+' id='bbb_lb_weight_camera' name='bbb_lb_weight_camera' value='$bbb_lb_weight_camera'></label></span>";
    $tool_content .= "<span class='fa fa-info-circle' data-toggle='tooltip' data-placement='right' title='$langBBBLBWeightCameraInfo'></span></label>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<span class='col-sm-3 control-label'>$langBBBLBWeightRoom:</span>";
    $tool_content .= "<span class='col-sm-2'><label><input class='form-control' type='number' min='1' max='1000' step='1' pattern='\d+' id='bbb_lb_weight_room' name='bbb_lb_weight_room' value='$bbb_lb_weight_room'></label></span>";
    $tool_content .= "<span class='fa fa-info-circle' data-toggle='tooltip' data-placement='right' title='$langBBBLBWeightRoomInfo'></span></label>";
    $tool_content .= "</div>";

    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label class='col-sm-5'>$langBBBDefaultNewRoom:</label>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<div class='col-sm-10 checkbox'>";
    $tool_content .= "<label><input type='checkbox' name='bbb_recording' $checked_recording value='1'>$langBBBRecord</label>";
    $tool_content .= "</div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<div class='col-sm-10 checkbox'>";
    $tool_content .= "<label><input type='checkbox' name='bbb_muteOnStart' $checked_muteOnStart value='1'>$langBBBmuteOnStart</label>";
    $tool_content .= "</div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<div class='col-sm-10 checkbox'>";
    $tool_content .= "<label><input type='checkbox' name='bbb_DisableMic' $checked_DisableMic value='1'>$langBBBlockSettingsDisableMic</label>";
    $tool_content .= "</div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<div class='col-sm-10 checkbox'>";
    $tool_content .= "<label><input type='checkbox' name='bbb_DisableCam' $checked_DisableCam value='1'>$langBBBlockSettingsDisableCam</label>";
    $tool_content .= "</div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<div class='col-sm-10 checkbox'>";
    $tool_content .= "<label><input type='checkbox' name='bbb_webcamsOnlyForModerator' $checked_webcamsOnlyForModerator value='1'>$langBBBwebcamsOnlyForModerator</label>";
    $tool_content .= "</div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<div class='col-sm-10 checkbox'>";
    $tool_content .= "<label><input type='checkbox' name='bbb_DisablePrivateChat' $checked_DisablePrivateChat value='1'>$langBBBlockSettingsDisablePrivateChat</label>";
    $tool_content .= "</div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<div class='col-sm-10 checkbox'>";
    $tool_content .= "<label><input type='checkbox' name='bbb_DisablePublicChat' $checked_DisablePublicChat value='1'>$langBBBlockSettingsDisablePublicChat</label>";
    $tool_content .= "</div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<div class='col-sm-10 checkbox'>";
    $tool_content .= "<label><input type='checkbox' name='bbb_DisableNote' $checked_DisableNote value='1'>$langBBBlockSettingsDisableNote</label>";
    $tool_content .= "</div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<div class='col-sm-10 checkbox'>";
    $tool_content .= "<label><input type='checkbox' name='bbb_HideUserList' $checked_HideUserList value='1'>$langBBBlockSettingsHideUserList</label>";
    $tool_content .= "</div>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<div class='col-sm-10 checkbox'>";
    $tool_content .= "<label><input type='checkbox' name='bbb_hideParticipants' $checked_hideParticipants value='1'>$langBBBHideParticipants</label>";
    $tool_content .= "</div>";
    $tool_content .= "</div>";

    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<label class='col-sm-3'>$langOtherOptions:</label>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<span class='col-sm-3 control-label'>$langBBBMaxDuration:</span>";
    $tool_content .= "<span class='col-sm-2'><label><input class='form-control' type='number' min='0' max='10000' step='10' pattern='\d+' id='bbb_max_duration' name='bbb_max_duration' value='$bbb_max_duration'></label></span>";
    $tool_content .= "<span class='fa fa-info-circle' data-toggle='tooltip' data-placement='right' title='$langInMinutes'></span></label>";
    $tool_content .= "</div>";
    $tool_content .= "<div class='form-group'>";
    $tool_content .= "<span class='col-sm-3 control-label'>$langBBBMaxPartPerRoom:</span>";
    $tool_content .= "<span class='col-sm-2'><label><input class='form-control' type='number' min='0' max='1000' step='10' pattern='\d+' id='bbb_max_part_per_room' name='bbb_max_part_per_room' value='$bbb_max_part_per_room'></label></span>";
    $tool_content .= "<span class='fa fa-info-circle' data-toggle='tooltip' data-placement='right' title='$langBBBMaxPartPerRoomInfo'></span></label>";
    $tool_content .= "</div>";

    $tool_content .= "<div class='form-group'>
                        <div class='col-sm-offset-3'>
                            <input class='btn btn-primary' type='submit' name='submit_config' value='Υποβολή'>
                        </div>
                     </div>";
    $tool_content .= "</form></div>";
} // end of bbb config

// edit server form
else {
    if (isset($_GET['edit_server'])) {
        $pageName = $langEdit;
        $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "bbbmoduleconf.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')));

        $server = Database::get()->querySingle("SELECT * FROM tc_servers WHERE id = ?d", $bbb_server);

        $tool_content .= "<div class='form-wrapper'>";
        $tool_content .= "<form class='form-horizontal' role='form' name='serverForm' action='$_SERVER[SCRIPT_NAME]' method='post'>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='hostname_form' class='col-sm-3 control-label'>$langName:</label>
                <div class='col-sm-9'><input class='form-control' type='text' id='hostname_form' name='hostname_form' value='$server->hostname'></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='api_url_form' class='col-sm-3 control-label'>API URL:</label>
                <div class='col-sm-9'><input class='form-control' type='text' id='api_url_form' name='api_url_form' value='$server->api_url'></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='key_form' class='col-sm-3 control-label'>$langPresharedKey:</label>
                <div class='col-sm-9'><input class='form-control' type='text' name='key_form' value='$server->server_key'></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='max_rooms_form' class='col-sm-3 control-label'>$langMaxRooms:</label>
                <div class='col-sm-9'><input class='form-control' type='text' id='max_rooms_for' name='max_rooms_form' value='$server->max_rooms'></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label for='max_rooms_form' class='col-sm-3 control-label'>$langMaxUsers:</label>
                <div class='col-sm-9'><input class='form-control' type='text' id='max_users_form' name='max_users_form' value='$server->max_users'></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label class='col-sm-3 control-label'>$langBBBEnableRecordings:</label>";
        if ($server->enable_recordings == "false") {
            $checkedfalse = " checked='true' ";
        } else {
            $checkedfalse = '';
        }
        if ($server->enable_recordings == "true") {
            $checkedtrue = " checked='true' ";
        } else {
            $checkedtrue = '';
        }
        $tool_content .= "<div class='col-sm-9 radio'><label><input  type='radio' id='recordings_on' name='enable_recordings' value='true' $checkedtrue>$langYes</label></div>";
        $tool_content .= "<div class='col-sm-9 radio'><label><input  type='radio' id='recordings_off' name='enable_recordings' value='false' $checkedfalse>$langNo</label></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'>";

        $tool_content .= "<label class='col-sm-3 control-label'>$langActivate:</label>";
        if ($server->enabled == "false") {
            $checkedfalse2 = " checked='false' ";
        } else {
            $checkedfalse2 = '';
        }
        if ($server->enabled == "true") {
            $checkedtrue2 = " checked='false' ";
        } else {
            $checkedtrue2 = '';
        }

        $tool_content .= "<div class='col-sm-9 radio'><label><input type='radio' id='enabled_true' name='enabled' $checkedtrue2 value='true'>$langYes</label></div>";
        $tool_content .= "<div class='col-sm-offset-3 col-sm-9 radio'><label><input type='radio' id='enabled_false' name='enabled' $checkedfalse2 value='false'>$langNo</label></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group'>";
        $tool_content .= "<label class='col-sm-3 control-label'>$langBBBServerOrder:</label>
                <div class='col-sm-9'><input class='form-control' type='text' name='weight' value='$server->weight'></div>";
        $tool_content .= "</div>";
        $tool_content .= "<div class='form-group' id='courses-list'>
                <label class='col-sm-3 control-label'>$langUseOfTc:&nbsp;&nbsp;
                <span class='fa fa-info-circle' data-toggle='tooltip' data-placement='right' title='$langToAllCoursesInfo'></span></label>
                <div class='col-sm-9'>
                    <select class='form-control' name='tc_courses[]' multiple class='form-control' id='select-courses'>";
                    $courses_list = Database::get()->queryArray("SELECT id, code, title FROM course WHERE id
                                                                    NOT IN (SELECT course_id FROM course_external_server)
                                                                    AND visible != " . COURSE_INACTIVE . "
                                                                ORDER BY title");
                    if ($server->all_courses == '1') {
                        $tool_content .= "<option value='0' selected>$langToAllCourses</option>";
                    } else {
                        $tc_courses_list = Database::get()->queryArray("SELECT id, code, title FROM course WHERE id
                                                    IN (SELECT course_id FROM course_external_server WHERE external_server = ?d) ORDER BY title", $_GET['edit_server']);
                        if (count($tc_courses_list) > 0) {
                            foreach($tc_courses_list as $c) {
                                $tool_content .= "<option value='$c->id' selected>" . q($c->title) . " (" . q($c->code) . ")</option>";
                            }
                            $tool_content .= "<option value='0'><h2>$langToAllCourses</h2></option>";
                        }
                    }
                    foreach($courses_list as $c) {
                        $tool_content .= "<option value='$c->id'>" . q($c->title) . " (" . q($c->code) . ")</option>";
                    }
        $tool_content .= "</select>
                    <a href='#' id='allCourses'>$langToAllCourses</a> | <a href='#' id='selectAll'>$langJQCheckAll</a> | <a href='#' id='removeAll'>$langJQUncheckAll</a>
                </div>
            </div>";
        $tool_content .= "<input class='form-control' type = 'hidden' name = 'id_form' value='$bbb_server'>";
        $tool_content .= "<div class='form-group'>
                            <div class='col-sm-offset-3 col-sm-9'>
                                <input class='btn btn-primary' type='submit' name='submit' value='$langAddModify'>
                            </div>
                         </div>";
        $tool_content .= "</form></div>";
        $tool_content .='<script language="javaScript" type="text/javascript">
                //<![CDATA[
                    var chkValidator  = new Validator("serverForm");
                    chkValidator.addValidation("key_form","req","' . $langBBBServerAlertKey . '");
                    chkValidator.addValidation("hostname_form","req","' . $langBBBServerAlertName . '");
                    chkValidator.addValidation("api_url_form","req","' . $langBBBServerAlertAPIUrl . '");
                    chkValidator.addValidation("max_rooms_form","req","' . $langBBBServerAlertMaxRooms . '");
                    chkValidator.addValidation("max_rooms_form","numeric","' . $langBBBServerAlertMaxRooms . '");
                    chkValidator.addValidation("max_users_form","req","' . $langBBBServerAlertMaxUsers . '");
                    chkValidator.addValidation("max_users_form","numeric","' . $langBBBServerAlertMaxUsers . '");
                    chkValidator.addValidation("weight","req","' . $langBBBServerAlertOrder . '");
                    chkValidator.addValidation("weight","numeric","' . $langBBBServerAlertOrder . '");
                //]]></script>';

    } else {
        //display available BBB servers and running meetings
        $tool_content .= action_bar(array(
            array('title' => $langAddServer,
                'url' => "bbbmoduleconf.php?add_server",
                'icon' => 'fa-plus-circle',
                'level' => 'primary-label',
                'button-class' => 'btn-success'),
           array('title' => $langConfig,
                'url' => "bbbmoduleconf.php?edit_config",
                'icon' => 'fa-gear',
                'level' => 'primary-label'),
            array('title' => $langBack,
                'url' => "extapp.php",
                'icon' => 'fa-reply',
                'level' => 'primary-label')));

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
                "<p class='text-center' style='padding-top: 5px'><button class='btn btn-default' data-toggle='modal' data-target='#bbbCronInfoModal'>\\1</button></p>",
                $tc_cron_message);
            $tool_content .= "
                <div class='modal fade' id='bbbCronInfoModal' tabindex='-1' role='dialog' aria-labelledby='bbbCronInfoModalLabel'>
                  <div class='modal-dialog' role='document'>
                    <div class='modal-content'>
                      <div class='modal-header'>
                        <button type='button' class='close' data-dismiss='modal' aria-label='$langClose'><span aria-hidden='true'>&times;</span></button>
                        <h4 class='modal-title' id='bbbCronInfoModal'>$langBBBCronEnableTitle</h4>
                      </div>
                      <div class='modal-body'>
                        $langBBBCronEnableInstructions
                      </div>
                      <div class='modal-footer'>
                        <button type='button' class='btn btn-default' data-dismiss='modal'>$langClose</button>
                      </div>
                    </div>
                  </div>
                </div>";
        }
        $tool_content .= "
            <div class='alert $tc_cron_class' style='display: flex; align-items: center;'>
                <div style='margin-right: 15px'><span class='fa $tc_cron_icon fa-2x'></span></div>
                <div style='width: 100%'>$tc_cron_message</div>
            </div>";

        $q = Database::get()->queryArray("SELECT * FROM tc_servers WHERE `type` = 'bbb' ORDER BY weight");
        if (count($q)>0) {
            $tool_content .= "<div class='table-responsive'>";
            $tool_content .= "<table class='table-default'>
                <thead>
                <tr><th class = 'text-center'>$langName</th>
                    <th class = 'text-center'>$langBBBEnabled</th>
                    <th class = 'text-center'>$langUsers</th>
                    <th class = 'text-center'>$langActiveRooms</th>
                    <th class = 'text-center'>$langBBBMics / $langBBBCameras</th>
                    <th class = 'text-center'>$langBBBServerOrderP / $langBBBServerLoad</th>
                    <th class = 'text-center'>".icon('fa-gears')."</th></tr>
                </thead>";
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
                    $tool_content .= "<tr>" .
                        "<td class = 'text-center'>$srv->hostname</td>" .
                        "<td class = 'text-center'>$enabled_bbb_server$courses_note</td>" .
                        "<td class = 'text-center'>$connected_users / $srv->max_users</td>" .
                        "<td class = 'text-center'>$active_rooms / $srv->max_rooms</td>" .
                        "<td class = 'text-center'>$mics / $cameras</td>" .
                        "<td class = 'text-center'>$srv->weight / $server_load</td>";
                } else {
                    $tool_content .= "<tr>" .
                        "<td class = 'text-center'>$srv->hostname</td>" .
                        "<td class = 'text-center'>$enabled_bbb_server$courses_note</td>" .
                        "<td class = 'text-center'>&mdash;</td>" .
                        "<td class = 'text-center'>&mdash;</td>" .
                        "<td class = 'text-center'>&mdash;</td>" .
                        "<td class = 'text-center'>&mdash;</td>";
                }
                $tool_content .= "<td class='option-btn-cell'>" .
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

            $tool_content .= "<tr>" .
                    "<td class = 'text-right' colspan='2'>$langTotal:</td>" .
                    "<td class = 'text-center'>$t_connected_users / $t_max_users</td>" .
                    "<td class = 'text-center'>$t_active_rooms / $t_max_rooms</td>" .
                    "<td class = 'text-center'>$t_mics / $t_cameras</td>" .
                    "<td class = 'text-center'>$bbb_lb_algo_info</td>" .
                    "</tr>";
            $tool_content .= "</table></div>";
        } else {
             $tool_content .= "<div class='alert alert-warning'>$langNoAvailableBBBServers</div>";
        }

       $q = Database::get()->queryArray("SELECT * FROM tc_servers WHERE enabled = 'true' AND `type` = 'bbb' ORDER BY weight");
        if (count($q)>0) {
            $tool_content .= "<div class='inner-heading'>$langActiveRooms</div>";
            $tool_content .= "<div class='table-responsive'>";
            $tool_content .= "<table class='table-default'>
                <thead>
                <tr><th class = 'text-center'>$langServer</th>
                    <th class = 'text-center'>$langCourse</th>
                    <th class = 'text-center'>$langTitle</th>
                    <th class = 'text-center'>$langUsers</th>
                    <th class = 'text-center'>$langBBBMics / $langBBBCameras</th>
                    <th class = 'text-center'>$langStart</th>
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

                        $tool_content .= "<tr>" .
                            "<td>$srv->hostname</td>" .
                            "<td>$courseLink ($course_code)</td>" .
                            "<td>$joinLink</td>" .
                            "<td class = 'text-center'>$mparticipants</td>" .
                            "<td class = 'text-center'>$mvoicecount / $mvideocount</td>" .
                            "<td class = 'text-center'>$createDate</td>" .
                            "</tr>";
                    }
                }
            }
            $tool_content .= "</table></div>";
        }
    }
}

draw($tool_content, 3, null, $head_content);


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
