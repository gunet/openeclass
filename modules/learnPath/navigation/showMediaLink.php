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


$require_current_course = true;
require_once '../../../include/init.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/lib/mediaresource.factory.php';

$nameTools = $langMediaTypeDesc;

if (isset($_GET['id'])) {
    $row = Database::get()->querySingle("SELECT * FROM videolink WHERE course_id = ?d AND url = ?s", $course_id, $_GET['id']);
    $lp_resource_sql = Database::get()->querySingle("SELECT name, comment FROM lp_module WHERE module_id = ?d", $_GET['viewModule_id']);
    $lp_spec_comment = Database::get()->querySingle("SELECT specificComment FROM lp_rel_learnPath_module WHERE module_id = ?d", $_GET['viewModule_id'])->specificComment;
    if ($row) {
        echo "<div align='center'>$lp_resource_sql->name</div>";
        echo "<div align='center'>$lp_resource_sql->comment</div>";
        echo "<div align='center'>$lp_spec_comment</div>";
        $vObj = MediaResourceFactory::initFromVideoLink($row);
        echo MultimediaHelper::medialinkIframeObject($vObj);
    }
}
