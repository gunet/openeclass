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

$require_current_course = FALSE;
require_once '../../include/baseTheme.php';
require_once 'include/course_settings.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/course.class.php';
require_once 'include/lib/user.class.php';
require_once 'indexer.class.php';
require_once 'courseindexer.class.php';

$pageName = $langSearch;
$courses_list = array();
$tree = new Hierarchy();
$c = new Course();
$user = new User();

// exit if no POST data
if (!register_posted_variables(array('search_terms' => false,
            'search_terms_title' => false,
            'search_terms_keywords' => false,
            'search_terms_instructor' => false,
            'search_terms_coursecode' => false,
            'search_terms_description' => false), 'any')) {
    view('modules.search.index');
    exit();
}

$idx = new Indexer();
if (!$idx->getIndex()) {
    view('modules.search.index');
    exit();
}

$hits = $idx->multiSearchRaw(CourseIndexer::buildQueries($_POST));
// exit if not results
if (count($hits) <= 0) {
    Session::flash('message', $langNoResult);
    Session::flash('alert-class', 'alert-warning');
    redirect_to_home_page('modules/search/search.php');
}

// Additional Access Rights
$anonymous = !((isset($uid) && $uid));
$cnthits = count($hits);
foreach ($hits as $hit) {
    if ($hit->visible == 3 && $uid != 1) {
        $cnthits--;
    }
}
$subscribed = array();
if ($uid > 1) {
    $res = Database::get()->queryArray("SELECT course.id
                           FROM course
                           JOIN course_user ON course.id = course_user.course_id
                            AND course_user.user_id = ?d", $uid);

    foreach ($res as $row) {
        $subscribed[] = $row->id;
    }
}

// construct courses array
$hitIds = array();
foreach ($hits as $hit) {
    $hitIds[] = intval($hit->pkid);
}
$inIds = implode(",", $hitIds);
$courses = Database::get()->queryArray("select c.*, cd.department "
        . " from course c left "
        . " join (select course, max(department) as department from course_department group by course) cd on (c.id = cd.course) "
        . " where c.id in (" . $inIds . ") order by field(id," . $inIds . ")");
$data['count_courses'] = $count_courses = count($courses);

$data['action_bar'] = action_bar(array(
        array('title' => $langAdvancedSearch,
              'url' => "search.php",
              'icon' => 'fa-search',
              'level' => 'primary-label',
              'button-class' => 'btn-success')));

$search_result_content = '';

foreach ($courses as $course) {
    $courseHref = "../../courses/" . q($course->code) . "/";
    $courseUrl = "<span id='cid" . $course->id . "'><a href='$courseHref'>" . q($course->title) . "</a></span> (" . q($course->public_code) . ")";
    $skipincourse = false;
    $courses_list[$course->id] = array($course->code, $course->visible);

    // anonymous see only title for reg/closed courses
    if (($course->visible == COURSE_CLOSED || $course->visible == COURSE_REGISTRATION) && $anonymous) {
        $courseUrl = "<span id='cid" . $course->id . "'>" . q($course->title) . "</span> (" . q($course->public_code) . ")";
    }

    // closed courses url displays contact form for logged-in users
    if ($course->visible == COURSE_CLOSED && $uid > 1 && !in_array($course->id, $subscribed)) {
        $courseUrl = "<span id='cid" . $course->id . "'>" . q($course->title) . "</span> (" . q($course->public_code) . ")";
        $disable_course_user_requests = setting_get(SETTING_COURSE_USER_REQUESTS_DISABLE, $course->id);
        if (!$disable_course_user_requests) {
            $courseUrl .= "<br/><small><em><a class='text-decoration-underline' href='../contact/index.php?course_id=" . intval($course->id) . "'>$langLabelCourseUserRequest</a></em></small>";
        }
        $skipincourse = true;
    }

    // reg courses url displays just title and subscription url for logged in non-subscribed users
    if ($course->visible == COURSE_REGISTRATION && $uid > 1 && !in_array($course->id, $subscribed)) {
        $courseUrl = "<span id='cid" . $course->id . "'>" . q($course->title) . "</span> (" . q($course->public_code) . ")";
        $skipincourse = true;
    }

    // logged-in users have extended search options
    if (!$anonymous && !$skipincourse && isset($_POST['search_terms'])) {
        $courseUrl .= "<br/><small><em><a href='$courseHref?from_search=" . urlencode($_POST['search_terms']) . "'>$langSearchInCourse</a></em></small>";
    }

    //  inactive courses are hidden from anyone except admin
    if ($course->visible == COURSE_INACTIVE && $uid != 1) {
        continue;
    }
    // courses with password
    $requirepassword = '';
    $search_result_content .= "<tr>";
    if ($uid > 0) {
        if (in_array($course->id, $subscribed)) {
            $search_result_content .= "<td align='center'><i class='fa-solid fa-check' title='$langAlreadySubscribe' alt='$langAlreadySubscribe' ></i></td>";
        } else {
            if (!empty($course->password) && ($course->visible == COURSE_REGISTRATION || $course->visible == COURSE_OPEN)) {
                $requirepassword = "<br />$m[code]: <input type='password' name='pass" . $course->id . "' autocomplete='off' />";
            }

            $disabled = (!is_enabled_course_registration($uid) or $course->visible == COURSE_CLOSED) ? 'disabled' : '';
            $vis_class = ($course->visible == COURSE_CLOSED) ? 'class="reg_closed"' : '';
            $search_result_content .= "<td align='center'><label class='label-container' aria-label='$langSelect'><input type='checkbox' name='selectCourse[]' value='" . $course->id . "' $disabled $vis_class /><span class='checkmark'></span></label>"
                    . "<input type='hidden' name='changeCourse[]' value='" . $course->id . "' /></td>";
        }
    }
    // courses with prerequisites
    $coursePrerequisites = "";
    $prereqsCnt = 0;
    $result = Database::get()->queryArray("SELECT c.*
                                 FROM course_prerequisite cp 
                                 JOIN course c on (c.id = cp.prerequisite_course) 
                                 WHERE cp.course_id = ?d 
                                 ORDER BY c.title", $course->id);
    foreach ($result as $row) {
        $prereqTitle = q($row->title . " (" . $row->public_code . ")");
        if ($prereqsCnt > 0) {
            $coursePrerequisites .= ", ";
        }
        $coursePrerequisites .= $prereqTitle;
        $prereqsCnt++;
    }
    if ($prereqsCnt > 0) {
        $coursePrerequisites = "<br/><small class='text-muted'>". $GLOBALS['langCoursePrerequisites'] . ": " . $coursePrerequisites . "</small>";
    }

    $departments = $c->getDepartmentIds($course->id);
    $i = 1;
    $dep = '';
    foreach ($departments as $department) {
        $br = ($i < count($departments)) ? '<br/>' : '';
        $dep .= $tree->getFullPath($department) . $br;
        $i++;
    }

    $course_faculty = "<div class='text-muted'>$dep</div";
    $search_result_content .= "<td>" . $courseUrl . $course_faculty . $requirepassword . $coursePrerequisites . "</td>
                      <td>" . q($course->prof_names) . "</td>
                      <td>" . q($course->keywords) . "</td>
                      <td>";
    $search_result_content .= course_access_icon($course->visible);
    $search_result_content .= "</td></tr>";
}

$data['courses_list'] = json_encode($courses_list);
$data['search_result_content'] = $search_result_content;

load_js('tools.js');

view('modules.search.results', $data);
