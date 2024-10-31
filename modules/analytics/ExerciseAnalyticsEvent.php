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

class ExerciseAnalyticsEvent extends Event {

    const EXERCISEGRADE = 'exercisegrade';

    public function __construct() {
        parent::__construct();

        $eventfunction =  function ($data) {

            if($this->exists) {
                $this->context['triggeredModule'] = $data->element_type;
                $this->context['course_id'] = $data->course_id;
                $this->context['user_id'] = $data->uid;
                $this->context['resource'] = $data->resource;

                $record = Database::get()->querySingle("SELECT s.* "
                . " FROM exercise_user_record s "
                . " JOIN exercise a ON (s.eid = a.id) "
                . " WHERE s.uid = ?d "
                . " AND s.eid = ?d"
                . " AND a.course_id = ?d"
                . " AND s.attempt_status = 1"
                . " ORDER BY total_score DESC LIMIT 1", $this->context['user_id'], $this->context['resource'], $this->context['course_id']);

                $this->context['value'] = 0;
                $objExercise = new Exercise();
                $objExercise->read($record->eid);
                $canonical_score = $objExercise->canonicalize_exercise_score($record->total_score, $record->total_weighting);
                if ($record && $canonical_score > 0) {
                    $this->context['value'] = $canonical_score;
                }

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

                $exercise_records = Database::get()->queryArray("SELECT s.* "
                    . " FROM exercise_user_record s "
                    . " JOIN exercise a ON (s.eid = a.id) "
                    . " WHERE s.eid = ?d"
                    . " AND a.course_id = ?d"
                    . " AND s.attempt_status = ?d"
                    . " AND DATE(s.record_end_date) >= ?t"
                    . " AND DATE(s.record_end_date) <= ?t"
                    . " ORDER BY s.total_score DESC LIMIT 1", $resource, $course_id, 1, $data->start_date, $data->end_date);

                foreach ($exercise_records as $exercise_record) {
                    $objExercise = new Exercise();
                    $objExercise->read($exercise_record->eid);
                    $canonical_score = $objExercise->canonicalize_exercise_score($exercise_record->total_score, $exercise_record->total_weighting);
                    $value = 0;
                    if ($exercise_record && $canonical_score > 0) {
                        $value = $canonical_score;
                    }
                    $this->insertValue($exercise_record->uid, $analytics_element_id, $value, $exercise_record->record_end_date);
                }
            }
        };
        $this->on(self::EXERCISEGRADE, $eventfunction);
    }
}
