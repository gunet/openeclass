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
define('HIDE_TOOL_TITLE', 1);

// $courseHome is used by the breadcrumb logic
// See function draw() in baseTheme.php for details
// $courseHome = true;
// $path2add is used in init.php to fix relative paths
$path2add = 1;
include '../../include/baseTheme.php';
require_once '../../modules/video/video_functions.php';

$nameTools = $langIdentity;
$main_content = $cunits_content = $bar_content = "";

add_units_navigation(TRUE);

load_js('tools.js');
load_js('jquery');
load_modal_box();
$head_content .= "<script type='text/javascript'>$(document).ready(add_bookmark);</script>";

//For statistics: record login
$sql_log = "INSERT INTO logins SET user_id='$uid', ip='$_SERVER[REMOTE_ADDR]', date_time=NOW()";
db_query($sql_log, $currentCourse);
include '../../include/action.php';
$action = new action();
$action->record(MODULE_ID_UNITS);

if (isset($_GET['from_search'])) { // if we come from home page search
        header("Location: {$urlServer}modules/search/search_incourse.php?all=true&search_terms=$_GET[from_search]");
}

$res = db_query("SELECT course_keywords, faculte.name AS faculte, type, visible, titulaires, fake_code
                        FROM cours, faculte
                        WHERE cours_id = $cours_id 
                        AND faculte.id = faculteid", $mysqlMainDb);
$result = mysql_fetch_array($res);
$keywords = q(trim($result['course_keywords']));
$faculte = $result['faculte'];
$type = $result['type'];
$visible = $result['visible'];
$professor = $result['titulaires'];
$fake_code = $result['fake_code'];
$main_extra = $description = $addon = '';
$res = db_query("SELECT res_id, title, comments FROM unit_resources WHERE unit_id =
                        (SELECT id FROM course_units WHERE course_id = $cours_id AND `order` = -1)
                        AND (visibility = 'v' OR res_id < 0)
                 ORDER BY `order`");
if ($res and mysql_num_rows($res) > 0) {
        while ($row = mysql_fetch_array($res)) {
                if ($row['res_id'] == -1) {
                        $description = standard_text_escape($row['comments']);
                } elseif ($row['res_id'] == -2) {
                        $addon = standard_text_escape($row['comments']);
                } else {
                        if (isset($idBloc[$row['res_id']]) and !empty($idBloc[$row['res_id']])) {
                                $element_id = "class='course_info' id='{$idBloc[$row['res_id']]}'";
                        } else {
                                $element_id = 'class="course_info other"';
                        }
                        $main_extra .= "<div $element_id><h1>" . q($row['title']) . "</h1>" .
                                standard_text_escape($row['comments']) . "</div>\n";
                }
        }
}
if ($is_editor) {
        $edit_link = "&nbsp;<a href='../../modules/course_description/editdesc.php?course=$code_cours'><img src='$themeimg/edit.png' title='$langEdit' alt='$langEdit' /></a>";
} else {
        $edit_link = '';
}
$main_content .= "\n      <div class='course_info'>";
if (!empty($description)) {
        $main_content .= "\n      <div class='descr_title'>$langDescription$edit_link</div>\n$description";

} else {
        $main_content .= "\n      <p>$langThisCourseDescriptionIsEmpty$edit_link</p>";
}
if (!empty($keywords)) {
	$main_content .= "\n      <p id='keywords'><b>$langCourseKeywords</b> $keywords</p>";
}
$main_content .= "\n      </div>\n";

if (!empty($addon)) {
	$main_content .= "\n      <div class='course_info'><h1>$langCourseAddon</h1><p>$addon</p></div>";
}
$main_content .= $main_extra;

units_set_maxorder();

// other actions in course unit
if ($is_editor) {
        if (isset($_REQUEST['edit_submit'])) {
                $main_content .= handle_unit_info_edit();
        } elseif (isset($_REQUEST['del'])) { // delete course unit
		$id = intval($_REQUEST['del']);
		db_query("DELETE FROM course_units WHERE id = '$id'");
		db_query("DELETE FROM unit_resources WHERE unit_id = '$id'");
		$main_content .= "<p class='success_small'>$langCourseUnitDeleted</p>";
	} elseif (isset($_REQUEST['vis'])) { // modify visibility
		$id = intval($_REQUEST['vis']);
		$sql = db_query("SELECT `visibility` FROM course_units WHERE id='$id'");
		list($vis) = mysql_fetch_row($sql);
		$newvis = ($vis == 'v')? 'i': 'v';
		db_query("UPDATE course_units SET visibility = '$newvis' WHERE id = $id AND course_id = $cours_id");
	} elseif (isset($_REQUEST['down'])) {
		$id = intval($_REQUEST['down']); // change order down
                move_order('course_units', 'id', $id, 'order', 'down',
                           "course_id=$cours_id");

	} elseif (isset($_REQUEST['up'])) { // change order up
		$id = intval($_REQUEST['up']);
                move_order('course_units', 'id', $id, 'order', 'up',
                           "course_id=$cours_id");
	}
}

// add course units
if ($is_editor) {
        $cunits_content .= "
    <p class='descr_title'>$langCourseUnits: <a href='{$urlServer}modules/units/info.php?course=$code_cours'><img src='$themeimg/add.png' width='16' height='16' title='$langAddUnit' alt='$langAddUnit' /></a></p>\n";

} else {
        $cunits_content .= "\n  <p class='descr_title'>$langCourseUnits</p>";
}
if ($is_editor) {
        list($last_id) = mysql_fetch_row(db_query("SELECT id FROM course_units
                                                   WHERE course_id = $cours_id AND `order` >= 0
                                                   ORDER BY `order` DESC LIMIT 1"));
	$query = "SELECT id, title, comments, visibility
		  FROM course_units WHERE course_id = $cours_id AND `order` >= 0
                  ORDER BY `order`";
} else {
	$query = "SELECT id, title, comments, visibility
		  FROM course_units WHERE course_id = $cours_id AND visibility='v' AND `order` >= 0
                  ORDER BY `order`";
}
$sql = db_query($query);
$first = true;
$count_index = 1;
while ($cu = mysql_fetch_array($sql)) {
        // Visibility icon
        $vis = $cu['visibility'];
        $icon_vis = ($vis == 'v')? 'visible.png': 'invisible.png';
        $class1_vis = ($vis == 'i')? ' class="invisible"': '';
        $class_vis = ($vis == 'i')? 'invisible': '';
        $cunits_content .= "\n\n\n      <table ";
        if ($is_editor) {
            $cunits_content .= "class='tbl'";
        } else {
            $cunits_content .= "class='tbl'";
        }
        $cunits_content .= " width='770'>";
        if ($is_editor) {
        $cunits_content .= "\n      <tr>".
                           "\n        <th width='25' class='right'>$count_index.</th>" .
                           "\n        <th width='635'><a class=\"$class_vis\" href='${urlServer}modules/units/?course=$code_cours&amp;id=$cu[id]'>" . q($cu['title']) . "</a></th>";
        } else {
        $cunits_content .= "\n      <tr>".
                           "\n        <th width='25' class='right'>$count_index.</th>".
                           "\n        <th width='729'><a class=\"$class_vis\" href='${urlServer}modules/units/?course=$code_cours&amp;id=$cu[id]'>" . q($cu['title']) . "</a></th>";
        }

        if ($is_editor) { // display actions
                $cunits_content .= "\n        <th width='70' class='center'>".
                        "<a href='../../modules/units/info.php?course=$code_cours&amp;edit=$cu[id]'>" .
                        "<img src='$themeimg/edit.png' title='$langEdit' /></a>" .
                        "\n        <a href='$_SERVER[PHP_SELF]?del=$cu[id]' " .
                        "onClick=\"return confirmation('$langConfirmDelete');\">" .
                        "<img src='$themeimg/delete.png' " .
                        "title='$langDelete' /></a>" .
                        "\n        <a href='$_SERVER[PHP_SELF]?vis=$cu[id]'>" .
                        "<img src='$themeimg/$icon_vis' " .
                        "title='$langVisibility' /></a></th>";
                if ($cu['id'] != $last_id) {
                        $cunits_content .= "\n        <th width='40' class='right'><a href='$_SERVER[PHP_SELF]?down=$cu[id]'>" .
                        "<img src='$themeimg/down.png' title='$langDown' /></a>";
                } else {
                        $cunits_content .= "\n        <th width='40' class='right'>&nbsp;&nbsp;&nbsp;&nbsp;";
                }
                if (!$first) {
                        $cunits_content .= "\n        <a href='$_SERVER[PHP_SELF]?up=$cu[id]'><img src='$themeimg/up.png' title='$langUp' /></a></th>";
                } else {
                        $cunits_content .= "\n        &nbsp;&nbsp;&nbsp;&nbsp;</th>";
                }
        }
        $cunits_content .= "\n      </tr>\n      <tr>\n        <td ";
        if ($is_editor) {
            $cunits_content .= "colspan='7' $class1_vis>";
        } else {
            $cunits_content .= "colspan='2'>";
        }
        $cunits_content .= standard_text_escape($cu['comments']) . "\n    </td>\n  </tr>\n" .
                           "\n  </table>\n";
        $first = false;
        $count_index++;
}
if ($first and !$is_editor) {
        $cunits_content = '';
}

switch ($type){
	case 'pre': { //pre
		$lessonType = $langpre;
		break;
	}
	case 'post': {//post
		$lessonType = $langpost;
		break;
	}
	case 'other': { //other
		$lessonType = $langother;
		break;
	}
}

$bar_content .= "\n<ul class='custom_list'><li><b>".$langCode."</b>: ".q($fake_code)."</li>".
                "\n<li><b>".$langTeachers."</b>: ".q($professor)."</li>".
                "\n<li><b>".$langFaculty."</b>: ".q($faculte)."</li>".
                "\n<li> <b>".$langType."</b>: ".$lessonType."</li>";

$require_help = TRUE;
$helpTopic = 'course_home';

if ($is_editor) {       
	$sql = "SELECT COUNT(user_id) AS numUsers
			FROM cours_user
			WHERE cours_id = $cours_id";
	$res = db_query($sql, $mysqlMainDb);
	while($result = mysql_fetch_row($res)) {
		$numUsers = $result[0];
        }        
	//set the lang var for lessons visibility status      
	switch ($visible) {                
		case COURSE_CLOSED: { 
			$lessonStatus = "<span title='$langPrivate'>$langPrivateShort</span>";
			break;
		}
		case COURSE_REGISTRATION: {
			$lessonStatus = "<span title='$langPrivOpen'>$langPrivOpenShort</span>";
			break;
		}
		case COURSE_OPEN: { 
			$lessonStatus = "<span title='$langPublic'>$langPublicShort</span>";
			break;
		}                
                case COURSE_INACTIVE: {                                                          
			$lessonStatus = "<span class='invisible' title='$langCourseInactive'>$langCourseInactiveShort</span>";
			break;
		}
	}
	$bar_content .= "<li><b>$langConfidentiality</b>: $lessonStatus</li>";
        if ($is_course_admin) {
            $link = "<a href='$urlAppend/modules/user/user.php?course=$code_cours'>$numUsers $langRegistered</a>";
        } else {
            $link = "$numUsers $langRegistered";
        }
	$bar_content .= "<li><b>$langUsers</b>: $link</li></ul>";
}

if ($is_editor or (isset($_SESSION['saved_editor']) and $_SESSION['saved_editor']) 
        or (isset($_SESSION['saved_statut']) and $_SESSION['saved_statut'] == 1)) {
        if (isset($_SESSION['saved_statut'])) {
                $button_message = $langStudentViewDisable;
                $button_image = "switch_t";
        } else {
                $button_message = $langStudentViewEnable;
                $button_image = "switch_s";
        }
        $toggle_student_view = "<form action='{$urlServer}student_view.php?course=$code_cours' method='post'>
                <input id='view_btn' type='image' src='$themeimg/$button_image.png' name='submit' title='$button_message'/>&nbsp;&nbsp;";
        $toggle_student_view_close = '</form>';
    } else {
        $toggle_student_view = $toggle_student_view_close = '';
}

$emailnotification = '';
if ($uid and $statut != 10 and !get_user_email_notification($uid, $cours_id)) {
        $emailnotification = "<div class='alert1'>$langNoUserEmailNotification 
        (<a href='{$urlServer}modules/profile/emailunsubscribe.php?cid=$cours_id'>$langModify</a>)</div>";
} 

$tool_content .= "
<div id='content_course'>

   <table width='100%'>
   <tr>
      <td valign='top'>$main_content</td>
      <td width='200' valign='top'>
        <table class='tbl_courseid' width='200'>
        <tr class='title1'>
          <td  class='title1'>$langIdentity</td>
        </tr>
        <tr>
          <td class='smaller'>$bar_content</td>
        </tr>
        </table>
        <br />
        <table class='tbl_courseid' width='200'>
        <tr class='title1'>
          <td class='title1'>$langTools</td>
          <td class='left'>$toggle_student_view
             <a href='../../modules/contact/index.php?course=$code_cours' id='email_btn'><img src='$themeimg/email.png' alt='$langContactProf' title='$langContactProf' /></a>&nbsp;&nbsp;
             <a href='$_SERVER[PHP_SELF]' title='" . q($intitule) . "' class='jqbookmark'><img src='$themeimg/bookmark.png' alt='$langAddAsBookmark' title='$langAddAsBookmark' /></a>&nbsp;&nbsp;
            <span class='feed'><a href='${urlServer}modules/announcements/rss.php?c=$currentCourseID'><img src='$themeimg/feed.png' alt='$langRSSFeed' title='$langRSSFeed' /></a></span>&nbsp;$toggle_student_view_close           
            </td>                     
        </tr>        
        </table>
        $emailnotification
        <br />\n";

$tool_content .= "
      </td>
   </tr>
   </table>

   <table width='100%' class='tbl'>
   <tr>
     <td>$cunits_content</td>
   </tr>
  </table>
</div>
";
draw($tool_content, 2, null, $head_content);