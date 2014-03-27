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

Class Msg {
    
    var $id;
    var $course_id;
    var $author_id;
    var $subject;
    var $body;
    var $timestamp;
    var $recipients = array();
    var $filename;
    var $real_filename;
    var $filesize;
    //user context
    var $uid;
    
    /**
     * Constructor
     * Takes either one argument to load the msg from db or multiple
     * arguments to create a new msg
     */
    public function __construct($arg1, $arg2, $arg3 = null, $arg4 = null, $arg5 = null, $arg6 = null, $arg7 = null, $arg8 = null, $arg9 = null) {
        if (func_num_args() > 2) {
            $this->uid = $arg1;
            $this->createNewMsg($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7, $arg8, $arg9);
        } else {
            $this->uid = $arg2;
            $this->loadMsgFromDB($arg1);
        }
    }
    
    /**
     * Private function that loads a Msg from DB
     * @param the msg id
     */
    private function loadMsgFromDB($id) {
        $sql = "SELECT * FROM `dropbox_message` WHERE `id` = ?d";
        $res = Database::get()->querySingle($sql, $id);
        
        $this->author_id = $res->author_id;
        $this->body = $res->body;
        $this->subject = $res->subject;
        $this->id = $id;
        $this->course_id = $res->course_id;
        $this->timestamp = $res->timestamp;
        
        $sql = "SELECT `recipient_id` FROM `dropbox_index` WHERE `msg_id` = ?d";
        $res = Database::get()->queryArray($sql, $id);
        
        foreach ($res as $r) {
            if ($r->recipient_id != $this->author_id) {
                $this->recipients[] = $r;
            }
        }
        
        $sql = "SELECT * FROM `dropbox_attachment` WHERE `msg_id` = ?d";
        $res = Database::get()->querySingle($sql, $id);
        if (is_object($res)) {
            $this->filename = $res->filename;
            $this->real_filename = $res->real_filename;
            $this->filesize = $res->filesize;
        } else {
            $this->filename = '';
            $this->real_filename = '';
            $this->filesize = 0;
        }
    }
    
    /**
     * Private function that creates a new msg
     */
    private function createNewMsg($author_id, $course_id, $subject, $body, $recipients, $filename, $real_filename, $filesize, $thread_id) {
        $this->author_id = $author_id;
        $this->course_id = $course_id;
        $this->subject = $subject;
        $this->body = $body;
        $this->recipients = $recipients;
        $this->timestamp = time();
        
        $sql = "INSERT INTO `dropbox_msg` (`author_id`, `course_id`, `subject`, `body`, `timestamp`) VALUES(?d,?d,?s,?s,?d)";
        $this->id = Database::get()->query($sql, $author_id, $course_id, $subject, $body, $this->timestamp)->lastInsertID;
        
        if (is_null($thread_id)) {
            //the thread id gets the id of the first thread message
            $thread_id = $this->id;
        }
        
        $sql = "INSERT INTO `dropbox_index` (`msg_id`,`recipient_id`, `thread_id`, `is_read`, `deleted`) VALUES (?d,?d,?d,?d,?d)";
        
        $argsarr = array();
        $argsarr[] = $this->id;
        $argsarr[] = $author_id;
        $argsarr[] = $thread_id;
        $argsarr[] = 0;
        $argsarr[] = 0;
        
        foreach ($recipients as $rec) {
            $sql .= ",(?d,?d,?d,?d,?d)";
            $argsarr[] = $this->id;
            $argsarr[] = $rec;
            $argsarr[] = $thread_id;
            $argsarr[] = 0;
            $argsarr[] = 0;
        }
        
        Database::get()->query($sql, $argsarr);
        
        if ($filename != '') {
            $this->filename = $filename;
            $this->real_filename = $real_filename;
            $this->filesize = $filesize;
            
            $sql = "INSERT INTO `dropbox_attachment` (`msg_id`, filename`, `real_filename`, `filesize`) VALUES(?d,?s,?s,?d)";
            Database::get()->query($sql, $this->id, $filename, $real_filename, $filesize);
        } else {
            $this->filename = '';
            $this->real_filename = '';
            $this->filesize = 0;
        }
    }
    
    /**
     * Mark message for deletion
     * Delete message if all users have marked it for deletion
     */
    public function delete() {
        $sql = "UPDATE `dropbox_index` 
                SET `deleted` = ?d 
                WHERE `recipient_id` = ?d 
                AND `msg_id` = ?d";
        Database::get()->query($sql, 1, $this->uid, $this->id);
        //delete msg that all recipients have marked for deletion
        $sql = "SELECT COUNT(`msg_id`) as `c` 
                FROM `dropbox_index` 
                WHERE `msg_id` = ?d 
                AND `deleted` = ?d";
        if (Database::get()->querySingle($sql, $this->id, 0)->c == 0) {
            $sql = "DELETE FROM `dropbox_msg` WHERE `id` = ?d";
            Database::get()->query($sql, $this->id);
            $sql = "DELETE FROM `dropbox_index` WHERE `id` = ?d";
            Database::get()->query($sql, $this->id);
            if ($this->course_id != 0) {//only course messages may have attachment
                if ($this->filename != '') {
                    global $dropbox_dir;
                    unlink($dropbox_dir . "/" . $this->filename);
                    $sql = "DELETE FROM `dropbox_attachment` WHERE `msg_id` = ?d";
                    Database::get()->query($sql, $this->id);
                }
            }
        }
    }
}