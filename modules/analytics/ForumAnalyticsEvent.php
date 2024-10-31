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

class ForumAnalyticsEvent extends Event {

    const FORUMEVENT = 'forumevent';

    public function __construct() {
        parent::__construct();

        $eventfunction =  function ($data) {

            if($this->exists) {
                $this->context['triggeredModule'] = $data->element_type;
                $this->context['course_id'] = $data->course_id;
                $this->context['user_id'] = $data->uid;



                $record = Database::get()->querySingle("SELECT count(fp.id) as value "
                . " FROM forum_post fp "
                . " JOIN forum_topic ft ON (ft.id = fp.topic_id) "
                . " JOIN forum f ON (f.id = ft.forum_id) "
                . " WHERE fp.poster_id = ?d "
                . " AND f.course_id = ?d", $this->context['user_id'], $this->context['course_id']);

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

                $forum_records = Database::get()->queryArray("SELECT count(fp.id) as value, post_time as time, fp.poster_id as user_id "
                . " FROM forum_post fp "
                . " JOIN forum_topic ft ON (ft.id = fp.topic_id) "
                . " JOIN forum f ON (f.id = ft.forum_id)"
                . " WHERE f.course_id = ?d"
                . " AND DATE(post_time) >= ?t"
                . " AND DATE(post_time) <= ?t"
                . " group by DATE(post_time), fp.poster_id", $course_id, $data->start_date, $data->end_date);

                foreach ($forum_records as $forum_record) {
                    $this->insertValue($forum_record->user_id, $analytics_element_id, $forum_record->value, $forum_record->time);
                }
            }
        };

        $this->on(self::FORUMEVENT, $eventfunction);

    }
}
