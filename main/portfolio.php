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
 * @file portfolio.php
 * @brief This component creates the content of the start page when the user is logged in
 */

use Widgets\WidgetArea;

$require_login = true;
define('HIDE_TOOL_TITLE', true);

include '../include/baseTheme.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';
require_once 'perso.php';

$data['tree'] = new Hierarchy();
$user = new User();

if(!empty($langLanguageCode)){
    load_js('bootstrap-calendar-master/js/language/'.$langLanguageCode.'.js');
}
load_js('bootstrap-calendar-master/js/calendar.js');
load_js('bootstrap-calendar-master/components/underscore/underscore-min.js');
load_js('datatables');

// display privacy policy consent message to user if necessary
if (get_config('activate_privacy_policy_text')) {
    $consentMessage = get_config('privacy_policy_text_' . $session->language);
    if (isset($_POST['accept_policy'])) {
        if ($_POST['accept_policy'] == 'yes') {
            user_accept_policy($uid);
        } elseif ($_POST['accept_policy'] == 'no') {
            user_accept_policy($uid, false);
        } else {
            $_SESSION['accept_policy_later'] = true;
        }
        if (isset($_POST['next']) and $_POST['next'] == 'profile') {
            redirect_to_home_page('main/profile/display_profile.php#privacyPolicySection');
        }
        redirect_to_home_page();
    }
}

$data['action_bar'] = action_bar(array(
        array('title' => $langRegCourses,
              'url' => $urlAppend . 'modules/auth/courses.php',
              'icon' => 'fa-check',
              'level' => 'primary-label',
              'show' => is_enabled_course_registration($uid),
              'button-class' => 'btn-success'),
    array('title' => $langCourseCreate,
              'url' => $urlAppend . 'modules/create_course/create_course.php',
              'show' => $_SESSION['status'] == USER_TEACHER,
              'icon' => 'fa-plus-circle',
              'level' => 'primary-label',
              'button-class' => 'btn-success')));

$data['perso_tool_content'] = $perso_tool_content;
$data['user_announcements'] = $user_announcements;

$data['portfolio_page_main_widgets'] = '';
$portfolio_page_main = new WidgetArea(PORTFOLIO_PAGE_MAIN);

foreach ($portfolio_page_main->getUserAndAdminWidgets($uid) as $key => $widget) {
    $data['portfolio_page_main_widgets'] .= $widget->run($key);
}

$data['portfolio_page_sidebar_widgets'] = "";
$portfolio_page_sidebar = new WidgetArea(PORTFOLIO_PAGE_SIDEBAR);

foreach ($portfolio_page_sidebar->getUserAndAdminWidgets($uid) as $key => $widget) {
    $data['portfolio_page_sidebar_widgets'] .= $widget->run($key);
}

$data['departments'] = $user->getDepartmentIds($uid);

$data['lastVisit'] = Database::get()->querySingle("SELECT * FROM loginout
                        WHERE id_user = ?d ORDER by idLog DESC LIMIT 1", $uid);

$data['userdata'] = Database::get()->querySingle("SELECT email, am, phone, registered_at,
                                            has_icon, description, password,
                                            email_public, phone_public, am_public
                                        FROM user
                                        WHERE id = ?d", $uid);

if ($_SESSION['status'] == USER_TEACHER) {
    if(!get_config('show_always_collaboration')){
        $data['num_of_courses'] = CountTeacherCourses($uid);
    }
    if(get_config('show_collaboration')){
        $data['num_of_collaborations'] = CountTeacherCollaborations($uid);
    }
} else {
    if(!get_config('show_always_collaboration')){
        $data['num_of_courses'] = CountStudentCourses($uid);
    }
    if(get_config('show_collaboration')){
        $data['num_of_collaborations'] = CountStudentCollaborations($uid);
    }
}

$data['user_messages'] = $user_messages;


// For pagination pictures of user-courses
if(!get_config('show_always_collaboration')){
    $data['courses'] = $mine_courses;
}
if(get_config('show_collaboration')){
    $data['collaborations'] = $mine_collaborations;
    if(get_config('show_always_collaboration')){
        $data['courses'] = $mine_collaborations;
    }
}


$data['items_per_page'] = $items_per_page = 4;

if(!get_config('show_always_collaboration')){
    $data['course_pages'] = ceil(count($mine_courses)/$items_per_page);
}

if(get_config('show_collaboration')){
    $data['collaboration_pages'] = ceil(count($mine_collaborations)/$items_per_page);
}


view('portfolio.index', $data);
