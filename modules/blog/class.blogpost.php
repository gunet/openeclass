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

/**
 * This class represents page of a Blog Post
 */
Class BlogPost {
    
    private $id = 0;
    private $title = '';
    private $content = '';
    private $creationTime = '0000-00-00 00:00:00';
    private $authorId = 0;
    private $views = 0;
    private $courseId = 0;

    /**
     * Load a blog post from db
     * @param postId the blog post id
     * @return boolean true on success, false on failure
     */
    public function loadFromDB($postId) {
        $sql = 'SELECT * FROM `blog_post` WHERE `id` = ?d';
        $result = Database::get()->querySingle($sql, $postId);
        if (is_object($result)) {
            $this->authorId = $result->user_id;
            $this->content = $result->content;
            $this->title = $result->title;
            $this->creationTime = $result->time;
            $this->id = $postId;
            $this->views = $result->views;
            $this->courseId = $result->course_id;
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Load multiple blog posts from a PDO array
     * @param arr the array with the data retrieved from DB
     * @return array with loaded blog post objects
     */
    public static function loadFromPDOArr($arr) {
        $ret = array();
        $i = 0;
        foreach ($arr as $a) {
            $ret[$i] = new BlogPost();
            $ret[$i]->id = $a->id;
            $ret[$i]->title = $a->title;
            $ret[$i]->content = $a->content;
            $ret[$i]->authorId = $a->user_id;
            $ret[$i]->views = $a->views;
            $ret[$i]->courseId = $a->course_id;
            $ret[$i]->creationTime = $a->time;
            $i++; 
        }
        return $ret;
    }
    
    /**
     * Save a blog post in database
     * @param title the blog post title
     * @param content the blog post content
     * @param authorId the user id of the author
     * @param course_id the id of the course
     * @return boolean true on success, false on failure
     */
    public function create($title, $content, $authorId, $course_id) {
        $sql = 'INSERT INTO `blog_post` (`title`, `content`, `user_id`, `course_id`, `time`, `views`) '
                .'VALUES(?s,?s,?d,?d,NOW(),0)';
        $id = Database::get()->query($sql, $title, $content, $authorId, $course_id)->lastInsertID;
        //load the blog post after creation
        if ($this->loadFromDB($id)) {
            return true;
        } else {
            return false;
        }
    }
     
    /**
     * Delete blog post
     * @return boolean true on success, false on failure
     */
    public function delete() {
        $sql = 'DELETE FROM `blog_post` WHERE `id` = ?d';
        $numrows = Database::get()->query($sql, $this->id)->affectedRows;
        if ($numrows == 1) {
            Commenting::deleteComments('blogpost', $this->id);
            Rating::deleteRatings('blogpost', $this->id);
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
    public function edit($title, $content) {
        $sql = 'SELECT COUNT(`id`) as c FROM `blog_post` WHERE `title` = ?s AND `content` = ?s AND `id`= ?d';
        $result = Database::get()->querySingle($sql, $title, $content, $this->id);
        if ($result->c == 0) {
            $sql = 'UPDATE `blog_post` SET `title` = ?s, `content` = ?s WHERE `id` = ?d';
            $numrows = Database::get()->query($sql, $title, $content, $this->id)->affectedRows;
            if ($numrows == 1) {
                $this->title = $title;
                $this->content = $content;
            	return true;
            } else {
            	return false;
            }
        } else {
            return true;
        }
    }
    
    /**
     * Increase views for a blog post
     * @return boolean true on success, false on failure
     */
    public function incViews() {
        $sql = 'UPDATE `blog_post` SET `views` = `views` + 1 WHERE `id` = ?d';
        $numrows = Database::get()->query($sql, $this->id)->affectedRows;
        if ($numrows == 1) {
        	return true;
        } else {
        	return false;
        }
    }    
    
    /**
     * Get blog post id
     * @return int
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Get blog post title
     * @return string
     */
    public function getTitle() {
    	return $this->title;
    }
    
    /**
     * Get blog post content
     * @return string
     */
    public function getContent() {
    	return $this->content;
    }
    
    /**
     * Get blog post author id
     * @return int
     */
    public function getAuthor() {
    	return $this->authorId;
    }
    
    /**
     * Get blog post course id
     * @return int
     */
    public function getCourse() {
    	return $this->courseId;
    }
    
    /**
     * Get blog post views
     * @return int
     */
    public function getViews() {
    	return $this->views;
    }
    
    /**
     * Get blog post creation time
     * @return DateTime
     */
    public function getTime() {
    	return $this->creationTime;
    }
    
    /**
     * Check if a user has permission to edit/delete course blog posts
     * @param isEditor boolean showing if user is teacher
     * @param studConfigVal boolean based on the config value allowing users to create posts
     * @param uid the user id
     * @return boolean
     */
    public function permEdit($isEditor, $studConfigVal, $uid) {
        if ($isEditor) {//teacher is always allowed to edit
            return true;
        } else {
            if ($studConfigVal) {//students allowed to edit
                if ($this->authorId == $uid) {//current user is post author
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }
}
