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
require_once 'include/log.class.php';

$toolName = $langToolManagement;
add_units_navigation(TRUE);

load_js('tools.js');
$head_content .= '<script type="text/javascript">var langEmptyGroupName = "' .
        $langNoPgTitle . '";</script>';

$page_url = 'modules/course_tools/?course=' . $course_code;

if (isset($_REQUEST['toolStatus'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

    // deactivate all modules
    Database::get()->query("UPDATE course_module SET visible = 0
                         WHERE course_id = ?d", $course_id);

    // activate modules set in request
    if (isset($_POST['toolStatActive'])) {
        foreach ($_POST['toolStatActive'] as $mid_ref) {
            $mids[] = getDirectReference($mid_ref);
        }
        $placeholders = join(', ', array_fill(0, count($mids), '?d'));
        Database::get()->query("UPDATE course_module SET visible = 1
                                    WHERE course_id = ?d AND module_id IN ($placeholders)",
                               $course_id, $mids);
    }
    Log::record($course_id, MODULE_ID_TOOLADMIN, LOG_MODIFY, array());
    Session::Messages($langRegDone, 'alert-success');
    redirect_to_home_page($page_url);
}

if (isset($_GET['delete'])) {
    $delete = getDirectReference($_GET['delete']);
    $r = Database::get()->querySingle("SELECT url, title, category FROM link WHERE id = ?d", $delete);    
    Database::get()->query("DELETE FROM link WHERE id = ?d", $delete);
    Log::record($course_id, MODULE_ID_TOOLADMIN, LOG_DELETE, array('id' => $delete,
                                                                   'link' => $r->url,
                                                                   'name_link' => $r->title));
    Session::Messages($langLinkDeleted, 'alert-success');
    redirect_to_home_page($page_url);
}

/**
 * Add external link
 */
if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $link = isset($_POST['link']) ? $_POST['link'] : '';
    $name_link = isset($_POST['name_link']) ? $_POST['name_link'] : '';
    if ((trim($link) == 'http://') or ( trim($link) == 'ftp://') or empty($link) or empty($name_link) or ! is_url_accepted($link)) {
        Session::Messages($langInvalidLink, 'alert-danger');
        redirect_to_home_page($page_url);
    }

    $sql = Database::get()->query("INSERT INTO link (course_id, url, title, category, description)
                            VALUES (?d, ?s, ?s, -1, ' ')", $course_id, $link, $name_link);
    $id = $sql->lastInsertID;
    Log::record($course_id, MODULE_ID_TOOLADMIN, LOG_INSERT, array('id' => $id,
                                                                   'link' => $link,
                                                                   'name_link' => $name_link));
    Session::Messages($langLinkAdded, 'alert-success');
    redirect_to_home_page($page_url);
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

$toolSelection[0] = $toolSelection[1] = '';
$module_list = Database::get()->queryArray('SELECT module_id, visible
                                FROM course_module WHERE course_id = ?d', $course_id);

foreach ($module_list as $item) {
    if ($item->module_id == MODULE_ID_TC and !is_configured_tc_server()) { // hide teleconference when no tc servers are enabled
        continue;
    } 
    $mid = getIndirectReference($item->module_id);
    $mtitle = q($modules[$item->module_id]['title']);
    $toolSelection[$item->visible] .= "<option value='$mid'>$mtitle</option>";    
}


$tool_content .= <<<tForm
<div class="panel panel-default panel-action-btn-default">
                    <div class='panel-heading list-header'>
                        <h3 class='panel-title'>$langActivateCourseTools</h3>
                    </div>
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
                        <select class="form-control" name="toolStatInactive[]" id='inactive_box' size='17' multiple>$toolSelection[0]</select>
                    </td>
                    <td class="text-center" style="vertical-align: middle;">
                        <button type="button" class="btn btn-default" onClick="move('inactive_box','active_box')"><span class="fa fa-arrow-right"></span></button><br><br>
                        <button type="button" class="btn btn-default" onClick="move('active_box','inactive_box')"><span class="fa fa-arrow-left"></span></button>
                    </td>
                    <td class="text-center">
                        <select class="form-control" name="toolStatActive[]" id='active_box' size='17' multiple>$toolSelection[1]</select>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="text-center">
                        <input type="submit" class="btn btn-primary" value="$langSubmit" name="toolStatus" onClick="selectAll('active_box',true)" />
                    </td>
                </tr>
            </table>
        </div>
tForm
.generate_csrf_token_form_field() .<<<tForm
    </form>
</div>
tForm;


// display table to edit/delete external links
$tool_content .= "<div class='panel panel-default panel-action-btn-default'>
    <div class='pull-right' style='padding:8px;'>
        <div id='operations_container'>
            <a class='btn btn-success' href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=true'><span class='fa fa-plus-circle'></span> $langAddExtLink</a></div>
        </div>
                    <div class='panel-heading list-header'>
                        <h3 class='panel-title'>$langOperations</h3>
                    </div><table class='table-default'>";
$q = Database::get()->queryArray("SELECT id, url, title FROM link
                        WHERE category = -1 AND
                        course_id = ?d", $course_id);
foreach ($q as $externalLinks) {
    $tool_content .= "
        <td class='text-left'>
            <div style='display:inline-block; width: 80%;'>
                <strong>" . q($externalLinks->title) . "</strong>
                <div style='padding-top:8px;'><small class='text-muted'>".q($externalLinks->url)."</small></div>
            </div>
            <div class='pull-right' style='font-size: 20px; padding-right: 20px'>
                <a class='text-danger' href='?course=$course_code&amp;delete=".getIndirectReference($externalLinks->id)."'><span class='fa fa-times'></span></a>
            </div>
    ";
    $tool_content .= "</td></tr>";
}
$tool_content .= "</table></div>";

draw($tool_content, 2, null, $head_content);
