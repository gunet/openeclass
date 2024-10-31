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

class Event implements Sabre\Event\EventEmitterInterface {
    use Sabre\Event\EventEmitterTrait;

    protected $context;
    protected $elements;

    public function __construct() {
        $this->preDataListeners();
    }

    protected function preDataListeners() {
        //do something here
        //$this->GetAnalyticsElementsInfo(1,37);

    }

    //Trigger after an action
    public static function trigger($eventname, $data, $exists) {
        $course_id = $data->course_id;
        $element_type = $data->element_type;

        $resource = 0;
        if (isset($data->resource))
            $resource = $data->resource;

        //Check if there are analytics to compute this action
        $now = date("Y-m-d");

        if ($exists) {
            $elements = Database::get()->queryArray("SELECT ae.id as id, ae.upper_threshold as upper_threshold, ae.lower_threshold as lower_threshold, ae.weight as weight, ae.min_value as min_value, ae.max_value as max_value, a.periodType as periodType, ae.resource as resource
            FROM analytics_element as ae
            INNER JOIN analytics as a
                on ae.analytics_id = a.id
            WHERE ae.module_id = ?d
            AND a.courseID = ?d
            AND a.start_date <= ?t
            AND a.end_date >= ?t
            AND a.active = ?d
            AND ae.resource = ?d", $element_type, $course_id, $now, $now, 1, $resource);
           //print_r($data);

            //print("Resource: $resource!! ");

            // print("SELECT ae.id as id, ae.upper_threshold as upper_threshold, ae.lower_threshold as lower_threshold, ae.weight as weight, ae.min_value as min_value, ae.max_value as max_value, a.periodType as periodType
            // FROM analytics_element as ae
            // INNER JOIN analytics as a
            //     on ae.analytics_id = a.id
            // WHERE ae.module_id = $element_type
            // AND a.courseID = $course_id
            // AND a.start_date <= $now
            // AND a.end_date >= $now
            // AND a.active = 1
            // AND ae.resource = $resource");

            if(count($elements) > 0) { //If the query return something trigger calculation otherwise there is nothing to compute
                $class = get_called_class();
                $event = new $class;
//
                $event->elements = $elements;
                $event->exists = true;
                $event->emit($eventname, [$data]);

                return $event->getContext();
            }

            return '';
        } else {
            $class = get_called_class();
            $event = new $class;
            $event->exists = false;
            $event->emit($eventname, [$data]);

            return $event->getContext();
        }
    }

    //Saves data to context
    public function getContext() {
        return $this->context;
    }

    // In this fucntion we are getting all the analytics elements
    // that are using this information.
    protected function GetAnalyticsElementsInfo ($course, $module) {
        $now = date("Y-m-d H:i:s");
        $modules = Database::get()->queryArray("SELECT ae.id as id, ae.upper_threshold as upper_threshold, ae.lower_threshold as lower_threshold, ae.weight as weight, ae.min_value as min_value, ae.max_value as max_value, a.periodType as periodType
                                                FROM analytics_element as ae
                                                INNER JOIN analytics as a
                                                WHERE ae.module_id = ?d
                                                AND a.courseID = ?d
                                                AND a.start_date <= ?t
                                                AND a.end_date >= ?t", $module, $course, $now, $now);

        return $modules;
    }

    protected function insertValue ($user_id, $analytics_element_id, $value, $time){
        $new_id = Database::get()->query("INSERT INTO user_analytics SET
                                user_id = ?d,
                                analytics_element_id = ?d,
                                value = ?d,
                                updated = ?t", $user_id, $analytics_element_id, $value, $time)->lastInsertID;
    }

    protected function updateValue ($id, $value){
        $now = date("Y-m-d H:i:s");
        Database::get()->query("UPDATE user_analytics SET
            value = ?d,
            updated = ?t
            WHERE id=?d", $value, $now, $id);
    }
}
