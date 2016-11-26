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

/**
 * This class represents a comment
*/
Class Comment {
    
    private $id = 0;
    private $content = '';
    private $creationTime = '0000-00-00 00:00:00';
    private $authorId = 0;
    private $rtype = '';
    private $rid = 0;
    
    /**
     * Load a comment from db
     * @param postId the blog post id
     * @return boolean true on success, false on failure
     */
    public function loadFromDB($commentId) {
    	$sql = 'SELECT * FROM `comments` WHERE `id` = ?d';
    	$result = Database::get()->querySingle($sql, $commentId);
    	if (is_object($result)) {
    		$this->authorId = $result->user_id;
    		$this->content = $result->content;
    		$this->creationTime = $result->time;
    		$this->id = $commentId;
    		$this->rtype = $result->rtype;
    		$this->rid = $result->rid;
    		return true;
    	} else {
    		return false;
    	}
    }
    
    /**
     * Load multiple comments from a PDO array
     * @param arr the array with the data retrieved from DB
     * @return array with loaded comments
     */
    public static function loadFromPDOArr($arr) {
    	$ret = array();
    	$i = 0;
    	foreach ($arr as $a) {
    		$ret[$i] = new Comment();
    		$ret[$i]->id = $a->id;
    		$ret[$i]->content = $a->content;
    		$ret[$i]->authorId = $a->user_id;
    		$ret[$i]->rid = $a->rid;
    		$ret[$i]->rtype = $a->rtype;
    		$ret[$i]->creationTime = $a->time;
    		$i++;
    	}
    	return $ret;
    }
    
    /**
     * Save a comment in database
     * @param content the blog post content
     * @param authorId the user id of the author
     * @param rtype the resource type
     * @param rid the resource id
     * @return boolean true on success, false on failure
     */
    public function create($content, $authorId, $rtype, $rid) {
        $sql = 'INSERT INTO `comments` (`content`, `user_id`, `rtype`, `rid`, `time`) '
                .'VALUES(?s,?d,?s,?d,NOW())';
        $id = Database::get()->query($sql, $content, $authorId, $rtype, $rid)->lastInsertID;
        //load the comment after creation
        if ($this->loadFromDB($id)) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Delete comment
     * @return boolean true on success, false on failure
     */
    public function delete() {
        //delete abuse reports for this comment first and log action
        $res = Database::get()->queryArray("SELECT * FROM abuse_report WHERE `rid` = ?d AND `rtype` = ?s", $this->id, 'comment');
        foreach ($res as $r) {
            Log::record($r->course_id, MODULE_ID_ABUSE_REPORT, LOG_DELETE,
                array('id' => $r->id,
                      'user_id' => $r->user_id,
                      'reason' => $r->reason,
                      'message' => $r->message,
                      'rtype' => 'comment',
                      'rid' => $this->id,
                      'rcontent' => $this->getContent(),
                      'status' => $r->status
                     ));
        } 
        $sql = 'DELETE FROM `abuse_report` WHERE `rid` = ?d AND `rtype` = ?s';
        Database::get()->query($sql, $this->id, 'comment');
        
        $sql = 'DELETE FROM `comments` WHERE `id` = ?d';
        $numrows = Database::get()->query($sql, $this->id)->affectedRows;
        if ($numrows == 1) {
        	return true;
        } else {
        	return false;
        }
    }
    
    
    /**
     * Update a blog post in database
     * @param title the blog post title
     * @param content the blog post content
     * @return boolean true on success, false on failure
     */
    public function edit($content) {
        $sql = 'SELECT COUNT(`id`) as c FROM `comments` WHERE `content` = ?s AND `id`= ?d';
        $result = Database::get()->querySingle($sql, $content, $this->id);
        if ($result->c == 0) {
            $sql = 'UPDATE `comments` SET `content` = ?s WHERE `id` = ?d';
            $numrows = Database::get()->query($sql, $content, $this->id)->affectedRows;
            if ($numrows == 1) {
                $this->content = $content;
            	return true;
            } else {
            	return false;
            }
        } else {//same content
            return true;
        }
    }
    
    /**
     * Get comment id
     * @return int
     */
    public function getId() {
    	return $this->id;
    }
    
    /**
     * Get comment content
     * @return string
     */
    public function getContent() {
    	return $this->content;
    }
    
    /**
     * Get comment author id
     * @return int
     */
    public function getAuthor() {
    	return $this->authorId;
    }
    
    /**
     * Get comment creation time
     * @return DateTime
     */
    public function getTime() {
    	return $this->creationTime;
    }
    
    /**
     * Get comment resource type
     * @return string
     */
    public function getRtype() {
    	return $this->rtype;
    }
    
    /**
     * Get comment resource id
     * @return int
     */
    public function getRid() {
    	return $this->rid;
    }
    
    /**
     * Check if a user has permission to edit/delete the comment
     * @param isEditor boolean showing if user is teacher
     * @param uid the user id
     * @return boolean
     */
    public function permEdit($isEditor, $uid) {
        global $session;
        if (!$session->status) {//anonymous
            return false;
        }
        if ($isEditor) {//teacher is always allowed to edit
            return true;
        } else {
	        if ($this->authorId == $uid) {//current user is comment author
	            return true;
	        } else {
	            return false;
	        }
        }
    }
}
