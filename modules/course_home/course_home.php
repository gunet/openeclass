<?php

/* ========================================================================
 * Open eClass 4.0
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
require_once 'modules/course_metadata/CourseXML.php';
require_once 'modules/progress/process_functions.php';

doc_init();
$data['course_info'] = $course_info = Database::get()->querySingle("SELECT keywords, visible, prof_names, public_code, course_license, finish_date,
                                               view_type, start_date, finish_date, description, home_layout, course_image, password
                                          FROM course WHERE id = ?d", $course_id);

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
            Session::Messages($langCourseUnitDeleted, 'alert-success');
            redirect_to_home_page("courses/$course_code/");
        } else {
            $res_id = intval(getDirectReference($_GET['del']));
            if (($id = check_admin_unit_resource($res_id))) {
                Database::get()->query("DELETE FROM course_weekly_view_activities WHERE id = ?d", $res_id);
                Indexer::queueAsync(Indexer::REQUEST_REMOVE, Indexer::RESOURCE_UNITRESOURCE, $res_id);
                Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_COURSE, $course_id);
                CourseXMLElement::refreshCourse($course_id, $course_code);
                Session::Messages($langResourceCourseUnitDeleted, 'alert-success');
                redirect_to_home_page("courses/$course_code/");
            }
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
    } elseif (isset($_REQUEST['visW'])) { // modify visibility of the Week
        $id = intval(getDirectReference($_REQUEST['visW']));
        $vis = Database::get()->querySingle("SELECT `visible` FROM course_weekly_view WHERE id = ?d", $id)->visible;
        $newvis = ($vis == 1) ? 0 : 1;
        Database::get()->query("UPDATE course_weekly_view SET visible = ?d WHERE id = ?d AND course_id = ?d", $newvis, $id, $course_id);
        redirect_to_home_page("courses/$course_code/");
    } elseif (isset($_REQUEST['access'])) {
        if ($course_info->view_type == 'weekly') {
            $id = intval(getDirectReference($_REQUEST['access']));
            $access = Database::get()->querySingle("SELECT `public` FROM course_weekly_view WHERE id = ?d", $id);
            $newaccess = ($access->public == '1') ? '0' : '1';
            Database::get()->query("UPDATE course_weekly_view SET public = ?d WHERE id = ?d AND course_id = ?d", $newaccess, $id, $course_id);
        } else {
            $id = intval(getDirectReference($_REQUEST['access']));
            $access = Database::get()->querySingle("SELECT `public` FROM course_units WHERE id = ?d", $id);
            $newaccess = ($access->public == '1') ? '0' : '1';
            Database::get()->query("UPDATE course_units SET public = ?d WHERE id = ?d AND course_id = ?d", $newaccess, $id, $course_id);
        }
        redirect_to_home_page("courses/$course_code/");
    } elseif (isset($_REQUEST['down'])) {
        $id = intval(getDirectReference($_REQUEST['down'])); // change order down
        if ($course_info->view_type == 'units' or $course_info->view_type == 'simple') {
            move_order('course_units', 'id', $id, 'order', 'down', "course_id=$course_id");
        } else {
            $res_id = intval(getDirectReference($_REQUEST['down']));
            if (($id = check_admin_unit_resource($res_id))) {
                move_order('course_weekly_view_activities', 'id', $res_id, 'order', 'down', "course_weekly_view_id=$id");
            }
        }
        redirect_to_home_page("courses/$course_code/");
    } elseif (isset($_REQUEST['up'])) { // change order up
        $id = intval(getDirectReference($_REQUEST['up']));
        if ($course_info->view_type == 'units' or $course_info->view_type == 'simple') {
            move_order('course_units', 'id', $id, 'order', 'up', "course_id=$course_id");
        } else {
            $res_id = intval(getDirectReference($_REQUEST['up']));
            if (($id = check_admin_unit_resource($res_id))) {
                move_order('course_weekly_view_activities', 'id', $res_id, 'order', 'up', "course_weekly_view_id=$id");
            }
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
                Session::Messages($langNotifyRegUser1, 'alert-success');
            } else {
                Session::Messages($langInvalidCode, 'alert-warning');
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

load_js('tools.js');
if(!empty($langLanguageCode)){
    load_js('bootstrap-calendar-master/js/language/'.$langLanguageCode.'.js');
}
load_js('bootstrap-calendar-master/js/calendar.js');
load_js('bootstrap-calendar-master/components/underscore/underscore-min.js');

ModalBoxHelper::loadModalBox();
$head_content .= "<link rel='stylesheet' type='text/css' href='{$urlAppend}js/bootstrap-calendar-master/css/calendar_small.css' />
<script type='text/javascript'>
    $(document).ready(function() {  "
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
        });
        $(".modal").on("show.bs.modal", function(e){
            $("#lalou").popover("hide");
        });'

    ."})
    </script>";
 
$head_content .= "
        <script>
        $(function() {
            $('#help-btn').click(function(e) {
                e.preventDefault();
                $.get($(this).attr(\"href\"), function(data) {bootbox.alert(data);});
            });
        });
        </script>
        ";

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
if (!empty($course_info->password)) {
    $head_content .= "
        <script type='text/javascript'>
            $(function() {
                $('#passwordModal').on('click', function(e){
                    e.preventDefault();
                    bootbox.dialog({
                        title: '$langLessonCode',
                        message: '<form class=\"form-horizontal\" role=\"form\" action=\"\" method=\"POST\" id=\"password_form\">'+
                                    '<div class=\"form-group\">'+
                                        '<div class=\"col-sm-12\">'+
                                            '<input type=\"text\" class=\"form-control\" id=\"password\" name=\"password\">'+
                                            '<input type=\"hidden\" class=\"form-control\" id=\"register\" name=\"register\">'+
                                        '</div>'+
                                    '</div>'+
                                  '</form>',
                        buttons: {
                            cancel: {
                                label: '$langCancel',
                                className: 'btn-default'
                            },
                            success: {
                                label: '$langSubmit',
                                className: 'btn-success',
                                callback: function (d) {
                                    var password = $('#password').val();
                                    if(password != '') {
                                        $('#password_form').submit();
                                    } else {
                                        $('#password').closest('.form-group').addClass('has-error');
                                        $('#password').after('<span class=\"help-block\">$langTheFieldIsRequired</span>');
                                        return false;
                                    }
                                }
                            }
                        }
                    });
                })
            });
        </script>";
}
// For statistics: record login
Database::get()->query("INSERT INTO logins
    SET user_id = ?d, course_id = ?d, ip = ?s, date_time = " . DBHelper::timeAfter(),
    $uid, $course_id, $_SERVER['REMOTE_ADDR']);

// opencourses hits sumation
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

$visible = $course_info->visible;

$course_descriptions = Database::get()->queryArray("SELECT cd.id, cd.title, cd.comments, cd.type, cdt.icon FROM course_description cd
                                    LEFT JOIN course_description_type cdt ON (cd.type = cdt.id)
                                    WHERE cd.course_id = ?d AND cd.visible = 1 ORDER BY cd.order", $course_id);
$course_descriptions_modals = "";

if(count($course_descriptions)>0){
    $course_info_extra = "";
    foreach ($course_descriptions as $key => $course_description) {
        $hidden_id = "hidden_" . $key;
        $next_id = '';
        $previous_id = '';
        if ($key + 1 < count($course_descriptions)) $next_id = "hidden_" . ($key + 1);
        if ($key > 0) $previous_id = "hidden_" . ($key - 1);

        $course_descriptions_modals .=    "<div class='modal fade' id='$hidden_id' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                        <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
                                        <div class='modal-title h4' id='myModalLabel'>" . q($course_description->title) . "</div>
                                    </div>
                                    <div class='modal-body' style='max-height: calc(100vh - 210px); overflow-y: auto;'>".
                                      standard_text_escape($course_description->comments)
                                    ."</div>
                                    <div class='modal-footer'>";
                                        if ($previous_id) {
                                            $course_descriptions_modals .= "<a id='prev_btn' class='btn btn-default' data-dismiss='modal' data-toggle='modal' href='#$previous_id'><span class='fa fa-arrow-left'></span></a>";
                                        }
                                        if ($next_id) {
                                            $course_descriptions_modals .= "<a id='next_btn' class='btn btn-default' data-dismiss='modal' data-toggle='modal' href='#$next_id'><span class='fa fa-arrow-right'></span></a>";
                                        }
        $course_descriptions_modals .=    "
                                    </div>
                                  </div>
                                </div>
                              </div>";
        $course_info_extra .= "<a class='list-group-item' data-modal='syllabus-prof' data-toggle='modal' data-target='#$hidden_id' href='javascript:void(0);'>".q($course_description->title) ."</a>";
    }
} else {
    $course_info_extra = "<div class='text-muted'>$langNoInfoAvailable</div>";
}
$data['course_info_popover'] = "<div  class='list-group'>$course_info_extra</div>";
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


$data['departments'] = $course->getDepartmentIds($course_id);

$data['numUsers'] = Database::get()->querySingle("SELECT COUNT(user_id) AS numUsers
                FROM course_user
                WHERE course_id = ?d", $course_id)->numUsers;

//set the lang var for lessons visibility status
switch ($visible) {
    case COURSE_CLOSED: {
        $data['lessonStatus'] = "    <span class='fa fa-lock fa-fw' data-toggle='tooltip' data-placement='top' title='$langClosedCourseShort'></span><span class='hidden'>.</span>";
        break;
    }
    case COURSE_REGISTRATION: {
        $data['lessonStatus'] = "   <span class='fa fa-lock fa-fw' data-toggle='tooltip' data-placement='top' title='$langPrivOpen'>
                                <span class='fa fa-pencil text-danger fa-custom-lock'></span>
                            </span><span class='hidden'>.</span>";
        break;
    }
    case COURSE_OPEN: {
        $data['lessonStatus'] = "    <span class='fa fa-unlock fa-fw' data-toggle='tooltip' data-placement='top' title='$langPublic'></span><span class='hidden'>.</span>";
        break;
    }
    case COURSE_INACTIVE: {
        $data['lessonStatus'] = "    <span class='fa fa-lock fa-fw' data-toggle='tooltip' data-placement='top' title='$langCourseInactiveShort'>
                                <span class='fa fa-times text-danger fa-custom-lock'></span>
                             </span><span class='hidden'>.</span>";
        break;
    }
}

if ($uid and !$is_editor) {
    $data['course_completion_id'] = $course_completion_id = has_course_completion(); // is course completion enabled?
    if ($course_completion_id) {
        $course_completion_status = has_certificate_completed($uid, 'badge', $course_completion_id);        
        $data['percentage'] = $percentage = get_cert_percentage_completion('badge', $course_completion_id) . "%";         
    }
}
    
// display opencourses level in bar
$level = ($levres = Database::get()->querySingle("SELECT level FROM course_review WHERE course_id =  ?d", $course_id)) ? CourseXMLElement::getLevel($levres->level) : false;
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
        dialog = $(\"<div class='modal fade' tabindex='-1' role='dialog' aria-labelledby='modal-label' aria-hidden='true'><div class='modal-dialog modal-lg'><div class='modal-content'><div class='modal-header'><button type='button' class='close' data-dismiss='modal'><span aria-hidden='true'>&times;</span><span class='sr-only'>{$langCancel}</span></button><div class='modal-title h4' id='modal-label'>{$langCourseMetadata}</div></div><div class='modal-body'>body</div></div></div></div>\");
    });

/* ]]> */
</script>";                        
    $data['opencourses_level'] = "
        <div class='row'>
            <div class='col-xs-4'>
                <img class='img-responsive center-block' src='$themeimg/open_courses_logo_small.png' title='" . $langOpenCourses . "' alt='" . $langOpenCourses . "' />
            </div>
            <div class='col-xs-8'>
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
            <div class='col-xs-12 text-right'>
                <small><a href='javascript:showMetadata(\"$course_code\");'>$langCourseMetadata</a>".icon('fa-tags', $langCourseMetadata, "javascript:showMetadata(\"$course_code\");")."</small>
            </div>
        </div>";
}

if ($is_editor) {
    warnCourseInvalidDepartment(true);

} elseif ($uid) {
    $myCourses = [];
    Database::get()->queryFunc("SELECT course.code course_code, course.public_code public_code,
                                   course.id course_id, status
                              FROM course_user, course
                             WHERE course_user.course_id = course.id
                               AND user_id = ?d", function ($course) use (&$myCourses) {
        $myCourses[$course->course_id] = $course;
    }, $uid);
    if (!in_array($course_id, array_keys($myCourses))) {
        $action_bar = action_bar(array(
            array('title' => $langRegister,
                  'url' => "/courses/$course_code?register",
                  'icon' => 'fa-check',
                  'link-attrs' => !empty($course_info->password) ? "id='passwordModal'" : "",
                  'level' => 'primary-label',
                  'button-class' => 'btn-success')));
    }
}


if ($is_editor) {
    $data['last_id'] = $last_id = Database::get()->querySingle("SELECT id FROM course_units
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

    $data['course_units'] = $sql = Database::get()->queryArray($query, $course_id);
    $total_cunits = count($sql);

// Contentbox: Thematikes enotites
// Contentbox: Calendar
// Contentbox: Announcements
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
