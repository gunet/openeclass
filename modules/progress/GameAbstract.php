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

require_once 'CourseCompletionEvent.php';

abstract class GameAbstract {

    protected $id;
    protected $type;
    protected $autoassign;
    protected $active;
    protected $criterionIds;
    protected $unit_id;

    protected $table;
    protected $field;

    protected $rule;
    protected $ruler;

    public static function initWithProperties($properties) {
        $instance = new static();
        $instance->loadByProperties($properties);
        return $instance;
    }

    protected function loadByProperties($properties) {
        $this->id = $properties->id;
        $this->type = $properties->type;
        $this->autoassign = $properties->autoassign;
        $this->active = $properties->active;
        $this->criterionIds = $properties->criterionIds;
        $this->unit_id = $properties->unit_id;

        $this->table = 'user_' . $properties->type;
        $this->field = $properties->type;

        $this->buildRule();
    }

    abstract protected function buildRule();

    abstract public function evaluate($context);

    private function triggerCourseCompletionEvent($uid, $unit_id = 0) {
        $course_id = Database::get()->querySingle("select course_id from $this->field where id = ?d and unit_id = ?d", $this->id, $unit_id)->course_id;
        if ($course_id && $course_id > 0) {
            $eventData = new stdClass();
            $eventData->courseId = $course_id;
            $eventData->uid = $uid;
            $eventData->unit_id = $unit_id;
            $eventData->activityType = CourseCompletionEvent::ACTIVITY;
            $eventData->module = MODULE_ID_PROGRESS;

            if ($unit_id == 0) {
                CourseCompletionEvent::trigger(CourseCompletionEvent::COMPLCRITCHANGE, $eventData);
            }
        }
    }

    private function prepareForContextProper($context, $terminal = false) {
        $uid = (isset($context['uid'])) ? $context['uid'] : null;
        $userCriterionIds = (isset($context['userCriterionIds'])) ? $context['userCriterionIds'] : array();

        if ( isset($context['unit_id']) and ($context['unit_id'] > 0) ) {
            $unit_id = $context['unit_id'];

            if ($uid) {
                $completed_criteria = 0;
                foreach($this->criterionIds as $crit) {
                    if (in_array($crit, $userCriterionIds)) {
                        $completed_criteria++;
                    }
                }

                $total_criteria = count($this->criterionIds);
                $exists = Database::get()->querySingle("select count(id) as cnt from $this->table where user = ?d and $this->field = ?d", $uid, $this->id)->cnt;

                if (!$exists) {
                    Database::get()->query("insert into $this->table (user, $this->field, completed_criteria, total_criteria, updated) values (?d, ?d, ?d, ?d, ?t)", $uid, $this->id, $completed_criteria, $total_criteria, gmdate('Y-m-d H:i:s'));
                    if (!$terminal) {
                        $this->triggerCourseCompletionEvent($uid, $unit_id);
                    }
                } else {
                    Database::get()->query("update $this->table set completed_criteria = ?d, total_criteria = ?d, updated = ?t where user = ?d and $this->field = ?d", $completed_criteria, $total_criteria, gmdate('Y-m-d H:i:s'), $uid, $this->id);
                    if (!$terminal) {
                        $this->triggerCourseCompletionEvent($uid, $unit_id);
                    }
                }
            }
        } else {
            if ($uid) {
                $completed_criteria = 0;
                foreach($this->criterionIds as $crit) {
                    if (in_array($crit, $userCriterionIds)) {
                        $completed_criteria++;
                    }
                }
                $total_criteria = count($this->criterionIds);

                $exists = Database::get()->querySingle("select count(id) as cnt from $this->table where user = ?d and $this->field = ?d", $uid, $this->id)->cnt;
                if (!$exists) {
                    Database::get()->query("insert into $this->table (user, $this->field, completed_criteria, total_criteria, updated) values (?d, ?d, ?d, ?d, ?t)", $uid, $this->id, $completed_criteria, $total_criteria, gmdate('Y-m-d H:i:s'));
                    if (!$terminal) {
                        $this->triggerCourseCompletionEvent($uid);
                    }
                } else {
                    Database::get()->query("update $this->table set completed_criteria = ?d, total_criteria = ?d, updated = ?t where user = ?d and $this->field = ?d", $completed_criteria, $total_criteria, gmdate('Y-m-d H:i:s'), $uid, $this->id);
                    if (!$terminal) {
                        $this->triggerCourseCompletionEvent($uid);
                    }
                }
            }
        }
    }

    protected function prepareForContext($context) {
        $this->prepareForContextProper($context, false);
    }

    protected function prepareForContextTerminal($context) {
        $this->prepareForContextProper($context, true);
    }

    protected function assertedAction($context) {
        $uid = (isset($context['uid'])) ? $context['uid'] : null;
        if ($uid) {
            $current = Database::get()->querySingle("select * from $this->table where user = ?d and $this->field = ?d", $uid, $this->id);
            if ($current && $current->assigned != null) {
                Database::get()->query("update $this->table set completed = true where user = ?d and $this->field = ?d", $uid, $this->id);
            } else {
                Database::get()->query("update $this->table set completed = true, assigned = ?t where user = ?d and $this->field = ?d", gmdate('Y-m-d H:i:s'), $uid, $this->id);
            }
        }
    }

    protected function notAssertedAction($context) {
        $uid = (isset($context['uid'])) ? $context['uid'] : null;
        if ($uid) {
            Database::get()->query("update $this->table set completed = false, assigned = null where user = ?d and $this->field = ?d", $uid, $this->id);
        }
    }

}
