<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

$require_usermanage_user = true;
require_once '../../include/baseTheme.php';
require_once 'include/jscalendar/calendar.php';
require_once 'include/log.php';

$nameTools = $langUserLog;
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);
$navigation[]= array ("url"=>"listusers.php", "name"=> $langListUsers);

$jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/', $language, 'calendar-blue2', false);
$local_head = $jscalendar->get_load_files_code();

$u_user_id = isset($_REQUEST['u'])?intval($_REQUEST['u']):'';
$u_date_start = isset($_POST['u_date_start'])? $_POST['u_date_start']: strftime('%Y-%m-%d', strtotime('now -15 day'));
$u_date_end = isset($_POST['u_date_end'])? $_POST['u_date_end']: strftime('%Y-%m-%d', strtotime('now +1 day'));
$logtype = isset($_POST['logtype'])? intval($_POST['logtype']): '0';

if (!isset($_GET['p'])) {        
        $u_course_id = isset($_POST['u_course_id'])? intval($_POST['u_course_id']): '-1';
        $u_module_id = isset($_POST['u_module_id'])? intval($_POST['u_module_id']): '-1';
}

// display logs
if (isset($_POST['submit'])) {
    $log = new Log();
    if (isset($_GET['p'])) {
        $log->display(0, $u_user_id, 0, $logtype, $u_date_start, $u_date_end);        
    } else { // display course modules logging
        $log->display($u_course_id, $u_user_id, $u_module_id, $logtype, $u_date_start, $u_date_end);
    }    
}

//calendar for determining start and end date
    $start_cal = $jscalendar->make_input_field(
           array('showsTime'      => false,
                 'showOthers'     => true,
                 'ifFormat'       => '%Y-%m-%d',
                 'timeFormat'     => '24'),
           array('style'       => '',
                 'name'        => 'u_date_start',
                 'value'       => $u_date_start));

    $end_cal = $jscalendar->make_input_field(
           array('showsTime'      => false,
                 'showOthers'     => true,
                 'ifFormat'       => '%Y-%m-%d',
                 'timeFormat'     => '24'),
           array('style'       => '',
                 'name'        => 'u_date_end',
                 'value'       => $u_date_end));

    if (!isset($_GET['p'])) {
        //possible courses
        $qry = "SELECT LEFT(title, 1) AS first_letter FROM course
                GROUP BY first_letter ORDER BY first_letter";
        $result = db_query($qry);
        $letterlinks = '';
        while ($row = mysql_fetch_assoc($result)) {
                $first_letter = $row['first_letter'];
                $letterlinks .= '<a href="?first='.$first_letter.'">'.$first_letter.'</a> ';
        }

        if (isset($_GET['first'])) {
                $firstletter = $_GET['first'];
                $qry = "SELECT id, title FROM course 
                                WHERE LEFT(title,1) = '".mysql_real_escape_string($firstletter)."'";
        } else {
                $qry = "SELECT id, title FROM course";
        }

        $cours_opts = "<option value='-1'>$langAllCourses</option>";
        $result = db_query($qry);
        while ($row = mysql_fetch_assoc($result)) {
                if ($u_course_id == $row['id']) { 
                        $selected = 'selected';                
                } else { 
                        $selected = ''; 
                }
                $cours_opts .= "<option $selected value='$row[id]'>$row[title]</option>";
        }
    }
    
    // --------------------------------------
    // display form
    // --------------------------------------    
    $tool_content .= "<form method='post'>
        <fieldset>
        <legend>$langUserLog</legend>
        <table class='tbl'>        
        <tr>
        <th width='220' class='left'>$langStartDate</th>
        <td>$start_cal</td>
        </tr>
        <tr>
        <th class='left'>$langEndDate</th>
        <td>$end_cal</td>
        </tr>";
        if (!isset($_GET['p'])) {
                $tool_content .= "<tr>
                <th class='left'>$langFirstLetterCourse</th>
                <td>$letterlinks</td>
                </tr>
                <tr>
                <th class='left'>$langCourse</th>
                <td><select name='u_course_id'>$cours_opts</select></td>
                </tr>
                <tr>
                <th class='left'>$langLogModules :</th>
                <td><select name='u_module_id'>";
                $tool_content .= "<option value='-1'>$langAllModules</option>";
                foreach ($modules as $m => $mid) {
                        $extra = '';
                        if ($u_module_id == $m) {
                                $extra = 'selected';
                        }
                        $tool_content .= "<option value=".$m." $extra>".$mid['title']."</option>";
                }        
                $tool_content .= "</select></td></tr>";
        }
        $tool_content .= "<tr><th class='left'>$langLogTypes :</th><td>";
        if (!isset($_GET['p'])) {
                $log_types = array('0' => $langAllActions,
                                LOG_INSERT => $langInsert,
                                LOG_MODIFY => $langModify,
                                LOG_DELETE => $langDelete);
        } else {
                $log_types = array('0' => $langAllActions,
                                LOG_PROFILE => $langModProfile,
                                LOG_CREATE_COURSE => $langCourseCreate,
                                LOG_DELETE_COURSE => $langCourseDel);
        }
        $tool_content .= selection($log_types, 'logtype', $logtype);
        $tool_content .= "</td></tr>        
        <tr>
        <th class='left'>&nbsp;</th>
        <td><input type='submit' name='submit' value=$langSubmit></td>
        </tr>        
        </table>
        </fieldset>
        </form>";
    
$tool_content .= "<p align='right'><a href='listusers.php'>$langBack</a></p>";

draw($tool_content, 3, null, $local_head);
