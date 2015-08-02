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

$require_current_course = true;
$require_course_admin = true;
$require_help = true;
$helpTopic = 'courseTools';
$require_login = true;

include '../../include/baseTheme.php';
require_once 'include/log.php';

$toolName = $langToolManagement;
add_units_navigation(TRUE);

load_js('tools.js');
$head_content .= '<script type="text/javascript">var langEmptyGroupName = "' .
        $langNoPgTitle . '";</script>';

if (isset($_GET['action'])) {
    $action = intval($_GET['action']);
}

if (isset($_REQUEST['toolStatus'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    if (isset($_POST['toolStatActive'])) {
        $tool_stat_active = $_POST['toolStatActive'];
    }
    if (isset($tool_stat_active)) {
        $loopCount = count($tool_stat_active);
    } else {
        $loopCount = 0;
    }
    $i = 0;
    $publicTools = array();
    $tool_id = null;
    while ($i < $loopCount) {
        if (!isset($tool_id)) {
            $tool_id = " (`module_id` = " . intval(getDirectReference($tool_stat_active[$i])) . ")";
        } else {
            $tool_id .= " OR (`module_id` = " . intval(getDirectReference($tool_stat_active[$i])) . ")";
        }
        $i++;
    }
    //reset all tools
    Database::get()->query("UPDATE course_module SET visible = 0
                         WHERE course_id = ?d", $course_id);
    //and activate the ones the professor wants active, if any
    if ($loopCount > 0) {
        Database::get()->query("UPDATE course_module SET visible = 1
                                 WHERE $tool_id AND
                                 course_id = ?d", $course_id);
    }
}

if (isset($_GET['delete'])) {
    $delete = getDirectReference($_GET['delete']);
    $r = Database::get()->querySingle("SELECT url, title, category FROM link WHERE id = ?d", $delete);    
    Database::get()->query("DELETE FROM link WHERE id = ?d", $delete);
    Log::record($course_id, MODULE_ID_TOOLADMIN, LOG_DELETE, array('id' => $delete,
                                                                   'link' => $r->url,
                                                                   'name_link' => $r->title));
    $tool_content .= "<div class='alert alert-success'>$langLinkDeleted</div>";
}

/**
 * Add external link
 */
if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $link = isset($_POST['link']) ? $_POST['link'] : '';
    $name_link = isset($_POST['name_link']) ? $_POST['name_link'] : '';
    if ((trim($link) == 'http://') or ( trim($link) == 'ftp://') or empty($link) or empty($name_link) or ! is_url_accepted($link)) {
        $tool_content .= "<div class='alert alert-danger'>$langInvalidLink</div>" .
                action_bar(array(
                    array('title' => $langBack,
                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=2",
                        'icon' => 'fa-reply',
                        'level' => 'primary-label')));
        draw($tool_content, 2, null, $head_content);
        exit();
    }

    $sql = Database::get()->query("INSERT INTO link (course_id, url, title, category, description)
                            VALUES (?d, ?s, ?s, -1, ' ')", $course_id, $link, $name_link);
    $id = $sql->lastInsertID;
    $tool_content .= "<div class='alert alert-success'>$langLinkAdded</div>";
    Log::record($course_id, MODULE_ID_TOOLADMIN, LOG_INSERT, array('id' => $id,
                                                                   'link' => $link,
                                                                   'name_link' => $name_link));
} elseif (isset($_GET['action'])) { // add external link
    $pageName = $langAddExtLink;
    $tool_content .= action_bar(array(
            array('title' => $langBack,
                  'url' => "index.php?course=$course_code",
                  'icon' => 'fa-reply',
                  'level' => 'primary-label'
                 )));
        
    $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langToolManagement);
    $helpTopic = 'Module';
    $tool_content .= "<div class='form-wrapper'>
            <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=true'>
            <fieldset>            
            <div class='form-group'>
                <label for='link' class='col-sm-2 control-label'>$langLink:</label>
                <div class='col-sm-10'>
                    <input id='link' class='form-control' type='text' name='link' size='50' value='http://'>
                </div>
            </div>
            <div class='form-group'>
                <label for-'name_link' class='col-sm-2 control-label'>$langLinkName:</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='text' name='name_link' size='50'>
                </div>              
            </div>
            <div class='form-group'>
            <div class='col-sm-offset-2 col-sm-10'>
              <input class='btn btn-primary' type='submit' name='submit' value='$langAdd'>
            </div>  
            </div>
            </fieldset>
            ". generate_csrf_token_form_field() ." 
            </form>
          </div>";
    draw($tool_content, 2, null, $head_content);
    exit();
}

$toolArr = getSideMenu(2);

if (is_array($toolArr)) {
    for ($i = 0; $i <= 1; $i++) {
        $toolSelection[$i] = '';
        $numOfTools = count($toolArr[$i][1]);
        for ($j = 0; $j < $numOfTools; $j++) {
            $toolSelection[$i] .= "<option value='" . getIndirectReference($toolArr[$i][4][$j]) . "'>" .
                    $toolArr[$i][1][$j] . "</option>\n";
        }
    }
}

$tool_content .= "
<div id='operations_container'>" .
        action_bar(array(
            array('title' => $langAddExtLink,
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=true",
                'icon' => 'fa-plus-circle',
                'level' => 'primary-label',
                'button-class' => 'btn-success'))) .
        "</div>";

$tool_content .= <<<tForm
<div class='form-wrapper'>
    <form name="courseTools" action="$_SERVER[SCRIPT_NAME]?course=$course_code" method="post" enctype="multipart/form-data">
        <div class="table-responsive">    
            <table class="table-default">
                <tr>
                    <th width="45%" class="text-center">$langInactiveTools</th>
                    <th width="10%" class="text-center">$langMove</th>
                    <th width="45%" class="text-center">$langActiveTools</th>
                </tr>
                <tr>
                    <td class="text-center">
                        <select class="form-control" name="toolStatInactive[]" id='inactive_box' size='17' multiple>$toolSelection[1]</select>
                    </td>
                    <td class="text-center">
                        <input class="btn btn-primary" type="button" onClick="move('inactive_box','active_box')" value="   >>   " /><br><br>
                        <input class="btn btn-primary" type="button" onClick="move('active_box','inactive_box')" value="   <<   " />
                    </td>
                    <td class="text-center">
                        <select class="form-control" name="toolStatActive[]" id='active_box' size='17' multiple>$toolSelection[0]</select>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="text-center">
                        <input type="submit" class="btn btn-primary" value="$langSubmitChanges" name="toolStatus" onClick="selectAll('active_box',true)" />
                    </td>
                </tr>
            </table>
        </div>
tForm
.generate_csrf_token_form_field() .<<<tForm
    </form>
</div>
tForm;
// ------------------------------------------------
// display table to edit/delete external links
// ------------------------------------------------

$tool_content .= "<table class='table-default'>
<tr><th colspan='2'>$langOperations</th></tr>
<tr>  
  <th width='90%' class='text-left'>$langTitle</th>
  <th class='text-center'>".icon('fa-gears')."</th>
</tr>";
$q = Database::get()->queryArray("SELECT id, title FROM link
                        WHERE category = -1 AND
                        course_id = ?d", $course_id);
foreach ($q as $externalLinks) {
    $tool_content .= "<td class='text-left'>" . q($externalLinks->title) . "</td>";
    $tool_content .= "<td class='text-center'>";
    $tool_content .= action_button(array(
                array('title' => $langDelete,
                      'url' => "?course=$course_code&amp;delete=".getIndirectReference($externalLinks->id),
                      'icon' => 'fa-times',
                      'class' => 'delete',
                      'confirm' => $langConfirmDelete)
            ));
    $tool_content .= "</td></tr>";
}
$tool_content .= "</table>";

draw($tool_content, 2, null, $head_content);
