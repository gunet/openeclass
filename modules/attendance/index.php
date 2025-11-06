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

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

$require_login = true;
$require_current_course = true;
$require_user_registration = true;
$require_help = true;
$helpTopic = 'attendance';

if (isset($_GET['qrCode_presence'])) {
    require_once '../../include/init.php';
} else {
    require_once '../../include/baseTheme.php';
}
require_once 'functions.php';
require_once 'include/log.class.php';

$toolName = $langAttendance;

// needed for updating users lists
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (isset($_POST['assign_type'])) {
        if ($_POST['assign_type'] == 2) {
            $data = Database::get()->queryArray("SELECT name, id FROM `group` WHERE course_id = ?d ORDER BY name", $course_id);
        } else {
            $data = array();
            // users who don't participate in attendance
            $d1 = Database::get()->queryArray("SELECT user.id AS id, surname, givenname
                                            FROM user, course_user
                                                WHERE user.id = course_user.user_id
                                                AND course_user.course_id = ?d
                                                AND course_user.status = " . USER_STUDENT . "
                                            AND user.id NOT IN (SELECT uid FROM attendance_users WHERE attendance_id = ?d) ORDER BY surname", $course_id, $_REQUEST['attendance_id']);
            $data[0] = $d1;
            // users who already participate in attendance
            $d2 = Database::get()->queryArray("SELECT uid AS id, givenname, surname FROM user, attendance_users
                                        WHERE attendance_users.uid = user.id AND attendance_id = ?d ORDER BY surname", $_REQUEST['attendance_id']);
            $data[1] = $d2;
        }
    }
    echo json_encode($data);
    exit;
}

//Datepicker
load_js('tools.js');
load_js('datatables');

if ($is_editor) {
    if (isset($_GET['ins'])) {
        $columns = 'null, null, null, null, { orderable: false }';
    } else {
        $columns = 'null, null, null, null, null, { orderable: false }';
    }
} else if ($is_course_reviewer) {
    $columns = 'null, null, null, null, null';
}

@$head_content .= "
<script type='text/javascript'>
$(function() {
    var oTable = $('#users_table{$course_id}').DataTable ({
               'columns': [ $columns ],
               'lengthMenu': [10, 15, 20 , -1],
               'fnDrawCallback': function( oSettings ) {
                    $('#users_table{$course_id}_wrapper label input[type=search]').attr({
                      'class' : 'form-control input-sm ms-0 mb-3',
                      'placeholder' : '$langSearch...'
                    });
                    $('#users_table{$course_id}_wrapper label').attr('aria-label', '$langSearch');
                },
               'sPaginationType': 'full_numbers',
                'bSort': true,
                'searchDelay': 1000,
                'oLanguage': {
                       'lengthLabels': {'-1': '$langAllOfThem'},
                       'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                       'sZeroRecords':  '".$langNoResult."',
                       'sInfo':         '$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults',
                       'sInfoEmpty':    '',
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
    $('#user_attendances_form').on('submit', function (e) {
        oTable.rows().nodes().page.len(-1).draw();
    });
$('input[id=button_groups]').click(changeAssignLabel);
    $('input[id=button_some_users]').click(changeAssignLabel);
    $('input[id=button_some_users]').click(ajaxParticipants);
    $('input[id=button_all_users]').click(hideParticipants);
    function hideParticipants()
    {
        $('#participants_tbl').addClass('hide');
        $('#users_box').find('option').remove();
        $('#all_users').show();
    }
    function changeAssignLabel()
    {
        var assign_to_specific = $('input:radio[name=specific_attendance_users]:checked').val();
        if(assign_to_specific>0){
           ajaxParticipants();
        }
        if (this.id=='button_groups') {
           $('#users').text('$langGroups');
        }
        if (this.id=='button_some_users') {
           $('#users').text('$langUsers');
        }
    }
    function ajaxParticipants()
    {
        $('#all_users').hide();
        $('#participants_tbl').removeClass('hide');
        var type = $('input:radio[name=specific_attendance_users]:checked').val();
        $.post('$_SERVER[SCRIPT_NAME]?course=$course_code&attendance_id=".q($_REQUEST['attendance_id'])."&editUsers=1',
        {
          assign_type: type
        },
        function(data,status){
            var index;
            var parsed_data = JSON.parse(data);
            var select_content = '';
            var select_content_2 = '';
            if (type==2) {
                for (index = 0; index < parsed_data.length; ++index) {
                    select_content += '<option value=\"' + parsed_data[index]['id'] + '\">' + q(parsed_data[index]['name']) + '<\/option>';
                }
            }
            if (type==1) {
                for (index = 0; index < parsed_data[0].length; ++index) {
                    select_content += '<option value=\"' + parsed_data[0][index]['id'] + '\">' + q(parsed_data[0][index]['surname'] + ' ' + parsed_data[0][index]['givenname']) + '<\/option>';
                }
                for (index = 0; index < parsed_data[1].length; ++index) {
                    select_content_2 += '<option value=\"' + parsed_data[1][index]['id'] + '\">' + q(parsed_data[1][index]['surname'] + ' ' + parsed_data[1][index]['givenname']) + '<\/option>';
                }
            }
            $('#users_box').find('option').remove().end().append(select_content);
            $('#participants_box').find('option').remove().end().append(select_content_2);

        });
    }
});
</script>";


$display = TRUE;
if (isset($_REQUEST['attendance_id'])) {
    $attendance_id = $_REQUEST['attendance_id'];
    $attendance = Database::get()->querySingle("SELECT * FROM attendance WHERE id = ?d", $attendance_id);
    $navigation[] = ['url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langAttendance];
    $pageName = $langEditChange;
}

if (isset($_GET['qrCode_presence'])) {
    $attendance_id = $_REQUEST['attendance_id'];
    $activeUser = Database::get()->querySingle("SELECT id,`uid` FROM attendance_users WHERE attendance_id = ?d AND `uid` = ?d", $attendance_id, $uid);
    $activeAttendance = false;
    $q_activeAttendance = Database::get()->querySingle("SELECT start_date, end_date FROM attendance WHERE id = ?d", $attendance_id);
    $now = new DateTime();
    if ($q_activeAttendance->start_date < $now->format('Y-m-d H:i:s') and $q_activeAttendance->end_date > $now->format('Y-m-d H:i:s')) {
        $activeAttendance = true;
    }
    if ($activeUser && $activeAttendance) {
        $userID = $activeUser->uid;
        $actID = intval($_GET['actId']);
        $attend = 1;
        $checkForBook = Database::get()->querySingle("SELECT id, attend FROM attendance_book WHERE attendance_activity_id = ?d AND `uid` = ?d", $actID, $userID);
        if ($checkForBook) {
            if ($checkForBook->attend != $attend) {
                Database::get()->query("UPDATE attendance_book SET attend = ?d WHERE id = ?d", $attend, $checkForBook->id);
            }
        } else {
            Database::get()->query("INSERT INTO attendance_book SET `uid` = ?d, attendance_activity_id = ?d, attend = ?d, comments = ''", $userID, $actID, $attend);
        }
        triggerAttendanceGame($course_id, $userID, $attendance_id, AttendanceEvent::UPDATE);
        Session::flash('message', $langAddPresenceSuccess);
        Session::flash('alert-class', 'alert-success');
    } else {
        Session::flash('message', $langForbidden);
        Session::flash('alert-class', 'alert-warning');
    }
    // If the attendance tool is inactive, redirect the user to the portfolio.
    if (isset($toolContent_ErrorExists)) {
        redirect_to_home_page("main/portfolio.php");
    } else {
        redirect_to_home_page("modules/attendance/index.php?course=$course_code");
    }
}


if ($is_editor) {

    if (isset($_GET['download_qrcode'])) {
        $actID = intval($_GET['download_qrcode']);
        $fileNameAct = $actID . '_QRcode.svg';
        $dload_filename = "$webDir/courses/$course_code/attendance_qrcode/$actID/$fileNameAct";
        $filePath = $dload_filename;
        // Check if the file exists
        if (file_exists($filePath)) {
            // Set the appropriate headers to trigger a download
            header('Content-Description: File Transfer');
            header('Content-Type: image/svg+xml');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));

            // Clear output buffer
            ob_clean();
            flush();

            // Read the file and send it to the output buffer
            readfile($filePath);
            exit;
        } else {
            // Handle the error if the file does not exist
            echo "File not found.";
        }
    }

    // Presence via QR code
    if (isset($_GET['gen_qrcodePr'])) {
        $actId = intval(getDirectReference($_GET['actId']));
        $fileNameAct = $actId . '_QRcode.svg';

        // Initialize URL and parameters
        $string = $urlServer . "/modules/attendance/index.php?course=" . $course_code . "&attendance_id=" . $attendance_id . "&actId=" . $actId . "&qrCode_presence=true";

        // Create QR Code
        $renderer = new ImageRenderer(new RendererStyle(256), new SvgImageBackEnd());
        $writer = new Writer($renderer);

        // Generate the QR image
        $qr_image = $writer->writeString($string);

        // Base64 encode the SVG string
        $qr_image_base64 = base64_encode($qr_image);

        // Prepare the data URL for the SVG
        $qr_image_data_url = "data:image/svg+xml;base64," . $qr_image_base64;

        // Specify the path where you want to save the image
        $qrCode_dir = "$webDir/courses/$course_code/attendance_qrcode/$actId";
        if (!file_exists($qrCode_dir)) {
            mkdir("$webDir/courses/$course_code/attendance_qrcode/$actId", 0755, true);
        }
        if (file_exists($qrCode_dir . '/' . $fileNameAct)) {
            unlink($qrCode_dir . '/' . $fileNameAct);
        }
        file_put_contents($qrCode_dir . '/' . $fileNameAct, $qr_image);

        $downloadURL = $_SERVER['SCRIPT_NAME'] . "?course=$course_code&download_qrcode=$actId";

        // Prepare JavaScript content for modal
        $head_content .= "
            <script type='text/javascript'>
                $(document).ready(function() {
                    var downloadURL = '$downloadURL';
                    var bts = {
                        download: {
                            label: '" . js_escape($langDownload) . "',
                            className: 'submitAdminBtn gap-1',
                            callback: function (d) {
                                var anchor = document.createElement('a');
                                anchor.href = downloadURL;
                                anchor.target = '_blank';
                                anchor.download = '$fileNameAct';
                                anchor.click();
                            }
                        }
                    };
    
                    if (screenfull.enabled) {
                        bts.fullscreen = {
                            label: '" . js_escape($langFullScreen) . "',
                            className: 'submitAdminBtn gap-1',
                            callback: function() {
                                screenfull.request(document.getElementById('fileFrame'));
                                return false;
                            }
                        };
                    }
                    bts.cancel = {
                        label: '" . js_escape($langCancel) . "',
                        className: 'cancelAdminBtn'
                    };
    
                    bootbox.dialog({
                        size: 'large',
                        title: '" . js_escape($langGenQrCode) . "',
                        onEscape: function() {},
                        backdrop: true,
                        message: '<div class=\"row\">' +
                                    '<div class=\"col-sm-12\">' +
                                        '<div class=\"iframe-container\" style=\"height:300px;\">' +
                                            '<iframe title=\"{$langGenQrCode}\" id=\"fileFrame\" src=\"{$qr_image_data_url}\" style=\"width:260px; height:260px; margin:auto; display:block;\"></iframe>' +
                                        '</div>' +
                                    '</div>' +
                                 '</div>',
                        buttons: bts
                    });
                });
            </script>
        ";
    }

    // change attendance visibility
    if (isset($_GET['vis'])) {
        Database::get()->query("UPDATE attendance SET active = ?d WHERE id = ?d AND course_id = ?d", $_GET['vis'], $_GET['attendance_id'], $course_id);
        $log_details = array('action' => 'change attendance visibility', 'id' =>$_GET['attendance_id'], 'title' => get_attendance_title($_GET['attendance_id']), 'visibility' => $_GET['vis']);
        Log::record($course_id, MODULE_ID_ATTENDANCE, LOG_MODIFY, $log_details);
        Session::flash('message',$langGlossaryUpdated);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/attendance/index.php?course=$course_code");
    }
    if (isset($_GET['dup'])) {
        clone_attendance($attendance_id);
        Session::flash('message',$langCopySuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/attendance/index.php?course=$course_code");
    }

    // Create new attendance book
    if (isset($_POST['attendanceBookCreate'])) {
        $v = new Valitron\Validator($_POST);
        $v->rule('required', array('title', 'start_date', 'end_date'));
        $v->rule('integer', array('limit'));
        $v->rule('min', array('limit'), 0);
        $v->rule('date', array('start_date', 'end_date'));
        if (!empty($_POST['end_date'])) {
            $v->rule('dateBefore', 'start_date', $_POST['end_date']);
        }
        $v->labels(array(
            'title' => "$langTheField $langTitle",
            'start_date' => "$langTheField $langStart",
            'end_date' => "$langTheField $langEnd",
            'limit' => "$langTheField $langAttendanceLimitNumber"
        ));
        if($v->validate()) {
            $newTitle = $_POST['title'];
            $attendance_limit = empty($_POST['limit']) ? 0 : intval($_POST['limit']);
            $start_date = (new DateTime($_POST['start_date']))->format('Y-m-d H:i:s');
            $end_date = (new DateTime($_POST['end_date']))->format('Y-m-d H:i:s');
            $attendance_id = Database::get()->query("INSERT INTO attendance SET course_id = ?d, `limit` = ?d, active = 1, title = ?s, start_date = ?t, end_date = ?t", $course_id, $attendance_limit, $newTitle, $start_date, $end_date)->lastInsertID;
            $log_details = array('id' => $attendance_id, 'title' => $newTitle, 'attendance_limit' => $attendance_limit, 'start_date' => $start_date, 'end_date' => $end_date);
            Log::record($course_id, MODULE_ID_ATTENDANCE, LOG_INSERT, $log_details);
            Session::flash('message',$langChangeAttendanceCreateSuccess);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/attendance/index.php?course=$course_code");
        } else {
            Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
            redirect_to_home_page("modules/attendance/index.php?course=$course_code&new=1");
        }
    }
    //delete user from attendance list
    if (isset($_GET['deleteuser']) and isset($_GET['ruid'])) {
        delete_attendance_user($_GET['at'], $_GET['ruid']);
        $log_details = array('id' => $_GET['at'], 'title' => get_attendance_title($_GET['at']), 'action' => 'delete user', 'user_name' => uid_to_name($_GET['ruid']));
        Log::record($course_id, MODULE_ID_ATTENDANCE, LOG_MODIFY, $log_details);
        redirect_to_home_page("modules/attendance/index.php?course=$course_code&attendance_id=" . urlencode($_GET['at']) ."&attendanceBook=1");
    }

    //reset attendance users
    $distinct_users_count = 0;
    if (isset($_POST['resetAttendanceUsers'])) {
        if ($_POST['specific_attendance_users'] == 2) { // specific users group
            foreach ($_POST['specific'] as $g) {
                $ug = Database::get()->queryArray("SELECT user_id FROM group_members WHERE group_id = ?d", $g);
                $already_inserted_users = Database::get()->queryArray("SELECT uid FROM attendance_users WHERE attendance_id = ?d", $attendance_id);
                $already_inserted_ids = [];
                foreach ($already_inserted_users as $already_inserted_user) {
                    $already_inserted_ids[] = $already_inserted_user->uid;
                }
                $added_users = array();
                foreach ($ug as $u) {
                    if (!in_array($u->user_id, $already_inserted_ids)) {
                        $newUsersQuery = Database::get()->query("INSERT INTO attendance_users (attendance_id, uid)
                                SELECT $attendance_id, user_id FROM course_user
                                WHERE course_id = ?d AND user_id = ?d", $course_id, $u->user_id);
                        update_user_attendance_activities($attendance_id, $u->user_id);
                        $distinct_users_count++;
                    }
                }
                $log_details = array('id'=>$attendance_id,'title'=>get_attendance_title($attendance_id), 'action' => 'reset users', 'user_count' => $distinct_users_count, 'group_count' => count($_POST['specific']), 'groups' => $_POST['specific']);
                Log::record($course_id, MODULE_ID_ATTENDANCE, LOG_MODIFY, $log_details);

            }
        } elseif ($_POST['specific_attendance_users'] == 1) { // specific users
            $active_attendance_users = '';
            if (isset($_POST['specific']) and count($_POST['specific'])) {
                $active_attendance_users = $_POST['specific'];
                $sql_placeholders = '(' . implode(', ', array_fill(0, count($active_attendance_users), '?d')) . ')';
                $users = Database::get()->queryArray("SELECT uid FROM attendance_users
                    WHERE attendance_id = ?d AND uid NOT IN $sql_placeholders", $attendance_id, $active_attendance_users);
            } else {
                $users = Database::get()->queryArray('SELECT uid FROM attendance_users
                    WHERE attendance_id = ?d', $attendance_id);
            }
            foreach ($users as $u) {
                delete_attendance_user($attendance_id, $u->uid);
            }
            $log_details = array('id' => $attendance_id,
                                 'title' => get_attendance_title($attendance_id),
                                 'action' => 'delete users',
                                 'user_count' => count($users),
                                 'users' => $users);
            Log::record($course_id, MODULE_ID_ATTENDANCE, LOG_MODIFY, $log_details);

            $already_inserted_ids = [];
            if (isset($active_attendance_users)) {
                $already_inserted_users = Database::get()->queryArray("SELECT uid FROM attendance_users WHERE attendance_id = ?d
                    AND uid IN $sql_placeholders", $attendance_id, $active_attendance_users);
                foreach ($already_inserted_users as $already_inserted_user) {
                    $already_inserted_ids[] = $already_inserted_user->uid;
                }
                $added_users = array();
                foreach ($active_attendance_users as $u) {
                    if (!in_array($u, $already_inserted_ids)) {
                        $newUsersQuery = Database::get()->query("INSERT INTO attendance_users (attendance_id, uid)
                                SELECT ?d, user_id FROM course_user
                                WHERE course_id = ?d AND user_id = ?d", $attendance_id, $course_id, $u);
                        update_user_attendance_activities($attendance_id, $u);
                        $added_users[] = $u;
                    }
                }
                $log_details = array('id' => $attendance_id, 'title' => get_attendance_title($attendance_id), 'action' => 'add users', 'user_count' => count($added_users), 'users' => $added_users);
                Log::record($course_id, MODULE_ID_ATTENDANCE, LOG_MODIFY, $log_details);
            }
        } else { // if we want all users between dates
            $usersstart = new DateTime($_POST['UsersStart']);
            $usersend = new DateTime($_POST['UsersEnd']);
            // Delete all students not in the Date Range
            $gu = Database::get()->queryArray("SELECT attendance_users.uid FROM attendance_users, course_user
                    WHERE attendance_users.uid = course_user.user_id
                    AND course_id = ?d
                    AND attendance_users.attendance_id = ?d
                    AND course_user.status = " . USER_STUDENT . "
                    AND DATE(course_user.reg_date) NOT BETWEEN ?s AND ?s", $course_id, $attendance_id, $usersstart->format("Y-m-d"), $usersend->format("Y-m-d"));

            foreach ($gu as $u) {
                delete_attendance_user($attendance_id, $u->uid);
            }
            $log_details = array('id' => $attendance_id, 'title' => get_attendance_title($attendance_id), 'action' => 'delete users not in date range', 'users_start' => $usersstart->format("Y-m-d"), 'users_end' => $usersend->format("Y-m-d"), 'user_count' => count($gu),'users' => $gu);
            Log::record($course_id, MODULE_ID_ATTENDANCE, LOG_MODIFY, $log_details);

            //Add students that are not already registered to the attendance
            $already_inserted_users = Database::get()->queryArray("SELECT attendance_users.uid FROM attendance_users, course_user
                    WHERE attendance_users.uid = course_user.user_id
                    AND course_id = ?d
                    AND attendance_users.attendance_id = ?d
                    AND course_user.status = " . USER_STUDENT . "
                    AND DATE(course_user.reg_date) BETWEEN ?s AND ?s", $course_id, $attendance_id, $usersstart->format("Y-m-d"), $usersend->format("Y-m-d"));
            $already_inserted_ids = [];
            foreach ($already_inserted_users as $already_inserted_user) {
                $already_inserted_ids[] = $already_inserted_user->uid;
            }
            $valid_users_for_insertion = Database::get()->queryArray("SELECT user_id
                        FROM course_user
                        WHERE course_id = ?d
                        AND status = " . USER_STUDENT . "
                       AND DATE(reg_date) BETWEEN ?s AND ?s", $course_id, $usersstart->format("Y-m-d"), $usersend->format("Y-m-d"));
            $added_users = array();
            foreach ($valid_users_for_insertion as $u) {
                if (!in_array($u->user_id, $already_inserted_ids)) {
                    Database::get()->query("INSERT INTO attendance_users (attendance_id, uid) VALUES (?d, ?d)", $attendance_id, $u->user_id);
                    update_user_attendance_activities($attendance_id, $u->user_id);
                    $added_users[] = $u->user_id;
                }
            }
            $log_details = array('id' => $attendance_id, 'title' => get_attendance_title($attendance_id), 'action' => 'add users in date range', 'users_start' => $usersstart->format("Y-m-d"), 'users_end' => $usersend->format("Y-m-d"), 'user_count' => count($added_users),'users' => $added_users);
            Log::record($course_id, MODULE_ID_ATTENDANCE, LOG_MODIFY, $log_details);

            // Delete all students not in the Date Range
            $gu = Database::get()->queryArray("SELECT attendance_users.uid FROM attendance_users, course_user "
                    . "WHERE attendance_users.uid = course_user.user_id "
                    . "AND attendance_users.attendance_id = ?d "
                    . "AND course_user.status = " . USER_STUDENT . " "
                    . "AND DATE(course_user.reg_date) NOT BETWEEN ?s AND ?s", $attendance_id, $usersstart->format("Y-m-d"), $usersend->format("Y-m-d"));
        }
        Session::flash('message',$langGradebookEdit);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page('modules/attendance/index.php?course=' . $course_code . '&attendance_id=' . $attendance_id . '&attendanceBook=1');
    }

    // Top menu
    $tool_content .= "<div class='col-sm-12'>";

    if (isset($_GET['editUsers']) or isset($_GET['Book'])) {
        $navigation[] = ['url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&attendance_id=$attendance_id", 'name' => $attendance->title];
        $pageName = isset($_GET['editUsers']) ? $langRefreshList : $langAttendanceManagement;
    } elseif(isset($_GET['editSettings'])) {
        $navigation[] = ['url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&attendance_id=$attendance_id", 'name' => $attendance->title];
        $pageName = $langConfig;
    } elseif (isset($_GET['attendanceBook'])) {
        $navigation[] = ['url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&attendance_id=$attendance_id", 'name' => $attendance->title];
        $pageName = $langAttendanceActiveUsers;
        $action_bar = action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id",
                  'icon' => 'fa fa-reply',
                  'level' => 'primary'),
            array('title' => $langRefreshList,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;editUsers=1",
                  'icon' => 'fa-users',
                  'level' => 'primary-label')
            ));
        $tool_content .= $action_bar;
    } elseif (isset($_GET['modify'])) {
        $navigation[] = ['url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&attendance_id=$attendance_id", 'name' => $attendance->title];
        $pageName = $langEditChange;
    } elseif (isset($_GET['ins'])) {
        $actID = intval(getDirectReference($_GET['ins']));
        $navigation[] = ['url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&attendance_id=$attendance_id", 'name' => $attendance->title];
        $pageName = $langAttendanceBook;
        $action_bar = action_bar(
            array(
                array('title' => $langImportAttendances,
                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;imp=$actID",
                    'button-class' => 'btn btn-success',
                    'icon' => 'fa-upload',
                    'level' => 'primary')
            ));
        $tool_content .= $action_bar;
    } elseif(isset($_GET['addActivity']) or isset($_GET['addActivityAs']) or isset($_GET['addActivityEx']) or isset($_GET['addActivityLp']) or isset($_GET['addActivityTc'])) {
        $navigation[] = ['url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&attendance_id=$attendance_id", 'name' => $attendance->title];
        if (isset($_GET['addActivityAs'])) {
            $pageName = "$langAdd $langInsertWork";
        } elseif (isset($_GET['addActivityEx'])) {
            $pageName = "$langAdd $langInsertExercise";
        } elseif (isset($_GET['addActivityLp'])) {
            $pageName = "$langAdd $langLearningPath1";
        } elseif (isset($_GET['addActivityTc'])) {
            $pageName = "$langAdd $langInsertTcMeeting";
        } else {
            $pageName = $langGradebookAddActivity;
        }
    } elseif (isset($_GET['book'])) {
        $navigation[] = ['url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&attendance_id=$attendance_id", 'name' => $attendance->title];
        $pageName = $langAttendanceBook;
        $action_bar = action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id",
                  'icon' => 'fa fa-reply ',
                  'level' => 'primary',
                  'button-class' => 'btn-success'),
            array('title' => $langAttendanceBook,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;attendance_id=$attendance_id&amp;attendanceBook=1",
                  'icon' => 'fa fa-reply',
                  'level' => 'primary-label')
            ));
    $tool_content .= $action_bar;
    } elseif (isset($_GET['new'])) {
        $navigation[] = ['url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langAttendance];
        $pageName = $langNewAttendance;
    } elseif (isset($_GET['attendance_id']) && $is_editor) {
        $pageName = get_attendance_title($_GET['attendance_id']);
    }  elseif (!isset($_GET['attendance_id'])) {
        $action_bar= action_bar(
            array(
                array('title' => $langNewAttendance,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;new=1",
                      'icon' => 'fa-solid fa-circle-plus',
                      'level' => 'primary-label',
                      'button-class' => 'btn-success')));
        $tool_content .= $action_bar;
    }
    $tool_content .= "</div>";

    // Update attendance book settings
    if (isset($_POST['attendanceBookSettings'])) {
        $v = new Valitron\Validator($_POST);
        $v->rule('required', array('title', 'start_date', 'end_date'));
        $v->rule('integer', array('limit'));
        $v->rule('min', array('limit'), 0);
        $v->rule('date', array('start_date', 'end_date'));
        if (!empty($_POST['end_date'])) {
            $v->rule('dateBefore', 'start_date', $_POST['end_date']);
        }
        $v->labels(array(
            'title' => "$langTheField $langTitle",
            'start_date' => "$langTheField $langStart",
            'end_date' => "$langTheField $langEnd",
            'limit' => "$langTheField $langAttendanceLimitNumber"
        ));
        if($v->validate()) {
            $attendance_limit = $_POST['limit'];
            $attendance_title = $_POST['title'];
            $start_date = (new DateTime($_POST['start_date']))->format('Y-m-d H:i:s');
            $end_date = (new DateTime($_POST['end_date']))->format('Y-m-d H:i:s');
            Database::get()->querySingle("UPDATE attendance SET `title` = ?s, `limit` = ?d, `start_date` = ?t, `end_date` = ?t WHERE id = ?d ", $attendance_title, $attendance_limit, $start_date, $end_date, $attendance_id);
            $log_details = array('id' => $attendance_id, 'title' => $attendance_title, 'attendance_limit' => $attendance_limit, 'start_date' => $start_date, 'end_date' => $end_date);
            Log::record($course_id, MODULE_ID_ATTENDANCE, LOG_MODIFY, $log_details);
            Session::flash('message',$langGradebookEdit);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/attendance/index.php?course=$course_code&attendance_id=$attendance_id");
        } else {
            Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
            redirect_to_home_page("modules/attendance/index.php?course=$course_code&attendance_id=$attendance_id&editSettings=1");
        }
    }
    //FORM: create / edit new activity
    if(isset($_GET['addActivity']) OR isset($_GET['modify'])){
        add_attendance_other_activity($attendance_id);
        $display = FALSE;
    }
    //UPDATE/INSERT DB: new activity from exersices, assignments, learning paths, teleconference
    elseif(isset($_GET['addCourseActivity'])) {
        $id = $_GET['addCourseActivity'];
        $type = intval($_GET['type']);
        $actt = add_attendance_activity($attendance_id, $id, $type);
        $log_details = array('id' => $attendance_id, 'title' => get_attendance_title($attendance_id), 'action' => 'add activity', 'activity_title' => $actt);
        Log::record($course_id, MODULE_ID_ATTENDANCE, LOG_MODIFY, $log_details);
        Session::flash('message',$langGradebookSucInsert);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/attendance/index.php?course=$course_code&attendance_id=$attendance_id");
        $display = FALSE;
    }

    //UPDATE/INSERT DB: add or edit activity to attendance module (edit concerns and course activities like lps)
    elseif(isset($_POST['submitAttendanceActivity'])) {
        $v = new Valitron\Validator($_POST);
        $v->rule('date', array('date'));
        $v->labels(array(
            'date' => "$langTheField $langGradebookActivityDate2"
        ));
        if($v->validate()) {
            $actTitle = isset($_POST['actTitle']) ? trim($_POST['actTitle']) : "";
            $actDesc = purify($_POST['actDesc']);
            $auto = isset($_POST['auto']) ? $_POST['auto'] : "";
            $actDate = empty($_POST['date']) ? null : (new DateTime($_POST['date']))->format('Y-m-d H:i');
            $visible = isset($_POST['visible']) ? 1 : 0;
            if ($_POST['id']) {
                //update
                $id = $_POST['id'];
                Database::get()->query("UPDATE attendance_activities SET `title` = ?s, date = ?t,
                                                description = ?s, `auto` = ?d
                                            WHERE id = ?d", $actTitle, $actDate, $actDesc, $auto, $id);
                $log_details = array('id' => $id, 'title' => get_attendance_title($id), 'action' => 'modify activity', 'activity_title' => $actTitle, 'activity_date' => $actDate, 'visible' => $visible);
                Log::record($course_id, MODULE_ID_ATTENDANCE, LOG_MODIFY, $log_details);
                Session::flash('message',$langGradebookEdit);
                Session::flash('alert-class', 'alert-success');
                redirect_to_home_page("modules/attendance/index.php?course=$course_code&attendance_id=$attendance_id");
            } else {
                //insert
                $insertAct = Database::get()->query("INSERT INTO attendance_activities SET attendance_id = ?d, title = ?s,
                                                            `date` = ?t, description = ?s",
                                                    $attendance_id, $actTitle, $actDate, $actDesc);
                $log_details = array('id' => $attendance_id, 'title' => get_attendance_title($attendance_id), 'action' => 'add activity', 'activity_title' => $actTitle, 'activity_date' => $actDate);
                Log::record($course_id, MODULE_ID_ATTENDANCE, LOG_MODIFY, $log_details);
                Session::flash('message',$langGradebookSucInsert);
                Session::flash('alert-class', 'alert-success');
                redirect_to_home_page("modules/attendance/index.php?course=$course_code&attendance_id=$attendance_id");
            }
        } else {
            Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
            $new_or_edit = $_POST['id'] ?  "&modify=".getIndirectReference($_POST['id']) : "&addActivity=1";
            redirect_to_home_page("modules/attendance/index.php?course=$course_code&attendance_id=".$attendance_id.$new_or_edit);
        }
    }

    elseif (isset($_GET['delete'])) {
        $log_details = array('id' => $attendance_id,
                             'title' => get_attendance_title($attendance_id),
                             'action' => 'delete activity',
                             'activity_title' => get_attendance_activity_title($attendance_id, getDirectReference($_GET['delete'])));
        delete_attendance_activity($attendance_id, getDirectReference($_GET['delete']));
        Log::record($course_id, MODULE_ID_ATTENDANCE, LOG_MODIFY, $log_details);
        redirect_to_home_page("modules/attendance/index.php?course=$course_code&attendance_id=$attendance_id");

    // delete attendance
    } elseif (isset($_GET['delete_at'])) {
        $log_details = array('id' => $_GET['delete_at'], 'title' => get_attendance_title($_GET['delete_at']));
        delete_attendance($_GET['delete_at']);
        Log::record($course_id, MODULE_ID_ATTENDANCE, LOG_DELETE, $log_details);
        redirect_to_home_page("modules/attendance/index.php?course=$course_code");
    }

    //DISPLAY: list of users and form for each user
    elseif(isset($_GET['attendanceBook']) or isset($_GET['book'])) {
        if (isset($_GET['update']) and $_GET['update']) {
            $tool_content .= "<div class='col-sm-12'><div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>$langAttendanceUsers</span></div></div>";
        }
        //record booking
        if(isset($_POST['bookUser'])) {
            $userID = intval($_POST['userID']); //user
            //get all the attendance activies --> for each attendance activity update or insert grade
            $result = Database::get()->queryArray("SELECT * FROM attendance_activities WHERE attendance_id = ?d", $attendance_id);
            if ($result) {
                foreach ($result as $activity) {
                    $attend = @ intval($_POST[$activity->id]); //get the record from the teacher (input name is the activity id)
                    //check if there is record for the user for this activity
                    $checkForBook = Database::get()->querySingle("SELECT id FROM attendance_book WHERE attendance_activity_id = ?d AND uid = ?d", $activity->id, $userID);
                    if($checkForBook){
                        //update
                        Database::get()->query("UPDATE attendance_book SET attend = ?d WHERE id = ?d ", $attend, $checkForBook->id);
                    } else {
                        //insert
                        Database::get()->query("INSERT INTO attendance_book SET uid = ?d, attendance_activity_id = ?d, attend = ?d, comments = ?s", $userID, $activity->id, $attend, '');
                    }
                }
                $message = "<div class='col-sm-12'><div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>$langGradebookEdit</span></div></div>";
            }
        }
        // display user presences
        if(isset($_GET['book'])) {
            display_user_presences($attendance_id);
        } else {  // display all users presence
            display_all_users_presences($attendance_id);
        }
        $display = FALSE;
    }

    elseif (isset($_GET['new'])) {
        attendance_book_form(); // create new attendance
        $display = FALSE;
    } elseif (isset($_GET['editSettings'])) { // attendance settings
        attendance_book_form($attendance_id);
        $display = FALSE;
    } elseif (isset($_GET['editUsers'])) { // edit attendance users
        user_attendance_settings($attendance_id);
        $display = FALSE;
    } elseif (isset($_GET['addActivityAs'])) { //display available assignments
        attendance_display_available_assignments($attendance_id);
        $display = FALSE;
    } elseif (isset($_GET['addActivityEx'])) { // display available exercises
        attendance_display_available_exercises($attendance_id);
        $display = FALSE;
    } elseif (isset($_GET['addActivityTc'])) {
        attendance_display_available_tc($attendance_id);
        $display = FALSE;
    }
    //DISPLAY - EDIT DB: insert grades for each activity
    elseif (isset($_GET['ins'])) {
        $actID = intval(getDirectReference($_GET['ins']));
        $error = false;
        register_user_presences($attendance_id, $actID);
        $display = false;
    }
    elseif (isset($_GET['imp'])) {
        if (isset($_GET['import_attendances'])) {
            import_attendances($attendance_id, $_GET['imp'], true);
        } else {
            import_attendances($attendance_id, $_GET['imp']);
        }
        $display = false;
    }

} else if ($is_course_reviewer) {
    if (isset($_GET['attendanceBook'])) {
        display_all_users_presences($attendance_id);
        $display = FALSE;
    }
}

if (isset($display) and $display == TRUE) {
    // display attendance
    if (isset($attendance_id)) {
        if ($is_course_reviewer) {
            display_attendance_activities($attendance_id);
        } else {
            $pageName = $attendance->title;
            student_view_attendance($attendance_id); // student view
        }
    } else { // display all attendances
        display_attendances();
    }
}

//Display content in template
draw($tool_content, 2, null, $head_content);
