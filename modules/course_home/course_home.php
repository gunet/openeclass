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
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/course.class.php';
require_once 'include/action.php';

$tree = new Hierarchy();
$course = new Course();

$require_help = TRUE;
$helpTopic = 'course_home';
$nameTools = $langIdentity;
$main_content = $cunits_content = $bar_content = "";

add_units_navigation(TRUE);

load_js('tools.js');
load_js('jquery');
ModalBoxHelper::loadModalBox();
$head_content .= "<script type='text/javascript'>$(document).ready(add_bookmark);</script>";

// For statistics: record login
Database::get()->query("INSERT INTO logins SET user_id = ?d, course_id = ?d, ip='$_SERVER[REMOTE_ADDR]', date_time=NOW()", $uid, $course_id);

$action = new action();
$action->record(MODULE_ID_UNITS);

if (isset($_GET['from_search'])) { // if we come from home page search
    header("Location: {$urlServer}modules/search/search_incourse.php?all=true&search_terms=$_GET[from_search]");
}

$result = Database::get()->querySingle("SELECT keywords, visible, prof_names, public_code, course_license
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
            "</a></div>\n";
}
if ($is_editor) {
    $edit_link = "&nbsp;<a href='../../modules/course_description/editdesc.php?course=$course_code'><img src='$themeimg/edit.png' title='$langEdit' alt='$langEdit' /></a>";
} else {
    $edit_link = '';
}

$main_content .= "\n      <div class='course_info'>";
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
    } elseif (isset($_REQUEST['del'])) { // delete course unit
        $id = intval($_REQUEST['del']);
        Database::get()->query("DELETE FROM course_units WHERE id = ?d", $id);
        Database::get()->query("DELETE FROM unit_resources WHERE unit_id = ?d", $id);
        $uidx->remove($id, false);
        $urdx->removeByUnit($id, false);
        $cidx->store($course_id, true);
        CourseXMLElement::refreshCourse($course_id, $course_code);
        $main_content .= "<p class='success_small'>$langCourseUnitDeleted</p>";
    } elseif (isset($_REQUEST['vis'])) { // modify visibility
        $id = intval($_REQUEST['vis']);
        $vis = Database::get()->querySingle("SELECT `visible` FROM course_units WHERE id = ?d", $id)->visible;
        $newvis = ($vis == 1) ? 0 : 1;
        Database::get()->query("UPDATE course_units SET visible = ?d WHERE id = ?d AND course_id = ?d", $newvis, $id, $course_id);
        $uidx->store($id, false);
        $cidx->store($course_id, true);
        CourseXMLElement::refreshCourse($course_id, $course_code);
    } elseif (isset($_REQUEST['access'])) {
        $id = intval($_REQUEST['access']);
        $access = Database::get()->querySingle("SELECT `public` FROM course_units WHERE id = ?d", $id);
        $newaccess = ($access == '1') ? '0' : '1';
        Database::get()->query("UPDATE course_units SET public = ?d WHERE id = ?d AND course_id = ?d", $newaccess, $id, $course_id);
    } elseif (isset($_REQUEST['down'])) {
        $id = intval($_REQUEST['down']); // change order down
        move_order('course_units', 'id', $id, 'order', 'down', "course_id=$course_id");
    } elseif (isset($_REQUEST['up'])) { // change order up
        $id = intval($_REQUEST['up']);
        move_order('course_units', 'id', $id, 'order', 'up', "course_id=$course_id");
    }
}

// add course units
$cunits_content .= "<h5 class='content-title'>" . q($langCourseUnits) . "</h5>";
if ($is_editor) {
    $cunits_content .= "
        <div class='toolbox float-right'>
            <a href='{$urlServer}modules/units/info.php?course=$course_code'>
                <button class='button color-green' title='$langAddUnit'>
                    <i class='fa fa-plus-circle'></i>
                    <span class='txt'>$langAddUnit</span>
                </button>
            </a>
        </div>
        <div style='clear: both'></div>";

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
$cunits_content .= "<ul class='tablelist contentbox'>";
$sql = Database::get()->queryArray($query);
$first = true;
$count_index = 1;
foreach ($sql as $cu) {
    // access status
    $access = $cu->public;
    // Visibility icon
    $vis = $cu->visible;
    $icon_vis = ($vis == 1) ? 'visible.png' : 'invisible.png';
    $class1_vis = ($vis == 0) ? ' class="invisible"' : '';
    $class_vis = ($vis == 0) ? 'invisible' : '';
    if (resource_access($vis, $access)) {
        $cunits_content .= "
            <li class='list-item'>
                <span class='item-wholeline'>
                    <h4>$count_index. <a href='${urlServer}modules/units/?course=$course_code&amp;id=$cu->id'>" . q($cu->title) . "</a></h4>
                    <span class='description'>" . standard_text_escape($cu->comments) . "</span>
                </span>
                <div class='item-right-cols'>";
        if ($is_editor) {
            $cunits_content .= "
                <span class='item-options'>
                    <span class='options-content'>";
            if ($cu->id != $last_id) {
                $cunits_content .= "
                        <a href='$_SERVER[SCRIPT_NAME]?down=$cu->id' title='$langDown'>
                            <i class='fa fa-arrow-down'></i>
                        </a>";
            }
            if (!$first) {
                $cunits_content .= "
                        <a href='$_SERVER[SCRIPT_NAME]?up=$cu->id' title='$langUp'>
                            <i class='fa fa-arrow-up'></i>
                        </a>";
            }
            $cunits_content .= "
                        <a href='{$urlAppend}modules/units/info.php?course=$course_code&amp;edit=$cu->id' title='$langEdit'>
                            <i class='fa fa-pencil'></i>
                        </a>
                        <a href='$_SERVER[SCRIPT_NAME]?vis=$cu->id' title='$langVisibility'>
                            <i class='fa fa-eye'></i>
                        </a>";
            if ($visible == COURSE_OPEN) { // public accessibility actions
                $cunits_content .= "
                        <a href='$_SERVER[SCRIPT_NAME]?access=$cu->id' title='$langResourceAccess'>
                            <i class='fa fa-lock'></i>
                        </a>";
            }
            $cunits_content .= "
                        <a href ='$_SERVER[SCRIPT_NAME]?del=$cu->id' class='delete-action' title='Delete'>
                            <i class='fa fa-times'></i>
                        </a>
                    </span>
                    <span class='options-icon'>
                        <i class='fa fa-gear'></i>
                    </span>
                </span>";
        }
        $count_index++;
    }
    $first = false;

    $cunits_content .= "
            </div>
        </li>";
}



// Delete empty units section for non-editors
if ($first and !$is_editor) {
    $cunits_content = '';
}

$bar_content .= "
    <div class='banner-tags'>
        <span class='label'><span>" . $langCode . "</span>: " . q($public_code) . "</span>" .
        "<span class='label'><span>" . $langTeachers . "</span>: " . q($professor) . "</span>" .
        "<span class='label'><span>" . $langFaculty . "</span>: ";

$departments = $course->getDepartmentIds($course_id);
$i = 1;
foreach ($departments as $dep) {
    $br = ($i < count($departments)) ? '<br/>' : '';
    $bar_content .= $tree->getFullPath($dep) . $br;
    $i++;
}

$bar_content .= "</span>\n";

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
$bar_content .= "<span class='label'><span>$langConfidentiality</span>: $lessonStatus</span>";
if ($is_course_admin) {
    $link = "<a href='{$urlAppend}modules/user/?course=$course_code'>$numUsers $langRegistered</a>";
} else {
    $link = "$numUsers $langRegistered";
}
$bar_content .= "
        <span class='label'><span>$langUsers</span>: $link</span> 
    </div>";

// display course license
if ($course_license) {
    $license_info_box = "
        <div class='column-one-third'>
            <span>
                ${langOpenCoursesLicense}<br/>
                <small>" . copyright_info($course_id) . "</small>
            </span>
        </div>";
} else {
    $license_info_box = '';
}

// display opencourses level in bar
require_once 'modules/course_metadata/CourseXML.php';
$level = ($levres = Database::get()->querySingle("SELECT level FROM course_review WHERE course_id = ?d", $course_id)) ? CourseXMLElement::getLevel($levres->level) : false;
$opencourses_level = '';
if (isset($level) && !empty($level)) {
    $metadataUrl = $urlServer . 'modules/course_metadata/info.php?course=' . $course_code;
    $opencourses_level = "<table class='tbl_courseid' width='200'>
        <tr class='title1'>
            <td class='title1'>${langOpenCourseShort}</td>
            <td style='text-align: right; padding-right: 1.2em'><a href='$metadataUrl'>" .
            icon('lom', $langCourseMetadata, $metadataUrl) . "</td></tr>
        <tr><td colspan='2'><div class='center'>" . icon('open_courses_logo_small', $GLOBALS['langOpenCourses']) . "</div>
                <div class='center'><b>${langOpenCoursesLevel}: $level</b></div></td></tr>
        </table>
        <br/>";
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
                <input id='view_btn' type='image' src='$themeimg/$button_image.png' name='submit' title='$button_message'/>&nbsp;&nbsp;";
    $toggle_student_view_close = '</form>';
} else {
    $toggle_student_view = $toggle_student_view_close = '';
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
<div id='title'>
    <h1 class='page-title'>Τίτλος Μαθήματος</h1>

    <div class='toolbox float-right'>

        <div type='button' class='button dropdown open-on-hover'>
            <span class='txt' class='tooltip-left' data-tooltip='Syllabus'>Πληροφορίες Μαθήματος</span>
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
                    <a href='../../modules/contact/index.php?course=$course_code' id='email_btn'>
                        <button class='button hover-blue' title='$langContactProf' >
                            <i class='fa fa-envelope'></i>
                        </button>
                    </a>";
            }
        }


        // Button: star - bookmark the page
        $tool_content .= "        
                    <a href='$_SERVER[SCRIPT_NAME]' title='" . q($title) . "' class='jqbookmark'>
                        <button class='button hover-blue' title='$langAddAsBookmark'>
                            <i class='fa fa-star'></i>
                        </button>
                    </a>";


        // Button: rss
        if (visible_module(MODULE_ID_ANNOUNCE))
        {
            $tool_content .= "
                    <a href='${urlServer}modules/announcements/rss.php?c=$course_code'>
                        <button class='button hover-blue' title='" . q($langRSSFeed) . "'>
                            <i class='fa fa-rss'></i>
                        </button>
                    </a>";
        }

        // Button: toggle student view
        $tool_content .= "  
                    <button class='button hover-blue' title='$langAddAsBookmark'>
                            $toggle_student_view_close
                            $toggle_student_view
                    </button>";     
           
$tool_content .= "
    </div>
    
</div>";








// Contentbox: Course main contentbox
$tool_content .= "
<div id='lesson-banner' class='contentbox'>
    <div class='banner-left'>
        <div class='banner-image'></div>
    </div>
    <div class='banner-content'>
        <div class='banner-description'>$main_content</p></div>
        <hr>
        $bar_content
    </div>
</div>";




// Contentbox: Thematikes enotites
// Contentbox: Calendar
// Contentbox: Announcements
$tool_content .= "
<div class='a-wrapper'>

    <div class='column-first column-one-half'>
        $cunits_content
    </div>

    <div class='column-one-half'>

        <h5 class='content-title'>Ημερολογιο</h5>
        <div class='contentbox padding'>
            <img style='margin:1em auto;display:block; max-width:100%;' src='http://users.auth.gr/panchara/eclass/project/img/calendar.png'>
        </div>


        <h5 class='content-title'>Ανακοινωσεις</h5>
        <ul class='tablelist contentbox'>
                
            <li class='list-item'>
                <span class='item-title'>Ανακοίνωση 1</span>
                <div class='item-right-cols'>
                    <span class='item-date'><span class='item-content'>13/2/2019</span></span>
                </div>
            </li>

            <li class='list-item'>
                <span class='item-title'>Ανακοίνωση 2</span>
                <div class='item-right-cols'>
                    <span class='item-date'><span class='item-content'>13/2/2019</span></span>
                </div>
            </li>

            <li class='list-item'>
                <span class='item-title'>Ανακοίνωση 3</span>
                <div class='item-right-cols'>
                    <span class='item-date'><span class='item-content'>13/2/2019</span></span>
                </div>
            </li>

        </ul>

    </div>

    <div style='clear: both'></div>

</div>";







// Contentbox: Keywords, Copyright and Opencourse mode
$tool_content .= "
<div id='lesson-footer' class='a-wrapper'>
    <div class='contentbox padding'>

        <div class='column-first column-one-third'>
            <span class='title'>Λέξεις Κλειδιά</span>
            <div class='label-group'>
                <span class=''>Ημιαγωγοί</span>,
                <span class=''>δίοδοι</span>,
                <span class=''>ανόρθωση</span>,
                <span class=''>σταθεροποίηση τάσης</span>,
                <span class=''>ψαλλίδιση</span>
            </div>
        </div>

        $license_info_box

        $opencourses_level

        <div style='clear: both'></div>

    </div>
</div>";







$tool_content .= "
<br/>
<br/><hr><br/>
<br/><br/><br/>
";

draw($tool_content, 2, null, $head_content);
