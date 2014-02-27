<?php
/* ========================================================================
 * Open eClass 2.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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
$require_login = TRUE;
$path2add = 3;

require_once "../../../include/init.php";
require_once '../../../include/lib/multimediahelper.class.php';
require_once '../../../include/lib/mediaresource.factory.php';

$nameTools = $langMediaTypeDesc;

if (isset($_GET['id']))
{
    $id = q($_GET['id']);
    
    $res = db_query("SELECT * FROM video WHERE path = " . quote($id), $code_cours);
    $row = mysql_fetch_array($res);
    
    if (!empty($row)) {
        $row['course_id'] = $cours_id;
        $vObj = MediaResourceFactory::initFromVideo($row);
        echo MultimediaHelper::mediaHtmlObject($vObj);
    }
}

