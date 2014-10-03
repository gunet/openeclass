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

/**
 * @file: course_home.php
 * @brief: course home page
 */
$require_current_course = true;
$guest_allowed = true;
define('HIDE_TOOL_TITLE', 1);
define('STATIC_MODULE', 1);
require_once '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/course.class.php';
require_once 'include/action.php';
require_once 'include/course_settings.php';
require_once 'modules/sharing/sharing.php';
require_once 'modules/rating/class.rating.php';
require_once 'modules/comments/class.comment.php';
require_once 'modules/comments/class.commenting.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'modules/weeks/functions.php';
require_once 'modules/document/doc_init.php';

$tree = new Hierarchy();
$course = new Course();

$require_help = TRUE;
$helpTopic = 'course_home';
$nameTools = $langIdentity;
$main_content = $cunits_content = $bar_content = "";

add_units_navigation(TRUE);

load_js('tools.js');
load_js('slick');
ModalBoxHelper::loadModalBox();
$head_content .= "<script type='text/javascript'>$(document).ready(add_bookmark);</script>
<script type='text/javascript'>
    $(document).ready(function() {
            $('.course_description').slick({
                dots: false, slidesToShow: 4, slidesToScroll: 1, touchMove: false
            });
            $('.inline').colorbox({ inline: true, width: '50%', rel: 'info', current: '' });
    })
    </script>";

// For statistics: record login
Database::get()->query("INSERT INTO logins SET user_id = ?d, course_id = ?d, ip = '$_SERVER[REMOTE_ADDR]', date_time = " . DBHelper::timeAfter() . "", $uid, $course_id);

$action = new action();
$action->record(MODULE_ID_UNITS);

if (isset($_GET['from_search'])) { // if we come from home page search
    header("Location: {$urlServer}modules/search/search_incourse.php?all=true&search_terms=$_GET[from_search]");
}

$result = Database::get()->querySingle("SELECT keywords, visible, prof_names, public_code, course_license, finish_date, view_type
                  FROM course WHERE id = ?d", $course_id);

$keywords = q(trim($result->keywords));
$visible = $result->visible;
$professor = $result->prof_names;
$public_code = $result->public_code;
$course_license = $result->course_license;
$main_extra = $description = $addon = '';

$res = Database::get()->queryArray("SELECT cd.id, cd.title, cd.comments, cd.type, cdt.icon FROM course_description cd
    LEFT JOIN course_description_type cdt ON (cd.type = cdt.id)
    WHERE cd.course_id = ?d AND cd.visible = 1 ORDER BY cd.order", $course_id);

$main_extra .= "<div class = 'course_description' style='width: 520px;'>";
$tool_content .= "<div style='display: none'>";
foreach ($res as $row) {
    $desctype = intval($row->type) - 1;
    $descicon = (!empty($row->icon)) ? $row->icon : 'default.png';
    $element_id = (isset($titreBloc[$desctype])) ? "class='course_info' id='{$titreBloc[$desctype]}'" : 'class="course_info other"';
    $icon_url = "$themeimg/bloc/" . $descicon;
    $hidden_id = "hidden_" . $row->id;
    $tool_content .= "<div id='$hidden_id'><h1>" .
            q($row->title) . "</h1>" .
            standard_text_escape($row->comments) . "</div>\n";
    $main_extra .= "<div $element_id>" .
            "<a href='#$hidden_id' class='inline' style='font-weight: bold; width: 100px; display: block; text-align: center; background: url($icon_url) center top no-repeat; padding-top: 80px;'>" .
            q($row->title) .
            "</a></div>";
}
$main_extra .= "</div>";
$tool_content .= "</div>";

if ($is_editor) {
    $edit_link = "&nbsp;<a href='../../modules/course_description/editdesc.php?course=$course_code'><img src='$themeimg/edit.png' title='$langEdit' alt='$langEdit' /></a>";
} else {
    $edit_link = '';
}

$main_content .= "<div class='course_info'>";
$desccomm = Database::get()->querySingle("SELECT comments FROM unit_resources WHERE unit_id =
                        (SELECT id FROM course_units WHERE course_id = ?d AND `order` = -1)
                        AND res_id = -1 ORDER BY `order`", $course_id);
if ($desccomm && $desccomm->comments) {
    $description = standard_text_escape($desccomm->comments);
}
if (!empty($description)) {
    $main_content .= "<div class='descr_title'>$langDescription$edit_link</div>\n$description";
} else {
    $main_content .= "<p>$langThisCourseDescriptionIsEmpty$edit_link</p>";
}
if (!empty($keywords)) {
    $main_content .= "<p id='keywords'><b>$langCourseKeywords</b> $keywords</p>";
}
$main_content .= "</div>";

if (!empty($addon)) {
    $main_content .= "<div class='course_info'><h1>$langCourseAddon</h1><p>$addon</p></div>";
}

if (setting_get(SETTING_COURSE_RATING_ENABLE, $course_id) == 1) {
    $rating = new Rating('fivestar', 'course', $course_id);
    $main_content .= $rating->put($is_editor, $uid, $course_id);
}

if (setting_get(SETTING_COURSE_COMMENT_ENABLE, $course_id) == 1) {
    commenting_add_js();
    $comm = new Commenting('course', $course_id);
    $main_content .= $comm->put($course_code, $is_editor, $uid);
}

if (is_sharing_allowed($course_id)) {
    if (setting_get(SETTING_COURSE_SHARING_ENABLE, $course_id) == 1) {
        $main_content .= print_sharing_links($urlServer."courses/$course_code", $title);
    }
}

$main_content .= $main_extra;

units_set_maxorder();

// other actions in course unit
if ($is_editor) {
    // update index and refresh course metadata
    require_once 'modules/search/indexer.class.php';
    require_once 'modules/search/courseindexer.class.php';
    require_once 'modules/search/unitindexer.class.php';
    require_once 'modules/search/unitresourceindexer.class.php';
    require_once 'modules/course_metadata/CourseXML.php';
    $idx = new Indexer();
    $cidx = new CourseIndexer($idx);
    $uidx = new UnitIndexer($idx);
    $urdx = new UnitResourceIndexer($idx);


    if (isset($_REQUEST['edit_submit'])) {
        $main_content .= handle_unit_info_edit();
    } elseif (isset($_REQUEST['edit_submitW'])){
        $title = $_REQUEST['weektitle'];
        $descr = $_REQUEST['weekdescr'];

        if (isset($_REQUEST['week_id'])) { //edit week
            $weekid = $_REQUEST['week_id'];
            Database::get()->query("UPDATE course_weekly_view SET title = ?s, comments = ?s
                                    WHERE id = ?d ", $title, $descr, $weekid);
        } else { //new week
            
            //check if the final week is complete
            $diffDate = Database::get()->querySingle("SELECT DATEDIFF(finish_week, start_week) AS diffDate, id FROM course_weekly_view WHERE course_id = ?d ORDER BY id DESC LIMIT 1", $course_id);
            
            if ($diffDate->diffDate == 6) { //if there is a whole week add one
                $endWeek = new DateTime($course_finishDate);
                $endWeek->modify('+6 day');
                $endWeekForDB = $endWeek->format("Y-m-d");
            } else {
                $days2add = 6-$diffDate->diffDate;
                $endWeek = new DateTime($course_finishDate);
                $endWeek->modify('+'.$days2add.' day');
                $endWeekForDB = $endWeek->format("Y-m-d");
                
                //fill the week
                $q = Database::get()->query("UPDATE course_weekly_view SET finish_week = ?t WHERE id = ?d ", $endWeekForDB, $diffDate->id);
                //add the final week
                $endWeek->modify('+1 day');
                $startWeekForDB = $endWeek->format("Y-m-d");
                
                $endWeek->modify('+6 day');
                $endWeekForDB = $endWeek->format("Y-m-d");
                $q = Database::get()->query("INSERT INTO course_weekly_view SET
                                  title = ?s, comments = ?s, visible = 1, start_week = ?t, finish_week = ?t,
                                  course_id = ?d", $title, $descr, $startWeekForDB, $endWeekForDB, $course_id);
            }
            
            //update the finish date at the course table
            Database::get()->query("UPDATE course SET finish_date = ?t
                                    WHERE id = ?d ", $endWeekForDB, $course_id);
            
        }

    } elseif (isset($_REQUEST['del'])) { // delete course unit
        $id = intval($_REQUEST['del']);
        $course_format = Database::get()->querySingle("SELECT `view_type` FROM course WHERE id = ?d", $course_id)->view_type; 
        if($course_format == "units"){
        Database::get()->query("DELETE FROM course_units WHERE id = ?d", $id);
        Database::get()->query("DELETE FROM unit_resources WHERE unit_id = ?d", $id);
        $uidx->remove($id, false);
        $urdx->removeByUnit($id, false);
        $cidx->store($course_id, true);
        CourseXMLElement::refreshCourse($course_id, $course_code);
        $main_content .= "<p class='success_small'>$langCourseUnitDeleted</p>";
        }else{
            $res_id = intval($_GET['del']);
            if ($id = check_admin_unit_resource($res_id)) {
                Database::get()->query("DELETE FROM course_weekly_view_activities WHERE id = ?d", $res_id);
                $urdx->remove($res_id, false, false);
                $cidx->store($course_id, true);
                CourseXMLElement::refreshCourse($course_id, $course_code);
                $tool_content .= "<p class='success'>$langResourceCourseUnitDeleted</p>";
            }
        }
        
    } elseif (isset($_REQUEST['vis'])) { // modify visibility
        $id = intval($_REQUEST['vis']);
        $vis = Database::get()->querySingle("SELECT `visible` FROM course_units WHERE id = ?d", $id)->visible;
        $newvis = ($vis == 1) ? 0 : 1;
        Database::get()->query("UPDATE course_units SET visible = ?d WHERE id = ?d AND course_id = ?d", $newvis, $id, $course_id);
        $uidx->store($id, false);
        $cidx->store($course_id, true);
        CourseXMLElement::refreshCourse($course_id, $course_code);
    } elseif (isset($_REQUEST['access'])) {
        if ($course_viewType == "weekly") {
            $id = intval($_REQUEST['access']);
            $access = Database::get()->querySingle("SELECT `public` FROM course_weekly_view WHERE id = ?d", $id);
            $newaccess = ($access->public == '1') ? '0' : '1';
            Database::get()->query("UPDATE course_weekly_view SET public = ?d WHERE id = ?d AND course_id = ?d", $newaccess, $id, $course_id);
        } else {
            $id = intval($_REQUEST['access']);
            $access = Database::get()->querySingle("SELECT `public` FROM course_units WHERE id = ?d", $id);
            $newaccess = ($access->public == '1') ? '0' : '1';
            Database::get()->query("UPDATE course_units SET public = ?d WHERE id = ?d AND course_id = ?d", $newaccess, $id, $course_id);
        }
    } elseif (isset($_REQUEST['down'])) {
        $id = intval($_REQUEST['down']); // change order down
        $course_format = Database::get()->querySingle("SELECT `view_type` FROM course WHERE id = ?d", $course_id)->view_type; 
        if($course_format == "units"){
        move_order('course_units', 'id', $id, 'order', 'down', "course_id=$course_id");
        }else{
            $res_id = intval($_REQUEST['down']);
            if ($id = check_admin_unit_resource($res_id)) {
                move_order('course_weekly_view_activities', 'id', $res_id, 'order', 'down', "course_weekly_view_id=$id");
            }
        }
    } elseif (isset($_REQUEST['up'])) { // change order up
        $id = intval($_REQUEST['up']);
        $course_format = Database::get()->querySingle("SELECT `view_type` FROM course WHERE id = ?d", $course_id)->view_type; 
        if($course_format == "units"){
        move_order('course_units', 'id', $id, 'order', 'up', "course_id=$course_id");
        }else{
            $res_id = intval($_REQUEST['up']);
            if ($id = check_admin_unit_resource($res_id)) {
                move_order('course_weekly_view_activities', 'id', $res_id, 'order', 'up', "course_weekly_view_id=$id");
            }
    }
}

    if (isset($_REQUEST['visW'])) { // modify visibility of the Week
        $id = intval($_REQUEST['visW']);
        $vis = Database::get()->querySingle("SELECT `visible` FROM course_weekly_view WHERE id = ?d", $id)->visible;
        $newvis = ($vis == 1) ? 0 : 1;
        Database::get()->query("UPDATE course_weekly_view SET visible = ?d WHERE id = ?d AND course_id = ?d", $newvis, $id, $course_id);
    }
    
    if (isset($_REQUEST['edit_submitW'])) { //update title and comments for week
        $title = $_REQUEST['weektitle'];
        $descr = $_REQUEST['weekdescr'];
        $unit_id = $_REQUEST['week_id'];
        Database::get()->query("UPDATE course_weekly_view SET
                                        title = ?s,
                                        comments = ?s
                                    WHERE id = ?d AND course_id = ?d", $title, $descr, $unit_id, $course_id);        
    }
    
}

//Check the course view type
$courseInfo = Database::get()->querySingle("SELECT view_type, start_date, finish_date FROM course WHERE id = ?d", $course_id);
$viewCourse = $courseInfo->view_type;
$start_date = $courseInfo->start_date;
$finish_date = $courseInfo->finish_date;
$course_viewType = $result->view_type;

// add course units
if ($is_editor) {
    $last_id = Database::get()->querySingle("SELECT id FROM course_units
                                                   WHERE course_id = ?d AND `order` >= 0
                                                   ORDER BY `order` DESC LIMIT 1", $course_id);
    if ($last_id) {
        $last_id = $last_id->id;
    }
    $query = "SELECT id, title, comments, visible, public
		  FROM course_units WHERE course_id = $course_id AND `order` >= 0
                  ORDER BY `order`";
} else {
    $query = "SELECT id, title, comments, visible, public
		  FROM course_units WHERE course_id = $course_id AND visible = 1 AND `order` >= 0
                  ORDER BY `order`";
}

$sql = Database::get()->queryArray($query);
$total_cunits = count($sql);

if ($total_cunits > 0) {
    $count_index = 1;
    $cunits_content .= "<div class='panel'><ul class='boxlist'>";
    foreach ($sql as $cu) {
        // access status
        $access = $cu->public;
        // Visibility icon
        $vis = $cu->visible;
        $icon_vis = ($vis == 1) ? 'visible.png' : 'invisible.png';
        $class1_vis = ($vis == 0) ? ' class="invisible"' : '';
        $class_vis = ($vis == 0) ? 'invisible' : '';
        $cunits_content .= "<li class='list-item contentbox'>
                                <div class='item-content'>
                                    <div class='item-header'>
                                        <h4 class='item-title'><a class='$class_vis' href='${urlServer}modules/units/?course=$course_code&amp;id=$cu->id'>" . q($cu->title) . "</a></h4>
                                    </div>	    
                                    <div class='item-body'>    
                                        $cu->comments
                                    </div>			      
                                </div>";
        if ($is_editor) {                                                
            $cunits_content .= "<div class='item-side'>" .
                action_button(array(
                    array('title' => $langVisibility,
                          'url' => "$_SERVER[SCRIPT_NAME]?vis=$cu->id",
                          'icon' => 'fa-eye'),
                    array('title' => $langEdit,
                          'url' => $urlAppend . "modules/units/info.php?course=$course_code&amp;edit=$cu->id",
                          'icon' => 'fa-edit'),
                    array('title' => $langResourceAccess,
                          'url' => "$_SERVER[SCRIPT_NAME]?access=$cu->id",
                          'icon' => $access == 1? 'fa-unlock': 'fa-lock',
                          'show' => $visible == COURSE_OPEN),
                    array('title' => $langDown,
                          'url' => "$_SERVER[SCRIPT_NAME]?down=$cu->id",
                          'icon' => 'fa-arrow-down',
                          'show' => $cu->id != $last_id),
                    array('title' => $langUp,
                          'url' => "$_SERVER[SCRIPT_NAME]?up=$cu->id",
                          'icon' => 'fa-arrow-up',
                          'show' => $count_index != 1),
                    array('title' => $langDelete,
                          'url' => "$_SERVER[SCRIPT_NAME]?del=$cu->id",
                          'icon' => 'fa-times',
                          'class' => 'delete',
                          'confirm' => $langCourseUnitDeleteConfirm))) .
                '</div>';
        }
        $cunits_content .= "</li>";
        $count_index++;
    }
    $cunits_content .= "</ul></div>"; 
}

$bar_content .= "<b style='text-transform: uppercase; color:#999999; font-size:10px;'>" . $langCode . ":</b> " . q($public_code) . "" .
        "<b style='text-transform: uppercase; color:#999999; font-size:10px;'> / " . $langTeachers . ":</b> " . q($professor) . "" .
        "<b style='text-transform: uppercase; color:#999999; font-size:10px;'> / " . $langFaculty . ":</b> ";

$departments = $course->getDepartmentIds($course_id);
$i = 1;
foreach ($departments as $dep) {
    $br = ($i < count($departments)) ? '<br/>' : '';
    $bar_content .= $tree->getFullPath($dep) . $br;
    $i++;
}

$bar_content .= "  ";

$numUsers = Database::get()->querySingle("SELECT COUNT(user_id) AS numUsers
                FROM course_user
                WHERE course_id = ?d", $course_id)->numUsers;

//set the lang var for lessons visibility status
switch ($visible) {
    case COURSE_CLOSED: {
            $lessonStatus = "<span title='$langPrivate'>$langPrivateShort</span>";
            break;
        }
    case COURSE_REGISTRATION: {
            $lessonStatus = "<span title='$langPrivOpen'>$langPrivOpenShort</span>";
            break;
        }
    case COURSE_OPEN: {
            $lessonStatus = "<span title='$langPublic'>$langPublicShort</span>";
            break;
        }
    case COURSE_INACTIVE: {
            $lessonStatus = "<span class='invisible' title='$langCourseInactive'>$langCourseInactiveShort</span>";
            break;
        }
}
$bar_content .= "<b style='text-transform: uppercase; color:#999999; font-size:10px;'> / $langConfidentiality:</b> $lessonStatus";
if ($is_course_admin) {
    $link = "<a href='{$urlAppend}modules/user/?course=$course_code'>$numUsers $langRegistered</a>";
} else {
    $link = "$numUsers $langRegistered";
}
$bar_content .= "<b style='text-transform: uppercase; color:#999999; font-size:10px;'> / $langUsers:</b> $link";

// display course license
if ($course_license) {
    $license_info_box = "
    
        ${langOpenCoursesLicense}
        <small>" . copyright_info($course_id) . "</small>";
} else {
    $license_info_box = '';
}

// display opencourses level in bar
require_once 'modules/course_metadata/CourseXML.php';
$level = ($levres = Database::get()->querySingle("SELECT level FROM course_review WHERE course_id =  ?d", $course_id)) ? CourseXMLElement::getLevel($levres->level) : false;
$opencourses_level = '';
if (isset($level) && !empty($level)) {
    $metadataUrl = $urlServer . 'modules/course_metadata/info.php?course=' . $course_code;
    $opencourses_level = "

    <div class='row'>

        <div class='col-md-4'>
            <img src='$themeimg/open_courses_logo_small.png' title='$GLOBALS[langOpenCourses]' alt='$GLOBALS[langOpenCourses]'>
        </div>

        <div class='col-md-8 margin-top-thin'>
            ${langOpenCoursesLevel}: $level
            <br />
            <small><a href='$metadataUrl'>$langCourseMetadata " .
            icon('fa-tags', $langCourseMetadata, $metadataUrl) . "</small>
        </div>        

    </div>


    <table class='tbl_courseid' width='200' style='display: none;'>
        <tr class='title1'>
            <td class='title1'></td>
            <td style='text-align: right; padding-right: 1.2em'><a href='$metadataUrl'>" .
            icon('fa-tags', $langCourseMetadata, $metadataUrl) . "</td>
        </tr>
        <tr>
            <td colspan='2'><div class='center'><img src='$themeimg/open_courses_logo_small.png' title='$GLOBALS[langOpenCourses]' alt='$GLOBALS[langOpenCourses]'></div>
                <div class='center'><b>${langOpenCoursesLevel}: $level</b></div>
            </td>
        </tr>
    </table>


        ";
}


if ($is_editor or ( isset($_SESSION['saved_editor']) and $_SESSION['saved_editor']) or ( isset($_SESSION['saved_status']) and $_SESSION['saved_status'] == 1)) {
    if (isset($_SESSION['saved_status'])) {
        $button_message = $langStudentViewDisable;
        $button_image = "switch_t";
    } else {
        $button_message = $langStudentViewEnable;
        $button_image = "switch_s";
    }
    $toggle_student_view = "<form action='{$urlServer}student_view.php?course=$course_code' method='post'>
                <input id='view_btn' type='image' src='$themeimg/$button_image.png' name='submit' title='$button_message'></form>";
} else {
    $toggle_student_view = '';
}

$emailnotification = '';
if ($uid and $status != USER_GUEST and ! get_user_email_notification($uid, $course_id)) {
    $emailnotification = "<div class='alert1'>$langNoUserEmailNotification
        (<a href='{$urlServer}main/profile/emailunsubscribe.php?cid=$course_id'>$langModify</a>)</div>";
}
// display `contact teacher via email` link if teacher actually receives email from his course
$receive_mail = FALSE;
$rec_mail = array();
$q = Database::get()->queryArray("SELECT user_id FROM course_user WHERE course_id = ?d
                                AND status = ?d", $course_id, USER_TEACHER);
foreach ($q as $p) {
    $prof_uid = $p->user_id;
    if (get_user_email_notification_from_courses($prof_uid) and get_user_email_notification($prof_uid, $course_id)) {
        $rec_mail[$prof_uid] = 1;
    }
}
if (!empty($rec_mail)) {
    $receive_mail = TRUE;
}

// Title and Toolbox
$tool_content .= "
<div class='row margin-top-thin'>
    <div class ='col-md-12'>

        <div class='toolbox pull-right'>



            <div type='button' class='btn-default-eclass place-at-toolbox dropdown open-on-hover'>
                <span class='txt' rel='tooltip' data-toggle='tooltip' data-placement='bottom'>Πληροφορίες Μαθήματος</span>
                <span class='fa fa-caret-down'></span>

                <ul class='dropdown-menu'>
                    <li><a class='md-trigger' data-modal='syllabus-prof' href='#''>Επιλογή 1</a></li>
                    <li><a class='md-trigger' data-modal='syllabus-toc' href='#''>Επιλογή 2</a></li>
                    <li><a class='md-trigger' data-modal='syllabus-books' href='#''>Επιλογή 3</a></li>
                    <li><a class='md-trigger' data-modal='syllabus-bibliography' href='#''>Επιλογή 4</a></li>
                </ul>
            </div>
    ";

            // Button: email - contact professor
            if ($status != USER_GUEST) {
                if ($receive_mail) {
                    $tool_content .= "
                        <a href='../../modules/contact/index.php?course=$course_code' id='email_btn' class='btn-default-eclass place-at-toolbox' title='$langContactProf' >
                                <i class='fa fa-envelope'></i>
                        </a>";
                }
            }

            // Button: rss
            if (visible_module(MODULE_ID_ANNOUNCE))
            {
                $tool_content .= "
                        <a href='${urlServer}modules/announcements/rss.php?c=$course_code' class='btn-default-eclass place-at-toolbox' title='" . q($langRSSFeed) . "'>
                                <i class='fa fa-rss'></i>
                        </a>";
            }

            // Button: toggle student view
            $tool_content .= "
        </div>
    </div>
</div>
";








// Contentbox: Course main contentbox
$tool_content .= "
<div class='row margin-top-thin'>
    <div class='col-md-12'>
        <div class='panel row padding'>

            <div class='banner-image-wrapper col-md-5 col-sm-5 col-xs-12'>
                <div >
                    <img class='banner-image img-responsive' src='../../template/bootstrap/img/ph1.jpg'/>
                </div>
            </div>

            <div class='col-md-7 col-sm-7 col-xs-12'>
                <div class=''>$main_content</div>             
            </div>
            
            <div class ='col-md-7 col-sm-12 col-xs-12'>
                <hr>
                $bar_content
            </div>


        </div>
    </div>
<div>
";




// Contentbox: Thematikes enotites
// Contentbox: Calendar
// Contentbox: Announcements
if ($total_cunits > 0 || $is_editor) {
    $alter_layout = FALSE;
    $cunits_sidebar_columns = 4; 
    $cunits_sidebar_subcolumns = 12;
} else {
    $alter_layout = TRUE;
    $cunits_sidebar_columns = 12; 
    $cunits_sidebar_subcolumns = 4;
}
$tool_content .= "
<div class='row'>";
if (!$alter_layout){
    $tool_content .= "
    <div class='col-md-8'>
        <h5 class='content-title'>$langCourseUnits</h5>".
        (($is_editor)? "
            <hr class='no-margin'/>
            <div class='align-right'>
                <div class='toolbox margin-bottom-thin margin-top-thin'>
                    <a href='{$urlServer}modules/units/info.php?course=$course_code' rel='tooltip' data-toggle='tooltip' data-placement='right' title ='$langAddUnit' class='btn btn-default-eclass place-at-toolbox size-s'>
                        <i class='fa fa-plus space-after-icon'></i>
                        $langAddUnit
                    </a>
                </div>                          
            </div>" : "")."
                
            $cunits_content

    </div>";
}

$tool_content .= "
    <div class='col-md-$cunits_sidebar_columns'>
        
        <div class='row'>
            <div class='col-md-$cunits_sidebar_subcolumns'>
                <h5 class='content-title'>${langOpenCourseShort}</h5>
                <div class='panel padding'>
                        $opencourses_level
                </div>
            </div>
        </div>

        <div class='row'>
            <div class='col-md-$cunits_sidebar_subcolumns'>
                <h5 class='content-title'>${langOpenCoursesLicense}</h5>
                <div class='panel license_info_box padding'>
                        $license_info_box
                </div>
            </div>
            <div class='col-md-$cunits_sidebar_subcolumns'>
                <h5 class='content-title'>$langCalendar</h5>
                <div class='panel padding'>
                        <img style='margin:1em auto;display:block; max-width:100%;' src='http://users.auth.gr/panchara/eclass/project/img/calendar.png'>
                </div>
            </div>
            <div class='col-md-$cunits_sidebar_subcolumns'>
                <h5 class='content-title'>$langAnnouncements</h5>
                <ul class='tablelist panel'>" . course_announcements() . "
                </ul>
            </div>
        </div>
        
    </div>
</div>
";


$tool_content .= "
<div id='content_course' style='display:none;'>
<table width='100%'>
<tr>
<td valign='top'>$main_content</td>
<td width='200' valign='top'>
  <table class='tbl_courseid' width='200'>
  <tr class='title1'>
    <td  class='title1'>$langIdentity</td>
  </tr>
  <tr>
    <td class='smaller'>$bar_content</td>
  </tr>
  </table>
  <br />
  $license_info_box
  $opencourses_level
  <table class='tbl_courseid' width='200'>
  <tr class='title1'>
    <td class='title1'>$langTools</td>
    <td class='left'>$toggle_student_view";
if ($status != USER_GUEST) {
    if ($receive_mail) {
        $tool_content .= icon('fa-envelope', $langContactProf, $urlAppend . "modules/contact/index.php?course=$course_code");
    }
}
if (visible_module(MODULE_ID_ANNOUNCE)) {
    $tool_content .= "<span class='feed'>" .
        icon('fa-rss', $langRSSFeed, $urlServer . "modules/announcements/rss.php?c=$course_code") . "</span>";
}
$tool_content .= "</td>
      </tr>
      </table>
      $emailnotification
      <br />\n";

$tool_content .= "</td></tr></table>";

if ($viewCourse == "weekly") {
    
    if (!$is_editor){
        $visibleFlag = " AND visible = 1";
    } else {
        $visibleFlag = "";
    }
    $tool_content .= "<p class='descr_title'>$langCourseWeeklyFormat: <a href='{$urlServer}modules/weeks/info.php?course=$course_code'><img src='$themeimg/add.png' width='16' height='16' title='$langAddUnit' alt='$langAddUnit' /></a></p>";

    $weeklyQuery = Database::get()->queryArray("SELECT id, start_week, finish_week, visible, title, comments, public FROM course_weekly_view WHERE course_id = ?d $visibleFlag", $course_id);
    foreach ($weeklyQuery as $week){
        $icon_vis = ($week->visible == 1) ? 'visible.png' : 'invisible.png';
        $class_vis = ($week->visible == 0) ? 'class=invisible' : '';
        $icon_access = ($week->public == 1) ? 'access_public.png' : 'access_limited.png';
        
        $tool_content .= "<fieldset>
                            <a href='../../modules/weeks/?course=$course_code&amp;id=$week->id'>
                                <h2 $class_vis>$langWeek: ".nice_format($week->start_week)." - ".nice_format($week->finish_week)." - " . q($week->title) . "</h2>
                            </a>
                            <a href='../../modules/weeks/info.php?course=$course_code&amp;edit=$week->id'>
                                <img src='$themeimg/edit.png' title='$langEdit' alt='$langEdit'> 
                            </a>
                            <a href='$_SERVER[SCRIPT_NAME]?visW=$week->id'>
                                <img src='$themeimg/$icon_vis' title='$langVisibility' alt='$langVisibility'>
                            </a>
                            <a href='$_SERVER[SCRIPT_NAME]?access=$week->id'>
                                <img src='$themeimg/$icon_access' title='" . q($langResourceAccess) . "' alt='" . q($langResourceAccess) . "' />
                            </a>
                            <div $class_vis>$week->comments</div>
                            <hr>";
                            show_resourcesWeeks($week->id);
        $tool_content .= "</fieldset>";
        
    }
}


if($viewCourse == "units"){
    $tool_content .= "<table width='100%' class='tbl'><tr><td>$cunits_content</td>
   </tr></table>";
}

$tool_content .= "</div></div></div>";

draw($tool_content, 2, null, $head_content);


function course_announcements() {
    global $course_id, $course_code, $langNoAnnounce, $urlAppend, $dateFormatLong;

    if (visible_module(MODULE_ID_ANNOUNCE)) {
        $q = Database::get()->queryArray("SELECT title, `date`, id
                            FROM announcement
                            WHERE course_id = ?d AND
                                  visible = 1
                            ORDER BY `date` DESC LIMIT 5", $course_id);
        if ($q) { // if announcements exist
            $ann_content = '';
            foreach ($q as $ann) {
                $ann_url = $urlAppend . "modules/announcements/?course=$course_code&amp;an_id=" . $ann->id;
                $ann_date = claro_format_locale_date($dateFormatLong, strtotime($ann->date));
                $ann_content .= "<li class='list-item'>" .
                    "<span class='item-title'><a href='$ann_url'>" . q(ellipsize($ann->title, 60)) .
                    "</a><br>$ann_date</span></li>";
            }
            return $ann_content;
        }
    }
    return "<li>$langNoAnnounce</li>";
}
