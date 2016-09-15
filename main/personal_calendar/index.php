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

if (!empty($langLanguageCode)) {
    load_js('bootstrap-calendar-master/js/language/' . $langLanguageCode . '.js');
}
load_js('bootstrap-calendar-master/js/calendar.js');
load_js('bootstrap-calendar-master/components/underscore/underscore-min.js');

$head_content .= "
<link rel='stylesheet' type='text/css' href='{$urlAppend}js/bootstrap-calendar-master/css/calendar.css' />
<script type='text/javascript'>
$(function() {
    $('#startdate').datetimepicker({
        format: 'dd-mm-yyyy hh:ii', 
        pickerPosition: 'bottom-right',
        language: '".$language."',
        autoclose: true
    });
    $('#enddatecal').datepicker({
        format: 'dd-mm-yyyy', 
        pickerPosition: 'bottom-right',
        language: '".$language."',
        autoclose: true
    });
    $('#duration').timepicker({
        showMeridian: false,
        pickerPosition: 'bottom-right',
        minuteStep: 1, 
        defaultTime: false,
        autoclose: true});
});
" .
        '
var selectedday = ' . $today['mday'] . ';
var selectedmonth = ' . $today['mon'] . ';
var selectedyear = ' . $today['year'] . ';
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
'
."var dialogUpdateOptions = {
    title: '$langConfirmUpdate',
    message: '$langConfirmUpdateRecursiveEvents',
    buttons: {
        cancel:{label: '$langCancel',
        callback: function() {
                 }
             },
        yes:{
            label: '$langYes',
            className: 'btn-primary',
             callback: function() {
                           $('#rep').val('yes');
                           $('#myeventform').submit();
                      }
            },
        no:{
            label: '$langNoJustThisOne',
            className: 'btn-info',
            callback: function() {
                           $('#rep').val('no');
                           $('#myeventform').submit();
                      }
            }
    }};
var dialogDeleteOptions = {
    title: '$langConfirmDelete',
    message: '$langConfirmDeleteRecursiveEvents',
    buttons: {
        cancel:{label: '$langCancel'},
        yes:{
            label: '$langYes',
            className: 'btn-danger'},
        no:{
            label: '$langNoJustThisOne',
            className: 'btn-warning'}}};

$(document).ready(function(){
    $('#enddatecal').hide();
    $('#submitEvent').on('click', 
            function(e){
                checkrequired($('#myeventform'));
    });
    
    $('#frequencynumber').change(function(){checkenableenddate();});
    $('#frequencyperiod').change(function(){checkenableenddate();});
});

function checkenableenddate(){
    if($('#frequencynumber').val() == '0' || $('#frequencyperiod').val() === \"\"){
        $('#enddatecal').hide();
    } else {
        $('#enddatecal').show();
    }
}
    
function checkrequired(thisform) {
    if ($('#newTitle').val()=='' || $('#startdate').val()=='') {
            bootbox.alert('$langTitleDateNotEmpty');
            return false;
    }
    if($('#id').val()>0 && $('#rep').val() != ''){
        bootbox.dialog(dialogUpdateOptions);
    } else {
        thisform.submit();
    }
}"
.'</script>';

$toolName = $langMyAgenda;

ModalBoxHelper::loadModalBox();
load_js('tools.js');
load_js('references.js');
$head_content .= '<script type="text/javascript">var langEmptyGroupName = "' .
        $langEmptyEventTitle . '";</script>';

$displayForm = true;

/* submit form: new or updated event */
if (isset($_POST['newTitle'])) {

    $newTitle = $_POST['newTitle'];
    $newContent = $_POST['newContent'];
    if (isset($_POST['visibility_level'])) {
        $visibility = $_POST['visibility_level'];
        $refobjid = null;
    } else {
        $refobjid = ($_POST['refobjid'] == "0") ? $_POST['refcourse'] : $_POST['refobjid'];
        $visibility = null;
    }
    $start = $_POST['startdate'];
    $duration = $_POST['duration'];
    if (!empty($_POST['id'])) { //existing event
        $id = intval($_POST['id']);
        $recursion = null;
        if (!empty($_POST['frequencyperiod']) && intval($_POST['frequencynumber']) > 0 && !empty($_POST['enddate'])) {
            $recursion = array('unit' => $_POST['frequencyperiod'], 'repeat' => $_POST['frequencynumber'], 'end' => $_POST['enddate']);
        }
        if (is_null($visibility)) {
            if(isset($_POST['rep']) && $_POST['rep'] == 'yes'){
                $resp = Calendar_Events::update_recursive_event($id, $newTitle, $start, $duration, $newContent, $recursion, $refobjid);
            } else {
              $resp = Calendar_Events::update_event($id, $newTitle, $start, $duration, $newContent, false, $recursion, $refobjid);
            }
            
        } else {
            $resp = Calendar_Events::update_admin_event($id, $newTitle, $start, $duration, $newContent, $visibility, $recursion);
            if(isset($_POST['rep']) && $_POST['rep'] == 'yes'){
                    $resp = Calendar_Events::update_recursive_admin_event($id, $newTitle, $start, $duration, $newContent, $visibility, $recursion);
            } else {
                $resp = Calendar_Events::update_admin_event($id, $newTitle, $start, $duration, $newContent, $visibility, $recursion);
            }
        }
        if ($resp['success']) {
            Session::Messages($langEventModify, 'alert-success');
        } else {
            Session::Messages($resp['message']);
        }
        redirect_to_home_page('main/personal_calendar/index.php');
    } else { // new event 
        $recursion = null;
        if (!empty($_POST['frequencyperiod']) && intval($_POST['frequencynumber']) > 0 && !empty($_POST['enddate'])) {
            $recursion = array('unit' => $_POST['frequencyperiod'], 'repeat' => $_POST['frequencynumber'], 'end' => $_POST['enddate']);
        }
        $resp = Calendar_Events::add_event($newTitle, $newContent, $start, $duration, $recursion, $refobjid, $visibility);
        if ($resp['success']) {
            Session::Messages($langEventAdd, 'alert-success');
        } else {
            Session::Messages($resp['message']);
        }
        redirect_to_home_page('main/personal_calendar/index.php');
    }
} // end of if $submit

/* delete */
if (isset($_GET['delete']) && (isset($_GET['et']) && ($_GET['et'] == 'personal' || $_GET['et'] == 'admin'))) {
    $thisEventId = intval($_GET['delete']);    
    if(isset($_GET['rep']) && $_GET['rep'] == 'yes'){        
        $resp = Calendar_Events::delete_recursive_event($thisEventId, $_GET['et']);
    } else {
        $resp = Calendar_Events::delete_event($thisEventId, $_GET['et']);
    }
    if ($resp['success']) {
        Session::Messages($langEventDel, 'alert-success');
    } else {
        Session::Messages($resp['message']);
    }
    redirect_to_home_page('main/personal_calendar/index.php');
}
$is_recursive_event = false;
$enddate = '';/* edit */
$applytogroup = '';
if (isset($_GET['modify'])) {    
    $modify = intval($_GET['modify']);
    $displayForm = false;
    if (isset($_GET['admin']) and $is_admin) {                
        $event = Calendar_Events::get_admin_event($modify);
        if ($event) {
            $eventToModify = $event->id;
            $contentToModify = $event->content;
            $titleToModify = q($event->title);
            $startDate_obj = DateTime::createFromFormat('Y-m-d H:i:s', $event->start);
            $startdate = DateTime::createFromFormat('Y-m-d H:i:s', $event->start)->format('d-m-Y H:i');
            $datetimeToModify = $startdate;
            $durationToModify = DateTime::createFromFormat('H:i:s', $event->duration)->format('H:i');
            $enddate = '';
            if(Calendar_Events::is_recursive($event->id, 'admin')){
                   $is_recursive_event = true;
                   $applytogroup = 'no';
                   $repeatnumber = substr($event->recursion_period, 1, strlen($event->recursion_period)-2);
                   $repeatperiod = substr($event->recursion_period, -1);
                   $enddate = DateTime::createFromFormat('Y-m-d', $event->recursion_end)->format('d-m-Y');
            }
            $visibility_level = $event->visibility_level;
            $displayForm = true;
        }
    } else {
        $event = Calendar_Events::get_event($modify);        
        if ($event) {
            $eventToModify = $event->id;
            $contentToModify = $event->content;
            $titleToModify = q($event->title);
            $startdate = DateTime::createFromFormat('Y-m-d H:i:s', $event->start)->format('d-m-Y H:i');
            $datetimeToModify = q($startdate);
            $durationToModify = DateTime::createFromFormat('H:i:s', $event->duration)->format('H:i');
            $gen_type_selected = $event->reference_obj_module;
            $course_selected = $event->reference_obj_course;
            $type_selected = $event->reference_obj_type;
            $object_selected = $event->reference_obj_id;
            $displayForm = true;
            $is_recursive_event = false;
            $enddate = '';
            if(Calendar_Events::is_recursive($event->id, 'personal')){
                $is_recursive_event = true;
                $applytogroup = 'no';
                $repeatnumber = substr($event->recursion_period, 1, strlen($event->recursion_period)-2);
                $repeatperiod = substr($event->recursion_period, -1);
                $enddate = DateTime::createFromFormat('Y-m-d', $event->recursion_end)->format('d-m-Y');
            }
            
        }
    }
}

/* display form */
if ($displayForm and (isset($_GET['addEvent']) or ($is_admin && isset($_GET['addAdminEvent'])) or isset($_GET['modify']))) { 
    if (isset($_GET['modify'])) {
        $pageName = $langModifEvent;
    } else {
        $pageName = $langAddEvent;
    }
    $tool_content .= action_bar(array(
                array('title' => $langBack,
                      'url' => "index.php",
                      'icon' => 'fa-reply',
                      'level' => 'primary-label')));
    
    $navigation[] = array('url' => "index.php", 'name' => $langMyAgenda);
    
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
    <div class='form-wrapper'>
        <form id='myeventform' class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]' style='display:inline'>
        <input type='hidden' id='id' name='id' value='$eventToModify'>
        <input type='hidden' name='rep' id='rep' value='$applytogroup'>
        <div class='form-group'>
          <label for='newTitle' class='col-sm-2 control-label'>$langTitle:</label>
          <div class='col-sm-10'>
               <input class='form-control' type='text' name='newTitle' id='newTitle' value='$titleToModify' placeholder='$langTitle'>
          </div>
        </div>
        <div class='form-group'>
          <label for='newContent' class='col-sm-2 control-label'>$langDescription:</label>
          <div class='col-sm-10'>
               " . rich_text_editor('newContent', 4, 20, $contentToModify) . "
          </div>
        </div>
        <div class='input-append date form-group' name='startdatecal' data-date='$langDate' data-date-format='dd-mm-yyyy'>
              <label for='startdate' class='col-sm-2 control-label'>$langDate:</label>
              <div class='col-sm-10'>
                  <div class='input-group'>
                     <input class='form-control' type='text' name='startdate' id='startdate' value='$datetimeToModify'>
                     <div class='input-group-addon'><span class='add-on'><span class='fa fa-calendar fa-fw'></span></span></div>
                  </div>
              </div>
        </div>
        <div class='input-append bootstrap-timepicker form-group'>
            <label for='durationcal' class='col-sm-2 control-label'>$langDuration <small>$langInHour</small></label>
            <div class='col-sm-10'>
                <div class='input-group add-on'>
                    <input class='form-control' name='duration' id='duration' type='text' class='input-small' value='" . $durationToModify . "'>
                    <div class='input-group-addon'><span class='fa fa-clock-o fa-fw'></span></div>
                </div>
            </div>
        </div>
        ";
        /* Repeat period*/
        $tool_content .= "
            <div class='form-group'>
              <label for='frequencynumber' class='col-sm-2 control-label'>$langRepeat $langEvery:</label>
              <div class='col-sm-2'>
                    <select class='form-control' name='frequencynumber' id='frequencynumber'>
                        <option value='0'>$langSelectFromMenu</option>";
                for ($i = 1; $i < 10; $i++) {
                    $tool_content .= "<option value=\"$i\"";
                    if($is_recursive_event && $i == $repeatnumber){
                        $tool_content .= ' selected';
                    }
                    $tool_content .= ">$i</option>";
                }
                $selected = array('D'=>'', 'W'=>'','M'=>'');
                if($is_recursive_event){
                    $selected[$repeatperiod] = ' selected';
                }
                $tool_content .= "</select></div>
                <div class='col-sm-2'>
                      <select class='form-control' name='frequencyperiod' id='frequencyperiod'>
                        <option value=\"\">$langSelectFromMenu...</option>
                        <option value=\"D\"{$selected['D']}>$langDays</option>
                        <option value=\"W\"{$selected['W']}>$langWeeks</option>
                        <option value=\"M\"{$selected['M']}>$langMonthsAbstract</option>
                    </select>
              </div>
              <div class='input-append date' id='enddatecal' data-date='$langDate' data-date-format='dd-mm-yyyy'>
                <label for='enddate' class='col-sm-2 control-label'>$langUntil:</label>
                <div class='col-sm-4'>
                  <div class='input-group'>
                     <input class='form-control' type='text' name='enddate' id='enddate' value='$enddate' type='text' >
                     <div class='input-group-addon'><span class='add-on'><span class='fa fa-calendar fa-fw'></span></span></div>
                  </div>
                </div>
              </div>
            </div>";
        if (!isset($_GET['addAdminEvent']) && !isset($_GET['admin'])) {
            $eventtype = 'personal';
            $tool_content .= "
            <div class='form-group'>
              <label for='startdate' class='col-sm-2 control-label'>$langReferencedObject:</label>
              <div class='col-sm-10'>
                   " . References::build_object_referennce_fields($gen_type_selected, $course_selected, $type_selected, $object_selected) . "
              </div>
            </div>";
        } else {
            $eventtype = 'admin';
            $selectedvis = array(0 => "", USER_TEACHER => "", USER_STUDENT => "", USER_GUEST => "");
            if (isset($visibility_level)) {
                $selectedvis[$visibility_level] = "selected";
            }            
            $tool_content .= "
            <div class='form-group'>
              <label for='startdate' class='col-sm-2 control-label'>$langShowTo:</label>
              <div class='col-sm-10'>
                <select class='form-control' name='visibility_level'>
                    <option value=\"" . USER_STUDENT . "\" " . $selectedvis[USER_STUDENT] . ">$langShowToAllregistered</option>
                    <option value=\"" . USER_GUEST . "\" " . $selectedvis[USER_GUEST] . ">$langShowToAll</option>
                    <option value='0' $selectedvis[0]>$langShowToAdminsOnly</option>
                    <option value=\"" . USER_TEACHER . "\" " . $selectedvis[USER_TEACHER] . ">$langShowToAdminsandProfs</option>                                        
                </select>
              </div>
            </div>";            
        }
        $tool_content .= "
            <div class='form-group'>
              <div class='col-sm-10 col-sm-offset-2'>
                   <input class='btn btn-primary' type='button' id='submitEvent' name='submitEvent' value='$langSubmit'>
                   <a class='btn btn-default' href='index.php'>$langCancel</a>
              </div>
            </div>            
            </form>
        </div>";    
} else {
    /* display actions toolbar */
    $tool_content .= 
                action_bar(array(
                    array('title' => $langAddEvent,
                        'url' => "$_SERVER[SCRIPT_NAME]?addEvent=1",
                        'icon' => 'fa-plus-circle',
                        'level' => 'primary-label',
                        'button-class' => 'btn-success'),
                    array('title' => $langAddAdminEvent,
                        'url' => "$_SERVER[SCRIPT_NAME]?addAdminEvent=1",
                        'icon' => 'fa-plus-circle',
                        'show' => $is_admin,
                        'button-class' => 'btn-success',
                        'level' => 'primary-label'),
                    array('title' => $langiCalExport,
                        'url' => "icalendar.php",
                        'icon' => 'fa-calendar',
                        'level' => 'primary'),
                ));

    if (isset($_GET['id'])) {
        require_once 'modules/agenda/course_calendar.inc.php';
        $id = intval($_GET['id']);
        if (isset($_GET['admin'])) {
            $personal_event = array('0' => Calendar_Events::get_admin_event($id));
            $tool_content .= event_list($personal_event, 'ASC', 'admin');
        } else {
            $personal_event = array('0' => Calendar_Events::get_event($id));
            $tool_content .= event_list($personal_event, 'ASC', 'personal');
        }
        
    } else {
        /* display events */
        $day = (isset($_GET['day'])) ? intval($_GET['day']) : null;
        $month = (isset($_GET['month'])) ? intval($_GET['month']) : null;
        $year = (isset($_GET['year'])) ? intval($_GET['year']) : null;
        $tool_content .= '<div id="calendar_wrapper" class="row">
                    <div class="col-md-12">
                        <div class="row calendar-header">
                        <div class="col-md-12">
                        <div id="calendar-header">
                            <div class="pull-right form-inline">
                                <div class="btn-group">
                                        <button class="btn btn-default btn-sm" data-calendar-nav="prev"><span class="fa fa-caret-left"></span>  ' . '' . '</button>
                                        <button class="btn btn-default btn-sm" data-calendar-nav="today">' . $langToday . '</button>
                                        <button class="btn btn-default btn-sm" data-calendar-nav="next">' . '' . ' <span class="fa fa-caret-right"></span> </button>
                                </div>
                                <div class="btn-group">
                                        <button class="btn btn-default btn-sm" data-calendar-view="year">' . $langYear . '</button>
                                        <button class="btn btn-default btn-sm active" data-calendar-view="month">' . $langMonth . '</button>
                                        <button class="btn btn-default btn-sm" data-calendar-view="week">' . $langWeek . '</button>
                                        <button class="btn btn-default btn-sm" data-calendar-view="day">' . $langDay . '</button>
                                </div>
                            </div>
                            <h4></h4>
                            </div>
                            </div>
                        </div>'
                . '<div class="row"><div id="bootstrapcalendar" class="col-md-12"></div></div>'
                . '</div></div>' .
                "<script type='text/javascript'>" .
                '$(document).ready(function(){

        var calendar = $("#bootstrapcalendar").calendar(
                {
                    tmpl_path: "' . $urlAppend . 'js/bootstrap-calendar-master/tmpls/",
                    events_source: "' . $urlAppend . 'main/calendar_data.php",
                    language: "el-GR",
                    onAfterViewLoad: function(view) {
                                $(".calendar-header h4").text(this.getTitle());
                                $(".btn-group button").removeClass("active");
                                $("button[data-calendar-view=\'" + view + "\']").addClass("active");
                                $("button[data-calendar-nav=\'today\']").text(this.getTitle());
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
}
add_units_navigation(TRUE);

draw($tool_content, 1, null, $head_content);
