<?php

/* ========================================================================
 * Open eClass 3.6
* E-learning and Course Management System
* ========================================================================
* Copyright 2003-2018  Greek Universities Network - GUnet
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
        if ($this->courseId == 0) { //all unread messages
             $sql = "SELECT COUNT(*) AS unread_count
                    FROM dropbox_msg
                        JOIN dropbox_index
                               ON dropbox_msg.id = dropbox_index.msg_id
                        LEFT JOIN course_module
                               ON course_module.course_id = dropbox_msg.course_id
                    WHERE course_module.module_id = ?d
                      AND course_module.visible <> 0
                      AND dropbox_index.recipient_id = ?d
                      AND dropbox_index.is_read = 0
                      AND dropbox_index.deleted = 0";
            return Database::get()->querySingle($sql, MODULE_ID_MESSAGE, $this->uid)->unread_count;
        } else { //unread messages in course context
            $sql = "SELECT COUNT(`msg_id`) as `unread_count`
                    FROM `dropbox_index`, `dropbox_msg`
                    WHERE `dropbox_index`.`msg_id` = `dropbox_msg`.`id`
                    AND `dropbox_msg`.`course_id` = ?d
                    AND `dropbox_index`.`is_read` = ?d
                    AND `dropbox_index`.`recipient_id` = ?d
                    AND `dropbox_index`.`deleted` = ?d";
            return Database::get()->querySingle($sql, $this->courseId, 0, $this->uid, 0)->unread_count;
        }
    }

    /**
     * Get the number of all messages of a user
     * @param string the type of messages (inbox or outbox)
     * @return int
     */
    public function MsgsNumber($type) {
             $sql = "SELECT COUNT(*) AS all_count
                    FROM dropbox_msg
                        JOIN dropbox_index
                               ON dropbox_msg.id = dropbox_index.msg_id
                        LEFT JOIN course_module
                               ON course_module.course_id = dropbox_msg.course_id
                    WHERE course_module.module_id = ?d
                      AND course_module.visible <> 0
                      AND dropbox_index.recipient_id = ?d
                      AND dropbox_index.deleted = 0";
        if ($type == 'inbox') {
            $sql .= " AND `dropbox_msg`.`author_id` <> ?d";
        } else {
            $sql .= " AND `dropbox_msg`.`author_id` = ?d";
        }
        return Database::get()->querySingle($sql, MODULE_ID_MESSAGE, $this->uid, $this->uid)->all_count;
    }

    /**
     * Get the messages of a user's inbox
     * @param string search keyword
     * @param int limit
     * @param int offset
     * @return Msgs objects
     */
    public function getInboxMsgs($keyword='', $limit=0, $offset=0) {
        $msgs = array();
        $args = array();

        $query_sql = $extra_sql = '';

        if (!empty($keyword)) {
            $query_sql = "AND `dropbox_msg`.`subject` LIKE concat('%', ?s, '%')";
            $args[] = $keyword;
        }
        if ($limit > 0) {
            $extra_sql = "LIMIT ?d,?d";
            $args[] = $offset;
            $args[] = $limit;
        }

        if ($this->courseId == 0) { //all messages except those from courses where dropbox is inactive
             $sql = "SELECT dropbox_msg.id
                    FROM dropbox_msg
                        JOIN dropbox_index
                               ON dropbox_msg.id = dropbox_index.msg_id
                        LEFT JOIN course_module
                               ON course_module.course_id = dropbox_msg.course_id
                    WHERE course_module.module_id = ?d
                      AND course_module.visible <> 0
                      AND dropbox_index.recipient_id = ?d
                      AND dropbox_index.recipient_id <> dropbox_msg.author_id
                      AND dropbox_index.deleted = 0 $query_sql
                    GROUP BY `dropbox_msg`.`id` ORDER BY `timestamp` DESC $extra_sql";
            $res = Database::get()->queryArray($sql, MODULE_ID_MESSAGE, $this->uid, $args);
        } else { //messages in course context
            $sql = "SELECT `dropbox_msg`.`id`
                    FROM `dropbox_msg`,`dropbox_index`
                    WHERE `dropbox_msg`.`id` = `dropbox_index`.`msg_id`
                    AND `dropbox_index`.`recipient_id` = ?d
                    AND `dropbox_msg`.`course_id` = ?d
                    AND `dropbox_index`.`recipient_id` != `dropbox_msg`.`author_id`
                    AND `dropbox_index`.`deleted` = ?d $query_sql
                    ORDER BY `timestamp` DESC $extra_sql";
            $res = Database::get()->queryArray($sql, $this->uid, $this->courseId, 0, $args);
        }

        foreach ($res as $r) {
           $msgs[] = new Msg($r->id, $this->uid, 'list_view');
        }

        return $msgs;
    }

    /**
     * Get the messages of a user's outbox
     * @param string search keyword
     * @param int limit
     * @param int offset
     * @return msg objects
     */
    public function getOutboxMsgs($keyword='', $limit=0, $offset=0) {
        $msgs = array();
        $args = array();

        $query_sql = $extra_sql = '';

        if (!empty($keyword)) {
            $query_sql = "AND `dropbox_msg`.`subject` LIKE concat('%', ?s, '%')";
            $args[] = $keyword;
        }
        if ($limit > 0) {
            $extra_sql = "LIMIT ?d,?d";
            $args[] = $offset;
            $args[] = $limit;
        }

        if ($this->courseId == 0) { //all messages except those from courses where messages is inactive
             $sql = "SELECT dropbox_msg.id
                    FROM dropbox_msg
                        JOIN dropbox_index
                               ON dropbox_msg.id = dropbox_index.msg_id
                        LEFT JOIN course_module
                               ON course_module.course_id = dropbox_msg.course_id
                    WHERE course_module.module_id = ?d
                      AND course_module.visible <> 0
                      AND dropbox_msg.author_id = ?d
                      AND dropbox_msg.author_id = dropbox_index.recipient_id
                      AND dropbox_index.deleted = 0 $query_sql
                    GROUP BY `dropbox_msg`.`id` ORDER BY `timestamp` DESC $extra_sql";
            $res = Database::get()->queryArray($sql, MODULE_ID_MESSAGE, $this->uid, $args);
        } else { //messages in course context
            $sql = "SELECT `dropbox_msg`.`id`, `dropbox_msg`.`timestamp`
                    FROM `dropbox_msg`,`dropbox_index`
                    WHERE `dropbox_msg`.`id` = `dropbox_index`.`msg_id`
                    AND `author_id` = ?d
                    AND  `dropbox_index`.`recipient_id` = ?d
                    AND `course_id` = ?d
                    AND `dropbox_index`.`deleted` = ?d $query_sql
                    ORDER BY `dropbox_msg`.`timestamp` DESC $extra_sql";
            $res = Database::get()->queryArray($sql, $this->uid, $this->uid, $this->courseId, 0, $args);
        }

        foreach ($res as $r) {
            $msgs[] = new Msg($r->id, $this->uid, 'list_view');
        }

        return $msgs;
    }
}
