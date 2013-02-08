<?php
/* ========================================================================
 * Open eClass 3.0
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
 * Index, Course Description
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This module displays the course description of every course. If the user
 * is the course's professor, he/she is shown of a link to add/edit the contents of
 * the module. Description text is kept in a special course unit with order=-1
 *
 */

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Coursedescription';
$guest_allowed = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';
require_once 'modules/units/functions.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/log.php';
/**** The following is added for statistics purposes ***/
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_DESCRIPTION);
/**************************************/
$nameTools = $langCourseDescription;

$unit_id = description_unit_id($course_id);

ModalBoxHelper::loadModalBox();
if ($is_editor) {
        load_js('tools.js');
	$tool_content .= "
	<div id='operations_container'>
	  <ul id='opslist'>
		<li><a href='edit.php?course=$course_code'>$langEditCourseProgram</a></li>
	  </ul>
	</div>";
        
        process_actions();

        if (isset($_POST['edIdBloc'])) {
                // Save results from block edit (save action)
                $res_id = intval($_POST['edIdBloc']);
                add_unit_resource($unit_id, 'description', $res_id,
                                  autounquote($_POST['edTitleBloc']),
                                  autounquote($_POST['edContentBloc']));
                $id = mysql_insert_id();
                $log_action = ($id > 0)?LOG_INSERT:LOG_MODIFY;                
                Log::record($course_id, MODULE_ID_DESCRIPTION, $log_action, array('id' => $id,
                                                                                 'title' => $_POST['edTitleBloc'],
                                                                                 'content' => $_POST['edContentBloc']));
                if ($res_id == -1) {
                        header("Location: {$urlServer}courses/$course_code");
                        exit;
                }
        }
}

$q = db_query("SELECT id, title, comments, res_id, visible FROM unit_resources WHERE
                        unit_id = $unit_id AND `order` >= 0 ORDER BY `order`");
if ($q and mysql_num_rows($q) > 0) {
        list($max_resource_id) = mysql_fetch_row(db_query("SELECT id FROM unit_resources
                                        WHERE unit_id = $unit_id ORDER BY `order` DESC LIMIT 1"));
	while ($row = mysql_fetch_array($q)) {
                $tool_content .= "
                <table width='100%' class='tbl_border'>
                <tr class='odd'>
                 <td class='bold'>" . q($row['title']) . "</td>\n" .
                 actions('description', $row['id'], $row['visible'], $row['res_id']) . "
                </tr>
                <tr>";
                if ($is_editor) {
                        $tool_content .= "\n<td colspan='6'>" . standard_text_escape($row['comments']) . "</td>";
                } else {
                        $tool_content .= "\n<td>" . standard_text_escape($row['comments']) . "</td>";
                }
                $tool_content .= "</tr></table><br />\n";
	}
} else {
	$tool_content .= "<p class='alert1'>$langThisCourseDescriptionIsEmpty</p>";
}

add_units_navigation(true);
draw($tool_content, 2, null, $head_content);
