<?php
/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2016  Greek Universities Network - GUnet
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

load_js('jstree3');
load_js('pwstrength.js');
load_js('tools.js');

$head_content .= <<<hContent
<script type="text/javascript">
/* <![CDATA[ */

function deactivate_input_password () {
        $('#coursepassword').attr('disabled', 'disabled');
        $('#coursepassword').closest('div.form-group').addClass('invisible');
}

function activate_input_password () {
        $('#coursepassword').removeAttr('disabled', 'disabled');
        $('#coursepassword').closest('div.form-group').removeClass('invisible');
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

	function hoursSum() {
        var lecthours = parseInt(document.getElementById('lecthours').value);
        var homehours = parseInt(document.getElementById('homehours').value);
        var totalhours = document.getElementById('totalhours');
        totalhours.value = homehours + lecthours;
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
$departments = isset($_POST['department']) ? $_POST['department'] : array();
$deps_valid = true;

foreach ($departments as $dep) {
    if ($allow_only_defaults && !in_array($dep, $allowables)) {
        $deps_valid = false;
        break;
    }
}

// Check if the teacher is allowed to create in the departments he chose
if (!$deps_valid) {
    $tool_content .= "<div class='alert alert-danger'>$langCreateCourseNotAllowedNode</div>
                    <p class='pull-right'><a class='btn btn-default' href='$_SERVER[SCRIPT_NAME]'>$langBack</a></p>";
    draw($tool_content, 1, null, $head_content);
    exit();
}

// display form
if (!isset($_POST['create_course'])) {
    // set skip_preloaded_defaults in order to not over-bloat pre-populating nodepicker with defaults in case of multiple allowance
    list($js, $html) = $tree->buildCourseNodePicker(array('defaults' => $allowables, 'allow_only_defaults' => $allow_only_defaults, 'skip_preloaded_defaults' => true));
    $head_content .= $js;
    $public_code = $title = $description = '';
    foreach ($license as $id => $l_info) {
        if ($id and $id < 10) {
            $cc_license[$id] = $l_info['title'];
        }
    }
    if (Database::get()->querySingle('SELECT id FROM activity_heading LIMIT 1')) {
        $activities = true;
    } else {
        $activities = false;
    }

    $tool_content .= action_bar(array(
        array('title' => $langBack,
              'url' => $urlServer,
              'icon' => 'fa-reply',
              'level' => 'primary-label',
              'button-class' => 'btn-default')), false);
    $tool_content .= "
    <div class='form-wrapper'>
    <form class='form-horizontal' role='form' method='post' name='createform' action='$_SERVER[SCRIPT_NAME]' onsubmit=\"return validateNodePickerForm();\">
        <fieldset>
            <div class='form-group'>
                <label for='title' class='col-sm-2 control-label'>$langTitle:</label>
                <div class='col-sm-10'>
                  <input name='title' id='title' type='text' class='form-control' value='" . q($title) . "' placeholder='$langTitle'>
                </div>
            </div>
            <div class='form-group'>
                <label for='title' class='col-sm-2 control-label'>$langCode:</label>
                <div class='col-sm-10'>
                  <input name='public_code' id='public_code' type='text' class='form-control' value='" . q($public_code) . "' placeholder='$langOptional'>
                </div>
            </div>
            <div class='form-group'>
                <label  class='col-sm-2 control-label'>$langFaculty:</label>
                <div class='col-sm-10'>
                  $html
                </div>
            </div>
            <div class='form-group'>
                <label for='prof_names' class='col-sm-2 control-label'>$langTeachers:</label>
                <div class='col-sm-10'>
                      <input class='form-control' type='text' name='prof_names' id='prof_names' value='" . q($prof_names) . "'>
                </div>
            </div>
            <div class='form-group'>
                <label for='localize' class='col-sm-2 control-label'>$langLanguage:</label>
                <div class='col-sm-10'>
                      " . lang_select_options('localize', "class='form-control'") . "
                </div>
            </div>
            <div class='form-group'>
                <label for='description' class='col-sm-2 control-label'>$langDescrInfo <small>$langOptional</small>:</label>
                <div class='col-sm-10'>
                      ".  rich_text_editor('description', 4, 20, $description)."
                </div>
            </div>
            <div class='form-group'>
                <label class='col-sm-2 control-label'>$langCourseFormat:</label>
                <div class='col-sm-10'>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='view_type' value='simple' id='simple'>
                        $langCourseSimpleFormat
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='view_type' value='units' id='units' checked>
                        $langWithCourseUnits
                      </label>
                    </div>
                    " .
                    ($activities? "
                    <div class='radio'>
                      <label>
                        <input type='radio' name='view_type' value='activity' id='activity'>
                        $langCourseActivityFormat
                      </label>
                    </div>": '') . "
                    <div class='radio'>
                      <label>
                        <input type='radio' name='view_type' value='wall' id='wall'>
                        $langCourseWallFormat
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='view_type' value='flippedclassroom' id='flippedclassroom'>
                        $langFlippedClassroom
                      </label>
                    </div>
                </div>
            </div>
            <div class='form-group'>
                <label class='col-sm-2 control-label'>$langOpenCoursesLicense:</label>
                <div class='col-sm-10'>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='l_radio' value='0' checked>
                        {$license[0]['title']}
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='l_radio' value='10'>
                        {$license[10]['title']}
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input id='cc_license' type='radio' name='l_radio' value='cc'>
                        $langCMeta[course_license]
                      </label>
                    </div>
                </div>
            </div>
            <div class='form-group' id='cc'>
                <div class='col-sm-10 col-sm-offset-2'>
                      " . selection($cc_license, 'cc_use', "",'class="form-control"') . "
                </div>
            </div>
            <div class='form-group'>
                <label for='localize' class='col-sm-2 control-label'>$langAvailableTypes:</label>
                <div class='col-sm-10'>
                    <div class='radio'>
                      <label>
                        <input id='courseopen' type='radio' name='formvisible' value='2'>".
                        course_access_icon(COURSE_OPEN)." $langOpenCourse
                        <span class='help-block'><small>$langPublic</small></span>
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input id='coursewithregistration' type='radio' name='formvisible' value='1' checked>".
                        course_access_icon(COURSE_REGISTRATION)." $langRegCourse
                        <span class='help-block'><small>$langPrivOpen</small></span>
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input id='courseclose' type='radio' name='formvisible' value='0'>".
                        course_access_icon(COURSE_CLOSED)." $langClosedCourse
                        <span class='help-block'><small>$langClosedCourseShort</small></span>
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input id='courseinactive' type='radio' name='formvisible' value='3'>".
                        course_access_icon(COURSE_INACTIVE)." $langInactiveCourse
                        <span class='help-block'><small>$langCourseInactive</small></span>
                      </label>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='coursepassword' class='col-sm-2 control-label'>$langOptPassword:</label>
                    <div class='col-sm-10'>
                          <input class='form-control' id='coursepassword' type='text' name='password' value='".@q($password)."' autocomplete='off'>
                    </div>
                    <div class='col-sm-2 text-center padding-thin'>
                        <span id='result'></span>
                    </div>
                </div>
                <div class='form-group'>
                    <div class='col-sm-10 col-sm-offset-2'>
                          <input class='btn btn-primary' type='submit' name='create_course' value='".q($langCourseCreate)."'>
                          <a href='{$urlServer}main/portfolio.php' class='btn btn-default'>$langCancel</a>
                    </div>
                </div>
            </div>
            <div class='text-right'><small>$langFieldsOptionalNote</small></div>
        </fieldset>
    ". generate_csrf_token_form_field() ."
    </form>
</div>";

} else if ($_POST['view_type'] == "flippedclassroom") {
    $_SESSION['title'] = $title;
    $_SESSION['code'] = $departments ;
    $_SESSION['language'] = $language ;
    $_SESSION['formvisible'] = $_POST['formvisible'] ;
    $_SESSION['l_radio'] = $_POST['l_radio'];
    $_SESSION['cc_use'] = $_POST['cc_use'] ;
    $_SESSION['public_code'] = $public_code ;
    $_SESSION['password'] = $_POST['password'];
    $_SESSION['description'] = purify($_POST['description']);

    if (empty($title)) {
        Session::Messages($langFieldsMissing);
        $validationFailed = true;
    }

    if ($validationFailed) {
        redirect_to_home_page('modules/create_course/create_course.php');
    }
    redirect_to_home_page('modules/create_course/flipped_classroom.php');
} else { // create the course and the course database
    // validation in case it skipped JS validation

    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $validationFailed = false;
    if (count($departments) < 1 || empty($departments[0])) {
        Session::Messages($langEmptyAddNode);
        $validationFailed = true;
    }

    if (empty($title)) {
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
        $public_code = mb_substr($_POST['public_code'], 0 ,20);
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
						flipped_flag = ?s,
                        view_type = ?s,
                        start_date = " . DBHelper::timeAfter() . ",
                        keywords = '',
                        created = " . DBHelper::timeAfter() . ",
                        glossary_expand = 0,
                        glossary_index = 1,
                        description = ?s",
            $code, $language, $title, $_POST['formvisible'],
            $course_license, $prof_names, $public_code, $doc_quota * 1024 * 1024,
            $video_quota * 1024 * 1024, $group_quota * 1024 * 1024,
            $dropbox_quota * 1024 * 1024, $password, 0, $view_type, $description);
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

    $tool_content .= "<div class='alert alert-success'><b>$langJustCreated:</b> " . q($title) . "<br>
                        <span class='smaller'>$langEnterMetadata</span></div>";
    $tool_content .= action_bar(array(
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
} // end of submit
draw($tool_content, 1, null, $head_content);
