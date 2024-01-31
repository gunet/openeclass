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
 * @file otheractions.php
 * @brief display other actions
 */

$require_admin = TRUE;
require_once '../../include/baseTheme.php';
require_once 'modules/usage/usage.lib.php';

load_js('tools.js');
load_js('datatables');

$toolName = $langPlatformGenStats;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);

$tool_content .= action_bar(array(
                    array('title' => $langBack,
                        'url' => "../usage/index.php?t=a",
                        'icon' => 'fa-reply',
                        'level' => 'primary-label')
                    ));

// ---------------------
// actions
// ---------------------
if (isset($_GET['stats'])) {
    switch ($_GET['stats']) {
        case 'musers':
            $tool_content .= "<div class='table-responsive'>
                            <table class='table-default'>";
            $loginDouble = list_ManyResult("SELECT DISTINCT BINARY(username) AS username, COUNT(*) AS nb
				FROM user GROUP BY username HAVING nb > 1 ORDER BY nb DESC", 'username');
            $tool_content .= "<tr class='list-header'><th><b>$langMultipleUsers</b></th>
			<th class='right'><strong>$langResult</strong></th>
			</tr>";
            if (count($loginDouble) > 0) {
                $tool_content .= tablize($loginDouble, 'username');
                $tool_content .= "<tr><td class='right' colspan='2'>" . error_message(count($loginDouble)) . "</td></tr>";
            } else {
                $tool_content .= "<tr><td class='right' colspan='2'>" . ok_message() . "</td></tr>";
            }
            $tool_content .= "</table></div>";
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
                $tool_content .= tablize($loginDouble, 'email');
                $tool_content .= "<tr><td class=right colspan='2'>";
                $tool_content .= error_message(count($loginDouble));
                $tool_content .= "</td></tr>";
            } else {
                $tool_content .= "<tr><td class=right colspan='2'>";
                $tool_content .= ok_message();
                $tool_content .= "</td></tr>";
            }
            $tool_content .= "</table></div>";
            break;
        case 'mlogins':
            $sqlLoginDouble = "SELECT DISTINCT BINARY(CONCAT(username, \" -- \", password)) AS pair,
				COUNT(*) AS nb FROM user GROUP BY pair HAVING nb > 1 ORDER BY nb DESC";
            $loginDouble = list_ManyResult($sqlLoginDouble, 'pair');
            $tool_content .= "<div class='table-responsive'>
                            <table class='table-default'>
                            <tr class='list-header'>
                            <th><b>$langMultiplePairs LOGIN - PASS</b></th>
                            <th class='right'><b>$langResult</b></th>
                            </tr>";
            if (count($loginDouble) > 0) {
                $tool_content .= tablize($loginDouble, 'pair');
                $tool_content .= "<tr><td class='right' colspan='2'>";
                $tool_content .= error_message(count($loginDouble));
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
                        <div class='content-title h3'>$langUsers</div>
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
        case 'cusers':
            $q = Database::get()->queryArray("SELECT surname, givenname, username, user_id, count(course_id) AS num_of_courses 
                                FROM course_user 
                                    JOIN user 
                                ON course_user.user_id = user.id 
                                WHERE course_user.status=5 
                                    GROUP BY user_id 
                                    ORDER BY num_of_courses 
                                    DESC 
                                    LIMIT 0,30");
            $tool_content .= "<div class='table-responsive'>
                    <table class='table-default'>
                    <tr>
                        <th class='list-header'><strong>$langUsers</strong></th>
                        <th class='text-center'><strong>$langResult</strong></th>
                    </tr>";
                    foreach ($q as $data) {
                        $link = $urlServer . "modules/admin/edituser.php?u=" . $data->user_id;
                        $tool_content .= "<tr>
                        <td>" . $data->surname . " " . $data->givenname . " (<a href='$link'>" . $data->username . "</a>)</td>
                        <td class='text-center'>" . $data->num_of_courses . "</td>
                        </tr>";
                    }
            $tool_content .= "</table></div>";
            break;
        case 'popularcourses':
            $q = Database::get()->queryArray("SELECT code, public_code, title, prof_names, visible, COUNT(*) AS num_of_users 
                                FROM course 
                                    JOIN course_user 
                                ON course_id = course.id 
                                GROUP BY course_id 
                                ORDER BY COUNT(*) 
                                DESC 
                                LIMIT 30");
            $tool_content .= "<div class='table-responsive'>
                <table class='table-default'>
                <tr>
                    <th class='list-header'><strong>$langPopularCourses</strong></th>
                    <th class='list-header'><strong>$langUsers</strong></th>
                </tr>";
                foreach ($q as $data) {
                    $class = ($data->visible == COURSE_INACTIVE)? "not_visible" : "";
                    $link = $urlServer . "courses/" . $data->code . "/";
                    $tool_content .= "<tr class = '$class'>                      
                    <td><a href='$link'>" . $data->title . "</a> <small>(" . $data->public_code . ")</small> <br> <em>" . $data->prof_names . "</em></td>
                    <td class='text-center'>" . $data->num_of_users . "</td>
                    </tr>";
                }
            $tool_content .= "</table></div>";
            break;
        default:
            break;
    }
}


draw($tool_content, 3, null, $head_content);

/**
 * @brief output a <tr> with an array
 * @return string
 */
function tablize($table, $search) {

    global $urlServer;

    $ret = "";
    if (is_array($table)) {
        foreach ($table as $key => $thevalue) {
            $ret .= "<tr>";
            switch($search) {
                case 'email' : $link = $urlServer . "modules/admin/listusers.php?uname=&fname=&lname=&email=" . urlencode($key) . "&am=&user_type=0"
                                        . "&auth_type=0&reg_flag=1&user_registered_at=&verified_mail=3"
                                        . "&department=0&search_type=contains";
                    break;
                case 'username':
                case 'pair': $link = $urlServer . "modules/admin/listusers.php?uname=" . urlencode($key) . "&fname=&lname=&email=&am=&user_type=0"
                                        . "&auth_type=0&reg_flag=1&user_registered_at=&verified_mail=3"
                                        . "&department=0&search_type=contains";
                    break;
                default : $link = '';
            }

            $ret .= "<td style='font-size: 90%'><a href='$link'>" . $key . "</a></td>";
            $ret .= "<td class='right'><strong>" . $thevalue . "</strong></td></tr>";
        }
    }
    return $ret;
}

/**
 * @brief ok message
 * @return string
 */
function ok_message() {
    global $langNotExist;

    return "<div class='text-center not_visible'> - $langNotExist - </div>";
}

/**
 * @brief error message
 * @return string
 */
function error_message($count) {
    global $langExist, $langRegisterActions;

    return "<strong><span style='color: #FF0000'>$langExist</span><small> ($count $langRegisterActions)</small></strong>";
}

/**
 *
 * @param type $sql
 * @param type $fieldname
 * @return array
 */
function list_ManyResult($sql, $fieldname) {

    $resu = array();
    $res = Database::get()->queryArray($sql);
    foreach ($res as $resA) {
        $name = $resA->$fieldname;
        $resu[$name] = $resA->nb;
    }
    return $resu;
}
