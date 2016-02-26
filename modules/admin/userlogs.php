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
 * @file userlogs.php
 * @author Yannis Exidaridis <jexi@noc.uoa.gr>
 * @brief display form in admin menu for displaying user actions
 */
$require_usermanage_user = true;
require_once '../../include/baseTheme.php';
require_once 'include/log.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

$tree = new Hierarchy();
$user = new User();

load_js('tools.js');
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
                   'sZeroRecords':  '".$langNoResult."',
                   'sInfo':         '$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults',
                   'sInfoEmpty':    '$langDisplayed 0 $langTill 0 $langFrom2 0 $langResults2',
                   'sInfoFiltered': '',
                   'sInfoPostFix':  '',
                   'sSearch':       '".$langSearch."',
                   'sUrl':          '',
                   'oPaginate': {
                       'sFirst':    '&laquo;',
                       'sPrevious': '&lsaquo;',
                       'sNext':     '&rsaquo;',
                       'sLast':     '&raquo;'
                   }
               }
            });
            $('.dataTables_filter input').attr('placeholder', '$langDetail');
        });
        </script>";


$head_content .= '<script type="text/javascript">
        var platform_actions = ["-2", "' . LOG_PROFILE . '", "' . LOG_CREATE_COURSE . '", "' . LOG_DELETE_COURSE . '" , "' . LOG_MODIFY_COURSE . '"];
        $(course_log_controls_init);
</script>';

$head_content .= "<script type='text/javascript'>
        $(function() {
            $('#user_date_start, #user_date_end').datetimepicker({
                format: 'dd-mm-yyyy hh:ii',
                pickerPosition: 'bottom-right',
                language: '".$language."',
                autoclose: true
            });
        });
    </script>";


$u = isset($_GET['u']) ? intval($_GET['u']) : '';

$pageName = "$langUserLog: " . uid_to_name($u);
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'listusers.php', 'name' => $langListUsers);

if (isset($_POST['user_date_start'])) {    
    $uds = DateTime::createFromFormat('d-m-Y H:i', $_POST['user_date_start']);
    $u_date_start = $uds->format('Y-m-d H:i');    
    $user_date_start = $uds->format('d-m-Y H:i');
} else {
    $date_start = new DateTime();
    $date_start->sub(new DateInterval('P15D'));    
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

$logtype = isset($_GET['logtype']) ? intval($_GET['logtype']) : '0';
$u_course_id = isset($_GET['u_course_id']) ? intval($_GET['u_course_id']) : '-1';
$u_module_id = isset($_GET['u_module_id']) ? intval($_GET['u_module_id']) : '-1';

if (isDepartmentAdmin()) {
    validateUserNodes(intval($u), true);
}

$log = new Log();
// display logs
if (isset($_GET['submit'])) {  // display course modules logging
    $log->display($u_course_id, $u, $u_module_id, $logtype, $u_date_start, $u_date_end, $_SERVER['SCRIPT_NAME']);        
} else {
    $log->display(0, $u, 0, $logtype, $u_date_start, $u_date_end, $_SERVER['SCRIPT_NAME']);
}

$terms = array();
$qry = "SELECT id, title FROM course";
$cours_opts[-1] = $langAllCourses;
Database::get()->queryFunc($qry
        , function ($row) use(&$cours_opts) {
    $cours_opts[$row->id] = $row->title;
}, $terms);

// --------------------------------------
// display form
// --------------------------------------
$module_names[-1] = $langAllModules;
foreach ($modules as $mid => $info) {
    $module_names[$mid] = $info['title'];
}

$i = html_entity_decode('&nbsp;&nbsp;&nbsp;', ENT_QUOTES, 'UTF-8');
$log_types = array(0 => $langAllActions,
    -1 => $i . $langCourseActions,
    LOG_INSERT => $i . $i . $langInsert,
    LOG_MODIFY => $i . $i . $langModify,
    LOG_DELETE => $i . $i . $langDelete,
    -2 => $i . $langSystemActions,
    LOG_PROFILE => $i . $i . $langModProfile,
    LOG_CREATE_COURSE => $i . $i . $langFinalize,
    LOG_DELETE_COURSE => $i . $i . $langCourseDel,
    LOG_MODIFY_COURSE => $i . $i . $langCourseInfoEdit);
$tool_content .= "<div class='form-wrapper'>";
$tool_content .= "<form class='form-horizontal' role='form' method='get' action='$_SERVER[SCRIPT_NAME]'>    
        <div class='input-append date form-group' data-date = '" . q($user_date_start) . "' data-date-format='dd-mm-yyyy'>
        <label class='col-sm-2 control-label'>$langStartDate:</label>
        <div class='col-xs-10 col-sm-9'>               
            <input class='form-control' name='user_date_start' id='user_date_start' type='text' value = '" . q($user_date_start) . "'>
        </div>
        <div class='col-xs-2 col-sm-1'>
            <span class='add-on'><i class='fa fa-times'></i></span>
            <span class='add-on'><i class='fa fa-calendar'></i></span>
        </div>
    </div>";
$tool_content .= "<div class='input-append date form-group' data-date= '" . q($user_date_end) . "' data-date-format='dd-mm-yyyy'>
        <label class='col-sm-2 control-label'>$langEndDate:</label>
            <div class='col-xs-10 col-sm-9'>
                <input class='form-control' name='user_date_end' id='user_date_start' type='text' value= '" . q($user_date_end) . "'>
            </div>
        <div class='col-xs-2 col-sm-1'>
            <span class='add-on'><i class='fa fa-times'></i></span>
            <span class='add-on'><i class='fa fa-calendar'></i></span>
        </div>
        </div>";

$tool_content .= "<div class='form-group'>  
    <label class='col-sm-2 control-label'>$langLogTypes:</label>
     <div class='col-sm-10'>" . selection($log_types, 'logtype', $logtype, "class='form-control'") . "</div>
  </div>";
        
$tool_content .= '<div class="form-group">
    <label class="col-sm-2 control-label">' . $langCourse . ':</label>
     <div class="col-sm-10">' . selection($cours_opts, 'u_course_id', $u_course_id, "class='form-control'") . '</div>
  </div>
  <div class="form-group">
    <label class="col-sm-2 control-label">' . $langLogModules . ':</label>
     <div class="col-sm-10">' . selection($module_names, 'u_module_id', $m, "class='form-control'") . '</div>
  </div>
  <input type="hidden" name="u" value="'. $u .'">
  <div class="col-sm-offset-2 col-sm-10">    
    <input class="btn btn-primary" type="submit" name="submit" value="' . $langSubmit . '">
    </div>      
</form></div>';        

$tool_content .= "<p align='right'><a href='listusers.php'>$langBack</a></p>";

draw($tool_content, 3, null, $head_content);
