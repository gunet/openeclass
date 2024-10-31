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

$require_current_course = TRUE;
require_once '../include/baseTheme.php';
require_once 'include/log.class.php';

if ($is_editor and isset($_GET['module_id']) and isset($_POST['hide'])) {
    $eclass_module_id = intval($_GET['module_id']);
    $visible = $_POST['hide'] == '0'? 1: 0;
    Database::get()->query("UPDATE course_module SET visible = ?d
        WHERE module_id = ?d AND course_id = ?d",
        $visible, $eclass_module_id, $course_id);
    $action = $visible? 'activate': 'deactivate';
    Log::record($course_id, MODULE_ID_TOOLADMIN, LOG_MODIFY, [
        $action => [$eclass_module_id]
    ]);

    if(isset($_POST['Active_Deactive_Btn'])){
        header("Location: " .$_POST['prev_url']);
    }
}

