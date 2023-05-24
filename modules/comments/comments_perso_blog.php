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
require_once 'modules/comments/class.comment.php';
require_once 'modules/comments/class.commenting.php';

if (get_config('personal_blog_commenting')) {
    //response array
    //[0] -> status, [1] -> message, other positions -> other data
    $response = array();

    if ($_POST['action'] == 'new') {
        if (isset($_SESSION['uid'])  && $session->status) {
            $comment = new Comment();
            if ($comment->create($_POST['commentText'], $uid, $_POST['rtype'], intval($_POST['rid']))) {
                $post_actions = '<div class="float-end">';
                $post_actions .= '<a href="javascript:void(0)" onclick="xmlhttpPost(\''.$urlServer.'modules/comments/comments_perso_blog.php\', \'delete\', '.$_POST['rid'].', \''.$_POST['rtype'].'\', \''.$langCommentsDelConfirm.'\', '.$comment->getId().')">';
                $post_actions .= '<span class="fa fa-times text-danger float-end" data-bs-original-title="'.$langDelete.'" title="" data-bs-toggle="tooltip"></span></a>';
                $post_actions .= '<a href="javascript:void(0)" onclick="xmlhttpPost(\''.$urlServer.'modules/comments/comments_perso_blog.php\', \'editLoad\', '.$_POST['rid'].', \''.$_POST['rtype'].'\', \'\', '.$comment->getId().')">';
                $post_actions .= '<span class="fa fa-edit pe-2 float-end text-warning" data-bs-original-title="'.$langModify.'" title="" data-bs-toggle="tooltip"></span></a>';
                $post_actions .='</div>';
                $response[0] = 'OK';
                $response[1] = "<div class='alert alert-success'>".$langCommentsSaveSuccess."</div>";
                $response[2] = $comment->getId();
                $response[3] = "
                 <div class='row mb-4 comment' id='comment-".$comment->getId()."'>
                    <div class='col-12'>
                        <div class='panel panel-default p-1'>
                            <div class='panel-heading'>
                                <a class='media-left' href='#'>
                                    ". profile_image($comment->getAuthor(), IMAGESIZE_SMALL) ."
                                </a>
                                ".$post_actions."
                            </div>
                            <div class='media-body bubble panel-body overflow-auto'>
                                <button class='btn btn-success btn-sm mt-2 media-heading'>".format_locale_date(strtotime($comment->getTime())).'</button>'.
                                    "<small>".$langBlogPostUser.display_user($comment->getAuthor(), false, false)."</small>
                                    
                                    <div class='margin-top-thin overflow-auto mt-3' id='comment_content-".$comment->getId()."'>". q($comment->getContent()) ."</div>
                            </div>
                        </div>
                    </div>
                </div>                    
                ";
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
            $permEdit = false;
            if (isset($_SESSION['uid'])  && $session->status) {
                if ($comment->getAuthor() == $uid) {
                    $permEdit = true;
                }
                $blog_user = Database::get()->querySingle("SELECT user_id FROM blog_post WHERE id = ?d", $comment->getRid());
                if ($blog_user->user_id == $uid) {
                    $permEdit = true;
                }
                if (isset($is_admin) && $is_admin) {
                    $permEdit = true;
                }
            }
            if ($permEdit) {
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
            $permEdit = false;
            if (isset($_SESSION['uid'])  && $session->status) {
                if ($comment->getAuthor() == $uid) {
                    $permEdit = true;
                }
                $blog_user = Database::get()->querySingle("SELECT user_id FROM blog_post WHERE id = ?d", $comment->getRid());
                if ($blog_user->user_id == $uid) {
                    $permEdit = true;
                }
                if (isset($is_admin) && $is_admin) {
                    $permEdit = true;
                }
            }
            if ($permEdit) {
                $response[0] = 'OK';
                $response[1] = '';
                $response[2] = '<textarea class="form-control" id="edit-textarea-'.$_POST['cid'].'" rows="5">'.q($comment->getContent()).'</textarea><br/>';
                $response[2] .= '<input class="btn submitAdminBtn" type="submit" value="'.$langSubmit.'" onclick="xmlhttpPost(\''.$urlServer.'modules/comments/comments_perso_blog.php\', \'editSave\','.$comment->getRid().', \''.$comment->getRtype().'\', \''.$langCommentsSaveConfirm.'\', '.$comment->getId().');"/>';
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
            $permEdit = false;
            if (isset($_SESSION['uid'])  && $session->status) {
                if ($comment->getAuthor() == $uid) {
                    $permEdit = true;
                }
                $blog_user = Database::get()->querySingle("SELECT user_id FROM blog_post WHERE id = ?d", $comment->getRid());
                if ($blog_user->user_id == $uid) {
                    $permEdit = true;
                }
                if (isset($is_admin) && $is_admin) {
                    $permEdit = true;
                }
            }
            if ($permEdit) {
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
