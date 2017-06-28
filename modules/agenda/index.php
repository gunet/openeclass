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
            className: 'btn-danger'},
        no:{
            label: '$langNoJustThisOne',
            className: 'btn-warning'}}};

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
        if (isset($_POST['id']) and !empty($_POST['id'])) {  // update event
            $id = $_POST['id'];
            $recursion = null;
            if (!empty($_POST['frequencyperiod']) && intval($_POST['frequencynumber']) > 0 && !empty($_POST['enddate'])) {
                $recursion = array('unit' => $_POST['frequencyperiod'], 'repeat' => $_POST['frequencynumber'], 'end' => $_POST['enddate']);
            }            
            if(isset($_POST['rep']) && $_POST['rep'] == 'yes'){
                $resp = update_recursive_event($id, $event_title, $startdate, $duration, $content, $recursion);
            } else {
                $resp = update_event($id, $event_title, $startdate, $duration, $content, $recursion);
            }
            $agdx->store($id);
        } else { // add new event
            $recursion = null;            
            if (!empty($_POST['frequencyperiod']) && intval($_POST['frequencynumber']) > 0 && !empty($_POST['enddate'])) {
                $recursion = array('unit' => $_POST['frequencyperiod'], 'repeat' => $_POST['frequencynumber'], 'end' => $_POST['enddate']);
            }            
            $ev = add_event($event_title, $content, $startdate, $duration, $recursion);                                   
            foreach($ev['event'] as $id) {
                $agdx->store($id);                
            }
        }
        Session::Messages($langStoredOK, 'alert-success');
        redirect_to_home_page("modules/agenda/index.php?course=$course_code");
    } elseif (isset($_GET['delete']) && $_GET['delete'] == 'yes') {
        $resp = (isset($_GET['rep']) && $_GET['rep'] == 'yes')? delete_recursive_event($id):delete_event($id);
        $agdx->remove($id);
        $msgresp = ($resp['success'])? $langDeleteOK : $langDeleteError.": ".$resp['message'];
        $alerttype = ($resp['success'])? 'alert-success' : 'alert-error';
        
        Session::Messages($msgresp, $alerttype);
        redirect_to_home_page("modules/agenda/index.php?course=$course_code");              
    }
    $is_recursive_event = false;

    if (isset($_GET['addEvent']) or isset($_GET['edit'])) {
        $pageName = $langAddEvent;
        $tool_content .= action_bar(array(
                array('title' => $langBack,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                      'icon' => 'fa-reply',
                      'level' => 'primary-label',
                      'show' => $is_editor)));        
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
        $tool_content .= "<div class='form-wrapper'>";
        $tool_content .= "<form id='agendaform' class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
            <input type='hidden' id = 'id' name='id' value='$id'>"
                . "<input type='hidden' name='rep' id='rep' value='$applytogroup'>";
        @$tool_content .= "
            <div class='form-group'>
                <label for='event_title' class='col-sm-2 control-label'>$langTitle :</label>
                <div class='col-sm-10'>
                    <input type='text' class='form-control' id='event_title' name='event_title' placeholder='$langTitle' value='" . q($event_title) . "'>
                </div>
            </div>
            <div class='input-append date form-group' data-date='$langDate' data-date-format='dd-mm-yyyy'>
                <label for='startdate' class='col-sm-2 control-label'>$langDate :</label>
                <div class='col-sm-10'>
                    <div class='input-group'>
                        <input class='form-control' name='startdate' id='startdate' type='text' value = '" .$startdate . "'>
                        <div class='input-group-addon'><span class='add-on'><span class='fa fa-calendar fa-fw'></span></span></div>
                    </div>
                </div>
            </div>
            <div class='input-append bootstrap-timepicker form-group'>
                <label for='durationcal' class='col-sm-2 control-label'>$langDuration <small>$langInHour</small></label>
                <div class='col-sm-10'>
                    <div class='input-group add-on'>
                        <input class='form-control' name='duration' id='durationcal' type='text' class='input-small' value='" . $duration . "'>
                        <div class='input-group-addon add-on'><span class='fa fa-clock-o fa-fw'></span></div>
                    </div>
                </div>
            </div>";
        /**** Recursion paramneters *****/
             $tool_content .= "<div class='form-group'>
                                    <label for='Repeat' class='col-sm-2 control-label'>$langRepeat $langEvery</label>
                                <div class='col-sm-2'>
                                    <select class='form-control' name='frequencynumber' id='frequencynumber'>
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
            $tool_content .= "<div class='col-sm-2'>
                        <select class='form-control' name='frequencyperiod' id='frequencyperiod'>
                            <option value=\"\">$langSelectFromMenu...</option>
                            <option value=\"D\"{$selected['D']}>$langDays</option>
                            <option value=\"W\"{$selected['W']}>$langWeeks</option>
                            <option value=\"M\"{$selected['M']}>$langMonthsAbstract</option>
                        </select>
                        </div>
                    ";
            $tool_content .= "<div class='input-append date' data-date='$langDate' data-date-format='dd-mm-yyyy'>
                <label for='Enddate' class='col-sm-2 control-label'>$langUntil :</label>
                    <div class='col-sm-4'>
                        <div class='input-group'>
                            <input class='form-control' name='enddate' id='enddate' type='text' value = '" .$enddate . "'>
                            <div class='input-group-addon'><span class='add-on'><span class='fa fa-calendar fa-fw'></span></span></div>
                        </div>
                    </div>
                </div>
              </div>";
        /**** end of recursion paramneters *****/
         $tool_content .= "<div class='form-group'>
                        <label for='Detail' class='col-sm-2 control-label'>$langDetail :</label>
                        <div class='col-sm-10'>" . rich_text_editor('content', 4, 20, $content) . "</div>
                      </div>            
                      <div class='form-group'>
                        <div class='col-sm-offset-2 col-sm-10'>".
                            form_buttons(array(
                                array(
                                    'text'  => $langSave,
                                    'name'  => 'submitbtn',
                                    'value' => $langAddModify,
                                    'id' => 'submitbtn'
                                ),
                                array(
                                    'href' => "index.php?course=$course_code",
                                )
                            ))
                            ."
                        </div>
                      </div>                
            </form></div>";
    }
}
    /* ---------------------------------------------
     *  End  of  prof only
     * ------------------------------------------- */
// display action bar
if (!isset($_GET['addEvent']) && !isset($_GET['edit'])) {        
    $tool_content .= action_bar(array(
            array('title' => $langAddEvent,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addEvent=1",
                  'icon' => 'fa-plus-circle',
                  'level' => 'primary-label',
                  'button-class' => 'btn-success',
                  'show' => $is_editor),
            array('title' => $langListCalendar,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                      'icon' => 'fa-list',
                      'level' => 'primary-label',
                      'button-class' => 'btn-default',
                      'show' => (($view == EVENT_LIST_VIEW) and (!isset($id)))),
            array('title' => $langListAll,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;v=1",
                      'icon' => 'fa-list',
                      'level' => 'primary-label',
                      'button-class' => 'btn-default',
                      'show' => ($view == EVENT_CALENDAR_VIEW or isset($id))),
            array('title' => $langiCalExport,
                  'url' => "icalendar.php?c=$course_id",
                  'icon' => 'fa-calendar',
                  'level' => 'primary')
        ));    
    if (isset($_GET['id'])) {
       $cal_content_list = event_list_view($id);
    } else {
        $cal_content_list = event_list_view();
    }
    if ($view == EVENT_LIST_VIEW) {
        $tool_content .= "<div class='row'><div class='col-md-12'>$cal_content_list</div></div>";
    } else {
        $tool_content .= ''
                . '<div id="calendar_wrapper" class="row">
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
                . '</div></div>';

        $tool_content .= "<script type='text/javascript'>" .
        '$(document).ready(function(){        
            var calendar = $("#bootstrapcalendar").calendar(
            {
                tmpl_path: "' . $urlAppend . 'js/bootstrap-calendar-master/tmpls/",
                events_source: "' . $urlAppend . 'modules/agenda/calendar_data.php?course='.$course_code.'",
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
