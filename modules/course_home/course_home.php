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


//For statistics: record login
global $uid, $currentCourse, $REMOTE_ADDR;
$sql_log = "INSERT INTO logins SET user_id='$uid', ip='$REMOTE_ADDR', date_time=NOW()";
db_query($sql_log, $currentCourse);

$sql = 'SELECT `description`,`course_keywords`, `course_addon`,`faculte`,`lastEdit`,`type`, `visible`, `titulaires`, `fake_code` FROM `cours` WHERE `code` = "'.$currentCourse.'"';
$res = db_query($sql, $mysqlMainDb);
while($result = mysql_fetch_row($res)) {
	$description = trim($result[0]);
	$keywords = trim($result[1]);
	$addon = nl2br(trim($result[2]));
	$faculte = $result[3];
	$type = $result[5];
	$visible = $result[6];
	$professor = $result[7];
	$fake_code = $result[8];
}

if ($is_adminOfCourse) {
        $edit_link = "&nbsp;<a href='../../modules/course_info/infocours.php'><img src='../../template/classic/img/edit.gif' title='$langEdit'></img></a>";
} else {
        $edit_link = '';
}
$main_content .= "<div class='course_info'>";
if (!empty($description)) {
        $main_content .= "<h1>$langDescription$edit_link</h1><p>$description</p>";

} else {
        $main_content .= "<p>$langThisCourseDescriptionIsEmpty$edit_link</p>";
}
if (!empty($keywords)) {
	$main_content .= "<p><b>$langCourseKeywords</b> $keywords</p>";
}
$main_content .= "</div>\n";

if (!empty($addon)) {
	$main_content .= "<div class='course_info'><h1>$langCourseAddon</h1><p>$addon</p></div>";
}

$result = db_query("SELECT MAX(`order`) FROM course_units WHERE course_id = $cours_id");
list($maxorder) = mysql_fetch_row($result);
 
// other actions in course unit
if ($is_adminOfCourse) {
        if (isset($_REQUEST['edit_submit'])) {
                $title = autoquote($_REQUEST['unittitle']);
                $descr = autoquote($_REQUEST['unitdescr']);
                if (isset($_REQUEST['unit_id'])) { // update course unit
                        $unit_id = intval($_REQUEST['unit_id']);
                        $result = db_query("UPDATE course_units SET
                                                   title = $title,
                                                   comments = $descr
                                            WHERE id = $unit_id AND course_id = $cours_id");
		        $main_content .= "<p class='success_small'>$langCourseUnitModified</p>";
                } else { // add new course unit
                        $order = $maxorder + 1; 
                        db_query("INSERT INTO course_units SET
                                         title = $title, comments =  $descr,
                                         `order` = $order, course_id = $cours_id");
		        $main_content .= "<p class='success_small'>$langCourseUnitAdded</p>";
                }
        } elseif (isset($_REQUEST['del'])) { // delete course unit
		$id = intval($_REQUEST['del']);
		db_query("DELETE FROM course_units WHERE id = '$id'");
		$main_content .= "<p class='success_small'>$langCourseUnitDeleted</p>";
	} elseif (isset($_REQUEST['vis'])) { // modify visibility
		$id = intval($_REQUEST['vis']);
		$sql = db_query("SELECT `visibility` FROM course_units WHERE id='$id'");
		list($vis) = mysql_fetch_row($sql);
		$newvis = ($vis == 'v')? 'i': 'v';
		db_query("UPDATE course_units SET visibility = '$newvis' WHERE id = $id AND course_id = $cours_id");
	} elseif (isset($_REQUEST['down'])) {
		$id = intval($_REQUEST['down']);
		$sql = db_query("SELECT `order` FROM course_units WHERE id='$id'");
		list($current) = mysql_fetch_row($sql);
		$sql = db_query("SELECT id, `order` FROM course_units 
				WHERE `order` > '$current' ORDER BY `order` LIMIT 1");
		list($next_id, $next) = mysql_fetch_row($sql);
		db_query("UPDATE course_units SET `order` = $next WHERE id = $id AND course_id = $cours_id");
		db_query("UPDATE course_units SET `order` = $current WHERE id = $next_id AND course_id = $cours_id");
	} elseif (isset($_REQUEST['up'])) {
		$id = intval($_REQUEST['up']);
		$sql = db_query("SELECT `order` FROM course_units WHERE id='$id'");
		list($current) = mysql_fetch_row($sql);
		$sql = db_query("SELECT id, `order` FROM course_units 
				WHERE `order` < '$current' ORDER BY `order` DESC LIMIT 1");
		list($prev_id, $prev) = mysql_fetch_row($sql);
		db_query("UPDATE course_units SET `order` = $prev WHERE id = $id AND course_id = $cours_id");
		db_query("UPDATE course_units SET `order` = $current WHERE id = $prev_id AND course_id = $cours_id");
	}	
}

// display course units header
if (!is_null($maxorder) or $is_adminOfCourse) {
        $main_content .= "<div class='course_info'><h1>$langCourseUnits</h1>";
}
// add course units
if ($is_adminOfCourse) {
	$main_content .= "<p><a href='{$urlServer}modules/units/info.php'>$langAddUnit</a></p>";
}
if ($is_adminOfCourse) {
        list($last_id) = mysql_fetch_row(db_query("SELECT id FROM course_units
                                                   WHERE course_id = $cours_id
                                                   ORDER BY `order` DESC LIMIT 1"));
	$query = "SELECT id, title, comments, visibility 
		  FROM course_units WHERE course_id = $cours_id
                  ORDER BY `order`";
} else {
	$query = "SELECT id, title, comments, visibility 
		  FROM course_units WHERE course_id = $cours_id AND visibility='v'
                  ORDER BY `order`";
}
$sql = db_query($query);
$first = true;
while ($cu = mysql_fetch_array($sql)) {
	if ($is_adminOfCourse) { // display actions
                // Visibility icon
                $vis = $cu['visibility'];
                $icon_vis = ($vis == 'v')? 'visible.gif': 'invisible.gif';

		$main_content .= "<div class='actions'>".
                        "<a href='../../modules/units/info.php?edit=$cu[id]'>" .
                        "<img src='../../template/classic/img/edit.gif' title='$langEdit' /></a>" .
		        "&nbsp;<a href='$_SERVER[PHP_SELF]?del=$cu[id]' " .
                        "onClick=\"return confirmation();\">" .
                        "<img src='../../template/classic/img/delete.gif' " .
                        "title='$langDelete'></img></a>" .
                        "&nbsp;<a href='$_SERVER[PHP_SELF]?vis=$cu[id]'>" .
        		"<img src='../../template/classic/img/$icon_vis' " .
                        "title='$langVisibility'></img></a>";
		if ($cu['id'] != $last_id) {
			$main_content .= "&nbsp;<a href='$_SERVER[PHP_SELF]?down=$cu[id]'>" .
					 "<img src='../../template/classic/img/down.gif' title='$langDown'></img></a>";
		}
		if (!$first) {
			$main_content .= "&nbsp;<a href='$_SERVER[PHP_SELF]?up=$cu[id]'>" .
			                 "<img src='../../template/classic/img/up.gif' title='$langUp'></img></a>";
		}
                $main_content .= "</div>";
	}
	$main_content .= "<h1><a href='${urlServer}modules/units/?id=$cu[id]'>$cu[title]</a></h1>";

	$main_content .= "<p>$cu[comments]</p>";
	$first = false;
}
// Close units div if open
if (!is_null($maxorder) or $is_adminOfCourse) {
        $main_content .= "</div>\n";
}

$main_content .= "<p>$cu[comments]</p>";

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


$tool_content .= "
<div id='container_login'>
   <div id='wrapper'>
      <div id='content_login'><p>$main_content</p></div>
   </div>
   <div id='navigation'>
      <table><tbody>
      <tr><td class='odd'>$bar_content</td></tr>
      </tbody></table>
      <br />
      <table><tbody>
      <tr>
        <td class='odd' width='1%' align='right'></td>
        <td align='left'>$langContactProf:
                         (<a href='../../modules/contact/index.php'>$langEmail</a>)</td>
      </tr>
      </tbody>
      </table>
   </div>
</div>";

draw($tool_content, 2,'course_home', $head_content);
