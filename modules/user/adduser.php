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
 * @file adduser.php
 * @brief Course admin can add users to the course.
 */
$require_current_course = true;
$require_course_admin = true;
$require_help = true;
$helpTopic = 'course_users';

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';
require_once 'include/log.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';

$tree = new Hierarchy();
$user = new User();

$toolName = $langUsers;
$pageName = $langAddUser;
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langUsers);

if (isset($_GET['add'])) {
    $uid_to_add = intval(getDirectReference($_GET['add']));
    $result = Database::get()->query("INSERT IGNORE INTO course_user (user_id, course_id, status, reg_date, document_timestamp)
                                    VALUES (?d, ?d, " . USER_STUDENT . ", " . DBHelper::timeAfter() . ", " . DBHelper::timeAfter(). ")", $uid_to_add, $course_id);
    $r = Database::get()->queryArray("SELECT id FROM course_user_request WHERE uid = ?d AND course_id = ?d", $uid_to_add, $course_id);
    if ($r) { // close course user request (if any)
        foreach ($r as $req) {
            Database::get()->query("UPDATE course_user_request SET status = 2 WHERE id = ?d", $req->id);
        }
    }

    Log::record($course_id, MODULE_ID_USERS, LOG_INSERT, array('uid' => $uid_to_add,
                                                               'right' => '+5'));
    if ($result) {
        Session::flash('message',$langTheU . ' ' . $langAdded);
        Session::flash('alert-class', 'alert-success');
        // notify user via email
        $email = uid_to_email($uid_to_add);
        if (!empty($email) and valid_email($email) and get_user_email_notification($uid_to_add)) {
            $emailsubject = "$langYourReg " . course_id_to_title($course_id);
            $emailbody = "$langNotifyRegUser1 <a href='{$urlServer}courses/$course_code/'>" . q(course_id_to_title($course_id)) . "</a> $langNotifyRegUser2 $langFormula \n$gunet";

            $header_html_topic_notify = "<!-- Header Section -->
            <div id='mail-header'>
                <br>
                <div>
                    <div id='header-title'>$langYourReg " . course_id_to_title($course_id)."</div>
                </div>
            </div>";

            $body_html_topic_notify = "<!-- Body Section -->
            <div id='mail-body'>
                <br>
                <div id='mail-body-inner'>
                    $langNotifyRegUser1 '" . course_id_to_title($course_id) . "' $langNotifyRegUser2
                    <br><br>$langFormula<br>$gunet
                </div>
            </div>";

            $emailbody = $header_html_topic_notify.$body_html_topic_notify;

            $plainemailbody = html2text($emailbody);

            send_mail_multipart("$_SESSION[givenname] $_SESSION[surname]",  $_SESSION['email'], '', $email, $emailsubject, $plainemailbody, $emailbody);
        }
    } else {
        Session::flash('message',$langAddError);
        Session::flash('alert-class', 'alert-warning');
    }
    redirect_to_home_page("modules/user/index.php?course=$course_code");
} else {
    register_posted_variables(array('search_surname' => true,
                                    'search_givenname' => true,
                                    'search_username' => true,
                                    'search_am' => true), 'any');

    $results = '';
    $data['search_username'] = $data['search_givenname'] = $data['search_surname']  = $data['search_am'] = '';
    if (isset($_POST['search_surname'])) {
        $data['search_surname'] = $_POST['search_surname'];
    }
    if (isset($_POST['search_givenname'])) {
        $data['search_givenname'] = $_POST['search_givenname'];
    }
    if (isset($_POST['search_username'])) {
        $data['search_username'] = $_POST['search_username'];
    }
    if (isset($_POST['search_am'])) {
        $data['search_am'] = $_POST['search_am'];
    }


    $search = array();
    $values = array();
    foreach (array('surname', 'givenname', 'username', 'am') as $term) {
        $tvar = 'search_' . $term;
        if (!empty($GLOBALS[$tvar])) {
            $search[] = "u.$term LIKE LOWER(?s)";
            $values[] = $GLOBALS[$tvar] . '%';
        }
    }
    $query = join(' AND ', $search);
    if (!empty($query)) {
        Database::get()->query("CREATE TEMPORARY TABLE register_users_to_course AS
                    SELECT user_id FROM course_user WHERE course_id = ?d", $course_id);
        $result = Database::get()->queryArray("SELECT u.id, u.surname, u.givenname, u.username, u.am, c.user_id AS registered
                                                FROM user u LEFT JOIN register_users_to_course c ON u.id = c.user_id
                                                WHERE u.expires_at >= CURRENT_DATE() AND $query", $values);
        if ($result) {
            $results = "<div class='col-sm-12'><div class='table-responsive'><table class='table-default'>
                                <thead><tr class='list-header'>
                                  <th class='count-col'>$langID</th>
                                  <th>$langName</th>
                                  <th>$langSurname</th>
                                  <th>$langUsername</th>
                                  <th>$langFaculty</th>
                                  <th>$langActions</th>
                                </tr></thead>";
            $i = 1;
            foreach ($result as $myrow) {
                $departments = $user->getDepartmentIds($myrow->id);
                $dep_content = '';
                $j = 1;
                foreach ($departments as $dep) {
                    $br = ($j < count($departments)) ? '<br>' : '';
                    $dep_content .= $tree->getPath($dep) . $br;
                    $j++;
                }
                $results .= "<td class='count-col'>$i.</td><td>" . q($myrow->givenname) . "</td><td>" .
                        q($myrow->surname) . "</td><td>" . q($myrow->username) . "</td><td>" .
                        $dep_content . "</td><td class='text-center'>" .
                        ($myrow->registered ? "<span class='not_visible'>($langUserAlreadyRegisterd)</span>" :
                        icon('fa-sign-in', $langRegister, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;add=" . getIndirectReference($myrow->id))) .
                        "</td></tr>";
                $i++;
            }
            $results .= "</table></div></div>";
        } else {
            $results .= "<div class='col-12'><div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$langNoUsersFound</span></div></div>";
        }
        Database::get()->query("DROP TEMPORARY TABLE register_users_to_course");
    }
}

$data['results'] = $results;

view('modules.user.adduser', $data);
