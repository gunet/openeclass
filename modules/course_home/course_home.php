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
require_once 'main/personal_calendar/calendar_events.class.php';

$tree = new Hierarchy();
$course = new Course();

$pageName = ''; // delete $pageName set in doc_init.php
$require_help = TRUE;
$helpTopic = 'course_home';
$main_content = $cunits_content = $bar_content = "";

add_units_navigation(TRUE);

load_js('tools.js');

if(!empty($langLanguageCode)){
    load_js('bootstrap-calendar-master/js/language/'.$langLanguageCode.'.js');
}
load_js('bootstrap-calendar-master/js/calendar.js');
load_js('bootstrap-calendar-master/components/underscore/underscore-min.js');

ModalBoxHelper::loadModalBox();
$head_content .= "<link rel='stylesheet' type='text/css' href='{$urlAppend}js/bootstrap-calendar-master/css/calendar_small.css' />
<script type='text/javascript'>
    $(document).ready(function() {  
    
        

            $('.inline').colorbox({ inline: true, width: '50%', rel: 'info', current: '' });"
//Calendar stuff
.'var calendar = $("#bootstrapcalendar").calendar({
                    tmpl_path: "'.$urlAppend.'js/bootstrap-calendar-master/tmpls/",
                    events_source: "'.$urlAppend.'main/calendar_data.php?course='.$course_code.'",
                    language: "'.$langLanguageCode.'",
                    views: {year:{enable: 0}, week:{enable: 0}, day:{enable: 0}},
                    onAfterViewLoad: function(view) {
                                $("#current-month").text(this.getTitle());
                                $(".btn-group button").removeClass("active");
                                $("button[data-calendar-view=\'" + view + "\']").addClass("active");
                                }
        });

        $(".btn-group button[data-calendar-nav]").each(function() {
            var $this = $(this);
            $this.click(function() {
                calendar.navigate($this.data("calendar-nav"));
            });
        });

        $(".btn-group button[data-calendar-view]").each(function() {
            var $this = $(this);
            $this.click(function() {
                calendar.view($this.data("calendar-view"));
            });
        });'

    ."})
    </script>";

// For statistics: record login
Database::get()->query("INSERT INTO logins SET user_id = ?d, course_id = ?d, ip = '$_SERVER[REMOTE_ADDR]', date_time = " . DBHelper::timeAfter() . "", $uid, $course_id);

$action = new action();
$action->record(MODULE_ID_UNITS);

if (isset($_GET['from_search'])) { // if we come from home page search
    header("Location: {$urlServer}modules/search/search_incourse.php?all=true&search_terms=$_GET[from_search]");
}

$course_info = Database::get()->querySingle("SELECT keywords, visible, prof_names, public_code, course_license, finish_date,
                                               view_type, start_date, finish_date, description, home_layout, course_image
                                          FROM course WHERE id = ?d", $course_id);

$keywords = q(trim($course_info->keywords));
$visible = $course_info->visible;
$professor = $course_info->prof_names;
$public_code = $course_info->public_code;
$course_license = $course_info->course_license;

$res = Database::get()->queryArray("SELECT cd.id, cd.title, cd.comments, cd.type, cdt.icon FROM course_description cd
                                    LEFT JOIN course_description_type cdt ON (cd.type = cdt.id)
                                    WHERE cd.course_id = ?d AND cd.visible = 1 ORDER BY cd.order", $course_id);

$tool_content .= "<div style='display: none'>";

if(count($res)>0){
    $course_info_extra = "";
    foreach ($res as $row) {
        $desctype = intval($row->type) - 1;    
        $hidden_id = "hidden_" . $row->id;
        $tool_content .= "<div id='$hidden_id'><h1>" . q($row->title) . "</h1>" .
                standard_text_escape($row->comments) . "</div>";    
        $course_info_extra .= "<li><a class='md-trigger inline' data-modal='syllabus-prof' href='#$hidden_id'>".q($row->title) ."</a></li>";
    }
    $course_info_btn = "
            <div class='btn-group' role='group'>
              <button type='button' class='btn btn-default btn-sm dropdown-toggle' data-toggle='dropdown' aria-expanded='false'>
                $langCourseDescription
                <span class='caret'></span>
                <span class='sr-only'>Toggle Dropdown</span>
              </button>
              <ul class='dropdown-menu pull-right' role='menu'>
                $course_info_extra
              </ul>
            </div>";    
} else {
   $course_info_btn = ''; 
}

$tool_content .= "</div>";



$main_content .= "<div class='course_info'>";
if ($course_info->description) {
    $description = standard_text_escape($course_info->description);
    $main_content .= "

    <div id='descr_content'>
        $description
    </div>
    ";
} else {
    $main_content .= "<p class='not_visible'> - $langThisCourseDescriptionIsEmpty - </p>";
}
if (!empty($keywords)) {
    $main_content .= "<p id='keywords'><b>$langCourseKeywords</b> $keywords</p>";
}
$main_content .= "</div>";

if (!empty($addon)) {
    $main_content .= "<div class='course_info'><h1>$langCourseAddon</h1><p>$addon</p></div>";
}
if (setting_get(SETTING_COURSE_COMMENT_ENABLE, $course_id) == 1) {
    commenting_add_js();
    $comm = new Commenting('course', $course_id);
    $main_content .= $comm->put($course_code, $is_editor, $uid);
}
if (setting_get(SETTING_COURSE_RATING_ENABLE, $course_id) == 1) {
    $rating = new Rating('fivestar', 'course', $course_id);
    $rating_content = $rating->put($is_editor, $uid, $course_id);
}
if (is_sharing_allowed($course_id)) {
    if (setting_get(SETTING_COURSE_SHARING_ENABLE, $course_id) == 1) {
        $social_content = print_sharing_links($urlServer."courses/$course_code", $currentCourseName);
    }
}
$panel_footer = "";
if(isset($rating_content) || isset($social_content)) {
    $panel_footer .= "                
                <div class='panel-footer'>
                    <div class='row'>";
    if(isset($rating_content)){
     $panel_footer .=
            "<div class='col-sm-6'>
                $rating_content
            </div>";       
    }
    if(isset($social_content)){
     $panel_footer .=
            "<div class='col-sm-6 ".(isset($rating_content) ? "text-right" : "")."'>
                $social_content
            </div>";         
    }
    $panel_footer .= "                
                </div>
            </div>";          
}
units_set_maxorder();

// other actions in course unit
if ($is_editor) {
    // update index and refresh course metadata
    require_once 'modules/search/indexer.class.php';
    require_once 'modules/course_metadata/CourseXML.php';

    if (isset($_REQUEST['edit_submit'])) {
        $main_content .= handle_unit_info_edit();
    } elseif (isset($_REQUEST['edit_submitW'])){
        $title = $_REQUEST['weektitle'];
        $descr = $_REQUEST['weekdescr'];
        if (isset($_REQUEST['week_id'])) { //edit week
            $weekid = $_REQUEST['week_id'];
            Database::get()->query("UPDATE course_weekly_view SET title = ?s, comments = ?s
                                    WHERE id = ?d ", $title, $descr, $weekid);
        }
    } elseif (isset($_REQUEST['del'])) { // delete course unit
        $id = intval($_REQUEST['del']);
        if ($course_info->view_type == 'units') {
            Database::get()->query('DELETE FROM course_units WHERE id = ?d', $id);
            Database::get()->query('DELETE FROM unit_resources WHERE unit_id = ?d', $id);
            Indexer::queueAsync(Indexer::REQUEST_REMOVE, Indexer::RESOURCE_UNIT, $id);
            Indexer::queueAsync(Indexer::REQUEST_REMOVEBYUNIT, Indexer::RESOURCE_UNITRESOURCE, $id);
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
            CourseXMLElement::refreshCourse($course_id, $course_code);
            $main_content .= "<div class='alert alert-success'>$langCourseUnitDeleted</div>";
        } else {
            $res_id = intval($_GET['del']);
            if (($id = check_admin_unit_resource($res_id))) {
                Database::get()->query("DELETE FROM course_weekly_view_activities WHERE id = ?d", $res_id);
                Indexer::queueAsync(Indexer::REQUEST_REMOVE, Indexer::RESOURCE_UNITRESOURCE, $res_id);
                Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
                CourseXMLElement::refreshCourse($course_id, $course_code);
                $tool_content .= "<div class='alert alert-success'>$langResourceCourseUnitDeleted</div>";
            }
        }
    } elseif (isset($_REQUEST['vis'])) { // modify visibility
        $id = intval($_REQUEST['vis']);
        $vis = Database::get()->querySingle("SELECT `visible` FROM course_units WHERE id = ?d", $id)->visible;
        $newvis = ($vis == 1) ? 0 : 1;
        Database::get()->query("UPDATE course_units SET visible = ?d WHERE id = ?d AND course_id = ?d", $newvis, $id, $course_id);
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNIT, $id);
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
        CourseXMLElement::refreshCourse($course_id, $course_code);
    } elseif (isset($_REQUEST['access'])) {
        if ($course_viewType == 'weekly') {
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
        if ($course_info->view_type == 'units' or $course_info->view_type == 'simple') {
            move_order('course_units', 'id', $id, 'order', 'down', "course_id=$course_id");
        } else {
            $res_id = intval($_REQUEST['down']);
            if (($id = check_admin_unit_resource($res_id))) {
                move_order('course_weekly_view_activities', 'id', $res_id, 'order', 'down', "course_weekly_view_id=$id");
            }
        }
    } elseif (isset($_REQUEST['up'])) { // change order up
        $id = intval($_REQUEST['up']);
        if ($course_info->view_type == 'units' or $course_info->view_type == 'simple') {
            move_order('course_units', 'id', $id, 'order', 'up', "course_id=$course_id");
        } else {
            $res_id = intval($_REQUEST['up']);
            if (($id = check_admin_unit_resource($res_id))) {
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
//style='color:#999999; font-size:13px;'
$bar_content .= "<b>" . $langCode . ":</b> " . q($public_code) . "" .
                "<br><b>" . $langFaculty . ":</b> ";

$departments = $course->getDepartmentIds($course_id);
$i = 1;
foreach ($departments as $dep) {
    $br = ($i < count($departments)) ? '<br>' : '';
    $bar_content .= $tree->getFullPath($dep) . $br;
    $i++;
}

$numUsers = Database::get()->querySingle("SELECT COUNT(user_id) AS numUsers
                FROM course_user
                WHERE course_id = ?d", $course_id)->numUsers;

//set the lang var for lessons visibility status
switch ($visible) {
    case COURSE_CLOSED: {
            $lessonStatus = "<span title='$langClosedCourseShort'>$langPrivateShort</span>";
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
            $lessonStatus = "<span title='$langCourseInactiveShort'>$langCourseInactive</span>";
            break;
        }
}
$bar_content_2 = "<br><b>$langConfidentiality:</b> $lessonStatus";
if ($is_course_admin) {
    $link = "<a href='{$urlAppend}modules/user/?course=$course_code'>$numUsers $langRegistered</a>";
} else {
    $link = "$numUsers $langRegistered";
}
$bar_content_2 .= "<br><b>$langUsers:</b> $link";

// display course license
if ($course_license) {
    $license_info_box = "<small>" . copyright_info($course_id) . "</small>";
} else {
    $license_info_box = '';
}

// display opencourses level in bar
require_once 'modules/course_metadata/CourseXML.php';
$level = ($levres = Database::get()->querySingle("SELECT level FROM course_review WHERE course_id =  ?d", $course_id)) ? CourseXMLElement::getLevel($levres->level) : false;
if (isset($level) && !empty($level)) {
    $metadataUrl = $urlServer . 'modules/course_metadata/info.php?course=' . $course_code;
    $head_content .= "
<link rel='stylesheet' type='text/css' href='course_metadata.css'>
<style type='text/css'></style>
<script type='text/javascript'>
/* <![CDATA[ */

    var dialog;
    
    var showMetadata = function(course) {
        $('.modal-body', dialog).load('../../modules/course_metadata/anoninfo.php', {course: course}, function(response, status, xhr) {
            if (status === 'error') {
                $('.modal-body', dialog).html('Sorry but there was an error, please try again');
            }
        });
        dialog.modal('show');
    };
        
    $(document).ready(function() {
        dialog = $(\"<div class='modal fade' tabindex='-1' role='dialog' aria-labelledby='modal-label' aria-hidden='true'><div class='modal-dialog modal-lg'><div class='modal-content'><div class='modal-header'><button type='button' class='close' data-dismiss='modal'><span aria-hidden='true'>&times;</span><span class='sr-only'>{$langCancel}</span></button><h4 class='modal-title' id='modal-label'>{$langCourseMetadata}</h4></div><div class='modal-body'>body</div></div></div></div>\");
    });

/* ]]> */
</script>";   
    $opencourses_level = "
    <div class='row'>
        <div class='col-md-4'>
            <img src='$themeimg/open_courses_logo_small.png' title='" . $langOpenCourses . "' alt='" . $langOpenCourses . "' />
        </div>
        <div class='col-md-8 margin-top-thin'>
            ${langOpenCoursesLevel}: $level
            <br />
            <small><a href='javascript:showMetadata(\"$course_code\");'>$langCourseMetadata " .
            icon('fa-tags', $langCourseMetadata, "javascript:showMetadata(\"$course_code\");") . "</small>
        </div>
    </div>
";
}

// display `contact teacher via email` link if teacher actually receives email from his course
/*$receive_mail = FALSE;
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
*/
// Contentbox: Course main contentbox
//Temp commit (waiting for Alex to fix course description issue) 
if ($course_info->home_layout == 3) {
    $left_column = '';
    $main_content_cols = '';
    $horizontal_info_row =
                "<div class='col-xs-12'>
                    <hr class='margin-top-fat margin-bottom-fat'>
                </div>
                <div class='col-xs-12 form-wrapper'>
                <div class='row'>
                <div class='col-xs-8'>              
                     $bar_content
                     $bar_content_2
                </div>
                <div class='col-xs-4 text-center'> 
                    $license_info_box
                </div></div></div>";    

} else {
    if(!empty($license_info_box)){

        $license_holder = "<hr class='col-xs-12 margin-top-slim margin-bottom-fat'>
                        <div class ='col-xs-12 text-center margin-top-fat'>
                           $license_info_box
                        </div>
                        <hr class='col-xs-12 margin-top-fat margin-bottom-fat visible-xs-block'>";
    } else {
        $license_holder = "";
    }           
   $left_column = "
            <div class='banner-image-wrapper col-md-5 col-sm-5 col-xs-12'>";
   if ($course_info->home_layout == 1) {
       $course_image_url = isset($course_info->course_image) ? "{$urlAppend}courses/$course_code/image/$course_info->course_image" : "$themeimg/ph1.jpg";
       $left_column .= "
                <div>
                    <img class='banner-image img-responsive' src='$course_image_url'/>
                </div>
                <hr class='col-xs-12 margin-top-fat margin-bottom-fat'>";
   }
    $left_column .= "
                <div class='col-xs-12 form-wrapper'>              
                     $bar_content
                     $bar_content_2
                </div>               
                $license_holder
            </div>";
   $horizontal_info_row = '';
   $main_content_cols = 'col-sm-7';
}
$edit_link = "";
if ($is_editor) {
    $edit_link = "
    <a href='{$urlAppend}modules/course_home/editdesc.php?course=$course_code' data-toggle='tooltip' data-placement='top' title='$langCourseInformationText'><i class='pull-left fa fa-edit fa'></i></a>";
} else {
    $edit_link = " ";
}
$tool_content .= "
<div class='row margin-top-thin margin-bottom-fat'>
    <div class='col-md-12'>
            <div class='panel panel-default'>
                
                <div class='panel-body'>
                            $left_column
                            <div class='col-xs-12 $main_content_cols'>
                                <div class='course-info-title clearfix'>
                                <h4 class='pull-left'>$langCourseDescriptionShort</h4><div class='pull-left edit-course-title'>$edit_link</div><div class='info-course-btn pull-right'>
                      $course_info_btn
                    </div>
                            </div>
                                <div class=''>$main_content</div>
                            </div>
                            $horizontal_info_row
                </div>
                
                $panel_footer
            </div>
        </div>
</div>
";


if ($is_editor) {
    $last_id = Database::get()->querySingle("SELECT id FROM course_units
                                                   WHERE course_id = ?d AND `order` >= 0
                                                   ORDER BY `order` DESC LIMIT 1", $course_id);
    if ($last_id) {
        $last_id = $last_id->id;
    }
    if ($course_info->view_type == 'weekly') {
        $query = "SELECT id, start_week, finish_week, visible, title, comments, public FROM course_weekly_view WHERE course_id = ?d";
    } else {
        $query = "SELECT id, title, comments, visible, public FROM course_units WHERE course_id = ?d AND `order` >= 0 ORDER BY `order`";
    }
} else {
    if ($course_info->view_type == 'weekly') {
        $query = "SELECT id, start_week, finish_week, visible, title, comments, public FROM course_weekly_view WHERE course_id = ?d AND visible = 1";
    } else {
        $query = "SELECT id, title, comments, visible, public FROM course_units WHERE course_id = ?d AND visible = 1 AND `order` >= 0 ORDER BY `order`";
    }
}

    $sql = Database::get()->queryArray($query, $course_id);
    $total_cunits = count($sql);    
    if ($total_cunits > 0) {        
        $cunits_content .= "";
        $count_index = 0;
        foreach ($sql as $cu) {
            if ($cu->visible == 1) {
               $count_index++;
            }
            // access status
            $access = $cu->public;
            // Visibility icon
            $vis = $cu->visible;
            $icon_vis = ($vis == 1) ? 'visible.png' : 'invisible.png';
            $class_vis = ($vis == 0) ? 'not_visible' : '';
            if ($course_info->view_type == 'weekly') {
                if (!empty($cu->title)) {
                    $cwtitle = "" . q($cu->title) . " ($langFrom2 ".nice_format($cu->start_week)." $langTill ".nice_format($cu->finish_week).")";                    
                } else {
                    $cwtitle = "$count_index$langOr $langsWeek ($langFrom2 ".nice_format($cu->start_week)." $langTill ".nice_format($cu->finish_week).")"; 
                }                
                $href = "<a class = '$class_vis' href='${urlServer}modules/weeks/?course=$course_code&amp;id=$cu->id&amp;cnt=$count_index'>$cwtitle</a>";
            } else {
                $href = "<a class='$class_vis' href='${urlServer}modules/units/?course=$course_code&amp;id=$cu->id'>" . q($cu->title) . "</a>";
            }
            $cunits_content .= "<div class='col-xs-12'><div class='panel clearfix'><div class='col-xs-12'>
                                    <div class='item-content'>
                                        <div class='item-header'>
                                            <h4 class='item-title'>$href</h4>";
                                if ($is_editor) {
                if ($course_info->view_type == 'weekly') { // actions for course weekly format
                    $cunits_content .= "<div class='item-side'>" .
                    action_button(array(
                        array('title' => $langEditChange,
                              'url' => $urlAppend . "modules/weeks/info.php?course=$course_code&amp;edit=$cu->id",
                              'icon' => 'fa-edit'),
                        array('title' => $vis == 1? $langViewHide : $langViewShow,
                              'url' => "$_SERVER[SCRIPT_NAME]?vis=$cu->id",
                              'icon' => $vis == 1? 'fa-eye-slash' : 'fa-eye'),
                        array('title' => $access == 1? $langResourceAccessLock : $langResourceAccessUnlock,
                              'url' => "$_SERVER[SCRIPT_NAME]?access=$cu->id",
                              'icon' => $access == 1? 'fa-lock' : 'fa-unlock',
                              'show' => $visible == COURSE_OPEN),)) .
                    '</div>';                    
                } else {
                    $cunits_content .= "<div class='item-side'>" .
                    action_button(array(
                        array('title' => $langEditChange,
                              'url' => $urlAppend . "modules/units/info.php?course=$course_code&amp;edit=$cu->id",
                              'icon' => 'fa-edit'),
                        array('title' => $langDown,
                              'level' => 'primary',
                              'url' => "$_SERVER[SCRIPT_NAME]?down=$cu->id",
                              'icon' => 'fa-arrow-down',
                              'disabled' => $cu->id == $last_id),
                        array('title' => $langUp,
                              'level' => 'primary',
                              'url' => "$_SERVER[SCRIPT_NAME]?up=$cu->id",
                              'icon' => 'fa-arrow-up',
                              'disabled' => $count_index == 1),
                        array('title' => $vis == 1? $langViewHide : $langViewShow,
                              'url' => "$_SERVER[SCRIPT_NAME]?vis=$cu->id",
                              'icon' => $vis == 1? 'fa-eye-slash' : 'fa-eye'),
                        array('title' => $access == 1? $langResourceAccessLock : $langResourceAccessUnlock,
                              'url' => "$_SERVER[SCRIPT_NAME]?access=$cu->id",
                              'icon' => $access == 1? 'fa-lock' : 'fa-unlock',
                              'show' => $visible == COURSE_OPEN),
                        array('title' => $langDelete,
                              'url' => "$_SERVER[SCRIPT_NAME]?del=$cu->id",
                              'icon' => 'fa-times',
                              'class' => 'delete',
                              'confirm' => $langCourseUnitDeleteConfirm))) .
                    '</div>';
                }
            }
                        $cunits_content .= "</div>	
                                        <div class='item-body'>
                                            $cu->comments
                                        </div>			
                                    </div>";
            $cunits_content .= "</div></div></div>";            
        }
    } else {
        $cunits_content .= "<div class='col-sm-12'><div class='panel'><div class='panel-body not_visible'> - $langNoUnits - </div></div></div>";
    }

// Contentbox: Thematikes enotites
// Contentbox: Calendar
// Contentbox: Announcements
if (($total_cunits > 0 or $is_editor) and ($course_info->view_type != 'simple')) {
    $alter_layout = FALSE;
    $cunits_sidebar_columns = 4;
    $cunits_sidebar_subcolumns = 12;
} else {
    $alter_layout = TRUE;
    $cunits_sidebar_columns = 12;
    $cunits_sidebar_subcolumns = 6;
}
$tool_content .= "<div class='row'>";
//if (!$alter_layout or $course_info->view_type != 'simple') {
if (!$alter_layout) {
    $unititle = ($course_info->view_type == 'weekly')? $langCourseWeeklyFormat : $langCourseUnits ;
    $tool_content .= "
    <div class='col-md-8 course-units'>
        <div class='row'>
            <div class='col-md-12'>
                <h3 class='content-title  pull-left'>$unititle</h3>
            ";
            
        if ($is_editor and $course_info->view_type == 'units') {            
            $link = "{$urlServer}modules/units/info.php?course=$course_code";
            $tool_content .= "<a href='$link' class='pull-left add-unit-btn' data-toggle='tooltip' data-placement='top' title='$langAddUnit'>
                                <i class='fa fa-plus-circle'></i>
                            </a>";           
        }
            
        $tool_content .= "</div></div>";
        $tool_content .= "<div class='row boxlist no-list'>
            $cunits_content
        </div>";
    $tool_content .= "</div>";
}

$tool_content .= "<div class='sssssss col-md-$cunits_sidebar_columns'>";

//BEGIN - Get user personal calendar
$today = getdate();
$day = $today['mday'];
$month = $today['mon'];
$year = $today['year'];
Calendar_Events::get_calendar_settings();
$user_personal_calendar = Calendar_Events::small_month_calendar($day, $month, $year);
//END - Get personal calendar

$tool_content .="<div class='row'>";
        // display open course level if exist
            if (isset($level) && !empty($level)) {
                $tool_content .= "
                    <div class='col-md-$cunits_sidebar_subcolumns'>
                        <h3 class='content-title'>$langOpenCourseShort</h3>
                        <div class='panel'>
                            <div class='panel-body'>
                                $opencourses_level
                            </div>
                        </div>
                    </div>
                ";
            }
                $tool_content .= "<div class='col-md-$cunits_sidebar_subcolumns'>
                    <h3 class='content-title'>$langCalendar</h3>
                    <div class='panel'>
                        <div class='panel-body'>
                            $user_personal_calendar
                        </div>
                        <div class='panel-footer'>
                            <div class='row'>
                                <div class='col-sm-6 event-legend'>
                                <div>
                                    <span class='event event-important'></span><span>$langAgendaDueDay</span>
                                </div>
                                <div>
                                    <span class='event event-info'></span><span>$langAgendaCourseEvent</span>
                                </div>
                            </div>
                            <div class='col-sm-6 event-legend'>
                                <div>
                                    <span class='event event-success'></span><span>$langAgendaSystemEvent</span>
                                </div>
                                <div>
                                    <span class='event event-special'></span><span>$langAgendaPersonalEvent</span>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='col-md-$cunits_sidebar_subcolumns'>
                                <h3 class='content-title'>$langAnnouncements</h3>
                                <div class='panel'>
                                    <div class='panel-body'>
                                        <ul class='tablelist'>" . course_announcements() . "
                                        </ul>
                                    </div>
                                    <div class='panel-footer clearfix'>
                                        <div class='pull-right'><a href='{$urlAppend}modules/announcements/?course=$course_code'><small>$langMore&hellip;</small></a></div>
                                    </div>
                                </div>
                            </div>
                       </div>
                       </div>
                </div>";

draw($tool_content, 2, null, $head_content);

/**
 * @brief fetch course announcements
 * @global type $course_id
 * @global type $course_code
 * @global type $langNoAnnounce
 * @global type $urlAppend
 * @global type $dateFormatLong
 * @return string
 */
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
                $ann_content .= "<li class='list-item'>
                                    <span class='item-wholeline'><div class='text-title'><a href='$ann_url'>" . q(ellipsize($ann->title, 60)) ."</a></div>$ann_date</span>
                                </li>";
            }
            return $ann_content;
        }
    }
    return "<li class='list-item'><span class='item-wholeline'><div class='text-title not_visible'> - $langNoAnnounce - </div></span></li>";
}
