<?php
/* ========================================================================
 * Open eClass 2.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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
 * Agenda Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This component creates the content for the agenda module
 *
 */

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Agenda';
$guest_allowed = true;

include '../../include/baseTheme.php';
include '../../include/lib/textLib.inc.php';
include '../../include/action.php';
include '../../include/jscalendar/calendar.php';

require_once '../video/video_functions.php';
load_modal_box();

$action = new action();
$action->record('MODULE_ID_AGENDA');

$dateNow = date("j-n-Y / H:i",time());
$datetoday = date("Y-n-j H:i",time());

$nameTools = $langAgenda;

mysql_select_db($dbname);

if (isset($_GET['id'])) {
	$id = intval($_GET['id']);
}

		
if ($is_editor and (isset($_GET['addEvent']) or isset($_GET['id']))) {
	$lang_jscalendar = langname_to_code($language);

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

$jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/', $lang_jscalendar, 'calendar-blue2', false);
$head_content .= $jscalendar->get_load_files_code();

$start_cal = $jscalendar->make_input_field(                         
	array('showOthers' => true,
               'showsTime' => true,
	        'align' => 'Tl',
                'ifFormat' => '%Y-%m-%d %H:%M',
                'timeFormat' => '24'),
	array('style' => 'font-weight: bold; font-size: 10px; width: 10em; color: #727266; background-color: #fbfbfb; border: 1px solid #C0C0C0; text-align: center',
                 'name' => 'date',
                 'value' => $datetoday));
}

if ($is_editor) {
	// modify visibility
	if (isset($_GET['mkInvisibl']) and $_GET['mkInvisibl'] == true) {
		db_query("UPDATE agenda SET visibility = 'i'
                                        WHERE id = $id");
                db_query("DELETE FROM `$mysqlMainDb`.agenda
                                 WHERE lesson_code = '$currentCourseID' AND
                                       lesson_event_id = $id");
	} elseif (isset($_GET['mkVisibl']) and ($_GET['mkVisibl'] == true)) {
		db_query("UPDATE agenda SET visibility = 'v' WHERE id = $id");
                db_query("INSERT INTO `$mysqlMainDb`.agenda
                                 (lesson_event_id, titre, contenu, day, hour, lasting, lesson_code)
                                 SELECT id, titre, contenu, day, hour, lasting, '$currentCourseID'
                                        FROM agenda WHERE id = $id");
	}
	if (isset($_POST['submit'])) {
                register_posted_variables(array('date' => true, 'titre' => true, 'contenu' => true, 'lasting' => true));
                $titre = autoquote($titre);
                $contenu = autoquote($contenu);
                $lasting = autoquote($lasting);        
                $datetime = explode(' ', $date);
                $date = autoquote($datetime[0]);      
                $hour = autoquote($datetime[1]);        
		if (isset($_POST['id']) and !empty($_POST['id'])) {
			$id = intval($_POST['id']);
                        db_query("UPDATE agenda
                                         SET titre = $titre,
                                             contenu = $contenu,
                                             day = $date,
                                             hour = $hour,
                                             lasting = $lasting
                                         WHERE id = $id");
			##[BEGIN personalisation modification]############
			db_query("UPDATE $mysqlMainDb.agenda
                                         SET titre = $titre,
                                             contenu = $contenu,
                                             day = $date,
                                             hour = $hour,
                                             lasting = $lasting
                                         WHERE lesson_code= '$currentCourseID' AND
                                               lesson_event_id = $id");
			##[END personalisation modification]############
		} else {
			db_query("INSERT INTO agenda
                                         SET titre = $titre,
                                             contenu = $contenu,
                                             day = $date,
                                             hour = $hour,
                                             lasting = $lasting");
                        $id = mysql_insert_id();
                        db_query("INSERT INTO `$mysqlMainDb`.agenda
                                         SET titre = $titre,
                                             contenu = $contenu,
                                             day = $date,
                                             hour = $hour,
                                             lasting = $lasting,
                                             lesson_code= '$currentCourseID',
                                             lesson_event_id = $id");
		}
                unset($id);
		unset($contenu);
		unset($titre);
		##[END personalisation modification]############
		$tool_content .= "<p class='success'>$langStoredOK</p><br />";
		unset($addEvent);
	}
	elseif (isset($_GET['delete']) && $_GET['delete'] == 'yes') {
		db_query("DELETE FROM agenda WHERE id =$id");
		##[BEGIN personalisation modification]############
		db_query("DELETE FROM $mysqlMainDb.agenda
                                 WHERE lesson_code= '$currentCourseID' AND
                                       lesson_event_id = $id");
		##[END personalisation modification]############
		$tool_content .= "<p class='success'>$langDeleteOK</p><br />";
		unset($addEvent);
	}
// Make top tool links
if ($is_editor) {
	$head_content .= '
	<script type="text/javascript">
	function confirmation ()
	{
	    if (confirm("'.$langConfirmDelete.'"))
		{return true;}
	    else
		{return false;}
	}
	</script>';
}
	if (isset($id) && $id) {
		$sql = "SELECT id, titre, contenu, day, hour, lasting FROM agenda WHERE id=$id";
		$result= db_query($sql, $currentCourseID);
		$myrow = mysql_fetch_array($result);
		$id = $myrow['id'];
		$titre = $myrow['titre'];
		$contenu = $myrow['contenu'];
		$hourAncient = $myrow['hour'];
		$dayAncient = $myrow['day']. ' '.$hourAncient;
		$lastingAncient = $myrow['lasting'];
		$start_cal = $jscalendar->make_input_field(
                        array('showsTime' => true,                           
                               'showOthers' => true,
                               'align' => 'Tl',
                               'ifFormat' => '%Y-%m-%d %H:%M',
                                'timeFormat' => '24'),
                        array('style' => 'font-weight: bold; font-size: 10px; width: 10em; color: #727266; background-color: #fbfbfb; border: 1px solid #C0C0C0; text-align: center',
                               'name' => 'date',
                               'value' => $dayAncient));
	} else {
		$tool_content .= "\n  <div id='operations_container'>\n    <ul id='opslist'>";
		if ((!isset($addEvent) && @$addEvent != 1) || isset($_POST['submit'])) {
			$tool_content .= "\n<li><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;addEvent=1'>".$langAddEvent."</a></li>";
		}
		$sens =" ASC";
		$result = db_query("SELECT id FROM agenda", $currentCourseID);
		if (mysql_num_rows($result) > 1) {
			if (isset($_GET["sens"]) && $_GET["sens"]=="d") {
				$tool_content .= "<li><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;sens=' >$langOldToNew</a></li>";
				$sens=" DESC ";
			} else {
				$tool_content .= "<li><a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;sens=d' >$langOldToNew</a></li>";
			}
		}
		$tool_content .= "</ul></div>\n";
        }
	if (!isset($id)) {
		$id = "";
	}

	if (isset($_GET['addEvent']) or isset($_GET['edit'])) {
		$nameTools = $langAddEvent;
		$navigation[] = array ("url" => $_SERVER['SCRIPT_NAME']."?course=$code_cours", "name" => $langAgenda);
		$tool_content .= "
		<form method='post' action='$_SERVER[SCRIPT_NAME]?course=$code_cours' onsubmit='return checkrequired(this, \"titre\");'>
		<input type='hidden' name='id' value='$id' />
                <fieldset>
                  <legend>$langOptions</legend>
		  <table class='tbl' width='100%'>";
		$day = date("d");		
		if (isset($titre)) {
			$titre_value = ' value="' . q($titre) . '"';
		} else {
			$titre_value = '';
                }
		$tool_content .= "
                  <tr>
                    <th>$langTitle:</th>
                    <td><input type='text' size='70' name='titre'$titre_value /></td>
                  </tr>
		  <tr>  
                    <th>$langDate:</th>
                    <td> ".$start_cal."</td>
                  </tr>                  
                  <tr>
                    <th>$langDuration <small> $langInHour</small>:</td>
                    <td><input type='text' name='lasting' value='".@$myrow['lasting']."' size='2' maxlength='2' /></td>
                  </tr>";
    		if (!isset($contenu)) {
			$contenu = '';
		}
		$tool_content .= "
                  <tr>
                    <th>$langDetail:</th>
                    <td>". rich_text_editor('contenu', 4, 20, $contenu) ."</td>
                  </tr>
		  <tr>
                    <th>&nbsp;</th>
                    <td class='right'><input type='submit' name='submit' value='".q($langAddModify)."' /></td>
                  </tr>
		  </table>
                </fieldset>
		</form>
                <br />";
	}
}

/*---------------------------------------------
*  End  of  prof only
*-------------------------------------------*/
if (!isset($sens)) $sens =" ASC";

if ($is_editor) { 
	$result = db_query("SELECT id, titre, contenu, day, hour, lasting, visibility FROM agenda ORDER BY day ".$sens.", hour ".$sens,$currentCourseID);
} else {
	$result = db_query("SELECT id, titre, contenu, day, hour, lasting, visibility FROM agenda WHERE visibility = 'v' 
		ORDER BY day ".$sens.", hour ".$sens,$currentCourseID);
}

if (mysql_num_rows($result) > 0) {
	$numLine = 0;
	$barMonth = "";
	$nowBarShowed = FALSE;
        $tool_content .= "
        <table width='100%' class='tbl_alt'>
        <tr>
          <th class='left'>$langEvents</th>";
        if ($is_editor) {
              $tool_content .= "<th width='50'><b>$langActions</b></th>";
        } else {
              $tool_content .= "<th width='50'>&nbsp;</th>";
        }
        $tool_content .= "</tr>";

	while ($myrow = mysql_fetch_array($result)) {
		$contenu = standard_text_escape($myrow['contenu']);
		if (!$nowBarShowed) {
			// Following order
			if (((strtotime($myrow["day"]." ".$myrow["hour"]) > time()) && ($sens==" ASC")) ||
				((strtotime($myrow["day"]." ".$myrow["hour"]) < time()) && ($sens==" DESC "))) {
				if ($barMonth != date("m",time())) {
					$barMonth = date("m",time());
					$tool_content .= "\n<tr>";
					// current month
					$tool_content .= "\n<td colspan='2' class='monthLabel'>".$langCalendar."&nbsp;<b>".ucfirst(claro_format_locale_date("%B %Y",time()))."</b></td>";
					$tool_content .= "\n</tr>";
				}
				$nowBarShowed = TRUE;
				$tool_content .= "\n<tr>";
				$tool_content .= "\n<td colspan='2' class='today'>$langDateNow $dateNow</td>";
				$tool_content .= "\n</tr>";
			}
		}
		if ($barMonth != date("m",strtotime($myrow["day"]))) {
			$barMonth = date("m",strtotime($myrow["day"]));
            		// month LABEL
			$tool_content .= "\n<tr>";
			if ($is_editor) {
				$tool_content .= "\n<td colspan='2' class='monthLabel'>";
			} else {
				$tool_content .= "\n<td colspan='2' class='monthLabel'>";
			}
			$tool_content .= "<div align='center'>".$langCalendar."&nbsp;<b>".ucfirst(claro_format_locale_date("%B %Y",strtotime($myrow["day"])))."</b></div></td>";
			$tool_content .= "\n</tr>";
		}
                $classvis = 'class="visible"';
		if ($is_editor) {
			if ($myrow["visibility"] == 'i') {
				$classvis = 'class="invisible"';
			} else {
                             if ($numLine%2 == 0) {
                               $classvis = 'class="even"';
                             } else {
                               $classvis = 'class="odd"';
                             }
			}
			$tool_content .= "\n        <tr $classvis>\n          <td valign='top'>";
                } else {
                        if ($numLine%2 == 0) {
                                $tool_content .= "<tr class='even'>";
                        } else {
                                $tool_content .= "<tr class='odd'>";
                        }
                        $tool_content .= "<td valign='top' colspan='2'>";
                }

                $tool_content .= "\n<span class='day'>".
                        ucfirst(claro_format_locale_date($dateFormatLong, strtotime($myrow['day']))).
                        "</span> ($langHour: ".ucfirst(date('H:i', strtotime($myrow['hour']))).")";	
                if ($myrow['lasting'] != '') {
                        if ($myrow['lasting'] == 1) {
                                $message = $langHour;
                        } else {
                                $message = $langHours;
                        }
                        $msg = "($langDuration: ".q($myrow['lasting'])." $message)";
                } else {
                        $msg = '';
                }
		$tool_content .=  "\n<br /><br /><div class='event'><b>";
                if ($myrow['titre'] == '') {
                        $tool_content .= $langAgendaNoTitle;
                } else {
                        $tool_content .= q($myrow['titre']);
                }
		$tool_content .= "</b> $msg $contenu</div></td>";

                //agenda event functions
                //added icons next to each function
                //(evelthon, 12/05/2006)
		if ($is_editor) {
                        $tool_content .=  "
                        <td class='right' width='70'>
                        <a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;id=".$myrow['id']."&amp;edit=true'>
                        <img src='$themeimg/edit.png' title='".q($langModify)."'></a>&nbsp;
                        <a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;id=".$myrow[0]."&amp;delete=yes' onClick='return confirmation();'>
                        <img src='$themeimg/delete.png' title='".q($langDelete)."'></a>&nbsp;";
                        if ($myrow["visibility"] == 'v') {
                                $tool_content .= "
                                <a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;id=".$myrow[0]."&amp;mkInvisibl=true'>
                                <img src='$themeimg/visible.png' title='".q($langVisible)."'></a>";
                        } else {
                                $tool_content .= "
                                <a href='$_SERVER[SCRIPT_NAME]?course=$code_cours&amp;id=".$myrow[0]."&amp;mkVisibl=true'>
                                <img src='$themeimg/invisible.png' title='".q($langVisible)."'></a>";
                        }
                        $tool_content .= "</td>";
		}
		$tool_content .= "</tr>";
		$numLine++;
	} 	// while
	$tool_content .= "</table>";
} else {
	$tool_content .= "<p class='alert1'>$langNoEvents</p>";
}
add_units_navigation(TRUE);

draw($tool_content, 2, null, $head_content);
