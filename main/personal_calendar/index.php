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


/*
 * Events Component
 *
 * @version 1.0
 * @abstract This component displays personal user events and offers several operations on them.
 * The user can:
 * 1. Add new personal events
 * 2. Delete personal events (one by one or all at once)
 * 3. Modify existing events
 * 4. Associate events with courses and course objects
 */

$require_login = true;
$require_help = TRUE;
$helpTopic = 'PersonalCalendar';

include '../../include/baseTheme.php';
$require_valid_uid = true;
require_once 'include/lib/textLib.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/references.class.php';
require_once 'main/personal_calendar/calendar_events.class.php';

Calendar_Events::get_calendar_settings();
$dateNow = date("j-n-Y / H:i", time());
$datetoday = date("Y-n-j H:i", time());
$today = getdate();
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
}

load_js('tools.js');
load_js('bootstrap-datetimepicker');
load_js('bootstrap-datepicker');
load_js('bootstrap-timepicker');

if(!empty($langLanguageCode)){
    load_js('bootstrap-calendar-master/js/language/'.$langLanguageCode.'.js');
}
load_js('bootstrap-calendar-master/js/calendar.js');
load_js('bootstrap-calendar-master/components/underscore/underscore-min.js');

$head_content .= "<link rel='stylesheet' type='text/css' href='{$urlAppend}js/jquery-ui-timepicker-addon.min.css'>
<link rel='stylesheet' type='text/css' href='{$urlAppend}js/bootstrap-calendar-master/css/calendar.css' />
<script type='text/javascript'>
$(function() {
    $('#startdate').datetimepicker({
        format: 'dd-mm-yyyy hh:ii', pickerPosition: 'bottom-left', 
        language: '".$language."',
        autoclose: true
    });
    $('#enddate').datepicker({
        format: 'dd-mm-yyyy',
        language: '".$language."',
        autoclose: true
    });
    $('#duration').timepicker({ 
        showMeridian: false, 
        minuteStep: 1, 
        defaultTime: false 
    });
});".
'
var selectedday = '.$today['mday'].';
var selectedmonth = '.$today['mon'].';
var selectedyear = '.$today['year'].';
function show_month(day,month,year){
    selectedday = day;
    selectedmonth = month;
    selectedyear = year;
    $.get("../calendar_data.php",{day:day, month: month, year: year}, function(data){$("#monthcalendar").html(data);});    
}
function show_week(day,month,year){
    selectedday = day;
    selectedmonth = month;
    selectedyear = year;
    $.get("../calendar_data.php",{day:day, month: month, year: year, caltype: "week"}, function(data){$("#monthcalendar").html(data);});    
}
function show_day(day,month,year){
    selectedday = day;
    selectedmonth = month;
    selectedyear = year;
    $.get("../calendar_data.php",{day:day, month: month, year: year, caltype: "day"}, function(data){$("#monthcalendar").html(data);});    
}
</script>';

//angela: Do we need recording of personal actions????
// The following is added for statistics purposes
//require_once 'include/action.php';

//$action = new action();
//$action->record(MODULE_ID_ANNOUNCE);

$nameTools = $langMyAgenda;

ModalBoxHelper::loadModalBox();
load_js('jquery');
load_js('tools.js');
load_js('references.js');
$head_content .= '<script type="text/javascript">var langEmptyGroupName = "' .
        $langEmptyEventTitle . '";</script>';

$displayForm = true;

/* submit form: new or updated event*/
if (isset($_POST['submitEvent'])) {
    
    $newTitle = $_POST['newTitle'];       
    $newContent = $_POST['newContent'];
    if(isset($_POST['visibility_level'])){
        $visibility = $_POST['visibility_level'];
        $refobjid = null;
    } else {
        $refobjid = ($_POST['refobjid'] == "0")? $_POST['refcourse']:$_POST['refobjid'];
        $visibility = null;
    }
    $eventDate_obj = DateTime::createFromFormat('d-m-Y H:i', $_POST['startdate']);
    $start = $eventDate_obj->format('Y-m-d H:i:s');    
    $duration = $_POST['duration'];
    if (!empty($_POST['id'])) { //existing event
        $id = intval($_POST['id']);
        if(is_null($visibility)){
            $resp = Calendar_Events::update_event($id, $newTitle, $start, $duration, $newContent, $refobjid);
        } else {
            $resp = Calendar_Events::update_admin_event($id, $newTitle, $start, $duration, $newContent, $visibility);
        }
        if($resp['success']){
            $message = "<p class='success'>$langEventModify</p>";
        }
        else{
            $message = "<p class='caution'>{$resp['message']}</p>";
        }
    } else { // new event 
        $recursion = null;
        if(!empty($_POST['frequencyperiod']) && intval($_POST['frequencynumber'])>0 && !empty($_POST['enddate'])){
            $endDate_obj = DateTime::createFromFormat('d-m-Y', $_POST['enddate']);
            $end = $endDate_obj->format('Y-m-d H:i:s');  
            $recursion = array('unit' => $_POST['frequencyperiod'], 'repeat' => $_POST['frequencynumber'], 'end'=> $end);
        }
        $resp = Calendar_Events::add_event($newTitle, $newContent, $start, $duration, $recursion, $refobjid, $visibility);
        if($resp['success']){
            $message = "<p class='success'>$langEventAdd</p>";
        }
        else{
            $message = "<p class='caution'>{$resp['message']}</p>";
        }
    }    
} // end of if $submit

/* delete */
if (isset($_GET['delete']) && (isset($_GET['et']) && ($_GET['et'] == 'personal' || $_GET['et'] == 'admin'))) {
    $thisEventId = intval($_GET['delete']);
    $resp = Calendar_Events::delete_event($thisEventId, $_GET['et']);
    if($resp['success']){
            $message = "<p class='success'>$langEventDel</p>";
    } else {
         $message = "<p class='caution'>{$resp['message']}</p>";
    }
}

/* edit */
if (isset($_GET['modify'])) {    
    $modify = intval($_GET['modify']);
    $displayForm = false;
    if(isset($_GET['admin']) && $_GET['admin'] == 1){
        $event = Calendar_Events::get_admin_event($modify);
        if ($event) {
            $eventToModify = $event->id;
            $contentToModify = $event->content;
            $titleToModify = q($event->title);
            $startDate_obj = DateTime::createFromFormat('Y-m-d H:i:s',$event->start);
            $startdate = $startDate_obj->format('d-m-Y H:i');
            $datetimeToModify = q($startdate);
            $durationToModify = q($event->duration);
            $visibility_level = $event->visibility_level;
            $displayForm = true;
        }
    } else {
        $event = Calendar_Events::get_event($modify);       
        if ($event) {
            $eventToModify = $event->id;
            $contentToModify = $event->content;
            $titleToModify = q($event->title);
            $startDate_obj = DateTime::createFromFormat('Y-m-d H:i:s',$event->start);
            $startdate = $startDate_obj->format('d-m-Y H:i');            
            $datetimeToModify = q($startdate);
            $durationToModify = q($event->duration);
            $gen_type_selected = $event->reference_obj_module;
            $course_selected = $event->reference_obj_course;
            $type_selected = $event->reference_obj_type;
            $object_selected = $event->reference_obj_id;
            $displayForm = true;
        }
    }
}

if (isset($message) && $message) {
    $tool_content .= $message . "<br/>";
    $displayForm = false; //do not show form
}

/* display form */
if ($displayForm and (isset($_GET['addEvent']) or ($is_admin && isset($_GET['addAdminEvent'])) or isset($_GET['modify']))) {
    $tool_content .= "
    <form method='post' action='$_SERVER[SCRIPT_NAME]' onsubmit=\"return checkrequired(this, 'antitle');\">
    <fieldset>
    <legend>$langEvent</legend>
    <table class='tbl' width='100%'>";
    if (isset($_GET['modify'])) {
        $langAdd = $nameTools = $langModifEvent;
    } else {
        $nameTools = $langAddEvent;
    }
    $navigation[] = array('url' => "index.php", 'name' => $langEvents);
    if (!isset($eventToModify))
        $eventToModify = "";
    if (!isset($contentToModify))
        $contentToModify = "";
    if (!isset($titleToModify))
        $titleToModify = "";
    if (!isset($datetimeToModify))
        $datetimeToModify = "";
    if (!isset($durationToModify))
        $durationToModify = "";
    if (!isset($gen_type_selected))
        $gen_type_selected = null;
    if (!isset($course_selected))
        $course_selected = null;
    if (!isset($type_selected))
        $type_selected = null;
    if (!isset($object_selected))
        $object_selected = null;
    
    $tool_content .= "
    <tr><th>$langEventTitle:</th></tr>
    <tr>
      <td><input type='text' name='newTitle' value='$titleToModify' size='50' /></td>
    </tr>
    <tr><th>$langEventBody:</th></tr>
    <tr>
      <td>" . rich_text_editor('newContent', 4, 20, $contentToModify) . "</td>
    </tr>
    <tr><th>$langDate:</th></tr>
    <tr>
        <td> <input type='text' name='startdate' id='startdate' value='$datetimeToModify'></td>
    </tr>
    <tr><th>$langDuration:</th></tr>
    <tr>
        <td><input type=\"text\" name=\"duration\" id='duration' value='$durationToModify'></td>
    </tr>";
    if(!isset($_GET['modify'])){
        $tool_content .= "
        <tr><th>$langRepeat:</th></tr>
        <tr>
            <td> $langEvery: "
                . "<select name='frequencynumber'>"
                . "<option value=\"0\">$langSelectFromMenu</option>";
        for($i = 1;$i<10;$i++)
        {
            $tool_content .= "<option value=\"$i\">$i</option>";
        }
        $tool_content .= "</select>"
                . "<select name='frequencyperiod'> "
                . "<option value=\"\">$langSelectFromMenu...</option>"
                . "<option value=\"D\">$langDays</option>"
                . "<option value=\"W\">$langWeeks</option>"
                . "<option value=\"M\">$langMonthsAbstract</option>"
                . "</select>"
                . " $langUntil: <input type='text' name='enddate' id='enddate' value=''></td>
        </tr>";
    }
    if(!isset($_GET['addAdminEvent']) && !isset($_GET['admin'])){
        $eventtype = 'personal';
        $tool_content .= "
        <tr><th>$langReferencedObject:</th></tr>
        <tr>
          <td>".
          References::build_object_referennce_fields($gen_type_selected, $course_selected, $type_selected, $object_selected)
       ."</td>";
    }
    else {
        $eventtype = 'admin';
        $selectedvis = array(0=>"", USER_TEACHER=>"",USER_STUDENT=>"",USER_GUEST=>"");
        if(isset($visibility_level)){
            $selectedvis[$visibility_level] = "selected";
        }
        $tool_content .= "<tr><th>$langShowTo:</th></tr>"
                . "<tr><td><select name='visibility_level'> "
                . "<option value=\"0\" ".$selectedvis[0].">$langShowToAdminsOnly</option>"
                . "<option value=\"".USER_TEACHER."\" ".$selectedvis[USER_TEACHER].">$langShowToAdminsandProfs</option>"
                . "<option value=\"".USER_STUDENT."\" ".$selectedvis[USER_STUDENT].">$langShowToAllregistered</option>"
                . "<option value=\"".USER_GUEST."\" ".$selectedvis[USER_GUEST].">$langShowToAll</option>"
                . "</select></td></tr>";
    }
    $tool_content .= "
        </tr>
        <tr>
          <td class='right'>
            <input type='submit' name='submitEvent' value='$langAdd' />
            <a href='?delete=$eventToModify&et=$eventtype' onClick=\"return confirmation('$langConfirmDelete');\"><span class='button' name='deleteEventBtn'>$langDelete</span></a>
          </td>
        </tr>";
    $tool_content .= "</table>
    <input type='hidden' name='id' value='$eventToModify' />
    </fieldset>
    </form>";
} else {
    /* display actions toolbar */
    $tool_content .= "
    <div id='operations_container'>
      <ul id='opslist'>
        <li><a href='$_SERVER[SCRIPT_NAME]?addEvent=1'>" . $langAddEvent . "</a></li>";
        if($is_admin){
            $tool_content .= "<li><a href='$_SERVER[SCRIPT_NAME]?addAdminEvent=1'>" . $langAddAdminEvent . "</a></li>";
        }
        $tool_content .= "<li><a href='icalendar.php'>" . $langiCalExport . "</a></li>
      </ul>
    </div>";
}


/* display events */
$day = (isset($_GET['day']))? intval($_GET['day']):null;
$month = (isset($_GET['month']))? intval($_GET['month']):null;
$year = (isset($_GET['year']))? intval($_GET['year']):null;
if($_SESSION['theme'] != 'bootstrap')
{
    $tool_content .= '<div id="monthcalendar" style="width:100%">';
    $tool_content .= Calendar_Events::calendar_view($day, $month, $year);
    $tool_content .= '</div>';
}
else{
    $tool_content .= ''
            . '<div class="row page-header">
                    <div class="pull-right form-inline">
                            <div class="btn-group">
                                    <button class="btn btn-primary" data-calendar-nav="prev">&larr; '.$langPrevious.'</button>
                                    <button class="btn" data-calendar-nav="today">'.$langToday.'</button>
                                    <button class="btn btn-primary" data-calendar-nav="next">'.$langNext.' &rarr;</button>
                            </div>
                            <div class="btn-group">
                                    <button class="btn btn-warning" data-calendar-view="year">'.$langYear.'</button>
                                    <button class="btn btn-warning active" data-calendar-view="month">'.$langMonth.'</button>
                                    <button class="btn btn-warning" data-calendar-view="week">'.$langWeek.'</button>
                                    <button class="btn btn-warning" data-calendar-view="day">'.$langDay.'</button>
                            </div>
                    </div>
                    <h3></h3>
            </div>'
            . '<div class="row"><div id="bootstrapcalendar" class="col-xs-6 col-sm-7 col-md-6 add-gutter"></div></div>'.
    "<script type='text/javascript'>".
    '$(document).ready(function(){

    var calendar = $("#bootstrapcalendar").calendar(
            {
                tmpl_path: "'.$urlAppend.'js/bootstrap-calendar-master/tmpls/",
                events_source: "'.$urlAppend.'main/calendar_data.php",
                language: "el-GR",
                onAfterViewLoad: function(view) {
                            $(".page-header h3").text(this.getTitle());
                            $(".btn-group button").removeClass("active");
                            $("button[data-calendar-view=\'" + view + "\']").addClass("active");
                            }
            }
        );

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
    });

    </script>';
}

add_units_navigation(TRUE);

draw($tool_content, 1, null, $head_content);
