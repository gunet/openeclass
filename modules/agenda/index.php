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
 * @description: agenda module
 * @file: index.php
 */
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'agenda';
$guest_allowed = true;

require_once '../../include/baseTheme.php';
require_once 'include/action.php';
require_once 'include/log.class.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'modules/search/agendaindexer.class.php';
require_once 'modules/agenda/course_calendar.inc.php';
ModalBoxHelper::loadModalBox();

$action = new action();
$action->record(MODULE_ID_AGENDA);
// define different views of agenda
define('EVENT_LIST_VIEW', 1);
define('EVENT_CALENDAR_VIEW', 0);

$dateNow = date("j-n-Y / H:i", time());

$toolName = $langAgenda;

if (isset($_GET['v'])) {
    $v = intval($_GET['v']); // get agenda view
    if ($v == 1) {
        $view = EVENT_LIST_VIEW; // list view
    } else {
        $view = EVENT_CALENDAR_VIEW; // calendar view
    }
} else if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $view = EVENT_LIST_VIEW;
} else {
    $view = EVENT_CALENDAR_VIEW; // default is calendar view
}

// list view if we want a specific event


load_js('tools.js');
load_js('bootstrap-datetimepicker');
load_js('bootstrap-timepicker');
load_js('bootstrap-datepicker');
if (!empty($langLanguageCode)) {
    load_js('bootstrap-calendar-master/js/language/' . $langLanguageCode . '.js');
}
load_js('bootstrap-calendar-master/js/calendar.js');
load_js('bootstrap-calendar-master/components/underscore/underscore-min.js');

$head_content .= "
<link rel='stylesheet' type='text/css' href='{$urlAppend}js/bootstrap-calendar-master/css/calendar.css' />
<script type='text/javascript'>
var dialogUpdateOptions = {
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
                           $('#agendaform').submit();
                      }
            },
        no:{
            label: '$langNoJustThisOne',
            className: 'btn-info',
            callback: function() {
                           $('#rep').val('no');
                           $('#agendaform').submit();
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

    $('#submitbtn').on('click',
            function(e){
                e.preventDefault();
                checkrequired($('#agendaform'));
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
$(function() {
    $('#startdate').datetimepicker({
        format: 'dd-mm-yyyy hh:ii', pickerPosition: 'bottom-right',
        language: '".$language."',
        autoclose: true
    });
    $('#enddate').datepicker({
        format: 'dd-mm-yyyy', pickerPosition: 'bottom-right',
        language: '".$language."',
        autoclose: true
    });
    $('#durationcal').timepicker({showMeridian: false, minuteStep: 1, defaultTime: false });
});
</script>";

if ($is_editor and (isset($_GET['addEvent']) or isset($_GET['id']))) {

    //--if add event
    $head_content .=
"<script type='text/javascript'>
function checkrequired(thisform) {
    if ($('#event_title').val()=='' || $('#startdate').val()=='') {
            bootbox.alert('$langTitleDateNotEmpty');
            return false;
    }
    if($('#id').val()>0 && $('#rep').val() != ''){
        bootbox.dialog(dialogUpdateOptions);
    } else {
        thisform.submit();
    }
}
</script>";
}

if ($is_editor) {
    $agdx = new AgendaIndexer();
    // modify visibility
    if (isset($_GET['mkInvisibl']) and $_GET['mkInvisibl'] == true) {
        Database::get()->query("UPDATE agenda SET visible = 0 WHERE course_id = ?d AND id = ?d", $course_id, $id);
        $agdx->store($id);
        redirect_to_home_page("modules/agenda/index.php?course=$course_code&v=1");
    } elseif (isset($_GET['mkVisibl']) and ( $_GET['mkVisibl'] == true)) {
        Database::get()->query("UPDATE agenda SET visible = 1 WHERE course_id = ?d AND id = ?d", $course_id, $id);
        $agdx->store($id);
        redirect_to_home_page("modules/agenda/index.php?course=$course_code&v=1");
    }
    if (isset($_POST['event_title'])) {
        register_posted_variables(array('startdate' => true, 'event_title' => true, 'content' => true, 'duration' => true));
        $content = purify($content);
        $enddateEvent = $_POST['enddateEvent'];
        if (isset($_POST['id']) and !empty($_POST['id'])) {  // update event
            $id = $_POST['id'];
            $recursion = null;
            if (!empty($_POST['frequencyperiod']) && intval($_POST['frequencynumber']) > 0 && !empty($_POST['enddate'])) {
                $recursion = array('unit' => $_POST['frequencyperiod'], 'repeat' => $_POST['frequencynumber'], 'end' => $_POST['enddate']);
            }
            if(isset($_POST['rep']) && $_POST['rep'] == 'yes'){
                $resp = update_recursive_event($id, $event_title, $startdate, $enddateEvent, $duration, $content, $recursion);
            } else {
                $resp = update_event($id, $event_title, $startdate, $enddateEvent, $duration, $content, $recursion);
            }
            $agdx->store($id);
        } else { // add new event
            $recursion = null;
            if (!empty($_POST['frequencyperiod']) && intval($_POST['frequencynumber']) > 0 && !empty($_POST['enddate'])) {
                $recursion = array('unit' => $_POST['frequencyperiod'], 'repeat' => $_POST['frequencynumber'], 'end' => $_POST['enddate']);
            }
            $ev = add_event($event_title, $content, $startdate, $enddateEvent, $duration, $recursion);
            foreach($ev['event'] as $id) {
                $agdx->store($id);
            }
        }
        Session::flash('message',$langStoredOK);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/agenda/index.php?course=$course_code");
    } elseif (isset($_GET['delete']) && $_GET['delete'] == 'yes') {
        $resp = (isset($_GET['rep']) && $_GET['rep'] == 'yes')? delete_recursive_event($id):delete_event($id);
        $agdx->remove($id);
        $msgresp = ($resp['success'])? $langDeleteOK : $langDeleteError.": ".$resp['message'];
        $alerttype = ($resp['success'])? 'alert-success' : 'alert-error';

        Session::flash('message',$msgresp);
        Session::flash('alert-class', $alerttype);
        redirect_to_home_page("modules/agenda/index.php?course=$course_code");
    }
    $is_recursive_event = false;

    if (isset($_GET['addEvent']) or isset($_GET['edit'])) {
        $pageName = $langAddEvent;
        $navigation[] = array("url" => $_SERVER['SCRIPT_NAME'] . "?course=$course_code", "name" => $langAgenda);
        $applytogroup = '';
        if (isset($id) && $id) {
            $myrow = Database::get()->querySingle("SELECT * FROM agenda WHERE course_id = ?d AND id = ?d", $course_id, $id);
            if ($myrow) {
                $id = $myrow->id;
                $event_title = $myrow->title;
                $content = $myrow->content;
                $startdate = date('d-m-Y H:i', strtotime($myrow->start));
                $duration = $myrow->duration;
                $applytogroup = '';
                $is_recursive_event = false;
                $enddate = '';
                if(is_recursive($myrow->id)){
                   $is_recursive_event = true;
                   $applytogroup = 'no';
                   $repeatnumber = substr($myrow->recursion_period, 1, strlen($myrow->recursion_period)-2);
                   $repeatperiod = substr($myrow->recursion_period, -1);
                   $repeatend_obj = DateTime::createFromFormat('Y-m-d', $myrow->recursion_end);
                   $enddate = $repeatend_obj->format('d-m-Y');
                }
            }
        } else {
            $id = $content = '';
            $duration = "0:00";
            $startdate = date('d-m-Y H:i', strtotime('now'));
            $enddate = '';
        }


        if(isset($_GET['edit'])){

                    $eventID = $id;
                    $startDateEvent = Database::get()->querySingle("SELECT start FROM agenda WHERE course_id = ?d AND id = ?d",$course_id,$id)->start;
                    $startDateEvent = date('Y-m-d',strtotime($startDateEvent));

                    $tool_content .= "
                                <div class='col-12 calendar-events-container'>
                                    <div class='card panelCard px-lg-4 py-lg-3 h-100'>
                                        <div class='card-body'>
                                            <div id='editAgendaEvents' class='myCalendarEvents'></div>
                                        </div>
                                    </div>
                                </div>

                                <div id='editAgendaEventModal' class='modal fade in' role='dialog'>
                                    <form id='agendaform' class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
                                        <div class='modal-dialog modal-md'>
                                            <!-- Modal content-->
                                            <div class='modal-content'>
                                                <div class='modal-header border-0'>
                                                    <div class='modal-title'>$langAddEvent</div>
                                                    <button type='button' class='close' data-bs-dismiss='modal' aria-label='Close'>
                                                    </button>
                                                </div>
                                                <div class='modal-body'>
                                                    <div class='form-wrapper form-edit border-0 px-0'>


                                                        <input type='hidden' id = 'id' name='id' value='$id'>
                                                        <input type='hidden' name='rep' id='rep' value='$applytogroup'>

                                                        <input type='hidden' name='startdate' id='startdate'>
                                                        <input type='hidden' name='enddateEvent' id='enddateEvent'>
                                                        <input type='hidden' name='duration' id='duration'>

                                                        <div class='form-group'>
                                                            <div class='control-label-notes'>$langStartDate</div>
                                                            <div id='fromNewDate'></div>

                                                            <div class='control-label-notes mt-3'>$langDuration <small>$langInHour</small></div>
                                                            <div class='small-text'>$duration</div>

                                                            <div class='control-label-notes mt-2'>$langCalculateNewDuration <small>$langInHour</small></div>
                                                            <div class='d-flex justify-content-start align-items-center gap-2'>
                                                                <div id='idNewDuration'></div>
                                                                <label class='label-container'>
                                                                    <input type='checkbox' id='OnOffDuration' checked>
                                                                    <span class='checkmark'></span>
                                                                </label>
                                                            </div>
                                                        </div>


                                                        <div class='row form-group mt-4'>
                                                            <label for='event_title' class='col-12 control-label-notes text-capitalize mb-0'>$langTitle</label>
                                                            <div class='col-12'>
                                                                <input type='text' class='form-control' id='event_title' name='event_title' placeholder='$langTitle' value='" . q($event_title) . "'>
                                                            </div>
                                                        </div>";



                                                        $tool_content .= "<div class='row form-group mt-4'>
                                                                                    <label class='col-12 control-label-notes text-capitalize'>$langRepeat $langEvery</label>

                                                                                    <div class='col-12'>
                                                                                    <div class='row'>
                                                                                <div class='col-md-6 col-12'>
                                                                                    <select class='form-select' name='frequencynumber' id='frequencynumber'>
                                                                                    <option value='0'>$langSelectFromMenu</option>";
                                                            for($i = 1;$i<10;$i++) {
                                                                $tool_content .= "<option value=\"$i\"";
                                                                if($is_recursive_event && $i == $repeatnumber){
                                                                    $tool_content .= ' selected';
                                                                }
                                                                $tool_content .= ">$i</option>";
                                                            }

                                                            $tool_content .= "</select></div>";
                                                            $selected = array('D'=>'', 'W'=>'','M'=>'');
                                                            if($is_recursive_event){
                                                                $selected[$repeatperiod] = ' selected';
                                                            }
                                                            $tool_content .= "<div class='col-md-6 col-12 mt-md-0 mt-4'>
                                                                        <select class='form-select' name='frequencyperiod' id='frequencyperiod'>
                                                                            <option value=\"\">$langSelectFromMenu...</option>
                                                                            <option value=\"D\"{$selected['D']}>$langDays</option>
                                                                            <option value=\"W\"{$selected['W']}>$langWeeks</option>
                                                                            <option value=\"M\"{$selected['M']}>$langMonthsAbstract</option>
                                                                        </select>
                                                                        </div></div></div>
                                                                    ";
                                                            $tool_content .= "<div class='row input-append date mt-4' data-date='$langDate' data-date-format='dd-mm-yyyy'>
                                                                <label for='enddate' class='col-12 control-label-notes text-capitalize mb-1'>$langUntil</label>
                                                                    <div class='col-12 ps-0 pe-0 ms-2'>
                                                                        <div class='input-group'>
                                                                            <span class='add-on input-group-text h-40px bg-input-default input-border-color border-end-0'><i class='fa-regular fa-calendar'></i></span>
                                                                            <input class='form-control mt-0 border-start-0' name='enddate' id='enddate' type='text' value = '" .$enddate . "'>

                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>";
                                                        /**** end of recursion paramneters *****/
                                                        $tool_content .= "<div class='row form-group mt-4'>
                                                                                <label class='col-12 control-label-notes text-capitalize'>$langDetail</label>
                                                                                <div class='col-12'>" . rich_text_editor('content', 4, 20, $content) . "</div>
                                                                            </div>



                                                    </div>
                                                </div>
                                                <div class='modal-footer border-0'>
                                                    <div class='col-md-9 col-12 d-flex justify-content-end align-items-center'>
                                                            ".
                                                            form_buttons(array(
                                                                array(
                                                                    'class' => 'submitAdminBtn',
                                                                    'text'  => $langSave,
                                                                    'name'  => 'submitbtn',
                                                                    'value' => $langAddModify,
                                                                    'id' => 'submitbtn'
                                                                ),
                                                                array(
                                                                    'class' => 'cancelAdminBtn ms-1',
                                                                    'href' => "index.php?course=$course_code",
                                                                )
                                                            ))
                                                            ."
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

                                var calendar = $('#editAgendaEvents').fullCalendar({
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
                                    events: '{$urlServer}modules/agenda/test_edit_event.php?eventID={$eventID}&course_id={$course_id}',


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

                                            $('#editAgendaEventModal #fromNewDate').text(startS);
                                            $('#editAgendaEventModal #startdate').val(startS);
                                            $('#editAgendaEventModal #enddateEvent').val(endS);

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
                                                $('#editAgendaEventModal #duration').val(duration);
                                            }else{
                                                $('#editAgendaEventModal #duration').val('00:00:00');
                                            }

                                            $('#OnOffDuration').on('click',function(){
                                                if($('#OnOffDuration').is(':checked')){
                                                    $('#editAgendaEventModal #duration').val(duration);
                                                }else{
                                                    $('#editAgendaEventModal #duration').val('00:00:00');
                                                }
                                            });


                                            $('#editAgendaEventModal #idNewDuration').text(duration);

                                            $('#editAgendaEventModal').modal('toggle');
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

                                            $('#editAgendaEventModal #fromNewDate').text(startS);
                                            $('#editAgendaEventModal #startdate').val(startS);
                                            $('#editAgendaEventModal #enddateEvent').val(endS);

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
                                                $('#editAgendaEventModal #duration').val(duration);
                                            }else{
                                                $('#editAgendaEventModal #duration').val('00:00:00');
                                            }

                                            $('#OnOffDuration').on('click',function(){
                                                if($('#OnOffDuration').is(':checked')){
                                                    $('#editAgendaEventModal #duration').val(duration);
                                                }else{
                                                    $('#editAgendaEventModal #duration').val('00:00:00');
                                                }
                                            });


                                            $('#editAgendaEventModal #idNewDuration').text(duration);

                                            $('#editAgendaEventModal').modal('toggle');
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


                                            $('#editAgendaEventModal #fromNewDate').text(startS);
                                            $('#editAgendaEventModal #startdate').val(startS);
                                            $('#editAgendaEventModal #enddateEvent').val(endS);

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
                                                $('#editAgendaEventModal #duration').val(duration);
                                            }else{
                                                $('#editAgendaEventModal #duration').val('00:00:00');
                                            }

                                            $('#OnOffDuration').on('click',function(){
                                                if($('#OnOffDuration').is(':checked')){
                                                    $('#editAgendaEventModal #duration').val(duration);
                                                }else{
                                                    $('#editAgendaEventModal #duration').val('00:00:00');
                                                }
                                            });


                                            $('#editAgendaEventModal #idNewDuration').text(duration);

                                            $('#editAgendaEventModal').modal('toggle');
                                        }else{
                                            alert('$langChooseDayAgain');
                                            window.location.reload();
                                        }
                                    }

                                });




                            });

                        </script>
                    ";







        } else {
            $event_title = '';

            $tool_content .= "
                <div class='col-12 calendar-events-container'>
                    <div class='card panelCard px-lg-4 py-lg-3 h-100'>
                        <div class='card-body'>
                            <div id='AgendaEvents' class='myCalendarEvents'></div>
                        </div>
                    </div>
                </div>

                <div id='createAgendaEventModal' class='modal fade in' role='dialog'>
                    <form id='agendaform' class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
                        <div class='modal-dialog modal-md'>

                            <!-- Modal content-->
                                <div class='modal-content'>
                                    <div class='modal-header border-0'>
                                        <div class='modal-title'>$langAddEvent</div>
                                        <button type='button' class='close' data-bs-dismiss='modal' aria-label='Close'>
                                        </button>
                                    </div>
                                    <div class='modal-body'>
                                        <div class='form-wrapper form-edit border-0 px-0'>

                                            <input type='hidden' id='id' name='id' value='$id'>
                                            <input type='hidden' name='rep' id='rep' value='$applytogroup'>

                                            <div class='form-group'>
                                                <div class='control-label-notes'>$langStartDate</div>
                                                <div id='from'></div>
                                                <div class='control-label-notes mt-3'>$langDuration <small>$langInHour</small></div>
                                                <div class='d-flex justify-content-start align-items-center gap-2'>
                                                    <div id='idDuration'></div>
                                                    <label class='label-container'>
                                                        <input type='checkbox' id='OnOffDuration' checked>
                                                        <span class='checkmark'></span>
                                                    </label>
                                                </div>
                                            </div>

                                            <input type='hidden' name='startdate' id='startdate'>
                                            <input type='hidden' name='enddateEvent' id='enddateEvent'>
                                            <input type='hidden' name='duration' id='durationcal'>

                                            <div class='form-group mt-4'>
                                                <label for='event_title' class='col-12 control-label-notes text-capitalize'>$langTitle</label>
                                                <div class='col-12'>
                                                    <input type='text' class='form-control' id='event_title' name='event_title' placeholder='$langTitle' value='" . q($event_title) . "'>
                                                </div>
                                            </div>

                                            <div class='row form-group mt-4'>
                                                <label class='col-12 control-label-notes text-capitalize'>$langDetail</label>
                                                <div class='col-12'>" . rich_text_editor('content', 4, 20, $content) . "</div>
                                            </div>";

                                            /**** Recursion parameters *****/
                                            $tool_content .= "<div class='row form-group mt-4'>
                                                                    <label class='col-12 control-label-notes text-capitalize'>$langRepeat $langEvery</label>

                                                                    <div class='col-12'>
                                                                    <div class='row'>
                                                                <div class='col-md-6 col-12'>
                                                                    <select class='form-select' name='frequencynumber' id='frequencynumber'>
                                                                    <option value='0'>$langSelectFromMenu</option>";
                                            for($i = 1;$i<10;$i++) {
                                                $tool_content .= "<option value=\"$i\"";
                                                if($is_recursive_event && $i == $repeatnumber){
                                                    $tool_content .= ' selected';
                                                }
                                                $tool_content .= ">$i</option>";
                                            }

                                            $tool_content .= "</select></div>";
                                            $selected = array('D'=>'', 'W'=>'','M'=>'');
                                            if($is_recursive_event){
                                                $selected[$repeatperiod] = ' selected';
                                            }
                                            $tool_content .= "<div class='col-md-6 col-12 mt-md-0 mt-4'>
                                                        <select class='form-select' name='frequencyperiod' id='frequencyperiod'>
                                                            <option value=\"\">$langSelectFromMenu...</option>
                                                            <option value=\"D\"{$selected['D']}>$langDays</option>
                                                            <option value=\"W\"{$selected['W']}>$langWeeks</option>
                                                            <option value=\"M\"{$selected['M']}>$langMonthsAbstract</option>
                                                        </select>
                                                        </div></div></div>
                                                    ";
                                        $tool_content .= "<div class='input-append date mt-4' data-date='$langDate' data-date-format='dd-mm-yyyy'>
                                            <label for='enddate' class='col-12 control-label-notes text-capitalize'>$langUntil</label>
                                                <div class='col-12'>
                                                    <div class='input-group'>
                                                        <span class='add-on input-group-text h-40px bg-input-default input-border-color border-end-0'><i class='fa-regular fa-calendar'></i></span>
                                                        <input class='form-control mt-0 border-start-0' name='enddate' id='enddate' type='text' value = '" .$enddate . "'>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>";

                  $tool_content .= "</div>
                            </div>
                            <div class='modal-footer border-0'>
                                <div class='form-group d-flex justify-content-center align-items-center'>
                                    ".
                                    form_buttons(array(
                                        array(
                                            'class' => 'submitAdminBtn',
                                            'text'  => $langSave,
                                            'name'  => 'submitbtn',
                                            'value' => $langAddModify,
                                            'id' => 'submitbtn'
                                        ),
                                        array(
                                            'class' => 'cancelAdminBtn ms-1',
                                            'href' => "index.php?course=$course_code",
                                        )
                                    ))
                                    ."
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

                        var calendar = $('#AgendaEvents').fullCalendar({
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

                                    $('#createAgendaEventModal #from').text(mywhen);
                                    $('#createAgendaEventModal #startdate').val(startS);
                                    $('#createAgendaEventModal #enddateEvent').val(endS);

                                    if(isOnDuration == 'true'){
                                        $('#createAgendaEventModal #durationcal').val(duration);
                                    }else{
                                        $('#createAgendaEventModal #durationcal').val('00:00:00');
                                    }

                                    $('#OnOffDuration').on('click',function(){
                                        if($('#OnOffDuration').is(':checked')){
                                            $('#createAgendaEventModal #durationcal').val(duration);
                                        }else{
                                            $('#createAgendaEventModal #durationcal').val('00:00:00');
                                        }
                                    });
                                    $('#createAgendaEventModal #idDuration').text(duration);
                                    $('#createAgendaEventModal').modal('toggle');
                                }else{
                                    alert('$langChooseDayAgain');
                                    window.location.reload();
                                }
                            },

                            eventDrop: function(event){

                            },

                            eventResize: function(event) {

                            }

                        });

                    });
                </script>
            ";
        }
    }
}
    /* ---------------------------------------------
     *  End  of  prof only
     * ------------------------------------------- */
// display action bar
if (!isset($_GET['addEvent']) && !isset($_GET['edit'])) {
    $action_bar = action_bar(array(
            array('title' => $langAddEvent,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addEvent=1",
                  'icon' => 'fa-plus-circle',
                  'level' => 'primary-label',
                  'button-class' => 'btn-success',
                  'show' => $is_editor),
            array('title' => $langListCalendar,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa-list',
                  'show' => (($view == EVENT_LIST_VIEW) and (!isset($id)))),
            array('title' => $langListAll,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;v=1",
                  'icon' => 'fa-list',
                  'show' => ($view == EVENT_CALENDAR_VIEW or isset($id))),
            array('title' => $langiCalExport,
                  'url' => "icalendar.php?c=$course_id",
                  'icon' => 'fa-calendar')
        ));
    $tool_content .= $action_bar;
    if (isset($_GET['id'])) {
       $cal_content_list = event_list_view($id);
    } else {
        $cal_content_list = event_list_view();
    }
    if ($view == EVENT_LIST_VIEW) {
        $tool_content .= "<div class='row'><div class='col-md-12'>$cal_content_list</div></div>";
    } else {
        $tool_content .= ''
                 . '
                 <div class="col-12 overflow-auto">
                    <div id="calendar_wrapper" class="border-card rounded-3">

                            <div class="calendar-header">

                                    <div id="calendar-header" class="personal-calendar-header d-flex justify-content-between align-items-center flex-wrap">

                                            <div class="btn-group">
                                                    <button class="btn bg-transparent text-agenda-title" data-calendar-nav="prev" aria-label="Previous"><span class="fa fa-caret-left"></span>  ' . '' . '</button>
                                                    <button class="btn bg-transparent text-agenda-title" data-calendar-nav="today">' . $langToday . '</button>
                                                    <button class="btn bg-transparent text-agenda-title" data-calendar-nav="next" aria-label="Next">' . '' . ' <span class="fa fa-caret-right"></span> </button>
                                            </div>
                                            <div class="btn-group">
                                                    <button class="btn bg-transparent text-agenda-title" data-calendar-view="year">' . $langYear . '</button>
                                                    <button class="btn bg-transparent active text-agenda-title" data-calendar-view="month">' . $langMonth . '</button>
                                                    <button class="btn bg-transparent text-agenda-title" data-calendar-view="week">' . $langWeek . '</button>
                                                    <button class="btn bg-transparent text-agenda-title" data-calendar-view="day">' . $langDay . '</button>
                                            </div>

                                        
                                    </div>

                            </div>'
                          . '
                                <div class="myPersonalCalendar" id="bootstrapcalendar" class="col-md-12"></div>
                            '
                      . '
                    </div>
                </div>';

        $tool_content .= "<script type='text/javascript'>" .
        '$(document).ready(function(){
            var calendar = $("#bootstrapcalendar").calendar(
            {
                tmpl_path: "' . $urlAppend . 'js/bootstrap-calendar-master/tmpls/",
                events_source: "' . $urlAppend . 'main/calendar_data.php?course='.$course_code.'",
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
                $("#bootstrapcalendar").show();
            });
        });

        $(".btn-group button[data-calendar-view]").each(function() {
            var $this = $(this);
            $this.click(function() {
                calendar.view($this.data("calendar-view"));
                $("#bootstrapcalendar").show();
            });
    });
    });

    </script>';
}
}
add_units_navigation(TRUE);

draw($tool_content, 2, null, $head_content);
