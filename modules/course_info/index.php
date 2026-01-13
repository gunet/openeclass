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

// if we come from the home page
if (isset($_GET['from_home']) and ( $_GET['from_home'] == TRUE) and isset($_GET['cid'])) {
    session_start();
    $_SESSION['dbname'] = $_GET['cid'];
}

$require_current_course = true;
$require_course_admin = true;
$require_help = true;
$helpTopic = 'course_settings';
require_once '../../include/baseTheme.php';
require_once 'include/log.class.php';
require_once 'include/lib/user.class.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/course_settings.php';
require_once 'modules/sharing/sharing.php';

// Get success message from current course language
if (Session::has('course-modify-success')) {
    Session::flash('message', $langModifDone);
    Session::flash('alert-class', 'alert-success');
}

$user = new User();
$course = new Course();
$tree = new Hierarchy();

if (isset($_GET['course_code'])){
    $course_code = $_GET['course_code'];
    $course_id = course_code_to_id($course_code);
}

// departments and validation
$depadmin_mode = get_config('restrict_teacher_owndep') && !$is_admin;
$allowables = array();
if ($depadmin_mode) {
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

$allow_clone = false;
// $atleastone is set to true by init when a department admin can admin this course
if ($is_power_user or $is_admin or ($is_departmentmanage_user and $atleastone))  {
    $allow_clone = true;
}


$toolName = $langCourseInfo;

// if the course is `open courses` certified, disable visibility choice in form
$isOpenCourseCertified = ($creview = Database::get()->querySingle("SELECT is_certified FROM course_review WHERE course_id = ?d", $course_id)) ? $creview->is_certified : false;
$data['disable_visibility'] = $disabledVisibility = ($isOpenCourseCertified) ? " disabled " : '';

if (isset($_POST['submit'])) {
    $view_type = $_POST['view_type'];
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    checkSecondFactorChallenge();
    if (!isset($_POST['start_date']) or !$_POST['start_date']) {
        $_POST['start_date'] = null;
    }
    if (!isset($_POST['start_date']) or !$_POST['finish_date']) {
        $_POST['finish_date'] = null;
    }
    if (empty($_POST['title'])) {
        Session::flash('message',$langNoCourseTitle);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page("modules/course_info/index.php?course=$course_code");
    } else {
        // update course settings
        if (isset($_POST['formvisible']) and ( $_POST['formvisible'] == '1' or $_POST['formvisible'] == '2')) {
            $password = $_POST['password'];
        } else {
            $password = '';
        }
        // if it is `open courses` certified keeep the current course_license
        if (isset($_POST['course_license'])) {
            $course_license = getDirectReference($_POST['course_license']);
        }
        // update course_license
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

        // disable visibility if it is opencourses certified
        if (get_config('opencourses_enable') && $isOpenCourseCertified) {
            $_POST['formvisible'] = '2';
        }

        // flipped classroom settings
        if ($view_type == 'flippedclassroom') {
            $view_type = 'units';
            $flipped_flag = 2;
        } else {
            $flipped_flag = 0;
        }
        // validate departments
        $departments = isset($_POST['department']) ? $_POST['department'] : array();
        $deps_valid = true;
        foreach ($departments as $dep) {
            if ($depadmin_mode && !in_array($dep, $allowables)) {
                $deps_valid = false;
                break;
            }
        }

        $typeCourse = 0;
        if(isset($view_type) && $view_type == 'sessions'){
            $typeCourse = 1;
        }
        if(get_config('show_collaboration') && get_config('show_always_collaboration')){
            $typeCourse = 1;
        }
        if(get_config('show_collaboration') && !get_config('show_always_collaboration')){
            if (isset($_POST['is_type_collaborative']) and $_POST['is_type_collaborative'] == 'on') {
                $typeCourse = 1;
            }
        }

        $old_deps = $course->getDepartmentIds($course_id);
        $deps_changed = count(array_diff($old_deps, $departments)) +
                        count(array_diff($departments, $old_deps));

        $course_language = $session->language;
        if (isset($_POST['course_language']) and in_array($_POST['course_language'], $session->active_ui_languages)) {
            $course_language = $_POST['course_language'];
        }

        //=======================================================
        // Check if the teacher is allowed to create in the departments he chose
        if ($deps_changed and !$deps_valid) {
            Session::flash('message',$langCreateCourseNotAllowedNode);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/course_info/index.php?course=$course_code");
        } else {
            Database::get()->query("UPDATE course
                            SET title = ?s,
                                public_code = ?s,
                                keywords = ?s,
                                visible = ?d,
                                course_license = ?d,
                                prof_names = ?s,
                                lang = ?s,
                                password = ?s,
                                view_type = ?s,
                                flipped_flag = ?s,
                                is_collaborative = ?d
                            WHERE id = ?d",
                                $_POST['title'], mb_substr($_POST['fcode'], 0, 100), $_POST['course_keywords'],
                                $_POST['formvisible'], $course_license, $_POST['teacher_name'],
                                $course_language, $password, $view_type, $flipped_flag, $typeCourse, $course_id);
            $course->refresh($course_id, $departments);

            Log::record($course_id, MODULE_ID_COURSEINFO, LOG_MODIFY,
                array('title' => $_POST['title'],
                      'public_code' => mb_substr($_POST['fcode'], 0 ,20),
                      'visible' => $_POST['formvisible'],
                      'prof_names' => $_POST['teacher_name'],
                      'lang' => $session->language));

            // update course settings
            if (isset($_POST['s_radio'])) {
                setting_set(SETTING_COURSE_SHARING_ENABLE, $_POST['s_radio'], $course_id);
            }
            if (isset($_POST['r_radio'])) {
                setting_set(SETTING_COURSE_RATING_ENABLE, $_POST['r_radio'], $course_id);
            }
            if (isset($_POST['ran_radio'])) {
                setting_set(SETTING_COURSE_ANONYMOUS_RATING_ENABLE, $_POST['ran_radio'], $course_id);
            }
            if (isset($_POST['c_radio'])) {
                setting_set(SETTING_COURSE_COMMENT_ENABLE, $_POST['c_radio'], $course_id);
            }
            if (isset($_POST['h5p_radio'])) {
                setting_set(SETTING_COURSE_H5P_USERS_UPLOADING_ENABLE, $_POST['h5p_radio'], $course_id);
            }
            if (isset($_POST['ar_radio'])) {
                setting_set(SETTING_COURSE_ABUSE_REPORT_ENABLE, $_POST['ar_radio'], $course_id);
            }
            if (isset($_POST['enable_offline_course'])) {
                setting_set(SETTING_OFFLINE_COURSE, $_POST['enable_offline_course'], $course_id);
            }
            if (isset($_POST['disable_log_course_user_requests'])) {
                setting_set(SETTING_COURSE_USER_REQUESTS_DISABLE, $_POST['disable_log_course_user_requests'], $course_id);
            }
            if (isset($_POST['f_radio'])) {
                setting_set(SETTING_COURSE_FORUM_NOTIFICATIONS, $_POST['f_radio'], $course_id);
            }
            if (isset($_POST['docs_public_write'])) {
                setting_set(SETTING_DOCUMENTS_PUBLIC_WRITE, $_POST['docs_public_write']? '1': '0', $course_id);
            }
            if (isset($_POST['enable_access_users_list'])) {
                setting_set(SETTING_USERS_LIST_ACCESS, $_POST['enable_access_users_list'], $course_id);
            }
            if (isset($_POST['enable_agenda_announcement_widget_courseCompletion'])) {
                setting_set(SETTING_AGENDA_ANNOUNCEMENT_COURSE_COMPLETION, $_POST['enable_agenda_announcement_widget_courseCompletion'], $course_id);
            }
            if (isset($_POST['faculty_users_registration'])) {
                setting_set(SETTING_FACULTY_USERS_REGISTRATION, 1, $course_id);
            } else {
                setting_set(SETTING_FACULTY_USERS_REGISTRATION, 0, $course_id);
            }
            if (isset($_POST['choose_print_header_from_list'])) {
                setting_set(SETTING_COUSE_IMAGE_PRINT_HEADER, $_POST['choose_print_header_from_list'], $course_id);
            }
            if (isset($_POST['choose_print_footer_from_list'])) {
                setting_set(SETTING_COUSE_IMAGE_PRINT_FOOTER, $_POST['choose_print_footer_from_list'], $course_id);
            }
            if (isset($_POST['header_image_alignment'])) {
                setting_set(SETTING_COUSE_IMAGE_PRINT_HEADER_ALIGNMENT, $_POST['header_image_alignment'], $course_id);
            }
            if (isset($_POST['footer_image_alignment'])) {
                setting_set(SETTING_COUSE_IMAGE_PRINT_FOOTER_ALIGNMENT, $_POST['footer_image_alignment'], $course_id);
            }
            if (isset($_POST['header_image_width'])) {
                setting_set(SETTING_COUSE_IMAGE_PRINT_HEADER_WIDTH, $_POST['header_image_width'], $course_id);
            }
            if (isset($_POST['footer_image_width'])) {
                setting_set(SETTING_COUSE_IMAGE_PRINT_FOOTER_WIDTH, $_POST['footer_image_width'], $course_id);
            }

            // Course settings modified, will get a success message after redirect in current course language
            Session::flash('course-modify-success', true);
            redirect_to_home_page("modules/course_info/index.php?course=$course_code");
        }
    }
} else {
    $my_courses = Database::get()->queryArray("SELECT a.course_id course_id, b.title course_title FROM course_user a, course b
                                  WHERE a.course_id = b.id
                                      AND a.course_id != ?d
                                      AND a.user_id = ?d
                                      AND a.status = " .USER_TEACHER . " 
                                    ORDER BY course_title",
                                $course_id, $uid);
    $courses_options = "";
    foreach ($my_courses as $row) {
        $courses_options .= "'<option value=\"$row->course_id\">" . js_escape($row->course_title) . "</option>'+";
    }

    $data['courses_options'] = $courses_options;

    warnCourseInvalidDepartment();

    $data['action_bar'] = action_bar([
        ['title' => $langSyllabus,
            'url' => "../course_description/index.php?course=$course_code&" . generate_csrf_token_link_parameter(),
            'icon' => 'fa-info-circle'],
        ['title' => $langBackupCourse,
            'url' => "archive_course.php?course=$course_code&" . generate_csrf_token_link_parameter(),
            'icon' => 'fa-archive'],
        ['title' => $langRefreshCourse,
            'url' => "refresh_course.php?course=$course_code",
            'icon' => 'fa-refresh'],
        ['title' => $langCloneCourse,
            'url' => "clone_course.php?course=$course_code",
            'icon' => 'fa-archive',
            'show' => get_config('allow_teacher_clone_course') || $allow_clone],
        ['title' => $langImportCourse,
            'url' => "import_course.php?course=$course_code&fetch=yes",
            'icon' => 'fa-file-import',
            'level' => 'primary-label',
            'modal-class' => 'importCourse',
            'button-class' => 'btn-success'
        ],
        ['title' => $langCourseMetadata,
            'url' => "../course_metadata/index.php?course=$course_code",
            'icon' => 'fa-file-text',
            'show' => (get_config('course_metadata') && !$is_collaborative_course)],
        ['title' => $langCourseMetadataControlPanel,
            'url' => "../course_metadata/control.php?course=$course_code",
            'icon' => 'fa-list',
            'show' => (get_config('opencourses_enable') && $is_opencourses_reviewer && !$is_collaborative_course)],
        ['title' => $langCourseCategoryActions,
            'url' => "../course_category/index.php?course=$course_code",
            'icon' => 'fa-file-text',
            'show' => (!$is_collaborative_course)],
        ['title' => $langDelCourse,
            'url' => "delete_course.php?course=$course_code",
            'icon' => 'fa-xmark',
            'text-class' => 'text-danger']
    ]);

    $c = Database::get()->querySingle("SELECT title, keywords, visible, public_code, prof_names, lang,
                	       course_license, password, id, view_type, flipped_flag, is_collaborative
                      FROM course WHERE code = ?s", $course_code);
    if ($depadmin_mode) {
        list($js, $html) = $tree->buildCourseNodePicker(array('defaults' => $course->getDepartmentIds($c->id), 'allowables' => $allowables));
    } else {
        list($js, $html) = $tree->buildCourseNodePicker(array('defaults' => $course->getDepartmentIds($c->id)));
    }
    $head_content .= $js;
    $data['buildusernode'] = $html;

    $data['title'] = $c->title;

    $visible = $c->visible;
    $visibleChecked = array(COURSE_CLOSED => '', COURSE_REGISTRATION => '', COURSE_OPEN => '', COURSE_INACTIVE => '');
    $visibleChecked[$visible] = " checked='checked'";
    $data['course_closed'] = $visibleChecked[COURSE_CLOSED];
    $data['course_registration'] = $visibleChecked[COURSE_REGISTRATION];
    $data['course_open'] = $visibleChecked[COURSE_OPEN];
    $data['course_inactive'] = $visibleChecked[COURSE_INACTIVE];

    $data['public_code'] = $c->public_code;
    $data['teacher_name'] = $c->prof_names;

    $language = $c->lang;
    $data['lang_select_options'] = lang_select_options('course_language', 'id="course_language_id" class="form-control"', $language);
    $data['course_keywords'] = $c->keywords;

    $data['password'] = $c->password;

    $course_type = array('simple' => '', 'units' => '', 'wall' => '', 'activity' => '');
    $course_type[$c->view_type] = 'checked';
    $data['course_type_simple'] = $course_type['simple'];
    $data['course_type_units'] = $course_type['units'];
    $data['course_type_wall'] = $course_type['wall'];
    $data['course_type_activity'] = $course_type['activity'];
    if ($c->view_type == "units" && $c->flipped_flag == 2) {
        $data['course_type_flipped_classroom'] = 'checked';
    } else {
        $data['course_type_flipped_classroom'] = '';
    }
    if($c->view_type == "sessions" && $c->is_collaborative == 1){
        $data['course_type_sessions'] = 'checked';
    }else{
        $data['course_type_sessions'] = '';
    }

    if($c->is_collaborative == 1){
        $data['is_type_collaborative'] = 'checked';
    }else{
        $data['is_type_collaborative'] = '';
    }


    $course_license = $c->course_license;
    if ($course_license > 0 and $course_license < 10) {
        $cc_checked = ' checked';
    } else {
        $cc_checked = '';
    }
    $data['cc_checked'] = $cc_checked;
    foreach ($license as $id => $l_info) {
        $license_checked[$id] = ($course_license == $id) ? ' checked' : '';
        if ($id and $id < 10) {
            $cc_license[$id] = $l_info['title'];
        }
    }

    $data['license_selection'] = selection($cc_license, 'cc_use', $course_license, 'id="course_license_id" class="form-control"'.$disabledVisibility);
    $data['license_checked0'] = $license_checked[0];
    $data['license_checked10'] = $license_checked[10];

    // options about logging course user requests
    if (course_status($course_id) != COURSE_CLOSED) {
        $log_course_user_requests_inactive = ' disabled';
        $log_course_user_requests_dis = $langCourseUserRequestsDisabled;
    } else {
        $log_course_user_requests_inactive = '';
        $log_course_user_requests_dis = '';
    }
    $data['log_course_user_requests_inactive'] = $log_course_user_requests_inactive;
    $data['log_course_user_requests_dis'] = $log_course_user_requests_dis;

    //Sharing options
    if (!is_sharing_allowed($course_id)) {
        $sharing_radio_dis = ' disabled';
        if (!get_config('enable_social_sharing_links')) {
            $sharing_dis_label = $langSharingDisAdmin;
        }
        if (course_status($course_id) != COURSE_OPEN) {
            $sharing_dis_label = $langSharingDisCourse;
        }
    } else {
        $sharing_radio_dis = '';
        $sharing_dis_label = '';
    }
    $data['sharing_radio_dis'] = $sharing_radio_dis;
    $data['sharing_dis_label'] = $sharing_dis_label;

    if (setting_get(SETTING_USERS_LIST_ACCESS, $course_id) == 1) {
        $check_enable_access_users_list = 'checked';
        $check_disable_access_users_list = '';
    } else {
        $check_enable_access_users_list = '';
        $check_disable_access_users_list = 'checked';
    }
    $data['check_enable_access_users_list'] = $check_enable_access_users_list;
    $data['check_disable_access_users_list'] = $check_disable_access_users_list;

    if (setting_get(SETTING_COURSE_FORUM_NOTIFICATIONS, $course_id) == 1) {
        $checkForumDis = '';
        $checkForumEn = 'checked';
    } else {
        $checkForumDis = 'checked';
        $checkForumEn = '';
    }
    $data['checkForumDis'] = $checkForumDis;
    $data['checkForumEn'] = $checkForumEn;

    if (setting_get(SETTING_COURSE_SHARING_ENABLE, $course_id) == 1) {
        $checkSharingDis = '';
        $checkSharingEn = 'checked';
    } else {
        $checkSharingDis = 'checked';
        $checkSharingEn = '';
    }
    $data['checkSharingDis'] = $checkSharingDis;
    $data['checkSharingEn'] = $checkSharingEn;

    if (setting_get(SETTING_COURSE_RATING_ENABLE, $course_id) == 1) {
        $checkRatingDis = '';
        $checkRatingEn = 'checked';
    } else {
        $checkRatingDis = 'checked';
        $checkRatingEn = '';
    }
    $data['checkRatingDis'] = $checkRatingDis;
    $data['checkRatingEn'] = $checkRatingEn;

    // Anonymous user rating
    if (course_status($course_id) != COURSE_OPEN) {
        $anon_rating_radio_dis = ' disabled';
        $anon_rating_dis_label = $langRatingAnonDisCourse;
    } else {
        $anon_rating_radio_dis = '';
        $anon_rating_dis_label = '';
    }
    $data['anon_rating_radio_dis'] = $anon_rating_radio_dis;
    $data['anon_rating_dis_label'] = $anon_rating_dis_label;

    if (setting_get(SETTING_COURSE_ANONYMOUS_RATING_ENABLE, $course_id) == 1) {
        $checkAnonRatingDis = '';
        $checkAnonRatingEn = 'checked ';
    } else {
        $checkAnonRatingDis = 'checked ';
        $checkAnonRatingEn = '';
    }
    $data['checkAnonRatingDis'] = $checkAnonRatingDis;
    $data['checkAnonRatingEn'] = $checkAnonRatingEn;

    // User comments
    if (setting_get(SETTING_COURSE_COMMENT_ENABLE, $course_id) == 1) {
        $checkCommentDis = '';
        $checkCommentEn = 'checked ';
    } else {
        $checkCommentDis = 'checked ';
        $checkCommentEn = '';
    }
    $data['checkCommentDis'] = $checkCommentDis;
    $data['checkCommentEn'] = $checkCommentEn;

    // H5P users uploading
    if (setting_get(SETTING_COURSE_H5P_USERS_UPLOADING_ENABLE, $course_id) == 1) {
        $checkH5PDis = '';
        $checkH5PEn = 'checked ';
    } else {
        $checkH5PDis = 'checked ';
        $checkH5PEn = '';
    }
    $data['checkH5PDis'] = $checkH5PDis;
    $data['checkH5PEn'] = $checkH5PEn;

    // Abuse report
    if (setting_get(SETTING_COURSE_ABUSE_REPORT_ENABLE, $course_id) == 1) {
        $checkAbuseReportDis = '';
        $checkAbuseReportEn = 'checked ';
    } else {
        $checkAbuseReportDis = 'checked ';
        $checkAbuseReportEn = '';
    }
    $data['checkAbuseReportDis'] = $checkAbuseReportDis;
    $data['checkAbuseReportEn'] = $checkAbuseReportEn;

    // offline course
    if (!get_config('offline_course')) {
        $log_offline_course_inactive = ' disabled';
    } else {
        $log_offline_course_inactive = '';
    }

    if (setting_get(SETTING_OFFLINE_COURSE, $course_id)) {
        $log_offline_course_enable = 'checked';
        $log_offline_course_disable = '';
    } else {
        $log_offline_course_enable = '';
        $log_offline_course_disable = 'checked';
    }
    $data['log_offline_course_inactive'] = $log_offline_course_inactive;
    $data['log_offline_course_enable'] = $log_offline_course_enable;
    $data['log_offline_course_disable'] = $log_offline_course_disable;

    // Log course user requests
    if (setting_get(SETTING_COURSE_USER_REQUESTS_DISABLE, $course_id)) {
        $log_course_user_requests_disable = ' checked';
        $log_course_user_requests_enable = '';
    } else {
        $log_course_user_requests_disable = '';
        $log_course_user_requests_enable = ' checked';
    }
    $data['log_course_user_requests_disable'] = $log_course_user_requests_disable;
    $data['log_course_user_requests_enable'] = $log_course_user_requests_enable;

    if (setting_get(SETTING_AGENDA_ANNOUNCEMENT_COURSE_COMPLETION, $course_id) == 1) {
        $check_enable_agenda_announcement_widget_courseCompletion = ' checked';
        $check_disable_agenda_announcement_widget_courseCompletion = '';
    } else {
        $check_enable_agenda_announcement_widget_courseCompletion = '';
        $check_disable_agenda_announcement_widget_courseCompletion = ' checked';
    }
    $data['check_enable_agenda_announcement_widget_courseCompletion'] = $check_enable_agenda_announcement_widget_courseCompletion;
    $data['check_disable_agenda_announcement_widget_courseCompletion'] = $check_disable_agenda_announcement_widget_courseCompletion;

    if (setting_get(SETTING_FACULTY_USERS_REGISTRATION, $course_id) == 1) {
        $check_enable_faculty_users_registration = ' checked';
    } else {
        $check_enable_faculty_users_registration = '';
    }
    $data['check_enable_faculty_users_registration'] = $check_enable_faculty_users_registration;

    $data['form_url'] = "$_SERVER[SCRIPT_NAME]?course_code=$course_code";

    $data['cancel_link'] = "{$urlServer}courses/$course_code/";

    view('modules.course_info.index', $data);
}
