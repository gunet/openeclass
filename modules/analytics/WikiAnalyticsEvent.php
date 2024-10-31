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

class WikiAnalyticsEvent extends Event {

    const WIKIEVENT = 'wikievent';

    public function __construct() {
        parent::__construct();

        $this->on(self::WIKIEVENT, function ($data) {

            if($this->exists) {
                $this->context['triggeredModule'] = $data->element_type;
                $this->context['course_id'] = $data->course_id;
                $this->context['user_id'] = $data->uid;

                $record = Database::get()->querySingle("SELECT count(wp.id) as value "
                . " FROM wiki_pages wp "
                . " JOIN wiki_properties w ON (w.id = wp.wiki_id) "
                . " WHERE owner_id = ?d "
                . " AND w.course_id = ?d"
                . " AND DATE(ctime) = CURDATE()", $this->context['user_id'], $this->context['course_id']);

                $this->context['value'] = $record->value;

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

                $wiki_records = Database::get()->queryArray("SELECT owner_id as user_id, ctime as time, count(wp.id) as value "
                . " FROM wiki_pages wp "
                . " JOIN wiki_properties w ON (w.id = wp.wiki_id) "
                . " WHERE w.course_id = ?d"
                . " AND DATE(ctime) >= ?t"
                . " AND DATE(ctime) <= ?t"
                . " group by DATE(ctime), owner_id", $course_id, $data->start_date, $data->end_date);
                print_r($wiki_records);
                foreach ($wiki_records as $wiki_record) {
                    $this->insertValue($wiki_record->user_id, $analytics_element_id, $wiki_record->value, $wiki_record->time);
                }
            }
        });

    }
}
