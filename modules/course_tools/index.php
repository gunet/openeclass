<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

$require_current_course = true;
$require_help = true;
$helpTopic = 'course_tools';
$require_login = true;

include '../../include/baseTheme.php';
require_once 'include/log.class.php';
require_once 'modules/lti_consumer/lti-functions.php';
require_once 'publish-functions.php';
require_once 'modules/admin/extconfig/ltipublishapp.php';

$up = new Permissions();
if (!$up->has_course_modules_permission()) {
    Session::Messages($langCheckCourseAdmin, 'alert-danger');
    redirect_to_home_page('courses/'. $course_code);
}

$toolName = $langToolManagement;
add_units_navigation(TRUE);
load_js('tools.js');
load_js('trunk8');

$page_url = "modules/course_tools/index.php?course=$course_code";
$data['post_url'] = $urlAppend . $page_url;

$table_modules = '';
if ($is_collaborative_course ?? false) {
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
if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    $link = $_POST['link'] ?? '';
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
} elseif (isset($_GET['add'])) { // add external link
    $pageName = $langAddExtLink;
    $navigation[] = array('url' => $page_url, 'name' => $langToolManagement);
    view('modules.course_tools.external_link_store', $data);
} elseif (isset($_GET['show_lti_template'])) {
    $pageName = $langTurnitinConfDetails;
    $navigation[] = array('url' => "../course_tools/index.php?course=$course_code", 'name' => $langToolManagement);
    $appId = getDirectReference($_GET['show_lti_template']);
    $lti = Database::get()->querySingle("SELECT * FROM lti_apps WHERE id = ?d ", $appId);
    $data['lti'] = $lti;
    $data['action_bar'] = action_bar(array(
        array('title' => $langBack,
            'url' => "../course_tools/index.php?course=$course_code",
            'icon' => 'fa-reply',
            'level' => 'primary')
    ));
    view('modules.course_tools.show_lti_template', $data);
} else {

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
        $data['toolSelection'][$item->visible][] = (object) array('id' => $mid, 'title' => $modules[$item->module_id]['title']);
    }

    $data['q'] = Database::get()->queryArray("SELECT id, url, title FROM link
                            WHERE category = -1 AND
                            course_id = ?d", $course_id);

    // check if LTI Provider is enabled (global config) and available for the current course
    $ltipublishapp = ExtAppManager::getApp('ltipublish');
    $data['ltiPublishIsEnabledForCurrentCourse'] = $ltipublishapp->isEnabledForCurrentCourse();

    $data['lti_apps'] = array_map(function ($app) {
        global $course_code, $is_editor, $urlAppend;

        $templateVisible = isset($app->course_visible) ? intval($app->course_visible) : 1;
        $isTemplatePanopto = !empty($app->is_template_panopto);
        $app->is_active_for_course = ($app->enabled == 1) && (!$isTemplatePanopto || $templateVisible == 1);

        $indirect_id = getIndirectReference($app->id);
        if ($is_editor) {
            $app->editUrl = "{$urlAppend}modules/lti_consumer/index.php?course=$course_code&amp;id=$indirect_id&amp;choice=edit";
            $app->enableUrl = "{$urlAppend}modules/lti_consumer/index.php?id=$indirect_id&amp;choice=do_" . ($app->enabled ? 'disable' : 'enable');
            $app->deleteUrl = "{$urlAppend}modules/lti_consumer/index.php?id=$indirect_id&amp;choice=do_delete";
            // toggle enable/disable
            $toggleChoice = $templateVisible ? 'do_template_disable' : 'do_template_enable';
            $app->templateEnableUrl = "{$urlAppend}modules/lti_consumer/index.php?id=$indirect_id&amp;choice=$toggleChoice";
        }
        if (!isset($app->description)) {
            $app->description = '';
        }
        $app->canJoin = $app->enabled || $is_editor;
        if ($app->canJoin) {
            if ($app->launchcontainer == LTI_LAUNCHCONTAINER_EMBED) {
                $app->joinLink = create_launch_button($app->id);
            } else {
                $app->joinLink = create_join_button(
                    $app->lti_provider_url,
                    $app->lti_provider_key,
                    $app->lti_provider_secret,
                    $app->id,
                    "lti_tool",
                    $app->title,
                    $app->description,
                    $app->launchcontainer);
            }
        } else {
            $app->joinLink = q($app->title);
        }
        return $app;
    }, fetch_lti_apps());

    $data['lti_providers'] = lti_provider_details();

    view('modules.course_tools.index', $data);
}
