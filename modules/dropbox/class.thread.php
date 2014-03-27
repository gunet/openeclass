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

Class Thread {
    
    var $id;
    var $subject;
    var $recipients;
    //var $is_read;
    
    /**
     * Constructor
     * @param id the thread id
     */
    public function __construct($id) {
        
        $this->id = $id;
        
        $sql = "SELECT `dropbox_index`.`recipient_id`, `dropbox_msg`.`subject` 
                FROM `dropbox_msg`,`dropbox_index`
                WHERE `dropbox_msg`.`id` = `dropbox_index`.`msg_id`
                AND `dropbox_index`.`thread_id` = ?d 
                GROUP BY `dropbox_index`.`recipient_id`";
        
        $res = Database::get()->queryArray($sql, $id);
        
        foreach ($res as $r) {
            $this->recipients[] = $r->recipient_id;
        }
        
        $this->subject = $r->subject;
    }
    
    /**
     * Get the messages of a thread
     * @return msg objects
     */
    public function getMsgs() {
        $msgs = array();
        
        $sql = "SELECT DISTINCT `msg_id` FROM `dropbox_index` WHERE `thread_id` = ?d";
        $res = Database::get()->queryArray($sql, $id);
        foreach ($res as $r) {
            $msgs[] = new Msg($r->msg-id);
        }
        return $msgs;
    }
    
    /**
     * Delete thread
     * @param the user id
     */
    public function delete($uid) {
        $msgs = $this->getMsgs();
        //delete all messages of this thread
        foreach ($msgs as $msg) {
            $msg->delete($uid);
        }
    }
}