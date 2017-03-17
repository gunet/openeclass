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

require_once '../../include/baseTheme.php';
require_once 'include/course_settings.php';
require_once 'class.comment.php';
require_once 'class.commenting.php';
require_once 'modules/progress/CommentEvent.php';

$wall_commenting = false;

$commentEventActivity = null;
if ($_POST['rtype'] == 'blogpost') {
    $setting_id = SETTING_BLOG_COMMENT_ENABLE;
    $commentEventActivity = CommentEvent::BLOG_ACTIVITY;
} elseif ($_POST['rtype'] == 'course') {
    $setting_id = SETTING_COURSE_COMMENT_ENABLE;
    $commentEventActivity = CommentEvent::COURSE_ACTIVITY;
} elseif ($_POST['rtype'] == 'wallpost') {
    $wall_commenting = true;
}

if ($wall_commenting || setting_get($setting_id, $course_id) == 1) {
    //response array
    //[0] -> status, [1] -> message, other positions -> other data 
    $response = array();
    
    if ($_POST['action'] == 'new') {
        if (Commenting::permCreate($is_editor, $uid, $course_id)) {
            $comment = new Comment();
            if ($comment->create($_POST['commentText'], $uid, $_POST['rtype'], intval($_POST['rid']))) {
                $post_actions = '<div class="pull-right">';
                $post_actions .= '<a href="javascript:void(0)" onclick="xmlhttpPost(\''.$urlServer.'modules/comments/comments.php?course='.$course_code.'\', \'editLoad\', '.$_POST['rid'].', \''.$_POST['rtype'].'\', \'\', '.$comment->getId().')">';
                $post_actions .= icon('fa-edit', $langModify).'</a> ';
                $post_actions .= '<a href="javascript:void(0)" onclick="xmlhttpPost(\''.$urlServer.'modules/comments/comments.php?course='.$course_code.'\', \'delete\', '.$_POST['rid'].', \''.$_POST['rtype'].'\', \''.$langCommentsDelConfirm.'\', '.$comment->getId().')">';
                $post_actions .= icon('fa-times', $langDelete).'</a>';
                $post_actions .='</div>';   
                
                $response[0] = 'OK';
                $response[1] = "<div class='alert alert-success'>".$langCommentsSaveSuccess."</div>";
                $response[2] = $comment->getId();
                $response[3] = "
         <div class='row margin-bottom-thin margin-top-thin comment' id='comment-".$comment->getId()."'>
                    <div class='col-xs-12'>
                        <div class='media'>
                            <a class='media-left' href='#'>
                                ". profile_image($comment->getAuthor(), IMAGESIZE_SMALL) ."
                            </a>
                            <div class='media-body bubble'>
                                <div class='label label-success media-heading'>".nice_format($comment->getTime(), true).'</div>'.
                                    "<small>".$langBlogPostUser.display_user($comment->getAuthor(), false, false)."</small>".
                                    $post_actions
                                    ."<div class='margin-top-thin' id='comment_content-".$comment->getId()."'>". q($comment->getContent()) ."</div>
                            </div>
                        </div>
                    </div>
                </div>                    
                ";
                triggerGame($course_id, $uid, CommentEvent::NEWCOMMENT, $commentEventActivity, $comment->getRid());
            } else {
                $response[0] = 'ERROR';
                $response[1] = "<div class='alert alert-warning'>".$langCommentsSaveFail."</div>";
            }
        } else {
            $response[0] = 'ERROR';
            $response[1] = "<div class='alert alert-warning'>".$langCommentsNewNoPerm."</div>";
        }
        echo json_encode($response);
    } else if ($_POST['action'] == 'delete') {
        $comment = new Comment();
        if ($comment->loadFromDB(intval($_POST['cid']))) {
            if ($comment->permEdit($is_editor, $uid)) {
                if ($comment->delete()) {
                    $response[0] = 'OK';
                    $response[1] = "<div class='alert alert-success'>".$langCommentsDelSuccess."</div>";
                    triggerGame($course_id, $uid, CommentEvent::DELCOMMENT, $commentEventActivity, $comment->getRid());
                } else {
                    $response[0] = 'ERROR';
                    $response[1] = "<div class='alert alert-warning'>".$langCommentsDelFail."</div>";
                }
            } else {
                $response[0] = 'ERROR';
                $response[1] = "<div class='alert alert-warning'>".$langCommentsDelNoPerm."</div>";
            }
        } else {
            $response[0] = 'ERROR';
            $response[1] = "<div class='alert alert-warning'>".$langCommentsLoadFail."</div>";
        }
        echo json_encode($response);
    } else if ($_POST['action'] == 'editLoad') {
        $comment = new Comment();
        if ($comment->loadFromDB(intval($_POST['cid']))) {
            if ($comment->permEdit($is_editor, $uid)) {
                $response[0] = 'OK';
                $response[1] = '';
                $response[2] = '<textarea class="form-control" id="edit-textarea-'.$_POST['cid'].'" rows="5">'.q($comment->getContent()).'</textarea><br/>';
                $response[2] .= '<input class="btn btn-primary" type="submit" value="'.$langSubmit.'" onclick="xmlhttpPost(\''.$urlServer.'modules/comments/comments.php?course='.$course_code.'\', \'editSave\','.$comment->getRid().', \''.$comment->getRtype().'\', \''.$langCommentsSaveConfirm.'\', '.$comment->getId().');"/>';
            } else {
                $response[0] = 'ERROR';
                $response[1] = "<div class='alert alert-warning'>".$langCommentsEditNoPerm."</div>";
            }
        } else {
            $response[0] = 'ERROR';
            $response[1] = "<div class='alert alert-warning'>".$langCommentsLoadFail."</div>";
        }
        echo json_encode($response);
    } else if ($_POST['action'] == 'editSave') {
        $comment = new Comment();
        if ($comment->loadFromDB(intval($_POST['cid']))) {
            if ($comment->permEdit($is_editor, $uid)) {
                if ($comment->edit($_POST['commentText'])) {
                    $response[0] = 'OK';
                    $response[1] = "<div class='alert alert-success'>".$langCommentsSaveSuccess."</div>";
                    $response[2] = '<div id="comment_content-'.$comment->getId().'">'.q($comment->getContent()).'</div>';
                } else {
                    $response[0] = 'ERROR';
                    $response[1] = "<div class='alert alert-warning'>".$langCommentsSaveFail."</div>";
                }
            } else {
                $response[0] = 'ERROR';
                $response[1] = "<div class='alert alert-warning'>".$langCommentsEditNoPerm."</div>";
            }
        } else {
            $response[0] = 'ERROR';
            $response[1] = "<div class='alert alert-warning'>".$langCommentsLoadFail."</div>";
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