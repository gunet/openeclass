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

require_once 'class.comment.php';
require_once 'class.commenting.php';
require_once '../../include/baseTheme.php';
require_once 'include/course_settings.php';

if ($_POST['rtype'] == 'blogpost') {
    $setting_id = SETTING_BLOG_COMMENT_ENABLE;
} elseif ($_POST['rtype'] == 'course') {
    $setting_id = SETTING_COURSE_COMMENT_ENABLE;
}

if (setting_get($setting_id, $course_id) == 1) {
    //response array
    //[0] -> status, [1] -> message, other positions -> other data 
    $response = array();
    
    if ($_POST['action'] == 'new') {
        if (Commenting::permCreate($is_editor, $uid, $course_id)) {
            $comment = new Comment();
            if ($comment->create($_POST['commentText'], $uid, $_POST['rtype'], intval($_POST['rid']))) {
                $response[0] = 'OK';
                $response[1] = "<p class='success'>".$langCommentsSaveSuccess."</p>";
                $response[2] = $comment->getId();
                $response[3] = '<div class="smaller">'.nice_format($comment->getTime(), true).$langBlogPostUser.uid_to_name($comment->getAuthor()).':</div>';
                $response[3] .= '<div id="comment_content-'.$comment->getId().'">'.q($comment->getContent()).'</div>';
                $response[3] .= '<div class="comment_actions">';
                $response[3] .= '<a href="javascript:void(0)" onclick="xmlhttpPost(\''.$urlServer.'modules/comments/comments.php?course='.$course_code.'\', \'editLoad\', '.$_POST['rid'].', \''.$_POST['rtype'].'\', \'\', '.$comment->getId().')">';
                $response[3] .= '<img src="'.$themeimg.'/edit.png" alt="'.$langModify.'" title="'.$langModify.'"/></a>';
                $response[3] .= '<a href="javascript:void(0)" onclick="xmlhttpPost(\''.$urlServer.'modules/comments/comments.php?course='.$course_code.'\', \'delete\', '.$_POST['rid'].', \''.$_POST['rtype'].'\', \''.$langCommentsDelConfirm.'\', '.$comment->getId().')">';
                $response[3] .= '<img src="'.$themeimg.'/delete.png" alt="'.$langDelete.'" title="'.$langDelete.'"/></a>';
                $response[3] .='</div>';
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
                $response[2] = '<textarea id="edit-textarea-'.$_POST['cid'].'" cols="40" rows="5">'.q($comment->getContent()).'</textarea><br/>';
                $response[2] .= '<input type="submit" value="'.$langSubmit.'" onclick="xmlhttpPost(\''.$urlServer.'modules/comments/comments.php?course='.$course_code.'\', \'editSave\','.$comment->getRid().', \''.$comment->getRtype().'\', \''.$langCommentsSaveConfirm.'\', '.$comment->getId().');"/>';
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
                    $response[1] = "<p class='success'>".$langCommentsSaveSuccess."</p>";
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
