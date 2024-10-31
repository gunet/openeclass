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

require_once 'Event.php';

class LpAnalyticsEvent extends Event {

    const LPPERCENTAGE = 'lppercentage';

    public function __construct() {
        parent::__construct();

        $eventfunction =  function ($data) {

            if($this->exists) {
                $this->context['triggeredModule'] = $data->element_type;
                $this->context['course_id'] = $data->course_id;
                $this->context['user_id'] = $data->uid;
                $this->context['resource'] = $data->resource;

                $record = Database::get()->querySingle("SELECT (SUM(raw)/SUM(lump.scoreMax) * 100.0) as value"
                . " FROM lp_user_module_progress lump "
                . " JOIN lp_learnPath lp ON (lp.learnPath_id = lump.learnPath_id) "
                . " WHERE lump.learnPath_id = ?d "
                . " AND lp.course_id = ?d"
                . " AND lump.user_id = ?d", $this->context['resource'], $this->context['course_id'], $this->context['user_id']);

                $this->context['value'] = 0;
                if ($record && floatval($record->value) > 0) {
                    $this->context['value'] = floatval($record->value);
                }

                foreach ($this->elements as $element) {
                    $record = Database::get()->querySingle("SELECT id, value FROM user_analytics WHERE 
                            user_id = ?d
                            AND analytics_element_id = ?d
                            AND DATE(`updated`) = CURDATE()", $this->context['user_id'], $element->id);

                    if ($record) {
                        $id = $record->id;
                        $value = $this->context['value'];

                        $this->updateValue($id, $value);
                    } else {
                        $user_id = $this->context['user_id'];
                        $analytics_element_id = $element->id;
                        $value = $this->context['value'];
                        $time = date("Y-m-d H:i:s");
                        $this->insertValue($user_id, $analytics_element_id, $value, $time);
                    }
                }
            } else {
                $course_id = $data->course_id;
                $analytics_element_id = $data->analytics_element_id;
                $resource = $data->resource;

                $lp_records = Database::get()->queryArray("SELECT(SUM(raw)/SUM(lump.scoreMax) * 100.0) as value, lump.user_id "
                . " FROM lp_user_module_progress lump "
                . " JOIN lp_learnPath lp ON (lp.learnPath_id = lump.learnPath_id) "
                . " WHERE lump.learnPath_id = ?d "
                . " AND lp.course_id = ?d"
                . " GROUP BY lump.user_id", $resource, $course_id);

                foreach ($lp_records as $lp_record) {
                    $value = 0;
                    if ($lp_record && floatval($lp_record->value) > 0) {
                        $value = floatval($lp_record->value);
                    }

                    $time = date("Y-m-d H:i:s");
                    $this->insertValue($lp_record->user_id, $analytics_element_id, $value, $time);
                }
            }
        };

        $this->on(self::LPPERCENTAGE, $eventfunction);

    }
}
