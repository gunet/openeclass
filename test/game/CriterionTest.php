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

require_once 'modules/game/Criterion.php';

class CriterionTest extends PHPUnit_Framework_TestCase {
    
    private $criterion;
    
    public function setUp() {
        $props = new stdClass();
        $props->id = 1;
        $props->type = 'certificate';
        $props->activity_type = 'exercise';
        $props->module = 10;
        $props->resource = 1;
        $props->threshold = 8.6;
        $props->operator = 'get';
        $this->criterion = Criterion::initWithProperties($props);
    }
    
    public function testOne() {
        $context = new Hoa\Ruler\Context();
        // η πληροφορία του act_type, module, resource μπορεί να προέρχεται κατευθείαν
        // από ένα user-generated event (π.χ. submit τελευταίου βήματος άσκησης)
        $context['activityType']  = 'exercise';
        $context['module']  = 10;
        $context['resource'] = 1;
        // η πληροφορία του threshold μπορεί να προέρχεται από ένα ContextCreator Object
        // ειδικό για ασκήσεις που θα το καλεί το PHP backend βασιζόμενο και πάλι στην 
        // πληροφορία που κουβαλάει το Event
        $context['threshold'] = new Hoa\Ruler\DynamicCallable(function () {
            // select user's exercise grade from DB
            return 8.6;
        });
        
        $this->assertTrue($this->criterion->evaluate($context));
    }
    
    public function testTwo() {
        $context = new Hoa\Ruler\Context();
        $context['activityType']  = 'forum';
        $context['module']  = 9;
        $context['threshold'] = new Hoa\Ruler\DynamicCallable(function () {
            // count user's forum posts from DB
            return 20.0;
        });
        
        $this->assertFalse($this->criterion->evaluate($context));
    }
}
