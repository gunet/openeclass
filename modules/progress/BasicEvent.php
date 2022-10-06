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

require_once 'Criterion.php';
require_once 'CriterionSet.php';
require_once 'Game.php';

class BasicEvent implements Sabre\Event\EventEmitterInterface {
    use Sabre\Event\EventEmitterTrait;
    
    const PREPARERULES = 'prepare-rules';
    const FIRERULES = 'fire-rules';
    const COMPLETIONRULES = 'completion-rules';
    
    protected $context;
    protected $eventData;
    protected $certificateIds;
    protected $badgeIds;
    protected $criterionSet;
    
    public static function trigger($eventname, $eventdata) {
        $class = get_called_class();
        $event = new $class;
        $event->emit($eventname, [$eventdata]);
        return $event->getContext();
    }
    
    public function __construct() {
        // fix for hiqdev protocol
        if (!array_key_exists('argv', $_SERVER)) {
            $_SERVER['argv'] = array();
            $_SERVER['argv'][0] = '';
            $_SERVER['argc'] = 0;
        }

        $this->preDataListeners();
    }
    
    public function getContext() {
        return $this->context;
    }
    
    protected function setEventData($data) {
        // create context from standard event data
        $context = new Hoa\Ruler\Context();
        $context['activityType']  = $data->activityType;
        $context['module']  = $data->module;
        if (isset($data->resource)) {
            $context['resource'] = $data->resource;
        }
        $context['courseId'] = $data->courseId;
        $context['uid'] = $data->uid;
        
        $this->eventData = $data;
        $this->context = $context;
        
        // set post-data event listeners
        $this->on(self::PREPARERULES, function() {
            $data = $this->eventData;
            $this->certificateIds = array();
            $this->badgeIds = array();
            $this->criterionSet = new CriterionSet();
            
            // select certificates not already conquered
            $certsQ = "select c.id from certificate c where c.course_id = ?d and c.id not in ("
                    . " select certificate from user_certificate where user = ?d and completed = 1)";
            Database::get()->queryFunc($certsQ, function($c) {
                $this->certificateIds[] = $c->id;
            }, $data->courseId, $data->uid);
            
            // select badges not already conquered
            $badgesQ = "select b.id from badge b where b.course_id = ?d and b.id not in ("
                    . " select badge from user_badge where user = ?d and completed = 1)";
            Database::get()->queryFunc($badgesQ, function($b) {
                $this->badgeIds[] = $b->id;
            }, $data->courseId, $data->uid);
            
            $iter = array();
            $iter['certificate'] = $this->certificateIds;
            $iter['badge'] = $this->badgeIds;
            
            foreach ($iter as $key => $ids) {
                // select criteria not already conquered
                if (count($ids) >0) {
                    $inIds = "(" . implode(",", $ids) . ")";
                    $args = array($data->uid, $data->activityType, $data->module);
                    $andResource = '';
                    if (isset($data->resource)) {
                        $andResource = " and c.resource = ?d ";
                        $args[] = $data->resource;
                    } else {
                        // safeguard in case criterion as described in its DB-row mistakenly uses a non-null resource when it should use NULL
                        // the safeguard will not allow Hoa\Ruler\Visitor\Asserter::visitContext() to raise an exception about the missing resource
                        // so, when the eventData does not include a resource, we are specifically querying for criteria with NULL resource 
                        // instead of blindly trusting the DB.
                        $andResource = " and c.resource is null ";
                    }
                    $critsQ = "select c.*, '$key' as type from {$key}_criterion c"
                        . " where c.$key in " . $inIds . " "
                        . " and c.id not in (select {$key}_criterion from user_{$key}_criterion where user = ?d) "
                        . " and c.activity_type = ?s "
                        . " and c.module = ?d "
                        . $andResource;
                    Database::get()->queryFunc($critsQ, function ($crit) {
                        $this->criterionSet->addCriterion(Criterion::initWithProperties($crit));
                    }, $args);
                }
            }
            
            // ready to fire the rule-engine
            $this->emit(self::FIRERULES);
        });
    }
    
    protected function preDataListeners() {
        $this->on(self::FIRERULES, function() {
            $this->criterionSet->evaluateCriteria($this->context);
            $this->emit(self::COMPLETIONRULES, [$this->eventData]);
        });
        
        $this->on(self::COMPLETIONRULES, function($data) {
            Game::checkCompleteness($data->uid, $data->courseId);
        });
    }
    
}
