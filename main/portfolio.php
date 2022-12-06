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
require_once 'perso.php';

$data['tree'] = new Hierarchy();
$user = new User();

if(!empty($langLanguageCode)){
    load_js('bootstrap-calendar-master/js/language/'.$langLanguageCode.'.js');
}
load_js('bootstrap-calendar-master/js/calendar.js');
load_js('bootstrap-calendar-master/components/underscore/underscore-min.js');
load_js('datatables');

// display privacy policy consent message to user if necessary
$modalShow = '';
if (get_config('activate_privacy_policy_text')) {
    $consentMessage = get_config('privacy_policy_text_' . $session->language);
    if (isset($_POST['accept_policy'])) {
        if ($_POST['accept_policy'] == 'yes') {
            user_accept_policy($uid);
        } elseif ($_POST['accept_policy'] == 'no') {
            user_accept_policy($uid, false);
        } else {
            $_SESSION['accept_policy_later'] = true;
        }
        if (isset($_POST['next']) and $_POST['next'] == 'profile') {
            redirect_to_home_page('main/profile/display_profile.php#privacyPolicySection');
        }
        redirect_to_home_page();
    }

    if ($_SESSION['status'] == USER_STUDENT and
        get_config('activate_privacy_policy_consent') and
        !isset($_SESSION['accept_policy_later']) and
        !user_has_accepted_policy($uid)) {
        $tool_content .= "
            <div class='modal fade' id='consentModal' tabindex='-1' role='dialog' aria-labelledby='consentModalLabel'>
                <div class='modal-dialog' role='document'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <button type='button' class='close' data-bs-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
                            <h4 class='modal-title' id='consentModalLabel'>$langUserConsent</h4>
                        </div>
                        <div class='modal-body' style='margin-left:20px; margin-right:20px;'>
                            $consentMessage
                        </div>
                        <div class='modal-footer'>
                            <form method='post' action='$_SERVER[SCRIPT_NAME]'>
                                <button type='submit' class='btn btn-success' role='button' name='accept_policy' value='yes'>$langAccept</button>
                                <button type='submit' class='btn btn-danger' role='button' name='accept_policy' value='no'>$langRejectRequest</button>
                                <button type='submit' class='btn btn-default' role='button' name='accept_policy' value='later'>$langLater</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>";
        $modalShow = "$('#consentModal').modal('show')";
    } else {
        $modalShow = '';
    }
}

$head_content .= "
<link rel='stylesheet' type='text/css' href='{$urlAppend}js/bootstrap-calendar-master/css/calendar_small.css' />
<script type='text/javascript'>
jQuery(document).ready(function() {
  $modalShow
  jQuery('#portfolio_lessons').DataTable({
    'bLengthChange': false,
    'iDisplayLength': 8,
    'bSort' : false,
    'fnDrawCallback': function( oSettings ) {
      $('#portfolio_lessons_filter label input').attr({
        class : 'form-control input-sm searchCoursePortfolio ms-0 mb-3',
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
 // $('div.all_courses').html('<a class=\"btn btn-outline-secondary cancelAdminBtn\" href=\"{$urlServer}main/my_courses.php\">$langAllCourses <span class=\"fa fa-arrow-right\"></span></a>');
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

$data['portfolio_page_main_widgets'] = '';
$portfolio_page_main = new \Widgets\WidgetArea(PORTFOLIO_PAGE_MAIN);
foreach ($portfolio_page_main->getUserAndAdminWidgets($uid) as $key => $widget) {
    $data['portfolio_page_main_widgets'] .= $widget->run($key);
}
$data['portfolio_page_sidebar_widgets'] = "";
$portfolio_page_sidebar = new \Widgets\WidgetArea(PORTFOLIO_PAGE_SIDEBAR);
foreach ($portfolio_page_main->getUserAndAdminWidgets($uid) as $key => $widget) {
    $data['portfolio_page_sidebar_widgets'] .= $widget->run($key);
}
$data['departments'] = $user->getDepartmentIds($uid);

$data['lastVisit'] = Database::get()->querySingle("SELECT * FROM loginout
                        WHERE id_user = ?d ORDER by idLog DESC LIMIT 1", $uid);

$data['userdata'] = Database::get()->querySingle("SELECT email, am, phone, registered_at,
                                            has_icon, description, password,
                                            email_public, phone_public, am_public
                                        FROM user
                                        WHERE id = ?d", $uid);

$data['teacher_courses_count'] = $teacher_courses_count;
$data['student_courses_count'] = $student_courses_count;


$data['user_messages'] = $user_messages;


// For pagination pictures of user-cources
$cources = getUserCoursesPic($uid);
$data['cources'] = $cources;

$items_per_page = 4;
$data['items_per_page'] = $items_per_page;

$cource_pages = ceil(count($cources)/$items_per_page);
$data['cource_pages'] = $cource_pages;

$data['menuTypeID'] = 1;
view('portfolio.index', $data);
