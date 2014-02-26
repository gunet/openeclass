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
 * This class represents the rating system
*/
Class Rating {
    
    private $rtype = '';
    private $rid = 0;
    
    /**
     * Constructor
     * @param rtype the type of the resource
     * @param rid the id of the resource
     */
    public function __construct($rtype, $rid) {
    	$this->rtype = $rtype;
    	$this->rid = $rid;
    }
    
    /**
     * Get number of ratings for the resource
     * @return int
     */
    public function getRatingsNum() {
    	$sql = "SELECT COUNT(`rate_id`) as c FROM `rating` WHERE `rtype` = ?s AND `rid` = ?d";
    	$res = Database::get()->querySingle($sql, $this->rtype, $this->rid);
    	return $res->c;
    }
    
    /**
     * Check if a user has rated the resource
     * @param
     * @return boolean
     */
    public function hasUserRated($user_id) {
        $sql = "SELECT COUNT(`rate_id`) as c FROM `rating` WHERE `rtype` = ?s AND `rid` = ?d AND `user_id`=?d";
        $res = Database::get()->querySingle($sql, $this->rtype, $this->rid, $user_id);
        if ($res->c > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Cast a new rating (or delete an old one)
     * @param value the rating value
     * @param user_id the user id
     * @return string the action that took place
     */
    public function castRating($value, $user_id) {
        $sql = "SELECT COUNT(`rate_id`) as `c` FROM `rating` WHERE `rid`=?d AND `rtype`=?s AND `user_id`=?d AND `value`=?d";
        $res = Database::get()->querySingle($sql, $this->rid, $this->rtype, $user_id, $value);
        
        if ($res->c > 0) {//clicking again the same icon deletes the rating
            $sql = "DELETE FROM `rating` WHERE `rid`=?d AND `rtype`=?s AND `user_id`=?d AND `value`=?d";
            Database::get()->query($sql, $this->rid, $this->rtype, $user_id, $value);
            
            $action = "del";
        } else {//either casting a new rating or changing the rating
            //delete old rating of the same user on this resource if it exists
            $sql = "DELETE FROM `rating` WHERE `rid`=?d AND `rtype`=?s AND `user_id`=?d";
            Database::get()->query($sql, $this->rid, $this->rtype, $user_id);
            
            //cast new rating
            $sql = "INSERT INTO `rating` (`rid`,`rtype`,`value`,`user_id`) VALUES(?d,?s,?d,?d)";
            Database::get()->query($sql, $this->rid, $this->rtype, $value, $user_id);
            
            $action = "ins";
        }
        
        //update cache table records for this resource
        $this->updateUpCache();
        $this->updateDownCache();
        
        return $action;
    }
    
    /**
     * Update caching table for positive ratings
     */
    private function updateUpCache() {
        $sql = "DELETE FROM `rating_cache` WHERE `rtype`=?s AND `rid`=?d AND `tag`=?s";
        Database::get()->query($sql, $this->rtype, $this->rid, 'up');
        
        $sql = "SELECT COUNT(`rate_id`) as `c` FROM `rating` WHERE `rtype`=?s AND `rid`=?d AND `value`=?s";
        $res = Database::get()->querySingle($sql, $this->rtype, $this->rid, 1);
        
        $sql = "INSERT INTO `rating_cache` (`rid`,`rtype`,`value`,`time`, `tag`) VALUES(?d,?s,?d,NOW(),?s)";
        Database::get()->query($sql, $this->rid, $this->rtype, $res->c, 'up');
    }
    
    /**
     * Update caching table for negative ratings
     */
    private function updateDownCache() {
        $sql = "DELETE FROM `rating_cache` WHERE `rtype`=?s AND `rid`=?d AND `tag`=?s";
        Database::get()->query($sql, $this->rtype, $this->rid, 'down');
        
        $sql = "SELECT COUNT(`rate_id`) as `c` FROM `rating` WHERE `rtype`=?s AND `rid`=?d AND `value`=?s";
        $res = Database::get()->querySingle($sql, $this->rtype, $this->rid, -1);
        
        $sql = "INSERT INTO `rating_cache` (`rid`,`rtype`,`value`,`time`, `tag`) VALUES(?d,?s,?d,NOW(),?s)";
        Database::get()->query($sql, $this->rid, $this->rtype, $res->c, 'down');
    }
    
    /**
     * Get positive ratings for a resource
     * @return int
     */
    public function getUpRating() {
        $sql = "SELECT `value` FROM `rating_cache` WHERE `rid`=?d AND `rtype`=?s AND `tag`=?s";
        $res = Database::get()->querySingle($sql, $this->rid, $this->rtype, 'up');
        if (is_object($res)) {
            return $res->value;
        } else {
            return 0;
        }
    }
    
    /**
     * Get negative rating for a resource
     * @return int
     */
    public function getDownRating() {
    	$sql = "SELECT `value` FROM `rating_cache` WHERE `rid`=?d AND `rtype`=?s AND `tag`=?s";
    	$res = Database::get()->querySingle($sql, $this->rid, $this->rtype, 'down');
    	if (is_object($res)) {
    		return $res->value;
    	} else {
    		return 0;
    	}
    }
    
    /**
     * check if a user has rated the resource
     * @param int the user id
     * @return boolean
     */
    public function userHasRated($user_id) {
        $sql = "SELECT COUNT(`rate_id`) as `c` FROM `rating` WHERE `rid`=?d AND `rtype`=?s AND `user_id`=?d";
        $res = Database::get()->querySingle($sql, $this->rid, $this->rtype, $user_id);
        if ($res->c > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    public function put($isEditor, $uid, $courseId) {
        global $langUserHasRated;
        
        $out = "<div class=\"rating\">";
        
        $onclick_up = $onclick_down = "";
        
        //disable icons when user hasn't permission to vote
        if (Rating::permRate($isEditor, $uid, $courseId)) {
            $onclick_up = "onclick=\"Rate(".$this->rid.",'".$this->rtype."',1)\"";
            $onclick_down = "onclick=\"Rate(".$this->rid.",'".$this->rtype."',-1)\"";
        }
        
        $out .= "<img src=\"../rating/up.png\" ".$onclick_up."/>&nbsp;";
        $out .= "<span id=\"rate_".$this->rid."_up\">".$this->getUpRating()."</span>&nbsp;&nbsp;";
        $out .= "<img src=\"../rating/down.png\" ".$onclick_down."/>&nbsp;";
        $out .= "<span id=\"rate_".$this->rid."_down\">".$this->getDownRating()."</span>";
        $out .= "<div class=\"smaller\" id=\"rate_msg_".$this->rid."\">";
        
        if ($this->userHasRated($uid)) {
            $out .= $langUserHasRated;
        }
        
        $out .= "</div>";
        $out .= "</div>";
        
        return $out;
    }
    
    /**
     * Delete all comments of a resource
     * @param rtype the resource type
     * @param rid the resource id
     * @return boolean
     */
    public static function deleteRatings($rtype, $rid) {
    	Database::get()->query("DELETE FROM `rating` WHERE `rtype`=?s AND `rid`=?d", $rtype, $rid);
    	Database::get()->query("DELETE FROM `rating_cache` WHERE `rtype`=?s AND `rid`=?d", $rtype, $rid);
    }
    
    /**
     * Check if a user has permission to rate course resources
     * @param isEditor boolean showing if user is teacher
     * @param uid the user id
     * @param courseId the course id
     * @return boolean
     */
    public static function permRate($isEditor, $uid, $courseId) {
        if ($isEditor) {//teacher is always allowed to rate
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
    
}

/**
 * Add necessary javascript to head section of an html document
 */
function rating_add_js() {
	global $head_content;
	$head_content .= '<script src="../rating/rating.js" type="text/javascript"></script>';
}
