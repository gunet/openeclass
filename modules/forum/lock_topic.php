<?php

/* ========================================================================
 * Open eClass 3.0
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
 * ======================================================================== */

/**
 * @file forum_admin.php  
 */

$require_current_course = TRUE;
$require_editor = true;

require_once '../../include/baseTheme.php';
require_once 'functions.php';

$ok = false;

if (isset($_GET['topic'])) {
    $topic = intval($_GET['topic']);
    if (does_exists($topic, 'topic')) {
        Database::get()->query("UPDATE forum_topic SET locked = !locked WHERE id = ?d", $topic);
        $ok = true;
    }
}

echo json_encode($ok);
