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

require_once 'Event.php';

class CommentsAnalyticsEvent extends Event {

    const WALLPOSTCOMMENT = 'wallpostcomment';
    const BLOGPOSTCOMMENT = 'blogpostcomment';
    const COURSECOMMENT = 'coursecomment';
    //const COMMENTSEVENT = 'commentsevent';

    public function __construct() {
        parent::__construct();

        $handle = function ($data) {
            if($this->exists) {
                $this->context['triggeredModule'] = $data->element_type;
                $this->context['course_id'] = $data->course_id;
                $this->context['user_id'] = $data->uid;
                if ($this->context['triggeredModule'] == ANALYTICS_BLOGCOMMENTS)
                    $record = Database::get()->querySingle("SELECT count(c.id) as value FROM comments as c 
                        INNER JOIN blog_post as bp on c.rid = bp.id WHERE 
                        c.rtype = 'blogpost'
                        AND c.user_id = ?d
                        AND bp.course_id = ?d
                        AND DATE(c.time) = CURDATE()", $this->context['user_id'], $this->context['course_id']);
                else if ($this->context['triggeredModule'] == ANALYTICS_COURSECOMMENTS)
                    $record = Database::get()->querySingle("SELECT count(id) as value FROM comments WHERE 
                        rtype = 'course'
                        AND user_id = ?d
                        AND rid = ?d
                        AND DATE(time) = CURDATE()", $this->context['user_id'], $this->context['course_id']);
                else if ($this->context['triggeredModule'] == ANALYTICS_WALLCOMMENTS)
                    $record = Database::get()->querySingle("SELECT count(c.id) as value FROM comments as c 
                        INNER JOIN wall_post as wp on c.rid = wp.id WHERE 
                        c.rtype = 'wallpost'
                        AND c.user_id = ?d
                        AND wp.course_id = ?d
                        AND DATE(c.time) = CURDATE()", $this->context['user_id'], $this->context['course_id']);

                $this->context['value'] = $record->value;

                foreach ($this->elements as $element) {
                    $record = Database::get()->querySingle("SELECT id, value FROM user_analytics WHERE 
                            user_id = ?d
                            AND analytics_element_id = ?d
                            AND DATE(`updated`) = CURDATE()", $this->context['user_id'], $element->id);
                    if ($record) {
                        $id = $record->id;
                        $value = $this->context['value'];
                        $this->updateValue($id, $value);
                    } else {
                        $user_id = $this->context['user_id'];
                        $analytics_element_id = $element->id;
                        $value = $this->context['value'];
                        $time = date("Y-m-d H:i:s");
                        $this->insertValue($user_id, $analytics_element_id, $value, $time);
                    }
                }
            } else {
                $course_id = $data->course_id;
                $analytics_element_id = $data->analytics_element_id;
                $element_type = $data->element_type;
                if ($element_type == ANALYTICS_BLOGCOMMENTS)
                    $comments_records = Database::get()->queryArray("SELECT c.user_id, DATE(c.time), count(c.id) as value 
                        FROM comments as c INNER JOIN blog_post as bp  ON c.rid = bp.id WHERE 
                            c.rtype = 'blogpost'
                            AND bp.course_id = ?d
                            AND DATE(c.time) >= ?t
                            AND DATE(c.time) <=?t
                            group by DATE(c.time), c.user_id", $course_id, $data->start_date, $data->end_date);
                else if ($element_type == ANALYTICS_COURSECOMMENTS)
                    $comments_records = Database::get()->queryArray("SELECT user_id, DATE(time), count(id) as value 
                        FROM comments WHERE 
                            rtype = 'course'
                            AND rid = ?d
                            AND DATE(time) >= ?t
                            AND DATE(time) <=?t
                            group by DATE(time), user_id", $course_id, $data->start_date, $data->end_date);
                else if ($element_type == ANALYTICS_WALLCOMMENTS)
                    $comments_records = Database::get()->queryArray("SELECT c.user_id, DATE(c.time), count(c.id) as value 
                        FROM comments as c INNER JOIN wall_post as wp  ON c.rid = wp.id WHERE 
                            c.rtype = 'wallpost'
                            AND wp.course_id = ?d
                            AND DATE(c.time) >= ?t
                            AND DATE(c.time) <=?t
                            group by DATE(c.time), c.user_id", $course_id, $data->start_date, $data->end_date);

                foreach ($comments_records as $comment_record) {
                    $this->insertValue($comment_record->user_id, $analytics_element_id, $comment_record->value, $comment_record->time);
                }
            }
        };

        $this->on(self::WALLPOSTCOMMENT, $handle);
        $this->on(self::BLOGPOSTCOMMENT, $handle);
        $this->on(self::COURSECOMMENT, $handle);
    }
}
