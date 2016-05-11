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

set_time_limit(0);
require_once '../../include/baseTheme.php';
require_once 'modules/search/indexer.class.php';

echo "Welcome to the reindexing CLI tool.\nPlease run this as user: www-data, i.e.:  sudo -u www-data php idxreindexcli.php\n\n";

Indexer::deleteAll();
Database::get()->query("DELETE FROM idx_queue");
Database::get()->queryFunc("SELECT id FROM course", function($r) {
    Database::get()->query("INSERT INTO idx_queue (course_id) VALUES (?d)", $r->id);
});

// fetch number of courses waiting in index queue
$n = Database::get()->querySingle("SELECT COUNT(id) AS count FROM idx_queue")->count;
$idx = new Indexer();

while ($n > 0) {
    // fetch next waiting course
    $cid = Database::get()->querySingle("SELECT course_id FROM idx_queue LIMIT 1")->course_id;
    
    // re-index
    echo "Indexing course id: " . $cid . ". ";
    echo "Removing old data. ";
    $idx->removeAllByCourse($cid);
    echo "Storing new data. ";
    $idx->storeAllByCourse($cid);
    
    // remove course from queue
    Database::get()->query("DELETE FROM idx_queue WHERE course_id = ?d", $cid);
    $n = Database::get()->querySingle("SELECT COUNT(id) AS count FROM idx_queue")->count;
    echo "Remaining courses for indexing: " . $n . "\n";
}

echo "\nOptimizing index ...\n";
$idx->getIndex()->optimize();

echo "All done, exiting ...\n";
exit();
