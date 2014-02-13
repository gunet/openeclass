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

require_once 'class.comment.php';
require_once 'class.commenting.php';
require_once '../../include/baseTheme.php';

//response array
//[0] -> status, [1] -> message, other positions -> other data 
$response = array();

if ($_POST['action'] == 'new') {
    $comment = new Comment();
    if ($comment->create($_POST['commentText'], $uid, $_POST['rtype'], intval($_POST['rid']))) {
        $response[0] = 'OK';
        $response[1] = "<p class='success'>".$langCommentsSaveSuccess."</p>";
        $response[2] = $comment->getId();
        $response[3] = '<div class="smaller">'.nice_format($comment->getTime(), true).$langBlogPostUser.uid_to_name($comment->getAuthor()).':</div>';
        $response[3] .= '<div id="comment_content-'.$comment->getId().'">'.q($comment->getContent()).'</div>';
        $response[3] .= '<div class="comment_actions">';
        $response[3] .= '<a href="javascript:void(0)" onclick="xmlhttpPost(\'../comments/comments.php\', \'editLoad\', '.$_POST['rid'].', \''.$_POST['rtype'].'\', \'\', '.$comment->getId().')">';
        $response[3] .= '<img src="'.$themeimg.'/edit.png" alt="'.$langModify.'" title="'.$langModify.'"/></a>';
        $response[3] .= '<a href="javascript:void(0)" onclick="xmlhttpPost(\'../comments/comments.php\', \'delete\', '.$_POST['rid'].', \''.$_POST['rtype'].'\', \''.$langCommentsDelConfirm.'\', '.$comment->getId().')">';
        $response[3] .= '<img src="'.$themeimg.'/delete.png" alt="'.$langDelete.'" title="'.$langDelete.'"/></a>';
        $response[3] .='</div>';
    } else {
        $response[0] = 'ERROR';
        $response[1] = "<p class='alert1'>".$langCommentsSaveFail."</p>";
    }
    echo json_encode($response);
} else if ($_POST['action'] == 'delete') {
    $comment = new Comment();
    if ($comment->loadFromDB(intval($_POST['cid']))) {
        if ($comment->delete()) {
            $response[0] = 'OK';
            $response[1] = "<p class='success'>".$langCommentsDelSuccess."</p>"; 
        } else {
            $response[0] = 'ERROR';
            $response[1] = "<p class='alert1'>".$langCommentsDelFail."</p>";
        }
    } else {
        $response[0] = 'ERROR';
        $response[1] = "<p class='alert1'>".$langCommentsLoadFail."</p>";
    }
    echo json_encode($response);
} else if ($_POST['action'] == 'editLoad') {
    $comment = new Comment();
    if ($comment->loadFromDB(intval($_POST['cid']))) {
        $response[0] = 'OK';
        $response[1] = '';
        $response[2] = '<textarea id="edit-textarea-'.$_POST['cid'].'" cols="40" rows="5">'.q($comment->getContent()).'</textarea><br/>';
        $response[2] .= '<input type="submit" value="'.$langSubmit.'" onclick="xmlhttpPost(\'../comments/comments.php\', \'editSave\','.$comment->getRid().', \''.$comment->getRtype().'\', \''.$langCommentsSaveConfirm.'\', '.$comment->getId().');"/>';
    } else {
        $response[0] = 'ERROR';
        $response[1] = "<p class='alert1'>".$langCommentsLoadFail."</p>";
    }
    echo json_encode($response);
} else if ($_POST['action'] == 'editSave') {
    $comment = new Comment();
    if ($comment->loadFromDB(intval($_POST['cid']))) {
       if ($comment->edit($_POST['commentText'])) {
           $response[0] = 'OK';
           $response[1] = "<p class='success'>".$langCommentsSaveSuccess."</p>";
           $response[2] = '<div id="comment_content-'.$comment->getId().'">'.$comment->getContent().'</div>';
       } else {
           $response[0] = 'ERROR';
           $response[1] = "<p class='alert1'>".$langCommentsSaveFail."</p>";
       }
    } else {
        $response[0] = 'ERROR';
        $response[1] = "<p class='alert1'>".$langCommentsLoadFail."</p>";
    }
    echo json_encode($response);
}
