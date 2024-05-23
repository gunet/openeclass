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


// New course default modules admin page

$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/create_course/functions.php';

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$navigation[] = array('url' => 'modules.php', 'name' => $langModules);
$pageName = $langDefaultModules;

if (isset($_POST['submit'])) {

    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

    if (isset($_POST['module'])) {
        set_config('default_modules', serialize(array_keys($_POST['module'])));
    }

    if(isset($_POST['moduleCollaboration'])){
        set_config('default_modules_collaboration', serialize(array_keys($_POST['moduleCollaboration'])));
    }

    Session::flash('message',$langWikiEditionSucceed);
    Session::flash('alert-class', 'alert-success');

    if(!isset($_POST['module']) or !isset($_POST['moduleCollaboration'])){
        Session::flash('message',$langWikiEditionNoSucceed);
        Session::flash('alert-class', 'alert-danger');
    }

    redirect_to_home_page('modules/admin/modules_default.php');
    
} else {

    if(!get_config('show_always_collaboration')){
        $data['disabled'] = [];
        foreach (Database::get()->queryArray('SELECT module_id FROM module_disable') as $item) {
            $data['disabled'][] = $item->module_id;
        }
        $data['modules'] = $modules;
    }

    if(get_config('show_collaboration')){
        $data['disabledCollaboration'] = [];
        foreach (Database::get()->queryArray('SELECT module_id FROM module_disable_collaboration') as $item) {
            $data['disabledCollaboration'][] = $item->module_id;
        }
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
            MODULE_ID_REQUEST => array('title' => $langRequests, 'link' => 'request', 'image' => 'fa-regular fa-clipboard'),
            MODULE_ID_ASSIGN => array('title' => $langWorks, 'link' => 'work', 'image' => 'fa-solid fa-upload'),
            MODULE_ID_GRADEBOOK => array('title' => $langGradebook, 'link' => 'gradebook', 'image' => 'fa-solid fa-a'),
            MODULE_ID_ATTENDANCE => array('title' => $langAttendance, 'link' => 'attendance', 'image' => 'fa-solid fa-clipboard-user'),
            MODULE_ID_SESSION => array('title' => $langSession, 'link' => 'session', 'image' => 'fa-solid fa-handshake')
        );
        $data['modules_collaborations'] = $modules_collaborations;
    }


    if(!get_config('show_always_collaboration')){
        $data['default'] = default_modules();
    }

    if(get_config('show_collaboration')){
        $data['defaultCollaboration'] = default_modules_collaboration();
    }

    $data['action_bar'] = action_bar(
        [
            [ 'title' => $langBack,
              'url' => $urlAppend . 'modules/admin/modules.php',
              'icon' => 'fa-reply',
              'level' => 'primary' ]
        ], false);

    view('admin.other.modules_default', $data);
}
