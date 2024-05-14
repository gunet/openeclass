<?php

/* ========================================================================
 * Open eClass 4.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2019  Greek Universities Network - GUnet
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
$helpTopic = 'course_tools';
$require_login = true;

include '../../include/baseTheme.php';
require_once 'include/log.class.php';
require_once 'modules/lti_consumer/lti-functions.php';
require_once 'publish-functions.php';
require_once 'modules/admin/extconfig/ltipublishapp.php';

$toolName = $langToolManagement;
add_units_navigation(TRUE);

load_js('tools.js');
$page_url = 'modules/course_tools/index.php?course=' . $course_code;

$table_modules = '';
if(isset($is_collaborative_course) and $is_collaborative_course){
    $table_modules = 'module_disable_collaboration';
}else{
    $table_modules = 'module_disable';
}

if (isset($_REQUEST['toolStatus'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

    $old = Database::get()->queryArray('SELECT module_id FROM course_module
        WHERE visible = 1 AND course_id = ?d', $course_id);
    $old = array_map(function ($module) {
        return $module->module_id;
    }, $old);

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

    $log = [];
    $added = array_diff($mids, $old);
    $removed = array_diff($old, $mids);
    if ($added) {
        $log['activate'] = $added;
    }
    if ($removed) {
        $log['deactivate'] = $removed;
    }
    if ($log) {
        Log::record($course_id, MODULE_ID_TOOLADMIN, LOG_MODIFY, $log);
    }
    Session::flash('message',$langRegDone);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page($page_url);
}

if (isset($_GET['delete'])) {
    $delete = getDirectReference($_GET['delete']);
    $r = Database::get()->querySingle("SELECT url, title, category FROM link WHERE id = ?d", $delete);
    Database::get()->query("DELETE FROM link WHERE id = ?d", $delete);
    Log::record($course_id, MODULE_ID_TOOLADMIN, LOG_DELETE, array('id' => $delete,
                                                                   'link' => $r->url,
                                                                   'name_link' => $r->title));
    Session::flash('message',$langLinkDeleted);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page($page_url);
}

/**
 * Add external link
 */
$data['csrf'] = generate_csrf_token_form_field();

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $link = isset($_POST['link']) ? $_POST['link'] : '';
    $name_link = isset($_POST['name_link']) ? $_POST['name_link'] : '';
    if ((trim($link) == 'http://') or ( trim($link) == 'ftp://') or empty($link) or empty($name_link) or ! is_url_accepted($link)) {
        Session::flash('message',$langInvalidLink);
        Session::flash('alert-class', 'alert-danger');
        redirect_to_home_page($page_url);
    }

    $sql = Database::get()->query("INSERT INTO link (course_id, url, title, category, description)
                            VALUES (?d, ?s, ?s, -1, ' ')", $course_id, $link, $name_link);
    $id = $sql->lastInsertID;
    Log::record($course_id, MODULE_ID_TOOLADMIN, LOG_INSERT, array('id' => $id,
                                                                   'link' => $link,
                                                                   'name_link' => $name_link));
    Session::flash('message',$langLinkAdded);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page($page_url);
} elseif (isset($_GET['action'])) { // add external link
    $pageName = $langAddExtLink;
    $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langToolManagement);

    $data['action_bar'] = action_bar(array(
            array('title' => $langBack,
                  'url' => "index.php?course=$course_code",
                  'icon' => 'fa-reply',
                  'level' => 'primary'
                 )));

    view('modules.course_tools.external_link_store', $data);
}elseif(!isset($_GET['action'])){

$data['toolSelection'][0] = $data['toolSelection'][1] = array();
$module_list = Database::get()->queryArray('SELECT module_id, visible
                                FROM course_module WHERE course_id = ?d
                                AND module_id NOT IN (SELECT module_id FROM '.$table_modules.')', $course_id);

foreach ($module_list as $item) {
    if ($item->module_id == MODULE_ID_TC and count(get_enabled_tc_services()) == 0) {
        // hide teleconference when no tc servers are enabled
        continue;
    }
    if (!isset($modules[$item->module_id]['title'])) {
        // hide deprecated modules with no title
        continue;
    }
    $mid = getIndirectReference($item->module_id);
    $mtitle = q($modules[$item->module_id]['title']);
    $data['toolSelection'][$item->visible][] = (object) array('id' => $mid, 'title' => $mtitle);
}

$data['q'] = Database::get()->queryArray("SELECT id, url, title FROM link
                        WHERE category = -1 AND
                        course_id = ?d", $course_id);

// check if LTI Provider is enabled (global config) and available for the current course
$ltipublishapp = ExtAppManager::getApp('ltipublish');
$data['ltiPublishIsEnabledForCurrentCourse'] = $ltipublishapp->isEnabledForCurrentCourse();

view('modules.course_tools.index', $data);
}
