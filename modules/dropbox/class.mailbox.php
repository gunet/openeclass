<?php

/* ========================================================================
 * Open eClass 3.0
* E-learning and Course Management System
* ========================================================================
* Copyright 2003-2013  Greek Universities Network - GUnet
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
 * This class represents a Mailbox
 */
Class Mailbox {
    //mailbox context
    var $uid;
    var $courseId;
    
    /**
     * Constructor
     * @param user_id the id of the user
     * @param course_id the id of the course in case of a course mailbox
     */
    public function __construct($user_id, $course_id) {
        $this->uid = $user_id;
        $this->courseId = $course_id;
    }
    
    /**
     * Get the number of unread messages of a user
     * @return int
     */
    public function unreadMsgsNumber() {
        $sql = "SELECT COUNT(DISTINCT `msg_id`) as `unread_count`
                FROM `dropbox_index`, `course_module`, `dropbox_msg`
                WHERE `dropbox_index`.`msg_id` = `dropbox_msg`.`id`
                AND (
                     (
                      `dropbox_msg`.`course_id` = `course_module`.`course_id`
                      AND `course_module`.`module_id` = ?d
                      AND `course_module`.`visible` = ?d
                     ) 
                     OR 
                      `dropbox_msg`.`course_id` = ?d
                    )
                AND `dropbox_index`.`is_read` = ?d 
                AND `dropbox_index`.`recipient_id` = ?d
                AND `dropbox_index`.`deleted` = ?d";
        return Database::get()->querySingle($sql, MODULE_ID_DROPBOX, 1, 0, 0, $this->uid, 0)->unread_count;
    }
    
    /**
     * Get the messages of a user's inbox
     * @return Msgs objects
     */
    public function getInboxMsgs() {
        $msgs = array();
        
        if ($this->courseId == 0) {//all messages except those from courses where dropbox is inactive
            $sql = "SELECT DISTINCT `dropbox_msg`.`id`
                    FROM `dropbox_msg`,`dropbox_index`, `course_module`
                    WHERE `dropbox_msg`.`id` = `dropbox_index`.`msg_id`
                    AND (
                     (
                      `dropbox_msg`.`course_id` = `course_module`.`course_id`
                      AND `course_module`.`module_id` = ?d
                      AND `course_module`.`visible` = ?d
                     ) 
                     OR 
                      `dropbox_msg`.`course_id` = ?d
                    ) 
                    AND `dropbox_index`.`recipient_id` = ?d 
                    AND `dropbox_index`.`recipient_id` != `dropbox_msg`.`author_id`
                    AND `dropbox_index`.`deleted` = ?d
                    ORDER BY `timestamp` DESC";
            $res = Database::get()->queryArray($sql, MODULE_ID_DROPBOX, 1, 0, $this->uid, 0);
        } else {//messages in course context
            $sql = "SELECT `dropbox_msg`.`id` 
                    FROM `dropbox_msg`,`dropbox_index`
                    WHERE `dropbox_msg`.`id` = `dropbox_index`.`msg_id`
                    AND `dropbox_index`.`recipient_id` = ?d
                    AND `dropbox_msg`.`course_id` = ?d
                    AND `dropbox_index`.`recipient_id` != `dropbox_msg`.`author_id`
                    AND `dropbox_index`.`deleted` = ?d
                    ORDER BY `timestamp` DESC";
            $res = Database::get()->queryArray($sql, $this->uid, $this->courseId, 0);
        }
        
        foreach ($res as $r) {
           $msgs[] = new Msg($r->id, $this->uid, 'list_view');
        }
        
        return $msgs;
    }
    
    /**
     * Get the messages of a user's outbox
     * @return msg objects
     */
    public function getOutboxMsgs() {
        $msgs = array();
        
        if ($this->courseId == 0) {//all mesages except those from courses where dropbox is inactive
            $sql = "SELECT DISTINCT `dropbox_msg`.`id` 
                    FROM `dropbox_msg`,`dropbox_index`, `course_module`
                    WHERE `dropbox_msg`.`id` = `dropbox_index`.`msg_id`
                    AND (
                     (
                      `dropbox_msg`.`course_id` = `course_module`.`course_id`
                      AND `course_module`.`module_id` = ?d
                      AND `course_module`.`visible` = ?d
                     ) 
                     OR 
                      `dropbox_msg`.`course_id` = ?d
                    ) 
                    AND `dropbox_index`.`deleted` = ?d 
                    AND `author_id` = ?d
                    AND  `dropbox_index`.`recipient_id` = ?d
                    ORDER BY `timestamp` DESC";
            $res = Database::get()->queryArray($sql, MODULE_ID_DROPBOX, 1, 0, 0, $this->uid, $this->uid);
        } else {//messages in course context
            $sql = "SELECT `dropbox_msg`.`id` 
                    FROM `dropbox_msg`,`dropbox_index` 
                    WHERE `dropbox_msg`.`id` = `dropbox_index`.`msg_id` 
                    AND `author_id` = ?d
                    AND  `dropbox_index`.`recipient_id` = ?d 
                    AND `course_id` = ?d 
                    AND `dropbox_index`.`deleted` = ?d
                    ORDER BY `timestamp` DESC";
            $res = Database::get()->queryArray($sql, $this->uid, $this->uid, $this->courseId, 0);
        }
        
        foreach ($res as $r) {
            $msgs[] = new Msg($r->id, $this->uid, 'list_view');
        }
        
        return $msgs;
    }
}