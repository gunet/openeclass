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
 * @file index.php
 * @brief Sessions display module
 */

$require_login = true;
$require_current_course = true;
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

load_js('tools.js');

if(isset($_GET['session'])){
    $data['sessionID'] = $sessionID = $_GET['session'];
}
elseif(isset($_GET['id'])){
    $data['sessionID'] = $sessionID = $_GET['id'];
}

$sessionTitle = title_session($course_id,$sessionID);
$pageName = $langAddResource;
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
    }
}
$data['type_resource'] = $type_resource;

view('modules.session.resources', $data);
