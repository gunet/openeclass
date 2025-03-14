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

include '../../include/baseTheme.php';

$toolName = $langPreview;
$navigation[] = array('url' => 'listfaculties.php', 'name' => $langSelectFac);

$data['courseId'] = $courseId = course_code_to_id($_GET['c']);
$data['c'] = $c = Database::get()->querySingle("SELECT * FROM course WHERE id = ?d",$courseId);

if ($c->visible == COURSE_INACTIVE) {
    redirect_to_home_page();
}

if (!isset($_SESSION['uid']) and $c->visible == COURSE_CLOSED) {
    redirect_to_home_page();
}

$data['course_descriptions'] = Database::get()->queryArray("SELECT cd.id, cd.title, cd.comments, cd.type, cdt.icon FROM course_description cd
                                    LEFT JOIN course_description_type cdt ON (cd.type = cdt.id)
                                    WHERE cd.course_id = ?d AND cd.visible = 1 ORDER BY cd.order", $courseId);

view('modules.auth.info_course', $data);
