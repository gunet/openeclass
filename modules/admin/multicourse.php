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

$require_departmentmanage_user = true;
require_once '../../include/baseTheme.php';
require_once 'include/log.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/user.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'modules/admin/hierarchy_validations.php';
require_once 'modules/create_course/functions.php';

$toolName = $langMultiCourse;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

$tool_content .= action_bar(array(
    array('title' => $langBack,
        'url' => "index.php",
        'icon' => 'fa-reply',
        'level' => 'primary-label')));

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $line = strtok($_POST['courses'], "\n");

    $departments = isset($_POST['department']) ? $_POST['department'] : array();
    // validation in case it skipped JS validation for department(s)
    if (count($departments) < 1 || empty($departments[0])) {
        Session::Messages($langEmptyAddNode);
        header("Location:" . $urlServer . "modules/admin/multicourse.php");
        exit;
    }

    $vis = intval($_POST['formvisible']);
    while ($line !== false) {
        $line = canonicalize_whitespace($line);
        if (!empty($line)) {
            $info = explode('|', $line);
            $title = $info[0];
            $prof_uid = null;
            $prof_not_found = false;
            if (isset($info[1])) {
                $prof_info = trim($info[1]);
                $prof_uid = find_prof(trim($info[1]));
                if ($prof_info and ! $prof_uid) {
                    $prof_not_found = true;
                }
            }
            if ($prof_uid) {
                $prof_name = uid_to_name($prof_uid);
            } else {
                $prof_name = '';
            }
            list($code, $cid) = create_course('', $_POST['lang'], $title, '', $departments, $vis, $prof_name, $_POST['password']);
            if ($cid) {
                if ($prof_uid) {
                    Database::get()->query("INSERT INTO course_user
                                SET course_id = $cid,
                                    user_id = $prof_uid,
                                    status = 1,
                                    tutor = 1,
                                    reg_date = " . DBHelper::timeAfter() . " ,
                                    document_timestamp = " . DBHelper::timeAfter() . "");
                }
                Database::get()->query("INSERT INTO group_properties SET
                            course_id = $cid,
                            self_registration = 1,
                            multiple_registration = 0,
                            forum = 1,
                            private_forum = 0,
                            documents = 1,
                            wiki = 0,
                            agenda = 0");
                create_modules($cid);
            }
            if ($code) {
                course_index($code);
            }
            $class = $prof_not_found ? 'alert alert-warning' : 'alert alert-success';
            $tool_content .= "<div class='$class'><b>" . q($title) . '</b>: ' . q($langMultiCourseCreated);
            if ($prof_uid) {
                $tool_content .= '<br>' . q($langTeacher) . ': <b>' . q($prof_name) . '</b>';
            } elseif ($prof_not_found) {
                $tool_content .= '<br>' . q($langTeacher) . ': <b>' .
                        q($prof_info) . '</b>: ' . q($langNoUsersFound2);
            }
            $tool_content .= '</div>';
        }
        $line = strtok("\n");
    }
} else {
    $tree = new hierarchy();
    $course = new course();
    $user = new user();

    load_js('jstree3');

    $tool_content .= "<div class='alert alert-info'>$langMultiCourseInfo</div>
        <div class='form-wrapper'>
        <form role='form' class='form-horizontal' method='post' action='" . $_SERVER['SCRIPT_NAME'] . "' onsubmit=\"return validateNodePickerForm();\">
        <fieldset>
        <div class='form-group'>
            <label for='title' class='col-sm-3 control-label'>$langMultiCourseTitles:</label>
            <div class='col-sm-9'>" . text_area('courses', 20, 80, '') . "</div>
        </div>
	<div class='form-group'>
            <label for='title' class='col-sm-3 control-label'>$langFaculty:</label>	  
            <div class='col-sm-9'>";
        list($js, $html) = $tree->buildCourseNodePicker(array('allowables' => $user->getDepartmentIds($uid)));
        $head_content .= $js;
        $tool_content .= $html;
    $tool_content .= "</div></div>";

    $tool_content .= "<div class='form-group'><label class='col-sm-offset-4 col-sm-8'>$langConfidentiality</label></div>
        <div class='form-group'>
            <label for='password' class='col-sm-3 control-label'>$langOptPassword</label>
            <div class='col-sm-9'>
                <input id='coursepassword' class='form-control' type='text' name='password' id='password' autocomplete='off' />
            </div>
        </div>
        <div class='form-group'>
        <label for='Public' class='col-sm-3 control-label'>$langOpenCourse</label>
            <div class='col-sm-9 radio'><label><input id='courseopen' type='radio' name='formvisible' value='2' checked> $langPublic</label></div>
            </div>
        <div class='form-group'>
            <label for='PrivateOpen' class='col-sm-3 control-label'>$langRegCourse</label>	
            <div class='col-sm-9 radio'><label><input id='coursewithregistration' type='radio' name='formvisible' value='1'> $langPrivOpen</label></div>
        </div>
        <div class='form-group'>
            <label for='PrivateClosed' class='col-sm-3 control-label'>$langClosedCourse</label>
            <div class='col-sm-9 radio'><label><input id='courseclose' type='radio' name='formvisible' value='0'> $langClosedCourseShort</label></div>
       </div>
        <div class='form-group'>
             <label for='Inactive' class='col-sm-3 control-label'>$langInactiveCourse</label>
             <div class='col-sm-9 radio'><label><input id='courseinactive' type='radio' name='formvisible' value='3'> $langCourseInactiveShort</label></div>
         </div>
         <div class='form-group'>
          <label for='language' class='col-sm-3 control-label'>$langLanguage:</label>	  
           <div class='col-sm-9'>" . lang_select_options('lang') . "</div>
         </div>
         <div class='form-group'>
            <div class='col-sm-10 col-sm-offset-2'>
                <input class='btn btn-primary' type='submit' name='submit' value='" . q($langSubmit) . "'>
                <a href='index.php' class='btn btn-default'>$langCancel</a>    
            </div>
        </div>
        </fieldset>
        ". generate_csrf_token_form_field() ."
        </form>
        </div>";
}

draw($tool_content, 3, null, $head_content);

/**
 * @brief helper function
 * @param type $sql
 * @param type $terms
 * @return boolean
 */
function prof_query($sql, $terms) {
    $result = Database::get()->querySingle("SELECT id FROM user WHERE status = 1 AND ( $sql )", $terms);
    if ($result) {
        return $result->id;
    } else {
        return false;
    }
}

/**
 * @brief find a professor by name ("Name surname") or username
 * @param type $uname
 * @return boolean
 */
function find_prof($uname) {
    if (($uid = prof_query('username = ?s', array($uname)))) {
        return $uid;
    } else {
        $names = explode(' ', $uname);
        if (count($names) == 2 and
                $uid = prof_query('(surname = ?s AND givenname = ?s) OR (givenname = ?s AND surname = ?s)', array($names[0], $names[1], $names[0], $names[1]))) {
            return $uid;
        }
    }
    return false;
}
