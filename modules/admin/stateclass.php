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
 * @file stateclass.php
 * @brief display various statistics 
 */ 
  
$require_admin = TRUE;
require_once '../../include/baseTheme.php';
require_once 'include/log.php';
require_once 'admin_statistics_tools_bar.php';

$toolName = $langPlatformGenStats;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);

admin_statistics_tools("stateclass");

$tool_content .= "<div class='table-responsive'>
                <table class='table-default'>
                    <tr><td><a href='$_SERVER[SCRIPT_NAME]?stats=login'>$langNbLogin</a></td></tr>
                    <tr><td><a href='$_SERVER[SCRIPT_NAME]?stats=failurelogin'>$langLoginFailures</a><small> ($langLast15Days)</small></td></tr>
                    <tr><td><a href='$_SERVER[SCRIPT_NAME]?stats=users'>$langUsers</a></td></tr>
                    <tr><td><a href='$_SERVER[SCRIPT_NAME]?stats=percourse'>$langUsersPerCourse</a></td></tr>
                    <tr><td><a href='$_SERVER[SCRIPT_NAME]?stats=cours'>$langStatCour</a></td></tr>
                    <tr><td><a href='$_SERVER[SCRIPT_NAME]?stats=musers'>$langMultipleUsers</a></td></tr>
                    <tr><td><a href='$_SERVER[SCRIPT_NAME]?stats=memail'>$langMultipleAddr e-mail</a></td></tr>
                    <tr><td><a href='$_SERVER[SCRIPT_NAME]?stats=mlogins'>$langMultiplePairs LOGIN - PASS</a></td></tr>
                    <tr><td><a href='$_SERVER[SCRIPT_NAME]?stats=vmusers'>$langMailVerification</a></td></tr>
                    <tr><td><a href='$_SERVER[SCRIPT_NAME]?stats=unregusers'>$langUnregUsers</a><small> ($langLastMonth)</small></td></tr>
                </table>            
            </div>";

// ---------------------
// actions
// ---------------------
if (isset($_GET['stats'])) {
    switch ($_GET['stats']) {
        case 'failurelogin':
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 0;
            $tool_content .= "<br />";
            $date_start = date("Y-m-d", strtotime("-15 days"));
            $date_end = date("Y-m-d", strtotime("+1 days"));
            $page_link = "&amp;stats=failurelogin";
            $log = new Log();
            $log->display(0, 0, 0, LOG_LOGIN_FAILURE, $date_start, $date_end, $_SERVER['PHP_SELF'], $limit, $page_link);
            break;
        case 'unregusers':
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 0;
            $tool_content .= "<br />";
            $date_start = date("Y-m-d", strtotime("-1 month"));
            $date_end = date("Y-m-d", strtotime("+1 days"));
            $page_link = "&amp;stats=unregusers";
            $log = new Log();
            $log->display(0, -1, 0, LOG_DELETE_USER, $date_start, $date_end, $_SERVER['PHP_SELF'], $limit, $page_link);
            break;
        case 'login':
            $course_codes = array();
            $result = Database::get()->queryFunc("SELECT code FROM course"
                    , function($row) use(&$course_codes) {
                $course_codes[] = $row->code;
            });

            $first_date_time = time();
            $totalHits = 0;

            foreach ($course_codes as $course_code) {

                Database::get()->queryFunc("SELECT SUM(hits) AS cnt FROM actions_daily WHERE course_id = ?d"
                        , function ($row) use(&$totalHits) {
                    $totalHits += $row->cnt;
                }, course_code_to_id($course_code));

                Database::get()->queryFunc("SELECT UNIX_TIMESTAMP(MIN(day)) AS first
                                        FROM actions_daily
                                        WHERE course_id = ?d"
                        , function($row) use(&$first_date_time) {
                    $tmp = $row->first;
                    if (!empty($tmp)) {
                        if ($tmp < $first_date_time) {
                            $first_date_time = $tmp;
                        }
                    }
                }, course_code_to_id($course_code));
            }
            $uptime = date("d-m-Y", $first_date_time);

            $tool_content .= "<div class='row'>
                        <div class='col-sm-12'>
                        <h3 class='content-title'>$langNbLogin</h3>
                        <ul class='list-group'>
			<li class='list-group-item'><label>$langFrom
			" . nice_format(Database::get()->querySingle("SELECT loginout.when AS `when` FROM loginout ORDER BY loginout.when LIMIT 1")->when, true) . "
			</label>
                        <span class='badge'>" . Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM loginout
				WHERE loginout.action ='LOGIN'")->cnt . "</span>
                        </li>
			<li class='list-group-item'><label>$langLast30Days</label>
			<span class='badge'>" . Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM loginout
                                                WHERE action ='LOGIN' AND (loginout.when > DATE_SUB(CURDATE(),INTERVAL 30 DAY))")->cnt . "</span>
                        </li>
			
			<li class='list-group-item'><label>$langLast7Days</label>
			<span class='badge'>" . Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM loginout
				WHERE action ='LOGIN' AND (loginout.when > DATE_SUB(CURDATE(),INTERVAL 7 DAY))")->cnt . "</span>
                        </li>
			<li class='list-group-item'><label>$langToday</label>
			<span class='badge'>" . Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM loginout
				WHERE action ='LOGIN' AND (loginout.when > curdate())")->cnt . "</span>
			</li>
			<li class='list-group-item'><label>$langTotalHits</label>			
                            <span class='badge'>$totalHits</span>
			</li>
			<tr>
			<li class='list-group-item'><label>$langUptime</label>
                            <span class='badge'>$uptime</span>
			</li>
                        </ul>
                        </div></div>";
            break;
        case 'users':
            $tool_content .= "<div class='row'>
                        <div class='col-sm-12'>
                        <h3 class='content-title'>$langUsers</h3>
                        <ul class='list-group'>
                        <li class='list-group-item'><label>$langNbProf</label>
                            <span class='badge'>" . Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE status = " . USER_TEACHER . ";")->cnt . "</span>
                        </li>
                        <li class='list-group-item'><label>$langNbStudents</label>
                            <span class='badge'>" . Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE status = " . USER_STUDENT . ";")->cnt . "</span>
                        </li>
                        <li class='list-group-item'><label>$langNumGuest</label>
                            <span class='badge'>" . Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE status = " . USER_GUEST . ";")->cnt . "</span>
                        </li>
                        <li class='list-group-item'><label>$langTotal</label>
                            <span class='badge'>" . Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user;")->cnt . "</span>
                        </li>
                        <li class='list-group-item'>
                            <label>$langUserNotLogin <a href='listusers.php?search=no_login'>$langFrom2 " . nice_format(Database::get()->querySingle("SELECT loginout.when as `when` FROM loginout ORDER BY loginout.when LIMIT 1")->when, true) . "</a></label>
                            <span class='badge'>" . Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM `user` LEFT JOIN `loginout` ON `user`.`id` = `loginout`.`id_user` WHERE `loginout`.`id_user` IS NULL;")->cnt . "</span>
                        </li>
                        </ul>
                        </div></div>";
            break;
        case 'cours':
            $tool_content .= "<div class='table-responsive'>
                            <table class='table-default'>
                            <tr class='list-header'>
                            <th class='left' colspan='2'><b>$langCoursesHeader</b></th>
                            </tr>
                            <tr>
                            <td class='left'>$langNumCourses</td>
                            <td class='right'><b>" . Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM course")->cnt . "</b></td>
                            </tr>
                            <tr>
                            <th class='left' colspan='2'><b>$langNunEachAccess</b></th>
                            </tr>" . tablize(list_ManyResult("SELECT DISTINCT visible, COUNT(*) AS nb
                                    FROM course GROUP BY visible ", 'visible')) . "
                            <tr>
                            <th class='left' colspan='2'><b>$langNumEachCourse</b></th>
                            </tr>" . tablize(list_ManyResult("SELECT DISTINCT hierarchy.name AS faculte, COUNT(*) AS nb
                                    FROM course, course_department, hierarchy
                                    WHERE course.id = course_department.course
                                      AND hierarchy.id = course_department.department GROUP BY hierarchy.id", 'faculte')) . "
                            <tr>
                            <th class='left' colspan='2'><b>$langNumEachLang</b></th>
                            </tr>" . tablize(list_ManyResult("SELECT DISTINCT lang, COUNT(*) AS nb FROM course 
                                            GROUP BY lang DESC", 'lang')) . "			
                            </tr>
                            </table></div>";
            break;
        case 'musers':
            $tool_content .= "<div class='table-responsive'>
                            <table class='table-default'>";
            $loginDouble = list_ManyResult("SELECT DISTINCT username, COUNT(*) AS nb
				FROM user GROUP BY BINARY username HAVING nb > 1 ORDER BY nb DESC", 'username');
            $tool_content .= "<tr class='list-header'><th><b>$langMultipleUsers</b></th>
			<th class='right'><strong>$langResult</strong></th>
			</tr>";
            if (count($loginDouble) > 0) {
                $tool_content .= tablize($loginDouble);
                $tool_content .= "<tr><td class='right' colspan='2'>" . error_message() . "</td></tr>";
            } else {
                $tool_content .= "<tr><td class='right' colspan='2'>" . ok_message() . "</td></tr>";
            }
            $tool_content .= "</table></div>";
            break;
        case 'percourse':
            $tool_content .= "<div class='table-responsive'>
                    <table class='table-default'>
                    <tr class='list-header'><th class='left' colspan='2'><b>$langUsersPerCourse</b></th>";
            $teachers = $students = $visitors = 0;
            foreach (Database::get()->queryArray("SELECT id, code, title, prof_names FROM course ORDER BY title") as $row) {
                $cu_key = q($row->title)." (".q($row->code).") &mdash; ".q($row->prof_names);
                foreach (Database::get()->queryArray("SELECT user.id, course_user.status FROM course_user, user, course
                                                        WHERE course.id = ?d AND course_user.course_id = ?d
                                                        AND course_user.user_id = user.id", $row->id, $row->id) as $numrows) {
                    switch ($numrows->status) {
                        case USER_TEACHER: $teachers++;
                            break;
                        case USER_STUDENT: $students++;
                            break;
                        case USER_GUEST: $visitors++;
                            break;
                        default: break;
                    }
                }
                $cu[$cu_key] = "<small>$teachers $langTeachers &mdash; $students $langStudents &mdash; $visitors $langGuests</small>";
                $teachers = $students = $visitors = 0;
            }
            $tool_content .= "</tr>" . tablize($cu) . "</table></div>";
            break;
        case 'memail':
            $sqlLoginDouble = "SELECT DISTINCT email, COUNT(*) AS nb FROM user GROUP BY email
				HAVING nb > 1 ORDER BY nb DESC";
            $loginDouble = list_ManyResult($sqlLoginDouble, 'email');
            $tool_content .= "<div class='table-responsive'>
                            <table class='table-default'>
                            <tr class='list-header'>
                            <th><b>$langMultipleAddr e-mail</b></th>
                            <th class='right'><strong>$langResult</strong></th>
                            </tr>";
            if (count($loginDouble) > 0) {
                $tool_content .= tablize($loginDouble);
                $tool_content .= "<tr><td class=right colspan='2'>";
                $tool_content .= error_message();
                $tool_content .= "</td></tr>";
            } else {
                $tool_content .= "<tr><td class=right colspan='2'>";
                $tool_content .= ok_message();
                $tool_content .= "</td></tr>";
            }
            $tool_content .= "</table></div>";
            break;
        case 'mlogins':
            $sqlLoginDouble = "SELECT DISTINCT CONCAT(username, \" -- \", password) AS paire,
				COUNT(*) AS nb FROM user GROUP BY BINARY paire HAVING nb > 1 ORDER BY nb DESC";
            $loginDouble = list_ManyResult($sqlLoginDouble, 'paire');
            $tool_content .= "<div class='table-responsive'>
                            <table class='table-default'>
                            <tr class='list-header'>
                            <th><b>$langMultiplePairs LOGIN - PASS</b></th>
                            <th class='right'><b>$langResult</b></th>
                            </tr>";
            if (count($loginDouble) > 0) {
                $tool_content .= tablize($loginDouble);
                $tool_content .= "<tr><td class='right' colspan='2'>";
                $tool_content .= error_message();
                $tool_content .= "</td></tr>";
            } else {
                $tool_content .= "<tr><td class='right' colspan='2'>";
                $tool_content .= ok_message();
                $tool_content .= "</td></tr>";
            }
            $tool_content .= "</table></div>";
            break;
        case 'vmusers':
            $tool_content .= "<div class='row'>
                        <div class='col-sm-12'>
                        <h3 class='content-title'>$langUsers</h3>
                        <ul class='list-group'>
                        <li class='list-group-item'><label><a href='listusers.php?search=yes&verified_mail=1'>$langMailVerificationYes</a></label>          
                            <span class='badge'>" . Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE verified_mail = " . EMAIL_VERIFIED . ";")->cnt . "</span>
                        </li>
                        <li class='list-group-item'><label><a href='listusers.php?search=yes&verified_mail=2'>$langMailVerificationNo</a></label>                            
                            <span class='badge'>" . Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE verified_mail = " . EMAIL_UNVERIFIED . ";")->cnt . "</span>
                        </li>
                        <li class='list-group-item'><label><a href='listusers.php?search=yes&verified_mail=0'>$langMailVerificationPending</a></label>
                            <span class='badge'>" . Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE verified_mail = " . EMAIL_VERIFICATION_REQUIRED . ";")->cnt . "</span>
                        </li>
                        <li class='list-group-item'><label><a href='listusers.php?search=yes'>$langTotal</a></label>
                            <span class='badge'>" . Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user;")->cnt . "</span>
                        </li>
                        </ul>
                        </div></div>";
            break;
        default:
            break;
    }
}

/**
 * @brief output a <tr> with an array
 * @global type $langTypesClosed
 * @global type $langTypesRegistration
 * @global type $langTypesOpen
 * @global type $langsCourseInactiveShort
 * @global type $langPre
 * @global type $langPost
 * @global type $langOther
 * @global type $native_language_names_init
 * @param type $table
 * @return string
 */
function tablize($table) {

    global $langTypesClosed, $langTypesRegistration, $langTypesOpen, $langCourseInactiveShort,
    $langPre, $langPost, $langOther, $native_language_names_init;

    $ret = "";    
    if (is_array($table)) {
        foreach ($table as $key => $thevalue) {            
            $ret .= "<tr>";
            switch ($key) {
                case '0': $key = $langTypesClosed;
                    break;
                case '1': $key = $langTypesRegistration;
                    break;
                case '2': $key = $langTypesOpen;                
                    break;
                case '3': $key = $langCourseInactiveShort;
                    break;
                case 'pre': $key = $langPre;
                    break;
                case 'post': $key = $langPost;
                    break;
                case 'other': $key = $langOther;
                    break;
                case 'el': $key = $native_language_names_init['el'];
                    break;
                case 'en': $key = $native_language_names_init['en'];
                    break;
                case 'es': $key = $native_language_names_init['es'];
                    break;
                case 'fr': $key = $native_language_names_init['fr'];
                    break;
                case 'it': $key = $native_language_names_init['it'];
                    break;
                case 'de': $key = $native_language_names_init['de'];
                    break;
            }
            $ret .= "<td style='font-size: 90%'>" . $key . "</td>";
            $ret .= "<td class='right'><strong>" . $thevalue . "</strong></td></tr>";
        }
    }
    return $ret;
}

/**
 * @brief ok message 
 * @global type $langNotExist
 * @return type
 */
function ok_message() {
    global $langNotExist;

    return "<div class='text-center not_visible'> - $langNotExist - </div>";
}

/**
 * @brief error message
 * @global type $langExist
 * @return type
 */
function error_message() {
    global $langExist;

    return "<b><span style='color: #FF0000'>$langExist</span></b>";
}

/**
 * 
 * @param type $sql
 * @param type $fieldname
 * @return type
 */
function list_ManyResult($sql, $fieldname) {
    // require hierarchy
    if ($fieldname == 'faculte') {
        require_once 'include/lib/hierarchy.class.php';
    }
    $resu = array();
    $res = Database::get()->queryArray($sql);
    foreach ($res as $resA) {
        if ($fieldname == 'faculte') {
            $name = hierarchy::unserializeLangField($resA->faculte);
        } else {
            $name = $resA->$fieldname;
        }
        $resu[$name] = $resA->nb;
    }
    return $resu;
}

load_js('tools.js');
draw($tool_content, 3, null, $head_content);
