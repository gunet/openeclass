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

$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'classes/SearchEngineFactory.php';
header('Content-Type: application/json; charset=utf-8');
set_time_limit(0);

// fetch number of courses waiting in index queue
$n = Database::get()->querySingle("SELECT COUNT(id) AS count FROM idx_queue")->count;

if ($n == 0) {
    // commit
    $searchEngine = SearchEngineFactory::create();
    $searchEngine->commit();
}

$data = array(
    "remaining" => $n
);

echo json_encode($data);
exit();
