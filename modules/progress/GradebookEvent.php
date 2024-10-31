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

class GradebookEvent extends BasicEvent {

    const ACTIVITY = 'gradebook';
    const UPGRADE = 'gradebook-grade-changed';

    public function __construct() {
        parent::__construct();

        $this->on(self::UPGRADE, function($data) {
            $threshold = 0;

            // fetch total grade and use it as threshold
            $s_grade = userGradeTotal($data->resource, $data->uid);
            if ($s_grade && floatval($s_grade) > 0) {
                $threshold = floatval($s_grade);
            }

            $this->setEventData($data);
            $this->context['threshold'] = $threshold;
            $this->emit(parent::PREPARERULES);
        });
    }

}
