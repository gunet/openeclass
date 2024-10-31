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

class LearningPathDurationEvent extends BasicEvent {

    const ACTIVITY = 'learning path duration';
    const UPDPROGRESS = 'learning-path-accessed';

    public function __construct() {
        parent::__construct();

        $this->on(self::UPDPROGRESS, function($data) {
            $threshold = 0;

            // fetch time from DB, extract hours and use it as threshold
            list($lpProgress, $lpTotalTime, $lpTotalStarted, $lpTotalAccessed, $lpTotalStatus, $lpAttemptsNb) = get_learnPath_progress_details($data->resource, $data->uid);
            list($hours, $minutes, $seconds, $primes) = extractScormTime($lpTotalTime);
            if ($hours && $minutes && intval($hours) >= 0 && intval($minutes) >= 0) {
                $threshold = floatval($hours + round($minutes / 60, 2) + round($seconds / 3600, 2));
            }

            $this->setEventData($data);
            $this->context['threshold'] = $threshold;
            $this->emit(parent::PREPARERULES);
        });
    }

}
