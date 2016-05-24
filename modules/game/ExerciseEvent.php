<?php

/* ========================================================================
 * Open eClass 
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2015  Greek Universities Network - GUnet
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
 * ======================================================================== 
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
            if ($eur && floatval($eur->total_score) > 0) {
                $threshold = floatval($eur->total_score);
            }
            
            $this->setEventData($data);
            $this->context['threshold'] = $threshold;
            $this->emit(parent::PREPARERULES);
        });
    }
    
}
