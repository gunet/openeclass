<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

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
     * @brief get mailbox path
     * @return string
     */
    public function get_mailbox_path() {

        global $webDir;

        $course_code = course_id_to_code($this->courseId);
        $message_dir = $webDir . "/courses/" . $course_code . "/dropbox";

        return $message_dir;
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
                    WHERE course_module.module_id = " . MODULE_ID_MESSAGE . "
                      AND course_module.visible <> 0
                      AND dropbox_index.recipient_id = ?d
                      AND dropbox_index.is_read = 0
                      AND dropbox_index.deleted = 0";
            $cnt1 = Database::get()->querySingle($sql, $this->uid)->unread_count;
            // personal messages
            $sql = "SELECT COUNT(*) AS unread_personal_count
                    FROM dropbox_msg
                        JOIN dropbox_index
                               ON dropbox_msg.id = dropbox_index.msg_id
                    WHERE course_id = 0
                      AND dropbox_index.recipient_id = ?d
                      AND dropbox_index.is_read = 0
                      AND dropbox_index.deleted = 0";
            $cnt2 = Database::get()->querySingle($sql, $this->uid)->unread_personal_count;

            return $cnt1+$cnt2;

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
        $args_1 = array();
        $args_2 = array();

        $query_sql = $extra_sql = '';

        if (!empty($keyword)) {
            $query_sql = "AND (`dropbox_msg`.`subject` LIKE ?s
                               OR `user`.`surname` LIKE ?s
                               OR `user`.`givenname` LIKE ?s)";
            $args_1[] = '%' . $keyword . '%';
            $args_1[] = $keyword . '%';
            $args_1[] = $keyword . '%';
        }
        if ($limit > 0) {
            $extra_sql = "LIMIT ?d,?d";
            $args_2[] = $offset;
            $args_2[] = $limit;
        }

        if ($this->courseId == 0) { //all messages except those from courses where dropbox is inactive
            $sql = "(SELECT dropbox_msg.id, dropbox_msg.timestamp AS ts
                    FROM dropbox_msg
                        JOIN dropbox_index
                               ON dropbox_msg.id = dropbox_index.msg_id
                        LEFT JOIN course_module
                               ON course_module.course_id = dropbox_msg.course_id
                        JOIN user
                               ON dropbox_msg.author_id = user.id
                    WHERE course_module.module_id = ?d
                      AND course_module.visible <> 0
                      AND dropbox_index.recipient_id = ?d
                      AND dropbox_index.recipient_id <> dropbox_msg.author_id
                      AND dropbox_index.deleted = 0 $query_sql
                    GROUP BY `dropbox_msg`.`id`, dropbox_msg.timestamp) "
                    . "UNION " // include personal messages
                    . "(SELECT dropbox_msg.id, dropbox_msg.timestamp AS ts
                    FROM dropbox_msg
                        JOIN dropbox_index
                               ON dropbox_msg.id = dropbox_index.msg_id
                        JOIN user
                               ON dropbox_msg.author_id = user.id
                    WHERE course_id = 0
                      AND dropbox_index.recipient_id = ?d
                      AND dropbox_index.recipient_id <> dropbox_msg.author_id
                      AND dropbox_index.deleted = 0 $query_sql
                    GROUP BY `dropbox_msg`.`id`, dropbox_msg.timestamp) ORDER BY ts DESC $extra_sql";
            $res = Database::get()->queryArray($sql, MODULE_ID_MESSAGE, $this->uid, $args_1, $this->uid, $args_1, $args_2);
        } else { //messages in course context
            $sql = "SELECT `dropbox_msg`.`id`
                    FROM `dropbox_msg`
                        JOIN `dropbox_index`
                               ON dropbox_msg.id = dropbox_index.msg_id
                        JOIN user
                               ON dropbox_msg.author_id = user.id
                    WHERE `dropbox_index`.`recipient_id` = ?d
                      AND `dropbox_msg`.`course_id` = ?d
                      AND `dropbox_index`.`recipient_id` != `dropbox_msg`.`author_id`
                      AND `dropbox_index`.`deleted` = ?d $query_sql
                    ORDER BY `timestamp` DESC $extra_sql";
            $res = Database::get()->queryArray($sql, $this->uid, $this->courseId, 0, $args_1, $args_2);
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
        $args_1 = array();
        $args_2 = array();

        $query_sql = $extra_sql = '';

        if (!empty($keyword)) {
            $query_sql = "AND `dropbox_msg`.`subject` LIKE concat('%', ?s, '%')";
            $args_1[] = $keyword;
        }
        if ($limit > 0) {
            $extra_sql = "LIMIT ?d,?d";
            $args_2[] = $offset;
            $args_2[] = $limit;
        }

        if ($this->courseId == 0) { //all messages except those from courses where messages is inactive
            $sql = "(SELECT dropbox_msg.id, dropbox_msg.timestamp AS ts
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
                    GROUP BY `dropbox_msg`.`id`, dropbox_msg.timestamp) "
                    . "UNION " // include personal messages
                    . "(SELECT dropbox_msg.id, dropbox_msg.timestamp AS ts
                    FROM dropbox_msg
                        JOIN dropbox_index
                               ON dropbox_msg.id = dropbox_index.msg_id
                    WHERE course_id = 0
                      AND dropbox_msg.author_id = ?d
                      AND dropbox_msg.author_id = dropbox_index.recipient_id
                      AND dropbox_index.deleted = 0 $query_sql
                    GROUP BY `dropbox_msg`.`id`, dropbox_msg.timestamp) ORDER BY ts DESC $extra_sql";
            $res = Database::get()->queryArray($sql, MODULE_ID_MESSAGE, $this->uid, $args_1, $this->uid, $args_1, $args_2);


        } else { //messages in course context
            $sql = "SELECT `dropbox_msg`.`id`, `dropbox_msg`.`timestamp`
                    FROM `dropbox_msg`,`dropbox_index`
                    WHERE `dropbox_msg`.`id` = `dropbox_index`.`msg_id`
                    AND `author_id` = ?d
                    AND  `dropbox_index`.`recipient_id` = ?d
                    AND `course_id` = ?d
                    AND `dropbox_index`.`deleted` = ?d $query_sql
                    ORDER BY `dropbox_msg`.`timestamp` DESC $extra_sql";
            $res = Database::get()->queryArray($sql, $this->uid, $this->uid, $this->courseId, 0, $args_1, $args_2);
        }

        foreach ($res as $r) {
            $msgs[] = new Msg($r->id, $this->uid, 'list_view');
        }

        return $msgs;
    }
}
