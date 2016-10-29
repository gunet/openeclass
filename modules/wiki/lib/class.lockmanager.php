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
 * This class implements the Locking Mechanism
 */

class LockManager {
    
    //time period before a lock expires
    var $lock_duration = 305; //5-minutes lock + 5 seconds safety interval
    //time period for which a lock is considered alive before an update is 
    var $keep_lock_alive_duration = 60;
    //current time
    var $curr_time;
    
    /**
     * Constructor
     */
    public function __construct() {
    	$this->curr_time = time();
    }
    
    /**
     * Check if a wiki page is locked
     * @param string page_title the title of the wiki page
     * @param int wiki_id the id of the wiki
     * @return boolean 
     */
    function isLocked($page_title, $wiki_id) {
        $sql = "SELECT COUNT(*) as c FROM wiki_locks "
               ."WHERE ptitle = ?s "
               ."AND wiki_id = ?d "
               ."AND (?d - unix_timestamp(ltime_created) <= ?d) "
               ."AND (?d - unix_timestamp(ltime_alive) <= ?d)"
        ;
        
        $result = Database::get()->querySingle($sql, $page_title, $wiki_id, $this->curr_time, $this->lock_duration, $this->curr_time, $this->keep_lock_alive_duration);
        
        if ($result->c > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Lock a wiki page
     * @param page_title the title of the wiki page
     * @param wiki_id the wiki id
     * @param uid the lock requester user id
     * @return boolean
     */
    function getLock($page_title, $wiki_id, $uid) {
        //every time a user request a new lock delete expired locks
        $this->releaseExpiredLocks();
        
        if (!$this->isLocked($page_title, $wiki_id)) { //page not locked, so add a new lock
            $sql = "INSERT INTO wiki_locks (ptitle, wiki_id, uid, ltime_created, ltime_alive) "
                   ."VALUES(?s,?d,?d,FROM_UNIXTIME(?d),FROM_UNIXTIME(?d))"
            ;
            
            Database::get()->query($sql, $page_title, $wiki_id, $uid, $this->curr_time, $this->curr_time);
            
            return true;
        } else {
            if ($this->getLockOwner($page_title, $wiki_id) == $uid) { 
                $sql = "UPDATE wiki_locks "
                       ."SET ltime_created = FROM_UNIXTIME(?d), "
                       ."ltime_alive = FROM_UNIXTIME(?d)"
                       ."WHERE ptitle = ?s "
                       ."AND wiki_id = ?d"
                ;
                
                Database::get()->query($sql, $this->curr_time, $this->curr_time, $page_title, $wiki_id);
                
                return true;
            } else {
                return false;
            }
        }
    }
    
    /**
     * Update lock alive timestamp after ajax polling
     * @param string page_title the title of the wiki page
     * @param int wiki_id the id of the wiki
     * @param uid the lock requester user id
     * @return boolean
     */
    function alive($page_title, $wiki_id, $uid) {
        if ($this->getLockOwner($page_title, $wiki_id) == $uid) {
            $sql = "UPDATE wiki_locks "
                   ."SET ltime_alive = FROM_UNIXTIME(?d)"
                   ."WHERE ptitle = ?s "
                   ."AND wiki_id = ?d"
            ;
            
            Database::get()->query($sql, $this->curr_time, $page_title, $wiki_id);
        }
    }
    
    /**
     * Release a page lock
     * @param page_title the title of the wiki page
     * @param wiki_id the wiki id
     */
    function releaseLock($page_title, $wiki_id) {
        $sql = "DELETE FROM wiki_locks "
               ."WHERE ptitle = ?s "
               ."AND wiki_id = ?d"
        ;
        
        Database::get()->query($sql, $page_title, $wiki_id);
    }
    
    /**
     * Returns the owner of a valid wiki page lock if one exists
     * @param page_title the title of the wiki page
     * @param wiki_id the wiki id
     * @return int the lock owner id or -1 if resource not locked
     */
    function getLockOwner($page_title, $wiki_id) {
        $sql = "SELECT uid "
               ."FROM wiki_locks "
               ."WHERE ptitle = ?s "
               ."AND wiki_id = ?d "
               ."AND (?d - unix_timestamp(ltime_created) <= ?d)"
               ."AND (?d - unix_timestamp(ltime_alive) <= ?d)" 
        ;
        
        $result = Database::get()->querySingle($sql, $page_title, $wiki_id, $this->curr_time, $this->lock_duration, $this->curr_time, $this->keep_lock_alive_duration);
        
        if (is_object($result)) {
            return $result->uid;
        } else {//no valid lock found
            return -1;
        }
    }
    
    /**
     * Disable lock alive timestamp when js is disabled
     * @param string page_title the title of the wiki page
     * @param int wiki_id the id of the wiki
     * @param uid the lock requester user id
     */
    function nojslock($page_title, $wiki_id, $uid) {
        if($this->getLockOwner($page_title, $wiki_id) == $uid) {
            $sql = "UPDATE wiki_locks "
                   ."SET ltime_alive = FROM_UNIXTIME(UNIX_TIMESTAMP(ltime_created) + ?d) "
                   ."WHERE ptitle = ?s "
                   ."AND wiki_id = ?d"
            ;
            
            Database::get()->query($sql, $this->lock_duration, $page_title, $wiki_id);
        }
    }
    
    /**
     * Release all expired locks
     */
    function releaseExpiredLocks() {
        $sql = "DELETE FROM wiki_locks "
               ."WHERE (?d - unix_timestamp(ltime_created) > ?d) "
               ."OR (?d - unix_timestamp(ltime_alive) > ?d)"
        ;
        
        Database::get()->query($sql, $this->curr_time, $this->lock_duration, $this->curr_time, $this->keep_lock_alive_duration);
    }
    
    
} 


