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

set_time_limit(0);
require_once __DIR__ . '/../../include/baseTheme.php';
require_once 'modules/search/classes/SearchEngineFactory.php';

$command_line = (php_sapi_name() == 'cli' && !isset($_SERVER['REMOTE_ADDR']));
if (!$command_line) {
    forbidden();
}

$user_id = fileowner("$webDir/courses/idx");
if ($user_id) {
    $username = posix_getpwuid($user_id)['name'];
    $usage = "Please run this as user '$username', i.e.: sudo -u $username";
} else {
    $usage = "Usage:";
}

if (!isset($argv[1]) or !in_array($argv[1], ['reindex', 'continue'])) {
    echo "Welcome to the reindexing CLI tool.\n\n",
        "$usage php $argv[0] [reindex|continue]\n\n",
        "Required argument:\n",
        "reindex: Delete current index and re-index all courses\n",
        "continue: Resume an interrupted indexing run\n\n";
    exit(1);
}

$searchEngine = SearchEngineFactory::create();

if ($argv[1] == 'reindex') {
    $searchEngine->deleteAll();
    Database::get()->query("DELETE FROM idx_queue");
    Database::get()->queryFunc("SELECT id FROM course", function($r) {
        Database::get()->query("INSERT INTO idx_queue (course_id) VALUES (?d)", $r->id);
    });
}

// fetch number of courses waiting in index queue
$n = Database::get()->querySingle("SELECT COUNT(id) AS count FROM idx_queue")->count;

while ($n > 0) {
    // fetch next waiting course
    $cid = Database::get()->querySingle("SELECT course_id FROM idx_queue LIMIT 1")->course_id;

    // re-index
    echo "Indexing course id: " . $cid . ". ";
    echo "Removing old data, Storing new data, and optimizing. ";
    $searchEngine->index($cid);

    // remove course from queue
    Database::get()->query("DELETE FROM idx_queue WHERE course_id = ?d", $cid);
    $n = Database::get()->querySingle("SELECT COUNT(id) AS count FROM idx_queue")->count;
    echo "Remaining courses for indexing: " . $n . "\n";
}

echo "All done, exiting ...\n";
exit();
