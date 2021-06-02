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

class AssignmentEvent extends BasicEvent {

    const ACTIVITY = 'assignment';
    const UPGRADE = 'assignment-grade-changed';

    public function __construct() {
        parent::__construct();

        $this->on(self::UPGRADE, function($data) {
            $threshold = 0;

            // fetch grade from DB and use it as threshold
            $subm = Database::get()->querySingle("SELECT s.* "
                    . " FROM assignment_submit s "
                    . " JOIN assignment a ON (s.assignment_id = a.id) "
                    . " WHERE s.uid = ?d "
                    . " AND s.assignment_id = ?d"
                    . " AND a.course_id = ?d", $data->uid, $data->resource, $data->courseId);
            if ($subm && floatval($subm->grade) > 0) {
                $threshold = floatval($subm->grade);
            }

            $this->setEventData($data);
            $this->context['threshold'] = $threshold;
            $this->emit(parent::PREPARERULES);
        });
    }

}
