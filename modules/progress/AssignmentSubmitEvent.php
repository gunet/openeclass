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

class AssignmentSubmitEvent extends BasicEvent {

    const ACTIVITY = 'assignment-submit';
    const UPDATE = 'assignment-submitted';

    public function __construct() {
        parent::__construct();

        $handle = function($data) {
            $threshold = 0;

            // fetch blog posts count from DB and use it as threshold
            $cnt = Database::get()->querySingle("SELECT count(id) AS count "
                . " FROM assignment_submit "
                . " WHERE uid = ?d "
                . " AND assignment_id = ?d ", $data->uid, $data->resource);
            if ($cnt && floatval($cnt->count) > 0) {
                $threshold = floatval($cnt->count);
            }

            $this->setEventData($data);
            $this->context['threshold'] = $threshold;
            $this->emit(parent::PREPARERULES);
        };

        $this->on(self::UPDATE, $handle);
    }

}
