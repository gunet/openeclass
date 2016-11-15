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

class LearningPathEvent extends BasicEvent {
    
    const ACTIVITY = 'learning path';
    const UPDPROGRESS = 'learning-path-accessed';
    
    public function __construct() {
        parent::__construct();
        
        $this->on(self::UPDPROGRESS, function($data) {
            $threshold = 0;
            
            // fetch grade from DB and use it as threshold
            $subm = Database::get()->querySingle("SELECT (SUM(raw)/SUM(lump.scoreMax) * 100.0) as grade "
                    . " FROM lp_user_module_progress lump "
                    . " JOIN lp_learnPath lp ON (lp.learnPath_id = lump.learnPath_id) "
                    . " WHERE lump.user_id = ?d "
                    . " AND lump.learnPath_id = ?d "
                    . " AND lp.course_id = ?d", $data->uid, $data->resource, $data->courseId);
            if ($subm && floatval($subm->grade) > 0) {
                $threshold = floatval($subm->grade);
            }
            
            $this->setEventData($data);
            $this->context['threshold'] = $threshold;
            $this->emit(parent::PREPARERULES);
        });
    }
    
}
