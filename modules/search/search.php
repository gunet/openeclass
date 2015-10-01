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
$hits1 = $idx->searchRaw(CourseIndexer::buildQuery($_POST));            // courses with visible 1 or 2
// Additional Access Rights
$anonymous = false;
if (isset($uid) and $uid) {
    $anonymous = true;
    $hits2 = $idx->searchRaw(CourseIndexer::buildQuery($_POST, false)); // courses with visible 0 or 3

    if ($uid == 0) {
        $hits = array_merge($hits1, $hits2); // admin has access to all
    } else {        
        $res = Database::get()->queryArray("SELECT course.id 
                           FROM course 
                           JOIN course_user ON course.id = course_user.course_id 
                            AND course_user.user_id = ?d", $uid);
        $subscribed = array();
        foreach ($res as $row) {
            $subscribed[] = $row->id;
        }
        $hits3 = array();
        foreach ($hits2 as $hit2) {
            if (in_array($hit2->pkid, $subscribed)) {
                $hits3[] = $hit2;
            }
        }
        $hits = array_merge($hits1, $hits3); // eponymous user can also search for his subscribed courses
    }
} else {
    $hits = $hits1;                          // anonymous can only access with visible 1 or 2
}
 
// exit if not results
if (count($hits) <= 0) {
    Session::Messages($langNoResult);
    redirect_to_home_page('modules/search/search.php');  
}

//////// PRINT RESULTS ////////
$tool_content .= action_bar(array(
                    array('title' => $langAdvancedSearch,
                          'url' => "search.php",
                          'icon' => 'fa-search',
                          'level' => 'primary-label',
                          'button-class' => 'btn-success',)));

$tool_content .= "
    <div class='alert alert-info'>$langDoSearch:&nbsp;<label> '$_POST[search_terms]'</label><br><small>" . count($hits) . " $langResults2</small></div>
    <table class='table-default'>
    <tr>      
      <th class='text-left'>" . $langCourse . " ($langCode)</th>
      <th class='text-left'>$langTeacher</th>
      <th class='text-left'>$langKeywords</th>
    </tr>";

foreach ($hits as $hit) {    
    $course = Database::get()->querySingle("SELECT code, title, public_code, prof_names, keywords FROM course WHERE id = ?d", $hit->pkid);

    // search in-course: Commented out @ 2014-11-24 because too costly to run 11 index sub-queries for each hit result
    $urlParam = '';
    //if (isset($_POST['search_terms']) && search_in_course($_POST['search_terms'], $hit->pkid, $anonymous)) {
    //    $urlParam = '?from_search=' . urlencode($_POST['search_terms']);
    //}
    
    $tool_content .= "<tr><td>
                      <a href='../../courses/" . q($course->code) . "/" . $urlParam . "'>" . q($course->title) . "
                      </a> (" . q($course->public_code) . ")</td>
                      <td>" . q($course->prof_names) . "</td>
                      <td>" . q($course->keywords) . "</td></tr>";
}
$tool_content .= "</table>";
draw($tool_content, 0);

/**
 * @brief search in course
 * @global Indexer $idx
 * @param type $searchTerms
 * @param type $courseId
 * @param type $anonymous
 * @return boolean
 */
function search_in_course($searchTerms, $courseId, $anonymous) {
    global $idx;

    $data = array();
    $data['search_terms'] = $searchTerms;
    $data['course_id'] = $courseId;

    require_once 'announcementindexer.class.php';
    $anhits = $idx->searchRaw(AnnouncementIndexer::buildQuery($data, $anonymous));
    if (count($anhits) > 0) {
        return true;
    }

    require_once 'agendaindexer.class.php';
    $aghits = $idx->searchRaw(AgendaIndexer::buildQuery($data, $anonymous));
    if (count($aghits) > 0) {
        return true;
    }

    require_once 'documentindexer.class.php';
    $dhits = $idx->searchRaw(DocumentIndexer::buildQuery($data, $anonymous));
    if (count($dhits) > 0) {
        return true;
    }

    require_once 'exerciseindexer.class.php';
    $exhits = $idx->searchRaw(ExerciseIndexer::buildQuery($data, $anonymous));
    if (count($exhits) > 0) {
        return true;
    }

    require_once 'forumindexer.class.php';
    $fhits = $idx->searchRaw(ForumIndexer::buildQuery($data, $anonymous));
    if (count($fhits) > 0) {
        return true;
    }

    require_once 'forumtopicindexer.class.php';
    $fthits = $idx->searchRaw(ForumTopicIndexer::buildQuery($data, $anonymous));
    if (count($fthits) > 0) {
        return true;
    }

    require_once 'linkindexer.class.php';
    $lhits = $idx->searchRaw(LinkIndexer::buildQuery($data, $anonymous));
    if (count($lhits) > 0) {
        return true;
    }

    require_once 'videoindexer.class.php';
    $vhits = $idx->searchRaw(VideoIndexer::buildQuery($data, $anonymous));
    if (count($vhits) > 0) {
        return true;
    }

    require_once 'videolinkindexer.class.php';
    $vlhits = $idx->searchRaw(VideolinkIndexer::buildQuery($data, $anonymous));
    if (count($vlhits) > 0) {
        return true;
    }

    require_once 'unitindexer.class.php';
    $uhits = $idx->searchRaw(UnitIndexer::buildQuery($data, $anonymous));
    if (count($uhits) > 0) {
        return true;
    }

    require_once 'unitresourceindexer.class.php';
    $urhits = $idx->searchRaw(UnitResourceIndexer::buildQuery($data, $anonymous));
    if (count($urhits) > 0) {
        return true;
    }

    return false;
}
