<?php

/* ========================================================================
 * Open eClass 
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2015  Greek Universities Network - GUnet
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

$require_current_course = true;
$require_editor = true;

require_once '../../include/baseTheme.php';
require_once 'Game.php';

$toolName = "Game Progress All";

$data = array();
$iter = array('certificate', 'badge');

// initialize data vars for template
foreach ($iter as $key) {
    $data['game_' . $key] = array();
}

// populate with data
foreach ($iter as $key) {
    $gameQ = "select a.*, "
            . " b.title, b.description, b.active, b.created, "
            . " u.surname, u.givenname, u.username "
            . " from user_{$key} a "
            . " join {$key} b on (a.{$key} = b.id) "
            . " join user u on (u.id = a.user) "
            . " where b.course_id = ?d";
    Database::get()->queryFunc($gameQ, function($game) use ($key, &$data) {
        $data['game_' . $key][] = $game;
    }, $course_id);
}

view('modules.progress.progressall', $data);