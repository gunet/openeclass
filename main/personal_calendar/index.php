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
$helpTopic = 'portfolio';
$helpSubTopic = 'my_calendar';
$require_valid_uid = true;

include '../../include/baseTheme.php';
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
            className: 'deleteAdminBtn'},
        no:{
            label: '$langNoJustThisOne',
            className: 'cancelAdminBtn'}}};

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
    $enddateEvent = $_POST['enddateEvent'];
    $duration = $_POST['duration'];
    if (!empty($_POST['id'])) { //existing event
        $id = intval($_POST['id']);
        $recursion = null;
        if (!empty($_POST['frequencyperiod']) && intval($_POST['frequencynumber']) > 0 && !empty($_POST['enddate'])) {
            $recursion = array('unit' => $_POST['frequencyperiod'], 'repeat' => $_POST['frequencynumber'], 'end' => $_POST['enddate']);
        }
        if (is_null($visibility)) {
            if(isset($_POST['rep']) && $_POST['rep'] == 'yes'){
                $resp = Calendar_Events::update_recursive_event($id, $newTitle, $start, $enddateEvent, $duration, $newContent, $recursion, $refobjid);
            } else {
              $resp = Calendar_Events::update_event($id, $newTitle, $start, $enddateEvent, $duration, $newContent, false, $recursion, $refobjid);
            }

        } else {
            $resp = Calendar_Events::update_admin_event($id, $newTitle, $start, $enddateEvent, $duration, $newContent, $visibility, $recursion);
            if(isset($_POST['rep']) && $_POST['rep'] == 'yes'){
                    $resp = Calendar_Events::update_recursive_admin_event($id, $newTitle, $start, $enddateEvent, $duration, $newContent, $visibility, $recursion);
            } else {
                $resp = Calendar_Events::update_admin_event($id, $newTitle, $start, $enddateEvent, $duration, $newContent, $visibility, $recursion);
            }
        }
        if ($resp['success']) {
            Session::flash('message', $langEventModify);
            Session::flash('alert-class', 'alert-success');
        } else {
            Session::flash('message', $resp['message']);
            Session::flash('alert-class', 'alert-warning');
        }
        redirect_to_home_page('main/personal_calendar/index.php');
    } else { // new event
        $recursion = null;
        if (!empty($_POST['frequencyperiod']) && intval($_POST['frequencynumber']) > 0 && !empty($_POST['enddate'])) {
            $recursion = array('unit' => $_POST['frequencyperiod'], 'repeat' => $_POST['frequencynumber'], 'end' => $_POST['enddate']);
        }
        $resp = Calendar_Events::add_event($newTitle, $newContent, $start, $enddateEvent, $duration, $recursion, $refobjid, $visibility);
        if ($resp['success']) {
            Session::flash('message', $langEventAdd);
            Session::flash('alert-class', 'alert-success');
        } else {
            Session::flash('message', $resp['message']);
            Session::flash('alert-class', 'alert-warning');
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
        Session::flash('message', $langEventDel);
        Session::flash('alert-class', 'alert-success');
    } else {
        Session::flash('message', $resp['message']);
        Session::flash('alert-class', 'alert-warning');
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



    if(isset($_GET['modify'])){

                    ///////////////////////////////Edit event in fullCalendar/////////////////////////////

                    $showEventUser = '';
                    if (isset($_GET['admin']) and $is_admin) {
                        $showEventUser = 'admin';
                        $eventID = $_GET['modify'];
                        $startDateEvent = Database::get()->querySingle("SELECT start FROM admin_calendar WHERE id = ?d",$eventID)->start;
                        $startDateEvent = date('Y-m-d',strtotime($startDateEvent));
                    }else{
                        $showEventUser = 'simpleUser';
                        $eventID = $_GET['modify'];
                        $startDateEvent = Database::get()->querySingle("SELECT start FROM personal_calendar WHERE id = ?d",$eventID)->start;
                        $startDateEvent = date('Y-m-d',strtotime($startDateEvent));
                    }


                    $tool_content .= "
                        <div class='col-12 calendar-events-container'>
                            <div class='card panelCard px-lg-4 py-lg-3 h-100'>
                                <div class='card-body'>
                                    <div id='EditCalendarEvents' class='myCalendarEvents'></div>
                                </div>
                            </div>
                        </div>";


                    $tool_content .= "
                        <div id='editEventModal' class='modal fade in' role='dialog'>
                            <form id='myeventform' class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]'>
                                <div class='modal-dialog modal-md'>

                                    <!-- Modal content-->
                                        <div class='modal-content'>
                                            <div class='modal-header border-0'>
                                                <div class='modal-title'>$langAddEvent</div> 
                                                <button aria-label='$langClose' type='button' class='close' data-bs-dismiss='modal'>
                                                </button>
                                            </div>
                                            <div class='modal-body'>
                                                <div class='form-wrapper form-edit rounded border-0 px-0'>
                                                    
                                                    <input type='hidden' id='id' name='id' value='$eventToModify'>
                                                    <input type='hidden' name='rep' id='rep' value='$applytogroup'>

                                                    <input type='hidden' name='startdate' id='startdate'>
                                                    <input type='hidden' name='enddateEvent' id='enddateEvent'>
                                                    <input type='hidden' name='duration' id='duration'>


                                                    <div class='form-group'>

                                                        <div class='form-label'>$langStartDate</div>
                                                        <div id='fromNewDate'></div>

                                                        <div class='control-label-notes mt-2'>$langDuration <small>$langInHour</small></div>
                                                        <div class='small-text'>$durationToModify</div>

                                                        <div class='control-label-notes mt-2'>$langCalculateNewDuration <small>$langInHour</small></div>
                                                        <div class='d-flex justify-content-start align-items-center gap-2'>
                                                            <div id='idNewDuration'></div>
                                                            <label class='label-container' aria-label='$langSelect'>
                                                                <input type='checkbox' id='OnOffDuration' checked>
                                                                <span class='checkmark'></span>
                                                            </label>
                                                        </div>

                                                    </div>

                                                    <div class='form-group mt-4'>
                                                        <label for='newTitle' class='col-sm-12 control-label-notes'>$langTitle</label>
                                                        <div class='col-sm-12'>
                                                            <input class='form-control' type='text' name='newTitle' id='newTitle' value='$titleToModify' placeholder='$langTitle'>
                                                        </div>
                                                    </div>
                                
                                                    
                                                    <div class='form-group mt-4'>
                                                        <label for='newContent' class='col-sm-12 control-label-notes'>$langDescription</label>
                                                        <div class='col-sm-12'>
                                                            " . rich_text_editor('newContent', 4, 20, $contentToModify) . "
                                                        </div>
                                                    </div>


                                                    <div class='form-group mt-4'>
                                                        <label for='frequencynumber' class='col-sm-12 control-label-notes'>$langRepeat $langEvery</label>
                                                        <div class='row'>
                                                            <div class='col-md-6 col-12'>
                                                                    <select class='form-select' name='frequencynumber' id='frequencynumber'>
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
                                                    $tool_content .= "</select>
                                                            </div>
                                                            <div class='col-md-6 col-12 mt-md-0 mt-4'>
                                                                    <select class='form-select' name='frequencyperiod' id='frequencyperiod' aria-label='$langSelectFromMenu'>
                                                                        <option value=\"\">$langSelectFromMenu...</option>
                                                                        <option value=\"D\"{$selected['D']}>$langDays</option>
                                                                        <option value=\"W\"{$selected['W']}>$langWeeks</option>
                                                                        <option value=\"M\"{$selected['M']}>$langMonthsAbstract</option>
                                                                    </select>
                                                            </div>
                                                        </div>




                                                        <div class='input-append date mt-4' id='enddatecal' data-date='$langDate' data-date-format='dd-mm-yyyy'>
                                                            <label for='enddate' class='col-12 control-label-notes'>$langUntil</label>
                                                            <div class='col-12'>
                                                                <div class='input-group'>
                                                                    <span class='add-on input-group-text h-40px bg-input-default input-border-color border-end-0'><i class='fa-regular fa-calendar'></i></span>  
                                                                    <input class='form-control mt-0 border-start-0' type='text' name='enddate' id='enddate' value='$enddate' type='text' >
                                                                    
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>";

                                                    if (!isset($_GET['addAdminEvent']) && !isset($_GET['admin'])) {
                                                        $eventtype = 'personal';
                                                        $tool_content .= "

                                                        
                                                        <div class='form-group mt-4'>
                                                            <label class='col-sm-6 control-label-notes'>$langReferencedObject</label>
                                                            <div class='col-sm-12'>
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
                                                            <div class='form-group mt-4'>
                                                                <label for='visibility_level_id' class='col-sm-12 control-label-notes'>$langShowTo</label>
                                                                <div class='col-sm-12'>
                                                                    <select class='form-select' name='visibility_level' id='visibility_level_id'>
                                                                        <option value=\"" . USER_STUDENT . "\" " . $selectedvis[USER_STUDENT] . ">$langShowToAllregistered</option>
                                                                        <option value=\"" . USER_GUEST . "\" " . $selectedvis[USER_GUEST] . ">$langShowToAll</option>
                                                                        <option value='0' $selectedvis[0]>$langShowToAdminsOnly</option>
                                                                        <option value=\"" . USER_TEACHER . "\" " . $selectedvis[USER_TEACHER] . ">$langShowToAdminsandProfs</option>
                                                                    </select>
                                                                </div>
                                                            </div>";
                                                    }

                                $tool_content .= "</div>
                                            </div>
                                        
                                        
                                            <div class='modal-footer border-0'>
                                                <div class='form-group d-flex justify-content-center align-items-center'>
                                                    <input class='btn submitAdminBtn' type='button' id='submitEvent' name='submitEvent' value='$langSubmit'>
                                                    <a class='btn cancelAdminBtn ms-2' href='index.php'>$langCancel</a>
                                                </div>
                                            </div>
                                        </div>

                                </div>
                            </form>
                        </div>";

                    $head_content .= "
                        <script type='text/javascript'>
                            $(document).ready(function () {


                                //initial clicker duration
                                var isOnDuration = '';
                                if($('#OnOffDuration').is(':checked')){
                                    isOnDuration = 'true';
                                }else{
                                    isOnDuration = 'false';
                                }
                        
                                var calendar = $('#EditCalendarEvents').fullCalendar({
                                    header:{
                                        left: 'prev,next ',
                                        center: 'title',
                                        right: ''
                                    },
                                    defaultView: 'agendaWeek',
                                    defaultDate: '{$startDateEvent}',
                                    firstDay: (new Date().getDay()),
                                    slotDuration: '00:30' ,
                                    minTime: '08:00:00',
                                    maxTime: '23:00:00',
                                    editable: true,
                                    contentHeight:'auto',
                                    selectable: true,
                                    allDaySlot: false,
                                    displayEventTime: true,
                                    events: '{$urlServer}main/personal_calendar/test_edit_event.php?eventID={$eventID}&theUser={$showEventUser}',    

                                    
                                    eventRender: function( event, element, view ) {
                                        var title = element.find( '.fc-title' );
                                        title.html( title.text() );
                                        title.addClass('text-center');

                                        var time = element.find( '.fc-time' );
                                        time.addClass('text-center');

                                    },

                                    eventClick:  function(event) {

                                        var eventStart = event.start;
                                        var eventEnd = event.end;  

                                        startDay =  moment(eventStart).format('DD');
                                        endDay = moment(eventEnd).format('DD');

                                        if(parseInt(startDay)==parseInt(endDay)){

                                            startS = moment(eventStart).format('DD-MM-YYYY HH:mm');
                                            endS = moment(eventEnd).format('DD-MM-YYYY HH:mm');
    
                                            $('#editEventModal #fromNewDate').text(startS);
                                            $('#editEventModal #startdate').val(startS);
                                            $('#editEventModal #enddateEvent').val(endS);
    
                                            //duration time
                                            var duration_start = moment(eventStart).format('HH:mm');
                                            var duration_end = moment(eventEnd).format('HH:mm');
                                            var value_start = duration_start.split(':');
                                            var value_end = duration_end.split(':');
    
                                            var startDate = new Date(0, 0, 0, value_start[0], value_start[1], 0);
                                            var endDate = new Date(0, 0, 0, value_end[0], value_end[1], 0);
                                            var diff = endDate.getTime() - startDate.getTime();
                                            var hours = Math.floor(diff / 1000 / 60 / 60);
                                            diff -= hours * 1000 * 60 * 60;
                                            var minutes = Math.floor(diff / 1000 / 60);
        
                                            if (hours < 0){
                                                hours = hours + 24;
                                            }
                                            
                                            duration = (hours <= 9 ? '0' : '') + hours + ':' + (minutes <= 9 ? '0' : '') + minutes +':00';
    
                                            
    
                                            if(isOnDuration == 'true'){
                                                $('#editEventModal #duration').val(duration);
                                            }else{
                                                $('#editEventModal #duration').val('00:00:00');
                                            }
                                            
                                            $('#OnOffDuration').on('click',function(){
                                                if($('#OnOffDuration').is(':checked')){
                                                    $('#editEventModal #duration').val(duration);
                                                }else{
                                                    $('#editEventModal #duration').val('00:00:00');
                                                }
                                            }); 
        
                                            
                                            $('#editEventModal #idNewDuration').text(duration);
    
                                            $('#editEventModal').modal('toggle');    
                                        }else{
                                            alert('$langChooseDayAgain');
                                            window.location.reload();
                                        }

                                                                   
                                    },

                                    eventDrop: function(event){                                    

                                        var eventStart = event.start;
                                        var eventEnd = event.end;  

                                        startDay =  moment(eventStart).format('DD');
                                        endDay = moment(eventEnd).format('DD');

                                        if(parseInt(startDay)==parseInt(endDay)){
                                            startS = moment(eventStart).format('DD-MM-YYYY HH:mm');
                                            endS = moment(eventEnd).format('DD-MM-YYYY HH:mm');

                                            $('#editEventModal #fromNewDate').text(startS);
                                            $('#editEventModal #startdate').val(startS);
                                            $('#editEventModal #enddateEvent').val(endS);

                                            //duration time
                                            var duration_start = moment(eventStart).format('HH:mm');
                                            var duration_end = moment(eventEnd).format('HH:mm');
                                            var value_start = duration_start.split(':');
                                            var value_end = duration_end.split(':');

                                            var startDate = new Date(0, 0, 0, value_start[0], value_start[1], 0);
                                            var endDate = new Date(0, 0, 0, value_end[0], value_end[1], 0);
                                            var diff = endDate.getTime() - startDate.getTime();
                                            var hours = Math.floor(diff / 1000 / 60 / 60);
                                            diff -= hours * 1000 * 60 * 60;
                                            var minutes = Math.floor(diff / 1000 / 60);
        
                                            if (hours < 0){
                                                hours = hours + 24;
                                            }
                                            
                                            duration = (hours <= 9 ? '0' : '') + hours + ':' + (minutes <= 9 ? '0' : '') + minutes +':00';

                                            

                                            if(isOnDuration == 'true'){
                                                $('#editEventModal #duration').val(duration);
                                            }else{
                                                $('#editEventModal #duration').val('00:00:00');
                                            }
                                            
                                            $('#OnOffDuration').on('click',function(){
                                                if($('#OnOffDuration').is(':checked')){
                                                    $('#editEventModal #duration').val(duration);
                                                }else{
                                                    $('#editEventModal #duration').val('00:00:00');
                                                }
                                            }); 
        
                                            
                                            $('#editEventModal #idNewDuration').text(duration);

                                            $('#editEventModal').modal('toggle');  
                                        }else{
                                            alert('$langChooseDayAgain');
                                            window.location.reload();
                                        }

                                    },

                                    eventResize: function(event) {

                                        
                                        var eventStart = event.start;
                                        var eventEnd = event.end;  


                                        startDay =  moment(eventStart).format('DD');
                                        endDay = moment(eventEnd).format('DD');

                                        if(parseInt(startDay)==parseInt(endDay)){
                                            startS = moment(eventStart).format('DD-MM-YYYY HH:mm');
                                            endS = moment(eventEnd).format('DD-MM-YYYY HH:mm');
                                            

                                            $('#editEventModal #fromNewDate').text(startS);
                                            $('#editEventModal #startdate').val(startS);
                                            $('#editEventModal #enddateEvent').val(endS);

                                            //duration time
                                            var duration_start = moment(eventStart).format('HH:mm');
                                            var duration_end = moment(eventEnd).format('HH:mm');
                                            var value_start = duration_start.split(':');
                                            var value_end = duration_end.split(':');
                                            
                                            var startDate = new Date(0, 0, 0, value_start[0], value_start[1], 0);
                                            var endDate = new Date(0, 0, 0, value_end[0], value_end[1], 0);
                                            var diff = endDate.getTime() - startDate.getTime();
                                            var hours = Math.floor(diff / 1000 / 60 / 60);
                                            diff -= hours * 1000 * 60 * 60;
                                            var minutes = Math.floor(diff / 1000 / 60);
        
                                            if (hours < 0){
                                                hours = hours + 24;
                                            }
                                            
                                            duration = (hours <= 9 ? '0' : '') + hours + ':' + (minutes <= 9 ? '0' : '') + minutes +':00';

                    

                                            if(isOnDuration == 'true'){
                                                $('#editEventModal #duration').val(duration);
                                            }else{
                                                $('#editEventModal #duration').val('00:00:00');
                                            }
                                            
                                            $('#OnOffDuration').on('click',function(){
                                                if($('#OnOffDuration').is(':checked')){
                                                    $('#editEventModal #duration').val(duration);
                                                }else{
                                                    $('#editEventModal #duration').val('00:00:00');
                                                }
                                            }); 
        
                                            
                                            $('#editEventModal #idNewDuration').text(duration);

                                            $('#editEventModal').modal('toggle');  
                                        }else{
                                            alert('$langChooseDayAgain');
                                            window.location.reload();
                                        }
                                    }
                                    
                                });


                               

                            });

                        </script>
                    ";






        }else{

                /////////////////////////////////////////////////Create Event in FullCalendar/////////////////////////////////////////////////

                $tool_content .= "


                            <div class='col-12 calendar-events-container'>
                                <div class='card panelCard px-lg-4 py-lg-3 h-100'>
                                    <div class='card-body'>
                                        <div id='calendarEvents' class='myCalendarEvents'></div>
                                    </div>
                                </div>
                            </div>

                            <div id='createEventModal' class='modal fade in' role='dialog'>
                                <form id='myeventform' class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]'>
                                    <div class='modal-dialog modal-md'>

                                        <!-- Modal content-->
                                            <div class='modal-content'>
                                                <div class='modal-header border-0'>
                                                    <div class='modal-title'>$langAddEvent</div> 
                                                    <button aria-label='$langClose' type='button' class='close' data-bs-dismiss='modal'>
                                                    </button>
                                                </div>
                                                <div class='modal-body'>
                                                    <div class='form-wrapper form-edit rounded border-0 px-0'>

                                                        <div class='form-group'>
                                                            <div class='control-label-notes'>$langStartDate</div>
                                                            <div id='from'></div>
                                                            <div class='control-label-notes mt-3'>$langDuration <small>$langInHour</small></div>
                                                            <div class='d-flex justify-content-start align-items-center gap-2'>
                                                                <div id='idDuration'></div>
                                                                <label class='label-container' aria-label='$langSelect'>
                                                                    <input type='checkbox' id='OnOffDuration' checked>
                                                                    <span class='checkmark'></span>
                                                                </label>
                                                            </div>
                                                        </div>

                                                        <input type='hidden' id='id' name='id' value='$eventToModify'>
                                                        <input type='hidden' name='rep' id='rep' value='$applytogroup'>

                                                        <div class='form-group mt-4'>
                                                            <label for='newTitle' class='col-sm-12 control-label-notes'>$langTitle</label>
                                                            <div class='col-sm-12'>
                                                                <input class='form-control' type='text' name='newTitle' id='newTitle' value='$titleToModify' placeholder='$langTitle'>
                                                            </div>
                                                        </div>

                                                        <div class='form-group mt-4'>
                                                            <label for='newContent' class='col-sm-12 control-label-notes'>$langDescription</label>
                                                            <div class='col-sm-12'>
                                                                " . rich_text_editor('newContent', 4, 20, $contentToModify) . "
                                                            </div>
                                                        </div>


                                                        <input type='hidden' name='startdate' id='startdate'>
                                                        <input type='hidden' name='enddateEvent' id='enddateEvent'>
                                                        <input type='hidden' name='duration' id='duration'>
                                            
                                                        <div class='form-group mt-4'>
                                                            <label for='frequencynumber' class='col-sm-12 control-label-notes'>$langRepeat $langEvery</label>
                                                            <div class='row'>
                                                                <div class='col-md-6 col-12'>
                                                                    <select class='form-select' name='frequencynumber' id='frequencynumber'>
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
                                                    $tool_content .=
                                                                    "</select>
                                                                </div>
                                                                <div class='col-md-6 col-12 mt-md-0 mt-4'>
                                                                    <select class='form-select' name='frequencyperiod' id='frequencyperiod' aria-label='$langSelectFromMenu'>
                                                                        <option value=\"\">$langSelectFromMenu...</option>
                                                                        <option value=\"D\"{$selected['D']}>$langDays</option>
                                                                        <option value=\"W\"{$selected['W']}>$langWeeks</option>
                                                                        <option value=\"M\"{$selected['M']}>$langMonthsAbstract</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>";

                                                    $tool_content .= "<div class='input-append date mt-4' id='enddatecal' data-date='$langDate' data-date-format='dd-mm-yyyy'>
                                                                            <label for='enddate' class='col-sm-6 control-label-notes'>$langUntil</label>
                                                                            <div class='input-group'>
                                                                                <span class='add-on input-group-text h-40px bg-input-default input-border-color border-end-0'><i class='fa-regular fa-calendar'></i></span>  
                                                                                <input class='form-control mt-0 border-start-0' type='text' name='enddate' id='enddate' value='$enddate' type='text' >
                                                                                
                                                                            </div>
                                                                        </div>";

                                                        if (!isset($_GET['addAdminEvent']) && !isset($_GET['admin'])) {
                                                            $eventtype = 'personal';
                                                            $tool_content .= "
                                                
                                                            
                                                            <div class='form-group mt-4'>
                                                                <label for='refobjgentype' class='col-sm-6 control-label-notes mb-0'>$langReferencedObject</label>
                                                                <div class='col-sm-12 mt-0'>
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
                                                                <div class='form-group mt-4'>
                                                                    <label for='visibility_level_id' class='col-sm-6 control-label-notes'>$langShowTo</label>
                                                                    <div class='col-sm-12'>
                                                                        <select class='form-select' name='visibility_level' id='visibility_level_id'>
                                                                            <option value=\"" . USER_STUDENT . "\" " . $selectedvis[USER_STUDENT] . ">$langShowToAllregistered</option>
                                                                            <option value=\"" . USER_GUEST . "\" " . $selectedvis[USER_GUEST] . ">$langShowToAll</option>
                                                                            <option value='0' $selectedvis[0]>$langShowToAdminsOnly</option>
                                                                            <option value=\"" . USER_TEACHER . "\" " . $selectedvis[USER_TEACHER] . ">$langShowToAdminsandProfs</option>
                                                                        </select>
                                                                    </div>
                                                                </div>";
                                                        }


                                $tool_content .=   "</div>
                                                </div>
                                            
                                            
                                                <div class='modal-footer border-0'>
                                                    <div class='form-group d-flex justify-content-center align-items-center'>
                                                        <input class='btn submitAdminBtn' type='button' id='submitEvent' name='submitEvent' value='$langSubmit'>
                                                        <a class='btn cancelAdminBtn ms-2' href='index.php'>$langCancel</a>
                                                    </div>
                                                </div>
                                            </div>

                                    </div>
                                </form>
                            </div>
                
                
                ";



                $head_content .= "

                    <script type='text/javascript'>
                        $(document).ready(function () {

                            //initial clicker duration
                            var isOnDuration = '';
                            if($('#OnOffDuration').is(':checked')){
                                isOnDuration = 'true';
                            }else{
                                isOnDuration = 'false';
                            }
                    
                            var calendar = $('#calendarEvents').fullCalendar({
                                header:{
                                    left: 'prev,next ',
                                    center: 'title',
                                    right: ''
                                },
                                defaultView: 'agendaWeek',
                                slotDuration: '00:30' ,
                                minTime: '08:00:00',
                                maxTime: '23:00:00',
                                editable: true,
                                contentHeight:'auto',
                                selectable: true,
                                allDaySlot: false,
                                displayEventTime: true,                               

                                eventClick:  function(event) {
                                    var id = event.id;                                   
                                },
                                    
                                //header and other values
                                select: function(start, end) {

                                    startDay =  moment(start).format('DD');
                                    endDay = moment(end).format('DD');

                                    if(parseInt(startDay)==parseInt(endDay)){
                                    
                                        var max_start = $.fullCalendar.moment(start).format('h:mm:ss');
                                        var max_end = $.fullCalendar.moment(end).format('h:mm:ss');
        
                                        endtime = $.fullCalendar.moment(end).format('h:mm');
                                        starttime = $.fullCalendar.moment(start).format('dddd, Do MMMM YYYY, h:mm');
                                        var mywhen = starttime + ' - ' + endtime;
                                        
                                        startS = moment(start).format('DD-MM-YYYY HH:mm');
                                        endS = moment(end).format('DD-MM-YYYY HH:mm');

                                        //duration time
                                        var duration_start = moment(start).format('HH:mm');
                                        var duration_end = moment(end).format('HH:mm');
                                        var value_start = duration_start.split(':');
                                        var value_end = duration_end.split(':');

                                        var startDate = new Date(0, 0, 0, value_start[0], value_start[1], 0);
                                        var endDate = new Date(0, 0, 0, value_end[0], value_end[1], 0);
                                        var diff = endDate.getTime() - startDate.getTime();
                                        var hours = Math.floor(diff / 1000 / 60 / 60);
                                        diff -= hours * 1000 * 60 * 60;
                                        var minutes = Math.floor(diff / 1000 / 60);

                                        if (hours < 0){
                                            hours = hours + 24;
                                        }
                                        
                                        duration = (hours <= 9 ? '0' : '') + hours + ':' + (minutes <= 9 ? '0' : '') + minutes +':00';

                                        

                                        $('#createEventModal #from').text(mywhen);
                                        $('#createEventModal #startdate').val(startS);
                                        $('#createEventModal #enddateEvent').val(endS);

                                        if(isOnDuration == 'true'){
                                            $('#createEventModal #duration').val(duration);
                                        }else{
                                            $('#createEventModal #duration').val('00:00:00');
                                        }
                                        
                                        $('#OnOffDuration').on('click',function(){
                                            if($('#OnOffDuration').is(':checked')){
                                                $('#createEventModal #duration').val(duration);
                                            }else{
                                                $('#createEventModal #duration').val('00:00:00');
                                            }
                                        }); 

                                        
                                        $('#createEventModal #idDuration').text(duration);
                                        $('#createEventModal').modal('toggle');
                                    }else{
                                        alert('$langChooseDayAgain');
                                        window.location.reload();
                                    }
                                },

                                
                                
                            });

                        });

                    </script>
                
                ";
        }








} else {
    /* display actions toolbar */
    $action_bar =
                action_bar(array(
                    array('title' => $langAddEvent,
                        'url' => "$_SERVER[SCRIPT_NAME]?addEvent=1",
                        'icon' => 'fa-calendar-check',
                        'level' => 'primary-label',
                        'button-class' => 'btn-success'),
                    array('title' => $langAddAdminEvent,
                        'url' => "$_SERVER[SCRIPT_NAME]?addAdminEvent=1",
                        'icon' => 'fa-notes-medical',
                        'show' => $is_admin,
                        'button-class' => 'btn-success',
                        'level' => 'primary-label'),
                    array('title' => $langiCalExport,
                        'url' => "icalendar.php",
                        'icon' => 'fa-calendar',
                        'level' => 'primary'),
                ));
    $tool_content .= $action_bar;
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
        // Define iCal feed icon
        $link = "main/personal_calendar/icalendar.php?uid=$uid&amp;token=" .
            token_generate('ical' . $uid);
        define('RSS', $link);
        define('RSS_ICON', 'fa-calendar');
        define('RSS_TITLE', $langiCalFeed);
        $iCalFeedLink = $urlServer . $link;

        /* display events */
        $day = (isset($_GET['day'])) ? intval($_GET['day']) : null;
        $month = (isset($_GET['month'])) ? intval($_GET['month']) : null;
        $year = (isset($_GET['year'])) ? intval($_GET['year']) : null;
        $tool_content .= '
                <div id="calendar_wrapper" class="border-card rounded-3">
                    <div class="col-12 overflow-auto">
                        <div class="calendar-header">
                            <div class="col-12">
                                <div id="calendar-header" class="personal-calendar-header d-flex justify-content-between align-items-center flex-wrap">
                                   
                                        <div class="btn-group">
                                                <button class="btn bg-transparent text-agenda-title" data-calendar-nav="prev" aria-label="'.$langPrevious.'"><span class="fa fa-caret-left"></span>  ' . '' . '</button>
                                                <button class="btn bg-transparent text-agenda-title" data-calendar-nav="today">' . $langToday . '</button>
                                                <button class="btn bg-transparent text-agenda-title" data-calendar-nav="next" aria-label="'.$langNext.'">' . '' . ' <span class="fa fa-caret-right"></span> </button>
                                        </div>

                                        <div class="btn-group">
                                                <button class="btn bg-transparent text-agenda-title" data-calendar-view="year">' . $langYear . '</button>
                                                <button class="btn bg-transparent active text-agenda-title" data-calendar-view="month">' . $langMonth . '</button>
                                                <button class="btn bg-transparent text-agenda-title" data-calendar-view="week">' . $langWeek . '</button>
                                                <button class="btn bg-transparent text-agenda-title" data-calendar-view="day">' . $langDay . '</button>
                                        </div>

                                        
                                    
                                        
                                </div>
                            </div>
                        </div>'
                . '<div class="myPersonalCalendar" id="bootstrapcalendar" class="col-md-12"></div>'
                . '</div></div>' .
                "<script type='text/javascript'>" .
                '$(document).ready(function(){

        var calendar = $("#bootstrapcalendar").calendar(
                {
                    tmpl_path: "' . $urlAppend . 'js/bootstrap-calendar-master/tmpls/",
                    events_source: "' . $urlAppend . 'main/calendar_data.php",
                    language: "el-GR",
                    onAfterViewLoad: function(view) {
                                $(".calendar-header h6").text(this.getTitle());
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

        $(".tiny-icon-rss").click(function (e) {
            e.preventDefault();
            $("#iCalDescription").modal();
        });

        });

        </script>' . "
        <div class='modal fade' id='iCalDescription' tabindex='-1' role='dialog'>
            <div class='modal-dialog' role='document'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        <div class='modal-title'>$langiCalFeed</div> 
                        <button type='button' class='close' data-bs-dismiss='modal' aria-label='$langClose'>
                        </button>
                    </div>
                    <div class='modal-body'>
                        <form>
                            <div class='form-group'>
                                <p class='form-control-static'>$langiCalExplanation</p>
                            </div>
                            <div class='form-group mt-3'>
                                <input aria-label='$langText' type='text' class='form-control' value='$iCalFeedLink' readonly>
                            </div>
                            <div class='form-group text-end'>
                                <button class='btn cancelAdminBtn' data-bs-dismiss='modal'>$langClose</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>";

    }
}
add_units_navigation(TRUE);

draw($tool_content, 1, null, $head_content);
