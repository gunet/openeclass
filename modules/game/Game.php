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

require_once 'GameAbstract.php';

class Game extends GameAbstract {
    
    public function __construct() {
        $this->ruler = new Hoa\Ruler\Ruler();
    }
    
    protected function buildRule() {
        $satisfiesAllCriteria = function($userCriterionIds) {
            $ret = true;
            
            foreach($this->criterionIds as $crit) {
                if (!in_array($crit, $userCriterionIds)) {
                    $ret = false;
                    break;
                }
            }
            
            return $ret;
        };
        
        $asserter = new Hoa\Ruler\Visitor\Asserter();
        $asserter->setOperator('satisfiesallcriteria', $satisfiesAllCriteria);
        $this->ruler->setAsserter($asserter);
        
        $this->rule = 'satisfiesallcriteria(userCriterionIds)';
    }
    
    public function evaluate($context) {
        $this->prepareForContext($context);
        
        if (!$this->autoassign) {
            return false;
        }
            
        if ($this->ruler->assert($this->rule, $context)) {
            $this->assertedAction($context);
            return true;
        } else {
            $this->notAssertedAction($context);
            return false;
        }
    }
}
