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

require_once 'CriterionAbstract.php';
require_once 'Operator.php';

class Criterion extends CriterionAbstract {

    public function __construct() {
        $this->ruler = new Hoa\Ruler\Ruler();
    }

    protected function buildRule() {
        $act_type = (!is_null($this->activityType)) ? 'activityType = "' . $this->activityType . '"': null;
        $module = (!is_null($this->module)) ? 'module = ' . $this->module : null;
        $resource = (!is_null($this->resource)) ? 'resource = ' . $this->resource : null;

        $threshold = null;
        if (!empty($this->threshold) && !empty($this->operator)) {
            $threshold = 'threshold ' . constant("Operator::{$this->operator}") . ' ' . $this->threshold;
        }

        $ar = array($act_type, $module, $resource, $threshold);
        $this->rule = implode(' and ', array_filter($ar, function ($v) {
            return $v !== null;
        }));
    }

    public function evaluate($context) {
        if($this->criterion_type == 'recurring') {
            if(is_null($this->max_points_from_criterion) && is_null($this->max_points_from_criterion_time_period)) { //no constraints, assign the points
                $this->assignPointsRecurringAction($context['uid']);
                return true;
            } else{

            }
        } else { //criteria for badges, certificates and onetime criteria for points games
            if ($this->ruler->assert($this->rule, $context)) {
                if($this->criterion_type == 'onetime') { //ponts games onetime event
                    $this->assertedAction($context, $this->points);
                } else { //badges or certificates event
                    $this->assertedAction($context);
                }
                return true;
            } else {
                if ($this->criterion_type != 'onetime') {
                    $this->notAssertedAction($context);
                }
                return false;
            }
        }
    }

}
