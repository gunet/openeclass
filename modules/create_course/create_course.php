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

$require_login = TRUE;
$require_help = TRUE;
$helpTopic = 'portfolio';
$helpSubTopic = 'create_course';

require_once '../../include/baseTheme.php';

if ($session->status !== USER_TEACHER && !$is_departmentmanage_user) { // if we are not teachers or department managers
    redirect_to_home_page();
}

// Load AI service for course creation
try {
    require_once 'include/lib/ai/services/AICourseExtractionService.php';
} catch (Exception $e) {
    // AI service not available, continue without it
    error_log("AI Course Service Error: " . $e->getMessage());
}

require_once 'include/log.class.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/course_settings.php';
require_once 'functions.php';

$tree = new Hierarchy();
$course = new Course();
$user = new User();

/**
 * Save structured syllabus sections to course_description table
 *
 * @param int $course_id The course ID
 * @param array $syllabus_sections Array of syllabus sections
 * @param string $language Course language for determining section names
 */
function saveSyllabusSections($course_id, $syllabus_sections, $language = 'el') {
    // Map section keys to course_description_type IDs
    $section_type_mapping = [
        'objectives' => 2,        // Μαθησιακοί στόχοι / Course Objectives/Goals
        'bibliography' => 3,      // Βιβλιογραφία / Bibliography
        'teaching_method' => 4,   // Μέθοδοι διδασκαλίας / Instructional Methods
        'assessment_method' => 5, // Μέθοδοι αξιολόγησης / Assessment Methods
        'prerequisites' => 6,     // Προαπαιτούμενα / Prerequisites/Prior Knowledge
        'instructors' => 7,       // Διδάσκοντες / Instructors
        'target_group' => 8,      // Ομάδα στόχος / Target Group
        'textbooks' => 9,         // Προτεινόμενα συγγράμματα / Textbooks
        'additional_info' => 10   // Περισσότερα / Additional info
    ];

    $order_counter = 1;
    foreach ($syllabus_sections as $section_key => $content) {
        if (empty($content) || !isset($section_type_mapping[$section_key])) {
            continue;
        }

        $type_id = $section_type_mapping[$section_key];

        // Get the section title from course_description_type
        $type_info = Database::get()->querySingle("SELECT title FROM course_description_type WHERE id = ?d", $type_id);
        if ($type_info) {
            // Unserialize the title to get language-specific name
            $titles = unserialize($type_info->title);
            $section_title = $titles[$language] ?? $titles['en'] ?? 'Section';
        } else {
            $section_title = 'Section';
        }

        // Insert the section
        Database::get()->query("INSERT INTO course_description SET
            course_id = ?d,
            title = ?s,
            comments = ?s,
            type = ?d,
            visible = 1,
            `order` = ?d,
            update_dt = " . DBHelper::timeAfter(),
            $course_id, $section_title, $content, $type_id, $order_counter
        );

        $order_counter++;
    }
}

$toolName = $langPortfolio;
$pageName = $langCourseCreate;

register_posted_variables(array('title' => true, 'password' => true));
if (empty($prof_names)) {
    $data['prof_names'] = $prof_names = "$_SESSION[givenname] $_SESSION[surname]";
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
$departments = $_POST['department'] ?? array();
$deps_valid = true;

foreach ($departments as $dep) {
    if ($allow_only_defaults && !in_array($dep, $allowables)) {
        $deps_valid = false;
        break;
    }
}
$data['deps_valid'] = $deps_valid;
$data['title'] = Session::has('title') ? Session::get('title') : '';
$data['public_code'] = Session::has('public_code') ? Session::get('public_code') : '';
$description = Session::has('description') ? Session::get('description') : '';
$data['prof_names'] = $prof_names = Session::has('prof_names') ? Session::get('prof_names') : "$_SESSION[givenname] $_SESSION[surname]";
$data['ai_syllabus_sections'] = Session::has('ai_syllabus_sections') ? Session::get('ai_syllabus_sections') : '';

// display form
if (!isset($_POST['create_course'])) {
        // set skip_preloaded_defaults in order to not over-bloat pre-populating nodepicker with defaults in case of multiple allowance
        list($js, $html) = $tree->buildCourseNodePicker(array('defaults' => $allowables, 'allow_only_defaults' => $allow_only_defaults, 'skip_preloaded_defaults' => true));
        $head_content .= $js;
        $data['buildusernode'] = $html;
        foreach ($license as $id => $l_info) {
            if ($id and $id < 10) {
                $cc_license[$id] = $l_info['title'];
            }
        }
        $data['license_0'] = $license[0]['title'];
        $data['license_10'] = $license[10]['title'];

        $data['icon_course_open'] = course_access_icon(COURSE_OPEN);
        $data['icon_course_registration'] = course_access_icon(COURSE_REGISTRATION);
        $data['icon_course_closed'] = course_access_icon(COURSE_CLOSED);
        $data['icon_course_inactive'] = course_access_icon(COURSE_INACTIVE);
        $data['lang_select_options'] = lang_select_options('localize', "class='form-control' id='lang_selected'");
        $data['rich_text_editor'] = rich_text_editor('description', 4, 20, $description);
        $data['selection_license'] = selection($cc_license, 'cc_use', "",'class="form-select" id="course_license_id"');
        $data['cancel_link'] = "{$urlServer}main/portfolio.php";
        generate_csrf_token_form_field();

        // course image
        $image_content = '';
        $dir_images = scandir($webDir . '/template/modern/images/courses_images');
        foreach($dir_images as $image) {
            $extension = pathinfo($image, PATHINFO_EXTENSION);
            $imgExtArr = ['jpg', 'jpeg', 'png'];
            if (in_array($extension, $imgExtArr)) {
                $image_content .= "
                    <div class='col'>
                        <div class='card panelCard card-default h-100'>
                            <img style='height:200px;' class='card-img-top' src='{$urlAppend}template/modern/images/courses_images/$image' alt='image course'/>
                            <div class='card-body'>
                                <input id='$image' type='button' class='btn submitAdminBtnDefault w-100 chooseCourseImage mt-3' value='$langSelect'>
                            </div>
                        </div>
                    </div>
                ";
            }
        }
        $data['image_content'] = $image_content;
        $data['default_access'] = intval(get_config('default_course_access', COURSE_REGISTRATION));

        // Check if AI service is available
        $data['ai_available'] = false;
        try {
            if (class_exists('AICourseExtractionService')) {
                $data['ai_available'] = AICourseExtractionService::isEnabled();
            }
        } catch (Exception $e) {
            error_log("AI availability check failed: " . $e->getMessage());
        }

        view('modules.create_course.index', $data);

} else if ($_POST['view_type'] == "flippedclassroom") {
    $_SESSION['title'] =  $_POST['title'];
    $_SESSION['code'] = $departments ;
    $_SESSION['language'] = $language ;
    $_SESSION['formvisible'] = $_POST['formvisible'] ;
    $_SESSION['l_radio'] = $_POST['l_radio'];
    $_SESSION['cc_use'] = $_POST['cc_use'] ;
    $_SESSION['public_code'] = $public_code ;
    $_SESSION['password'] = $_POST['password'];
    $_SESSION['description'] = purify($_POST['description']);

    if (empty($title)) {
        Session::flash('message', $langFieldsMissing);
        Session::flash('alert-class', 'alert-warning');
        $validationFailed = true;
    }

    if ($validationFailed) {
        redirect_to_home_page('modules/create_course/create_course.php');
    }

    redirect_to_home_page('modules/create_course/flipped_classroom.php');

} else  { // create the course and the course database

    // validation in case it skipped JS validation
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('title'));
    $v->labels(array('title' => "$langTheField $langTitle"));
    if ($v->validate()) {
        if (count($departments) < 1 || empty($departments[0])) {
            Session::flashPost()->Messages($langEmptyAddNode)->Errors($v->errors());
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
        if (file_exists('config/config.php')) {
            if (get_config('show_always_collaboration') and get_config('show_collaboration')) {
                include "lang/$language/messages_collaboration.inc.php";
            }
        }
        if ($extra_messages) {
            include $extra_messages;
        }

        // create course directories
        if (!create_course_dirs($code)) {
            Session::flash('message', $langGeneralError);
            Session::flash('alert-class', 'alert-danger');
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
            $public_code = mb_substr($_POST['public_code'], 0, 20);
        }
        $description = purify($_POST['description']);

        $course_image = '';
        if (isset($_FILES['course_image']) && is_uploaded_file($_FILES['course_image']['tmp_name'])) {
            $file_name = $_FILES['course_image']['name'];
            validateUploadedFile($file_name, 2);
            move_uploaded_file($_FILES['course_image']['tmp_name'], "$webDir/courses/$code/image/$file_name");
            require_once 'modules/admin/extconfig/externals.php';
            $connector = AntivirusApp::getAntivirus();
            if ($connector->isEnabled()) {
                $output = $connector->check("$webDir/courses/$course_code/image/$file_name");
                if ($output->status == $output::STATUS_INFECTED) {
                    AntivirusApp::block($output->output);
                }
            }
            $course_image = $file_name;
        }

        if (!empty($_POST['choose_from_list'])) {
            $imageName = $_POST['choose_from_list'];
            $imagePath = "$webDir/template/modern/images/courses_images/$imageName";
            $newPath = "$webDir/courses/$code/image/";
            $name = pathinfo($imageName, PATHINFO_FILENAME);
            $ext = get_file_extension($imageName);
            $image_without_ext = preg_replace('/\\.[^.\\s]{3,4}$/', '', $imageName);
            $newName = $newPath . $image_without_ext . "." . $ext;
            $copied = copy($imagePath, $newName);
            if ((!$copied)) {
                echo "Error : Not Copied";
            } else {
                $course_image = $image_without_ext . "." . $ext;
            }
        }

        $typeCourse = 0;
        if (isset($view_type) && $view_type == 'sessions') {
            $typeCourse = 1;
        }
        if (get_config('show_collaboration') && get_config('show_always_collaboration')) {
            $typeCourse = 1;
        }
        if (get_config('show_collaboration') && !get_config('show_always_collaboration')) {
            if (isset($_POST['is_type_collaborative']) and $_POST['is_type_collaborative'] == 'on') {
                $typeCourse = 1;
            }
        }

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
                        is_collaborative = ?d,
                        description = ?s,
                        course_image = ?s,
                        view_units = 1",
            $code, $language, $title, $_POST['formvisible'],
            $course_license, $_POST['prof_names'], $public_code, $doc_quota * 1024 * 1024,
            $video_quota * 1024 * 1024, $group_quota * 1024 * 1024,
            $dropbox_quota * 1024 * 1024, $password, 0, $view_type, $typeCourse, $description, $course_image);
        $new_course_id = $result->lastInsertID;
        if (!$new_course_id) {
            Session::flash('message', $langGeneralError);
            Session::flash('alert-class', 'alert-danger');
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
                                        document_timestamp = " . DBHelper::timeAfter(),
            $new_course_id, $uid);

        // Process AI-generated syllabus sections if available
        if (!empty($_POST['ai_syllabus_sections'])) {
            $syllabus_sections = json_decode($_POST['ai_syllabus_sections'], true);
            if ($syllabus_sections && is_array($syllabus_sections)) {
                try {
                    saveSyllabusSections($new_course_id, $syllabus_sections, $language);
                    error_log("AI Syllabus sections saved for course ID: $new_course_id");
                } catch (Exception $e) {
                    error_log("Failed to save AI syllabus sections for course $new_course_id: " . $e->getMessage());
                }
            }
        }

        $course->refresh($new_course_id, $departments);

        // create courses/<CODE>/index.php
        course_index($code);
        // add a default forum category
        Database::get()->query("INSERT INTO forum_category
                            SET cat_title = ?s,
                            course_id = ?d", $langForumDefaultCat, $new_course_id);

        // set course option faculty_users_registration (if checked)
        if (isset($_POST['faculty_users_registration'])) {
            setting_set(SETTING_FACULTY_USERS_REGISTRATION, 1, $new_course_id);
        }

        $_SESSION['courses'][$code] = USER_TEACHER;

        $data['action_bar'] = action_bar(array(
            array('title' => $langEnter,
                'url' => $urlAppend . "courses/$code/",
                'icon' => 'fa-arrow-right',
                'level' => 'primary',
                'button-class' => 'btn-success')));

        // logging
        Log::record(0, 0, LOG_CREATE_COURSE, array('id' => $new_course_id,
            'code' => $code,
            'title' => $title,
            'language' => $language,
            'visible' => $_POST['formvisible']));
        $data['title'] = $title;
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page('modules/create_course/create_course.php');
    }
    view('modules.create_course.create_course', $data);
} // end of submit
