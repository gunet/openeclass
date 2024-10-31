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

require_once 'BasicEvent.php';

class ExerciseEvent extends BasicEvent {

    const ACTIVITY = 'exercise';
    const NEWRESULT = 'exercise-submitted';

    public function __construct() {
        parent::__construct();

        $this->on(self::NEWRESULT, function($data) {
            $threshold = 0;

            // fetch score from DB and use it as threshold
            $eur = Database::get()->querySingle("SELECT s.* "
                    . " FROM exercise_user_record s "
                    . " JOIN exercise a ON (s.eid = a.id) "
                    . " WHERE s.uid = ?d "
                    . " AND s.eid = ?d"
                    . " AND a.course_id = ?d"
                    . " AND s.attempt_status = ?d"
                    . " ORDER BY eurid DESC LIMIT 1", $data->uid, $data->resource, $data->courseId, ATTEMPT_COMPLETED);

            $objExercise = new Exercise();
            if ($eur) {
                $objExercise->read($eur->eid);
                $canonical_score = $objExercise->canonicalize_exercise_score($eur->total_score, $eur->total_weighting);
                if ($eur && $canonical_score > 0) {
                    $threshold = $canonical_score;
                }
            }

            $this->setEventData($data);
            $this->context['threshold'] = $threshold;
            $this->emit(parent::PREPARERULES);
        });
    }

}
