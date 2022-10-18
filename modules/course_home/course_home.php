<?php

/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2019  Greek Universities Network - GUnet
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
require_once 'include/course_settings.php';
require_once 'include/log.class.php';
require_once 'modules/sharing/sharing.php';
require_once 'modules/rating/class.rating.php';
require_once 'modules/comments/class.comment.php';
require_once 'modules/comments/class.commenting.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'modules/document/doc_init.php';
require_once 'main/personal_calendar/calendar_events.class.php';
require_once 'modules/course_metadata/CourseXML.php';
require_once 'modules/progress/process_functions.php';
require_once 'modules/wall/wall_wrapper.php';

doc_init();
$tree = new Hierarchy();
$course = new Course();
$pageName = ''; // delete $pageName set in doc_init.php

$main_content = $cunits_content = $course_info_extra = "";

add_units_navigation(TRUE);

load_js('tools.js');

$data['course_info'] = $course_info = Database::get()->querySingle("SELECT keywords, visible, prof_names, public_code, course_license, finish_date,
                                               view_type, start_date, finish_date, description, home_layout, course_image, password
                                          FROM course WHERE id = ?d", $course_id);

// Handle unit reordering
if ($is_editor and isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    if (isset($_POST['toReorder'])) {
        reorder_table('course_units', 'course_id', $course_id, $_POST['toReorder'],
            isset($_POST['prevReorder'])? $_POST['prevReorder']: null);
        exit;
    }
}

// other actions in course unit
if ($is_editor) {
    // update index and refresh course metadata
    require_once 'modules/search/indexer.class.php';

    if (isset($_REQUEST['del'])) { // delete course unit
        $id = intval(getDirectReference($_REQUEST['del']));
        if ($course_info->view_type == 'units') {
            Database::get()->query('DELETE FROM course_units WHERE id = ?d', $id);
            Database::get()->query('DELETE FROM unit_resources WHERE unit_id = ?d', $id);
            Indexer::queueAsync(Indexer::REQUEST_REMOVE, Indexer::RESOURCE_UNIT, $id);
            Indexer::queueAsync(Indexer::REQUEST_REMOVEBYUNIT, Indexer::RESOURCE_UNITRESOURCE, $id);
            Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
            CourseXMLElement::refreshCourse($course_id, $course_code);
            //Session::Messages($langCourseUnitDeleted, 'alert-success');
            Session::flash('message',$langCourseUnitDeleted);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("courses/$course_code/");
        }
    } elseif (isset($_REQUEST['vis'])) { // modify visibility
        $id = intval(getDirectReference($_REQUEST['vis']));
        $vis = Database::get()->querySingle("SELECT `visible` FROM course_units WHERE id = ?d", $id)->visible;
        $newvis = ($vis == 1) ? 0 : 1;
        Database::get()->query("UPDATE course_units SET visible = ?d WHERE id = ?d AND course_id = ?d", $newvis, $id, $course_id);
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_UNIT, $id);
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
        CourseXMLElement::refreshCourse($course_id, $course_code);
        redirect_to_home_page("courses/$course_code/");
    } elseif (isset($_REQUEST['access'])) {
        $id = intval(getDirectReference($_REQUEST['access']));
        $access = Database::get()->querySingle("SELECT `public` FROM course_units WHERE id = ?d", $id);
        $newaccess = ($access->public == '1') ? '0' : '1';
        Database::get()->query("UPDATE course_units SET public = ?d WHERE id = ?d AND course_id = ?d", $newaccess, $id, $course_id);
        redirect_to_home_page("courses/$course_code/");
    } elseif (isset($_REQUEST['down'])) {
        $id = intval(getDirectReference($_REQUEST['down'])); // change order down
        if ($course_info->view_type == 'units' or $course_info->view_type == 'simple') {
            move_order('course_units', 'id', $id, 'order', 'down', "course_id=$course_id");
        }
        redirect_to_home_page("courses/$course_code/");
    } elseif (isset($_REQUEST['up'])) { // change order up
        $id = intval(getDirectReference($_REQUEST['up']));
        //$id = intval($_REQUEST['up']);
        if ($course_info->view_type == 'units' or $course_info->view_type == 'simple') {
            move_order('course_units', 'id', $id, 'order', 'up', "course_id=$course_id");
        }
        redirect_to_home_page("courses/$course_code/");
    }
}

// Student Register to course
if (isset($_REQUEST['register'])) {
    if ($course_info) {
        $allow_reg = $course_info->visible == COURSE_REGISTRATION
                     || $course_info->visible == COURSE_OPEN;
        if ($allow_reg) {
            if (empty($course_info->password) || $course_info->password == $_POST['password']) {
                Database::get()->query("INSERT IGNORE INTO `course_user` (`course_id`, `user_id`, `status`, `reg_date`)
                                    VALUES (?d, ?d, ?d, NOW())", $course_id, $uid, USER_STUDENT);
                //Session::Messages($langNotifyRegUser1, 'alert-success');
                Session::flash('message',$langNotifyRegUser1);
                Session::flash('alert-class', 'alert-success');
            } else {
                //Session::Messages($langInvalidCode, 'alert-warning');
                Session::flash('message',$langInvalidCode);
                Session::flash('alert-class', 'alert-warning');
            }
        }
        redirect_to_home_page("courses/$course_code");
    }
}

$data['tree'] = new Hierarchy();
$course = new Course();

$pageName = ''; // delete $pageName set in doc_init.php
$require_help = TRUE;
$helpTopic = 'course_home';

add_units_navigation(TRUE);

load_js('bootstrap-calendar');
load_js('bootstrap-calendar-master/components/underscore/underscore-min.js');
load_js('sortable/Sortable.min.js');

ModalBoxHelper::loadModalBox();
$head_content .= "
<script type='text/javascript'>
    $(document).ready(function() {
        $('#btn-syllabus').click(function () {
            $(this).find('.fa-chevron-right').toggleClass('fa-rotate-90');
    });";

// Units sorting
// if ($is_editor and $course_info->view_type == 'units') {
//     $head_content .= '
//         Sortable.create(boxlistSort, {
//             animation: 350,
//             handle: \'.fa-arrows\',
//             animation: 150,
//             onUpdate: function (evt) {
//                 var itemEl = $(evt.item);
//                 var idReorder = itemEl.attr(\'data-id\');
//                 var prevIdReorder = itemEl.prev().attr(\'data-id\');

//                 $.ajax({
//                   type: \'post\',
//                   dataType: \'text\',
//                   data: {
//                       toReorder: idReorder,
//                       prevReorder: prevIdReorder,
//                   }
//                 });
//             }
//         });';
// }

// Calendar stuff
$head_content .= 'var calendar = $("#bootstrapcalendar").calendar({
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
$registerUrl = js_escape($urlAppend . 'modules/course_home/register.php?course=' . $course_code);

// For statistics: record login
Database::get()->query("INSERT INTO logins
    SET user_id = ?d, course_id = ?d, ip = ?s, date_time = " . DBHelper::timeAfter(),
    $uid, $course_id, Log::get_client_ip());

// opencourses hits summation
$visitsopencourses = 0;
$hitsopencourses = 0;
if (get_config('opencourses_enable')) {
    $cxml = CourseXMLElement::initFromFile($course_code);
    $reslastupdate = Database::get()->querySingle("select datestamp from oai_record where course_id = ?d and deleted = ?d", $course_id, 0);
    $lastupdate = null;
    if ($reslastupdate) {
        $lastupdate = strtotime($reslastupdate->datestamp);
    }
    if ($cxml && $lastupdate && (time() - $lastupdate > 24 * 60 * 60)) {
        // need to refresh hits when no update occurred during the last 24 hours
        CourseXMLElement::refreshCourse($course_id, $course_code);
        $cxml = CourseXMLElement::initFromFile($course_code);
    }
    $visitsopencourses = ($cxml && $cxml->visits) ? intval((string) $cxml->visits) : 0;
    $hitsopencourses = ($cxml && $cxml->hits) ? intval((string) $cxml->hits) : 0;
}

$action = new action();
$action->record(MODULE_ID_UNITS);

if (isset($_GET['from_search'])) { // if we come from home page search
    header("Location: {$urlServer}modules/search/search_incourse.php?all=true&search_terms=$_GET[from_search]");
}

$visible = $data['visible'] = $course_info->visible;

$res = Database::get()->queryArray("SELECT cd.id, cd.title, cd.comments, cd.type, cdt.icon FROM course_description cd
                                    LEFT JOIN course_description_type cdt ON (cd.type = cdt.id)
                                    WHERE cd.course_id = ?d AND cd.visible = 1 ORDER BY cd.order", $course_id);
$course_descriptions_modals = "";


$head_content .= "
<script>
    $(function() {
        $('body').keydown(function(e) {
            if(e.keyCode == 37 || e.keyCode == 39) {
                if ($('.modal.in').length) {
                    var visible_modal_id = $('.modal.in').attr('id').match(/\d+/);
                    if (e.keyCode == 37) {
                        var new_modal_id = parseInt(visible_modal_id) - 1;
                    } else {
                        var new_modal_id = parseInt(visible_modal_id) + 1;
                    }
                    var new_modal = $('#hidden_'+new_modal_id);
                    if (new_modal.length) {
                        hideVisibleModal();
                        new_modal.modal('show');
                    }
                }
            }
        });
    });
    function hideVisibleModal(){
        var visible_modal = $('.modal.in');
        if (visible_modal) { // modal is active
            visible_modal.modal('hide'); // close modal
        }
    };
</script>";

if (count($res) > 0) {
    foreach ($res as $key => $row) {
        $desctype = intval($row->type) - 1;
        $hidden_id = "hidden_" . $key;
        $next_id = '';
        $previous_id = '';
        if ($key + 1 < count($res)) $next_id = "hidden_" . ($key + 1);
        if ($key > 0) $previous_id = "hidden_" . ($key - 1);

        $course_descriptions_modals .= "<div class='modal fade' id='$hidden_id' tabindex='-1' role='dialog' aria-labelledby='myModalLabel_$key' aria-hidden='true'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                        <button type='button' class='close' data-bs-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
                                        <div class='modal-title h4' id='myModalLabel_$key'>" . q($row->title) . "</div>
                                    </div>
                                    <div class='modal-body' style='max-height: calc(100vh - 210px); overflow-y: auto;'>".
                                        standard_text_escape($row->comments)
                                    ."</div>
                                    <div class='modal-footer'>";
                                        if ($previous_id) {
                                            $course_descriptions_modals .= "<a id='prev_btn' class='btn btn-secondary' data-bs-dismiss='modal' data-bs-toggle='modal' href='#$previous_id'><span class='fa fa-arrow-left'></span></a>";
                                        }
                                        if ($next_id) {
                                            $course_descriptions_modals .= "<a id='next_btn' class='btn btn-secondary' data-bs-dismiss='modal' data-bs-toggle='modal' href='#$next_id'><span class='fa fa-arrow-right'></span></a>";
                                        }
        $course_descriptions_modals .=    "
                                    </div>
                                    </div>
                                </div>
                                </div>";
        $course_info_extra .= "<a class='list-group-item' data-bs-modal='syllabus-prof' data-bs-toggle='modal' data-bs-target='#$hidden_id' href='javascript:void(0);'>".q($row->title) ."</a>";
    }
} else {
    $course_info_extra = "<div class='text-muted'>$langNoInfoAvailable</div>";
}

$data['course_info_popover'] = "<div class='list-group'>$course_info_extra</div>";
$data['course_descriptions_modals'] = $course_descriptions_modals;

if ($course_info->description) {
    $description = standard_text_escape($course_info->description);

    // Text button for read more & read less
    $postfix_truncate_more = "<a href='#' class='more_less_btn'>$langReadMore &nbsp;<span class='fa fa-arrow-down'></span></a>";
    $postfix_truncate_less = "<a href='#' class='more_less_btn'>$langReadLess &nbsp;<span class='fa fa-arrow-up'></span></a>";

    // Create full description text & truncated text
    $data['full_description'] = $description.$postfix_truncate_less;
    $data['truncated_text'] = ellipsize_html($description, 1000, $postfix_truncate_more);
}

// offline course setting
$data['offline_course'] = get_config('offline_course') && (setting_get(SETTING_OFFLINE_COURSE, $course_id));

if (setting_get(SETTING_COURSE_COMMENT_ENABLE, $course_id) == 1) {
    commenting_add_js();
    $comm = new Commenting('course', $course_id);
    $data['comment_content'] = $comm->put($course_code, $is_editor, $uid);
}
if (setting_get(SETTING_COURSE_RATING_ENABLE, $course_id) == 1) {
    $rating = new Rating('fivestar', 'course', $course_id);
    $data['rating_content'] = $rating->put($is_editor, $uid, $course_id);
}
if (is_sharing_allowed($course_id)) {
    if (setting_get(SETTING_COURSE_SHARING_ENABLE, $course_id) == 1) {
        $data['social_content'] = print_sharing_links($urlServer."courses/$course_code", $currentCourseName);
    }
}

$data['course_descriptions'] = $res;
$data['courseDescriptionVisible'] = $courseDescriptionVisible = count($res);
$data['edit_course_desc_link'] = '';
if ($is_editor) {
    if ($courseDescriptionVisible > 0) {
        $data['edit_course_desc_link'] = "&nbsp;&nbsp;" . icon('fa-pencil', $langCourseDescription,$urlAppend . "modules/course_description/index.php?course=" . $course_code);
    } else {
        $data['edit_course_desc_link'] = "&nbsp;&nbsp;" . icon('fa-plus', $langAdd,$urlAppend . "modules/course_description/index.php?course=" . $course_code);
    }
}

$data['departments'] = $course->getDepartmentIds($course_id);

$data['numUsers'] = Database::get()->querySingle("SELECT COUNT(user_id) AS numUsers
                FROM course_user
                WHERE course_id = ?d", $course_id)->numUsers;


//set the lang var for lessons visibility status
switch ($visible) {
    case COURSE_CLOSED: {
        $data['lessonStatus'] = "    <span class='fa fa-lock fa-fw' data-bs-toggle='tooltip' data-bs-placement='top' title='$langClosedCourseShort'></span><span class='hidden'>.</span>";
        break;
    }
    case COURSE_REGISTRATION: {
        $data['lessonStatus'] = "   <span class='fa fa-lock fa-fw' data-bs-toggle='tooltip' data-bs-placement='top' title='$langPrivOpen'>
                                <span class='fa fa-pencil text-danger fa-custom-lock'></span>
                            </span><span class='hidden'>.</span>";
        break;
    }
    case COURSE_OPEN: {
        $data['lessonStatus'] = "    <span class='fa fa-unlock fa-fw' data-bs-toggle='tooltip' data-bs-placement='top' title='$langPublic'></span><span class='hidden'>.</span>";
        break;
    }
    case COURSE_INACTIVE: {
        $data['lessonStatus'] = "    <span class='fa fa-lock fa-fw' data-bs-toggle='tooltip' data-bs-placement='top' title='$langCourseInactiveShort'>
                                <span class='fa fa-times text-danger fa-custom-lock'></span>
                             </span><span class='hidden'>.</span>";
        break;
    }
}


if ($uid) {
    $data['course_completion_id'] = $course_completion_id = is_course_completion_active(); // is course completion active?
    if ($course_completion_id) {
        if ($is_editor) {
            $certified_users = Database::get()->querySingle("SELECT COUNT(*) AS t FROM user_badge
                                                              JOIN course_user ON user_badge.user=course_user.user_id
                                                                    AND status = " .USER_STUDENT . "
                                                                    AND editor = 0
                                                                    AND course_id = ?d
                                                                    AND completed = 1
                                                                    AND badge = ?d", $course_id, $course_completion_id)->t;
        } else {
            $course_completion_status = has_certificate_completed($uid, 'badge', $course_completion_id);
            $data['percentage'] = $percentage = get_cert_percentage_completion('badge', $course_completion_id) . "%";
        }
    }
}



// display opencourses level in bar
$level = ($levres = Database::get()->querySingle("SELECT level FROM course_review WHERE course_id =  ?d", $course_id)) ? CourseXMLElement::getLevel($levres->level) : false;
$data['level'] = $level;
if (isset($level) && !empty($level)) {
    $metadataUrl = $urlServer . 'modules/course_metadata/info.php?course=' . $course_code;
    $head_content .= "
<link rel='stylesheet' type='text/css' href='{$urlAppend}modules/course_metadata/course_metadata.css'>
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

        dialog = $(\"<div class='modal fade' tabindex='-1' role='dialog' aria-labelledby='modal-label' aria-hidden='true'><div class='modal-dialog modal-lg'><div class='modal-content'><div class='modal-header'><button type='button' class='close' data-bs-dismiss='modal'><span aria-hidden='true'>&times;</span><span class='sr-only'>{$langCancel}</span></button><div class='modal-title h4' id='modal-label'>{$langCourseMetadata}</div></div><div class='modal-body'>body</div></div></div></div>\");
    });

/* ]]> */
</script>";
    $data['opencourses_level'] = "
        <div class='row'>
            <div class='col-12 d-flex justify-content-center'>
                <img class='img-responsive center-block' src='$themeimg/open_courses_logo_small.png' title='" . $langOpenCourses . "' alt='" . $langOpenCourses . "' />
            </div>
            <div class='col-12 mt-3'>
                <div style='border-bottom:1px solid #ccc; margin-bottom: 5px;'>${langOpenCoursesLevel}: $level</div>
                <p class='not_visible'>
                <small>$langVisitsShort : &nbsp;$visitsopencourses</small>
                <br />
                <small>$langHitsShort : &nbsp;$hitsopencourses</small>
                </p>
            </div>
        </div>";
    $data['opencourses_level_footer'] = "
        <div class='row'>
            <div class='col-12 text-center'>
                <small><a href='javascript:showMetadata(\"$course_code\");'>$langCourseMetadata</a>".icon('fa-tags', $langCourseMetadata, "javascript:showMetadata(\"$course_code\");")."</small>
            </div>
        </div>";
}



if ($is_editor) {
    warnCourseInvalidDepartment(true);
}

if ($is_editor) {
    $data['last_id'] = $last_id = Database::get()->querySingle("SELECT id FROM course_units
                                                   WHERE course_id = ?d AND `order` >= 0
                                                   ORDER BY `order` DESC LIMIT 1", $course_id);
    if ($last_id) {
        $last_id = $last_id->id;
    }
    $query = "SELECT id, title, start_week, finish_week, comments, visible, public, `order` FROM course_units WHERE course_id = ?d AND `order` >= 0 ORDER BY `order`";
} else {
    $query = "SELECT id, title, start_week, finish_week, comments, visible, public, `order` FROM course_units WHERE course_id = ?d AND visible = 1 AND `order` >= 0 ORDER BY `order`";
}

$data['all_units'] = $all_units = Database::get()->queryArray($query, $course_id);
foreach ($all_units as $unit) {
    check_unit_progress($unit->id);  // check unit completion - call to Game.php
}

$visible_units_id = [];
if (!$is_editor) {
    $visible_user_units = findUserVisibleUnits($uid, $all_units);
    foreach ($visible_user_units as $d) {
        $visible_units_id[] = $d->id;
    }
}

/****************** TEST CAROUSEL OR ROW UNITS PREFERASION ******************/
if ($is_editor){
    if (isset($_GET['viewUnit'])){
        Database::get()->query("UPDATE course SET view_units = ?d  WHERE id = ?d", $_GET['viewUnit'], $course_id);
    }
}
$show_course = Database::get()->querySingle("SELECT view_units FROM course WHERE id =  ?d", $course_id);
$carousel_or_row = $show_course->view_units; 
/***************************************************************************/

$total_cunits = count($all_units);
$data['total_cunits'] = $total_cunits;
if ($total_cunits > 0) {
    $cunits_content .= "";
    $count_index = 0;
    $counterUnits = 0;

    if($carousel_or_row == 0){
        $cunits_content .= "<div id='carouselUnitsControls' class='carousel slide' data-bs-ride='carousel'>";

        //this is foreach for indicatoras carousel-units
        $counterIndicator = 0;

        $cunits_content .=  "<div class='carousel-indicators h-auto mb-1'>";
        foreach ($all_units as $cu) {
            if($counterIndicator == 0){
                $cunits_content .=  "<button type='button' data-bs-target='#carouselUnitsControls' data-bs-slide-to='$counterIndicator' class='active' aria-current='true'></button>";
            }else{
                $cunits_content .=  "<button type='button' data-bs-target='#carouselUnitsControls' data-bs-slide-to='$counterIndicator' aria-current='true'></button>";
            }
            $counterIndicator++;
        }
        $cunits_content .=  "</div>";

        $cunits_content .= "<div class='carousel-inner'>";
        foreach ($all_units as $cu) {
            $not_shown = false;
            $icon = '';
                // check if course unit has started
            if (!$is_editor) {
                if (!(is_null($cu->start_week)) and (date('Y-m-d') < $cu->start_week)) {
                    $not_shown = true;
                    $icon = icon('fa-clock-o', $langUnitNotStarted);
                // or has completed units (if any)
                } else if (!in_array($cu->id, $visible_units_id)) {
                    $not_shown = true;
                    $icon = icon('fa-minus-circle', $langUnitNotCompleted);
                } else {

                    if (in_array($cu->id, $visible_units_id)) {
                        $sql_badge = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND unit_id = ?d", $course_id, $cu->id);
                        if ($sql_badge) {
                            $badge_id = $sql_badge->id;
                            $per = get_cert_percentage_completion('badge', $badge_id);
                            if ($per == 100) {
                                $icon = icon('fa-check-circle', $langInstallEnd);
                            } else {
                                $icon = icon('fa-hourglass-2', $per . "%");
                            }
                        }
                    }
                }
            }
            // check visibility
            if ($cu->visible == 1) {
                $count_index++;
            }
            $access = $cu->public;
            $vis = $cu->visible;
            $class_vis = ($vis == 0 or $not_shown) ? 'not_visible' : '';
            $cu_indirect = getIndirectReference($cu->id);

            if($counterUnits == 0){
                $cunits_content .= "<div class='carousel-item active'>";
            }else{
                $cunits_content .= "<div class='carousel-item'>";
            }
            $cunits_content .= "<div id='unit_$cu_indirect' class='col-12' data-id='$cu->id'><div class='panel clearfix'><div class='col-12'>
                <div class='item-content'>
                    <div class='item-header clearfix'>
                        <div class='item-title h4 $class_vis text-primary fs-6'>";
            if ($not_shown) {
                $cunits_content .= q($cu->title) ;
            } else {
                $cunits_content .= "<a class='$class_vis' href='${urlServer}modules/units/index.php?course=$course_code&amp;id=$cu->id'>" . q($cu->title) . "</a>";
            }
            $cunits_content .= "<br><small><span class='help-block'>";
            if (!(is_null($cu->start_week))) {
                $cunits_content .= "$langFrom2 " . format_locale_date(strtotime($cu->start_week), 'short', false);
            }
            if (!(is_null($cu->finish_week))) {
                $cunits_content .= " $langTill " . format_locale_date(strtotime($cu->finish_week), 'short', false);
            }
            $cunits_content .= "</span></small>";
            $cunits_content .= "</div>";

            $cunits_content .= "</div>
                <div class='item-body'>";
            if (!is_null($cu->comments)) {
                $cunits_content .= standard_text_escape($cu->comments);
            }
            $cunits_content .= "</div>";

            if ($is_editor) {

                $cunits_content .= "<div class='col-sm-12 mt-3 text-end'>".action_button(array(
                    array('title' => $langEditChange,
                        'url' => $urlAppend . "modules/units/info.php?course=$course_code&amp;edit=$cu->id",
                        'icon' => 'fa-edit'),
                    array('title' => $vis == 1? $langViewHide : $langViewShow,
                        'url' => $urlAppend . "modules/course_home/course_home.php?course=$course_code&amp;vis=$cu_indirect",
                        'icon' => $vis == 1? 'fa-eye-slash' : 'fa-eye'),
                    array('title' => $access == 1? $langResourceAccessLock : $langResourceAccessUnlock,
                        'url' => $urlAppend . "modules/course_home/course_home.php?course=$course_code&amp;access=$cu_indirect",
                        'icon' => $access == 1? 'fa-lock' : 'fa-unlock',
                        'show' => $visible == COURSE_OPEN),
                    array('title' => $langDelete,
                        'url' => $urlAppend . "modules/course_home/course_home.php?course=$course_code&amp;del=$cu_indirect&amp;order=".$cu->order,
                        'icon' => 'fa-trash',
                        'class' => 'delete',
                        'confirm' => $langCourseUnitDeleteConfirm)))."</div>";
            } else {
                $cunits_content .= "<div class='item-side' style='font-size: 20px;'>";
                $cunits_content .= $icon;
                $cunits_content .= "</div>";
            }

            $cunits_content .= "<hr>
                        <div class='col-sm-12 bg-transparent'>

                            <button class='carousel-prev-btn' type='button' data-bs-target='#carouselUnitsControls' data-bs-slide='prev'>
                                <span class='d-flex justify-content-center align-items-center fa fa-arrow-left text-primary'></span>
                            </button>";

            $cunits_content .=  "<button class='carousel-next-btn float-end' type='button' data-bs-target='#carouselUnitsControls' data-bs-slide='next'>
                                    <span class='d-flex justify-content-center align-items-center fa fa-arrow-right text-primary'></span>
                            </button>
                    
                        </div>";

            $cunits_content .= "</div></div></div></div></div>";
            $counterUnits++;
        }

        // end carousel-inner
        $cunits_content .= "</div>";



        //end courseUnitsControls
        $cunits_content .= "</div>";
    }else{
        $counter_hr = 0;
        foreach ($all_units as $cu) {
            $counter_hr++;
            $not_shown = false;
            $icon = '';
                // check if course unit has started
            if (!$is_editor) {
                if (!(is_null($cu->start_week)) and (date('Y-m-d') < $cu->start_week)) {
                    $not_shown = true;
                    $icon = icon('fa-clock-o', $langUnitNotStarted);
                // or has completed units (if any)
                } else if (!in_array($cu->id, $visible_units_id)) {
                    $not_shown = true;
                    $icon = icon('fa-minus-circle', $langUnitNotCompleted);
                } else {

                    if (in_array($cu->id, $visible_units_id)) {
                        $sql_badge = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND unit_id = ?d", $course_id, $cu->id);
                        if ($sql_badge) {
                            $badge_id = $sql_badge->id;
                            $per = get_cert_percentage_completion('badge', $badge_id);
                            if ($per == 100) {
                                $icon = icon('fa-check-circle', $langInstallEnd);
                            } else {
                                $icon = icon('fa-hourglass-2', $per . "%");
                            }
                        }
                    }
                }
            }
            // check visibility
            if ($cu->visible == 1) {
                $count_index++;
            }
            $access = $cu->public;
            $vis = $cu->visible;
            $class_vis = ($vis == 0 or $not_shown) ? 'not_visible' : '';
            $cu_indirect = getIndirectReference($cu->id);
            $cunits_content .= "<div id='unit_$cu_indirect' class='col-12' data-id='$cu->id'><div class='panel clearfix'><div class='col-12'>
                <div class='item-content'>
                    <div class='item-header clearfix'>
                        <div class='item-title h4 $class_vis text-primary fs-6'>";
            if ($not_shown) {
                $cunits_content .= q($cu->title) ;
            } else {
                $cunits_content .= "<a class='$class_vis' href='${urlServer}modules/units/index.php?course=$course_code&amp;id=$cu->id'>" . q($cu->title) . "</a>";
            }
            $cunits_content .= "<br><small><span class='help-block'>";
            if (!(is_null($cu->start_week))) {
                $cunits_content .= "$langFrom2 " . format_locale_date(strtotime($cu->start_week), 'short', false);
            }
            if (!(is_null($cu->finish_week))) {
                $cunits_content .= " $langTill " . format_locale_date(strtotime($cu->finish_week), 'short', false);
            }
            $cunits_content .= "</span></small>";
            $cunits_content .= "</div>";

            $cunits_content .= "</div>
                <div class='item-body'>";
            $cunits_content .= ($cu->comments == ' ')? '': standard_text_escape($cu->comments);
            $cunits_content .= "</div>";

            if ($is_editor) {

                $cunits_content .= "<div class='float-end'>".action_button(array(
                    array('title' => $langEditChange,
                        'url' => $urlAppend . "modules/units/info.php?course=$course_code&amp;edit=$cu->id",
                        'icon' => 'fa-edit'),
                    array('title' => $vis == 1? $langViewHide : $langViewShow,
                        'url' => $urlAppend . "modules/course_home/course_home.php?course=$course_code&amp;vis=$cu_indirect",
                        'icon' => $vis == 1? 'fa-eye-slash' : 'fa-eye'),
                    array('title' => $access == 1? $langResourceAccessLock : $langResourceAccessUnlock,
                        'url' => $urlAppend . "modules/course_home/course_home.php?course=$course_code&amp;access=$cu_indirect",
                        'icon' => $access == 1? 'fa-lock' : 'fa-unlock',
                        'show' => $visible == COURSE_OPEN),
                    array('title' => $langDelete,
                        'url' => $urlAppend . "modules/course_home/course_home.php?course=$course_code&amp;del=$cu_indirect&amp;order=".$cu->order,
                        'icon' => 'fa-trash',
                        'class' => 'delete',
                        'confirm' => $langCourseUnitDeleteConfirm)))."</div>";

            } else {
                $cunits_content .= "<div class='item-side' style='font-size: 20px;'>";
                $cunits_content .= $icon;
                $cunits_content .= "</div>";
            }

            $cunits_content .= "</div></div></div></div>";
            if($counter_hr <= count($all_units)-1){
                $cunits_content .= "<hr>";
            }
        }
    }

} else {
    $cunits_content .= "<div class='not_visible text-center'> - $langNoUnits - </div>";
}

$data['cunits_content'] = $cunits_content;

if (($total_cunits > 0 or $is_editor) and ($course_info->view_type != 'simple')) {
    $data['alter_layout'] = $alter_layout = FALSE;
    $data['cunits_sidebar_columns'] = $cunits_sidebar_columns = 4;
    $data['cunits_sidebar_subcolumns'] = $cunits_sidebar_subcolumns = 12;
} else {
    $data['alter_layout'] = $alter_layout = TRUE;
    $data['cunits_sidebar_columns'] = $cunits_sidebar_columns = 12;
    $data['cunits_sidebar_subcolumns'] = $cunits_sidebar_subcolumns = 6;
}

$data['course_home_main_area_widgets'] = '';
if (!$alter_layout) {
    $course_home_page_main = new \Widgets\WidgetArea(COURSE_HOME_PAGE_MAIN);
    foreach ($course_home_page_main->getCourseAndAdminWidgets($course_id) as $key => $widget) {
        $data['course_home_main_area_widgets'] .= $widget->run($key);
    }
}

//BEGIN - Get user personal calendar
$today = getdate();
$day = $today['mday'];
$month = $today['mon'];
$year = $today['year'];
if (isset($uid)) {
    Calendar_Events::get_calendar_settings();
}
$data['user_personal_calendar'] = Calendar_Events::small_month_calendar($day, $month, $year);
//END - Get personal calendar

$data['course_home_sidebar_widgets'] = '';
$course_home_page_sidebar = new \Widgets\WidgetArea(COURSE_HOME_PAGE_SIDEBAR);
foreach ($course_home_page_sidebar->getCourseAndAdminWidgets($course_id) as $key => $widget) {
    $data['course_home_sidebar_widgets'] .= $widget->run($key);
}

$data['edit_link'] = $data['action_bar'] = '';
$data['registered'] = false;
if ($is_editor) {
    warnCourseInvalidDepartment(true);
    $data['edit_link'] =
    "<div class='access access-edit pull-left'><a href='{$urlAppend}modules/course_home/editdesc.php?course=$course_code'>
        <span class='fa fa-pencil' style='line-height:30px;' data-bs-toggle='tooltip' data-bs-placement='top' title='Επεξεργασία Πληροφοριών'></span>
        <span class='hidden'>.</span></a>
    </div>";
}
else if ($uid) {
    $myCourses = [];
    Database::get()->queryFunc("SELECT course.code  course_code, course.public_code public_code,
                                        course.id course_id, status
                                        FROM course_user, course
                                        WHERE course_user.course_id = course.id
                                        AND user_id = ?d", function ($course) use (&$myCourses) {
                                            $myCourses[$course->course_id] = $course;
                                        }, $uid);
    if (!in_array($course_id, array_keys($myCourses))) {
        $data['action_bar'] = action_bar([[
            'title' => trans('langRegister'),
            'url' => $urlAppend . "modules/course_home/register.php?course=$course_code",
            'icon' => 'fa-check',
            'link-attrs' => "id='passwordModal'",
            'level' => 'primary-label',
            'button-class' => 'btn-success']]);
    } else {
        $data['registered'] = true;
    }
    $data['edit_link'] = '';
}

view('modules.course.home.index', $data);

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
    global $course_id, $course_code, $langNoAnnounce, $urlAppend, $dateFormatLong, $indexOfAnnounce;

    if (visible_module(MODULE_ID_ANNOUNCE)) {
        $q = Database::get()->queryArray("SELECT title, `date`, id
                            FROM announcement
                            WHERE course_id = ?d
                                AND visible = 1
                                AND (start_display <= NOW() OR start_display IS NULL)
                                AND (stop_display >= NOW() OR stop_display IS NULL)
                            ORDER BY `date` DESC LIMIT 5", $course_id);

        $typeViewOfCourse = Database::get()->queryArray("SELECT view_type FROM course WHERE id = ?d", $course_id);
        foreach($typeViewOfCourse as $t){
            $type_course = $t->view_type;
        }
        if($type_course == 'simple'){
            $indexOfAnnounce = 5;
        }else{
            $indexOfAnnounce = 3;
        }
        

        if ($q) { // if announcements exist
            $ann_content = '';
            $counter_ann = 1;
            foreach ($q as $ann) {
                if($counter_ann <= $indexOfAnnounce){
                $ann_url = $urlAppend . "modules/announcements/index.php?course=$course_code&amp;an_id=" . $ann->id;
                $ann_date = format_locale_date(strtotime($ann->date));
                $ann_content .= "<li class='list-group-item ps-0 pe-0'>
                                    <span class='item-wholeline'><div class='text-title'><a href='$ann_url'>" . q(ellipsize($ann->title, 60)) ."</a></div>$ann_date</span>
                                </li>";
                }
                $counter_ann++;
            }
            return $ann_content;
        }
    }
    return "<li style='list-style-type: none;' class='list-item pt-3 pb-3'><span class='item-wholeline'><div class='text-title text-center not_visible'> - $langNoAnnounce - </div></span></li>";
}
