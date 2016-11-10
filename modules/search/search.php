<?php

/* ========================================================================
 * Open eClass 3.5
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

$require_current_course = FALSE;
require_once '../../include/baseTheme.php';
require_once 'indexer.class.php';
require_once 'courseindexer.class.php';
$pageName = $langSearch;
$courses_list = array();

// exit if search is disabled
if (!get_config('enable_search')) {
    $tool_content .= action_bar(array(
                                array('title' => $langBack,
                                      'url' => $urlServer,
                                      'icon' => 'fa-reply',
                                      'level' => 'primary-label',
                                      'button-class' => 'btn-default')
                            ),false);
    $tool_content .= "<div class='alert alert-info'>$langSearchDisabled</div>";
    draw($tool_content, 0);
    exit();
}

// exit if no POST data
if (!register_posted_variables(array('search_terms' => false,
            'search_terms_title' => false,
            'search_terms_keywords' => false,
            'search_terms_instructor' => false,
            'search_terms_coursecode' => false,
            'search_terms_description' => false), 'any')) {
    $tool_content .= CourseIndexer::getDetailedSearchForm();
    draw($tool_content, 0);
    exit();
}

// search in the index
$idx = new Indexer();
$hits = $idx->multiSearchRaw(CourseIndexer::buildQueries($_POST));

// exit if not results
if (count($hits) <= 0) {
    Session::Messages($langNoResult);
    redirect_to_home_page('modules/search/search.php');
}

// Additional Access Rights
$anonymous = (isset($uid) && $uid) ? false : true;
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

//////// PRINT RESULTS ////////
$tool_content .= action_bar(array(
                    array('title' => $langAdvancedSearch,
                          'url' => "search.php",
                          'icon' => 'fa-search',
                          'level' => 'primary-label',
                          'button-class' => 'btn-success',)));

$tool_content .= "
    <div class='alert alert-info'>$langDoSearch";
if (isset($_POST['search_terms'])) {
    $search_terms = q(canonicalize_whitespace($_POST['search_terms']));
    $tool_content .= ":&nbsp;<label> '$search_terms'</label>";
}
$tool_content .= "<br><small>" . count($courses) . " $langResults2</small></div>
    <table class='table-default'>
    <tr>";
if ($uid > 0) {
    $tool_content .= "<th width='50' align='center'>$langRegistration</th>";
}
$tool_content .= "<th class='text-left'>" . $langCourse . " ($langCode)</th>
      <th class='text-left'>$langTeacher</th>
      <th class='text-left'>$langKeywords</th>
      <th class='text-left'>$langType</th>
    </tr>";


foreach ($courses as $course) {
    $courseHref = "../../courses/" . q($course->code) . "/";
    $courseUrl = "<span id='cid" . $course->id . "'><a href='$courseHref'>" . q($course->title) . "</a></span> (" . q($course->public_code) . ")";
    $skipincourse = false;
    $courses_list[$course->id] = array($course->code, $course->visible);

    // anonymous see only title for reg/closed courses
    if (($course->visible == COURSE_CLOSED || $course->visible == COURSE_REGISTRATION) && $anonymous) {
        $courseUrl = "<span id='cid" . $course->id . "'>" . q($course->title) . "</span> (" . q($course->public_code) . ")";
    }

    // closed courses url displays contact form for logged in users
    if ($course->visible == COURSE_CLOSED && $uid > 1 && !in_array($course->id, $subscribed)) {
        $courseUrl = "<span id='cid" . $course->id . "'>" . q($course->title) . "</span> (" . q($course->public_code) . ")";
        $courseUrl .= "<br/><small><em><a href='../contact/index.php?course_id=" . intval($course->id) . "'>$langLabelCourseUserRequest</a></em></small>";
        $skipincourse = true;
    }

    // reg courses url displays just title and subscription url for logged in non-subscribed users
    if ($course->visible == COURSE_REGISTRATION && $uid > 1 && !in_array($course->id, $subscribed)) {
        $courseUrl = "<span id='cid" . $course->id . "'>" . q($course->title) . "</span> (" . q($course->public_code) . ")";
        $skipincourse = true;
    }

    // logged in users have extended search options
    if (!$anonymous && !$skipincourse && isset($_POST['search_terms'])) {
        $courseUrl .= "<br/><small><em><a href='$courseHref?from_search=" . urlencode($_POST['search_terms']) . "'>$langSearchInCourse</a></em></small>";
    }

    //  inactive courses are hidden from anyone except admin
    if ($course->visible == COURSE_INACTIVE && $uid != 1) {
        continue;
    }

    $requirepassword = '';
    $tool_content .= "<tr>";
    if ($uid > 0) {
        if (in_array($course->id, $subscribed)) {
            $tool_content .= "<td align='center'><img src='$themeimg/tick.png' title='$langAlreadySubscribe' alt='$langAlreadySubscribe'/></td>";
        } else {
            if (!empty($course->password) && ($course->visible == COURSE_REGISTRATION || $course->visible == COURSE_OPEN)) {
                $requirepassword = "<br />$m[code]: <input type='password' name='pass" . $course->id . "' autocomplete='off' />";
            }

            $disabled = ($course->visible == COURSE_CLOSED) ? 'disabled' : '';
            $vis_class = ($course->visible == COURSE_CLOSED) ? 'class="reg_closed"' : '';
            $tool_content .= "<td align='center'><input type='checkbox' name='selectCourse[]' value='" . $course->id . "' $disabled $vis_class />"
                    . "<input type='hidden' name='changeCourse[]' value='" . $course->id . "' /></td>";
        }
    }
    $tool_content .= "<td>" . $courseUrl . $requirepassword . "</td>
                      <td>" . q($course->prof_names) . "</td>
                      <td>" . q($course->keywords) . "</td>
                      <td>";
    foreach ($course_access_icons as $visible => $image) {
        if ($visible == $course->visible) {
            $tool_content .= $image;
        }
    }
    $tool_content .= "</td></tr>";
}
$tool_content .= "</table>";

$tool_content .= "<script type='text/javascript'>$(course_list_init);
var themeimg = '" . js_escape($themeimg) . "';
var urlAppend = '".js_escape($urlAppend)."';
var lang = {
        unCourse: '" . js_escape($langUnCourse) . "',
        cancel: '" . js_escape($langCancel) . "',
        close: '" . js_escape($langClose) . "',
        unregCourse: '" . js_escape($langUnregCourse) . "',
        reregisterImpossible: '" . js_escape("$langConfirmUnregCours $m[unsub]") . "',
        invalidCode: '" . js_escape($langInvalidCode) . "',
};
var courses = ".(json_encode($courses_list)).";
</script>";

load_js('tools.js');

draw($tool_content, 0, null, $head_content);
