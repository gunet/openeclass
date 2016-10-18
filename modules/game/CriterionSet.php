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

/**
 * A set of rule-engine criteria.
 */
class CriterionSet {
    
    protected $criteria = array();
    
    /**
     * CriterionSet constructor.
     * 
     * @param array $criteria Criterion objects to add to CriterionSet
     */
    public function __construct(array $criteria = array()) {
        foreach ($criteria as $criterion) {
            $this->addCriterion($criterion);
        }
    }
    
    /**
     * Add a Criterion to the CriterionSet.
     * 
     * @param Criterion $criterion Criterion to add to the set
     */
    public function addCriterion(Criterion $criterion) {
        $this->criteria[spl_object_hash($criterion)] = $criterion;
    }
    
    /**
     * Evaluate all Criteria in the CriterionSet.
     * 
     * @param Hoa\Ruler\Context $context Context with which to evaluate each rule
     */
    public function evaluateCriteria(Hoa\Ruler\Context $context) {
        foreach ($this->criteria as $criterion) {
            $criterion->evaluate($context);
        }
    }
}
