<?php
/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2026, Greek Universities Network - GUnet
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

// Detect Cadmos course export
$cadmos = false;
$content_type = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' and stripos($content_type, 'application/json') !== false) {
    $body = @json_decode(file_get_contents('php://input'), true);
    if (is_array($body) and isset($body['username']) and isset($body['token']) and isset($body['source'])) {
        $cadmos = true;
    }
}
if (!$cadmos) {
    $require_login = true;
}

require_once '../../include/baseTheme.php';
require_once 'include/log.class.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/course_settings.php';
require_once 'modules/create_course/functions.php';

// If not called from Cadmos, the user should have course creation rights
if (!$cadmos and !($session->status === USER_TEACHER or $is_departmentmanage_user)) {
    redirect_to_home_page();
}

if ($cadmos) {
    header('Content-Type: application/json');
    $coby_secret = get_config('ext_coby_secret');
    if (empty($coby_secret)) {
        http_response_code(501);
        echo json_encode(['error' => 'Coby shared secret is not configured in Open eClass']);
        exit;
    }

    $username = trim($body['username'] ?? '');
    $email = trim($body['email'] ?? '');
    $timestamp = strval($body['timestamp'] ?? '');
    $token = trim($body['token'] ?? '');

    // Allowed clock skew: 300 seconds (5 minutes)
    if (abs(time() - intval($timestamp)) > 300) {
        http_response_code(401);
        echo json_encode(['error' => 'Timestamp expired or clock skew too large']);
        exit;
    }

    // Verify HMAC-SHA256 signature
    $sign_payload = "{$username}|{$email}|{$timestamp}";
    $expected_token = hash_hmac('sha256', $sign_payload, $coby_secret);
    if (!hash_equals($expected_token, $token)) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid token signature']);
        exit;
    }

    // Lookup user in eClass
    $user = Database::get()->querySingle("SELECT id, status FROM user WHERE username = ?s", $username);
    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found in Open eClass']);
        exit;
    }

    // Check course creation permissions (Teacher or Department Manager)
    $is_dep_mgr = Database::get()->querySingle("SELECT user_id FROM hierarchy_user WHERE user_id = ?d LIMIT 1", $user->id);
    if ($user->status != USER_TEACHER && !$is_dep_mgr) {
        http_response_code(403);
        echo json_encode(['error' => 'User does not have course creation privileges']);
        exit;
    }

    $source_str = is_string($body['source']) ? $body['source'] : json_encode($body['source'], JSON_UNESCAPED_UNICODE);

    $q = Database::get()->query("INSERT INTO cadmos_course SET user_id = ?d, source = ?s, created = NOW()", $user->id, $source_str);
    if ($q) {
        $cadmos_id = $q->lastInsertID;
        $redirect_url = $urlServer . "modules/create_course/cadmos.php?id=" . $cadmos_id;
        echo json_encode([
            'success' => true,
            'id' => $cadmos_id,
            'message' => 'Cadmos course export received successfully',
            'redirect_url' => $redirect_url
        ]);
        exit;
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Database error while saving Cadmos course']);
        exit;
    }
}

// User-facing flow for creating course from Cadmos design
$tree = new Hierarchy();
$course = new Course();
$user = new User();

load_js('bootstrap-datepicker');

$toolName = $langPortfolio;
$pageName = $langCourseCreate;

$selected_cadmos_id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['cadmos_id']) ? intval($_POST['cadmos_id']) : 0);

$pending_cadmos_courses = Database::get()->queryArray("SELECT id, source, created FROM cadmos_course WHERE user_id = ?d AND course_id IS NULL ORDER BY id DESC", $uid);

$current_cadmos_course = null;
if ($selected_cadmos_id > 0) {
    $current_cadmos_course = Database::get()->querySingle("SELECT id, source, created FROM cadmos_course WHERE id = ?d AND user_id = ?d AND course_id IS NULL", $selected_cadmos_id, $uid);
}

if (!$current_cadmos_course && count($pending_cadmos_courses) > 0) {
    $current_cadmos_course = $pending_cadmos_courses[0];
    $selected_cadmos_id = $current_cadmos_course->id;
}

if (!$current_cadmos_course) {
    Session::flash('message', $langCadmosNoPendingCourses);
    Session::flash('alert-class', 'alert-info');
    redirect_to_home_page('modules/create_course/create_course.php');
}

$cadmos_data = json_decode($current_cadmos_course->source);
$cadmos_title = $cadmos_data->data->LessonInfo->StrategyName ?? '';
$cadmos_description = $cadmos_data->data->LessonInfo->Description ?? '';

register_posted_variables(array('title' => true, 'password' => true));
if (empty($prof_names)) {
    $data['prof_names'] = $prof_names = "$_SESSION[givenname] $_SESSION[surname]";
}

// departments and validation
$allow_only_defaults = get_config('restrict_teacher_owndep') && !$is_admin;
$allowables = array();
if ($allow_only_defaults) {
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
$data['title'] = Session::has('title') ? Session::get('title') : $cadmos_title;
$data['public_code'] = Session::has('public_code') ? Session::get('public_code') : '';
$description = Session::has('description') ? Session::get('description') : $cadmos_description;
$data['prof_names'] = $prof_names = Session::has('prof_names') ? Session::get('prof_names') : "$_SESSION[givenname] $_SESSION[surname]";
$data['cadmos_id'] = $current_cadmos_course->id;
$data['is_cadmos'] = true;
$data['pending_cadmos_courses'] = $pending_cadmos_courses;

// display form
if (!isset($_POST['create_course'])) {
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
    $data['lang_select_options'] = lang_select_options('localize', "id='lang_selected'");
    $data['rich_text_editor'] = rich_text_editor('description', 4, 20, $description, options: array('id' => 'description'));
    $data['selection_license'] = selection($cc_license, 'cc_use', "", 'class="form-select" id="course_license_id"');
    $data['cancel_link'] = "{$urlServer}main/portfolio.php";
    $data['is_coby_enabled'] = false;
    $data['courseStartDate'] = date('d-m-Y');
    $data['course_enableStartDate'] = 'checked';
    $data['courseEndDate'] = $data['course_enableEndDate'] = '';
    $data['courseRegStartDate'] = $data['course_enableRegStartDate'] = '';
    $data['courseRegEndDate'] = $data['course_enableRegEndDate'] = '';

    generate_csrf_token_form_field();

    // course image
    $image_content = '';
    $dir_images = scandir($webDir . '/template/modern/images/courses_images');
    foreach ($dir_images as $image) {
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

    $data['enable_activity'] = Database::get()->querySingle('SELECT id FROM activity_content LIMIT 1');

    view('modules.create_course.index', $data);

} else { // create course and database entries
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('title'));
    $v->labels(array('title' => "$langTheField $langTitle"));
    if ($v->validate()) {
        if (count($departments) < 1 || empty($departments[0])) {
            Session::flashPost()->Messages($langEmptyAddNode)->Errors($v->errors());
            redirect_to_home_page('modules/create_course/cadmos.php?id=' . $current_cadmos_course->id);
        }
        // create new course code: uppercase, no spaces allowed
        $code = strtoupper(new_code($departments[0]));
        $code = str_replace(' ', '', $code);
        // create course directories
        if (!create_course_dirs($code)) {
            Session::flash('message', $langGeneralError);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page('modules/create_course/cadmos.php?id=' . $current_cadmos_course->id);
        }

        // get default quota values
        $doc_quota = get_config('doc_quota');
        $group_quota = get_config('group_quota');
        $video_quota = get_config('video_quota');
        $dropbox_quota = get_config('dropbox_quota');

        $course_license = 0;
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

        $view_type = 'units';
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
                $output = $connector->check("$webDir/courses/$code/image/$file_name");
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
            if ($copied) {
                $course_image = $image_without_ext . "." . $ext;
            }
        }

        $typeCourse = 0;
        if (get_config('show_collaboration') && get_config('show_always_collaboration')) {
            $typeCourse = 1;
        }
        if (get_config('show_collaboration') && !get_config('show_always_collaboration')) {
            if (isset($_POST['is_type_collaborative']) and $_POST['is_type_collaborative'] == 'on') {
                $typeCourse = 1;
            }
        }

        if (isset($_POST['course_enableStartDate']) && $_POST['courseStartDate'] !== '') {
            $courseStartDate = DateTime::createFromFormat('d-m-Y', $_POST['courseStartDate']);
            $start_date = $courseStartDate->format('Y-m-d');
        } else {
            $start_date = date("Y-m-d");
        }

        if (isset($_POST['course_enableEndDate']) && $_POST['courseEndDate'] !== '') {
            $courseEndDate = DateTime::createFromFormat('d-m-Y', $_POST['courseEndDate']);
            $end_date = $courseEndDate->format('Y-m-d');
        } else {
            $end_date = null;
        }

        if (isset($_POST['course_enableRegStartDate']) && $_POST['courseRegStartDate'] !== '') {
            $courseRegStartDate = DateTime::createFromFormat('d-m-Y', $_POST['courseRegStartDate']);
            $reg_start_date = $courseRegStartDate->format('Y-m-d');
        } else {
            $reg_start_date = null;
        }

        if (isset($_POST['course_enableRegEndDate']) && $_POST['courseRegEndDate'] !== '') {
            $courseRegEndDate = DateTime::createFromFormat('d-m-Y', $_POST['courseRegEndDate']);
            $reg_end_date = $courseRegEndDate->format('Y-m-d');
        } else {
            $reg_end_date = null;
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
                        start_date = ?s,
                        end_date = ?s,
                        reg_start_date = ?s,
                        reg_end_date = ?s,
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
            $dropbox_quota * 1024 * 1024, $password, 0, $view_type,
            $start_date, $end_date, $reg_start_date, $reg_end_date,
            $typeCourse, $description, $course_image);
        $new_course_id = $result->lastInsertID;
        if (!$new_course_id) {
            Session::flash('message', $langGeneralError);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page('modules/create_course/cadmos.php?id=' . $current_cadmos_course->id);
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

        $course->refresh($new_course_id, $departments);

        // create courses/<CODE>/index.php
        course_index($code);

        // add a default forum category
        Database::get()->query("INSERT INTO forum_category
                            SET cat_title = ?s,
                            course_id = ?d", $langForumDefaultCat, $new_course_id);

        // Import Cadmos units, activities and learning goals
        import_cadmos_data($new_course_id, $code, $current_cadmos_course->source);

        // Mark this Cadmos course as imported to this course
        Database::get()->query("UPDATE cadmos_course SET course_id = ?d WHERE id = ?d", $new_course_id, $current_cadmos_course->id);

        // set course option faculty_users_registration (if checked)
        if (isset($_POST['faculty_users_registration'])) {
            setting_set(SETTING_FACULTY_USERS_REGISTRATION, 1, $new_course_id);
        }

        $_SESSION['courses'][$code] = USER_TEACHER;

        // logging
        Log::record(0, 0, LOG_CREATE_COURSE, array('id' => $new_course_id,
            'code' => $code,
            'title' => $title,
            'language' => $language,
            'visible' => $_POST['formvisible']));
        $data['title'] = $title;
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page('modules/create_course/cadmos.php?id=' . $current_cadmos_course->id);
    }
    Session::flash('message', $langCourseCreated . "<div class='smaller'>$langEnterMetadata</div>");
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page("courses/" . $code . "/index.php");
}
