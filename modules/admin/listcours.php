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


/*===========================================================================
	listcours.php
	@last update: 11-07-2006 by Pitsiougas Vagelis
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Pitsiougas Vagelis <vagpits@uom.gr>
==============================================================================
        @Description: List courses

 	This script allows the administrator list all available courses, or some
 	of them if a search is obmitted

 	The user can : - See basic information about courses
 	               - Go to users of a course
 	               - Go to delete course page
 	               - Go to edit course page
                 - Return to main administrator page
                 - A paging navigation has been added

 	@Comments: The script is organised in three sections.

  1) Select courses from database (all or some of them based on search)
  2) Display basic information about selected courses
  3) Display all on an HTML page

==============================================================================*/

$require_power_user = true;
require_once '../../include/baseTheme.php';
require_once 'admin.inc.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/course.class.php';

$tree = new hierarchy();
$course = new course();

$nameTools = $langListCours;
$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);

$caption = '';
$searchurl = '';

// Manage list limits
$countcourses = mysql_fetch_array(db_query("SELECT COUNT(*) AS cnt FROM course"));
$fulllistsize = $countcourses['cnt'];

define('COURSES_PER_PAGE', 15);

$limit = isset($_GET['limit'])? intval($_GET['limit']): 0;

// Display Actions Toolbar
$tool_content .= "
    <div id='operations_container'>
    <ul id='opslist'>
      <li><a href='searchcours.php'>$langSearchCourses</a></li>
    </ul>
    </div>";

// A search has been submitted
if (isset($_GET['search']) && $_GET['search'] == "yes") {
	$searchurl = "&search=yes";
	// Search from post form
	if (isset($_POST['search_submit'])) 	{
		$searchtitle = $_SESSION['searchtitle'] = autounquote($_POST['formsearchtitle']);
		$searchcode = $_SESSION['searchcode'] = autounquote($_POST['formsearchcode']);
		$searchtype = $_SESSION['searchtype'] = intval($_POST['formsearchtype']);
		$searchfaculte = $_SESSION['searchfaculte'] = intval($_POST['formsearchfaculte']);
	}
	// Search from session
	else {
		$searchtitle = $_SESSION['searchtitle'];
		$searchcode = $_SESSION['searchcode'];
		$searchtype = $_SESSION['searchtype'];
		$searchfaculte = $_SESSION['searchfaculte'];
	}
	// Search for courses
	$searchcours=array();
	if(!empty($searchtitle)) {
		$searchcours[] = "title LIKE " . quote('%' . $searchtitle . '%');
	}
	if(!empty($searchcode)) {
		$searchcours[] = "course.code LIKE " . quote('%' . $searchcode . '%');
	}
	if ($searchtype != "-1") {
		$searchcours[] = "visible = $searchtype";
	}
	if ($searchfaculte) {
		$searchcours[] = "hierarchy.id = $searchfaculte";
	}
	$query=join(' AND ',$searchcours);
	if (!empty($query)) {
                $sql = db_query("SELECT DISTINCT course.code, course.title, course.prof_names, course.visible, course.id
                                   FROM course, course_department, hierarchy
                                  WHERE course.id = course_department.course
                                    AND hierarchy.id = course_department.department AND $query
                               ORDER BY course.code");
		$caption .= "$langFound ".mysql_num_rows($sql)." $langCourses ";
	} else {
                $sql = db_query("SELECT DISTINCT course.code, course.title, course.prof_names, course.visible, course.id
                                   FROM course, course_department, hierarchy
                                  WHERE course.id = course_department.course
                                    AND hierarchy.id = course_department.department
                               ORDER BY course.code");
		$caption .= "$langFound ".mysql_num_rows($sql)." $langCourses ";
	}
}
// Normal list, no search, select all courses
else {
	$a = mysql_fetch_array(db_query("SELECT COUNT(*) FROM course"));
	$caption .= $langManyExist.": <b>".$a[0]." $langCourses</b>";
        $sql = db_query("SELECT code, title, prof_names, visible, id
                           FROM course
                       ORDER BY code LIMIT $limit, " . COURSES_PER_PAGE);

	if ($fulllistsize > COURSES_PER_PAGE ) {
		// Display navigation in pages
		$tool_content .= show_paging($limit, COURSES_PER_PAGE, $fulllistsize, "$_SERVER[SCRIPT_NAME]");
	}
}

$key = mysql_num_rows($sql);
if ($key == 0) {
        $tool_content .= "<p class='alert1'>$langNoCourses</p>";
} else {
        // Construct course list table
        $tool_content .= "<table class='tbl_alt' width='100%'>
        <tr>
        <td colspan='7' class='right'>
                ".$caption."
        </td>
        </tr>
        <tr>
        <th scope='col' width='1' class='odd'>&nbsp;</th>
        <th scope='col' class='odd'><div align='left'>".$langCourseCode."</div></th>
        <th scope='col' width='1' class='odd'>".$langGroupAccess."</th>
        <th scope='col' width='260' class='odd'><div align='left'>".$langFaculty."</div></th>
        <th scope='col' width='100' class='odd'>".$langActions."</th>
        </tr>";
        $k = 0;
        for ($j = 0; $j < mysql_num_rows($sql); $j++) {
                $logs = mysql_fetch_array($sql);
                if ($k%2 == 0) {
                        $tool_content .= "<tr class='even'>";
                } else {
                        $tool_content .= "<tr class='odd'>";
                }
                $tool_content .= "<td width='1'><img style='margin-top:4px;' src='$themeimg/arrow.png' title='bullet' /></td>
                <td><a href='{$urlServer}courses/".$logs['code']."/'><b>".q($logs['title'])."</b>
                        </a> (".q($logs['code']).")<br /><i>".q($logs['prof_names'])."</i>
                </td>
                <td align='center'>";
                // Define course type
                switch ($logs['visible']) {
                        case COURSE_CLOSED:
                                $tool_content .= "<img src='$themeimg/lock_closed.png' title='$langClosedCourse' />";
                                break;
                        case COURSE_REGISTRATION:
                                $tool_content .= "<img src='$themeimg/lock_registration.png' title='$langRegCourse' />";
                                break;
                        case COURSE_OPEN:
                                $tool_content .= "<img src='$themeimg/lock_open.png' title='$langOpenCourse' />";
                                break;
                        case COURSE_INACTIVE:
                                $tool_content .= "<img src='$themeimg/lock_inactive.png' title='$langCourseInactiveShort' />";
                                break;
                }
                $tool_content .= "</td><td class='smaller'>";

                $departments = $course->getDepartmentIds($logs['id']);
                $i = 1;
                foreach ($departments as $dep) {
                        $br = ($i < count($departments)) ? '<br/>' : '';
                        $tool_content .= $tree->getFullPath($dep) . $br;
                        $i++;
                }
                $tool_content .= "</td>";
                // Add links to course users, delete course and course edit
                $tool_content .= "
                <td align='center' width='40'>
                        <a href='listusers.php?c=".$logs['id']."'><img src='$themeimg/user_list.png' title='$langUsers' /></a>&nbsp;
                        <a href='../usage/displaylog.php?c=".$logs['code']."'><img src='$themeimg/user_list.png' title='$langUsersLog' /></a>&nbsp;
                        <a href='editcours.php?c=".$logs['code'].$searchurl."'><img src='$themeimg/edit.png' title='$langEdit'></a>
                        <a href='delcours.php?c=".$logs['id']."'><img src='$themeimg/delete.png' title='$langDelete'></a>
                </td>";
                $k++;
        }
        // Close table correctly
        $tool_content .= "</tr></table>";
        // If a search is started display link to search page
        if (isset($_GET['search']) && $_GET['search'] == "yes") {
                $tool_content .= "<p align='right'><a href='searchcours.php'>".$langReturnSearch."</a></p>";
        } elseif ($fulllistsize > COURSES_PER_PAGE) {
                // Display navigation in pages
                $tool_content .= show_paging($limit, COURSES_PER_PAGE, $fulllistsize, "$_SERVER[SCRIPT_NAME]");
        }
}
// Display link to index.php
$tool_content .= "<p align='right'><a href='index.php'>$langBack</a></p>";

draw($tool_content,3);
