<?
/**===========================================================================
*              GUnet e-Class 2.0
*       E-learning and Course Management Program
* ===========================================================================
*	Copyright(c) 2003-2006  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/

/**
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
$action = new action();
$action->record('MODULE_ID_AGENDA');

$dateNow = date("j-n-Y / H:i",time());
$nameTools = $langAgenda;
$tool_content = $head_content = "";

mysql_select_db($dbname);
if ((@$addEvent == 1 || (isset($id) && $id)) && $is_adminOfCourse) {

if ($language == 'greek') {
		$lang_editor='gr';
		$lang_jscalendar = 'el';
}
	else {
		$lang_editor='en';
		$lang_jscalendar = $lang_editor;
}

	//--end if add event

	//--if add event
	$head_content = <<<hContent
<script type="text/javascript">
        _editor_url  = "$urlAppend/include/xinha/";
        _editor_lang = "en";
        _editor_skin = "silva";
</script>
<script type="text/javascript" src="$urlAppend/include/xinha/XinhaCore.js"></script>
<script type="text/javascript" src="$urlAppend/include/xinha/my_config.js"></script>

hContent;

$jscalendar = new DHTML_Calendar($urlServer.'include/jscalendar/', $lang_jscalendar, 'calendar-blue2', false);
$head_content .= $jscalendar->get_load_files_code();

$start_cal = $jscalendar->make_input_field(
					array('showOthers' => true,
								'align' => 'Tl',
                 'ifFormat' => '%Y-%m-%d'),
					array('style' => 'width: 15em; color: #840; background-color: #ff8; border: 1px solid #000; text-align: center',
                 'name' => 'date',
                 'value' => ' '));
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
	
	$tool_content .= "<div id=\"operations_container\">
		<ul id=\"opslist\">";
	if ((!isset($addEvent) && @$addEvent != 1) || isset($_POST['submit'])) {
		$tool_content .= "
			<li><a href=\"".$_SERVER['PHP_SELF']."?addEvent=1\">".$langAddEvent."</a></li>";
	}

	$sens =" ASC";
	$result = db_query("SELECT id FROM agenda", $currentCourseID);
	if (mysql_num_rows($result) > 1) {

		if (isset($_GET["sens"]) && $_GET["sens"]=="d") {
			$tool_content .=  "<li><a href=\"".$_SERVER['PHP_SELF']."?sens=\" >$langOldToNew</a></li>";
			$sens=" DESC ";
		} else {
			$tool_content .=  "<li><a href=\"".$_SERVER['PHP_SELF']."?sens=d\" >$langOldToNew</a></li>";
		}
	}
	$tool_content .= "</ul></div>";
}

if ($is_adminOfCourse) {
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

			$sql = "INSERT INTO agenda (id, titre,contenu, day, hour, lasting)
        VALUES (NULL, '".mysql_real_escape_string(trim($titre))."','".mysql_real_escape_string(trim($contenu))."', '".mysql_real_escape_string($date_selection)."','".mysql_real_escape_string($hour)."', '".mysql_real_escape_string($lasting)."')";
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

			$perso_matrix['titre'] = $perso_query_result[0];
			$perso_matrix['titre'] = $perso_query_result[1];
			$perso_matrix['contenu'] = $perso_query_result[2];
			$perso_matrix['date_selection'] = $perso_query_result[3];
			$perso_matrix['hour'] = $perso_query_result[4];
			$perso_matrix['lasting'] = $perso_query_result[5];

			// Add all data to the main table.
			$perso_sql = "INSERT INTO $mysqlMainDb.agenda (lesson_event_id, titre, contenu, day, hour, lasting, lesson_code)
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

		$tool_content .=  "<table width=\"99%\" align=\"center\">
					<tbody><tr><td class=\"success\">$langStoredOK</td></tr></tbody>
					</table><br/>";

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

		$tool_content .= "<table width=\"99%\"><tbody>
		<tr><td class=\"success\">$langDeleteOK</td></tr>
		</tbody></table><br/>";
		unset($addEvent);
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
          array('style' => 'width: 15em; color: #840; background-color: #ff8; border: 1px solid #000; text-align: center',
                 'name' => 'date',
                 'value' => $dayAncient));
	}

	if (!isset($id)) {
		$id="";
	}


	if (@$addEvent == 1 || (isset($id) && $id)) {
		$tool_content .= <<<tContentForm

    <p>$langAddEvent</p>
	<form method="post" action="".$_SERVER[PHP_SELF]."">
    <input type="hidden" name="id" value="$id">
    <table width="99%">
    <thead>
    <tr>
		<th>$langChooseDate</th>
        <th>$langHour</th>
        <th>$langMinute</th>
        <th>$langLasting</th>
    </tr>
    </thead>
tContentForm;

		$day	= date("d");
		$hours	= date("H");
		$minutes= date("i");

		if (isset($hourAncient) && $hourAncient) {
			$hourAncient = split(":", $hourAncient);
			$hours=$hourAncient[0];
			$minutes=$hourAncient[1];
		}
/*
		if (isset($dayAncient) && $dayAncient) {
			$dayAncient= split("-",  $dayAncient);
			$year=$dayAncient[0];
			$month=$dayAncient[1];
			$day=$dayAncient[2];
		}  */
		
		$tool_content .= "<tbody><tr><td align='center'>";
		
		// display jscalendar
		$tool_content .= " ".$start_cal."</td>";

		$tool_content .= "<td align='center'><select name='fhour'>
		    <option value='$hours'>$hours</option>
    		<option value='--'>--</option>";

    for ($h=0; $h<=24; $h++)
          $tool_content .= "<option value='$h'>$h</option>";

    $tool_content .= "</select></td>";

		$tool_content .= "<td align='center'>
    <select name=\"fminute\">
    <option value=\"$minutes\">[$minutes]</option>
    <option value=\"--\">--</option>";

		for ($m=0; $m<=55; $m=$m+5) 
					$tool_content .=  "<option value='$m'>$m</option>";

		$tool_content .= "</select></td>
    <td align='center'><input type=\"text\" name=\"lasting\" value=\"".@$myrow['lasting']."\" size=\"2\" maxlength=\"2\"></td>
    </tr></tbody>
    </table><br>";

		$tool_content .="
    <table width = \"99%\">
    <thead><tr><th>$langTitle :</th>
    <td colspan=\"5\"><input type=\"text\" size=\"60\" name=\"titre\" value=\"".@$titre."\"></td>
    </tr></thead></table><br>";

		if (!isset($contenu)) {
			$contenu="";
		}

		$tool_content .= "
    <table width = \"99%\">
    <thead><tr><th colspan=6>$langDetail</th></tr></thead>
    <tbody>
    <tr>
    <td colspan=\"6\">
		<textarea id='xinha' name='contenu' value='$contenu' rows='20' cols='78'>".$contenu."</textarea></td></tr>
    </tbody></table>
    <br>
    <input type=\"Submit\" name=\"submit\" value=\"$langAddModify\">
</form>
<br><br>";

	}
}

/*---------------------------------------------
*  End  of  prof only
*-------------------------------------------*/
if (!isset($sens)) $sens =" ASC";
$result = db_query("SELECT id, titre, contenu, day, hour, lasting FROM agenda ORDER BY day ".$sens.", hour ".$sens,$currentCourseID);
if (mysql_num_rows($result) > 0) {
	$tool_content .=  "<table width=\"99%\" align=\"center\">";
	$tool_content .=  "<thead><tr><th>$langEvents</th>";
	if ($is_adminOfCourse) {
		$tool_content .=  "<th width=60>$langActions</th>";
	}
	$tool_content .= "</tr></thead>";
	$tool_content .= "<tbody>";

	$numLine=0;
	$barreMois ="";
	$nowBarShowed = FALSE;

	while ($myrow = mysql_fetch_array($result)) {
		$contenu = $myrow["contenu"];
		$contenu = nl2br($contenu);
		$contenu = make_clickable($contenu);
		if (!$nowBarShowed) {
			// Following order
			if (((strtotime($myrow["day"]." ".$myrow["hour"]) > time()) && ($sens==" ASC")) ||
			((strtotime($myrow["day"]." ".$myrow["hour"]) < time()) && ($sens==" DESC "))) {
				if ($barreMois!=date("m",time())) {
					$barreMois=date("m",time());
					$tool_content .= "<tr>
               <td class=\"month\" colspan=\"2\" valign=\"top\">
                ".$langCalendar."&nbsp;".ucfirst(claro_format_locale_date("%B %Y",time()))."
                </td></tr>";
				}
				$nowBarShowed = TRUE;
				$tool_content .=  "<tr>
      <td colspan=2 class=\"today\"><b>$langDateNow : $dateNow</b></td></tr>";
			}
		}
		if ($barreMois!=date("m",strtotime($myrow["day"]))) {
			$barreMois=date("m",strtotime($myrow["day"]));
			$tool_content .=  "<tr><td class=\"month\" colspan=\"2\" valign=\"top\">
	   ".$langCalendar."&nbsp;".ucfirst(claro_format_locale_date("%B %Y",strtotime($myrow["day"])))."
     </td></tr>";
		}
		
		if($numLine%2 == 0)
			$tool_content .=  "<tr>";
		elseif($numLine%2 == 1)
			$tool_content .=  "<tr class=\"odd\">";
		if ($is_adminOfCourse)
			$tool_content .=  "<td valign=\"top\">";
		else
			$tool_content .=  "<td valign=\"top\" colspan=\"2\">";

		$tool_content .=  "<p>".ucfirst(claro_format_locale_date($dateFormatLong,strtotime($myrow["day"])))."
        / $langHour:
        ".ucfirst(date("H:i",strtotime($myrow["hour"])))." ";

		if ($myrow["lasting"] !="") {
			if ($myrow["lasting"] == 1)
				$message = $langHour;
			else
				$message = $langHours;
			$tool_content .=  "<br>".$langLasting.": ".$myrow["lasting"]." ".$message."";
		}

		$tool_content .=  "<br><b>".$myrow["titre"]."</b><br>$contenu</p></td>";

		//agenda event functions
		//added icons next to each function
		//(evelthon, 12/05/2006)

		if ($is_adminOfCourse) {
			$tool_content .=  "<td align='right'>
        <a href=\"$_SERVER[PHP_SELF]?id=".$myrow["id"]."\">
            <img src=\"../../template/classic/img/edit.gif\" border=\"0\" title=\"".$langModify."\"></a>
						&nbsp;
        <a href=\"$_SERVER[PHP_SELF]?id=".$myrow[0]."&delete=yes\" onClick=\"return confirmation('".addslashes($myrow["titre"])."');\">
            <img src=\"../../template/classic/img/delete.gif\" border=\"0\" title=\"".$langDelete."\"></a>
        </td>";
		}
		$tool_content .=  "</tr>";
		$numLine++;
	} 	// while
	$tool_content .= "</tbody>";
	$tool_content .=  "</table>";
} else  {
	$tool_content .= "<p>$langNoEvents</p>";
}
if($is_adminOfCourse && isset($head_content)) {
	draw($tool_content, 2, 'agenda', $head_content, @$body_action);
} else {
	draw($tool_content, 2, 'agenda');
}
?>

