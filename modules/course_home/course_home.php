<?
/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
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
*	Contact address:	GUnet Asynchronous Teleteaching Group,
*				Network Operations Center, University of Athens,
*				Panepistimiopolis Ilissia, 15784, Athens, Greece
*				eMail: eclassadmin@gunet.gr
============================================================================*/

/*
 * Course Home Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This component creates the content for the course's home page
 *
 */

$require_current_course = TRUE;
$guest_allowed = true;

//$courseHome is used by the breadcrumb logic
//See function draw() in baseTheme.php for details
//$courseHome = true;
//$path2add is used in init.php to fix relative paths
$path2add=1;
include '../../include/baseTheme.php';
$nameTools = $langIdentity;
$tool_content = $head_content = $main_content = $bar_content = "";

$head_content .= '
<script>
function confirmation ()
{
    if (confirm("'.$langConfirmDelete.'"))
        {return true;}
    else
        {return false;}
}
</script>
';


//For statistics: record login
global $uid, $currentCourse, $REMOTE_ADDR;
$sql_log = "INSERT INTO logins SET user_id='$uid', ip='$REMOTE_ADDR', date_time=NOW()";
db_query($sql_log, $currentCourse);

$sql = 'SELECT `description`,`course_keywords`, `course_addon`,`faculte`,`lastEdit`,`type`, `visible`, `titulaires`, `fake_code` FROM `cours` WHERE `code` = "'.$currentCourse.'"';
$res = db_query($sql, $mysqlMainDb);
while($result = mysql_fetch_row($res)) {
	$description = $result[0];
	$keywords = $result[1];
	$addon = $result[2];
	$faculte = $result[3];
	$type = $result[5];
	$visible = $result[6];
	$professor = $result[7];
	$fake_code = $result[8];
}

if(strlen($description) > 0) {
	$main_content .= "<div id=\"course_home_id\">";
	if (!$is_adminOfCourse) {
		$main_content .= "$langDescription";
	} else {
		$main_content .= "$langDescription &nbsp;<a href='../../modules/course_info/infocours.php'>
		<img src='../../template/classic/img/edit.gif' title='$langEdit' border='0'></img></a>";
    }
    $main_content .= "</div><p>$description</p>";

} else {
	$main_content .= $langThisCourseDescriptionIsEmpty;
	if (!$is_adminOfCourse) {
		$main_content .= "&nbsp;";
	} else {
		$main_content .= "&nbsp;&nbsp;<a href='../../modules/course_info/infocours.php'>
		<img src='../../template/classic/img/edit.gif' title='$langEdit' border='0'></img></a>";
    }
}

if (strlen($keywords) > 0) {
	$main_content .= "<p><br /><b>$langCourseKeywords </b>$keywords</p><p>&nbsp;</p>";
}

if(strlen($addon) > 0) {
	$main_content .= "<div id=\"course_home_id\">$langCourseAddon</div><p>". nl2br($addon)."</p>";
}

// other actions in course unit
if ($is_adminOfCourse) {
	if (isset($del)) { // delete course unit
		$id = intval($del);
		db_query("DELETE FROM course_units WHERE id = '$id'");
		$main_content .= "<p class='success_small'>$langCourseUnitDeleted</p>";
	} elseif (isset($vis)) { // modify visibility
		$id = intval($vis);
		$sql = db_query("SELECT `visibility` FROM course_units WHERE id='$id'");
		list($vis) = mysql_fetch_row($sql);
		$newvis = ($vis == 'v')? 'i': 'v';
		db_query("UPDATE course_units SET visibility = '$newvis' WHERE id = '$id'");
	} elseif (isset($down)) {
		$id = intval($down);
		$sql = db_query("SELECT `order` FROM course_units WHERE id='$id'");
		list($current) = mysql_fetch_row($sql);
		$sql = db_query("SELECT id, `order` FROM course_units 
				WHERE `order` > '$current' ORDER BY `order` LIMIT 1");
		list($next_id, $next) = mysql_fetch_row($sql);
		db_query("UPDATE course_units SET `order` = $next WHERE id = $id");
		db_query("UPDATE course_units SET `order` = $current WHERE id = $next_id");
	} elseif (isset($up)) {
		$id = intval($up);
		$sql = db_query("SELECT `order` FROM course_units WHERE id='$id'");
		list($current) = mysql_fetch_row($sql);
		$sql = db_query("SELECT id, `order` FROM course_units 
				WHERE `order` < '$current' ORDER BY `order` DESC LIMIT 1");
		list($prev_id, $prev) = mysql_fetch_row($sql);
		db_query("UPDATE course_units SET `order` = $prev WHERE id = $id");
		db_query("UPDATE course_units SET `order` = $current WHERE id = $prev_id");
	}	
}

// display course units
$main_content .= "<div id='course_home_id'><p>$langCourseUnits</p></div>";
// add course units
if ($is_adminOfCourse) {
	$main_content .= "<p><a href='{$urlServer}modules/units/index.php'>$langCourseUnit</a></p>";
}
list($last_id) = mysql_fetch_row(db_query("SELECT id FROM course_units WHERE course_id='$currentCourseID' 
		ORDER BY `order` DESC LIMIT 1"));
if ($is_adminOfCourse) {
	$query = "SELECT id, title, comments, visibility 
		FROM course_units WHERE course_id='$currentCourseID' ORDER BY `order`";
} else {
	$query = "SELECT id, title, comments, visibility 
		FROM course_units WHERE course_id='$currentCourseID' AND visibility='v' ORDER BY `order`";
}
$sql = db_query($query);
$first = true;
while ($cu = mysql_fetch_array($sql)) {
	$main_content .= "<h4>$cu[title]";
	$vis = $cu['visibility'];
	if ($vis == 'v') { // define visibility actions
		$icon_vis = 'visible.gif';
	} else {
		$icon_vis = 'invisible.gif';
	}
	if ($is_adminOfCourse) { // display actions
		$main_content .= "&nbsp;&nbsp;
		<a href='../../modules/units/index.php?id=$cu[id]&edit=TRUE'>
		<img src='../../template/classic/img/edit.gif' title='$langEdit'></img></a>";
		$main_content .= "&nbsp;&nbsp;
		<a href='$_SERVER[PHP_SELF]?del=$cu[id]' onClick=\"return confirmation();\">
		<img src='../../template/classic/img/delete.gif' title='$langDelete'></img></a>";
		$main_content .= "&nbsp;&nbsp;
		<a href='$_SERVER[PHP_SELF]?vis=$cu[id]'>
		<img src='../../template/classic/img/$icon_vis' title='$langVisibility'></img></a>";
		if ($cu['id'] != $last_id) {
			$main_content .= "<a href='$_SERVER[PHP_SELF]?down=$cu[id]'>" .
					 "<img src='../../template/classic/img/down.gif' title='$langDown'></img></a>";
		}
		if (!$first) {
			$main_content .= "&nbsp;&nbsp;<a href='$_SERVER[PHP_SELF]?up=$cu[id]'>" .
			                 "<img src='../../template/classic/img/up.gif' title='$langUp'></img></a>";
		}
	}
	$main_content .= "</h3>";
	$main_content .= "<p>$cu[comments]</p>";
	$main_content .= "<br>";
	$first = false;
}

switch ($type){
	case 'pre': { //pre
		$lessonType = $m['pre'];
		break;
	}
	case 'post': {//post
		$lessonType = $m['post'];
		break;
	}
	case 'other': { //other
		$lessonType = $m['other'];
		break;
	}
}

$bar_content .= "<p><b>".$langLessonCode."</b>: ".$fake_code."</p>";
$bar_content .= "<p><b>".$langTeachers."</b>: ".$professor."</p>";
$bar_content .= "<p><b>".$langFaculty."</b>: ".$faculte."</p>";
$bar_content .= "<p><b>".$m['type']."</b>: ".$lessonType."</p>";

$require_help = TRUE;
$helpTopic = 'course_home';

if ($is_adminOfCourse) {
	$sql = "SELECT COUNT(user_id) AS numUsers
			FROM cours_user
			WHERE code_cours = '$currentCourse'";
	$res = db_query($sql, $mysqlMainDb);
	while($result = mysql_fetch_row($res)) {
		$numUsers = $result[0];
	}

	//set the lang var for lessons visibility status
	switch ($visible){
		case 0: { //closed
			$lessonStatus = $langPrivate;
			break;
		}

		case 1: {//open with registration
			$lessonStatus = $langPrivOpen;
			break;
		}

		case 2: { //open
			$lessonStatus = $langPublic;
			break;
		}
	}
	$bar_content .= "<p><b>".$langConfidentiality."</b>: ".$lessonStatus."</p>";
	$bar_content .= "<p><b>".$langUsers."</b>: ".$numUsers." ".$langRegistered."</p>";
}


$tool_content .= <<<lCont
<div id="container_login">
<div id="wrapper">
<div id="content_login"><p>$main_content</p></div>
</div>
<div id="navigation">
  <table width="99%">
  <tbody>
  <tr>
    <td class="odd">
    $bar_content
    </td>
  </tr>
  </tbody>
  </table>

  <br />

  <table width="99%">
  <tbody>
  <tr>
    <td class="odd" width="1%" align="right"></td>
    <td align="left">
      $langContactProf: (<a href="../../modules/contact/index.php">$langEmail</a>)
    </td>
  </tr>
  </tbody>
  </table>


</div>
</div>

lCont;

//-----------------------------------------------------------
draw($tool_content, 2,'course_home', $head_content);
?>
