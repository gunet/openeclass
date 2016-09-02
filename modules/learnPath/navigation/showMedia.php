<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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


$require_current_course = true;
require_once '../../../include/init.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/lib/mediaresource.factory.php';

$nameTools = $langMediaTypeDesc;

if (isset($_GET['id'])) {
    
    $row = Database::get()->querySingle("SELECT * FROM video WHERE course_id = ?d AND path = ?s", $course_id, $_GET['id']);
    $lp_resource_sql = Database::get()->querySingle("SELECT name, comment FROM lp_module WHERE module_id = (SELECT module_id FROM lp_asset WHERE path = ?s)", $_GET['id']);
    $lp_spec_comment = Database::get()->querySingle("SELECT specificComment FROM lp_rel_learnPath_module WHERE module_id = (SELECT module_id FROM lp_asset WHERE path = ?s)", $_GET['id'])->specificComment;
    if ($row) {
        echo "<div align='center'>$lp_resource_sql->name</div>";
        echo "<div align='center'>$lp_resource_sql->comment</div>";
        echo "<div align='center'>$lp_spec_comment</div>";
        $vObj = MediaResourceFactory::initFromVideo($row);
        echo MultimediaHelper::mediaHtmlObject($vObj);
    }
}

