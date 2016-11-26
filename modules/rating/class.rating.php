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
    
    private $widget = ''; //rating widget type
    private $rtype = ''; //resource type
    private $rid = 0; //resource id
    
    /**
     * Constructor
     * @param rtype the type of the resource
     * @param rid the id of the resource
     * @param widget the widget type, e.g. up_down, fivestar
     */
    public function __construct($widget, $rtype, $rid) {
    	$this->rtype = $rtype;
    	$this->rid = $rid;
    	$this->widget = $widget;
    }
    
    /**
     * Add necessary javascript to head section of an html document
     */
    private function rating_add_js() {
        global $head_content, $urlServer;
        static $loaded;
        
        if (isset($loaded[$this->widget])) {
            return;
        } else {
            $loaded[$this->widget] = true;
        }
        
        $head_content .= '<link rel="stylesheet" type="text/css" href="'.$urlServer.'modules/rating/style.css">';
        
        if ($this->widget == 'up_down') {
            $head_content .= '<script src="'.$urlServer.'modules/rating/js/up_down/rating.js" type="text/javascript"></script>';
        } elseif ($this->widget == 'fivestar') {
            load_js('jquery.rateit.min.js');
        } elseif ($this->widget == 'thumbs_up') {
            $head_content .= '<script src="'.$urlServer.'modules/rating/js/thumbs_up/rating.js" type="text/javascript"></script>';
        }
    }
    
    /**
     * Get number of ratings for the resource
     * @return array
     */
    public function getRatingsNum() {
        $ret = array();
        
        if ($this->widget == "up_down") {
            $sql = "SELECT `count` as c FROM `rating_cache` WHERE `rtype` = ?s AND `rid` = ?d AND `tag` = ?s";
            $res = Database::get()->querySingle($sql, $this->rtype, $this->rid, 'up');
            if (!$res) {
                $ret['up'] = 0;
            } else {
                $ret['up'] = $res->c;
            }
            
            $sql = "SELECT `count` as c FROM `rating_cache` WHERE `rtype` = ?s AND `rid` = ?d AND `tag` = ?s";
            $res = Database::get()->querySingle($sql, $this->rtype, $this->rid, 'down');
            if (!$res) {
                $ret['down'] = 0;
            } else {
                $ret['down'] = $res->c;
            }
        } elseif ($this->widget == "fivestar"){
            $sql = "SELECT `count` as c FROM `rating_cache` WHERE `rtype` = ?s AND `rid` = ?d AND `tag` = ?s";
            $res = Database::get()->querySingle($sql, $this->rtype, $this->rid, $this->widget);
            if (!$res) {
                $ret['fivestar'] = 0;
            } else {
                $ret['fivestar'] = $res->c;
            }
        } elseif ($this->widget == "thumbs_up"){
            $sql = "SELECT `count` as c FROM `rating_cache` WHERE `rtype` = ?s AND `rid` = ?d AND `tag` = ?s";
            $res = Database::get()->querySingle($sql, $this->rtype, $this->rid, $this->widget);
            if (!$res) {
                $ret['like'] = 0;
            } else {
                $ret['like'] = $res->c;
            }
        }
    	
    	return $ret;
    }
    
    /**
     * Cast a new rating (or delete an old one)
     * @param value the rating value
     * @param user_id the user id
     * @return string the action that took place
     */
    public function castRating($value, $user_id) {
        if ($this->widget == 'up_down') {
            if ($user_id == 0) {//anonymous user
                $sql = "SELECT COUNT(`rate_id`) as `c` FROM `rating` WHERE `rid`=?d AND `rtype`=?s AND `widget`=?s AND `user_id`=?d AND `value`=?d AND `rating_source`=?s AND `time` >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
                $res = Database::get()->querySingle($sql, $this->rid, $this->rtype, $this->widget, $user_id, $value, $_SERVER['REMOTE_ADDR']);
            } else {
                $sql = "SELECT COUNT(`rate_id`) as `c` FROM `rating` WHERE `rid`=?d AND `rtype`=?s AND `widget`=?s AND `user_id`=?d AND `value`=?d";
                $res = Database::get()->querySingle($sql, $this->rid, $this->rtype, $this->widget, $user_id, $value);
            }
            
            if ($res->c > 0) {//clicking again the same icon deletes the rating
                if ($user_id == 0) {//anonymous user
                    $sql = "DELETE FROM `rating` WHERE `rid`=?d AND `rtype`=?s AND `widget`=?s AND `user_id`=?d AND `value`=?d AND `rating_source`=?s AND `time` >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
                    Database::get()->query($sql, $this->rid, $this->rtype, $this->widget, $user_id, $value, $_SERVER['REMOTE_ADDR']);
                } else {
                    $sql = "DELETE FROM `rating` WHERE `rid`=?d AND `rtype`=?s AND `widget`=?s AND `user_id`=?d AND `value`=?d";
                    Database::get()->query($sql, $this->rid, $this->rtype, $this->widget, $user_id, $value);
                }
                
                $action = "del";
            } else {//either casting a new rating or changing the rating
                if ($user_id == 0) {//anonymous user
                    //delete old rating of the same user on this resource if it exists
                    $sql = "DELETE FROM `rating` WHERE `rid`=?d AND `rtype`=?s AND `widget`=?s AND `user_id`=?d AND `rating_source`=?s AND `time` >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
                    Database::get()->query($sql, $this->rid, $this->rtype, $this->widget, $user_id, $_SERVER['REMOTE_ADDR']);
                } else {
                    //delete old rating of the same user on this resource if it exists
                    $sql = "DELETE FROM `rating` WHERE `rid`=?d AND `rtype`=?s AND `widget`=?s AND `user_id`=?d";
                    Database::get()->query($sql, $this->rid, $this->rtype, $this->widget, $user_id);
                }
                
                //cast new rating
                $sql = "INSERT INTO `rating` (`rid`,`rtype`,`widget`,`value`,`user_id`,`rating_source`,`time`) VALUES(?d,?s,?s,?d,?d,?s,NOW())";
                Database::get()->query($sql, $this->rid, $this->rtype, $this->widget, $value, $user_id, $_SERVER['REMOTE_ADDR']);
                
                $action = "ins";
            } 
        } elseif ($this->widget == 'thumbs_up') {
            if ($user_id == 0) {//anonymous user
                $sql = "SELECT COUNT(`rate_id`) as `c` FROM `rating` WHERE `rid`=?d AND `rtype`=?s AND `widget`=?s AND `user_id`=?d AND `value`=?d AND `rating_source`=?s AND `time` >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
                $res = Database::get()->querySingle($sql, $this->rid, $this->rtype, $this->widget, $user_id, $value, $_SERVER['REMOTE_ADDR']);
            } else {
                $sql = "SELECT COUNT(`rate_id`) as `c` FROM `rating` WHERE `rid`=?d AND `rtype`=?s AND `widget`=?s AND `user_id`=?d AND `value`=?d";
                $res = Database::get()->querySingle($sql, $this->rid, $this->rtype, $this->widget, $user_id, $value);
            }
            
            if ($res->c > 0) {//clicking again the same icon deletes the rating
                if ($user_id == 0) {//anonymous user
                    $sql = "DELETE FROM `rating` WHERE `rid`=?d AND `rtype`=?s AND `widget`=?s AND `user_id`=?d AND `value`=?d AND `rating_source`=?s AND `time` >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
                    Database::get()->query($sql, $this->rid, $this->rtype, $this->widget, $user_id, $value, $_SERVER['REMOTE_ADDR']);
                } else {
                    $sql = "DELETE FROM `rating` WHERE `rid`=?d AND `rtype`=?s AND `widget`=?s AND `user_id`=?d AND `value`=?d";
                    Database::get()->query($sql, $this->rid, $this->rtype, $this->widget, $user_id, $value);
                }
            
                $action = "del";
            } else {//either casting a new rating or changing the rating
                //cast new rating
                $sql = "INSERT INTO `rating` (`rid`,`rtype`,`widget`,`value`,`user_id`,`rating_source`, `time`) VALUES(?d,?s,?s,?d,?d,?s,NOW())";
                Database::get()->query($sql, $this->rid, $this->rtype, $this->widget, $value, $user_id, $_SERVER['REMOTE_ADDR']);
            
                $action = "ins";
            }
        } elseif ($this->widget == 'fivestar') {
            //Delete old ratings
            if ($user_id == 0) {//anonymous user
                $sql = "DELETE FROM `rating` WHERE `rid`=?d AND `rtype`=?s AND `widget`=?s AND `user_id`=?d AND `rating_source`=?s AND `time` >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
                Database::get()->query($sql, $this->rid, $this->rtype, $this->widget, $user_id, $_SERVER['REMOTE_ADDR']);
            } else {
                $sql = "DELETE FROM `rating` WHERE `rid`=?d AND `rtype`=?s AND `widget`=?s AND `user_id`=?d";
                Database::get()->query($sql, $this->rid, $this->rtype, $this->widget, $user_id);
            }       
         
            if ($value == 0) {//reset vote
                $action = "del";
            } else {
                //cast new rating
                $sql = "INSERT INTO `rating` (`rid`,`rtype`,`widget`,`value`,`user_id`, `rating_source`, `time`) VALUES(?d,?s,?s,?d,?d,?s,NOW())";
                Database::get()->query($sql, $this->rid, $this->rtype, $this->widget, $value, $user_id, $_SERVER['REMOTE_ADDR']);
                
                $action = "ins";
            }
        }
        
        //update cache table records for this resource
        $this->updateCache();
        
        return $action;
    }
    
    /**
     * Update caching table
     */
    private function updateCache() {
        if ($this->widget == 'up_down') {
            $this->updateUpCache();
            $this->updateDownCache();
        } elseif ($this->widget == 'fivestar') {
            $this->updateFivestarCache();
        } elseif ($this->widget == 'thumbs_up') {
            $this->updateThumbsUpCache();
        }
    }
    
    /**
     * Update caching table for positive ratings (vote up down widget)
     */
    private function updateUpCache() {
        $sql = "DELETE FROM `rating_cache` WHERE `rtype`=?s AND `rid`=?d AND `tag`=?s";
        Database::get()->query($sql, $this->rtype, $this->rid, 'up');
        
        $sql = "SELECT COUNT(`rate_id`) as `c` FROM `rating` WHERE `rtype`=?s AND `rid`=?d AND `widget` = ?s AND `value`=?d";
        $res = Database::get()->querySingle($sql, $this->rtype, $this->rid, $this->widget, 1);
        
        $sql = "INSERT INTO `rating_cache` (`rid`,`rtype`,`value`, `count`, `tag`) VALUES(?d,?s,?d,?d,?s)";
        Database::get()->query($sql, $this->rid, $this->rtype, $res->c, $res->c, 'up');
    }
    
    /**
     * Update caching table for negative ratings (vote up down widget)
     */
    private function updateDownCache() {
        $sql = "DELETE FROM `rating_cache` WHERE `rtype`=?s AND `rid`=?d AND `tag`=?s";
        Database::get()->query($sql, $this->rtype, $this->rid, 'down');
        
        $sql = "SELECT COUNT(`rate_id`) as `c` FROM `rating` WHERE `rtype`=?s AND `rid`=?d AND `widget` = ?s AND `value`=?d";
        $res = Database::get()->querySingle($sql, $this->rtype, $this->rid, $this->widget, -1);
        
        $sql = "INSERT INTO `rating_cache` (`rid`,`rtype`,`value`, `count`, `tag`) VALUES(?d,?s,?d,?d,?s)";
        Database::get()->query($sql, $this->rid, $this->rtype, $res->c, $res->c, 'down');
    }
    
    /**
     * Update caching table for fivestar widget
     */
    private function updateFivestarCache() {
        $sql = "DELETE FROM `rating_cache` WHERE `rtype`=?s AND `rid`=?d AND `tag`=?s";
        Database::get()->query($sql, $this->rtype, $this->rid, $this->widget);
        
        $sql = "SELECT COUNT(`rate_id`) as `c`, AVG(`value`) as `avg` FROM `rating` WHERE `rtype`=?s AND `rid`=?d AND `widget` = ?s";
        $res = Database::get()->querySingle($sql, $this->rtype, $this->rid, $this->widget);
        
        if ($res->c != 0) {
            $sql = "INSERT INTO `rating_cache` (`rid`,`rtype`,`value`, `count`, `tag`) VALUES(?d,?s,?f,?d,?s)";
            Database::get()->query($sql, $this->rid, $this->rtype, $res->avg, $res->c, $this->widget);
        }
    }
    
    /**
     * Update caching table for thumbs up widget
     */
    private function updateThumbsUpCache() {
        $sql = "DELETE FROM `rating_cache` WHERE `rtype`=?s AND `rid`=?d AND `tag`=?s";
        Database::get()->query($sql, $this->rtype, $this->rid, $this->widget);
    
        $sql = "SELECT COUNT(`rate_id`) as `c` FROM `rating` WHERE `rtype`=?s AND `rid`=?d AND `widget` = ?s AND `value`=?d";
        $res = Database::get()->querySingle($sql, $this->rtype, $this->rid, $this->widget, 1);
    
        $sql = "INSERT INTO `rating_cache` (`rid`,`rtype`,`value`, `count`, `tag`) VALUES(?d,?s,?d,?d,?s)";
        Database::get()->query($sql, $this->rid, $this->rtype, $res->c, $res->c, $this->widget);
    }
    
    /**
     * Get positive ratings for a resource (vote up down widget)
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
     * Get negative rating for a resource (vote up down widget)
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
     * Get fivestar rating for a resource (fivestar widget)
     * @return int
     */
    public function getFivestarRating() {
        $sql = "SELECT `value` FROM `rating_cache` WHERE `rid`=?d AND `rtype`=?s AND `tag`=?s";
        $res = Database::get()->querySingle($sql, $this->rid, $this->rtype, $this->widget);
        if (is_object($res)) {
            return round($res->value,1);
        } else {
            return -1;
        }
    }
    
    /**
     * Get rating for a resource (thumbs up widget)
     * @return int
     */
    public function getThumbsUpRating() {
        $sql = "SELECT `value` FROM `rating_cache` WHERE `rid`=?d AND `rtype`=?s AND `tag`=?s";
        $res = Database::get()->querySingle($sql, $this->rid, $this->rtype, $this->widget);
        if (is_object($res)) {
            return $res->value;
        } else {
            return 0;
        }
    }
    
    /**
     * check if a user has rated the resource
     * @param int the user id
     * @return false if user hasn't rated, rating value otherwise
     */
    public function userHasRated($user_id) {
        if ($user_id == 0) {//anonymous users
            $sql = "SELECT `value` FROM `rating` WHERE `rid`=?d AND `rtype`=?s AND `widget` = ?s AND `user_id`=?d AND `rating_source`=?s AND `time` >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
            $res = Database::get()->querySingle($sql, $this->rid, $this->rtype, $this->widget, $user_id, $_SERVER['REMOTE_ADDR']);
        } else {
            $sql = "SELECT `value` FROM `rating` WHERE `rid`=?d AND `rtype`=?s AND `widget` = ?s AND `user_id`=?d";
            $res = Database::get()->querySingle($sql, $this->rid, $this->rtype, $this->widget, $user_id);
        }
        
        if ($res) {
            return $res->value;
        } else {
            return false;
        }
    }
    
    /**
     * get user rating (fivestar widget)
     * @param int the user id
     * @return int
     */
    public function getFivestarUserRating($user_id) {
        if ($user_id == 0) {//anonymous user
            $sql = "SELECT `value` FROM `rating` WHERE `rid`=?d AND `rtype`=?s AND `widget` = ?s AND `user_id`=?d AND `rating_source`=?s AND `time` >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
            $res = Database::get()->querySingle($sql, $this->rid, $this->rtype, 'fivestar', $user_id, $_SERVER['REMOTE_ADDR']);
        } else {
            $sql = "SELECT `value` FROM `rating` WHERE `rid`=?d AND `rtype`=?s AND `widget` = ?s AND `user_id`=?d";
            $res = Database::get()->querySingle($sql, $this->rid, $this->rtype, 'fivestar', $user_id);
        }
        
        return round($res->value,1);
    }
    
    public function put($isEditor, $uid, $courseId) {
        global $langUserHasRated, $langRatingVote, $langRatingVotes, $langRatingAverage, $langRateIt, $urlServer;
        
        $this->rating_add_js();
        
        if ($this->widget == 'up_down') {
        
            $out = "<div class=\"rating\">";
            
            $onclick_up = $onclick_down = "";
            
            if (!is_null($isEditor)) {
                //disable icons when user hasn't permission to vote
                if (Rating::permRate($isEditor, $uid, $courseId, $this->rtype)) {
                    $onclick_up = "onclick=\"Rate('".$this->widget."',".$this->rid.",'".$this->rtype."',1,'".$urlServer."modules/rating/rate.php')\"";
                    $onclick_down = "onclick=\"Rate('".$this->widget."',".$this->rid.",'".$this->rtype."',-1,'".$urlServer."modules/rating/rate.php')\"";
                }
            } else { //ratings in personal blog
                if (isset($_SESSION['uid'])) {
                    $onclick_up = "onclick=\"Rate('".$this->widget."',".$this->rid.",'".$this->rtype."',1,'".$urlServer."modules/rating/rate_perso_blog.php')\"";
                    $onclick_down = "onclick=\"Rate('".$this->widget."',".$this->rid.",'".$this->rtype."',-1,'".$urlServer."modules/rating/rate_perso_blog.php')\"";
                }
            }
            
            $has_rated = $this->userHasRated($uid);
            if ($has_rated !== false) {
                $value = $has_rated;
                $has_rated = true;
                if ($value == 1) {
                    $img_up = 'thumbs_up_active.png';
                    $img_down = 'thumbs_down_inactive.png';
                } elseif ($value == -1) {
                    $img_up = 'thumbs_up_inactive.png';
                    $img_down = 'thumbs_down_active.png';
                }
            } else {
                $has_rated = false;
                $img_up = 'thumbs_up_inactive.png';
                $img_down = 'thumbs_down_inactive.png';
            }
            
            $out .= "<img id=\"rate_".$this->rid."_img_up\" src=\"".$urlServer."modules/rating/".$img_up."\" ".$onclick_up."/>&nbsp;";
            $out .= "<span id=\"rate_".$this->rid."_up\">".$this->getUpRating()."</span>&nbsp;&nbsp;";
            $out .= "<img id=\"rate_".$this->rid."_img_down\" src=\"".$urlServer."modules/rating/".$img_down."\" ".$onclick_down."/>&nbsp;";
            $out .= "<span id=\"rate_".$this->rid."_down\">".$this->getDownRating()."</span>";
            $out .= "<div class=\"smaller\" id=\"rate_msg_".$this->rid."\">";
            
            if ($has_rated) {
                $out .= $langUserHasRated;
            }
            
            $out .= "</div>";
            $out .= "</div>";
            
        } elseif ($this->widget == 'thumbs_up') {
            $out = "<div class=\"rating\">";
            
            $onclick_up = "";
            
            //disable icons when user hasn't permission to vote
            if (Rating::permRate($isEditor, $uid, $courseId, $this->rtype)) {
                $onclick_up = "onclick=\"Rate('".$this->widget."',".$this->rid.",'".$this->rtype."',1,'".$urlServer."modules/rating/rate.php')\"";
            }
            
            $has_rated = $this->userHasRated($uid);
            if ($has_rated !== false) {
                $has_rated = true;
                $img_up = 'thumbs_up_active.png';
            } else {
                $has_rated = false;
                $img_up = 'thumbs_up_inactive.png';
            }
            
            $out .= "<img id=\"rate_".$this->rid."_img\" src=\"".$urlServer."modules/rating/".$img_up."\" ".$onclick_up."/>&nbsp;";
            $out .= "<span id=\"rate_".$this->rid."_up\">".$this->getThumbsUpRating()."</span>";
            $out .= "<div class=\"smaller\" id=\"rate_msg_".$this->rid."\">";
            
            if ($has_rated) {
                $out .= $langUserHasRated;
            }
            
            $out .= "</div>";
            $out .= "</div>";
        } elseif ($this->widget == 'fivestar') {
            $out = "<div class=\"rating\">";
            
            $num_ratings = $this->getRatingsNum();
            
            if (Rating::permRate($isEditor, $uid, $courseId, $this->rtype)) {
                
                $avg = $this->getFivestarRating();
                $avg_datavalue = 'data-rateit-value="'.$avg.'" data-rateit-ispreset="true" data-rateit-readonly="true"';

                $out .= '';

                $out .= '<div class="rateit" id="rateit-avg-'.$this->rtype.'-'.$this->rid.'" '.$avg_datavalue.'></div>';

                $out .= '<div id="rateit-info-'.$this->rtype.'-'.$this->rid.'" class="rateit-info">';

                if ($num_ratings['fivestar'] != 0) {
                    $out .= '<small class="text-muted">&nbsp;('.$avg.')</small>';
                }

                if ($num_ratings['fivestar'] == 1) {
                    $out .= '<small class="text-muted">&nbsp;&nbsp;|&nbsp;&nbsp;'.$num_ratings['fivestar'].$langRatingVote.'&nbsp;&nbsp;|&nbsp;&nbsp;</small>';
                } else {
                    $out .= '<small class="text-muted">&nbsp;&nbsp;|&nbsp;&nbsp;'.$num_ratings['fivestar'].$langRatingVotes.'&nbsp;&nbsp;|&nbsp;&nbsp;</small>';
                }

                $out .= '</div><span id="rateitwrapdiv-'.$this->rtype.'-'.$this->rid.'" class="ratestar"> <a href="javascript:void(0)" data-toggle="tooltip" data-placement="top" title="'.$langRateIt.'"><span class="fa fa-plus-circle"></span></a></span>';

                /* *** Original ***
                $out .= '<div id="sdf rateit-info-'.$this->rtype.'-'.$this->rid.'">';

                if ($num_ratings['fivestar'] != 0) {
                    $out .= $langRatingAverage.$avg.', ';
                }
                
                if ($num_ratings['fivestar'] == 1) {
                    $out .= $num_ratings['fivestar'].$langRatingVote.'</div>';
                } else {
                    $out .= $num_ratings['fivestar'].$langRatingVotes.'</div>';
                }*/
                
                //$out .= '<div id="rateitwrapdiv-'.$this->rtype.'-'.$this->rid.'" ><a href="javascript:void(0)">'.$langRateIt.'</a></div>';

                
                $userRating = "";
                if ($this->userHasRated($uid) !== false) {
                    $userRating = 'data-rateit-value="'.$this->getFivestarUserRating($uid).'"';
                }
                $out .= '<div class="hideratewidget" id="rateitwidgetdiv-'.$this->rtype.'-'.$this->rid.'">';
                $out .= '<div class="rateit" id="rateit-'.$this->rtype.'-'.$this->rid.'" '.$userRating.'></div>';
                $out .= '</div>';
                
                $out .= '<script type="text/javascript">';
                $out .= ' $("#rateitwrapdiv-'.$this->rtype.'-'.$this->rid.'").click(function() {$("#rateitwidgetdiv-'.$this->rtype.'-'.$this->rid.'").toggle()});';
                $out .= ' $("#rateit-'.$this->rtype.'-'.$this->rid.'").bind(\'rated\', function (event, value) { 
                    $.ajax({
                         url: \''.$urlServer.'modules/rating/rate.php\',
                         data: { rtype: "'.$this->rtype.'", rid: '.$this->rid.', widget: "'.$this->widget.'",value: value }, 
                         type: \'GET\',
                         success: function (data) {
                             response = JSON.parse(data);
                             $("#rateit-info-'.$this->rtype.'-'.$this->rid.'").html(response[0]);
                             $("#rateit-avg-'.$this->rtype.'-'.$this->rid.'").rateit("value",response[1]);
                         },
                     });
                });';
                
                $out .= ' $("#rateit-'.$this->rtype.'-'.$this->rid.'").bind(\'reset\', function (event, value) {
                    $.ajax({
                         url: \''.$urlServer.'modules/rating/rate.php\',
                         data: { rtype: "'.$this->rtype.'", rid: '.$this->rid.', widget: "'.$this->widget.'",value: 0 }, 
                         type: \'GET\',
                         success: function (data) {
                             response = JSON.parse(data);
                             $("#rateit-info-'.$this->rtype.'-'.$this->rid.'").html(response[0]);
                             $("#rateit-avg-'.$this->rtype.'-'.$this->rid.'").rateit("value",response[1]);
                         },
                     });
                });';
                $out .= '</script>';
                
            } else {
                $avg_datavalue = "";
                if ($num_ratings['fivestar'] != 0) {
                    $avg = $this->getFivestarRating();
                    $avg_datavalue = 'data-rateit-value="'.$avg.'"';
                }
                
                $out .= '<div class="rateit" '.$avg_datavalue.' data-rateit-ispreset="true" data-rateit-readonly="true"></div>';
                $out .= '<div id="rateit-info-'.$this->rtype.'-'.$this->rid.'">';

                if ($num_ratings['fivestar'] != 0) {
                    $out .= '<small class="text-muted">&nbsp;('.$avg.') &nbsp;&nbsp;</small>';
                }

                if ($num_ratings['fivestar'] == 1) {
                    $out .= '<small class="text-muted">&nbsp;&nbsp;|&nbsp;&nbsp;'.$num_ratings['fivestar'].$langRatingVote.'hg </small></div>';
                } else {
                    $out .= '<small class="text-muted">&nbsp;&nbsp;|&nbsp;&nbsp;'.$num_ratings['fivestar'].$langRatingVotes.'</small></div>';
                }
            }
            
            $out .= "</div>";
        }
        
        return $out;
    }
    
    /**
     * Delete all ratings of a resource
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
    public static function permRate($isEditor, $uid, $courseId, $rtype) {
        
        if ($rtype == 'course') {
            if ((course_status($courseId) == COURSE_OPEN) AND (setting_get(SETTING_COURSE_ANONYMOUS_RATING_ENABLE, $courseId) == 1)) {
                return true;
            }
        }
        
        global $session;
        if (!$session->status) {//anonymous (this rule is checked after previous check on anonymous users being allowed to rate course home)
            return false;
        }
        
        if ($isEditor) {//teacher is always allowed to rate
        	return true;
        } else {
        	//students allowed to rate
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
