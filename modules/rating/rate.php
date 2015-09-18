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

$is_link = false;

if ($_GET['rtype'] == 'blogpost') {
	$setting_id = SETTING_BLOG_RATING_ENABLE;
} elseif ($_GET['rtype'] == 'course') {
    $setting_id = SETTING_COURSE_RATING_ENABLE;
} elseif ($_GET['rtype'] == 'forum_post') {
    $setting_id = SETTING_FORUM_RATING_ENABLE;
} elseif ($_GET['rtype'] == 'link') {
    $is_link = true; //there is no rating setting for social bookmarks, rating is always enabled
}

if ($is_link || setting_get($setting_id, $course_id) == 1) {
    if (Rating::permRate($is_editor, $uid, $course_id, $_GET['rtype'])) {
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
            
        } elseif ($widget == 'thumbs_up') {
            $up_value = $rating->getThumbsUpRating();
            
            $response[0] = $up_value;//positive rating
            $response[1] = $action;//new rating or deletion of old one
            $response[2] = $langUserHasRated;//necessary string
            if ($had_rated === false) {
                $response[3] = $urlServer."modules/rating/thumbs_up_active.png";
            } else {
                $response[3] = $urlServer."modules/rating/thumbs_up_inactive.png";
            }
        } elseif ($widget == 'fivestar') {
            $response[0] = "";
            
            $num_ratings = $rating->getRatingsNum();
            
            if ($num_ratings['fivestar'] != 0) {
                $avg = $rating->getFivestarRating();
                $response[0] .= '<small class="text-muted">&nbsp;('.$avg.')</small>';
                $response[1] = $avg;
            } else {
                $response[1] = 0;
            }
            
            if ($num_ratings['fivestar'] == 1) {
                $response[0] .= '<small class="text-muted">&nbsp;&nbsp;|&nbsp;&nbsp;'.$num_ratings['fivestar'].$langRatingVote.'</small>';
            } else {
                $response[0] .= '<small class="text-muted">&nbsp;&nbsp;|&nbsp;&nbsp;'.$num_ratings['fivestar'].$langRatingVotes.'</small>';
            }
            
        }
        
        echo json_encode($response);
    }
}
