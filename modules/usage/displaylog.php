<?php

/* ========================================================================
 * Open eClass 3.0
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
 * ======================================================================== */

/**
 * @file displaylog.php
 * @author Yannis Exidaridis <jexi@noc.uoa.gr>
 * @brief form for displaying logs
 */
if (isset($_GET['from_admin'])) {
    $course_id = $_GET['c'];
} elseif (isset($_REQUEST['from_other'])) {
    $require_admin = TRUE;
} else {
    $require_current_course = true;
    $require_login = true;
}

$require_course_admin = true;
require_once '../../include/baseTheme.php';
require_once 'include/log.php';

load_js('datatables');
load_js('bootstrap-datetimepicker');

$head_content .= "<script type='text/javascript'>
        $(document).ready(function() {
            $('#log_results_table').DataTable ({                                
                'sPaginationType': 'full_numbers',
                'bAutoWidth': true,
                'searchDelay': 1000,
                'oLanguage': {
                   'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
                   'sZeroRecords':  '" . $langNoResult . "',
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
        });
        </script>";

$head_content .= "<script type='text/javascript'>
        $(function() {
            $('#user_date_start, #user_date_end').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-left',
                language: '".$language."',
                autoclose: true    
            });            
        });
    </script>";

if (!isset($_REQUEST['course_code'])) {
    $course_code = course_id_to_code($course_id);
}


if (isset($_GET['from_other'])) {    
    $toolName = $langSystemActions;
    $navigation[] = array('url' => '../admin/index.php', 'name' => $langAdmin);
    $navigation[] = array('url' => '../admin/otheractions.php', 'name' => $langRecordLog);
    $tool_content .= action_bar(array(
        array('title' => $langRecordLog,
            'url' => "../admin/otheractions.php",
            'icon' => 'fa-bar-chart',
            'level' => 'primary-label'),
        array('title' => $langBack,
            'url' => "../admin/index.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')
        ),false);
} else {
    $toolName = $langUsersLog;
    $navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langUsage);
    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "index.php?course=$course_code",
            'icon' => 'fa-reply',
            'level' => 'primary-label')
    ),false);
}
$logtype = isset($_REQUEST['logtype']) ? intval($_REQUEST['logtype']) : '0';
$u_user_id = isset($_REQUEST['u_user_id']) ? intval($_REQUEST['u_user_id']) : '-1';
$u_module_id = isset($_REQUEST['u_module_id']) ? intval($_REQUEST['u_module_id']) : '-1';

if (isset($_POST['user_date_start'])) {    
    $uds = DateTime::createFromFormat('d-m-Y H:i', $_POST['user_date_start']);
    $u_date_start = $uds->format('Y-m-d H:i');    
    $user_date_start = $uds->format('d-m-Y H:i');
} else {
    $date_start = new DateTime();
    $date_start->sub(new DateInterval('P30D'));    
    $u_date_start = $date_start->format('Y-m-d H:i');
    $user_date_start = $date_start->format('d-m-Y H:i');       
}
if (isset($_POST['user_date_end'])) {
    $ude = DateTime::createFromFormat('d-m-Y H:i', $_POST['user_date_end']);    
    $u_date_end = $ude->format('Y-m-d H:i');
    $user_date_end = $ude->format('d-m-Y H:i');        
} else {
    $date_end = new DateTime();
    $u_date_end = $date_end->format('Y-m-d H:i');
    $date_end->add(new DateInterval('P1D'));
    $user_date_end = $date_end->format('d-m-Y H:i');        
}

if (isset($_REQUEST['submit'])) {   
    $log = new Log();
    $log->display($course_id, $u_user_id, $u_module_id, $logtype, $u_date_start, $u_date_end, $_SERVER['SCRIPT_NAME']);
    if (isset($_GET['from_admin']) or isset($_GET['from_other'])) {
        draw($tool_content, 3, null, $head_content);
    } else {
        draw($tool_content, 2, null, $head_content);
    }
    exit();
}

// if we haven't choose 'system actions'
if (!isset($_GET['from_other'])) {
    $letterlinks = '';
    $result = Database::get()->queryArray("SELECT LEFT(a.surname, 1) AS first_letter
            FROM user AS a LEFT JOIN course_user AS b ON a.id = b.user_id
            WHERE b.course_id = ?d
            GROUP BY first_letter ORDER BY first_letter", $course_id);

    foreach ($result as $row) {
        $first_letter = $row->first_letter;
        $letterlinks .= '<a href="?course=' . $course_code . '&amp;first=' . urlencode($first_letter) . '">' . q($first_letter) . '</a> ';
    }

    $user_opts = "<option value='-1'>$langAllUsers</option>";
    if (isset($_GET['first'])) {
        $firstletter = $_GET['first'];
        $result = Database::get()->queryArray("SELECT a.id, a.surname, a.givenname, a.username, a.email, b.status
                    FROM user AS a LEFT JOIN course_user AS b ON a.id = b.user_id
                    WHERE b.course_id = ?d AND LEFT(a.surname,1) = ?s", $course_id, $firstletter);
    } else {
        $result = Database::get()->queryArray("SELECT a.id, a.surname, a.givenname, a.username, a.email, b.status
            FROM user AS a LEFT JOIN course_user AS b ON a.id = b.user_id
            WHERE b.course_id = ?d", $course_id);
    }

    foreach ($result as $row) {
        if ($u_user_id == $row->id) {
            $selected = 'selected';
        } else {
            $selected = '';
        }
        $user_opts .= '<option ' . $selected . ' value="' . $row->id . '">' .
                q($row->givenname . ' ' . $row->surname) . "</option>";
    }
}

$tool_content .= '<div class="form-wrapper">';
if (isset($_GET['from_other'])) { 
    $tool_content .= '<form class="form-horizontal" role="form" method="post" action="' . $_SERVER['SCRIPT_NAME'] . '?from_other=TRUE">';
} else {
    $tool_content .= '<form class="form-horizontal" role="form" method="post" action="' . $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '">';
}

// if we haven't choose 'system actions'
if (!isset($_GET['from_other'])) { 
    $tool_content .= '<div class="form-group">
            <label class="col-sm-2 control-label">' . $langLogModules . ':</label>
            <div class="col-sm-10"><select name="u_module_id" class="form-control">';
    $tool_content .= "<option value='-1'>$langAllModules</option>";
    foreach ($modules as $m => $mid) {
        $extra = '';
        if ($u_module_id == $m) {
            $extra = 'selected';
        }
        $tool_content .= "<option value=" . $m . " $extra>" . $mid['title'] . "</option>";
    }
    if ($u_module_id == MODULE_ID_USERS) {
        $extra = 'selected';
    }
    if ($u_module_id == MODULE_ID_TOOLADMIN) {
        $extra = 'selected';
    }
    if ($u_module_id == MODULE_ID_ABUSE_REPORT) {
        $extra = 'selected';
    }
    $tool_content .= "<option value = " . MODULE_ID_USERS . " $extra>$langAdminUsers</option>";
    $tool_content .= "<option value = " . MODULE_ID_TOOLADMIN . " $extra>$langExternalLinks</option>";
    $tool_content .= "<option value = " . MODULE_ID_ABUSE_REPORT . " $extra>$langAbuseReport</option>";
    $tool_content .= "</select></div></div>";
}

$tool_content .= '<div class="form-group">
        <label class="col-sm-2 control-label">' . $langLogTypes . ':</label>        
         <div class="col-sm-10">';

if (isset($_GET['from_other'])) {   // system actions
    $log_types = array(LOG_CREATE_COURSE => $langCourseCreate,
                       LOG_DELETE_COURSE => $langCourseDel);
} else {    // course actions
    $log_types = array(0 => $langAllActions,
                    LOG_INSERT => $langInsert,
                    LOG_MODIFY => $langModify,
                    LOG_DELETE => $langDelete);
}

$tool_content .= selection($log_types, 'logtype', $logtype, "class='form-control'");
$tool_content .= "</div></div>";
$tool_content .= "<div class='input-append date form-group' id='user_date_start' data-date = '" . q($user_date_start) . "' data-date-format='dd-mm-yyyy'>
<label class='col-sm-2 control-label'>$langStartDate:</label>
<div class='col-xs-10 col-sm-9'>               
    <input class='form-control' name='user_date_start' type='text' value = '" . q($user_date_start) . "'>
</div>
<div class='col-xs-2 col-sm-1'>
    <span class='add-on'><i class='fa fa-times'></i></span>
    <span class='add-on'><i class='fa fa-calendar'></i></span>
</div>
</div>";        
$tool_content .= "<div class='input-append date form-group' id='user_date_end' data-date= '" . q($user_date_end) . "' data-date-format='dd-mm-yyyy'>
        <label class='col-sm-2 control-label'>$langEndDate:</label>
            <div class='col-xs-10 col-sm-9'>
                <input class='form-control' name='user_date_end' type='text' value= '" . q($user_date_end) . "'>
            </div>
        <div class='col-xs-2 col-sm-1'>
            <span class='add-on'><i class='fa fa-times'></i></span>
            <span class='add-on'><i class='fa fa-calendar'></i></span>
        </div>
        </div>";


// if we haven't choose 'system actions'
if (!isset($_GET['from_other'])) {
    $tool_content .= '<div class="form-group">  
    <label class="col-sm-2 control-label">' . $langFirstLetterUser . ':</label>
        <div class="col-sm-10">' . $letterlinks . '</div>
      </div>
      <div class="form-group">  
        <label class="col-sm-2 control-label">' . $langUser . ':</label>
         <div class="col-sm-10"><select name="u_user_id" class="form-control">' . $user_opts . '</select></div>
      </div>';
}

$tool_content .= "<div class='form-group'><div class='col-sm-offset-2 col-sm-10'>".form_buttons(array(
                array(
                    'text' => $langSubmit,
                    'name' => 'submit',
                    'value'=> $langSubmit
                ),
                array(
                    'href' => "index.php?course=$course_code",
                )
            ))."</div></div></form></div>";

if (isset($_GET['from_admin']) or isset($_GET['from_other'])) {
    draw($tool_content, 3, null, $head_content);
} else {
    draw($tool_content, 2, null, $head_content);
}