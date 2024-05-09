<?php

/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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


// Disable modules admin page

$require_admin = true;
require_once '../../include/baseTheme.php';

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$pageName = $langDisableModules;

$table_modules = '';
if((isset($collaboration_platform) and !$collaboration_platform) or is_null($collaboration_platform)){
    $table_modules = 'module_disable';
}else{
    $table_modules = 'module_disable_collaboration';
}

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();
    if((isset($collaboration_platform) and !$collaboration_platform) or is_null($collaboration_platform)){
        Database::get()->query('DELETE FROM '.$table_modules.'');
    }else{
        $alwaysdisabledCollaborationModules = array(MODULE_ID_ASSIGN, MODULE_ID_ATTENDANCE, MODULE_ID_GRADEBOOK, MODULE_ID_MINDMAP, MODULE_ID_PROGRESS,
                                                MODULE_ID_LP,MODULE_ID_EXERCISE,MODULE_ID_GLOSSARY,MODULE_ID_EBOOK,MODULE_ID_WIKI,MODULE_ID_ABUSE_REPORT,MODULE_ID_COURSEPREREQUISITE,MODULE_ID_LTI_CONSUMER,
                                                MODULE_ID_ANALYTICS,MODULE_ID_H5P,MODULE_ID_COURSE_WIDGETS);
        $always_disabled_m = array();
        foreach($alwaysdisabledCollaborationModules as $m){
            $always_disabled_m[] = $m;
        }
        $values = implode(',', $always_disabled_m);
        Database::get()->query('DELETE FROM '.$table_modules.' WHERE module_id NOT IN ('.$values.')');
    }
    if (isset($_POST['moduleDisable'])) {
        $optArray = implode(', ', array_fill(0, count($_POST['moduleDisable']), '(?d)'));
        Database::get()->query('INSERT INTO '.$table_modules.' (module_id) VALUES ' . $optArray,
            array_keys($_POST['moduleDisable']));
    }
    //Session::Messages($langWikiEditionSucceed, 'alert-success');
    Session::flash('message',$langWikiEditionSucceed);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page('modules/admin/modules.php');
} else {
    $data['disabled'] = [];
    foreach (Database::get()->queryArray('SELECT module_id FROM '.$table_modules.'') as $item) {
        $data['disabled'][] = $item->module_id;
    }
    $data['action_bar'] = action_bar(
                        [
                            [ 'title' => $langBack,
                              'url' => $urlAppend . 'modules/admin/index.php',
                              'icon' => 'fa-reply',
                              'level' => 'primary' ],
                            [ 'title' => $langDefaultModules,
                              'url' => $urlAppend . 'modules/admin/modules_default.php',
                              'icon' => 'fa-square-check',
                              'level' => 'primary-label' ]

                        ], false);

    $alwaysEnabledModules = array(MODULE_ID_AGENDA, MODULE_ID_DOCS, MODULE_ID_ANNOUNCE, MODULE_ID_MESSAGE, MODULE_ID_DESCRIPTION);
    foreach ($alwaysEnabledModules as $alwaysEnabledModule) {
        unset($modules[$alwaysEnabledModule]);
    }
    if((isset($collaboration_platform) and $collaboration_platform)){
        $alwaysdisabledCollaborationModules = array(MODULE_ID_ASSIGN, MODULE_ID_ATTENDANCE, MODULE_ID_GRADEBOOK, MODULE_ID_MINDMAP, MODULE_ID_PROGRESS,
                                                MODULE_ID_LP,MODULE_ID_EXERCISE,MODULE_ID_GLOSSARY,MODULE_ID_EBOOK,MODULE_ID_WIKI,MODULE_ID_ABUSE_REPORT,MODULE_ID_COURSEPREREQUISITE,MODULE_ID_LTI_CONSUMER,
                                                MODULE_ID_ANALYTICS,MODULE_ID_H5P,MODULE_ID_COURSE_WIDGETS);
        foreach($alwaysdisabledCollaborationModules as $disabledCollaborationModule){
            unset($modules[$disabledCollaborationModule]);
        }
    }
    $data['modules'] = $modules;
}

view('admin.other.modules', $data);
