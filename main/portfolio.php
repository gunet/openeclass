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
$data['action_bar'] = action_bar(array(
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

$data['perso_tool_content'] = $perso_tool_content;
$data['user_announcements'] = $user_announcements;

$portfolio_page_main = new \Widgets\WidgetArea(PORTFOLIO_PAGE_MAIN);
$data['portfolio_page_main_widgets'] = '';
foreach ($portfolio_page_main->getUserAndAdminWidgets($uid) as $key => $widget) {
    $data['portfolio_page_main_widgets'] .= $widget->run($key);
}      
$portfolio_page_sidebar = new \Widgets\WidgetArea(PORTFOLIO_PAGE_SIDEBAR);
$data['portfolio_page_sidebar_widgets'] = "";
foreach ($portfolio_page_main->getUserAndAdminWidgets($uid) as $key => $widget) {
    $data['portfolio_page_sidebar_widgets'] .= $widget->run($key);
} 
$data['departments'] = $user->getDepartmentIds($uid);

$data['lastVisit'] = Database::get()->querySingle("SELECT * FROM loginout
                        WHERE id_user = ?d ORDER by idLog DESC LIMIT 1", $uid);

$data['teacher_courses_count'] = $teacher_courses_count;
$data['student_courses_count'] = $student_courses_count;

$data['menuTypeID'] = 1;
view('portfolio.index', $data);




//draw($tool_content, 1, null, $head_content, null, null, $perso_tool_content);
