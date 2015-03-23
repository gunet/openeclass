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

//Datepicker
load_js('tools.js');
load_js('jquery');
load_js('jquery-ui');
load_js('jquery-ui-timepicker-addon.min.js');
load_js('datatables');
load_js('datatables_filtering_delay');


$q = $_GET['q'];


$tags = Database::get()->queryArray("SELECT id, tag FROM tags WHERE course_id = ?d AND tag LIKE ?s", $course_id, "%$q%");
if($tags){
    foreach($tags as $tag){
        $tags2[] = array("id"=>$tag->tag, "text"=>$tag->tag);
        //$tags2[] = array("text"=>$tag->tag);
    }
}else{
    $tags2[] = array("text"=>"");
}


//echo $tags2;
echo json_encode($tags2);