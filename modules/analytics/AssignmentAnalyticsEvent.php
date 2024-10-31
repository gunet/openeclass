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

class AssignmentAnalyticsEvent extends Event {

    const ASSIGNMENTGRADE = 'assignmentgrade';
    const ASSIGNMENTDL = 'assignmentbeforedl';

    public function __construct() {
        parent::__construct();

        $eventfunction =  function ($data) {

            if($this->exists) {
                $this->context['triggeredModule'] = $data->element_type;
                $this->context['course_id'] = $data->course_id;
                $this->context['user_id'] = $data->uid;
                $this->context['resource'] = $data->resource;

                $record = Database::get()->querySingle("SELECT s.* "
                . " FROM assignment_submit s "
                . " JOIN assignment a ON (s.assignment_id = a.id) "
                . " WHERE s.uid = ?d "
                . " AND s.assignment_id = ?d"
                . " AND a.course_id = ?d", $this->context['user_id'], $this->context['resource'], $this->context['course_id']);

                $this->context['value'] = 0;
                if ($record && floatval($record->grade) > 0) {
                    $this->context['value'] = floatval($record->grade);
                }

                // print_r($this->context);
                // print_r($this->elements);
                // die("aaa");

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
                $resource = $data->resource;

                $assignment_records = Database::get()->queryArray("SELECT s.* "
                    . " FROM assignment_submit s "
                    . " JOIN assignment a ON (s.assignment_id = a.id) "
                    . " WHERE s.assignment_id = ?d"
                    . " AND a.course_id = ?d"
                    . " AND DATE(s.submission_date) >= ?t"
                    . " AND DATE(s.submission_date) <= ?t"
                    . " ORDER BY s.grade DESC LIMIT 1", $resource, $course_id, $data->start_date, $data->end_date);

                foreach ($assignment_records as $assignment_record) {
                    $value = 0;
                    if ($assignment_record && floatval($assignment_record->grade) > 0) {
                        $value = floatval($assignment_record->grade);
                    }

                    $this->insertValue($assignment_record->uid, $analytics_element_id, $value, $assignment_record->submission_date);
                }
            }
        };

        $eventfunctiondl =  function ($data) {
            if($this->exists) {
                $this->context['triggeredModule'] = $data->element_type;
                $this->context['course_id'] = $data->course_id;
                $this->context['user_id'] = $data->uid;
                $this->context['resource'] = $data->resource;

                $recorddl = Database::get()->querySingle("SELECT s.*, a.deadline "
                . " FROM assignment_submit s "
                . " JOIN assignment a ON (s.assignment_id = a.id) "
                . " WHERE s.uid = ?d "
                . " AND s.assignment_id = ?d"
                . " AND a.course_id = ?d", $this->context['user_id'], $this->context['resource'], $this->context['course_id']);

                $value = 0;
                if ($recorddl && $recorddl->submission_date <= $recorddl->deadline ) {
                    $value = 1;
                }

                foreach ($this->elements as $element) {
                    $recorddl = Database::get()->querySingle("SELECT id, value FROM user_analytics WHERE 
                            user_id = ?d
                            AND analytics_element_id = ?d
                            AND DATE(`updated`) = CURDATE()", $this->context['user_id'], $element->id);



                    if ($recorddl) {
                        $id = $recorddl->id;

                        $this->updateValue($id, $value);
                    } else {
                        $user_id = $this->context['user_id'];
                        $analytics_element_id = $element->id;
                        $value = $value;
                        $time = date("Y-m-d H:i:s");

                        $this->insertValue($user_id, $analytics_element_id, $value, $time);
                    }
                }
            } else {
                $course_id = $data->course_id;
                $analytics_element_id = $data->analytics_element_id;
                $resource = $data->resource;

                $assignment_recordsdl = Database::get()->queryArray("SELECT s.*, a.deadline "
                    . " FROM assignment_submit s "
                    . " JOIN assignment a ON (s.assignment_id = a.id) "
                    . " WHERE s.assignment_id = ?d"
                    . " AND a.course_id = ?d"
                    . " AND DATE(s.submission_date) >= ?t"
                    . " AND DATE(s.submission_date) <= ?t"
                    . " ORDER BY s.grade DESC LIMIT 1", $resource, $course_id, $data->start_date, $data->end_date);

                foreach ($assignment_recordsdl as $assignment_recorddl) {
                    $value = 0;
                    if ($assignment_recorddl && $assignment_recorddl->submission_date < $assignment_recorddl->deadline ) {
                        $value = 1;
                    }

                    $this->insertValue($assignment_recorddl->uid, $analytics_element_id, $value, $assignment_recorddl->submission_date);
                }
            }
        };

        $this->on(self::ASSIGNMENTGRADE, $eventfunction);
        $this->on(self::ASSIGNMENTDL, $eventfunctiondl);

    }
}
