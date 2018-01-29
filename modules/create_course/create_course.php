<?php

/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2018  Greek Universities Network - GUnet
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

$require_login = TRUE;
$require_help = TRUE;
$helpTopic = 'portfolio';
$helpSubTopic = 'create_course';

require_once '../../include/baseTheme.php';

if ($session->status !== USER_TEACHER && !$is_departmentmanage_user) { // if we are not teachers or department managers
    redirect_to_home_page();
}

require_once 'include/log.class.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'functions.php';

$tree = new Hierarchy();
$course = new Course();
$user = new User();

$toolName = $langCourseCreate;

register_posted_variables(array('title' => true, 'password' => true, 'prof_names' => true));
if (empty($prof_names)) {
    $prof_names = "$_SESSION[givenname] $_SESSION[surname]";
}

// departments and validation
$allow_only_defaults = get_config('restrict_teacher_owndep') && !$is_admin;
$allowables = array();
if ($allow_only_defaults) {
    // Method: getDepartmentIdsAllowedForCourseCreation
    // fetches only specific tree nodes, not their sub-children
    //$user->getDepartmentIdsAllowedForCourseCreation($uid);
    // the code below searches for the allow_course flag in the user's department subtrees
    $userdeps = $user->getDepartmentIds($uid);
    $subs = $tree->buildSubtreesFull($userdeps);
    foreach ($subs as $node) {
        if (intval($node->allow_course) === 1) {
            $allowables[] = $node->id;
        }
    }
}
$departments = isset($_POST['department']) ? arrayValuesDirect($_POST['department']) : array();
$deps_valid = true;

foreach ($departments as $dep) {
    if ($allow_only_defaults && !in_array($dep, $allowables)) {
        $deps_valid = false;
        break;
    }
}
$data['deps_valid'] = $deps_valid;

// display form
if (!isset($_POST['create_course'])) {
        // set skip_preloaded_defaults in order to not over-bloat pre-populating nodepicker with defaults in case of multiple allowance
        list($js, $html) = $tree->buildCourseNodePickerIndirect(array('defaults' => $allowables, 'allow_only_defaults' => $allow_only_defaults, 'skip_preloaded_defaults' => true));        
        $head_content .= $js;
        $data['buildusernode'] = $html;
        $public_code = $title = '';
        foreach ($license as $id => $l_info) {
            if ($id and $id < 10) {
                $cc_license[$id] = $l_info['title'];
            }
        }
        $data['license_0'] = $license[0]['title'];
        $data['license_10'] = $license[10]['title'];
        $data['action_bar'] = action_bar(array(
                                array('title' => $langBack,
                                      'url' => $urlServer,
                                      'icon' => 'fa-reply',
                                      'level' => 'primary-label',
                                      'button-class' => 'btn-default')
                            ),false);
        
        $data['icon_course_open'] = $course_access_icons[COURSE_OPEN];
        $data['icon_course_registration'] = $course_access_icons[COURSE_REGISTRATION];
        $data['icon_course_closed'] = $course_access_icons[COURSE_CLOSED];
        $data['icon_course_inactive'] = $course_access_icons[COURSE_INACTIVE];
        $data['lang_select_options'] = lang_select_options('localize', "class='form-control'");
        $data['rich_text_editor'] = rich_text_editor('description', 4, 20, @$description);
        $data['selection_license'] = selection($cc_license, 'cc_use', "",'class="form-control"');
        $data['cancel_link'] = "{$urlServer}main/portfolio.php";
        generate_csrf_token_form_field(); 
        $data['menuTypeID'] = 1;
        view('modules.create_course.index', $data);
        
} else  { // create the course and the course database
    // validation in case it skipped JS validation
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $validationFailed = false;
    if (count($departments) < 1 || empty($departments[0])) {
        Session::Messages($langEmptyAddNode);
        $validationFailed = true;
    }

    if (empty($title) || empty($prof_names)) {
        Session::Messages($langFieldsMissing);
        $validationFailed = true;
    }

    if ($validationFailed) {
        redirect_to_home_page('modules/create_course/create_course.php');
    }
    
    // create new course code: uppercase, no spaces allowed
    $code = strtoupper(new_code($departments[0]));
    $code = str_replace(' ', '', $code);

    // include_messages
    include "lang/$language/common.inc.php";
    $extra_messages = "config/{$language_codes[$language]}.inc.php";
    if (file_exists($extra_messages)) {
        include $extra_messages;
    } else {
        $extra_messages = false;
    }
    include "lang/$language/messages.inc.php";
    if ($extra_messages) {
        include $extra_messages;
    }

    // create course directories
    if (!create_course_dirs($code)) {
        Session::Messages($langGeneralError, 'alert-danger');
        redirect_to_home_page('modules/create_course/create_course.php');
    }

    // get default quota values
    $doc_quota = get_config('doc_quota');
    $group_quota = get_config('group_quota');
    $video_quota = get_config('video_quota');
    $dropbox_quota = get_config('dropbox_quota');

    // get course_license
    if (isset($_POST['l_radio'])) {
        $l = $_POST['l_radio'];
        switch ($l) {
            case 'cc':
                if (isset($_POST['cc_use'])) {
                    $course_license = intval($_POST['cc_use']);
                }
                break;
            case '10':
                $course_license = 10;
                break;
            default:
                $course_license = 0;
                break;
        }
    }

    if (ctype_alnum($_POST['view_type'])) {
        $view_type = $_POST['view_type'];        
    }    
    if (empty($_POST['public_code'])) {
        $public_code = $code;
    } else {
        $public_code = $_POST['public_code'];
    }
    $description = purify($_POST['description']);
    $result = Database::get()->query("INSERT INTO course SET
                        code = ?s,
                        lang = ?s,
                        title = ?s,
                        visible = ?d,
                        course_license = ?d,
                        prof_names = ?s,
                        public_code = ?s,
                        doc_quota = ?f,
                        video_quota = ?f,
                        group_quota = ?f,
                        dropbox_quota = ?f,
                        password = ?s,
                        view_type = ?s,
                        start_date = " . DBHelper::timeAfter() . ",
                        finish_date = " . DBHelper::timeAfter(31536000) . ",
                        keywords = '',
                        created = " . DBHelper::timeAfter() . ",
                        glossary_expand = 0,
                        glossary_index = 1,
                        description = ?s",
            $code, $language, $title, $_POST['formvisible'],
            intval($course_license), $prof_names, $public_code, $doc_quota * 1024 * 1024,
            $video_quota * 1024 * 1024, $group_quota * 1024 * 1024,
            $dropbox_quota * 1024 * 1024, $password, $view_type,
            $description);
    $new_course_id = $result->lastInsertID;
    if (!$new_course_id) {
        Session::Messages($langGeneralError);
        redirect_to_home_page('modules/create_course/create_course.php');
    }
    
    // create course modules
    create_modules($new_course_id);

    Database::get()->query("INSERT INTO course_user SET
                                        course_id = ?d,
                                        user_id = ?d,
                                        status = " . USER_TEACHER . ",
                                        tutor = 1,
                                        reg_date = " . DBHelper::timeAfter() . ",
                                        document_timestamp = " . DBHelper::timeAfter() . "",
                                    $new_course_id, $uid);
    
    $course->refresh($new_course_id, $departments);

    // create courses/<CODE>/index.php
    course_index($code);

    // add a default forum category
    Database::get()->query("INSERT INTO forum_category
                            SET cat_title = ?s,
                            course_id = ?d", $langForumDefaultCat, $new_course_id);

    $_SESSION['courses'][$code] = USER_TEACHER;
       
    $data['action_bar'] = action_bar(array(
        array('title' => $langEnter,
              'url' => $urlAppend . "courses/$code/",
              'icon' => 'fa-arrow-right',
              'level' => 'primary-label',
              'button-class' => 'btn-success')));
    
    // logging
    Log::record(0, 0, LOG_CREATE_COURSE, array('id' => $new_course_id,
                                               'code' => $code,
                                               'title' => $title,
                                               'language' => $language,
                                               'visible' => $_POST['formvisible']));
    $data['title'] = $title;
    $data['menuTypeID'] = 1;
    view('modules.create_course.create_course', $data);
} // end of submit


