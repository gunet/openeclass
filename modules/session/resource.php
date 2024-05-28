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
    $data['session_id'] = $session_id = $_GET['session'];
}
elseif(isset($_GET['id'])){
    $data['session_id'] = $session_id = $_GET['id'];
}

$sessionTitle = title_session($course_id,$session_id);
$pageName = $langAddResource;
$navigation[] = array('url' => 'index.php?course=' . $course_code, 'name' => $langSession);
$navigation[] = array('url' => 'session_space.php?course=' . $course_code . "&session=" . $session_id , 'name' => $sessionTitle);

if(isset($_POST['submit_doc'])){
    insert_session_docs($session_id);
}elseif(isset($_POST['submit_tc'])){
    insert_session_tc($session_id);
}elseif(isset($_POST['submit_work'])){
    insert_session_work($session_id);
}

$type_resource = '';
if(isset($_GET['type'])){
    if($_GET['type'] == 'doc'){
        $type_resource = list_documents($session_id);
    }elseif($_GET['type'] == 'work'){
        $type_resource = list_assignment($session_id);
    }elseif($_GET['type'] == 'tc'){
        $type_resource = list_teleconferences($session_id);
    }
}
$data['type_resource'] = $type_resource;

view('modules.session.resources', $data);
