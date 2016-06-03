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

$require_login = true;
$require_current_course = true;

require_once '../../include/baseTheme.php';
require_once 'Game.php';

$toolName = "Game Progress";

// check for completeness in order to refresh user data
Game::checkCompleteness($uid, $course_id);

$data = array();
$iter = array('certificate', 'badge');
foreach ($iter as $key) {
    $gameQ = "select a.*, b.title, "
            . " b.description, b.active, b.created "
            . " from user_{$key} a "
            . " join {$key} b on (a.{$key} = b.id) "
            . " where a.user = ?d and b.course = ?d";
    Database::get()->queryFunc($gameQ, function($game) use ($key, &$data) {
        $data['game_' . $key][] = $game;
    }, $uid, $course_id);
}

view('modules.game.progress', $data);