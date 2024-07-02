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


/**
 * @file edit_resource.php
 * @brief Modify session resources
 */

$require_login = true;
$require_current_course = true;
$require_help = TRUE;
$helpTopic = 'course_sessions';

require_once '../../include/baseTheme.php';
require_once 'functions.php';

check_activation_of_collaboration();

if(isset($_GET['session'])){
    $data['sessionID'] = $sessionID = $_GET['session'];
    session_exists($sessionID);
}

$sessionTitle = title_session($course_id,$sessionID);
$pageName = $langEditResource;
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langSession);
$navigation[] = array('url' => 'session_space.php?course=' . $course_code . "&session=" . $sessionID , 'name' => $sessionTitle);

if(isset($_POST['modify_resource'])){
    if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) csrf_token_error();

    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('title'));
    $v->labels(array(
        'title' => "$langTheField $langTitle"
    ));
  
    if($v->validate()) {
        Database::get()->query("UPDATE session_resources SET
                                    title = ?s,
                                    comments = ?s
                                    WHERE id = ?d",q($_POST['title']),purify($_POST['comments']),$_POST['resourceId']);

        $typeRes = Database::get()->querySingle("SELECT res_id,type FROM session_resources WHERE id = ?d",$_POST['resourceId']);
        if($typeRes && $typeRes->type == 'tc'){
            Database::get()->query("UPDATE tc_session SET
                                    title = ?s,
                                    description = ?s
                                    WHERE id = ?d AND course_id = ?d AND id_session = ?d",q($_POST['title']),purify($_POST['comments']),$typeRes->res_id,$course_id,$sessionID);
        }
        if($typeRes && $typeRes->type == 'doc'){
            Database::get()->query("UPDATE document SET
                                    title = ?s,
                                    comment = ?s
                                    WHERE id = ?d AND course_id = ?d",q($_POST['title']),purify($_POST['comments']),$typeRes->res_id,$course_id,$sessionID);
        }

        Session::flash('message',$langResourceCompleted);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/session/session_space.php?course=".$course_code."&session=".$sessionID);
    }
}

$resource_info = Database::get()->querySingle("SELECT title,comments FROM session_resources WHERE id = ?d",$_GET['resource_id']);
$data['resource_id'] = $_GET['resource_id'];
$data['title'] = $resource_info->title;
$data['comments'] = rich_text_editor('comments', 5, 40, $resource_info->comments );

$data['action_bar'] = action_bar([
    [ 'title' => $langBack,
        'url' => $urlAppend . 'modules/session/session_space.php?course=' . $course_code . "&session=" . $sessionID,
        'icon' => 'fa-reply',
        'button-class' => 'btn-success',
        'level' => 'primary-label' ]
], false);


view('modules.session.edit_resource', $data);
