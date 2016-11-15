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

abstract class GameAbstract {
    
    protected $id;
    protected $type;
    protected $autoassign;
    protected $active;
    protected $criterionIds;
    
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
        
        $this->table = 'user_' . $properties->type;
        $this->field = $properties->type;
        
        $this->buildRule();
    }
    
    abstract protected function buildRule();
    
    abstract public function evaluate($context);
    
    protected function prepareForContext($context) {
        $uid = (isset($context['uid'])) ? $context['uid'] : null;
        $userCriterionIds = (isset($context['userCriterionIds'])) ? $context['userCriterionIds'] : array();
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
            } else {
                Database::get()->query("update $this->table set completed_criteria = ?d, total_criteria = ?d, updated = ?t where user = ?d and $this->field = ?d", $completed_criteria, $total_criteria, gmdate('Y-m-d H:i:s'), $uid, $this->id);
            }
        }
    }
    
    protected function assertedAction($context) {
        $uid = (isset($context['uid'])) ? $context['uid'] : null;
        if ($uid) {
            Database::get()->query("update $this->table set completed = true, assigned = ?t where user = ?d and $this->field = ?d", gmdate('Y-m-d H:i:s'), $uid, $this->id);
        }
    }
    
    protected function notAssertedAction($context) {
        $uid = (isset($context['uid'])) ? $context['uid'] : null;
        if ($uid) {
            Database::get()->query("update $this->table set completed = false, assigned = null where user = ?d and $this->field = ?d", $uid, $this->id);
        }
    }
}
