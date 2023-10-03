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
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greeceαψτι
 *                  e-mail: info@openeclass.org
 * ======================================================================== */

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
    Session::Messages($langModifDone,'alert-success');
}

$user = new User();
$course = new Course();
$tree = new Hierarchy();

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

load_js('jstree3');
load_js('pwstrength.js');
load_js('tools.js');

$head_content .= <<<hContent
<script type="text/javascript">
/* <![CDATA[ */

function deactivate_input_password () {
        $('#coursepassword').attr('disabled', 'disabled');
        $('#coursepassword').closest('div.form-group').addClass('hidden');
}

function activate_input_password () {
        $('#coursepassword').removeAttr('disabled', 'disabled');
        $('#coursepassword').closest('div.form-group').removeClass('hidden');
}

function displayCoursePassword() {

        if ($('#courseclose,#courseiactive').is(":checked")) {
                deactivate_input_password ();
        } else {
                activate_input_password ();
        }
}
    var lang = {
hContent;
$head_content .= "pwStrengthTooShort: '" . js_escape($langPwStrengthTooShort) . "', ";
$head_content .= "pwStrengthWeak: '" . js_escape($langPwStrengthWeak) . "', ";
$head_content .= "pwStrengthGood: '" . js_escape($langPwStrengthGood) . "', ";
$head_content .= "pwStrengthStrong: '" . js_escape($langPwStrengthStrong) . "'";
$head_content .= <<<hContent
    };

    function showCCFields() {
        $('#cc').show();
    }
    function hideCCFields() {
        $('#cc').hide();
    }

    $(document).ready(function() {

        $('#coursepassword').keyup(function() {
            $('#result').html(checkStrength($('#coursepassword').val()))
        });

        displayCoursePassword();

        $('#courseopen').click(function(event) {
                activate_input_password();
        });
        $('#coursewithregistration').click(function(event) {
                activate_input_password();
        });
        $('#courseclose').click(function(event) {
                deactivate_input_password();
        });
        $('#courseinactive').click(function(event) {
                deactivate_input_password();
        });

        $('input[name=l_radio]').change(function () {
            if ($('#cc_license').is(":checked")) {
                showCCFields();
            } else {
                hideCCFields();
            }
        }).change();
    });

/* ]]> */
</script>
hContent;

$toolName = $langCourseInfo;

// if the course is `open courses` certified, disable visibility choice in form
$isOpenCourseCertified = ($creview = Database::get()->querySingle("SELECT is_certified FROM course_review WHERE course_id = ?d", $course_id)) ? $creview->is_certified : false;
$disabledVisibility = ($isOpenCourseCertified) ? " disabled " : '';


if (isset($_POST['submit'])) {
    $view_type = $_POST['view_type'];
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    if (empty($_POST['title'])) {
        $tool_content .= "<div class='alert alert-danger'>$langNoCourseTitle</div>
                                  <p>&laquo; <a href='$_SERVER[SCRIPT_NAME]?course=$course_code'>$langAgain</a></p>";
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
            Session::Messages($langCreateCourseNotAllowedNode, 'alert-danger');
            redirect_to_home_page("modules/course_info/?course=$course_code");
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
                                flipped_flag = ?s
                            WHERE id = ?d",
                                $_POST['title'], mb_substr($_POST['fcode'], 0, 100), $_POST['course_keywords'],
                                $_POST['formvisible'], $course_license, $_POST['teacher_name'],
                                $course_language, $password, $view_type, $flipped_flag, $course_id);
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
            if (isset($_POST['enable_docs_public_write'])) {
                setting_set(SETTING_DOCUMENTS_PUBLIC_WRITE, $_POST['enable_docs_public_write'], $course_id);
            }
            if (isset($_POST['enable_access_users_list'])) {
                setting_set(SETTING_USERS_LIST_ACCESS, $_POST['enable_access_users_list'], $course_id);
            }
            // Course settings modified, will get success message after redirect in current course language
            Session::flash('course-modify-success', true);
            redirect_to_home_page("modules/course_info/index.php?course=$course_code");
        }
    }
} else {
    warnCourseInvalidDepartment();

    $action_bar_array0 = array(
        array('title' => $langCourseDescription,
            'url' => "../course_description/index.php?course=$course_code&".generate_csrf_token_link_parameter(),
            'icon' => 'fa-info-circle',
            'level' => 'primary-label'),
        array('title' => $langBackupCourse,
            'url' => "archive_course.php?course=$course_code&".generate_csrf_token_link_parameter(),
            'icon' => 'fa-archive',
            'level' => 'primary-label'),
        array('title' => $langBack,
            'url' => "{$urlServer}courses/$course_code/index.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')
    );

    // access control for when to display the link to clone course
    if (get_config('allow_teacher_clone_course') || $is_admin) {
        $action_bar_array0 = array_merge($action_bar_array0, array(
            array('title' => $langCloneCourse,
                  'url' => "clone_course.php?course=$course_code",
                  'icon' => 'fa-archive')
        ));
    }

    $action_bar_array = array_merge($action_bar_array0, array(
        array('title' => $langRefreshCourse,
            'url' => "refresh_course.php?course=$course_code",
            'icon' => 'fa-refresh'),
        array('title' => $langCourseMetadata,
            'url' => "../course_metadata/index.php?course=$course_code",
            'icon' => 'fa-file-text',
            'show' => get_config('course_metadata')),
        array('title' => $langCourseMetadataControlPanel,
            'url' => "../course_metadata/control.php?course=$course_code",
            'icon' => 'fa-list',
            'show' => get_config('opencourses_enable') && $is_opencourses_reviewer),
        array('title' => $langCourseCategoryActions,
            'url' => "../course_category/index.php?course=$course_code",
            'icon' => 'fa-file-text'),
        array('title' => $langDelCourse,
            'url' => "delete_course.php?course=$course_code",
            'icon' => 'fa-times',
            'button-class' => 'btn-danger')
    ));

    $tool_content .= "
    <div id='operations_container'>" . action_bar($action_bar_array) . "</div>";

    $c = Database::get()->querySingle("SELECT title, keywords, visible, public_code, prof_names, lang,
                           course_license, password, id, view_type, flipped_flag
                      FROM course WHERE code = ?s", $course_code);
    $title = $c->title;
    $visible = $c->visible;
    $visibleChecked = array(COURSE_CLOSED => '', COURSE_REGISTRATION => '', COURSE_OPEN => '', COURSE_INACTIVE => '');
    $visibleChecked[$visible] = " checked='checked'";
    $public_code = q($c->public_code);
    $teacher_name = q($c->prof_names);
    $languageCourse = $c->lang;
    $course_keywords = q($c->keywords);
    $password = q($c->password);
    $course_license = $c->course_license;
    if ($course_license > 0 and $course_license < 10) {
        $cc_checked = ' checked';
    } else {
        $cc_checked = '';
    }
    foreach ($license as $id => $l_info) {
        $license_checked[$id] = ($course_license == $id) ? ' checked' : '';
        if ($id and $id < 10) {
            $cc_license[$id] = $l_info['title'];
        }
    }
    // options about logging course user requests
    if (course_status($course_id) != COURSE_CLOSED) {
        $log_course_user_requests_inactive = ' disabled';
        $log_course_user_requests_dis = $langCourseUserRequestsDisabled;
    } else {
        $log_course_user_requests_inactive = '';
        $log_course_user_requests_dis = '';
    }
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


    if (setting_get(SETTING_USERS_LIST_ACCESS, $course_id) == 1) {
        $check_enable_access_users_list = 'checked';
        $check_disable_access_users_list = '';
    } else {
        $check_enable_access_users_list = '';
        $check_disable_access_users_list = 'checked';
    }

    if (setting_get(SETTING_COURSE_FORUM_NOTIFICATIONS, $course_id) == 1) {
        $checkForumDis = '';
        $checkForumEn = 'checked';
    } else {
        $checkForumDis = 'checked';
        $checkForumEn = '';
    }

    if (setting_get(SETTING_COURSE_SHARING_ENABLE, $course_id) == 1) {
        $checkSharingDis = '';
        $checkSharingEn = 'checked';
    } else {
        $checkSharingDis = 'checked';
        $checkSharingEn = '';
    }

    if (setting_get(SETTING_COURSE_RATING_ENABLE, $course_id) == 1) {
        $checkRatingDis = '';
        $checkRatingEn = 'checked';
    } else {
        $checkRatingDis = 'checked';
        $checkRatingEn = '';
    }
    //ANONYMOUS USER RATING
    if (course_status($course_id) != COURSE_OPEN) {
        $anon_rating_radio_dis = ' disabled';
        $anon_rating_dis_label = $langRatingAnonDisCourse;
    } else {
        $anon_rating_radio_dis = '';
        $anon_rating_dis_label = '';
    }

    if (setting_get(SETTING_COURSE_ANONYMOUS_RATING_ENABLE, $course_id)) {
        $checkAnonRatingDis = '';
        $checkAnonRatingEn = 'checked ';
    } else {
        $checkAnonRatingDis = 'checked ';
        $checkAnonRatingEn = '';
    }
    // USER COMMENTS
    if (setting_get(SETTING_COURSE_COMMENT_ENABLE, $course_id)) {
        $checkCommentDis = '';
        $checkCommentEn = 'checked ';
    } else {
        $checkCommentDis = 'checked ';
        $checkCommentEn = '';
    }
    // ABUSE REPORT
    if (setting_get(SETTING_COURSE_ABUSE_REPORT_ENABLE, $course_id)) {
        $checkAbuseReportDis = '';
        $checkAbuseReportEn = 'checked ';
    } else {
        $checkAbuseReportDis = 'checked ';
        $checkAbuseReportEn = '';
    }
    // OFFLINE COURSE
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
    // LOG COURSE USER REQUESTS
    if (setting_get(SETTING_COURSE_USER_REQUESTS_DISABLE, $course_id)) {
        $log_course_user_requests_disable = 'checked';
        $log_course_user_requests_enable = '';
    } else {
        $log_course_user_requests_disable = '';
        $log_course_user_requests_enable = 'checked';
    }
    if (setting_get(SETTING_DOCUMENTS_PUBLIC_WRITE, $course_id)) {
        $enable_docs_public_write = ' checked';
        $disable_docs_public_write = '';
    } else {
        $enable_docs_public_write = '';
        $disable_docs_public_write = ' checked';
    }
    $tool_content .= "<div class='form-wrapper'>
    <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code' onsubmit='return validateNodePickerForm();'>
    <fieldset>
    <div class='form-group'>
            <label for='fcode' class='col-sm-2 control-label'>$langCode</label>
            <div class='col-sm-10'>
                <input type='text' class='form-control' name='fcode' id='fcode' value='$public_code'>
            </div>
        </div>
        <div class='form-group'>
            <label for='title' class='col-sm-2 control-label'>$langCourseTitle:</label>
            <div class='col-sm-10'>
        <input type='text' class='form-control' name='title' id='title' value='" . q($title) . "'>
        </div>
        </div>
        <div class='form-group'>
            <label for='teacher_name' class='col-sm-2 control-label'>$langTeachers:</label>
            <div class='col-sm-10'>
        <input type='text' class='form-control' name='teacher_name' id='teacher_name' value='$teacher_name'>
        </div>
        </div>
        <div class='form-group'>
        <label for='Faculty' class='col-sm-2 control-label'>$langFaculty:</label>
            <div class='col-sm-10'>";
        if ($depadmin_mode) {
            list($js, $html) = $tree->buildCourseNodePicker(array('defaults' => $course->getDepartmentIds($c->id), 'allowables' => $allowables));
        } else {
            list($js, $html) = $tree->buildCourseNodePicker(array('defaults' => $course->getDepartmentIds($c->id)));
        }
        $head_content .= $js;
        $tool_content .= $html;

        if ($c->view_type == 'activity' or Database::get()->querySingle('SELECT id FROM activity_heading LIMIT 1')) {
            $activities = true;
        } else {
            $activities = false;
        }

        @$tool_content .= "</div></div>
        <div class='form-group'>
            <label for='course_keywords' class='col-sm-2 control-label'>$langCourseKeywords</label>
            <div class='col-sm-10'>
                <input type='text' class='form-control' name='course_keywords' id='course_keywords' value='$course_keywords'>
            </div>
        </div>
        <div class='form-group'>
                <label class='col-sm-2 control-label'>$langCourseFormat:</label>
                <div class='col-sm-10'>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='view_type' value='simple' id='simple'".($c->view_type == "simple" ? " checked" : "").">
                        $langCourseSimpleFormat
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='view_type' value='units' id='units'".($c->view_type == "units" ? " checked" : "").">
                        $langWithCourseUnits
                      </label>
                    </div>" . ($activities? ("
                    <div class='radio'>
                      <label>
                        <input type='radio' name='view_type' value='activity' id='activity'".($c->view_type == "activity" ? " checked" : "").">
                        $langCourseActivityFormat
                      </label>
                    </div>"): '') . "
                    <div class='radio'>
                      <label>
                        <input type='radio' name='view_type' value='wall' id='wall'".($c->view_type == "wall" ? " checked" : "").">
                        $langCourseWallFormat
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='view_type' value='flippedclassroom' id='flippedclassroom'".(($c->view_type == "units" && $c->flipped_flag == 2)? " checked" : "").">
                        $langFlippedClassroom
                      </label>
                    </div>
                </div>
            </div>";

    if ($isOpenCourseCertified) {
        $tool_content .= "<input type='hidden' name='course_license' value='" . getIndirectReference($course_license) . "'>";
    }
    $language = $c->lang;
    $tool_content .= "
            <div class='form-group'>
                <label class='col-sm-2 control-label'>$langOpenCoursesLicense:</label>
                <div class='col-sm-10'>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='l_radio' value='0'$license_checked[0]$disabledVisibility>
                        {$license[0]['title']}
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='l_radio' value='10'$license_checked[10]$disabledVisibility>
                        {$license[10]['title']}
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input id='cc_license' type='radio' name='l_radio' value='cc'$cc_checked$disabledVisibility>
                        $langCMeta[course_license]
                      </label>
                    </div>
                </div>
            </div>
            <div class='form-group'>
                <div class='col-sm-10 col-sm-offset-2' id='cc'>
                    " . selection($cc_license, 'cc_use', $course_license, 'class="form-control"'.$disabledVisibility) . "
                </div>
            </div>
            <div class='form-group'>
                <label class='col-sm-2 control-label'>$langConfidentiality:</label>
                <div class='col-sm-10'>
                    <div class='radio'>
                      <label>
                        <input id='courseopen' type='radio' name='formvisible' value='2' $visibleChecked[2]>
                        " . course_access_icon(COURSE_OPEN). "$langOpenCourse
                        <span class='help-block'><small>$langPublic</small></span>
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input id='coursewithregistration' type='radio' name='formvisible' value='1' $visibleChecked[1]>
                        " . course_access_icon(COURSE_REGISTRATION) . "$langTypeRegistration
                        <span class='help-block'><small>$langPrivOpen</small></span>
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input id='courseclose' type='radio' name='formvisible' value='0' $visibleChecked[0] $disabledVisibility>
                        " . course_access_icon(COURSE_CLOSED) . "$langClosedCourse
                        <span class='help-block'><small>$langClosedCourseShort</small></span>
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input id='courseinactive' type='radio' name='formvisible' value='3' $visibleChecked[3] $disabledVisibility>
                            " . course_access_icon(COURSE_INACTIVE) . "$langInactiveCourse
                        <span class='help-block'><small>$langCourseInactive</small></span>
                      </label>
                    </div>
                </div>
            </div>
            <div class='form-group'>
                <label for='coursepassword' class='col-sm-2 control-label'>$langPassCode:</label>
                <div class='col-sm-10'>
                      <input class='form-control' id='coursepassword' type='text' name='password' value='".@q($password)."' autocomplete='off'>
                </div>
                <div class='col-sm-2 text-center padding-thin'>
                    <span id='result'></span>
                </div>
            </div>
        <div class='form-group'>
                <label for='Options' class='col-sm-2 control-label'>$langLanguage:</label>
                <div class='col-sm-10'>" . lang_select_options('course_language', 'class="form-control"', $language) . "</div>
        </div>";

        $tool_content .= "<div class='course-info-title clearfix'>
                            <a role='button' data-toggle='collapse' href='#MoreInfo' aria-expanded='false' aria-controls='MoreInfo'>
                                 <h5 class='panel-heading' style='margin-bottom: 0px;'>
                                       <span class='fa fa-chevron-down fa-fw'></span> $langReadMore
                                 </h5>
                            </a>
                          </div>";

        $tool_content .= "<div class='collapse' id='MoreInfo'>
            <div class='form-group'>
                <label class='col-sm-2 control-label'>$langCourseOfflineSettings:</label>
                <div class='col-sm-10'>
                    <div class='radio'>
                      <label>
                            <input type='radio' value='1' name='enable_offline_course' $log_offline_course_enable $log_offline_course_inactive> $langActivate
                            <span class='help-block'><small>$langCourseOfflineLegend</small></span>
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                            <input type='radio' value='0' name='enable_offline_course' $log_offline_course_disable $log_offline_course_inactive> $langDeactivate
                      </label>
                    </div>
                </div>
            </div>

            <div class='form-group'>
                <label class='col-sm-2 control-label'>$langCourseUserRequests:</label>
                <div class='col-sm-10'>
                    <div class='radio'>
                      <label>
                            <input type='radio' value='0' name='disable_log_course_user_requests' $log_course_user_requests_enable $log_course_user_requests_inactive> $langActivate
                            <span class='help-block'><small>$log_course_user_requests_dis</small></span>
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                            <input type='radio' value='1' name='disable_log_course_user_requests' $log_course_user_requests_disable $log_course_user_requests_inactive> $langDeactivate
                      </label>
                    </div>
                </div>
            </div>

            <div class='form-group'>
                <label class='col-sm-2 control-label'>$langUsersListAccess:</label>
                <div class='col-sm-10'>
                    <div class='radio'>
                      <label>
                            <input type='radio' value='1' name='enable_access_users_list' $check_enable_access_users_list> $langActivate
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                            <input type='radio' value='0' name='enable_access_users_list' $check_disable_access_users_list> $langDeactivate
                            <span class='help-block'><small>$langUsersListAccessInfo</small></span>
                      </label>
                    </div>
                </div>
            </div>

            <div class='form-group'>
                <label class='col-sm-2 control-label'>$langCourseSharing:</label>
                <div class='col-sm-10'>
                    <div class='radio'>
                      <label>
                            <input type='radio' value='1' name='s_radio' $checkSharingEn $sharing_radio_dis> $langSharingEn
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                            <input type='radio' value='0' name='s_radio' $checkSharingDis $sharing_radio_dis> $langSharingDis
                            <span class='help-block'><small>$sharing_dis_label</small></span>
                      </label>
                    </div>
                </div>
            </div>
            <div class='form-group'>
                <label class='col-sm-2 control-label'>$langForum:</label>
                <div class='col-sm-10'>
                    <div class='radio'>
                      <label>
                            <input type='radio' value='1' name='f_radio' $checkForumEn> $langDisableForumNotifications
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                            <input type='radio' value='0' name='f_radio' $checkForumDis> $langActivateForumNotifications
                      </label>
                    </div>
                </div>
            </div>
            <div class='form-group'>
                <label class='col-sm-2 control-label'>$langCourseRating:</label>
                <div class='col-sm-10'>
                    <div class='radio'>
                      <label>
                            <input type='radio' value='1' name='r_radio' $checkRatingEn> $langRatingEn
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                            <input type='radio' value='0' name='r_radio' $checkRatingDis> $langRatingDis
                      </label>
                    </div>
                </div>
            </div>
            <div class='form-group'>
                <label class='col-sm-2 control-label'>$langCourseAnonymousRating:</label>
                <div class='col-sm-10'>
                    <div class='radio'>
                      <label>
                            <input type='radio' value='1' name='ran_radio' $checkAnonRatingEn $anon_rating_radio_dis> $langRatingAnonEn
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                            <input type='radio' value='0' name='ran_radio' $checkAnonRatingDis $anon_rating_radio_dis> $langRatingAnonDis
                            <span class='help-block'><small>$anon_rating_dis_label</small></span>
                      </label>
                    </div>
                </div>
            </div>
            <div class='form-group'>
                <label class='col-sm-2 control-label'>$langCourseCommenting:</label>
                <div class='col-sm-10'>
                    <div class='radio'>
                      <label>
                            <input type='radio' value='1' name='c_radio' $checkCommentEn> $langCommentsEn
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                            <input type='radio' value='0' name='c_radio' $checkCommentDis> $langCommentsDis
                      </label>
                    </div>
                </div>
            </div>
            <div class='form-group'>
                <label class='col-sm-2 control-label'>$langAbuseReport:</label>
                <div class='col-sm-10'>
                    <div class='radio'>
                      <label>
                            <input type='radio' value='1' name='ar_radio' $checkAbuseReportEn> $langAbuseReportEn
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                            <input type='radio' value='0' name='ar_radio' $checkAbuseReportDis> $langAbuseReportDis
                      </label>
                    </div>
                </div>
            </div>" . (get_config('enable_docs_public_write')? "
            <div class='form-group'>
                <label class='col-sm-2 control-label'>$langPublicDocumentManagement:</label>
                <div class='col-sm-10'>
                    <div class='radio'>
                      <label>
                            <input type='radio' value='1' name='enable_docs_public_write'$enable_docs_public_write> $langActivate
                            <span class='help-block'><small>$langPublicDocumentManagementExplanation</small></span>
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                            <input type='radio' value='0' name='enable_docs_public_write'$disable_docs_public_write> $langDeactivate
                      </label>
                    </div>
                </div>
            </div>": '') . "
            </div>
            <div class='form-group'>
                <div class='col-sm-10 col-sm-offset-2'>
                    <input class='btn btn-primary' type='submit' name='submit' value='$langSubmit'>
                </div>
            </div>
        </fieldset>
        ". generate_csrf_token_form_field() ."
    </form>
</div>";
}

draw($tool_content, 2, null, $head_content);
