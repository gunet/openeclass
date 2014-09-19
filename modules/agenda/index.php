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
ModalBoxHelper::loadModalBox();

$action = new action();
$action->record(MODULE_ID_AGENDA);

$dateNow = date("j-n-Y / H:i", time());

$nameTools = $langAgenda;

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
}

load_js('tools.js');
load_js('jquery');
load_js('jquery-ui');
load_js('jquery-ui-timepicker-addon.min.js');

$head_content .= "<link rel='stylesheet' type='text/css' href='{$urlAppend}js/jquery-ui-timepicker-addon.min.css'>
<script type='text/javascript'>
$(function() {
    $('input[name=date]').datetimepicker({
    dateFormat: 'yy-mm-dd', 
    timeFormat: 'hh:mm'
    });
    $('input[name=enddate]').datepicker({
    dateFormat: 'yy-mm-dd'
    });
    $('input[name=duration]').timepicker({ 
    timeFormat: 'H:mm'
    });
});
</script>";

if ($is_editor and ( isset($_GET['addEvent']) or isset($_GET['id']))) {

    //--if add event
    $head_content .= <<<hContent
<script type="text/javascript">
function checkrequired(which, entry) {
	var pass=true;
	if (document.images) {
		for (i=0;i<which.length;i++) {
			var tempobj=which.elements[i];
			if (tempobj.name == entry) {
				if (tempobj.type=="text"&&tempobj.value=='') {
					pass=false;
					break;
		  		}
	  		}
		}
	}
	if (!pass) {
		alert("$langEmptyAgendaTitle");
		return false;
	} else {
		return true;
	}
}
</script>
hContent;
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
    if (isset($_POST['submit'])) {
        register_posted_variables(array('date' => true, 'event_title' => true, 'content' => true, 'duration' => true));
        $content = purify($content);
        if (isset($_POST['id']) and ! empty($_POST['id'])) {
            $id = intval($_POST['id']);
            Database::get()->query("UPDATE agenda SET title = ?s, content = ?s, start = ?t, duration = ?s
                        WHERE course_id = ?d AND id = ?d", $event_title, $content, $date, $duration, $course_id, $id);
            $log_type = LOG_MODIFY;
        } else {
            $period = "";
            $enddate = "";
            if(isset($_POST['frequencyperiod']) && isset($_POST['frequencynumber']) && isset($_POST['enddate']))
            {
                $period = "P".$_POST['frequencynumber'].$_POST['frequencyperiod'];
                $enddate = $_POST['enddate'];
            }
        
            $id = Database::get()->query("INSERT INTO agenda "
                        ."SET course_id = ?d, "
                        ."title = ?s, "
                        ."content = ?s, " 
                        ."start = ?t, "                                             
                        ."duration = ?t, "
                        . "recursion_period = ?t, "
                        . "recursion_end = ?t, " 
                        ."visible = 1", $course_id, $event_title, $content, $date, $duration, $period, $enddate)->lastInsertID;
            
            if(isset($id) && !is_null($id)){
                $log_type = LOG_INSERT;
                $agdx->store($id);
                $txt_content = ellipsize(canonicalize_whitespace(strip_tags($content)), 50, '+');
                Log::record($course_id, MODULE_ID_AGENDA, $log_type, array('id' => $id,
                                                            'date' => $date,
                                                            'duration' => $duration,
                                                            'title' => $event_title,
                                                            'content' => $txt_content));
                Database::get()->query("UPDATE agenda SET source_event_id = id WHERE id = ?d",$id);
                if(!empty($period) && !empty($enddate)){
                    $sourceevent = $id;
                    $interval = new DateInterval($period);
                    $startdatetime = new DateTime($date);
                    $enddatetime = new DateTime($enddate." 23:59:59");
                    $newdate = date_add($startdatetime, $interval);
                    while($newdate <= $enddatetime)
                    {
                        $neweventid = Database::get()->query("INSERT INTO agenda "
                                . "SET course_id = ?d, content = ?s, title = ?s, start = ?t, duration = ?t, visible = 1,"
                                . "recursion_period = ?s, recursion_end = ?t, "
                                . "source_event_id = ?d", 
                        $course_id, purify($content), $event_title, $newdate->format('Y-m-d H:i'), $duration, $period, $enddate, $sourceevent)->lastInsertID;
                        $agdx->store($id);
                        $txt_content = ellipsize(canonicalize_whitespace(strip_tags($content)), 50, '+');
                        Log::record($course_id, MODULE_ID_AGENDA, $log_type, array('id' => $neweventid,
                                                            'date' => $newdate->format('Y-m-d H:i'),
                                                            'duration' => $duration,
                                                            'title' => $event_title,
                                                            'content' => $txt_content));
                        
                        $newdate = date_add($startdatetime, $interval);
                    }
                }
            }
            $log_type = LOG_INSERT;
        }
        $agdx->store($id);
        $txt_content = ellipsize(canonicalize_whitespace(strip_tags($content)), 50, '+');
        Log::record($course_id, MODULE_ID_AGENDA, $log_type, array('id' => $id,
            'date' => $date,
            'duration' => $duration,
            'title' => $event_title,
            'content' => $txt_content));
        unset($id);
        unset($content);
        unset($event_title);
        $tool_content .= "<p class='success'>$langStoredOK</p><br>";
        unset($addEvent);
    } elseif (isset($_GET['delete']) && $_GET['delete'] == 'yes') {
        $row = Database::get()->querySingle("SELECT title, content, start, duration
                                        FROM agenda WHERE id = ?d", $id);
        $txt_content = ellipsize(canonicalize_whitespace(strip_tags($row->content)), 50, '+');
        Database::get()->query("DELETE FROM agenda WHERE course_id = ?d AND id = ?d", $course_id, $id);
        $agdx->remove($id);
        Log::record($course_id, MODULE_ID_AGENDA, LOG_DELETE, array('id' => $id,
            'date' => $row->start,
            'duration' => $row->duration,
            'title' => $row->title,
            'content' => $txt_content));
        $tool_content .= "<p class='success'>$langDeleteOK</p><br>";
        unset($addEvent);
    }

    $tool_content .= "<div id='operations_container'><ul id='opslist'>";
    if ((!isset($addEvent) && @$addEvent != 1) || isset($_POST['submit'])) {
        $tool_content .= "<li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;addEvent=1'>" . $langAddEvent . "</a></li>";
    }
    $sens = " ASC";
    $result = Database::get()->queryArray("SELECT id FROM agenda WHERE course_id = ?d", $course_id);
    if (count($result) > 1) {
        if (isset($_GET["sens"]) && $_GET["sens"] == "d") {
            $tool_content .= "<li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;sens=' >$langOldToNew</a></li>";
            $sens = " DESC ";
        } else {
            $tool_content .= "<li><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;sens=d' >$langOldToNew</a></li>";
        }
    }
    $tool_content .= "</ul></div>";
    if (isset($id) && $id) {
        $myrow = Database::get()->querySingle("SELECT id, title, content, start, duration FROM agenda WHERE course_id = ?d AND id = ?d", $course_id, $id);
        if ($myrow) {
            $id = $myrow->id;
            $event_title = $myrow->title;
            $content = $myrow->content;
            $dayAncient = $myrow->start;
            $lastingAncient = $myrow->duration;
        } else {
            $id = '';
        }
    } else {
        $id = '';
    }

    if (isset($_GET['addEvent']) or isset($_GET['edit'])) {
        $nameTools = $langAddEvent;
        $navigation[] = array("url" => $_SERVER['SCRIPT_NAME'] . "?course=$course_code", "name" => $langAgenda);
        $tool_content .= "<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code' onsubmit='return checkrequired(this, \"event_title\");'>
            <input type='hidden' name='id' value='$id'>
            <fieldset>
            <legend>$langOptions</legend>
            <table class='tbl' width='100%'>";
        $day = date("d");
        @$tool_content .= "
            <tr>
              <th>$langTitle:</th>
              <td><input type='text' size='70' name='event_title' value = '" . q($event_title) . "'></td>
            </tr>
            <tr>
              <th>$langDate:</th>
              <td>
               <input type='text' name='date' value='" . datetime_remove_seconds($myrow->start) . "'>
              </td>
            </tr>
            <tr>
              <th>$langDuration <small> $langInHour</small>:</th>
              <td><input type='text' name='duration' value='" . $myrow->duration . "' size='2' maxlength='3'></td>
            </tr>";
        if(!isset($_GET['edit'])){
            $tool_content .= "
            <tr><th>$langRepeat:</th>
                <td> $langEvery: "
                    . "<select name='frequencynumber'>"
                    . "<option value=\"0\">$langSelectFromMenu</option>";
            for($i = 1;$i<10;$i++)
            {
                $tool_content .= "<option value=\"$i\">$i</option>";
            }
            $tool_content .= "</select>"
                    . "<select name='frequencyperiod'> "
                    . "<option>$langSelectFromMenu...</option>"
                    . "<option value=\"D\">$langDays</option>"
                    . "<option value=\"W\">$langWeeks</option>"
                    . "<option value=\"M\">$langMonthsAbstract</option>"
                    . "</select>"
                    . " $langUntil: <input type='text' name='enddate' value=''></td>
            </tr>";
        }
        
        if (!isset($content)) {
            $content = '';
        }
        $tool_content .= "
            <tr>
              <th>$langDetail:</th>
              <td>" . rich_text_editor('content', 4, 20, $content) . "</td>
            </tr>
            <tr>
              <th>&nbsp;</th>
              <td class='right'><input type='submit' name='submit' value='$langAddModify'></td>
            </tr>
            </table>
            </fieldset>
            </form>
            <br>";
    }
}

/* ---------------------------------------------
 *  End  of  prof only
 * ------------------------------------------- */
if (!isset($sens))
    $sens = " ASC";

if ($is_editor) {
    $result = Database::get()->queryArray("SELECT id, title, content, start, duration, visible FROM agenda WHERE course_id = ?d
		ORDER BY start " . $sens, $course_id);
} else {
    $result = Database::get()->queryArray("SELECT id, title, content, start, duration, visible FROM agenda WHERE course_id = ?d
		AND visible = 1 ORDER BY start " . $sens, $course_id);
}

if (count($result) > 0) {
    $numLine = 0;
    $barMonth = '';
    $nowBarShowed = false;
    $tool_content .= "<table width='100%' class='tbl_alt'>
                    <tr><th class='left'>$langEvents</th>";
    if ($is_editor) {
        $tool_content .= "<th width='50'><b>$langActions</b></th>";
    } else {
        $tool_content .= "<th width='50'>&nbsp;</th>";
    }
    $tool_content .= "</tr>";

    foreach ($result as $myrow) {
        $content = standard_text_escape($myrow->content);
        $d = strtotime($myrow->start);
        if (!$nowBarShowed) {
            // Following order
            if ((($d > time()) and ( $sens == " ASC")) or ( ($d < time()) and ( $sens == " DESC "))) {
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
            if ($is_editor) {
                $tool_content .= "<td colspan='2' class='monthLabel'>";
            } else {
                $tool_content .= "<td colspan='2' class='monthLabel'>";
            }
            $tool_content .= "<div align='center'>" . $langCalendar . "&nbsp;<b>" . ucfirst(claro_format_locale_date("%B %Y", $d)) . "</b></div></td>";
            $tool_content .= "</tr>";
        }
        if ($numLine % 2 == 0) {
            $classvis = "class='even'";
        } else {
            $classvis = "class='odd'";
        }
        if ($is_editor) {
            if ($myrow->visible == 0) {
                $classvis = 'class="invisible"';
            }
        }
        $tool_content .= "<tr $classvis>";
        if ($is_editor) {
            $tool_content .= "<td valign='top'>";
        } else {
            $tool_content .= "<td valign='top' colspan='2'>";
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
        $tool_content .= "<br><b><div class='event'>";
        if ($myrow->title == '') {
            $tool_content .= $langAgendaNoTitle;
        } else {
            $tool_content .= q($myrow->title);
        }
        $tool_content .= "</b> $msg $content</div></td>";

        if ($is_editor) {
            $tool_content .= "<td class='right' width='70'>                        
                        " . icon('fa-edit', $langModify, "?course=$course_code&amp;id=$myrow->id&amp;edit=true") . "&nbsp;
                        " . icon('fa-times', $langDelete, "?course=$course_code&amp;id=$myrow->id&amp;delete=yes", "onClick=\"return confirmation('$langConfirmDelete');\"") . "&nbsp;";
            if ($myrow->visible == 1) {
                $tool_content .= icon('fa-eye', $langVisible, "?course=$course_code&amp;id=$myrow->id&amp;mkInvisibl=true");
            } else {
                $tool_content .= icon('fa-eye-slash', $langVisible, "?course=$course_code&amp;id=$myrow->id&amp;mkVisibl=true");
            }
            $tool_content .= "</td>";
        }
        $tool_content .= "</tr>";
        $numLine++;
    }
    $tool_content .= "</table>";
} else {
    $tool_content .= "<p class='alert1'>$langNoEvents</p>";
}
add_units_navigation(TRUE);

draw($tool_content, 2, null, $head_content);
