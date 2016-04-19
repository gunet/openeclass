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
 * @file searchcours.php
 * @brief search on courses by title, code, type and faculty
 */

$require_departmentmanage_user = true;

require_once '../../include/baseTheme.php';
require_once 'include/lib/hierarchy.class.php';
require_once 'include/lib/user.class.php';
require_once 'hierarchy_validations.php';

$tree = new Hierarchy();
$user = new User();

load_js('jstree3');
load_js('bootstrap-datetimepicker');

$head_content .= "<script type='text/javascript'>
        $(function() {
            $('#id_date').datetimepicker({
                format: 'dd-mm-yyyy hh:ii', 
                pickerPosition: 'bottom-right', 
                language: '" . $language . "',
                autoclose: true    
            });
        });
    </script>";

$toolName = $langSearchCourse;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);

$tool_content .= action_bar(array(
    array('title' => $langAllCourses,
        'url' => "listcours.php",
        'icon' => 'fa-search',
        'level' => 'primary-label'),
    array('title' => $langBack,
        'url' => "index.php",
        'icon' => 'fa-reply',
        'level' => 'primary-label')));

$reg_flag = isset($_GET['reg_flag']) ? intval($_GET['reg_flag']) : '';
$date = '';
// search form
$tool_content .= "<div class='form-wrapper'>
    <form role='form' class='form-horizontal' action='listcours.php?search=yes' method='get'>
    <fieldset>      
      <div class='form-group'>
      <label for='formsearchtitle' class='col-sm-2 control-label'>$langTitle:</label>
        <div class='col-sm-10'><input type='text' class='form-control' id='formsearchtitle' name='formsearchtitle' value='" . @$searchtitle . "'></div>
      </div>
      <div class='form-group'>
        <label for='formsearchcode' class='col-sm-2 control-label'>$langCourseCode:</label>
        <div class='col-sm-10'>
            <input type='text' class='form-control' name='formsearchcode' value='" . @$searchcode . "'>           
        </div>
      </div>";

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

$tool_content .= "<div class='form-group'>
        <label for='formsearchtype' class='col-sm-2 control-label'>$langCourseVis:</label>
        <div class='col-sm-10'>
          <select class='form-control' name='formsearchtype'>
           <option value='-1' " . @$typeSel[-1] . ">$langAllTypes</option>
           <option value='2' " . @$typeSel[2] . ">$langTypeOpen</option>
           <option value='1' " . @$typeSel[1] . ">$langTypeRegistration</option>
           <option value='0' " . @$typeSel[0] . ">$langTypeClosed</option>
           <option value='3' " . @$typeSel[3] . ">$langCourseInactiveShort</option>
          </select>
        </div>
      </div>";

$reg_flag_data = array();
$reg_flag_data[1] = $langAfter;
$reg_flag_data[2] = $langBefore;
$tool_content .= "<div class='form-group'><label class='col-sm-2 control-label'>$langCreationDate:</label>";        
$tool_content .= "<div class='col-sm-5'>".selection($reg_flag_data, 'reg_flag', $reg_flag, 'class="form-control"')."</div>";
$tool_content .= "<div class='col-sm-5'>";
$tool_content .= "<input class='form-control' id='id_date' name='date' type='text' value='$date' data-date-format='dd-mm-yyyy' placeholder='$langCreationDate'>                    
                </div>";
$tool_content .= "</div>";
$tool_content .= "<div class='form-group'><label class='col-sm-2 control-label'>$langFaculty:</label>";
$tool_content .= "<div class='col-sm-10'>";
if (isDepartmentAdmin()) {
    list($js, $html) = $tree->buildNodePickerIndirect(array('params' => 'name="formsearchfaculte"', 'tree' => array('0' => $langAllFacultes), 'multiple' => false, 'allowables' => $user->getDepartmentIds($uid)));
} else {
    list($js, $html) = $tree->buildNodePickerIndirect(array('params' => 'name="formsearchfaculte"', 'tree' => array('0' => $langAllFacultes), 'multiple' => false));
}

$head_content .= $js;
$tool_content .= $html;
$tool_content .= "</div></div>";
$tool_content .= "<div class='form-group'>
                    <div class='col-sm-10 col-sm-offset-2'>
                        <input class='btn btn-primary' type='submit' name='search_submit' value='$langSearch'>
                        <a href='index.php' class='btn btn-default'>$langCancel</a>        
                    </div>
      </div>";
$tool_content .= "</fieldset></form></div>";

draw($tool_content, 3, null, $head_content);
