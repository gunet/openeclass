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
 * @file portfolio.php
 * @brief This component creates the content of the start page when the user is logged in 
 */

$require_login = true;
define('HIDE_TOOL_TITLE', true);

include '../include/baseTheme.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';

$tree = new Hierarchy();
$user = new User();

//ModalBoxHelper::loadModalBox();

if(!empty($langLanguageCode)){
    load_js('bootstrap-calendar-master/js/language/'.$langLanguageCode.'.js');
}
load_js('bootstrap-calendar-master/js/calendar.js');
load_js('bootstrap-calendar-master/components/underscore/underscore-min.js');
load_js('datatables');

$head_content .= "
<link rel='stylesheet' type='text/css' href='{$urlAppend}js/bootstrap-calendar-master/css/calendar_small.css' />"
."<script type='text/javascript'>
jQuery(document).ready(function() {
  jQuery('#portfolio_lessons').DataTable({
    'bLengthChange': false,
    'iDisplayLength': 5,
    'bSort' : false,
    'fnDrawCallback': function( oSettings ) {
      $('#portfolio_lessons_filter label input').attr({
        class : 'form-control input-sm',
        placeholder : '$langSearch...'
      });
      $('#portfolio_lessons_filter label').prepend('<span class=\"sr-only\">$langSearch</span>')
    },
    'dom': '<\"all_courses\">frtip',
    'oLanguage': {
           'sLengthMenu':   '$langDisplay _MENU_ $langResults2',
           'sZeroRecords':  '".$langNoResult."',
           'sInfo':         '$langDisplayed _START_ $langTill _END_ $langFrom2 _TOTAL_ $langTotalResults',
           'sInfoEmpty':    '$langDisplayed 0 $langTill 0 $langFrom2 0 $langResults2',
           'sInfoFiltered': '',
           'sInfoPostFix':  '',
           'sSearch':       '',
           'sUrl':          '',
           'oPaginate': {
               'sFirst':    '&laquo;',
               'sPrevious': '&lsaquo;',
               'sNext':     '&rsaquo;',
               'sLast':     '&raquo;'
           }
       }    
  });
  $('div.all_courses').html('<a class=\"btn btn-xs btn-default\" href=\"{$urlServer}main/my_courses.php\">$langAllCourses</a>');  
  jQuery('.panel_title').click(function()
  {
    var mypanel = $(this).next();
    mypanel.slideToggle(100);
    if($(this).hasClass('active')) {
    $(this).removeClass('active');
    } else {
    $(this).addClass('active');
    }
  });"
  .'var calendar = $("#bootstrapcalendar").calendar({
                    tmpl_path: "'.$urlAppend.'js/bootstrap-calendar-master/tmpls/",
                    events_source: "'.$urlAppend.'main/calendar_data.php",
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
."});
".
'function show_month(day,month,year){
    $.get("calendar_data.php",{caltype:"small", day:day, month: month, year: year}, function(data){$("#smallcal").html(data);});    
}
</script>';

require_once 'perso.php';

$tool_content .= action_bar(array(
        array('title' => $langRegCourses, 
              'url' => $urlAppend . 'modules/auth/courses.php',
              'icon' => 'fa-check',
              'level' => 'primary-label',
              'button-class' => 'btn-success'),
    array('title' => $langCourseCreate,
              'url' => $urlAppend . 'modules/create_course/create_course.php',
              'show' => $_SESSION['status'] == USER_TEACHER,
              'icon' => 'fa-plus-circle',
              'level' => 'primary-label',
              'button-class' => 'btn-success')));

$tool_content .= "
    <div class='row'>
        <div id='my-courses' class='col-md-7'>
            <div class='row'>
                <div class='col-md-12'>
                    <div class='content-title h2'>".trans('langMyCourses')."</div>
                    <div class='panel'>
                        <div class='panel-body'>
                            $perso_tool_content[lessons_content]                       
                        </div>
                    </div>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-12 my-announcement-list'>
                    <div class='content-title h2'>".trans('langMyPersoAnnouncements')."</div>
                    <div class='panel'>
                        <div class='panel-body'>
                            <ul class='tablelist'>";
                                if(!empty($user_announcements)){
                                    $tool_content.=$user_announcements;
                                }else{
                                    $tool_content.="<li class='list-item' style='border-bottom:none;'><div class='text-title not_visible'> - $langNoRecentAnnounce - </div></li>";
                                }
                                $tool_content.="</ul>
                        </div>
                        <div class='panel-footer clearfix'>
                            <div class='pull-right'><a href='../modules/announcements/myannouncements.php'><small>$langMore&hellip;</small></a></div>
                        </div>
                    </div>
                </div>
            </div>";
        $portfolio_page_main = new \Widgets\WidgetArea(PORTFOLIO_PAGE_MAIN);
        foreach ($portfolio_page_main->getUserAndAdminWidgets($uid) as $key => $widget) {
            $tool_content .= $widget->run($key);
        }        
$tool_content .= "            
        </div>
    <div class='col-md-5'>
        <div class='row'>
            <div class='col-md-12'>
                <div class='content-title h2'>".trans('langMyAgenda')."</div>
                <div class='panel'>
                    <div class='panel-body'>
                        $perso_tool_content[personal_calendar_content]
                    </div>
                    <div class='panel-footer'>
                        <div class='row'>
                            <div class='col-sm-6 event-legend'>
                                <div>
                                    <span class='event event-important'></span><span>".trans('langAgendaDueDay')."</span>
                                </div>
                                <div>
                                    <span class='event event-info'></span><span>".trans('langAgendaCourseEvent')."</span>
                                </div>
                            </div>
                            <div class='col-sm-6 event-legend'>
                                <div>
                                    <span class='event event-success'></span><span>".trans('langAgendaSystemEvent')."</span>
                                </div>
                                <div>
                                    <span class='event event-special'></span><span>".trans('langAgendaPersonalEvent')."</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class='row'>
            <div class='col-md-12 my-messages-list'>
                <div class='content-title h2'>$langMyPersoMessages</div>
                <div class='panel'>
                    <div class='panel-body'>
                        <ul class='tablelist'>";
                        if(!empty($user_messages)){
                            $tool_content.=$user_messages;
                        }else{
                            $tool_content.="<li class='list-item' style='border-bottom:none;'><div class='text-title not_visible'> - $langDropboxNoMessage- </div></li>";
                        }
                        $tool_content.="</ul>
                    </div>
                    <div class='panel-footer clearfix'>
                        <div class='pull-right'><a href='{$urlAppend}modules/dropbox/'><small>$langMore&hellip;</small></a></div>
                    </div>
                </div>
            </div>
        </div>";
        $portfolio_page_sidebar = new \Widgets\WidgetArea(PORTFOLIO_PAGE_SIDEBAR);
        foreach ($portfolio_page_sidebar->getUserAndAdminWidgets($uid) as $key => $widget) {
            $tool_content .= $widget->run($key);
        }
$tool_content .= "        
    </div>";

$userdata = Database::get()->querySingle("SELECT surname, givenname, username, email, status, phone, am, registered_at,
                has_icon, description, password,
                email_public, phone_public, am_public
            FROM user
            WHERE id = ?d", $uid);
$numUsersRegistered = Database::get()->querySingle("SELECT COUNT(*) AS numUsers
        FROM course_user cu1, course_user cu2
        WHERE cu1.course_id = cu2.course_id AND cu1.user_id = ?d AND cu1.status = ?d AND cu2.status <> ?d;", $uid, USER_TEACHER, USER_TEACHER)->numUsers;
$lastVisit = Database::get()->querySingle("SELECT * FROM loginout
                        WHERE id_user = ?d ORDER by idLog DESC LIMIT 1", $uid);
if ($lastVisit) {
    $lastVisitLabel = "<br><span class='tag'>$langProfileLastVisit : </span><span class='tag-value text-muted'>" .
        claro_format_locale_date($dateFormatLong, strtotime($lastVisit->when)) . "</span>";
} else {
    $lastVisitLabel = '';
}

$tool_content .= "
</div>
<div id='profile_box' class='row'>
    <div class='col-md-12'>
        <div class='content-title h2'>$langCompactProfile</div>
        <div class='panel'>
            <div class='panel-body'>
                <div class='row'>
                    <div class='col-xs-4 col-sm-2'>
                        <img src='" . user_icon($uid, IMAGESIZE_LARGE) . "' style='width:80px;' class='img-circle center-block img-responsive' alt='$langProfileImage'><br>
                        <div class='not_visible text-center' style='margin:0px;'>".q($_SESSION['uname'])."</div>
                    </div>
                    <div class='col-xs-8 col-sm-5'>
                    <div class='h3' style='font-size: 18px; margin: 10px 0 10px 0;'><a href='".$urlServer."main/profile/display_profile.php'>".q("$_SESSION[givenname] $_SESSION[surname]")."</a></div>
                    <div><div class='h5'><span class='tag'>$langHierarchyNode: </span></div><span class='tag-value text-muted'>";
                    $departments = $user->getDepartmentIds($uid);
                        $i = 1;
                        foreach ($departments as $dep) {
                            $br = ($i < count($departments)) ? '<br>' : '';
                            $tool_content .= $tree->getFullPath($dep) . $br;
                            $i++;
                        }
                    $tool_content .= "</span></div>$lastVisitLabel</div>
                    <div class='col-xs-12 col-sm-5'>
                        <ul class='list-group'>
                            <li class='list-group-item'>
                              <span class='badge'>$student_courses_count</span>
                              <span class='text-muted'>$langSumCoursesEnrolled</span>
                            </li>
                            ";
                            if( !$is_editor && $teacher_courses_count>0 ) {
                                $tool_content .= "<li class='list-group-item'>
                                                    <span class='badge'>$teacher_courses_count</span>
                                                    <span class='text-muted'>$langSumCoursesSupport</span>
                                                    </li>";
                            }
                            $tool_content .= "
                        </ul>
                        <div class='pull-right'><a href='".$urlServer."main/profile/password.php'><small>$langProfileQuickPassword</small></a></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
";
draw($tool_content, 1, null, $head_content, null, null, $perso_tool_content);
