<?php

/* ========================================================================
 * Open eClass 
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
 * ======================================================================== 
 */

$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/search/indexer.class.php';
header('Content-Type: application/json; charset=utf-8');

// fetch number of courses waiting in index queue
$n = Database::get()->querySingle("SELECT COUNT(id) AS count FROM idx_queue")->count;
$rem = $n;

if ($n > 0) {
    // fetch next waiting course
    $cid = Database::get()->querySingle("SELECT course_id FROM idx_queue LIMIT 1")->course_id;
    
    // re-index
    $idx = new Indexer();
    $idx->removeAllByCourse($cid);
    $idx->storeAllByCourse($cid);
    set_time_limit(0);
    $idx->getIndex()->optimize();
    
    // remove course from queue
    Database::get()->query("DELETE FROM idx_queue WHERE course_id = ?d", $cid);
    $rem = $n - 1;
}

$data = array(
    "remaining" => $rem
);

echo json_encode($data);
exit();
