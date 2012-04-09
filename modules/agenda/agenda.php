<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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
$action->record(MODULE_ID_AGENDA);

$dateNow = date("j-n-Y / H:i",time());
$datetoday = date("Y-n-j H:i",time());

$nameTools = $langAgenda;

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
                db_query("UPDATE agenda SET visibility = 'i' WHERE course_id = $cours_id AND id = $id");
	} elseif (isset($_GET['mkVisibl']) and ($_GET['mkVisibl'] == true)) {
                db_query("UPDATE agenda SET visibility = 'v' WHERE course_id = $cours_id AND id = $id");
	}
	if (isset($_POST['submit'])) {
                register_posted_variables(array('date' => true, 'title' => true, 'content' => true, 'lasting' => true));
                $title = autoquote($title);                
                $content = autoquote(purify($content));                
                $lasting = autoquote($lasting);        
                $datetime = explode(' ', $date);
                $date = autoquote($datetime[0]);      
                $hour = autoquote($datetime[1]);        
		if (isset($_POST['id']) and !empty($_POST['id'])) {
			$id = intval($_POST['id']);
                        db_query("UPDATE agenda
                                         SET title = $title,
                                             content = $content,
                                             day = $date,
                                             hour = $hour,
                                             lasting = $lasting
                                         WHERE course_id = $cours_id AND id = $id");
		} else {
			db_query("INSERT INTO agenda
                                         SET course_id = $cours_id,
                                             title = $title,
                                             content = $content,
                                             day = $date,
                                             hour = $hour,
                                             lasting = $lasting");
                        $id = mysql_insert_id();
		}
                unset($id);
		unset($content);
		unset($title);
		$tool_content .= "<p class='success'>$langStoredOK</p><br />";
		unset($addEvent);
	}
	elseif (isset($_GET['delete']) && $_GET['delete'] == 'yes') {
                db_query("DELETE FROM agenda WHERE course_id = $cours_id AND id = $id");
		$tool_content .= "<p class='success'>$langDeleteOK</p><br />";
		unset($addEvent);
	}

        // Make top tool links
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

    $tool_content .= "<div id='operations_container'><ul id='opslist'>";
    if ((!isset($addEvent) && @$addEvent != 1) || isset($_POST['submit'])) {
        $tool_content .= "<li><a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;addEvent=1'>".$langAddEvent."</a></li>";
    }
    $sens =" ASC";
    $result = db_query("SELECT id FROM agenda WHERE course_id = $cours_id");
    if (mysql_num_rows($result) > 1) {
        if (isset($_GET["sens"]) && $_GET["sens"]=="d") {
            $tool_content .= "<li><a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;sens=' >$langOldToNew</a></li>";
            $sens=" DESC ";
        } else {
            $tool_content .= "<li><a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;sens=d' >$langOldToNew</a></li>";
        }
    }
    $tool_content .= "</ul></div>";

	if (isset($id) && $id) {
		$sql = "SELECT id, title, content, day, hour, lasting FROM agenda WHERE course_id = $cours_id AND id = $id";
		$result= db_query($sql);
		$myrow = mysql_fetch_array($result);
		$id = $myrow['id'];
		$title = $myrow['title'];
		$content = $myrow['content'];
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
		$id = "";
	}

	if (isset($_GET['addEvent']) or isset($_GET['edit'])) {
		$nameTools = $langAddEvent;
		$navigation[] = array ("url" => $_SERVER['PHP_SELF']."?course=$code_cours", "name" => $langAgenda);
		$tool_content .= "
		<form method='post' action='$_SERVER[PHP_SELF]?course=$code_cours' onsubmit='return checkrequired(this, \"title\");'>
		<input type='hidden' name='id' value='$id' />
                <fieldset>
                  <legend>$langOptions</legend>
		  <table class='tbl' width='100%'>";
		$day = date("d");		
		if (isset($title)) {
			$title_value = ' value="' . q($title) . '"';
		} else {
			$title_value = '';
                }
		$tool_content .= "
                  <tr>
                    <th>$langTitle:</th>
                    <td><input type='text' size='70' name='title'$title_value /></td>
                  </tr>
		  <tr>  
                    <th>$langDate:</th>
                    <td> ".$start_cal."</td>
                  </tr>                  
                  <tr>
                    <th>$langDuration <small> $langInHour</small>:</td>
                    <td><input type='text' name='lasting' value='".@$myrow['lasting']."' size='2' maxlength='2' /></td>
                  </tr>";
    		if (!isset($content)) {
			$content = "";
		}
		$tool_content .= "
                  <tr>
                    <th>$langDetail:</th>
                    <td>". rich_text_editor('content', 4, 20, $content) ."</td>
                  </tr>
		  <tr>
                    <th>&nbsp;</th>
                    <td class='right'><input type='submit' name='submit' value='$langAddModify' /></td>
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
	$result = db_query("SELECT id, title, content, day, hour, lasting, visibility FROM agenda WHERE course_id = $cours_id 
		ORDER BY day ".$sens.", hour ".$sens);
} else {
	$result = db_query("SELECT id, title, content, day, hour, lasting, visibility FROM agenda WHERE course_id = $cours_id 
		AND visibility = 'v' ORDER BY day ".$sens.", hour ".$sens);
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
		$content = standard_text_escape($myrow['content']);
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
			$tool_content .= "<tr $classvis><td valign='top'>";
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
                if ($myrow['title'] == '') {
                        $tool_content .= $langAgendaNoTitle;
                } else {
                        $tool_content .= q($myrow['title']);
                }
		$tool_content .= "</b> $msg $content</div></td>";

                //agenda event functions
                //added icons next to each function
                //(evelthon, 12/05/2006)
		if ($is_editor) {
                        $tool_content .=  "
                        <td class='right' width='70'>
                        <a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;id=".$myrow['id']."&amp;edit=true'>
                        <img src='$themeimg/edit.png' border='0' title='".$langModify."'></a>&nbsp;
                        <a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;id=".$myrow[0]."&amp;delete=yes' onClick='return confirmation();'>
                        <img src='$themeimg/delete.png' border='0' title='".$langDelete."'></a>&nbsp;";
                        if ($myrow["visibility"] == 'v') {
                                $tool_content .= "
                                <a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;id=".$myrow[0]."&amp;mkInvisibl=true'>
                                <img src='$themeimg/visible.png' border='0' title='".$langVisible."'></a>";
                        } else {
                                $tool_content .= "
                                <a href='$_SERVER[PHP_SELF]?course=$code_cours&amp;id=".$myrow[0]."&amp;mkVisibl=true'>
                                <img src='$themeimg/invisible.png' border='0' title='".$langVisible."'></a>";
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
