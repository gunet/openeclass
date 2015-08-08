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

$require_current_course = TRUE;

require_once '../../include/baseTheme.php';
require_once 'modules/wall/wall_functions.php';

$posts_per_page = 5;

if (visible_module(MODULE_ID_WALL)) {
    if (isset($_GET['page'])) {
        $page = intval($_GET['page']);
        if ($page > 1) {//first page is shown in index.php
            $posts = Database::get()->queryArray("SELECT id, user_id, content, youtube, FROM_UNIXTIME(timestamp) as datetime, pinned  FROM wall_post WHERE course_id = ?d ORDER BY pinned DESC, timestamp DESC LIMIT ?d,?d", $course_id, ($page-1)*$posts_per_page, $posts_per_page);
            if (count($posts) != 0) {
                echo generate_infinite_container_html($posts, ++$page);
            }  
        }
    }
}
