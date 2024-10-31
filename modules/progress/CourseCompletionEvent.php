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
            $course_completion = Database::get()->querySingle("SELECT id FROM badge WHERE course_id = ?d AND bundle = -1 
                                                                        AND active = 1 AND unit_id = ?d", $data->courseId, $data->unit_id);

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
