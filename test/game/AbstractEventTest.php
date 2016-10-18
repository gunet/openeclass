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

require_once 'config/config.php';
require_once 'modules/db/database.php';

abstract class AbstractEventTest extends PHPUnit_Framework_TestCase {
    
    protected $event;
    protected $currentdata;
    protected static $hasResource;
    protected static $hasThreshold;
    
    public function assertPreConditions() {
        $this->assertNull($this->event->getContext());
    }
    
    public function assertPostConditions() {
        $data = $this->currentdata;
        $context = $this->event->getContext();
        
        if ($context !== NULL) {
            $this->assertInstanceOf('Hoa\\Ruler\\Context', $context);
            $this->assertEquals($data->activityType, $context['activityType']);
            $this->assertEquals($data->module, $context['module']);
            $this->assertEquals($data->courseId, $context['courseId']);
            $this->assertEquals($data->uid, $context['uid']);
            if (self::$hasResource) {
                $this->assertEquals($data->resource, $context['resource']);
            } else {
                $this->assertObjectNotHasAttribute('resource', $data);
                $this->assertObjectNotHasAttribute('resource', $context);
            }
            if (self::$hasThreshold) {
                $this->assertArrayHasKey('threshold', $context);
                $this->assertNotNull($context['threshold']);
            } else {
                $this->assertArrayNotHasKey('threshold', $context);
            }
        }
    }
    
    public function testEmptyContext() {
        $ev = $this->event;
        $this->assertNull($ev->getContext());
        $ev->emit('hello');
        $this->assertNull($ev->getContext());
        $ev->emit('testevent');
        $this->assertNull($ev->getContext());
    }
}
