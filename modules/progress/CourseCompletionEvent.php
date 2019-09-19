<?php

/* ========================================================================
 * Open eClass 
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2019  Greek Universities Network - GUnet
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

require_once 'BasicTerminalEvent.php';
require_once 'process_functions.php';

class CourseCompletionEvent extends BasicTerminalEvent {
    
    const ACTIVITY = 'coursecompletiongrade';
    const COMPLCRITCHANGE = 'coursecompletion-completedcriteria-changed';
    
    public function __construct() {
        parent::__construct();
        
        $this->on(self::COMPLCRITCHANGE, function($data) {
            $threshold = 0;
            
            // fetch current progress and use it as threshold
            $course_completion = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND bundle = -1 AND active = 1", $data->courseId);
            if ($course_completion && isset($course_completion->id) && $course_completion->id > 0) {
                $percentage = get_cert_percentage_completion('badge', $course_completion->id);
                if ($percentage && floatval($percentage) > 0) {
                    $threshold = floatval($percentage);
                }
            }

            $this->setEventData($data);
            $this->context['threshold'] = $threshold;
            $this->emit(parent::PREPARERULES);
        });
    }
    
}
