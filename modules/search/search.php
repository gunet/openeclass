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
require_once 'indexer.class.php';
require_once 'courseindexer.class.php';
$pageName = $langSearch;

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
$hits = $idx->searchRaw(CourseIndexer::buildQuery($_POST));

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
$inIds = "(" . implode(",", $hitIds) . ")";
$courses = Database::get()->queryArray("SELECT * FROM course WHERE id in " . $inIds);
 
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
    $tool_content .= ":&nbsp;<label> '$_POST[search_terms]'</label>";
}
$tool_content .= "<br><small>" . $cnthits . " $langResults2</small></div>
    <table class='table-default'>
    <tr>      
      <th class='text-left'>" . $langCourse . " ($langCode)</th>
      <th class='text-left'>$langTeacher</th>
      <th class='text-left'>$langKeywords</th>
      <th class='text-left'>$langType</th>
    </tr>";
// use the following array for the legend icons
$icons = array(
    3 => "<img src='$themeimg/lock_inactive.png' alt='" . $langInactiveCourse . "' title='" . $langInactiveCourse . "' width='16' height='16' />",
    2 => "<img src='$themeimg/lock_open.png' alt='" . $langOpenCourse . "' title='" . $langOpenCourse . "' width='16' height='16' />",
    1 => "<img src='$themeimg/lock_registration.png' alt='" . $langRegCourse . "' title='" . $langRegCourse . "' width='16' height='16' />",
    0 => "<img src='$themeimg/lock_closed.png' alt='" . $langClosedCourse . "' title='" . $langClosedCourse . "' width='16' height='16' />"
);

foreach ($courses as $course) {    
    $courseHref = "../../courses/" . q($course->code) . "/";
    $courseUrl = "<a href='$courseHref'>" . q($course->title) . "</a> (" . q($course->public_code) . ")";
    
    // anonymous see only title for reg/closed courses
    if (($course->visible == 0 || $course->visible == 1) && $anonymous) {
        $courseUrl = q($course->title);
    }
    
    // closed courses url displays contact form for logged in users
    if ($course->visible == 0 && $uid > 1 && !in_array($course->id, $subscribed)) {
        $courseUrl = "<a href='../contact/index.php?course_id=" . intval($course->id) . "'>" . q($course->title) . "</a>";
    }
    
    // reg courses url displays just title for logged in non-subscribed users
    if ($course->visible == 1 && $uid > 1 && !in_array($course->id, $subscribed)) {
        $courseUrl = q($course->title);
    }
    
    // logged in users have extended search options
    if (!$anonymous && isset($_POST['search_terms'])) {
        $courseUrl .= "<br/><small><em><a href='$courseHref?from_search=" . urlencode($_POST['search_terms']) . "'>$langSearchInCourse</a></em></small>";
    }
    
    //  inactive courses are hidden from anyone except admin
    if ($course->visible == 3 && $uid != 1) {
        continue;
    }
    
    $tool_content .= "<tr><td>
                      $courseUrl</td>
                      <td>" . q($course->prof_names) . "</td>
                      <td>" . q($course->keywords) . "</td>
                      <td>";
    foreach ($icons as $visible => $image) {
        if ($visible == $course->visible) {
            $tool_content .= $image;
        }
    }
    $tool_content .= "</td></tr>";
}
$tool_content .= "</table>";
draw($tool_content, 0);
