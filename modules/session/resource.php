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
 * @file resource.php
 * @brief Sessions display resources
 */

$require_login = true;
$require_current_course = true;
if(isset($_GET['type']) and !$_GET['type']=='doc_upload'){
    $require_consultant = true;
}
$require_help = TRUE;
$helpTopic = 'course_sessions';

require_once '../../include/baseTheme.php';
require_once 'include/lib/forcedownload.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'include/lib/fileManageLib.inc.php';
require_once 'include/lib/fileUploadLib.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/lib/mediaresource.factory.php';
require_once 'insert_doc.php';
require_once 'insert_work.php';
require_once 'insert_tc.php';
require_once 'functions.php';

check_activation_of_collaboration();

load_js('tools.js');

if(isset($_GET['session'])){
    $data['sessionID'] = $sessionID = $_GET['session'];
}
elseif(isset($_GET['id'])){
    $data['sessionID'] = $sessionID = $_GET['id'];
}

session_exists($sessionID);
check_user_belongs_in_session($sessionID);

$sessionTitle = title_session($course_id,$sessionID);

if(isset($_GET['type']) and $_GET['type'] == 'doc_upload'){
    $pageName = $langDownloadFile;
}else{
    $pageName = $langAddResource;
}
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langSession);
$navigation[] = array('url' => 'session_space.php?course=' . $course_code . "&session=" . $sessionID , 'name' => $sessionTitle);

if(isset($_POST['submit_doc'])){
    insert_session_docs($sessionID);
}elseif(isset($_POST['submit_tc'])){
    insert_session_tc($sessionID);
}elseif(isset($_POST['submit_work'])){
    insert_session_work($sessionID);
}elseif(isset($_POST['submit_upload'])){
    upload_session_doc($sessionID);
}elseif(isset($_POST['submit_passage'])){
    insert_session_passage($sessionID);
}

$type_resource = '';
if(isset($_GET['type'])){
    if($_GET['type'] == 'doc'){
        $type_resource = list_documents($sessionID,$course_id);
    }elseif($_GET['type'] == 'work'){
        $type_resource = list_assignment($sessionID);
    }elseif($_GET['type'] == 'tc'){
        $type_resource = list_teleconferences($sessionID);
    }elseif($_GET['type'] == 'doc_upload'){
        $type_resource = upload_file($sessionID);
    }elseif($_GET['type'] == 'add_tc'){
        $is_created_tc = session_tc_creation($sessionID,$course_id,'bbb',$_GET['token']);
        if($is_created_tc){
            Session::flash('message',$langBBBAddSuccessful);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/session/session_space.php?course=$course_code&session=$sessionID");
        }else{
            Session::flash('message',$langForbidden);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page("modules/session/session_space.php?course=$course_code&session=$sessionID");
        }
    }elseif($_GET['type'] == 'passage'){
        $type_resource = passage_insertion($sessionID);
    }
}
$data['type_resource'] = $type_resource;

view('modules.session.resources', $data);
