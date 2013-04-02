<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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
$nameTools = $langSearch;

// exit if search is disabled
if (!get_config('enable_search')) {
    $tool_content .= "<div class='info'>$langSearchDisabled</div>";
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
if (isset($uid) and $uid) {
    $hits2 = $idx->searchRaw(CourseIndexer::buildQuery($_POST, false)); // courses with visible 0 or 3
    
    if ($uid == 0)
        $hits = array_merge($hits1, $hits2); // admin has access to all
    else {
        $res = db_query("SELECT course.id 
                           FROM course 
                           JOIN course_user ON course.id = course_user.course_id 
                            AND course_user.user_id = ". $uid);
        $subscribed = array();
        while ($row = mysql_fetch_assoc($res))
            $subscribed[] = $row['id'];
        
        $hits3 = array();
        foreach($hits2 as $hit2) {
            if (in_array($hit2->pkid, $subscribed))
                $hits3[] = $hit2;
        }
        
        $hits = array_merge($hits1, $hits3); // eponymous user can also search for his subscribed courses
    }
} else
    $hits = $hits1;                          // anonymous can only access with visible 1 or 2


// exit if not results
if (count($hits) <= 0) {
    $tool_content .= "<p class='alert1'>$langNoResult</p>";
    draw($tool_content, 0);
    exit();
}


//////// PRINT RESULTS ////////
$tool_content .= "
    <div id='operations_container'>
      <ul id='opslist'>
         <li><a href='search.php'>$langNewSearch</a></li>
      </ul>
    </div>";

$tool_content .= "
    <p>$langDoSearch:&nbsp;<b>" .  count($hits) . " $langResults</b></p>
    <script type='text/javascript' src='../auth/sorttable.js'></script>
    <table width='100%' class='sortable' id='t1' align='left'>
    <tr>
      <th width='1'>&nbsp;</th>
      <th><div align='left'>" . $langCourse . " ($langCode)</div></th>
      <th width='200'><div align='left'>$langTeacher</div></th>
      <th width='150'><div align='left'>$langKeywords</div></th>
    </tr>";

$k = 0;
foreach ($hits as $hit) {
    $res = db_query("SELECT code, title, public_code, prof_names, keywords FROM course WHERE id = ". intval($hit->pkid));
    $course = mysql_fetch_assoc($res);
    
    $class = ($k % 2) ? 'odd' : 'even';
    $tool_content .= "<tr class='$class'>
                      <td><img src='$themeimg/arrow.png' alt='' /></td><td>
                      <a href='../../courses/" . q($course['code']) . "/'>" . q($course['title']) ."
                      </a> (" . q($course['public_code']) . ")</td>
                      <td>" . q($course['prof_names']) . "</td>
                      <td>" . q($course['keywords']) . "</td></tr>";
    $k++;
}
$tool_content .= "</table>";
draw($tool_content, 0);
