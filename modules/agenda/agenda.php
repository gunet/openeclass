<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
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

$action = new action();
$action->record('MODULE_ID_AGENDA');

$dateNow = date("j-n-Y / H:i",time());
$datetoday = date("Y-n-j",time());

$nameTools = $langAgenda;
$tool_content = $head_content = "";

mysql_select_db($dbname);

if (isset($_GET['id'])) {
	$id = intval($_GET['id']);
}
$date = isset($_POST['date'])?$_POST['date']:"";
$fhour = isset($_POST['fhour'])?$_POST['fhour']:"";
$fminute = isset($_POST['fminute'])?$_POST['fminute']:"";
$titre = isset($_POST['titre'])?$_POST['titre']:"";
$contenu = isset($_POST['contenu'])?$_POST['contenu']:"";
$lasting = isset($_POST['lasting'])?$_POST['lasting']:"";
		
if ($is_adminOfCourse and (isset($_GET['addEvent']) or isset($_GET['id']))) {
	$lang_editor = langname_to_code($language);
	$lang_jscalendar = langname_to_code($language);

	//--if add event
$head_content = <<<hContent
<script type="text/javascript" src="$urlAppend/include/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
tinyMCE.init({
	// General options
		language : "$lang_editor",
		mode : "textareas",
		theme : "advanced",
		plugins : "pagebreak,style,save,advimage,advlink,inlinepopups,media,print,contextmenu,paste,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,emotions,preview",

		// Theme options
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontsizeselect,forecolor,backcolor,removeformat,hr",
		theme_advanced_buttons2 : "pasteword,|,bullist,numlist,|indent,blockquote,|,sub,sup,|,undo,redo,|,link,unlink,|,charmap,media,emotions,image,|,preview,cleanup,code",
		theme_advanced_buttons3 : "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,

		// Example content CSS (should be your site CSS)
		content_css : "$urlAppend/template/classic/img/tool.css",

		// Drop lists for link/image/media/template dialogs
		template_external_list_url : "lists/template_list.js",
		external_link_list_url : "lists/link_list.js",
		external_image_list_url : "lists/image_list.js",
		media_external_list_url : "lists/media_list.js",

		// Style formats
		style_formats : [
			{title : 'Bold text', inline : 'b'},
			{title : 'Red text', inline : 'span', styles : {color : '#ff0000'}},
			{title : 'Red header', block : 'h1', styles : {color : '#ff0000'}},
			{title : 'Example 1', inline : 'span', classes : 'example1'},
			{title : 'Example 2', inline : 'span', classes : 'example2'},
			{title : 'Table styles'},
			{title : 'Table row 1', selector : 'tr', classes : 'tablerow1'}
		],

		// Replace values for the template plugin
		template_replace_values : {
			username : "Open eClass",
			staffid : "991234"
		}
});
</script>

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
	// modify visibility
	if (isset($_GET['mkInvisibl']) and $_GET['mkInvisibl'] == true) {
		$sql = "UPDATE agenda SET visibility = 'i'
			WHERE id='".mysql_real_escape_string($id)."'";
		$p_sql= "DELETE FROM agenda WHERE lesson_code = '$currentCourseID' 
			AND lesson_event_id ='".mysql_real_escape_string($id)."'";
		db_query($sql);
		db_query($p_sql, $mysqlMainDb);
	} elseif (isset($_GET['mkVisibl']) and ($_GET['mkVisibl'] == true)) {
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
		db_query($sql);
		db_query($p_sql, $mysqlMainDb);
	}
	if (isset($_POST['submit'])) {
		$date_selection = $date;
		$hour = $fhour.":".$fminute;
		if (isset($_POST['id']) and !empty($_POST['id'])) {
			$id = intval($_POST['id']);
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
		} else {
			$sql = "INSERT INTO agenda (titre, contenu, day, hour, lasting)
        		VALUES ('".mysql_real_escape_string(trim($titre))."',
				'".mysql_real_escape_string(trim($contenu))."', 
				'".mysql_real_escape_string($date_selection)."',
				'".mysql_real_escape_string($hour)."',
				'".mysql_real_escape_string($lasting)."')";
		}
		unset($contenu);
		unset($titre);
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
		$tool_content .=  "<p class='success'>$langStoredOK</p><br />";
		unset($addEvent);
	}
	elseif (isset($_GET['delete']) && $_GET['delete'] == 'yes') {
		$sql = "DELETE FROM agenda WHERE id=$id";
		$result = db_query($sql,$currentCourseID);

		##[BEGIN personalisation modification]############
		$perso_sql= "DELETE FROM $mysqlMainDb.agenda
                      WHERE lesson_code= '$currentCourseID'
                      AND lesson_event_id='$id' ";

		db_query($perso_sql, $mysqlMainDb);
		##[END personalisation modification]############

		$tool_content .= "<p class='success_small'>$langDeleteOK</p><br />";
		unset($addEvent);
	}
//Make top tool links
if ($is_adminOfCourse) {
	$head_content .= '
<script type="text/javascript">
function confirmation ()
{
    if (confirm("'.$langConfirmDelete.'"))
        {return true;}
    else
        {return false;}
}
</script>
';

	$tool_content .= "\n  <div id='operations_container'>\n    <ul id='opslist'>";
	if ((!isset($addEvent) && @$addEvent != 1) || isset($_POST['submit'])) {
		$tool_content .= "\n      <li><a href='$_SERVER[PHP_SELF]?addEvent=1'>".$langAddEvent."</a></li>";
	}
	$sens =" ASC";
	$result = db_query("SELECT id FROM agenda", $currentCourseID);
	if (mysql_num_rows($result) > 1) {
		if (isset($_GET["sens"]) && $_GET["sens"]=="d") {
			$tool_content .= "\n      <li><a href='$_SERVER[PHP_SELF]?sens=' >$langOldToNew</a></li>";
			$sens=" DESC ";
		} else {
			$tool_content .= "\n      <li><a href='$_SERVER[PHP_SELF]?sens=d' >$langOldToNew</a></li>";
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

	if (isset($_GET['addEvent']) or isset($_GET['edit'])) {
		$nameTools = $langAddEvent;
		$navigation[] = array ("url" => $_SERVER['PHP_SELF'], "name" => $langAgenda);
		$tool_content .= "
		<form method='post' action='$_SERVER[PHP_SELF]' onsubmit='return checkrequired(this, \"titre\");'>
		<input type='hidden' name='id' value='$id' />
                <fieldset>
                  <legend>$langOptions</legend>
		  <table class='tbl'>";
		$day	= date("d");
		$hours	= date("H");
		$minutes= date("i");

		if (isset($hourAncient) && $hourAncient) {
			$hourAncient = explode(":", $hourAncient);
			$hours=$hourAncient[0];
			$minutes=$hourAncient[1];
		}
		$tool_content .= "
                  <tr>
                    <th>$langTitle:</th>
                    <td><input type='text' size='70' name='titre' value='".@$titre."' /></td>
                  </tr>
		  <tr>
                    <th>$langDate:</th>
                    <td> ".$start_cal."</td>
                  </tr>
                  <tr>
		    <th>$langHour:</th>
                    <td><select name='fhour'>
		 	<option value='$hours'>$hours</option>
			<option value='--'>--</option>";
			for ($h=0; $h<=24; $h++)
			   $tool_content .= "\n                        <option value='$h'>$h</option>";
			   $tool_content .= "\n                        </select>&nbsp;&nbsp;&nbsp;";
			   $tool_content .= "
                    </td>
                  </tr>
                  <tr>
                    <th>$langMinute:</th>
                    <td><select name='fminute'>
			<option value='$minutes'>[$minutes]</option>
			<option value='--'>--</option>";
			for ($m=0; $m<=55; $m=$m+5)
				$tool_content .=  "\n                        <option value='$m'>$m</option>";

			$tool_content .= "\n                        </select>
                    </td>
                  </tr>
                  <tr>
                    <th>$langLasting $langInHour:</td>
                    <td><input type='text' name='lasting' value='".@$myrow['lasting']."' size='2' maxlength='2' /></td>
                  </tr>";
    		if (!isset($contenu)) {
			$contenu = "";
		}
		$tool_content .= "
                  <tr>
                    <th>$langDetail:</th>
                    <th>". rich_text_editor('contenu', 4, 20, $contenu) ."</td>
                  </tr>
		  <tr>
                    <th>&nbsp;</th>
                    <td><input type='submit' name='submit' value='$langAddModify' /></td>
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

if ($is_adminOfCourse) { 
	$result = db_query("SELECT id, titre, contenu, day, hour, lasting, visibility FROM agenda ORDER BY day ".$sens.", hour ".$sens,$currentCourseID);
} else {
	$result = db_query("SELECT id, titre, contenu, day, hour, lasting, visibility FROM agenda WHERE visibility = 'v' 
		ORDER BY day ".$sens.", hour ".$sens,$currentCourseID);
}

if (mysql_num_rows($result) > 0) {
	$numLine=0;
	$barreMois = "";
	$nowBarShowed = FALSE;
        $tool_content .= "
        <table width='99%' align='left' class='tbl_alt'>
        <tr>
          <th><div align='left'><b>$langEvents</b></div></th>";
        if ($is_adminOfCourse) {
              $tool_content .= "
          <th width='60' class='right'><b>$langActions</b></th>";
        } else {
              $tool_content .= "
          <th width='60' class='right'>&nbsp;</th>";
        }
       
        $tool_content .= "
        </tr>";

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
					$tool_content .= "\n        <tr class='odd'>";
					// current month
					$tool_content .= "\n          <td colspan='2' class='monthLabel'>".$langCalendar."&nbsp;<b>".ucfirst(claro_format_locale_date("%B %Y",time()))."</b></td>";
					$tool_content .= "\n        </tr>";
				}
				$nowBarShowed = TRUE;
				$tool_content .= "\n        <tr>";
				$tool_content .= "\n          <td colspan=2 class='today'><b>$langDateNow : $dateNow</b></td>";
				$tool_content .= "\n        </tr>";
			}
		}
		if ($barreMois!=date("m",strtotime($myrow["day"]))) {
			$barreMois=date("m",strtotime($myrow["day"]));
            		// month LABEL
			$tool_content .= "\n        <tr>";
			$tool_content .= "\n          <td colspan='2' class='monthLabel'><div align='center'>".$langCalendar."&nbsp;<b>".ucfirst(claro_format_locale_date("%B %Y",strtotime($myrow["day"])))."</b></div></td>";
			$tool_content .= "\n        </tr>";
		}

		if ($is_adminOfCourse) {
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
                              $tool_content .= "\n        <tr class='even'>";
                            } else {
                              $tool_content .= "\n        <tr class='odd'>";
                            }
                        $tool_content .= "\n          <td valign='top' colspan='2'>";
		}

		$tool_content .= "\n              <span class='day'>".ucfirst(claro_format_locale_date($dateFormatLong,strtotime($myrow["day"])))."</span> ($langHour: ".ucfirst(date("H:i",strtotime($myrow["hour"]))).")";
		$message = "$langUnknown";
		if ($myrow["lasting"] != "") {
			if ($myrow["lasting"] == 1)
				$message = $langHour;
			else
				$message = $langHours;
		}
		$tool_content .=  "\n              <p class='event'><b>";
		if ($myrow["titre"]=="") {
		    $tool_content .= "".$langAgendaNoTitle."";
		} else {
		    $tool_content .= "".$myrow["titre"]."";
		}
		$tool_content .= "</b> (".$langLasting.": ".$myrow["lasting"]." ".$message.")</p>
              <p class='agendaBody'>$contenu</p></td>";

	//agenda event functions
	//added icons next to each function
	//(evelthon, 12/05/2006)
		if ($is_adminOfCourse) {
			$tool_content .=  "
          <td class='right' width='80'>
            <a href='$_SERVER[PHP_SELF]?id=".$myrow['id']."&amp;edit=true'>
            <img src='../../template/classic/img/edit.png' border='0' title='".$langModify."'></a>&nbsp;
            <a href='$_SERVER[PHP_SELF]?id=".$myrow[0]."&amp;delete=yes' onClick='return confirmation();'>
            <img src='../../template/classic/img/delete.png' border='0' title='".$langDelete."'></a>&nbsp;";
			if ($myrow["visibility"] == 'v') {
				$tool_content .= "
            <a href='$_SERVER[PHP_SELF]?id=".$myrow[0]."&amp;mkInvisibl=true'>
            <img src='../../template/classic/img/visible.png' border='0' title='".$langVisible."'></a>";
			} else {
				$tool_content .= "
            <a href='$_SERVER[PHP_SELF]?id=".$myrow[0]."&amp;mkVisibl=true'>
            <img src='../../template/classic/img/invisible.png' border='0' title='".$langVisible."'></a>";
			}
			$tool_content .= "
          </td>";
		}
		$tool_content .= "\n        </tr>";
		$numLine++;
	} 	// while
	$tool_content .= "\n        </table>";

} else  {
	$tool_content .= "\n          <p class='alert1'>$langNoEvents</p>";
}
add_units_navigation(TRUE);
if($is_adminOfCourse && isset($head_content)) {
	draw($tool_content, 2, '', $head_content, @$body_action);
} else {
	draw($tool_content, 2);
}
