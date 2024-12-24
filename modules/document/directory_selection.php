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

$require_editor = true;
$require_current_course = true;
require_once '../../include/baseTheme.php';
require_once 'modules/document/doc_init.php';

doc_init();

$directories = Database::get()->queryArray("SELECT path, filename FROM document
    WHERE format = '.dir' AND $group_sql
    ORDER BY path");

$curDirPath = $_GET['openDir'] ?? '';

$directories = array_map(function ($dir) use ($curDirPath) {
    $dir->depth = substr_count($dir->path, '/') - ($curDirPath? 0: 1);
    return $dir;
}, $directories);

view('modules.document.directory_selection',
    ['directories' => $directories, 'curDirPath' => $curDirPath]);
