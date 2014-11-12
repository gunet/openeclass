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
require_once 'modules/graphics/plotter.php';

$nameTools = $langWelcomeToPortfolio;

ModalBoxHelper::loadModalBox();

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
  jQuery('#portfolio_lessons').dataTable({
    'bLengthChange': false,
    'iDisplayLength': 5,
    'bSort' : false,
    'fnDrawCallback': function( oSettings ) {
      $('#portfolio_lessons_filter label input').attr({
        class : 'form-control input-sm',
        placeholder : '$langSearch...'
      });
    },
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
  jQuery('.panel_content').hide();
   jQuery('.panel_content_open').show();
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

$tool_content = ($_SESSION['status'] == USER_TEACHER?
    action_bar(array(
        array('title' => $langCourseCreate,
              'url' => $urlAppend . 'modules/create_course/create_course.php',
              'icon' => 'fa-plus-circle',
              'level' => 'primary-label',
              'button-class' => 'btn-success'))): '') . "
    <div class='row margin-top-fat'>
        <div id='my-courses' class='col-md-7'>
            <div class='row'>
                <div class='col-md-12'>
                    <h3 class='content-title'>{%LANG_MY_PERSO_LESSONS%}</h3>
                    <div class='panel'>
                        <div class='panel-body'>
                            {%LESSON_CONTENT%}                        
                        </div>
                    </div>
                </div>
            </div>
            <div class='row'>
            <div class='col-md-12 my-announcement-list'>
                <h3 class='content-title'>{%LANG_MY_PERSO_ANNOUNCEMENTS%}</h3>
                <div class='panel'>
                    <div class='panel-body'>
                        <ul class='tablelist'>";
                            if(!empty($user_announcements)){
                                $tool_content.=$user_announcements;
                            }else{
                                $tool_content.="<li class='list-item' style='border-bottom:none;'>$langNoRecentAnnounce</li>";
                            }
                            $tool_content.="</ul>
                    </div>
                    <div class='panel-footer'>
                        <p class='link-to-more'><a href='#'>$langMore&hellip;</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class='col-md-5'>
        <div class='row'>
            <div class='col-md-12'>
                <h3 class='content-title'>{%LANG_MY_PERSONAL_CALENDAR%}</h3>
                <div class='panel'>
                    <div class='panel-body'>
                        {%PERSONAL_CALENDAR_CONTENT%}
                    </div>
                    <div class='panel-footer'>
                    <span class='event event-important'></span><span class='event event-info'></span><span class='event event-info'></span><span class='event event-warning'></span><span class='event event-inverse'></span><span class='event event-success'></span><span class='event event-special'></span>
                    </div>
                </div>
            </div>
        </div>
        <div class='row'>
                <div class='col-md-12 my-messages-list'>
                    <h3 class='content-title'>$langMyPersoMessages</h3>
                    <div class='panel'>
                        <div class='panel-body'>
                            <ul class='tablelist'>";
                            if(!empty($user_messages)){
                                $tool_content.=$user_messages;
                            }else{
                                $tool_content.="<li class='list-item' style='border-bottom:none;'>$langDropboxNoMessage</li>";
                            }
                            $tool_content.="</ul>
                        </div>
                        <div class='panel-footer'>
                            <p class='link-to-more'><a href='{$urlAppend}modules/dropbox/'>$langMore&hellip;</a></p>
                        </div>
                    </div>
                </div>
            </div>
    </div>";

$tool_content .= "
</div>
<div id='profile_box' class='row'>
    <div class='col-md-12'>
        <div class='panel'>
            <div class='panel-body'>
                <div class='row'>
                    <div class='col-sm-3'>
                        <img src='" . user_icon($uid, IMAGESIZE_LARGE) . "' style='width:100px;' class='img-circle center-block img-responsive' alt='Circular Image'>
                        <h4 class='text-center'>".q("$_SESSION[givenname] $_SESSION[surname]")."</h4>
                    </div>
                    <!--<div class='col-sm-9'>
                        <div class='stats'>".courseVisitsPlot()."</div>
                    </div>--> 
                </div>
            </div>
        </div>
    </div>
</div>
";
draw($tool_content, 1, null, $head_content, null, null, $perso_tool_content);

/**
 * draws statistics graph
 * @global type $uid
 * @global type $langCourseVisits
s * @return type
 */
function courseVisitsPlot() {
    global $uid, $langCourseVisits, $langNoStats;
    
    $totalHits = 0;
    $totalDuration = 0;

    $result = Database::get()->queryArray("SELECT a.code code, a.title title
                                        FROM course AS a LEFT JOIN course_user AS b
                                             ON a.id = b.course_id
                                        WHERE b.user_id = ?d
                                        AND a.visible != " . COURSE_INACTIVE . "
                                        ORDER BY a.title", $uid);

    if (count($result) > 0) {  // found courses ?    
        foreach ($result as $row) {
            $course_codes[] = $row->code;
            $course_names[$row->code] = $row->title;
        }
        foreach ($course_codes as $code) {
            $cid = course_code_to_id($code);
            $row = Database::get()->querySingle("SELECT SUM(hits) AS cnt FROM actions_daily
                                WHERE user_id = ?d
                                AND course_id =?d", $uid, $cid);
            if ($row) {
                $totalHits += $row->cnt;
                $hits[$code] = $row->cnt;
            }
            $result = Database::get()->querySingle("SELECT SUM(duration) AS duration FROM actions_daily
                                        WHERE user_id = ?d
                                        AND course_id = ?d", $uid, $cid);
            $duration[$code] = $result->duration;
            $totalDuration += $duration[$code];
        }

        $chart = new Plotter(600, 300);
        $chart->setTitle($langCourseVisits);
        foreach ($hits as $code => $count) {
            if ($count > 0) {
                $chart->addPoint($course_names[$code], $count);
                $chart->modDimension(7, 0);
            }
        }
        return $chart->plot();
    } else {
        return "$langNoStats";
    }
}
