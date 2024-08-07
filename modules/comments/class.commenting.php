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

require_once 'include/log.class.php';
require_once 'modules/abuse_report/abuse_report.php';

/**
 * This class represents a commenting system
*/
Class Commenting {

    private $rtype = '';
    private $rid = 0;

    /**
     * Constructor
     * @param course_id the id of the course in case of a course blog
     * @param user_id the id of the user in case of a user blog
     */
    public function __construct($rtype, $rid) {
    	$this->rtype = $rtype;
    	$this->rid = $rid;
    }


    /**
     * Get number of comments for a resource
     * @return int
     */
    public function getCommentsNum() {
        $sql = "SELECT COUNT(`id`) as c FROM `comments` WHERE `rtype` = ?s AND `rid` = ?d";
        $res = Database::get()->querySingle($sql, $this->rtype, $this->rid);
        return $res->c;
    }

    /**
     * Get comments from DB
     * @return array with Comment objects
     */
    public function getCommentsDB() {
    	$sql = 'SELECT * FROM `comments` WHERE '
    	      .'`rtype` = ?s AND '
    	      .'`rid` = ?d '
    	      .'ORDER BY `time` ASC';
    	$result = Database::get()->queryArray($sql, $this->rtype, $this->rid);
    	$ret = array();
    	if (is_array($result)) {
    		$ret = Comment::loadFromPDOArr($result);
    	}
    	return $ret;
    }

    /**
     * Injects all commenting module code in other subsystems
     * @param courseCode the course code
     * @param $isEditor
     * @param $uid the user id
     * @return string
     */
    public function put($courseCode, $isEditor, $uid, $always_open = false) {
        global $langComments, $langBlogPostUser, $langSubmit, $langModify, $langDelete,
        $langCommentsDelConfirm, $langCommentsSaveConfirm, $urlServer, $head_content, $langClose, $langTypeOutMessage, $langSubmitComment;

        $commentsNum = $this->getCommentsNum();

        if (!$always_open) {
            $comments_title = "<i class='fa-regular fa-comment-dots'></i>
                                &nbsp;|&nbsp;
                                <a class='course_commenting vsmall-text text-decoration-underline' data-bs-toggle='modal' data-bs-target='#commentArea-$this->rid'>
                                    $langComments
                                    <span id='commentsNum-$this->rid'>($commentsNum)</span> 
                                    
                                </a>";
            $out = "$comments_title
                    <div class='modal fade text-start' id='commentArea-$this->rid' role='dialog'>
                      <div class='modal-dialog modal-md'>
                        <div class='modal-content'>
                          <div class='modal-header'>
                            <div class='modal-title'>
                                <div class='icon-modal-default'><i class='fa-solid fa-cloud-arrow-up fa-xl Neutral-500-cl'></i></div>
                                <div class='modal-title-default text-center mb-0'>$langComments</div>
                            </div>
                           
                              
                          </div>
                          <div class='modal-body px-0' id='comments-$this->rid'>";
        } else {
            $comments_title = "<h3 id='comments_title'>$langComments (<span class='fs-5' id='commentsNum-$this->rid'>$commentsNum</span>)</h3>";
            $out = "<div class='commenting pt-3 pb-3 mt-3'>
                        $comments_title
                    <div class='commentArea' id='commentArea-$this->rid'>
                    <div id='comments-$this->rid'>";
        }

        if ($commentsNum != 0) {
            //retrieve comments
            $comments = $this->getCommentsDB();
            foreach ($comments as $comment) {
                if (is_null($courseCode)) { //for the case of personal blog posts comments
                    if (isset($_SESSION['uid']) && ($isEditor || ($comment->getAuthor() == $uid))) { //$isEditor corresponds to blog editor
                        $post_actions = '<div class="d-flex flex-wrap gap-3">';
                        $post_actions .= '<a aria-label="'.$langModify.'" href="javascript:void(0)" onclick="xmlhttpPost(\''.$urlServer.'modules/comments/comments_perso_blog.php\', \'editLoad\', '.$this->rid.', \''.$this->rtype.'\', \'\', '.$comment->getId().')">';
                        $post_actions .= '<i class="fa-solid fa-edit" data-bs-original-title="'.$langModify.'" title="" data-bs-toggle="tooltip"></i></a>';
                        $post_actions .= '<a aria-label="'.$langDelete.'" class="link-delete" href="javascript:void(0)" onclick="xmlhttpPost(\''.$urlServer.'modules/comments/comments_perso_blog.php\', \'delete\', '.$this->rid.', \''.$this->rtype.'\', \''.$langCommentsDelConfirm.'\', '.$comment->getId().')">';
                        $post_actions .= '<i class="fa-solid fa-xmark" data-bs-original-title="'.$langDelete.'" title="" data-bs-toggle="tooltip"></i></a>';
                        $post_actions .='</div>';
                    } else {
                        $post_actions = '';
                    }
                } else {
                    if ($comment->permEdit($isEditor, $uid)) {
                        $post_actions = '<div class="d-flex flex-wrap gap-3">';

                        if (abuse_report_show_flag('comment', $comment->getId(), course_code_to_id($courseCode), $isEditor)) {
                            $head_content .= abuse_report_add_js();
                            $post_actions .= abuse_report_icon_flag ('comment', $comment->getId(), course_code_to_id($courseCode));
                        }

                        
                        $post_actions .= '<a aria-label="'.$langModify.'" href="javascript:void(0)" onclick="xmlhttpPost(\''.$urlServer.'modules/comments/comments.php?course='.$courseCode.'\', \'editLoad\', '.$this->rid.', \''.$this->rtype.'\', \'\', '.$comment->getId().')">';
                        $post_actions .= '<i class="fa-solid fa-edit" data-bs-original-title="'.$langModify.'" title="" data-bs-toggle="tooltip"></i></a>';
                        $post_actions .= '<a aria-label="'.$langDelete.'" class="link-delete" href="javascript:void(0)" onclick="xmlhttpPost(\''.$urlServer.'modules/comments/comments.php?course='.$courseCode.'\', \'delete\', '.$this->rid.', \''.$this->rtype.'\', \''.$langCommentsDelConfirm.'\', '.$comment->getId().')">';
                        $post_actions .= '<i class="fa-solid fa-xmark" data-bs-original-title="'.$langDelete.'" title="" data-bs-toggle="tooltip"></i></a>';

                        $post_actions .='</div>';
                    } else {
                        if (abuse_report_show_flag('comment', $comment->getId(), course_code_to_id($courseCode), $isEditor)) {
                            $head_content .= abuse_report_add_js();
                            $post_actions = '<div class="d-flex flex-wrap">'.abuse_report_icon_flag ('comment', $comment->getId(), course_code_to_id($courseCode)).'</div>';
                        } else {
                            $post_actions = '';
                        }
                    }
                }
                $out .= "<div class='row mb-4 comment' id='comment-".$comment->getId()."'>
                          <div class='col-12'>
                            <div class='card panelCard panelCard-comments px-lg-4 py-lg-3'>
                                <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                    <div>
                                        <a class='media-left p-0' href='#'>
                                            ". profile_image($comment->getAuthor(), IMAGESIZE_SMALL,'img-circle rounded-circle') ."
                                            <small>".$langBlogPostUser.display_user($comment->getAuthor(), false, false)."</small>
                                        </a>
                                    </div>
                                    ".$post_actions."
                                </div>
                                <div class='card-body'>
                                    <p class='TextBold'>".format_locale_date(strtotime($comment->getTime())).'</p>'.
                                    "<div class='margin-top-thin overflow-auto mt-3' id='comment_content-".$comment->getId()."'>". q($comment->getContent()) ."</div>
                                </div>
                            </div>
                          </div>
                         </div>";
            }
        }
        $out .= "</div>";

        if (is_null($courseCode)) { //for the case of personal blog posts comments
            if (isset($_SESSION['uid'])) {
                $out .= '<div class="col-12"><div class="form-wrapper form-edit Borders px-0 pb-3"><form action="" onsubmit="xmlhttpPost(\''.$urlServer.'modules/comments/comments_perso_blog.php\', \'new\','.$this->rid.', \''.$this->rtype.'\', \''.$langCommentsSaveConfirm.'\'); return false;">';
                $out .= '<div class="d-flex gap-3">';
                $out .= profile_image($_SESSION['uid'], IMAGESIZE_SMALL,'img-circle rounded-circle');
                $out .= '<textarea class="form-control" aria-label="'.$langTypeOutMessage.'" placeholder="'.$langTypeOutMessage.'" name="textarea" id="textarea-'.$this->rid.'" rows="5"></textarea>';
                $out .= '</div>';
                $out .= '<input style="margin-left:46px;" class="btn submitAdminBtn mt-3" name="send_button" type="submit" value="'.$langSubmitComment.'" />';
                $out .= '</form></div></div>';
            }
        } else {
            if (Commenting::permCreate($isEditor, $uid, course_code_to_id($courseCode))) {
                $out .= '<div class="col-12 pb-5"><div class="form-wrapper form-edit Borders px-0"><form action="" onsubmit="xmlhttpPost(\''.$urlServer.'modules/comments/comments.php?course='.$courseCode.'\', \'new\','.$this->rid.', \''.$this->rtype.'\', \''.$langCommentsSaveConfirm.'\'); return false;">';
                $out .= '<textarea class="form-control" aria-label="'.$langTypeOutMessage.'" placeholder="'.$langTypeOutMessage.'" name="textarea" id="textarea-'.$this->rid.'" rows="5"></textarea><br/>';
                $out .= '<input class="btn submitAdminBtn" name="send_button" type="submit" value="'.$langSubmitComment.'" />';
                $out .= '</form></div></div>';
            }
        }

        if (!$always_open) {
            // $out .= '<div class="modal-footer">
            //             <button type="button" class="btn cancelAdminBtn" data-bs-dismiss="modal">'.$langClose.'</button>
            //          </div>';
            $out .= '</div>';
        }

        $out .= '</div>';
        $out .= '</div>';

        return $out;
    }

    /**
     * Check if a user has permission to create comments
     * @param isEditor boolean showing if user is teacher
     * @param uid the user id
     * @param courseId the course id
     * @return boolean
     */
    public static function permCreate($isEditor, $uid, $courseId) {
        global $session;
        if (!$session->status) {//anonymous
            return false;
        }
        if ($isEditor) {//teacher is always allowed to create
            return true;
        } else {
            //students allowed to create
            $sql = "SELECT COUNT(`user_id`) as c FROM `course_user` WHERE `course_id` = ?d AND `user_id` = ?d";
            $result = Database::get()->querySingle($sql, $courseId, $uid);
            if ($result->c > 0) {//user is course member
                return true;
    	    } else {//user is not course member
                return false;
            }
        }
    }

    /**
     * Delete all comments of a resource
     * @param rtype the resource type
     * @param rid the resource id
     * @return boolean
     */
    public static function deleteComments($rtype, $rid) {
        //delete abuse reports for these comments and log these actions before
        $comms = Database::get()->queryArray("SELECT id, content FROM `comments` WHERE `rtype`=?s AND `rid`=?d", $rtype, $rid);
        foreach ($comms as $c) {
            $reps = Database::get()->queryArray("SELECT * FROM abuse_report WHERE rtype = ?s AND rid = ?d", 'comment', $c->id);
            foreach ($reps as $r) {
                Log::record($r->course_id, MODULE_ID_ABUSE_REPORT, LOG_DELETE,
                    array('id' => $r->id,
                          'user_id' => $r->user_id,
                          'reason' => $r->reason,
                          'message' => $r->message,
                          'rtype' => 'comment',
                          'rid' => $c->id,
                          'rcontent' => $c->comment,
                          'status' => $r->status
                    ));
            }
            Database::get()->query("DELETE FROM abuse_report WHERE rid = ?d AND rtype = ?s", $c->id, 'comment');
        }

        Database::get()->query("DELETE FROM `comments` WHERE `rtype`=?s AND `rid`=?d", $rtype, $rid);
    }

}

/**
 * Add necessary javascript to head section of an html document
 */
function commenting_add_js() {
    global $head_content, $urlServer;
    $head_content .= '<script src="'.$urlServer.'modules/comments/commenting.js" type="text/javascript"></script>';
}
