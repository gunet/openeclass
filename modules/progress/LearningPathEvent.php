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

class LearningPathEvent extends BasicEvent {

    const ACTIVITY = 'learning path';
    const UPDPROGRESS = 'learning-path-accessed';

    public function __construct() {
        parent::__construct();

        $this->on(self::UPDPROGRESS, function($data) {
            $threshold = 0;

            // fetch learning path score from DB and use it as threshold
            list(, , , , , , $score, ) = get_learnPath_progress_details($data->resource, $data->uid, true, null, $data->courseId);
            if ($score && floatval($score) > 0) {
                $threshold = floatval($score);
            }

            $this->setEventData($data);
            $this->context['threshold'] = $threshold;
            $this->emit(parent::PREPARERULES);
        });
    }

}
