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

$require_current_course = FALSE;

require_once '../../include/baseTheme.php';
require_once 'modules/rating/class.rating.php';

if (get_config('personal_blog_rating') == 1) {
    $widget = $_GET['widget'];
    $rtype = $_GET['rtype'];
    $rid = intval($_GET['rid']);
    $value = intval($_GET['value']);
    
    //response array
    $response = array();
    
    $rating = new Rating($widget, $rtype, $rid);
    $had_rated = $rating->userHasRated($uid);
    $action = $rating->castRating($value, $uid);
    
    if ($widget == 'up_down') {
        $up_value = $rating->getUpRating();
        $down_value = $rating->getDownRating();
        
        $response[0] = $up_value;//positive rating
        $response[1] = $down_value;//negative rating
        $response[2] = $action;//new rating or deletion of old one
        $response[3] = $langUserHasRated;//necessary string
        
        if ($had_rated === false && $value == 1) {
            $response[4] = $urlServer."modules/rating/thumbs_up_active.png";
            $response[5] = $urlServer."modules/rating/thumbs_down_inactive.png";
        } elseif ($had_rated === false && $value == -1) {
            $response[4] = $urlServer."modules/rating/thumbs_up_inactive.png";
            $response[5] = $urlServer."modules/rating/thumbs_down_active.png";
        } elseif ($had_rated !== false) {
            if ($action == 'del') {
                $response[4] = $urlServer."modules/rating/thumbs_up_inactive.png";
                $response[5] = $urlServer."modules/rating/thumbs_down_inactive.png";
            } else {
                if ($value == 1) {
                    $response[4] = $urlServer."modules/rating/thumbs_up_active.png";
                    $response[5] = $urlServer."modules/rating/thumbs_down_inactive.png";
                } elseif ($value == -1) {
                    $response[4] = $urlServer."modules/rating/thumbs_up_inactive.png";
                    $response[5] = $urlServer."modules/rating/thumbs_down_active.png";
                }
            }
        }
        
    } 
    echo json_encode($response);
}
