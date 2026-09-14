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

/**
 * @file index.php
 * @brief Main script for mindmap module
 */
$require_current_course = TRUE;
$require_login = TRUE;
$require_help = TRUE;
$helpTopic = 'mind_map';

require_once '../../include/baseTheme.php';
require_once 'modules/document/doc_init.php';
require_once 'include/lib/forcedownload.php';

/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_MINDMAP);
/* * *********************************** */

$toolName = $langMindmap;
$navigation[] = array("url" => "../document/index.php?course=$course_code", "name" => $langDoc);

if (isset($_GET['jmpath'])) {
    doc_init();
    $path_components = explode('/', $_GET['jmpath']);
    $file_path = public_path_to_disk_path($path_components);
    $arr = null;
    if ($file_path and $file_path->format == 'jm') {
        $arr = file_get_contents($basedir . $file_path->path);
    }
    if (!$arr) {
       not_found();
    }
} else {
    $arr = "";
}

add_units_navigation(TRUE);

$data['arr'] = $arr;

view('modules.mindmap.index', $data);
