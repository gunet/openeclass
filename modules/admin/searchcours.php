<?php

/* ========================================================================
 * Open eClass 3.0
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

/**
 *      @file searchcours.php
 * 	@authors list: Karatzidis Stratos <kstratos@uom.gr>
 * 		       Pitsiougas Vagelis <vagpits@uom.gr>
 *      @brief: search on courses by title, code, type and faculty 	
 */
$require_departmentmanage_user = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

$tree = new Hierarchy();
$user = new User();

load_js('jquery');
load_js('jquery-ui');
load_js('jstree');
load_js('jquery-ui-timepicker-addon.min.js');

$head_content .= "<link rel='stylesheet' type='text/css' href='{$urlAppend}js/jquery-ui-timepicker-addon.min.css'>
<script type='text/javascript'>
$(function() {
$('input[name=date]').datetimepicker({
    dateFormat: 'yy-mm-dd', 
    timeFormat: 'hh:mm'
    });
});
</script>";

$nameTools = $langSearchCourse;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);

$reg_flag = isset($_GET['reg_flag']) ? intval($_GET['reg_flag']) : '';

// search form
$tool_content .= "<form action='listcours.php?search=yes' method='get'>
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

$tool_content .= "<tr><th class='left'>$langCreationDate:</th><td>";
$reg_flag_data = array();
$reg_flag_data[1] = $langAfter;
$reg_flag_data[2] = $langBefore;
$tool_content .= selection($reg_flag_data, 'reg_flag', $reg_flag);

@$tool_content .= "<input type='text' name='date' value='" . $date . "'>";
$tool_content .= "</td></tr>";
$tool_content .= "<tr><th class='left'>" . $langFaculty . ":</th><td>";

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
