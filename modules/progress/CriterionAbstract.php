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

abstract class CriterionAbstract {
    
    protected $id;
    protected $type;
    protected $activityType;
    protected $module;
    protected $resource;
    protected $threshold;
    protected $operator;
    
    protected $table;
    protected $field;
    
    protected $rule;
    protected $ruler;
    
    /*public static function initWithId($id, $type) {
        $instance = new static();
        $instance->loadById($id, $type);
        return $instance;
    }
    
    protected function loadById($id, $type) {
        echo "running loadById(): select * from \$type_criterion WHERE id = \$id \n";
        // TODO: $crit = FROM DB
        $this->loadByProperties($crit);
    }*/
    
    public static function initWithProperties($properties) {
        $instance = new static();
        $instance->loadByProperties($properties);
        return $instance;
    }
    
    protected function loadByProperties($properties) {
        $this->id = $properties->id;
        $this->type = $properties->type;
        $this->activityType = $properties->activity_type;
        $this->module = $properties->module;
        $this->resource = $properties->resource;
        $this->threshold = $properties->threshold;
        $this->operator = $properties->operator;
        
        $this->table = 'user_' . $properties->type . '_criterion';
        $this->field = $properties->type . '_criterion';
        
        $this->buildRule();
    }
    
    abstract protected function buildRule();
    
    abstract public function evaluate($context);
    
    protected function assertedAction($context) {
        $uid = (isset($context['uid'])) ? $context['uid'] : null;
        if ($uid) {
            $exists = Database::get()->querySingle("select count(id) as cnt from $this->table where user = ?d and $this->field = ?d", $uid, $this->id)->cnt;
            if (!$exists) {
                Database::get()->query("insert into $this->table (user, $this->field, created) values (?d, ?d, ?t)", $uid, $this->id, gmdate('Y-m-d H:i:s'));
            }
        }
    }
    
    protected function notAssertedAction($context) {
        $uid = (isset($context['uid'])) ? $context['uid'] : null;
        if ($uid) {
            Database::get()->query("delete from $this->table where user = ?d and $this->field = ?d", $uid, $this->id);
        }
    }
}

