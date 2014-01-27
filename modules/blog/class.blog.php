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
 * This class represents a Blog
 */
Class Blog {
    private $course_id;
    private $user_id;
    
    /**
     * Constructor
     * @param course_id the id of the course in case of a course blog
     * @param user_id the id of the user in case of a user blog
     */
    public function __construct($course_id, $user_id) {
        if ($course_id != 0) {//course blog
            $this->course_id = $course_id;
            $this->user_id = 0;
        } else {//user blog
            $this->user_id = $user_id;
            $this->course_id = 0;
        }
    }
    
    /**
     * Get the number of blog posts in a blog
     * @return int
     */
    public function blogPostsNumber() {
        $sql = 'SELECT COUNT(`id`) as c FROM `blog_post` WHERE ';
        if ($this->course_id != 0) {//course blog
            $sql .= '`course_id` = ?';
            $param = $this->course_id;
        } else {//user blog
            $sql .= '`course_id` = 0 AND `user_id` = ?';
            $param = $this->user_id;
        }
        $numPosts = Database::get()->querySingle($sql, $param)->c;
        return $numPosts;
    }
    
    /**
     * Get blog posts from DB with pagination
     * @param page the page of the blog
     * @param postsPerPage the number of blog posts per page
     * @return array with blog post objects
     */
    public function getBlogPostsDB($page, $postsPerPage) {
        $offset = $page*$postsPerPage;
        $sql = 'SELECT * FROM `blog_post` WHERE ';
        if ($this->course_id != 0) {//course blog
            $sql .= '`course_id` = ? ORDER BY `time` DESC LIMIT ?,?';
            $param = $this->course_id;
        } else {//user blog
            $sql .= '`course_id` = 0 AND `user_id` = ? ORDER BY `time` DESC LIMIT ?,?';
            $param = $this->user_id;
        }
        $result = Database::get()->queryArray($sql, $param, $offset, $postsPerPage);
        $ret = array();
        if (is_array($result)) {
        	$ret = BlogPost::loadFromPDOArr($result);
        }
        return $ret;
    }
    
    /**
     * Get the most popular blog posts in a blog from DB
     * @param num the number of blog posts to get
     * @return array with blog post objects
     */
    private function getPopularBlogPostsDB($num) {
        $sql = 'SELECT * FROM `blog_post` WHERE ';
        if ($this->course_id != 0) {//course blog
        	$sql .= '`course_id` = ? ORDER BY `views` DESC LIMIT ?';
        	$param = $this->course_id;
        } else {//user blog
        	$sql .= '`course_id` = 0 AND `user_id` = ? ORDER BY `views` DESC LIMIT ?';
        	$param = $this->user_id;
        }
        $result = Database::get()->queryArray($sql, $param, $num);
        $ret = array();
        if (is_array($result)) {
            $ret = BlogPost::loadFromPDOArr($result);
        }
        return $ret;
    }
    
    /**
     * HTML code for the most popular blog posts
     * @param num the number of blog posts to show
     * @return string HMTL code
     */
    public function popularBlogPostsHTML($num) {
        global $course_code, $langBlogPopular;
        $posts = $this->getPopularBlogPostsDB($num);
        $out = "<table><tr><th style=\"border:0px;\">$langBlogPopular</th></tr>";
        foreach ($posts as $post) {
            $out .= "<tr><td><a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;action=showPost&amp;pId=".$post->getId()."'>".$post->getTitle()."</a></td></tr>";
        }
        $out .= "</table><br>";
        return $out;
    }
    
    public function navLinksHTML($page, $postsPerPage) {
        global $course_code, $langBlogNewerPosts, $langBlogOlderPosts;
        
        $num_posts = $this->blogPostsNumber();
        
        if ($page < ceil($num_posts/$postsPerPage)-1) {
        	$older = TRUE;
        } elseif ($page == ceil($num_posts/$postsPerPage)-1) {
        	$older = FALSE;
        }
         
        if($page > 0)
        	$newer = TRUE;
        
        $out = "<table id='navcontainer' width='100%'><tr>";
        if(isset($newer) && $newer)
        	$out .= "<td class='left'><a href='$_SERVER[PHP_SELF]?course=".$course_code."&amp;action=showBlog&amp;page=".($page-1)."'>".$langBlogNewerPosts."</a>&nbsp;</td>";
        if(isset($older) && $older)
        	$out .= "<td class='right'><a href='$_SERVER[PHP_SELF]?course=".$course_code."&amp;action=showBlog&amp;page=".($page+1)."'>".$langBlogOlderPosts."</a>&nbsp;</td>";
        $out .= "</tr></table>";
        
        return $out;
    }
    
    public function chronologicalTreeHTML() {
        
    }
    
    /**
     * Get blog course id
     * @return int, 0 if a user's blog
     */
    public function getCourse() {
    	return $this->course_id;
    }
    
    /**
     * Get blog user id
     * @return int, 0 if a course's blog
     */
    public function getUser() {
    	return $this->user_id;
    }
}