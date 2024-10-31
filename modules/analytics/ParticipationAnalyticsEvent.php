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

class ParticipationAnalyticsEvent extends Event {

    const LOGINRECORDED = 'loginrecorded';
    const HITRECORDED = 'hitrecorded';
    const DURATIONRECORDED = 'durationrecorded';

    public function __construct() {
        parent::__construct();

        $this->on(self::LOGINRECORDED, function ($data) {
            if($this->exists) {
                $this->context['triggeredModule'] = $data->element_type;
                $this->context['course_id'] = $data->course_id;
                $this->context['user_id'] = $data->uid;

                foreach ($this->elements as $element) {
                    $record = Database::get()->querySingle("SELECT id, value FROM user_analytics WHERE 
                            user_id = ?d
                            AND analytics_element_id = ?d
                            AND DATE(`updated`) = CURDATE()", $this->context['user_id'], $element->id);

                    if ($record) {
                        $id = $record->id;
                        $value = $record->value;

                        $this->updateValue($id, $value);
                    } else {
                        $user_id = $this->context['user_id'];
                        $analytics_element_id = $element->id;
                        $value = 1;
                        $time = date("Y-m-d H:i:s");
                        $this->insertValue($user_id, $analytics_element_id, $value, $time);
                    }
                }
            } else {
                $course_id = $data->course_id;
                $analytics_element_id = $data->analytics_element_id;

                $hit_records = Database::get()->queryArray("SELECT user_id, last_update FROM actions_daily WHERE 
                    course_id = ?d
                    AND day >= ?t
                    AND day <=?t
                    group by day, user_id", $course_id, $data->start_date, $data->end_date);

                foreach ($hit_records as $hit_record) {
                    $this->insertValue($hit_record->user_id, $analytics_element_id, 1, $hit_record->last_update);
                }
            }
        });

        $this->on(self::HITRECORDED, function ($data) {

            if($this->exists) {
                $this->context['triggeredModule'] = $data->element_type;
                $this->context['course_id'] = $data->course_id;
                $this->context['user_id'] = $data->uid;

                $record = Database::get()->querySingle("SELECT sum(hits) as value FROM actions_daily WHERE 
                    user_id = ?d
                    AND course_id = ?d
                    AND day = CURDATE()", $this->context['user_id'], $this->context['course_id']);

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

                $hit_records = Database::get()->queryArray("SELECT user_id, last_update, sum(hits) as value FROM actions_daily WHERE 
                    course_id = ?d
                    AND day >= ?t
                    AND day <=?t
                    group by day, user_id", $course_id, $data->start_date, $data->end_date);


                foreach ($hit_records as $hit_record) {
                    $this->insertValue($hit_record->user_id, $analytics_element_id, $hit_record->value, $hit_record->last_update);
                }
            }
        });

        $this->on(self::DURATIONRECORDED, function ($data) {
            if($this->exists) {
                $this->context['triggeredModule'] = $data->element_type;
                $this->context['course_id'] = $data->course_id;
                $this->context['user_id'] = $data->uid;

                $record = Database::get()->querySingle("SELECT sum(duration) as value FROM actions_daily WHERE 
                    user_id = ?d
                    AND course_id = ?d
                    AND day = CURDATE()", $this->context['user_id'], $this->context['course_id']);

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

                $hit_records = Database::get()->queryArray("SELECT user_id, last_update, sum(duration) as value FROM actions_daily WHERE 
                    course_id = ?d
                    AND day >= ?t
                    AND day <=?t
                    group by day, user_id", $course_id, $data->start_date, $data->end_date);


                foreach ($hit_records as $hit_record) {
                    $this->insertValue($hit_record->user_id, $analytics_element_id, $hit_record->value, $hit_record->last_update);
                }
            }
        });
    }
}
