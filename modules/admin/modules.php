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


// Modules for main platform
if (isset($_POST['submit'])) {

    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

    if(!get_config('show_always_collaboration')){
        Database::get()->query('DELETE FROM module_disable');
        if (isset($_POST['moduleDisable'])) {
            $optArray = implode(', ', array_fill(0, count($_POST['moduleDisable']), '(?d)'));
            Database::get()->query('INSERT INTO module_disable (module_id) VALUES ' . $optArray,
                array_keys($_POST['moduleDisable']));
        }
    }

    if(get_config('show_collaboration')){
        $alwaysdisabledCollaborationModules = array(MODULE_ID_ASSIGN, MODULE_ID_ATTENDANCE, MODULE_ID_GRADEBOOK, MODULE_ID_MINDMAP, 
                                                    MODULE_ID_PROGRESS,MODULE_ID_LP,MODULE_ID_EXERCISE,MODULE_ID_GLOSSARY,MODULE_ID_EBOOK,
                                                    MODULE_ID_WIKI,MODULE_ID_ABUSE_REPORT,MODULE_ID_COURSEPREREQUISITE,MODULE_ID_LTI_CONSUMER,
                                                    MODULE_ID_ANALYTICS,MODULE_ID_H5P,MODULE_ID_COURSE_WIDGETS);

        $always_disabled_m = array();
        foreach($alwaysdisabledCollaborationModules as $m){
            $always_disabled_m[] = $m;
        }
        $values = implode(',', $always_disabled_m);
        Database::get()->query('DELETE FROM module_disable_collaboration WHERE module_id NOT IN ('.$values.')');

        if (isset($_POST['moduleDisableCollaboration'])) {  
            $optArrayCollab = implode(', ', array_fill(0, count($_POST['moduleDisableCollaboration']), '(?d)'));
            Database::get()->query('INSERT INTO module_disable_collaboration (module_id) VALUES ' . $optArrayCollab, array_keys($_POST['moduleDisableCollaboration']));
        }
    }


    Session::flash('message',$langWikiEditionSucceed);
    Session::flash('alert-class', 'alert-success');
    redirect_to_home_page('modules/admin/modules.php');

} else {

    if(!get_config('show_always_collaboration')){
        $data['disabled'] = [];
        foreach (Database::get()->queryArray('SELECT module_id FROM module_disable') as $item) {
            $data['disabled'][] = $item->module_id;
        }
    }

    if(get_config('show_collaboration')){
        $data['disabledCollaboration'] = [];
        foreach (Database::get()->queryArray('SELECT module_id FROM module_disable_collaboration') as $item) {
            $data['disabledCollaboration'][] = $item->module_id;
        }
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

    if(!get_config('show_always_collaboration')){
        $alwaysEnabledModules = array(MODULE_ID_AGENDA, MODULE_ID_DOCS, MODULE_ID_ANNOUNCE, MODULE_ID_MESSAGE);
        foreach ($alwaysEnabledModules as $alwaysEnabledModule) {
            unset($modules[$alwaysEnabledModule]);
        }
        $data['modules'] = $modules;
    }

    if(get_config('show_collaboration')){

        $modules_collaborations = array(
            MODULE_ID_AGENDA => array('title' => $langAgenda, 'link' => 'agenda', 'image' => 'fa-regular fa-calendar'),
            MODULE_ID_LINKS => array('title' => $langLinks, 'link' => 'link', 'image' => 'fa-solid fa-link'),
            MODULE_ID_DOCS => array('title' => $langDoc, 'link' => 'document', 'image' => 'fa-regular fa-folder'),
            MODULE_ID_VIDEO => array('title' => $langVideo, 'link' => 'video', 'image' => 'fa-solid fa-film'),
            MODULE_ID_ANNOUNCE => array('title' => $langAnnouncements, 'link' => 'announcements', 'image' => 'fa-regular fa-bell'),
            MODULE_ID_FORUM => array('title' => $langForums, 'link' => 'forum', 'image' => 'fa-regular fa-comment'),
            MODULE_ID_GROUPS => array('title' => $langGroups, 'link' => 'group', 'image' => 'fa-solid fa-user-group'),
            MODULE_ID_MESSAGE => array('title' => $langDropBox, 'link' => 'message', 'image' => 'fa-regular fa-envelope'),
            MODULE_ID_CHAT => array('title' => $langChat, 'link' => 'chat', 'image' => 'fa-regular fa-comment-dots'),
            MODULE_ID_QUESTIONNAIRE => array('title' => $langQuestionnaire, 'link' => 'questionnaire', 'image' => 'fa-solid fa-question'),
            MODULE_ID_WALL => array('title' => $langWall, 'link' => 'wall', 'image' => 'fa-solid fa-quote-left'),
            MODULE_ID_TC => array('title' => $langBBB, 'link' => 'tc', 'image' => 'fa-solid fa-users-rectangle'),
            MODULE_ID_REQUEST => array('title' => $langRequests, 'link' => 'request', 'image' => 'fa-regular fa-clipboard')
        );


        $alwaysEnabledModulesCollaborations = array(MODULE_ID_AGENDA, MODULE_ID_DOCS, MODULE_ID_ANNOUNCE, MODULE_ID_MESSAGE, MODULE_ID_DESCRIPTION);
        foreach ($alwaysEnabledModulesCollaborations as $alwaysEnabledModuleCollaboration) {
            unset($modules_collaborations[$alwaysEnabledModuleCollaboration]);
        }

        $alwaysdisabledCollaborationModules = array(MODULE_ID_ASSIGN, MODULE_ID_ATTENDANCE, MODULE_ID_GRADEBOOK, MODULE_ID_MINDMAP, 
                                                    MODULE_ID_PROGRESS,MODULE_ID_LP,MODULE_ID_EXERCISE,MODULE_ID_GLOSSARY,MODULE_ID_EBOOK,
                                                    MODULE_ID_WIKI,MODULE_ID_ABUSE_REPORT,MODULE_ID_COURSEPREREQUISITE,MODULE_ID_LTI_CONSUMER,
                                                    MODULE_ID_ANALYTICS,MODULE_ID_H5P,MODULE_ID_COURSE_WIDGETS);

        foreach($alwaysdisabledCollaborationModules as $disabledCollaborationModule){
            unset($modules_collaborations[$disabledCollaborationModule]);
        }
        
        $data['modules_collaboration'] = $modules_collaborations;
    }
}

view('admin.other.modules', $data);
