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

/**
 * @file displaylog.php
 * @author Yannis Exidaridis <jexi@noc.uoa.gr>
 * @brief form for displaying logs
 */

if (isset($_GET['from_admin'])) {
    $require_admin = TRUE;
    $course_id = $_GET['c'];
} elseif (isset($_REQUEST['from_other'])) {
    $require_admin = TRUE;
} else {
    $require_current_course = true;
    $require_login = true;
    $require_editor = true;
}

$require_help = true;
$helpTopic = 'course_stats';
$helpSubTopic = 'users_actions';
require_once '../../include/baseTheme.php';
require_once 'include/log.class.php';

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
              'class' : 'form-control input-sm ms-0 mb-3',
              'placeholder' : '$langSearch...'
            });
            $('.dataTables_filter label').attr('aria-label', '$langSearch');  
        });
        </script>";

$head_content .= "<script type='text/javascript'>
        $(function() {
            $('#user_date_start, #user_date_end').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-left',
                language: '".$language."',
                autoclose: true,
                minuteStep: 20
            });
        });
    </script>";

if (!isset($_REQUEST['course_code'])) {
    $course_code = course_id_to_code($course_id);
}


if (isset($_GET['from_other'])) {
    $toolName = $langAdmin;
    $pageName = $langSystemActions;
    $navigation[] = array('url' => '../admin/index.php', 'name' => $langAdmin);
    $navigation[] = array('url' => '../usage/index.php?t=a', 'name' => $langUsage);
} else {
    $toolName = $langUsersLog;
    $navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langUsage);
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
    $log->display($course_id, $u_user_id, $u_module_id, $logtype, $u_date_start, $u_date_end);
    if (isset($_GET['from_admin']) or isset($_GET['from_other'])) {
        draw($tool_content, 3, null, $head_content);
    } else {
        draw($tool_content, 2, null, $head_content);
    }
    exit();
}

// if we haven't chosen 'system actions'
if (!isset($_GET['from_other'])) {
    $letterlinks = '';
    $result = Database::get()->queryArray("SELECT LEFT(a.surname, 1) AS first_letter
            FROM user AS a LEFT JOIN course_user AS b ON a.id = b.user_id
            WHERE b.course_id = ?d
            GROUP BY first_letter ORDER BY first_letter", $course_id);

    foreach ($result as $row) {
        $first_letter = $row->first_letter;
        $letterlinks .= '<a aria-label="'.$langFirstLetterUser.'" href="?course=' . $course_code . '&amp;first=' . urlencode($first_letter) . '">' . q($first_letter) . '</a> ';
    }

    $user_opts = "<option value='-1'>$langAllUsers</option>";
    if (isset($_GET['first'])) {
        $firstletter = $_GET['first'];
        $result = Database::get()->queryArray("SELECT a.id, a.surname, a.givenname, a.username, a.email, b.status
                    FROM user AS a LEFT JOIN course_user AS b ON a.id = b.user_id
                    WHERE b.course_id = ?d AND LEFT(a.surname,1) = ?s
                    ORDER BY a.surname, a.givenname, a.am", $course_id, $firstletter);
    } else {
        $result = Database::get()->queryArray("SELECT a.id, a.surname, a.givenname, a.username, a.email, b.status
            FROM user AS a LEFT JOIN course_user AS b ON a.id = b.user_id
            WHERE b.course_id = ?d
            ORDER BY a.surname, a.givenname, a.am", $course_id);
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


$flex_content = '';
$flex_grow = '';
$column_content = '';

if (!isset($_GET['from_admin']) and !isset($_GET['from_other'])) {
    $flex_content = 'd-lg-flex gap-4';
    $flex_grow = 'flex-grow-1';
    $column_content = 'form-content-modules';
} else {
    $flex_content = 'row m-auto';
    $flex_grow = 'col-lg-6 col-12 px-0';
    $column_content = 'col-lg-6 col-12';
}

$tool_content .= '<div class="'.$flex_content.' mt-4">
<div class="'.$flex_grow.'"><div class="form-wrapper form-edit rounded">';
if (isset($_GET['from_other'])) {
    $tool_content .= '<form class="form-horizontal" role="form" method="post" action="' . $_SERVER['SCRIPT_NAME'] . '?from_other=TRUE">';
} else {
    $tool_content .= '<form class="form-horizontal" role="form" method="post" action="' . $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '">';
}

// if we haven't choose 'system actions'
if (!isset($_GET['from_other'])) {
    $tool_content .= '<div class="row form-group mt-3">
            <label for="id_u_module_id" class="col-12 control-label-notes">' . $langLogModules . ' <span class="asterisk Accent-200-cl">(*)</span></label>
            <div class="col-12"><select name="u_module_id" class="form-select" id="id_u_module_id">';
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
    $tool_content .= "<option value = " . MODULE_ID_COURSEINFO . " $extra>$langConfig</option>";
    $tool_content .= "<option value = " . MODULE_ID_TOOLADMIN . " $extra>$langExternalLinks</option>";
    $tool_content .= "<option value = " . MODULE_ID_ABUSE_REPORT . " $extra>$langAbuseReport</option>";
    $tool_content .= "</select></div></div>";
}

$tool_content .= '<div class="row form-group mt-4">
        <label for="log_typeID" class="col-12 control-label-notes">' . $langActions . '</label>        
         <div class="col-12">';

if (isset($_GET['from_other'])) {   // system actions
    $log_types = array(LOG_CREATE_COURSE => $langCourseCreate,
                       LOG_DELETE_COURSE => $langCourseDel);
} else {    // course actions
    $log_types = array(0 => $langAllActions,
                    LOG_INSERT => $langInsert,
                    LOG_MODIFY => $langModify,
                    LOG_DELETE => $langDelete);
}

$tool_content .= selection($log_types, 'logtype', $logtype, "class='row form-control mt-4' id='log_typeID'");
$tool_content .= "</div></div>";
$tool_content .= "<div class='row input-append date form-group mt-4' data-date = '" . q($user_date_start) . "' data-date-format='dd-mm-yyyy'>
    
        <label class='col-12 control-label-notes' for='user_date_start'>$langFrom</label>
        <div class='col-12'> 
            <div class='input-group'>
                <span class='add-on input-group-text h-40px bg-input-default input-border-color border-end-0'><i class='fa-regular fa-calendar'></i></span>  
                <input class='form-control mt-0 border-start-0' id='user_date_start' name='user_date_start' type='text' value = '" . q($user_date_start) . "'>
                
            </div>
        </div>
    
</div>";
$tool_content .= "<div class='row input-append date form-group mt-4' data-date= '" . q($user_date_end) . "' data-date-format='dd-mm-yyyy'>
    
        <label class='col-12 control-label-notes' for='user_date_end'>$langTill</label>
        <div class='col-12'>
            <div class='input-group'>   
                <span class='add-on input-group-text h-40px bg-input-default input-border-color border-end-0'><i class='fa-regular fa-calendar'></i></span>
                <input class='form-control mt-0 border-start-0' id='user_date_end' name='user_date_end' type='text' value= '" . q($user_date_end) . "'>
                
            </div>
        </div>
    
</div>";


// if we haven't chosen 'system actions'
if (!isset($_GET['from_other'])) {
    $tool_content .=
      '<div class="row form-group mt-4">  
        <div class="col-12 control-label-notes mb-2">' . $langFirstLetterUser . '</div>
        <div class="col-12">' . $letterlinks . '</div>
      </div>
      <div class="row form-group mt-4">  
        <label for="usId" class="col-12 control-label-notes">' . $langUser . ' <span class="asterisk Accent-200-cl">(*)</span></label>
        <div class="col-12"><select name="u_user_id" class="form-select" id="usId">' . $user_opts . '</select></div>
      </div>';
}

$cancel_url = (isset($_GET['from_other']))? "index.php?t=a" : "index.php?course=$course_code";
$tool_content .= "<div class='row form-group mt-5'>
                    <div class='col-12 d-flex justify-content-end align-items-center'>
                        ".form_buttons(array(
                            array(
                                'class' => 'submitAdminBtn',
                                'text' => $langSubmit,
                                'name' => 'submit',
                                'value'=> $langSubmit
                            ),
                            array(
                                'class' => 'cancelAdminBtn',
                                'href' => "$cancel_url",
                            )
                        ))."      
                    </div>
                </div>
                </form>
    </div></div>
    <div class='$column_content d-none d-lg-block'>
        <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
    </div>
</div>";

if (isset($_GET['from_admin']) or isset($_GET['from_other'])) {
    draw($tool_content, 3, null, $head_content);
} else {
    draw($tool_content, 2, null, $head_content);
}
