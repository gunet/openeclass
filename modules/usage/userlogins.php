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
 * @file userlogins.php
 * @brief Shows logins made by a user or all users of a course, during a specific period.  
 */

$require_current_course = true;
$require_course_admin = true;
$require_help = true;
$helpTopic = 'Usage';
$require_login = true;

require_once '../../include/baseTheme.php';
require_once 'include/action.php';
require_once 'statistics_tools_bar.php';

load_js('bootstrap-datetimepicker');

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

statistics_tools($course_code, "userlogins");

$pageName = $langUserLogins;
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langUsage);

if (isset($_POST['user_date_start'])) {
    $uds = DateTime::createFromFormat('d-m-Y H:i', $_POST['user_date_start']);
    $u_date_start = $uds->format('Y-m-d H:i');
    $user_date_start = $uds->format('d-m-Y H:i');
} else {
    $date_start = new DateTime();
    $date_start->sub(new DateInterval('P2D'));    
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
    $user_date_end = $date_end->format('d-m-Y H:i');        
}

$usage_defaults = array(
    'u_user_id' => -1    
);

foreach ($usage_defaults as $key => $val) {
    if (!isset($_POST[$key])) {
        $$key = $val;
    } else {
        $$key = $_POST[$key];
    }
}

$date_fmt = '%Y-%m-%d';
$date_where = '(date_time BETWEEN ?s AND ?s)';
$date_terms = array($u_date_start, $u_date_end);
$date_what = "DATE_FORMAT(MIN(date_time), '$date_fmt') AS date_start, DATE_FORMAT(MAX(date_time), '$date_fmt') AS date_end ";

$terms = array();
if ($u_user_id != -1) {
    $user_where = 'AND user_id = ?d';
    $terms[] = intval($u_user_id);
} else {
    $user_where = '';
}
// get data from logins
$result_2 = Database::get()->queryArray("SELECT a.id, a.surname, a.givenname, a.username
                 FROM user AS a LEFT JOIN course_user AS b ON a.id = b.user_id
                 WHERE b.course_id = ?d $user_where", $course_id, $terms);
$users = array();
foreach ($result_2 as $row) {
    $users[$row->id] = $row->surname . ' ' . $row->givenname;
}
$table_cont = '';
$unknown_users = array();
$result = Database::get()->queryArray("SELECT user_id, ip, date_time FROM logins 
                 WHERE course_id = ?d AND $date_where $user_where
                 ORDER BY date_time DESC", $course_id, $date_terms, $terms);

foreach ($result as $row) {
    $known = false;
    if (isset($users[$row->user_id])) {
        $user = $users[$row->user_id];
        $known = true;
    } elseif (isset($unknown_users[$row->user_id])) {
        $user = $unknown_users[$row->user_id];
    } else {
        $user = uid_to_name($row->user_id);
        if ($user === false) {
            $user = $langAnonymous;
        }
        $unknown_users[$row->user_id] = $user;
    }
    
    $table_cont .= "<td>";
    if ($known) {
        $table_cont .= q($user);
    } else {
        $table_cont .= "<span class='red'>" . q($user) . "</span>";
    }
    $table_cont .= "</td>
                <td align='text-center'>" . $row->ip . "</td>
                <td align='text-center'>" . nice_format($row->date_time, true) . "</td>
                </tr>"; 
}

//Records exist?
if (count($unknown_users) > 0) {
    $tool_content .= "<div class='alert alert-info'>$langAnonymousExplain</div>";
}

if ($table_cont) {
    $tool_content .= "<div class='table-responsive'>
        <table class='table-default'>        
        <tr>
        <th class='text-left'>" . $langUser . "</th>
        <th>" . $langAddress . "</th>
        <th>" . $langLoginDate . "</th>
        </tr>";
    $tool_content .= "" . $table_cont . "";
    $tool_content .= "</table></div>";
}

if (!($table_cont)) {
    $tool_content .= "<div class='alert alert-warning'>$langNoLogins</div>";
}

$result = Database::get()->queryArray("SELECT LEFT(a.surname, 1) AS first_letter
        FROM user AS a LEFT JOIN course_user AS b ON a.id = b.user_id
        WHERE b.course_id = ?d
        GROUP BY first_letter ORDER BY first_letter", $course_id);

$letterlinks = '';
foreach ($result as $row) {
    $first_letter = $row->first_letter;
    $letterlinks .= '<a href="?course=' . $course_code . '&amp;first=' . urlencode($first_letter) . '">' . $first_letter . '</a> ';
}

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
$user_opts = '<option value="-1">' . $langAllUsers . "</option>";
foreach ($result as $row) {
    if ($u_user_id == $row->id) {
        $selected = 'selected';
    } else {
        $selected = '';
    }
    $user_opts .= '<option ' . $selected . ' value="' . $row->id . '">' . q($row->givenname . ' ' . $row->surname) . "</option>";
}

$tool_content .= '<div class="form-wrapper">';
$tool_content .= '<form class="form-horizontal" role="form" method="post" action="' . $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '">';
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
$tool_content .= '<div class="form-group">  
    <label class="col-sm-2 control-label">' . $langFirstLetterUser . ':</label>
    <div class="col-sm-10">' . $letterlinks . '</div>
  </div>
  <div class="form-group">  
    <label class="col-sm-2 control-label">' . $langUser . ':</label>
     <div class="col-sm-10"><select name="u_user_id" class="form-control">' . $user_opts . '</select></div>
  </div>  
  <div class="col-sm-offset-2 col-sm-10">    
    <input class="btn btn-primary" type="submit" name="btnUsage" value="' . $langSubmit . '">
    </div>  
</form></div>';

draw($tool_content, 2, null, $head_content);
