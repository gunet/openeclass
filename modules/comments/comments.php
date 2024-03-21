<?php
/* ========================================================================
 * Open eClass 3.8
* E-learning and Course Management System
* ========================================================================
* Copyright 2003-2019  Greek Universities Network - GUnet
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
require_once 'include/course_settings.php';
require_once 'class.comment.php';
require_once 'class.commenting.php';
require_once 'modules/progress/CommentEvent.php';
require_once 'modules/analytics/CommentsAnalyticsEvent.php';


$wall_commenting = false;

$commentEventActivity = null;
$commentTypeAnalytics = null;
if ($_POST['rtype'] == 'blogpost') {
    $setting_id = SETTING_BLOG_COMMENT_ENABLE;
    $commentEventActivity = CommentEvent::BLOG_ACTIVITY;
    $commentTypeAnalytics = CommentsAnalyticsEvent::BLOGPOSTCOMMENT;
} elseif ($_POST['rtype'] == 'course') {
    $setting_id = SETTING_COURSE_COMMENT_ENABLE;
    $commentEventActivity = CommentEvent::COURSE_ACTIVITY;
    $commentTypeAnalytics = CommentsAnalyticsEvent::COURSECOMMENT;
} elseif ($_POST['rtype'] == 'wallpost') {
    $wall_commenting = true;
    $commentTypeAnalytics = CommentsAnalyticsEvent::WALLPOSTCOMMENT;
}

if ($wall_commenting || setting_get($setting_id, $course_id) == 1) {
    //response array
    //[0] -> status, [1] -> message, other positions -> other data
    $response = array();

    if ($_POST['action'] == 'new') {
        if (Commenting::permCreate($is_editor, $uid, $course_id)) {
            $comment = new Comment();
            if ($comment->create($_POST['commentText'], $uid, $_POST['rtype'], intval($_POST['rid']))) {
                $post_actions = '<div class="d-flex gap-3">';
                $post_actions .= '<a href="javascript:void(0)" onclick="xmlhttpPost(\''.$urlServer.'modules/comments/comments.php?course='.$course_code.'\', \'editLoad\', '.$_POST['rid'].', \''.$_POST['rtype'].'\', \'\', '.$comment->getId().')">';
                $post_actions .= '<i class="fa-solid fa-edit" data-bs-original-title="'.$langModify.'" title="" data-bs-toggle="tooltip"></i></a>';
                $post_actions .= '<a class="link-delete" href="javascript:void(0)" onclick="xmlhttpPost(\''.$urlServer.'modules/comments/comments.php?course='.$course_code.'\', \'delete\', '.$_POST['rid'].', \''.$_POST['rtype'].'\', \''.$langCommentsDelConfirm.'\', '.$comment->getId().')">';
                $post_actions .= '<i class="fa-solid fa-xmark" data-bs-original-title="'.$langDelete.'" title="" data-bs-toggle="tooltip"></i></a>';
                $post_actions .='</div>';

                $response[0] = 'OK';
                $response[1] = "<div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>".$langCommentsSaveSuccess."</span></div>";
                $response[2] = $comment->getId();
                $response[3] = "
                <div class='row mb-4 comment' id='comment-".$comment->getId()."'>
                    <div class='col-12'>
                        <div class='card panelCard px-lg-4 py-lg-3 h-100'>
                            <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                <div>
                                    <a href='#'>
                                        ". profile_image($comment->getAuthor(), IMAGESIZE_SMALL,'rounded-circle') ."
                                        <small>" .display_user($comment->getAuthor(), false, false). "</small>
                                    </a>
                                </div>
                                ".$post_actions."
                            </div>
                            <div class='card-body'>
                                <p class='TextBold'>".format_locale_date(strtotime($comment->getTime())).'</p>'.
                                "<p id='comment_content-".$comment->getId()."'>". q($comment->getContent()) ."</p>
                            </div>
                        </div>
                    </div>
                </div>                    
                ";
                triggerGame($course_id, $uid, CommentEvent::NEWCOMMENT, $commentEventActivity, $comment->getRid());
                triggerAnalytics($course_id, $uid, $commentTypeAnalytics);
            } else {
                $response[0] = 'ERROR';
                $response[1] = "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>".$langCommentsSaveFail."</span></div>";
            }
        } else {
            $response[0] = 'ERROR';
            $response[1] = "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>".$langCommentsNewNoPerm."</span></div>";
        }
        echo json_encode($response);
    } else if ($_POST['action'] == 'delete') {
        $comment = new Comment();
        if ($comment->loadFromDB(intval($_POST['cid']))) {
            if ($comment->permEdit($is_editor, $uid)) {
                if ($comment->delete()) {
                    $response[0] = 'OK';
                    $response[1] = "<div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>".$langCommentsDelSuccess."</span></div>";
                    triggerGame($course_id, $uid, CommentEvent::DELCOMMENT, $commentEventActivity, $comment->getRid());
                    triggerAnalytics($course_id, $uid, $commentTypeAnalytics);
                } else {
                    $response[0] = 'ERROR';
                    $response[1] = "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>".$langCommentsDelFail."</span></div>";
                }
            } else {
                $response[0] = 'ERROR';
                $response[1] = "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>".$langCommentsDelNoPerm."</span></div>";
            }
        } else {
            $response[0] = 'ERROR';
            $response[1] = "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>".$langCommentsLoadFail."</span></div>";
        }
        echo json_encode($response);
    } else if ($_POST['action'] == 'editLoad') {
        $comment = new Comment();
        if ($comment->loadFromDB(intval($_POST['cid']))) {
            if ($comment->permEdit($is_editor, $uid)) {
                $response[0] = 'OK';
                $response[1] = '';
                $response[2] = '<textarea class="form-control" id="edit-textarea-'.$_POST['cid'].'" rows="5">'.q($comment->getContent()).'</textarea><br/>';
                $response[2] .= '<input class="btn submitAdminBtn" type="submit" value="'.$langSubmit.'" onclick="xmlhttpPost(\''.$urlServer.'modules/comments/comments.php?course='.$course_code.'\', \'editSave\','.$comment->getRid().', \''.$comment->getRtype().'\', \''.$langCommentsSaveConfirm.'\', '.$comment->getId().');"/>';
            } else {
                $response[0] = 'ERROR';
                $response[1] = "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>".$langCommentsEditNoPerm."</span></div>";
            }
        } else {
            $response[0] = 'ERROR';
            $response[1] = "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>".$langCommentsLoadFail."</span></div>";
        }
        echo json_encode($response);
    } else if ($_POST['action'] == 'editSave') {
        $comment = new Comment();
        if ($comment->loadFromDB(intval($_POST['cid']))) {
            if ($comment->permEdit($is_editor, $uid)) {
                if ($comment->edit($_POST['commentText'])) {
                    $response[0] = 'OK';
                    $response[1] = "<div class='alert alert-success'><i class='fa-solid fa-circle-check fa-lg'></i><span>".$langCommentsSaveSuccess."</span></div>";
                    $response[2] = '<div id="comment_content-'.$comment->getId().'">'.q($comment->getContent()).'</div>';
                } else {
                    $response[0] = 'ERROR';
                    $response[1] = "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>".$langCommentsSaveFail."</span></div>";
                }
            } else {
                $response[0] = 'ERROR';
                $response[1] = "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>".$langCommentsEditNoPerm."</span></div>";
            }
        } else {
            $response[0] = 'ERROR';
            $response[1] = "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>".$langCommentsLoadFail."</span></div>";
        }
        echo json_encode($response);
    }
}

function triggerGame($courseId, $uid, $eventName, $commentEventActivity, $resourceId) {
    if ($commentEventActivity !== null) {
        $eventData = new stdClass();
        $eventData->courseId = $courseId;
        $eventData->uid = $uid;
        $eventData->activityType = $commentEventActivity;
        $eventData->module = MODULE_ID_COMMENTS;
        $eventData->resource = $resourceId;
        CommentEvent::trigger($eventName, $eventData);
    }
}

function triggerAnalytics($courseId, $uid, $commentTypeAnalytics) {
    if ($commentTypeAnalytics !== null) {
        $data = new stdClass();
        $data->uid = $uid;
        $data->course_id = $courseId;

        if ($commentTypeAnalytics == CommentsAnalyticsEvent::BLOGPOSTCOMMENT)
            $data->element_type = 20;
        else if ($commentTypeAnalytics == CommentsAnalyticsEvent::COURSECOMMENT)
            $data->element_type = 21;
        else if ($commentTypeAnalytics == CommentsAnalyticsEvent::WALLPOSTCOMMENT)
            $data->element_type = 22;

        CommentsAnalyticsEvent::trigger($commentTypeAnalytics, $data, true);
    }
}
