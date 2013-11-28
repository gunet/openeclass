<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2013  Greek Universities Network - GUnet
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

/**
 *       @file searchcours.php
 * 	@authors list: Karatzidis Stratos <kstratos@uom.gr>
 * 		       Pitsiougas Vagelis <vagpits@uom.gr>
 *       @brief: This script allows the administrator to perform a search on courses by
 * 	title, code, type and faculte
 */
$require_departmentmanage_user = true;

require_once '../../include/baseTheme.php';
require_once 'include/jscalendar/calendar.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

$tree = new Hierarchy();
$user = new User();

load_js('jquery');
load_js('jquery-ui-new');
load_js('jstree');
$jscalendar = new DHTML_Calendar($urlServer . 'include/jscalendar/', $language, 'calendar-blue2', false);
$head_content .= $jscalendar->get_load_files_code();

$nameTools = $langSearchCourse;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);

// Destroy search variables from session
if (isset($_GET['new']) && ($_GET['new'] == "yes")) {
    unset($_SESSION['searchtitle']);
    unset($_SESSION['searchcode']);
    unset($_SESSION['searchtype']);
    unset($_SESSION['searchfaculte']);
    unset($searchtitle);
    unset($searchcode);
    unset($searchtype);
    unset($searchfaculte);
}

$reg_flag = isset($_GET['reg_flag']) ? intval($_GET['reg_flag']) : '';

// search form
$tool_content .= "<form action='listcours.php?search=yes' method='post'>
    <fieldset>
      <legend>" . $langSearchCriteria . " " . @$newsearch . "</legend>
      <table width='100%' class='tbl'>
      <tr>
        <th class='left' width='150'>$langTitle:</th>
        <td><input type='text' name='formsearchtitle' size='40' value='" . @$searchtitle . "'></td>
      </tr>
      <tr>
        <th class='left'><b>$langCourseCode:</b></th>
        <td><input type='text' name='formsearchcode' size='40' value='" . @$searchcode . "'></td>
      </tr>";

if (isset($_GET['searchcode'])) {
    switch ($searchcode) {
        case "2":
            $typeSel[2] = "selected";
            break;
        case "1":
            $typeSel[1] = "selected";
            break;
        case "0":
            $typeSel[0] = "selected";
            break;
        case "3":
            $typeSel[0] = "selected";
            break;
        default:
            $typeSel[-1] = "selected";
            break;
    }
}

$tool_content .= "<tr><th class='left'><b>$langCourseVis:</b></th>
        <td>
          <select name='formsearchtype'>
           <option value='-1' " . @$typeSel[-1] . ">$langAllTypes</option>
           <option value='2' " . @$typeSel[2] . ">$langTypeOpen</option>
           <option value='1' " . @$typeSel[1] . ">$langTypeRegistration</option>
           <option value='0' " . @$typeSel[0] . ">$langTypeClosed</option>
           <option value='3' " . @$typeSel[3] . ">$langCourseInactiveShort</option>
          </select>
        </td>
      </tr>";

$tool_content .= "<tr><th class='left'>$langRegistrationDate:</th><td>";
$reg_flag_data = array();
$reg_flag_data[1] = $langAfter;
$reg_flag_data[2] = $langBefore;
$tool_content .= selection($reg_flag_data, 'reg_flag', $reg_flag);

$start_cal = $jscalendar->make_input_field(
        array('showOthers' => true,
    'showsTime' => true,
    'align' => 'Tl',
    'ifFormat' => '%Y-%m-%d %H:%M',
    'timeFormat' => '24'), array('style' => 'font-weight: bold; font-size: 10px; width: 10em; color: #727266; background-color: #fbfbfb; border: 1px solid #C0C0C0; text-align: center',
    'name' => 'date',
    'value' => ''));

$tool_content .= $start_cal;
$tool_content .= "</td></tr>";
$tool_content .= "<tr><th class='left'><b>" . $langFaculty . ":</b></th><td>";

if (isDepartmentAdmin())
    list($js, $html) = $tree->buildNodePicker(array('params' => 'name="formsearchfaculte"', 'tree' => array('0' => $langAllFacultes), 'useKey' => "id", 'multiple' => false, 'allowables' => $user->getDepartmentIds($uid)));
else
    list($js, $html) = $tree->buildNodePicker(array('params' => 'name="formsearchfaculte"', 'tree' => array('0' => $langAllFacultes), 'useKey' => "id", 'multiple' => false));

$head_content .= $js;
$tool_content .= $html;
$tool_content .= "</td></tr>";

$tool_content .= "<tr><th>&nbsp;</th>
        <td class='right'><input type='submit' name='search_submit' value='$langSearch'></td>
      </tr>";
$tool_content .= "</table></fieldset></form>";

$tool_content .= "<p align='right'><a href='index.php'>" . $langBack . "</a></p>";

draw($tool_content, 3, null, $head_content);
