<?
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

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
// support for math symbols
include '../../include/phpmathpublisher/mathpublisher.php';

$action = new action();
$action->record('MODULE_ID_AGENDA');

$dateNow = date("j-n-Y / H:i",time());
$datetoday = date("Y-n-j",time());

$nameTools = $langAgenda;
$tool_content = $head_content = "";

mysql_select_db($dbname);
if ((isset($addEvent) && $addEvent == 1) || ((isset($id) && $id)) && $is_adminOfCourse) {

if ($language == 'greek') {
	$lang_editor='el';
	$lang_jscalendar = 'el';
} else {
	$lang_editor='en';
	$lang_jscalendar = $lang_editor;
}

	//--end if add event

	//--if add event
	$head_content = <<<hContent
<script type="text/javascript">
        _editor_url  = "$urlAppend/include/xinha/";
        _editor_lang = "$lang_editor";
</script>
<script type="text/javascript" src="$urlAppend/include/xinha/XinhaCore.js"></script>
<script type="text/javascript" src="$urlAppend/include/xinha/my_config.js"></script>

hContent;

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
	    	 'align' => 'Tl',
                 'ifFormat' => '%Y-%m-%d'),
	array('style' => 'width: 8em; color: #727266; background-color: #fbfbfb; border: 1px solid #C0C0C0; text-align: center',
                 'name' => 'date',
                 'value' => $datetoday));
}

if ($is_adminOfCourse) {
	// change visibility
	if (isset($mkInvisibl) or isset($mkVisibl)) { 
		if (@$mkInvisibl == true) {
			$sql = "UPDATE agenda SET visibility = 'i'
                		WHERE id='".mysql_real_escape_string($id)."'";
			$p_sql= "DELETE FROM agenda WHERE lesson_code = '$currentCourseID' 
				AND lesson_event_id ='".mysql_real_escape_string($id)."'";
		} elseif (@$mkVisibl == true) {
			$sql = "UPDATE agenda SET visibility = 'v' WHERE id='".mysql_real_escape_string($id)."'";
			$p_sql = "SELECT id, titre, contenu, DAY, HOUR, lasting
				FROM agenda WHERE id='".mysql_real_escape_string($id)."'";
			$perso_result = db_query($p_sql, $currentCourseID);
			$perso_query_result = mysql_fetch_row($perso_result);
			$perso_matrix['titre'] = $perso_query_result[1];
			$perso_matrix['contenu'] = $perso_query_result[2];
			$perso_matrix['date_selection'] = $perso_query_result[3];
			$perso_matrix['hour'] = $perso_query_result[4];
			$perso_matrix['lasting'] = $perso_query_result[5];
			// Add all data to the main table.
			$p_sql = "INSERT INTO agenda (lesson_event_id, titre, contenu, day, hour, lasting, lesson_code)
				VALUES('".$perso_query_result[0]."','".$perso_query_result[1]."',
				'".$perso_query_result[2]."','".$perso_query_result[3]."',
				'".$perso_query_result[4]."','".$perso_query_result[5]."',
				'".$currentCourseID."')";
		}
		db_query($sql);
		db_query($p_sql, $mysqlMainDb);
	}
	if (isset($_POST['submit'])) {
		$date_selection = $date;
		$hour = $fhour.":".$fminute;

		if(isset($id) && $id) {
			$sql = "UPDATE agenda
                	SET titre='".mysql_real_escape_string(trim($titre))."',
                	contenu='".mysql_real_escape_string(trim($contenu))."',
                	day='".mysql_real_escape_string($date_selection)."',
                	hour='".mysql_real_escape_string($hour)."',
                	lasting='".mysql_real_escape_string($lasting)."'
                	WHERE id='".mysql_real_escape_string($id)."'";

			##[BEGIN personalisation modification]############
			$perso_sql = "UPDATE $mysqlMainDb.agenda
                  	SET titre='".mysql_real_escape_string(trim($titre))."',
                        contenu='".mysql_real_escape_string(trim($contenu))."',
                        day = '".mysql_real_escape_string($date_selection)."',
                        hour= '".mysql_real_escape_string($hour)."',
                        lasting='".mysql_real_escape_string($lasting)."'
                	WHERE lesson_code= '$currentCourseID'
               		AND lesson_event_id='".mysql_real_escape_string($id)."' ";
			##[END personalisation modification]############

			unset($id);
			unset($contenu);
			unset($titre);
		} else {
			$sql = "INSERT INTO agenda (id, titre, contenu, day, hour, lasting)
        		VALUES (NULL,'".mysql_real_escape_string(trim($titre))."',
				'".mysql_real_escape_string(trim($contenu))."', 
				'".mysql_real_escape_string($date_selection)."',
				'".mysql_real_escape_string($hour)."',
				'".mysql_real_escape_string($lasting)."')";
			unset($id);
			unset($contenu);
			unset($titre);
		}
		$result = db_query($sql, $currentCourseID);
		
			##[BEGIN personalisation modification]############
			if (substr_count($sql,"INSERT") == 1) {
				$perso_sql = "SELECT id, titre, contenu, DAY, HOUR , lasting
					FROM agenda ORDER BY id DESC LIMIT 1 ";
				$perso_result = db_query($perso_sql, $currentCourseID);
				$perso_query_result = mysql_fetch_row($perso_result);
				$perso_matrix['titre'] = $perso_query_result[1];
				$perso_matrix['contenu'] = $perso_query_result[2];
				$perso_matrix['date_selection'] = $perso_query_result[3];
				$perso_matrix['hour'] = $perso_query_result[4];
				$perso_matrix['lasting'] = $perso_query_result[5];
	
				// Add all data to the main table.
				$perso_sql = "INSERT INTO $mysqlMainDb.agenda
				(lesson_event_id, titre, contenu, day, hour, lasting, lesson_code)
				VALUES('".$perso_query_result[0]."','".$perso_query_result[1]."',
				'".$perso_query_result[2]."','".$perso_query_result[3]."',
				'".$perso_query_result[4]."','".$perso_query_result[5]."',
				'".$currentCourseID."')";
			}
	
			db_query($perso_sql, $mysqlMainDb);
	
			unset($perso_matrix);
			unset($perso_sql_delete);
			unset($id);
			##[END personalisation modification]############
		$tool_content .=  "<p class=\"success_small\">$langStoredOK</p><br />";
		unset($addEvent);
	}
	elseif (isset($delete) && $delete) {
		$sql = "DELETE FROM agenda WHERE id=$id";
		$result = db_query($sql,$currentCourseID);

		##[BEGIN personalisation modification]############
		$perso_sql= "DELETE FROM $mysqlMainDb.agenda
                      WHERE lesson_code= '$currentCourseID'
                      AND lesson_event_id='$id' ";

		db_query($perso_sql, $mysqlMainDb);
		##[END personalisation modification]############

		$tool_content .= "<p class=\"success_small\">$langDeleteOK</p><br />";
		unset($addEvent);
	}
//Make top tool links
if ($is_adminOfCourse) {
	$head_content .= '
<script>
function confirmation (name)
{
    if (confirm("'.$langSureToDel.' "+ name + " ?"))
        {return true;}
    else
        {return false;}
}
</script>
';

	$tool_content .= "\n  <div id=\"operations_container\">\n    <ul id=\"opslist\">";
	if ((!isset($addEvent) && @$addEvent != 1) || isset($_POST['submit'])) {
		$tool_content .= "\n<li><a href=\"".$_SERVER['PHP_SELF']."?addEvent=1\">".$langAddEvent."</a></li>";
	}

	$sens =" ASC";
	$result = db_query("SELECT id FROM agenda", $currentCourseID);
	if (mysql_num_rows($result) > 1) {
		if (isset($_GET["sens"]) && $_GET["sens"]=="d") {
			$tool_content .=  "\n<li><a href=\"".$_SERVER['PHP_SELF']."?sens=\" >$langOldToNew</a></li>";
			$sens=" DESC ";
		} else {
			$tool_content .=  "\n<li><a href=\"".$_SERVER['PHP_SELF']."?sens=d\" >$langOldToNew</a></li>";
		}
	}
	$tool_content .= "\n    </ul>\n  </div>\n";
}
	if (isset($id) && $id) {
		$sql = "SELECT id, titre, contenu, day, hour, lasting FROM agenda WHERE id=$id";
		$result= db_query($sql, $currentCourseID);
		$myrow = mysql_fetch_array($result);
		$id = $myrow["id"];
		$titre = $myrow["titre"];
		$contenu= $myrow["contenu"];
		$hourAncient=$myrow["hour"];
		$dayAncient=$myrow["day"];
		$lastingAncient=$myrow["lasting"];

		$start_cal = $jscalendar->make_input_field(
          array('showOthers' => true,
                'align' => 'Tl',
                 'ifFormat' => '%Y-%m-%d'),
          array('style' => 'width: 8em; color: #727266; background-color: #fbfbfb; border: 1px solid #C0C0C0; text-align: center',
                 'name' => 'date',
                 'value' => $dayAncient));
	}

	if (!isset($id)) {
		$id="";
	}

	if ((isset($addEvent) && ($addEvent == 1)) || (isset($edit) && $edit == true)) {
		$nameTools = $langAddEvent;
		$navigation[] = array ("url"=>"$_SERVER[PHP_SELF]", "name"=> $langAgenda);
		$tool_content .= <<<tContentForm
<form method="post" action="".$_SERVER[PHP_SELF]." onsubmit="return checkrequired(this, 'titre');">
    <input type="hidden" name="id" value="$id">
    <table width=\"99%\" class=\"FormData\">
    <tbody>
    <tr>
      <th class=\"left\" width=\"150\">&nbsp;</th>
      <td><b>$langAddEvent</b><td>
    </tr>
tContentForm;
		$day	= date("d");
		$hours	= date("H");
		$minutes= date("i");

		if (isset($hourAncient) && $hourAncient) {
			$hourAncient = split(":", $hourAncient);
			$hours=$hourAncient[0];
			$minutes=$hourAncient[1];
		}
		$tool_content .= "<tr><th class=\"left\">$langTitle:</th>
			<td><input type=\"text\" size=\"85\" name=\"titre\" value=\"".@$titre."\"  class='FormData_InputText'></td>
			</tr>
			<tr>
			<th class=\"left\" rowspan=\"2\">$l_options:</th>
			<td>$langDate: ".$start_cal."</td>
			</tr>
			<tr>
		<td>$langHour: <select name='fhour' class='auth_input'>
				<option value='$hours'>$hours</option>
				<option value='--'>--</option>";
			for ($h=0; $h<=24; $h++)
				$tool_content .= "\n<option value='$h'>$h</option>";
			$tool_content .= "</select>&nbsp;&nbsp;&nbsp;&nbsp;";
			$tool_content .= "$langMinute: <select name=\"fminute\" class='auth_input'>
			<option value=\"$minutes\">[$minutes]</option>
			<option value=\"--\">--</option>";
			for ($m=0; $m<=55; $m=$m+5)
				$tool_content .=  "<option value='$m'>$m</option>";

			$tool_content .= "</select>&nbsp;&nbsp;&nbsp;&nbsp;$langLasting $langInHour:
			<input class='FormData_InputText' type=\"text\" name=\"lasting\" value=\"".@$myrow['lasting']."\" size=\"2\" maxlength=\"2\"></td>
    			</tr>";
    		if (!isset($contenu)) {
			$contenu = "";
		}
		$tool_content .= "<tr><th class=\"left\">$langDetail:</th>
			<td><textarea id='xinha' name='contenu' value='$contenu'>".$contenu."</textarea></td>
			</tr>
			<tr><th class=\"left\">&nbsp;</th>
			<td><input type=\"submit\" name=\"submit\" value=\"$langAddModify\"></td>
			</tr></tbody></table>
			</form><br />";
	}
}

/*---------------------------------------------
*  End  of  prof only
*-------------------------------------------*/
if (!isset($sens)) $sens =" ASC";

if ($is_adminOfCourse) { 
	$result = db_query("SELECT id, titre, contenu, day, hour, lasting, visibility FROM agenda ORDER BY day ".$sens.", hour ".$sens,$currentCourseID);
} else {
	$result = db_query("SELECT id, titre, contenu, day, hour, lasting, visibility FROM agenda WHERE visibility = 'v' 
		ORDER BY day ".$sens.", hour ".$sens,$currentCourseID);
}

if (mysql_num_rows($result) > 0) {
	$tool_content .=  "\n    <table width=\"99%\" align=\"left\" class=\"FormData\">";
	$tool_content .=  "\n    <tbody>";
	$tool_content .=  "\n    <tr>";
	$tool_content .=  "\n      <th style=\"border: 1px solid #edecdf\"><div align=\"left\"><b>$langEvents</b></div></th>";
	if ($is_adminOfCourse) {
		$tool_content .=  "\n<th width=\"60\" class='right' style=\"border: 1px solid #edecdf\"><b>$langActions</b></th>";
	}
	$tool_content .= "\n    </tr>\n    </tbody>";
	$tool_content .= "\n    </table>\n\n";
	$numLine=0;
	$barreMois = "";
	$nowBarShowed = FALSE;
	$tool_content .= "\n    <table width=\"99%\" align=\"left\" class=\"Agenda\">";
	$tool_content .= "\n    <tbody>";
	while ($myrow = mysql_fetch_array($result)) {
		$contenu = $myrow["contenu"];
		$contenu = nl2br($contenu);
		$contenu = make_clickable($contenu);
		// display math symbols (if there are)
	    	$contenu = mathfilter($contenu, 12, "../../courses/mathimg/");
		if (!$nowBarShowed) {
			// Following order
			if (((strtotime($myrow["day"]." ".$myrow["hour"]) > time()) && ($sens==" ASC")) ||
				((strtotime($myrow["day"]." ".$myrow["hour"]) < time()) && ($sens==" DESC "))) {
				if ($barreMois!=date("m",time())) {
					$barreMois=date("m",time());
					$tool_content .= "\n    <tr class=\"odd\">";
					// current month
					$tool_content .= "\n      <td colspan=\"2\" class=\"monthLabel\">".$langCalendar."&nbsp;<b>".ucfirst(claro_format_locale_date("%B %Y",time()))."</b></td>";
					$tool_content .= "\n    </tr>";
				}
				$nowBarShowed = TRUE;
				$tool_content .=  "\n    <tr>";
				$tool_content .=  "\n      <td colspan=2 class=\"today\"><b>$langDateNow : $dateNow</b></td>";
				$tool_content .=  "\n    </tr>";
			}
		}
		if ($barreMois!=date("m",strtotime($myrow["day"]))) {
			$barreMois=date("m",strtotime($myrow["day"]));
            		// month LABEL
			$tool_content .= "\n    <tr class=\"odd\">";
			$tool_content .=  "\n      <td colspan=\"2\" class=\"monthLabel\">
			<div align=\"center\">".$langCalendar."&nbsp;<b>".ucfirst(claro_format_locale_date("%B %Y",strtotime($myrow["day"])))."</b></div></td>";
			$tool_content .= "\n    </tr>";
		}

		if ($is_adminOfCourse) {
			if ($myrow["visibility"] == 'i') {
				$classvis = 'class = invisible_agenda';
			} else {
				$classvis = '';
			}
			$tool_content .= "\n<tr $classvis><td valign=\"top\">";
		} else {
			$tool_content .= "\n<tr><td valign=\"top\" colspan=\"2\">";
		}
		$tool_content .= "<span class=\"day\">".ucfirst(claro_format_locale_date($dateFormatLong,strtotime($myrow["day"])))."</span> ($langHour: ".ucfirst(date("H:i",strtotime($myrow["hour"]))).")";
	$message = "$langUnknown";
	if ($myrow["lasting"] !="") {
		if ($myrow["lasting"] == 1)
			$message = $langHour;
		else
			$message = $langHours;
	}
		$tool_content .=  "<p class=\"event\"><b>";
            if ($myrow["titre"]=="") {
                $tool_content .= "".$langAgendaNoTitle."";
            } else {
                $tool_content .= "".$myrow["titre"]."";
            }
		$tool_content .= "</b> (".$langLasting.": ".$myrow["lasting"]." ".$message.")</p>
		<p class=\"agendaBody\">$contenu</p></td>";

	//agenda event functions
	//added icons next to each function
	//(evelthon, 12/05/2006)
	if ($is_adminOfCourse) {
		$tool_content .=  "\n<td class='right' width=\"80\">
		<a href=\"$_SERVER[PHP_SELF]?id=".$myrow['id']."&edit=true\">
            	<img src=\"../../template/classic/img/edit.gif\" border=\"0\" title=\"".$langModify."\"></a>&nbsp;
        	<a href=\"$_SERVER[PHP_SELF]?id=".$myrow[0]."&delete=yes\" onClick=\"return confirmation('".addslashes($myrow["titre"])."');\">
            	<img src=\"../../template/classic/img/delete.gif\" border=\"0\" title=\"".$langDelete."\"></a>&nbsp;";
		if ($myrow["visibility"] == 'v') {
			$tool_content .= "<a href=\"$_SERVER[PHP_SELF]?id=".$myrow[0]."&mkInvisibl=true\">
        	    	<img src=\"../../template/classic/img/visible.gif\" border=\"0\" title=\"".$langVisible."\"></a>";
		} else {
 			$tool_content .= "<a href=\"$_SERVER[PHP_SELF]?id=".$myrow[0]."&mkVisibl=true\">
        	    	<img src=\"../../template/classic/img/invisible.gif\" border=\"0\" title=\"".$langVisible."\"></a>";
		}
		$tool_content .= "</td>";
	}
		$tool_content .=  "\n    </tr>";
		$numLine++;

	} 	// while
	$tool_content .= "\n    </tbody>";
	$tool_content .=  "\n    </table>\n";

} else  {
	$tool_content .= "<p class='alert1'>$langNoEvents</p>";
}
if($is_adminOfCourse && isset($head_content)) {
	draw($tool_content, 2, 'agenda', $head_content, @$body_action);
} else {
	draw($tool_content, 2, 'agenda');
}
?>

