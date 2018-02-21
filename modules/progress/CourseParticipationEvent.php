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

class CourseParticipationEvent extends BasicEvent {

    const ACTIVITY = 'courseparticipation';
    const LOGGEDIN = 'loggedin';
    const STATSAPPENDED = 'statsappended';

    public function __construct() {
        parent::__construct();

        $this->on(self::LOGGEDIN, function ($data) {

            // fetch usage duration per course from DB and use it as threshold
            $result = Database::get()->queryArray("SELECT SUM(duration) AS duration, course.id
                                        FROM course
                                            LEFT JOIN course_user ON course.id = course_user.course_id
                                            LEFT JOIN actions_daily
                                                ON actions_daily.user_id = course_user.user_id AND
                                                   actions_daily.course_id = course_user.course_id
                                        WHERE course_user.user_id = ?d
                                        AND course.visible != " . COURSE_INACTIVE . "
                                        GROUP BY course.id
                                        ORDER BY duration DESC", $data->uid);

            if (count($result) > 0) {
                foreach ($result as $item) {
                    if (intval($item->duration) > 0) {
                        $data->courseId = intval($item->id);
                        $threshold = floatval($item->duration / 3600); // turn seconds to hours

                        $this->setEventData($data);
                        $this->context['threshold'] = $threshold;
                        $this->emit(parent::PREPARERULES);
                    }
                }
            }

        });

        $this->on(self::STATSAPPENDED, function ($data) {

            // fetch usage duration for specific course/user from DB and use it as threshold
            $result = Database::get()->querySingle("SELECT SUM(duration) AS duration "
                . " FROM actions_daily "
                . " WHERE user_id = ?d "
                . " AND course_id = ?d", $data->uid, $data->courseId);

            if ($result && intval($result->duration) > 0) {
                $threshold = floatval($result->duration / 3600); // turn seconds to hours

                $this->setEventData($data);
                $this->context['threshold'] = $threshold;
                $this->emit(parent::PREPARERULES);
            }

        });
    }

}
