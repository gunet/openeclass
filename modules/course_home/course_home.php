<?php
/* ========================================================================
 * Open eClass 2.9
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
if (!defined('HIDE_TOOL_TITLE')) {
        define('HIDE_TOOL_TITLE', 1);
}

// $courseHome is used by the breadcrumb logic
// See function draw() in baseTheme.php for details
// $courseHome = true;
// $path2add is used in init.php to fix relative paths
$path2add = 1;
require_once '../../include/baseTheme.php';
require_once '../../include/lib/modalboxhelper.class.php';
require_once '../../include/lib/multimediahelper.class.php';

$nameTools = $langIdentity;
$main_content = $cunits_content = $bar_content = "";

add_units_navigation(TRUE);

load_js('tools.js');
load_js('jquery');
load_js('slick');
ModalBoxHelper::loadModalBox();
$head_content .= "<script type='text/javascript'>$(document).ready(add_bookmark);</script>
<script type='text/javascript'>
    $(document).ready(function() {
            $('.course_description').slick({
                dots: false, slidesToShow: 4, slidesToScroll: 1, touchMove: false
            });
            $('.inline').colorbox({ inline: true, width: '50%', rel: 'info' });
    })
</script>";

//For statistics: record login
$sql_log = "INSERT INTO logins SET user_id='$uid', ip='$_SERVER[REMOTE_ADDR]', date_time=NOW()";
db_query($sql_log, $currentCourse);
require_once '../../include/action.php';
$action = new action();
$action->record('MODULE_ID_UNITS');

if (isset($_GET['from_search'])) { // if we come from home page search
        header("Location: {$urlServer}modules/search/search_incourse.php?all=true&search_terms=$_GET[from_search]");
}

$res = db_query("SELECT course_keywords, faculte.name AS faculte, type, visible, titulaires,
                        fake_code, course_license
                        FROM cours, faculte
                        WHERE cours_id = $cours_id AND faculte.id = faculteid", $mysqlMainDb);
$result = mysql_fetch_array($res);
$keywords = q(trim($result['course_keywords']));
$faculte = $result['faculte'];
$type = $result['type'];
$visible = $result['visible'];
$professor = $result['titulaires'];
$fake_code = $result['fake_code'];
$main_extra = $description = $addon = '';
$course_license = $result['course_license'];
$res = db_query("SELECT res_id, title, comments FROM unit_resources WHERE unit_id =
                        (SELECT id FROM course_units WHERE course_id = $cours_id AND `order` = -1)
                        AND (visibility = 'v' OR res_id < 0)
                 ORDER BY `order`");
   
if ($res and mysql_num_rows($res) > 0) {
    $main_extra .= "<div class = 'course_description' style='width: 520px;'>";
    $tool_content .= "<div style='display: none'>";
    while ($row = mysql_fetch_array($res)) {
            if ($row['res_id'] == -1) {
                    $description = standard_text_escape($row['comments']);
            } elseif ($row['res_id'] == -2) {
                    $addon = standard_text_escape($row['comments']);
            } else {                
                    if (isset($titreBloc[$row['res_id']])) {
                            $element_id = "class='course_info' id='{$titreBloc[$row['res_id']]}'";                            
                            $icon_url = "$themeimg/bloc/$row[res_id].png";                            
                    } else {                        
                            $element_id = 'class="course_info other"';
                            $icon_url = "$themeimg/bloc/default.png";
                    }                           
                    $hidden_id = "hidden_$row[res_id]";
                    $tool_content .= "<div id='$hidden_id'><h1>" .
                            q($row['title']) . "</h1>" .
                            standard_text_escape($row['comments']) . "</div>\n";
                    $main_extra .= "<div $element_id>" .
                            "<a href='#$hidden_id' class='inline' style='font-weight: bold; width: 100px; display: block; text-align: center; background: url($icon_url) center top no-repeat; padding-top: 80px;'>" .
                            q($row['title']) .
                            "</a></div>\n";
            }
    }
    $main_extra .= "</div>";    
    $tool_content .= "</div>";
}

if ($is_editor) {
        $edit_link = "&nbsp;<a href='../../modules/course_description/editdesc.php?course=$code_cours'>
                <img src='$themeimg/edit.png' title='".q($langEdit)."' alt='".q($langEdit)."' /></a>";
} else {
        $edit_link = '';
}
$main_content .= "<div class='course_info'>";
if (!empty($description)) {
        $main_content .= "<div class='descr_title'>$langDescription$edit_link</div>\n$description";

} else {
        $main_content .= "<p>$langThisCourseDescriptionIsEmpty$edit_link</p>";
}
if (!empty($keywords)) {
	$main_content .= "<p id='keywords'><b>$langCourseKeywords</b> $keywords</p>";
}
$main_content .= "</div>\n";

if (!empty($addon)) {
	$main_content .= "<div class='course_info'><h1>$langCourseAddon</h1><p>$addon</p></div>";
}
        
$main_content .= $main_extra;

units_set_maxorder();

// other actions in course unit
if ($is_editor) {
        // refresh course metadata
        require_once '../../modules/course_metadata/CourseXML.php';
        
        if (isset($_REQUEST['edit_submit'])) {
                $main_content .= handle_unit_info_edit();
        } elseif (isset($_REQUEST['del'])) { // delete course unit
		$id = intval($_REQUEST['del']);
		db_query("DELETE FROM course_units WHERE id = '$id'");
		db_query("DELETE FROM unit_resources WHERE unit_id = '$id'");
                CourseXMLElement::refreshCourse($cours_id, $code_cours);
		$main_content .= "<p class='success_small'>$langCourseUnitDeleted</p>";
	} elseif (isset($_REQUEST['vis'])) { // modify visibility
		$id = intval($_REQUEST['vis']);
		$sql = db_query("SELECT `visibility` FROM course_units WHERE id='$id'");
		list($vis) = mysql_fetch_row($sql);
		$newvis = ($vis == 'v')? 'i': 'v';
		db_query("UPDATE course_units SET visibility = '$newvis' WHERE id = $id AND course_id = $cours_id");
                CourseXMLElement::refreshCourse($cours_id, $code_cours);
	} elseif (isset($_REQUEST['access'])) {
                $id = intval($_REQUEST['access']);
                $sql = db_query("SELECT `public` FROM course_units WHERE id = '$id'");
		list($access) = mysql_fetch_row($sql);
		$newaccess = ($access == '1')? '0': '1';
		db_query("UPDATE course_units SET public = '$newaccess' WHERE id = $id AND course_id = $cours_id");
        }
        elseif (isset($_REQUEST['down'])) {
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
            <p class='descr_title'>$langCourseUnits: <a href='{$urlServer}modules/units/info.php?course=$code_cours'>
            <img src='$themeimg/add.png' width='16' height='16' title='".q($langAddUnit)."' alt='".q($langAddUnit)."' /></a></p>\n";

} else {
        $cunits_content .= "\n  <p class='descr_title'>$langCourseUnits</p>";
}
if ($is_editor) {
        list($last_id) = mysql_fetch_row(db_query("SELECT id FROM course_units
                                                   WHERE course_id = $cours_id AND `order` >= 0
                                                   ORDER BY `order` DESC LIMIT 1", $mysqlMainDb));
	$query = "SELECT id, title, comments, visibility, public
		  FROM course_units WHERE course_id = $cours_id AND `order` >= 0
                  ORDER BY `order`";
} else {
	$query = "SELECT id, title, comments, visibility, public
		  FROM course_units WHERE course_id = $cours_id AND visibility='v' AND `order` >= 0
                  ORDER BY `order`";
}
$sql = db_query($query, $mysqlMainDb);
$first = true;
$count_index = 1;
while ($cu = mysql_fetch_array($sql)) {
        // access status
        $access = $cu['public'];
        // Visibility icon
        $vis = $cu['visibility'];               
        $icon_vis = ($vis == 'v')? 'visible.png': 'invisible.png';
        $class1_vis = ($vis == 'i')? ' class="invisible"': '';
        $class_vis = ($vis == 'i')? 'invisible': '';        
        $cunits_content .= "<table ";
        if ($is_editor) {
            $cunits_content .= "class='tbl'";
        } else {
            $cunits_content .= "class='tbl'";
        }
        $cunits_content .= " width='770'>";
        if ($is_editor) {
                $cunits_content .= "<tr>".
                           "<th width='25' class='right'>$count_index.</th>" .
                           "<th width='580'><a class=\"$class_vis\" href='${urlServer}modules/units/?course=$code_cours&amp;id=$cu[id]'>" . q($cu['title']) . "</a></th>";
        } elseif (resource_access($vis, $access)) {
                $cunits_content .= "<tr>".
                           "<th width='25' class='right'>$count_index.</th>".
                           "<th width='729'><a class=\"$class_vis\" href='${urlServer}modules/units/?course=$code_cours&amp;id=$cu[id]'>" . q($cu['title']) . "</a></th>";
        }

        if ($is_editor) { // display actions
                $cunits_content .= "<th width='80' class='center'>".
                        "<a href='../../modules/units/info.php?course=$code_cours&amp;edit=$cu[id]'>" .
                        "<img src='$themeimg/edit.png' title='".q($langEdit)."' alt='".q($langEdit)."' /></a>" .
                        "<a href='$_SERVER[SCRIPT_NAME]?del=$cu[id]' " .
                        "onClick=\"return confirmation('$langConfirmDelete');\">" .
                        "<img src='$themeimg/delete.png' " .
                        "title='".q($langDelete)."' alt='".q($langDelete)."' /></a>" .
                        "<a href='$_SERVER[SCRIPT_NAME]?vis=$cu[id]'>" .
                        "<img src='$themeimg/$icon_vis' " .
                        "title='".q($langVisibility)."' alt='".q($langVisibility)."' /></a>&nbsp;";
                if ($visible == COURSE_OPEN) { // public accessibility actions                        
                        $icon_access = ($access == 1)? 'access_public.png': 'access_limited.png';                        
                        $cunits_content .= "<a href='$_SERVER[SCRIPT_NAME]?access=$cu[id]'>" .
                                        "<img src='$themeimg/$icon_access' " .
                                        "title='".q($langResourceAccess)."' alt='".q($langResourceAccess)."' /></a>";
                        $cunits_content .= "&nbsp;&nbsp;</th>";
                }
                if ($cu['id'] != $last_id) {
                        $cunits_content .= "<th width='40' class='right'><a href='$_SERVER[SCRIPT_NAME]?down=$cu[id]'>" .
                        "<img src='$themeimg/down.png' title='".q($langDown)."' /></a>";
                } else {
                        $cunits_content .= "<th width='40' class='right'>&nbsp;&nbsp;&nbsp;&nbsp;";
                }
                if (!$first) {
                        $cunits_content .= "<a href='$_SERVER[SCRIPT_NAME]?up=$cu[id]'>
                                <img src='$themeimg/up.png' title='".q($langUp)."' /></a></th>";
                } else {
                        $cunits_content .= "&nbsp;&nbsp;&nbsp;&nbsp;</th>";
                }
        }
        $cunits_content .= "</tr><tr><td ";
        if ($is_editor) {
            $cunits_content .= "colspan='8' $class1_vis>";
        } else {
            $cunits_content .= "colspan='2'>";
        }
        if (resource_access($vis, $access)) {
            $cunits_content .= standard_text_escape($cu['comments']);
            $count_index++;
        } else {
            $cunits_content .= "&nbsp;";
        }
        $cunits_content .= "</td></tr></table>";
        $first = false;        
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
// display course access
$bar_content .= "<li><b>$langConfidentiality</b>: $lessonStatus</li>";
if ($is_course_admin) {
    $link = "<a href='$urlAppend/modules/user/user.php?course=$code_cours'>$numUsers $langRegistered</a>";
} else {
    $link = "$numUsers $langRegistered";
}
$bar_content .= "<li><b>$langUsers</b>: $link</li></ul>";

// display course license
if ($course_license) {
    $license_info_box = "<table class='tbl_courseid' width='200'>
        <tr class='title1'>
            <td class='title1'>${langOpenCoursesLicense}</td></tr>
        <tr><td><div align='center'><small>".copyright_info($cours_id)."</small></div></td></tr>
        </table>
        <br/>";
} else {
    $license_info_box = '';
}

// display opencourses level in bar
require_once '../../modules/course_metadata/CourseXML.php';
$level = CourseXMLElement::getLevel(db_query_get_single_value("SELECT level from `$mysqlMainDb`.course_review WHERE course_id = " . $cours_id));
$opencourses_level = '';
if (isset($level) && !empty($level)) {
    $metadataUrl = $urlServer . 'modules/course_metadata/info.php?course=' . $code_cours;
    $opencourses_level = "<table class='tbl_courseid' width='200'>
        <tr class='title1'>
            <td class='title1'>${langOpenCourseShort}</td>
            <td style='text-align: right; padding-right: 1.2em'><a href='$metadataUrl'>" .
                icon('lom', $langCourseMetadata, $metadataUrl) . "</td></tr>
        <tr><td colspan='2'><div class='center'>" . icon('open_courses_logo_small', $GLOBALS['langOpenCourses']) . "</div>
                <div class='center'><b>${langOpenCoursesLevel}: $level</b></div></td></tr>
        </table>
        <br/>";
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
        (<a href='{$urlServer}main/emailunsubscribe.php?cid=$cours_id'>$langModify</a>)</div>";
} 

$tool_content .= "
<div id='content_course'>

   <table width='100%'>
   <tr>
      <td valign='top'>$main_content</td>";
if (!defined('EXPORTING')) {
        $tool_content .= "
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
        $license_info_box
        $opencourses_level
        <table class='tbl_courseid' width='200'>
        <tr class='title1'>
          <td class='title1'>$langTools</td>
          <td class='left'>$toggle_student_view";
        if (isset($_SESSION['uid'])) {
                $tool_content .= "<a href='../../modules/contact/index.php?course=$code_cours' id='email_btn'><img src='$themeimg/email.png' alt='".q($langContactProf)."' title='".q($langContactProf)."' /></a>&nbsp;&nbsp;";
        }
             $tool_content .= "<a href='$_SERVER[SCRIPT_NAME]' title='" . q($intitule) . "' class='jqbookmark'><img src='$themeimg/bookmark.png' alt='$langAddAsBookmark' title='".q($langAddAsBookmark)."' /></a>&nbsp;&nbsp;";
                if (visible_module(7)) {
                       $tool_content .= "
                        <span class='feed'><a href='${urlServer}modules/announcements/rss.php?c=$currentCourseID'>
                        <img src='$themeimg/feed.png' alt='".q($langRSSFeed)."' title='".q($langRSSFeed)."' /></a></span>&nbsp;$toggle_student_view_close";
                }
        $tool_content .= "</td>
        </tr>
        </table>
        $emailnotification
        <br />
      </td>";
}
$tool_content .= "</tr></table>
   <table width='100%' class='tbl'>
   <tr>
     <td>$cunits_content</td>
   </tr>
  </table>
</div>";

draw($tool_content, 2, null, $head_content);
