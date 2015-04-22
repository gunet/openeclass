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

$require_login = true;
$require_current_course = true;
$require_help = true;
$helpTopic = '';

require_once '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';

$q = $_GET['q'];

$taglist = Database::get()->queryArray("SELECT id, tag FROM tags WHERE course_id = ?d AND tag LIKE ?s GROUP BY tag", $course_id, "%$q%");
if ($taglist) {
    foreach ($taglist as $tag) {
        $tags[] = array('id' => $tag->tag, 'text' => $tag->tag);
    }
} else {
    $tags[] = array('text' => '');
}

echo json_encode($tags);
