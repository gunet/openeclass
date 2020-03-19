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
                   'sZeroRecords':  '\" . $langNoResult . \"',
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
    });
</script>";


$bbb_server = isset($_GET['edit_server']) ? intval($_GET['edit_server']) : '';

if (isset($_POST['code_to_assign'])) {
    $course_id_to_assign = course_code_to_id($_POST['code_to_assign']);
    Database::get()->query("INSERT INTO course_external_server SET course_id = ?d, external_server = ?d",
                                    $course_id_to_assign, $_POST['tc_server']);
    Session::Messages("Το μάθημα προστέθηκε",'alert-success');
}
if (isset($_GET['add_course_to_tc'])) {
    $tc_server = $_GET['tc_server'];
    $tool_content .= "<div class='form-wrapper'>";
        $tool_content .= "<form action='$_SERVER[SCRIPT_NAME]' method='post' class='form-horizontal' role='form'>                        
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

// list of courses with bbb enabled
else if (isset($_GET['list'])) {
    $tool_content .= action_bar(array(
        array('title' => $langAdd,
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
        $tool_content .= "<td><a href='${urlServer}courses/$code/' target='_blank'>" . course_id_to_title($data->course_id) . "</a>
                                    &nbsp;<small>(" . course_id_to_code($data->course_id). ")</small>
                                    <div style='margin-top: 5px;'><small>". course_id_to_prof($data->course_id) . "</small></div>
                          </td>";
        $tool_content .= "<td>". $dep ."</td>";
        $tool_content .= "</tr>";
    }
    $tool_content .= "</tbody>";
    $tool_content .= "</table>";
}  else if (isset($_GET['add_server'])) {
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
                    <a href='#' id='selectAll'>$langJQCheckAll</a> | <a href='#' id='removeAll'>$langJQUncheckAll</a>
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

// Save new config
else if (isset($_POST['submit'])) {
    $key = $_POST['key_form'];
    $api_url = $_POST['api_url_form'];
    if (!preg_match('/\/$/', $api_url)) { // append '/' if doesn't exist
        $api_url = $api_url . '/';
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
                api_url = ?s,
                max_rooms =?s,
                max_users =?s,
                enable_recordings =?s,
                enabled = ?s,
                weight = ?d,
                all_courses = ?d
                WHERE id =?d", $key, $api_url, $max_rooms, $max_users, $enable_recordings, $enabled, $weight, $allcourses, $id);
        Database::get()->query("DELETE FROM course_external_server WHERE external_server = ?d", $id);
        if ($allcourses == 0) {
            foreach ($tc_courses as $tc_data) {
                Database::get()->query("INSERT INTO course_external_server SET course_id = ?d, external_server = ?d", $tc_data, $id);
                update_tc_session($tc_data, $id); // update existing tc_sessions
            }
        }
    } else {
        $q = Database::get()->query("INSERT INTO tc_servers (`type`, hostname, ip, server_key, api_url, max_rooms, max_users, enable_recordings, enabled, weight, all_courses) VALUES
        ('bbb', '', '', ?s, ?s, ?s, ?s, ?s, ?s, ?d, ?d)", $key, $api_url, $max_rooms, $max_users, $enable_recordings, $enabled, $weight, $allcourses);
        $tc_id = $q->lastInsertID;
        if ($allcourses == 0) {
            foreach ($tc_courses as $tc_data) {
                Database::get()->query("INSERT INTO course_external_server SET course_id = ?d, external_server = ?d", $tc_data, $tc_id);
                update_tc_session($tc_data, $tc_id); // update existing tc_sessions
            }
        }
    }
    // Display result message
    Session::Messages($langFileUpdatedSuccess,"alert-success");
    redirect_to_home_page("modules/admin/bbbmoduleconf.php");
} // end of if($submit)
// Display config edit form
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
                        $tool_content .= "<option value='0' selected><h2>$langToAllCourses</h2></option>";
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
                    <a href='#' id='selectAll'>$langJQCheckAll</a> | <a href='#' id='removeAll'>$langJQUncheckAll</a>
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
                    chkValidator.addValidation("api_url_form","req","' . $langBBBServerAlertAPIUrl . '");
                    chkValidator.addValidation("max_rooms_form","req","' . $langBBBServerAlertMaxRooms . '");
                    chkValidator.addValidation("max_rooms_form","numeric","' . $langBBBServerAlertMaxRooms . '");
                    chkValidator.addValidation("max_users_form","req","' . $langBBBServerAlertMaxUsers . '");
                    chkValidator.addValidation("max_users_form","numeric","' . $langBBBServerAlertMaxUsers . '");
                    chkValidator.addValidation("weight","req","' . $langBBBServerAlertOrder . '");
                    chkValidator.addValidation("weight","numeric","' . $langBBBServerAlertOrder . '");
                //]]></script>';

    } else {
        //display available BBB servers
        $tool_content .= action_bar(array(
            array('title' => $langAddServer,
                'url' => "bbbmoduleconf.php?add_server",
                'icon' => 'fa-plus-circle',
                'level' => 'primary-label',
                'button-class' => 'btn-success'),
            array('title' => $langBack,
                'url' => "extapp.php",
                'icon' => 'fa-reply',
                'level' => 'primary-label')));

        $q = Database::get()->queryArray("SELECT * FROM tc_servers WHERE `type` = 'bbb' ORDER BY weight");
        if (count($q)>0) {
            $tool_content .= "<div class='table-responsive'>";
            $tool_content .= "<table class='table-default'>
                <thead>
                <tr><th class = 'text-center'>API URL</th>
                    <th class = 'text-center'>$langBBBEnabled</th>
                    <th class = 'text-center'>$langOnlineUsers</th>
                    <th class = 'text-center'>$langMaxRooms</th>
                    <th class = 'text-center'>$langBBBServerOrderP</th>
                    <th class = 'text-center'>".icon('fa-gears')."</th></tr>
                </thead>";
            foreach ($q as $srv) {
                $enabled_bbb_server = ($srv->enabled == 'true')? $langYes : $langNo;
                $connected_users = get_connected_users($srv->server_key, $srv->api_url, $srv->ip);
                $q = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM course_external_server WHERE external_server = ?d", $srv->id);
                $num_of_tc_courses = $q->cnt;
                $mess = '';
                if ($srv->enabled == "true") {
                    $mess = " <small>(<a href='$_SERVER[SCRIPT_NAME]?list=$srv->id'>$langIn $num_of_tc_courses $langsCourses)</a></small>";
                }
                $tool_content .= "<tr>" .
                    "<td>$srv->api_url</td>" .
                    "<td class = 'text-center'>$enabled_bbb_server $mess</td>" .
                    "<td class = 'text-center'>$connected_users</td>" .
                    "<td class = 'text-center'>$srv->max_rooms</td>" .
                    "<td class = 'text-center'>$srv->weight</td>" .
                    "<td class='option-btn-cell'>" .
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
            $tool_content .= "</table></div>";
        } else {
             $tool_content .= "<div class='alert alert-warning'>$langNoAvailableBBBServers</div>";
        }
    }
}


draw($tool_content, 3, null, $head_content);


/**
 * @brief update existing tc_session with new tc_server
 * @param type $course_id
 * @param type $tc_server_id
 */
function update_tc_session($course_id, $tc_server_id) {

    $q = Database::get()->querySingle("SELECT * FROM tc_session WHERE course_id = ?d", $course_id);
    if ($q) {
        Database::get()->query("UPDATE tc_session SET running_at = ?d WHERE course_id = ?d", $tc_server_id, $course_id);
    }
}
