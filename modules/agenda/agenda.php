<?
/**===========================================================================
*              GUnet e-Class 2.0
*       E-learning and Course Management Program
* ===========================================================================
*	Copyright(c) 2003-2006  Greek Universities Network - GUnet
*	Á full copyright notice can be read in "/info/copyright.txt".
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

$langFiles = 'agenda';
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Agenda';

include '../../include/baseTheme.php';

include('../../include/lib/textLib.inc.php');

include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_AGENDA');

$dateNow = date("d-m-Y / H:i:s",time());
$nameTools = $langAgenda;
$tool_content = "";

mysql_select_db($dbname);
if (@$addEvent == 1 || (isset($id) && $id)) {
	if ($language == 'greek')
	$lang_editor='gr';
	else
	$lang_editor='en';
	//--end if add event


	//--if add event
	$head_content = <<<hContent
<script type="text/javascript">
  _editor_url = '$urlAppend/include/htmlarea/';
  _css_url='$urlAppend/css/';
  _image_url='$urlAppend/include/htmlarea/images/';
  _editor_lang = '$lang_editor';
</script>
<script type="text/javascript" src='$urlAppend/include/htmlarea/htmlarea.js'></script>

<script type="text/javascript">
var editor = null;

function initEditor() {

  var config = new HTMLArea.Config();
  config.height = '180px';
  config.hideSomeButtons(" showhelp undo redo popupeditor ");

  editor = new HTMLArea("ta",config);

  // comment the following two lines to see how customization works
  editor.generate();
  return false;
}

</script>
hContent;

	$body_action = "onload=\"initEditor()\"";
}
/*
$tool_content .= <<<tContent1
<div id="tool_operations">
<span class="operation">$langDateNow : $dateNow</span>
</div>

tContent1;
*/
if ($is_adminOfCourse) {

	if (isset($submit)&&$submit) {
		$date_selection = $fyear."-".$fmonth."-".$fday;
		$hour = $fhour.":".$fminute.":00";
		if(isset($tout)&&$tout)
		{
			$sql="DELETE FROM agenda";
		}
		elseif(isset($id) && $id) {
			$sql = "UPDATE agenda
                SET titre='".trim($titre)."',
                contenu='".trim($contenu)."',
                day='$date_selection',
                hour='$hour',
                lasting='$lasting'
                WHERE id='$id'";

			##[BEGIN personalisation modification]############
			$perso_sql = "UPDATE $mysqlMainDb.agenda
                                                        SET
                                                                titre=        '".trim($titre)."',
                                                                contenu='".trim($contenu)."',
                                                                day=        '$date_selection',
                                                                hour=        '$hour',
                                                                lasting='$lasting'
                                                        WHERE
                                                                lesson_code= '$currentCourseID'
                                                        AND
                                                                lesson_event_id='$id' ";
			##[END personalisation modification]############

			unset($id);
			unset($contenu);
			unset($titre);
		}
		else
		{
			$sql = "INSERT INTO agenda (id, titre,contenu, day, hour, lasting)
        VALUES (NULL, '".trim($titre)."','".trim($contenu)."', '$date_selection','$hour', '$lasting')";
			unset($id);
			unset($contenu);
			unset($titre);
		}
		$result = db_query($sql, $currentCourseID);

		##[BEGIN personalisation modification]############
		if (substr_count($sql,"INSERT") == 1)
		{
			$perso_sql = "SELECT id, titre, contenu,
                                        DAY , HOUR , lasting
                                        FROM agenda
                                        ORDER BY id DESC 
                                        LIMIT 1 ";

			$perso_result = db_query($perso_sql, $currentCourseID);
			$perso_query_result = mysql_fetch_row($perso_result);

			$perso_matrix['titre']                         = $perso_query_result[0];
			$perso_matrix['titre']                         = $perso_query_result[1];
			$perso_matrix['contenu']                 = $perso_query_result[2];
			$perso_matrix['date_selection'] = $perso_query_result[3];
			$perso_matrix['hour']                         = $perso_query_result[4];
			$perso_matrix['lasting']                 = $perso_query_result[5];

			//                Add all data to the main table.
			$perso_sql = "INSERT INTO $mysqlMainDb.agenda
                                (lesson_event_id, titre, contenu, day, hour, lasting, lesson_code)
                                VALUES
                                (        '".$perso_query_result[0]."','".$perso_query_result[1]."',
                                        '".$perso_query_result[2]."','".$perso_query_result[3]."',
                                        '".$perso_query_result[4]."','".$perso_query_result[5]."',
                                        '".$currentCourseID."'
                                )";

		}

		db_query($perso_sql, $mysqlMainDb);

		unset($perso_matrix);
		unset($perso_sql_delete);
		unset($id);
		##[END personalisation modification]############

		$tool_content .=  "
					<table>
						<tbody>
							<tr>
								<td class=\"success\">
									$langStoredOK
								</td>
							</tr>
						</tbody>
					</table>
					<br/>";

		unset($addEvent);
	}
	elseif (isset($delete) && $delete) {
		$sql = "DELETE FROM agenda WHERE id=$id";
		$result = db_query($sql,$currentCourseID);

		##[BEGIN personalisation modification]############
		$perso_sql= "DELETE FROM $mysqlMainDb.agenda
                                                WHERE
                                                        lesson_code= '$currentCourseID'
                                                AND
                                                        lesson_event_id='$id' ";

		db_query($perso_sql, $mysqlMainDb);
		##[END personalisation modification]############

		$tool_content .=  "
					<table>
						<tbody>
							<tr>
								<td class=\"success\">
									$langDeleteOK
								</td>
							</tr>
						</tbody>
					</table>
					<br/>";
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

	}

	if (!isset($id)) {
		$id="";
	}


	if (@$addEvent == 1 || (isset($id) && $id)) {
		$tool_content .= <<<tContentForm


    <p>$langAddEvent</p>
<form method="post" action="".$_SERVER[PHP_SELF]."">
    <input type="hidden" name="id" value="$id">
    <table width = "99%">
    <thead>
    <tr>
        <th>$langDay</th>
        <th>$langMonth</th>
        <th>$langYear</th>
        <th>$langHour</th>
        <th>$langMinute</th>
        <th>$langLasting</th>
    </thead>
    </tr>
tContentForm;

		$day	= date("d");
		$month	= date("m");
		$year	= date("Y");
		$hours	= date("H");
		$minutes= date("i");

		if (isset($hourAncient) && $hourAncient) {
			$hourAncient = split(":", $hourAncient);
			$hours=$hourAncient[0];
			$minutes=$hourAncient[1];
		}
		if (isset($dayAncient) && $dayAncient) {
			$dayAncient= split("-",  $dayAncient);
			$year=$dayAncient[0];
			$month=$dayAncient[1];
			$day=$dayAncient[2];
		}
		$tool_content .= "
<tbody>
    <tr>
        <td>
            <select name=\"fday\">
                <option value=\"$day\">[$day]</option>";

		for ($d=1; $d<=31; $d++) $tool_content .= "<option value='$d'>$d</option>";

		$tool_content .= "
    </select>
    </td>
    <td>
    <select name=\"fmonth\">
    <option value=\"$month\">[".$langMonthNames['long'][($month-1)]."]</option>";

		for ($i=1; $i<=12; $i++)
		$tool_content .= "<option value='$i'>".$langMonthNames['long'][$i-1]."</option>";
		$tool_content .= "
    </select>
    </td>
    <td>
    <select name=\"fyear\">
    <option value=\"$year\">[$year]</option>";

		$currentyear = date("Y");
		for ($y = $currentyear - 1; $y <= $currentyear + 2; $y++) {
			$tool_content .= "<option value=\"$y\">$y</option>\n";
		}

		$tool_content .= "
    </select>
    </td>
    <td>
    <select name=\"fhour\">
    <option value=\"$hours\">[$hours]</option>
    <option value=\"--\">--</option>";

		for ($h=0; $h<=24; $h++)
		$tool_content .= "<option value='$h'>$h</option>";

		$tool_content .= "
    </select>
    </td>
    <td>
    <select name=\"fminute\">
    <option value=\"$minutes\">[$minutes]</option>
    <option value=\"--\">--</option>";

		for ($m=0; $m<=55; $m=$m+5) $tool_content .=  "<option value='$m'>$m</option>";

		$tool_content .= "
    </select>
    </td>
    <td><input type=\"text\" name=\"lasting\" value=\"".@$myrow['lasting']."\" size=\"8\"></td>
    </tr>
    </tbody>
    </table><br>";

		$tool_content .="
    <table width = \"99%\">
    <thead>
        <tr>
            <th>$langTitle :</th>
            <td colspan=\"5\"><input type=\"text\" size=\"60\" name=\"titre\" value=\"".@$titre."\"></td>
        </tr>
    </thead>
    </table><br>";

		if (!isset($contenu)){
			$contenu="";
		}

		$tool_content .= "
    <table width = \"99%\">
    <thead>
        <tr>
            <th colspan=6>$langDetail</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan=\"6\">
                <textarea id='ta' name='contenu' value='$contenu' rows='20' cols='78'>".$contenu."</textarea>
            </td></tr>
    </tbody>
</table>
    <br>
    <input type=\"Submit\" name=\"submit\" value=\"$langOk\">

</form>
<br><br><br><br>";
	}


	if (@$addEvent != 1) {
		$tool_content .= "

			<a href=\"".$_SERVER['PHP_SELF']."?addEvent=1\">".$langAddEvent."</a> | 
				
";

	}
}

/*---------------------------------------------
*  End  of  prof only
*-------------------------------------------*/
$sens =" ASC";
if (isset($_GET["sens"]) && $_GET["sens"]=="d")
{
	$tool_content .=  "<a href=\"".$_SERVER['PHP_SELF']."?sens=\" >$langOldToNew</a><br/><br/>";
	$sens=" DESC ";
}
else
{
	$tool_content .=  "<a href=\"".$_SERVER['PHP_SELF']."?sens=d\" >$langNewToOld</a><br/><br/>";
}
$tool_content .=  "<table width=\"99%\" cellpadding=\"2\" cellspacing=\"0\" border=\"0\">";
$tool_content .=  "<thead><tr><th>$langEvents</th>";
if ($is_adminOfCourse) {
	$tool_content .=  "<th>$langActions</th>";
}
$tool_content .= "</tr></thead>";
$tool_content .= "<tbody>";
$numLine=0;
$result = db_query("SELECT id, titre, contenu, day, hour, lasting FROM agenda ORDER BY day ".$sens.", hour ".$sens,$currentCourseID);
$barreMois ="";
$nowBarShowed = FALSE;

while ($myrow = mysql_fetch_array($result)) {
	$contenu = $myrow["contenu"];
	$contenu = nl2br($contenu);
	$contenu = make_clickable($contenu);
	if (!$nowBarShowed)
	{
		// Following order
		if (((strtotime($myrow["day"]." ".$myrow["hour"]) > time()) && ($sens==" ASC")) ||
		((strtotime($myrow["day"]." ".$myrow["hour"]) < time()) && ($sens==" DESC "))) {
			if ($barreMois!=date("m",time())) {
				$barreMois=date("m",time());
				$tool_content .=  "
                <tr>
                    <td class=\"month\" colspan=\"2\" valign=\"top\">
                        ".$langCalendar."&nbsp;".ucfirst(claro_format_locale_date("%B %Y",time()))."
                    </td>
                </tr>";
			}
			$nowBarShowed = TRUE;
			$tool_content .=  "<tr>
                    <td colspan=2 class=\"today\">

                            <b>$langNow : $dateNow</b>

                    </td>

                </tr>";
		}
	}
	if ($barreMois!=date("m",strtotime($myrow["day"])))
	{
		$barreMois=date("m",strtotime($myrow["day"]));
		$tool_content .=  "<tr><td class=\"month\" colspan=\"2\" valign=\"top\">
            ".$langCalendar."&nbsp;".ucfirst(claro_format_locale_date("%B %Y",strtotime($myrow["day"])))."
            </td></tr>";
	}
	$tool_content .=  "
<!-- Date -->
    ";
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

	$tool_content .=  "<br>
        <b>".$myrow["titre"]."</b>
        <br>$contenu</p></td>";

	//agenda event functions
	//added icons next to each function
	//(evelthon, 12/05/2006)

	if ($is_adminOfCourse) {
		$tool_content .=  "<td >
        <a href=\"$_SERVER[PHP_SELF]?id=".$myrow["id"]."\">
            <img src=\"../../template/classic/img/edit.gif\" border=\"0\" alt=\"".$langModify."\"></a>

        <a href=\"$_SERVER[PHP_SELF]?id=".$myrow[0]."&delete=yes\">
            <img src=\"../../template/classic/img/delete.gif\" border=\"0\" alt=\"".$langDelete."\"></a>

        </td>";
	}
	$tool_content .=  "</tr>";
	$numLine++;
} 	// while
$tool_content .= "</tbody>";
$tool_content .=  "</table>";

if($is_adminOfCourse && isset($head_content)) {
	draw($tool_content, 2, 'agenda', $head_content, $body_action);
} else {
	draw($tool_content, 2, 'agenda');
}
?>

