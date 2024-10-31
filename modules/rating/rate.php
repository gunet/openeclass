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

$require_current_course = TRUE;

require_once '../../include/baseTheme.php';
require_once 'include/course_settings.php';
require_once 'class.rating.php';
require_once 'modules/progress/RatingEvent.php';

$is_link = false;
$is_wallpost = false;
$rateEventActivity = null;

if ($_GET['rtype'] == 'blogpost') {
	$setting_id = SETTING_BLOG_RATING_ENABLE;
} elseif ($_GET['rtype'] == 'course') {
    $setting_id = SETTING_COURSE_RATING_ENABLE;
} elseif ($_GET['rtype'] == 'forum_post') {
    $setting_id = SETTING_FORUM_RATING_ENABLE;
    $rateEventActivity = RatingEvent::FORUM_ACTIVITY;
} elseif ($_GET['rtype'] == 'link') {
    $is_link = true; //there is no rating setting for social bookmarks, rating is always enabled
    $rateEventActivity = RatingEvent::SOCIALBOOKMARK_ACTIVITY;
} elseif ($_GET['rtype'] == 'wallpost') {
    $is_wallpost = true; //there is no rating setting for wall posts, rating is always enabled
}

if ($is_link || $is_wallpost || setting_get($setting_id, $course_id) == 1) {
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
        triggerGame($course_id, $uid, $rateEventActivity);

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
                $response[0] .= '<small class="text-muted">&nbsp;&nbsp;|&nbsp;&nbsp;'.$num_ratings['fivestar'].$langRatingVote.'&nbsp;&nbsp;|&nbsp;&nbsp;</small>';
            } else {
                $response[0] .= '<small class="text-muted">&nbsp;&nbsp;|&nbsp;&nbsp;'.$num_ratings['fivestar'].$langRatingVotes.'&nbsp;&nbsp;|&nbsp;&nbsp;</small>';
            }

        }

        echo json_encode($response);
    }
}

function triggerGame($courseId, $uid, $rateEventActivity) {
    if ($rateEventActivity !== null) {
        $eventData = new stdClass();
        $eventData->courseId = $courseId;
        $eventData->uid = $uid;
        $eventData->activityType = $rateEventActivity;
        $eventData->module = MODULE_ID_RATING;

        RatingEvent::trigger(RatingEvent::RATECAST, $eventData);
    }
}
