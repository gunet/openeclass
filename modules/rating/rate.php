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

require_once 'class.rating.php';
require_once '../../include/baseTheme.php';
require_once 'include/course_settings.php';

if ($_GET['rtype'] == 'blogpost') {
	$setting_id = SETTING_BLOG_RATING_ENABLE;
}

if (setting_get($setting_id, $course_id) == 1) {
    if (Rating::permRate($is_editor, $uid, $course_id)) {
        $rtype = $_GET['rtype'];
        $rid = intval($_GET['rid']);
        $value = intval($_GET['value']);
        
        //response array
        $response = array();
        
        $rating = new Rating($rtype, $rid);
        $action = $rating->castRating($value, $uid);
        
        $up_value = $rating->getUpRating();
        $down_value = $rating->getDownRating();
        
        $response[0] = $up_value;//positive rating
        $response[1] = $down_value;//negative rating
        $response[2] = $action;//new rating or deletion of old one
        $response[3] = $langUserHasRated;//necessary string
        
        
        echo json_encode($response);
    }
}
