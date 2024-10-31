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

require_once '../../include/baseTheme.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_REQUEST['nodeid'])) {
    exit();
}

$nodeid = intval($_REQUEST['nodeid']);

if ($nodeid <= 0) {
    exit();
}

require_once 'include/lib/hierarchy.class.php';
$tree = new Hierarchy();
$fullpath = $tree->getFullPath($nodeid);

if (strlen($fullpath) <= 0) {
    exit();
}

$data = array(
    "nodefullpath" => $fullpath
);

echo json_encode($data);
exit();

