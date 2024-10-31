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
require_once 'modules/attendance/functions.php';

class AttendanceEvent extends BasicEvent {

    const ACTIVITY = 'attendance';
    const UPDATE = 'attendancebook-updated';

    public function __construct() {
        parent::__construct();

        $this->on(self::UPDATE, function($data) {
            $threshold = 0;

            // fetch total presences count and use it as threshold
            $attTotal = userAttendTotal($data->resource, $data->uid);
            if ($attTotal && floatval($attTotal) > 0) {
                $threshold = floatval($attTotal);
            }

            $this->setEventData($data);
            $this->context['threshold'] = $threshold;
            $this->emit(parent::PREPARERULES);
        });
    }

}
