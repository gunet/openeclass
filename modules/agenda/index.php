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
$helpTopic = 'Agenda';
$guest_allowed = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';
require_once 'include/action.php';
require_once 'include/log.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'modules/search/agendaindexer.class.php';
require_once 'modules/agenda/course_calendar.inc.php';
ModalBoxHelper::loadModalBox();

$action = new action();
$action->record(MODULE_ID_AGENDA);

$dateNow = date("j-n-Y / H:i", time());

$nameTools = $langAgenda;

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
}

load_js('tools.js');
load_js('bootstrap-datetimepicker');
load_js('bootstrap-timepicker');
load_js('bootstrap-datepicker');
 
$head_content .= "
<script type='text/javascript'>
var dialogUpdateOptions = {
    title: '$langConfirmUpdate',
    message: '$langConfirmUpdateRecursiveEvents',
    buttons: {
        cancel:{label: '$langCancel',
        callback: function() {
                      return false;
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
    $('#submitbtn').on('click', 
            function(e){
                checkrequired($('#agendaform'));
    });
});
$(function() {
    $('#startdatecal').datetimepicker({
        format: 'dd-mm-yyyy hh:ii', pickerPosition: 'bottom-left', 
        language: '".$language."',
        autoclose: true
    });
    $('#enddatecal').datepicker({
        format: 'dd-mm-yyyy', pickerPosition: 'bottom-left', 
        language: '".$language."',
        autoclose: true
    });
    $('#durationcal').timepicker({showMeridian: false, minuteStep: 1, defaultTime: false });
});
</script>";

if ($is_editor and (isset($_GET['addEvent']) or isset($_GET['id']))) {

    //--if add event
    $head_content .= <<<hContent
<script type="text/javascript">
function checkrequired(thisform) {
    if ($('#event_title').val()=='' || $('#startdate').val()=='') {
            bootbox.alert("$langTitleDateNotEmpty");
            return false;
    }
    if($('#id').val()>0 && $('#rep').val() != ''){
        bootbox.dialog(dialogUpdateOptions);
    } else {
        thisform.submit();
    }
}
</script>
hContent;
}

$result = Database::get()->queryArray("SELECT id FROM agenda WHERE course_id = ?d", $course_id);
    if (count($result) > 1) {
        if (isset($_GET["sens"]) && $_GET["sens"] == "d") {
            $sens = " DESC"; 
        } else {
            $sens = " ASC";    
        }
    }
    
// display action bar
if (isset($_GET['addEvent']) or isset($_GET['edit'])) {
    $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                  'icon' => 'fa-reply',
                  'level' => 'primary',
                  'show' => $is_editor)));
} else {
    $tool_content .= action_bar(array(
            array('title' => $langAddEvent,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addEvent=1",
                  'icon' => 'fa-plus-circle',
                  'level' => 'primary-label',
                  'button-class' => 'btn-success',
                  'show' => $is_editor),
            array('title' => $langOldToNew,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;sens=",
                  'icon' => 'fa-arrows-v',
                  'level' => 'primary-label',
                  'show' => count($result) and (isset($_GET['sens']) and $_GET['sens'] == "d")),
            array('title' => $langOldToNew,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;sens=d",
                  'icon' => 'fa-arrows-v',
                  'level' => 'primary-label',
                  'show' => count($result) and (!isset($_GET['sens']) or (isset($_GET['sens']) and $_GET['sens'] != "d")))
        ));                        
}

if ($is_editor) {
    $agdx = new AgendaIndexer();
    // modify visibility
    if (isset($_GET['mkInvisibl']) and $_GET['mkInvisibl'] == true) {
        Database::get()->query("UPDATE agenda SET visible = 0 WHERE course_id = ?d AND id = ?d", $course_id, $id);
        $agdx->store($id);
    } elseif (isset($_GET['mkVisibl']) and ( $_GET['mkVisibl'] == true)) {
        Database::get()->query("UPDATE agenda SET visible = 1 WHERE course_id = ?d AND id = ?d", $course_id, $id);
        $agdx->store($id);
    }
    if (isset($_POST['event_title'])) {
        register_posted_variables(array('startdate' => true, 'event_title' => true, 'content' => true, 'duration' => true));
        $content = purify($content);
        if (isset($_POST['id']) and !empty($_POST['id'])) {  // update event
            $id = $_POST['id'];                        
            if(isset($_POST['rep']) && $_POST['rep'] == 'yes'){
                $resp = update_recursive_event($id, $event_title, $startdate, $duration, $content);
            } else {
                $resp = update_event($id, $event_title, $startdate, $duration, $content);
            }
            $agdx->store($id);
        } else {
            $recursion = null;            
            if (!empty($_POST['frequencyperiod']) && intval($_POST['frequencynumber']) > 0 && !empty($_POST['enddate'])) {
                $recursion = array('unit' => $_POST['frequencyperiod'], 'repeat' => $_POST['frequencynumber'], 'end' => $_POST['enddate']);
            }            
            $ev = add_event($event_title, $content, $startdate, $duration, $recursion);                                   
            foreach($ev['event'] as $id) {
                $agdx->store($id);                
            }
        }        
        $tool_content .= "<div class='alert alert-success text-center' role='alert'>$langStoredOK</div>";        
    } elseif (isset($_GET['delete']) && $_GET['delete'] == 'yes') {
        $resp = (isset($_GET['rep']) && $_GET['rep'] == 'yes')? delete_recursive_event($id):delete_event($id);
        $agdx->remove($id);
        $msgresp = ($resp['success'])? $langDeleteOK : $langDeleteError.": ".$resp['message'];
        $alerttype = ($resp['success'])? 'alert-success' : 'alert-error';
        $tool_content .= "<div class='alert $alerttype text-center' role='alert'>$msgresp</div><br>";        
    }

    if (isset($_GET['addEvent']) or isset($_GET['edit'])) {
        $nameTools = $langAddEvent;
        $navigation[] = array("url" => $_SERVER['SCRIPT_NAME'] . "?course=$course_code", "name" => $langAgenda);
        $rep = '';
        if (isset($id) && $id) {
            $myrow = Database::get()->querySingle("SELECT id, title, content, start, duration FROM agenda WHERE course_id = ?d AND id = ?d", $course_id, $id);
            if ($myrow) {
                $id = $myrow->id;
                $event_title = $myrow->title;
                $content = $myrow->content;
                $startdate = date('d-m-Y H:i', strtotime($myrow->start));
                $duration = $myrow->duration;
                $rep = (is_recursive($myrow->id))? 'no':''; 
            }
        } else {
            $id = $content = $duration = '';
            $startdate = date('d-m-Y H:i', strtotime('now'));
            $enddate = date('d-m-Y', strtotime('now +1 week'));
        } 
        $tool_content .= "<div class='form-wrapper'>";
        $tool_content .= "<form id='agendaform' class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
            <input type='hidden' id = 'id' name='id' value='$id'>"
                . "<input type='hidden' name='rep' id='rep' value='$rep'>";
        @$tool_content .= "
            <div class='form-group'>
                <label for='event_title' class='col-sm-2 control-label'>$langTitle :</label>
                <div class='col-sm-10'>
                    <input type='text' class='form-control' id='event_title' name='event_title' placeholder='$langTitle' value='" . q($event_title) . "'>
                </div>
            </div>
            <div class='input-append date form-group' id='startdatecal' data-date='$langDate' data-date-format='dd-mm-yyyy'>
                <label for='startdate' class='col-sm-2 control-label'>$langDate :</label>
                <div class='col-xs-10 col-sm-9'>        
                    <input class='form-control' name='startdate' id='startdate' type='text' value = '" .$startdate . "'>
                </div>
                <div class='col-xs-2 col-sm-1'>  
                    <span class='add-on'><i class='fa fa-times'></i></span>
                    <span class='add-on'><i class='fa fa-calendar'></i></span>
                </div>
            </div>
            <div class='input-append bootstrap-timepicker form-group'>
                <label for='durationcal' class='col-sm-2 control-label'>$langDuration <small>$langInHour</small></label>
                <div class='col-xs-10 col-sm-9'>
                    <input class='form-control' name='duration' id='durationcal' type='text' class='input-small' value='" . $duration . "'>
                </div>
                <div class='col-xs-2 col-sm-1'>
                    <span class='add-on'><i class='icon-time'></i></span>
                </div>
            </div>";
        if(!isset($_GET['edit'])) {
            $tool_content .= "<div class='form-group'>
                                    <label for='Repeat' class='col-sm-2 control-label'>$langRepeat $langEvery</label>
                                <div class='col-sm-2'>
                                    <select class='form-control' name='frequencynumber'>
                                    <option value='0'>$langSelectFromMenu</option>";
            for($i = 1;$i<10;$i++) {
                $tool_content .= "<option value=\"$i\">$i</option>";
            }
            $tool_content .= "</select></div>";            
            $tool_content .= "<div class='col-sm-2'>
                        <select class='form-control' name='frequencyperiod'>
                            <option value=\"D\">$langSelectFromMenu...</option>
                            <option value=\"D\">$langDays</option>
                            <option value=\"W\">$langWeeks</option>
                            <option value=\"M\">$langMonthsAbstract</option>
                        </select>
                        </div>
                    </div>";
            $tool_content .= "<div class='input-append date form-group' id='enddatecal' data-date='$langDate' data-date-format='dd-mm-yyyy'>
                <label for='Enddate' class='col-sm-2 control-label'>$langUntil :</label>
                    <div class='col-xs-10 col-sm-9'>
                        <input class='form-control' name='enddate' id='enddate' type='text' value = '" . $enddate . "'>
                    </div>
                    <div class='col-xs-2 col-sm-1'>  
                        <span class='add-on'><i class='fa fa-times'></i></span>
                        <span class='add-on'><i class='fa fa-calendar'></i></span>
                    </div>
                </div>";
        }
                
        $tool_content .= "<div class='form-group'>
                        <label for='Detail' class='col-sm-2 control-label'>$langDetail :</label>
                        <div class='col-sm-10'>" . rich_text_editor('content', 4, 20, $content) . "</div>
                      </div>            
                      <div class='form-group'>
                        <div class='col-sm-offset-2 col-sm-10'>
                            <input type='button' class='btn btn-default' id='submitbtn' name='submitbtn' value='$langAddModify'>
                        </div>
                      </div>                
            </form></div>";
    }
}

/* ---------------------------------------------
 *  End  of  prof only
 * ------------------------------------------- */
if (!isset($_GET['sens'])) {
    $sens = " ASC";
}
if ($is_editor) {
    $result = Database::get()->queryArray("SELECT id, title, content, start, duration, visible, recursion_period, recursion_end FROM agenda WHERE course_id = ?d
		ORDER BY start " . $sens, $course_id);
} else {
    $result = Database::get()->queryArray("SELECT id, title, content, start, duration, visible FROM agenda WHERE course_id = ?d
		AND visible = 1 ORDER BY start " . $sens, $course_id);
}

if (count($result) > 0) {
    $barMonth = '';
    $nowBarShowed = false;
    $tool_content .= "<div class='table-responsive'><table class='table-default'>
                    <tr><th class='left'>$langEvents</th>";
    if ($is_editor) {
        $tool_content .= "<th class='text-center'>" . icon('fa-gears') . "</th>";
    } else {
        $tool_content .= "<th width='50'>&nbsp;</th>";
    }
    $tool_content .= "</tr>";

    foreach ($result as $myrow) {
        $content = standard_text_escape($myrow->content);
        $d = strtotime($myrow->start);
        if (!$nowBarShowed) {
            // Following order
            if ((($d > time()) and ($sens == " ASC")) or ( ($d < time()) and ( $sens == " DESC "))) {
                if ($barMonth != date("m", time())) {
                    $barMonth = date("m", time());
                    $tool_content .= "<tr>";
                    // current month
                    $tool_content .= "<td colspan='2' class='monthLabel'>" . $langCalendar . "&nbsp;<b>" . ucfirst(claro_format_locale_date("%B %Y", time())) . "</b></td>";
                    $tool_content .= "</tr>";
                }
                $nowBarShowed = TRUE;
                $tool_content .= "<tr>";
                $tool_content .= "<td colspan='2' class='today'>$langDateNow $dateNow</td>";
                $tool_content .= "</tr>";
            }
        }
        if ($barMonth != date("m", $d)) {
            $barMonth = date("m", $d);
            // month LABEL
            $tool_content .= "<tr>";            
            $tool_content .= "<td colspan='2' class='monthLabel'>";            
            $tool_content .= "<div align='center'>" . $langCalendar . "&nbsp;<b>" . ucfirst(claro_format_locale_date("%B %Y", $d)) . "</b></div></td>";
            $tool_content .= "</tr>";
        }
         
        $classvis = '';         
        if ($is_editor) {
            if ($myrow->visible == 0) {
                $classvis = 'class = "not_visible"';
            }
        }
        $tool_content .= "<tr $classvis>";
        if ($is_editor) {
            $tool_content .= "<td>";
        } else {
            $tool_content .= "<td colspan='2'>";
        }

        $tool_content .= "<span class='day'>" . ucfirst(claro_format_locale_date($dateFormatLong, $d)) . "</span> ($langHour: " . ucfirst(date('H:i', $d)) . ")";
        if ($myrow->duration != '') {
            if ($myrow->duration == 1) {
                $message = $langHour;
            } else {
                $message = $langHours;
            }
            $msg = "($langDuration: " . q($myrow->duration) . " $message)";
        } else {
            $msg = '';
        }
        $tool_content .= "<br><b>";
        if ($myrow->title == '') {
            $tool_content .= $langAgendaNoTitle;
        } else {
            $tool_content .= q($myrow->title);
        }
        $tool_content .= " $msg $content</b></td>";

        if ($is_editor) {
            $tool_content .= "<td class='option-btn-cell'>";
            $tool_content .= action_button(array(
                    array('title' => $langModify,
                          'url' => "?course=$course_code&amp;id=$myrow->id&amp;edit=true",
                          'icon' => 'fa-edit'),
                    array('title' => $langConfirmDeleteRecursive,
                          'url' => "?course=$course_code&amp;id=$myrow->id&amp;delete=yes&amp;rep=yes",
                          'icon' => 'fa-times-circle-o',
                          'class' => 'delete',
                          'confirm' => $langConfirmDeleteRecursiveEvents,
                          'show' => !(is_null($myrow->recursion_period) || is_null($myrow->recursion_end))),
                    array('title' => $langDelete,
                          'url' => "?course=$course_code&amp;id=$myrow->id&amp;delete=yes",
                          'icon' => 'fa-times',
                          'class' => 'delete',
                          'confirm' => $langConfirmDeleteEvent),
                    array('title' => $langVisible,
                          'url' => "?course=$course_code&amp;id=$myrow->id" . ($myrow->visible? "&amp;mkInvisibl=true" : "&amp;mkVisibl=true"),
                          'icon' => $myrow->visible ? 'fa-eye' : 'fa-eye-slash')
                ));
           $tool_content .= "</td>";                                 
        }
        $tool_content .= "</tr>";
    }
    $tool_content .= "</table></div>";
} else {
    $tool_content .= "<p class='alert alert-warning text-center'>$langNoEvents</p>";
}
add_units_navigation(TRUE);

draw($tool_content, 2, null, $head_content);
