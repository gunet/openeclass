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
require_once 'modules/game/AssignmentEvent.php';

class AssignmentEventStaticTest extends PHPUnit_Framework_TestCase {
    
    public function testAssignmentContext() {
        $data = new stdClass();
        $data->courseId = 1;
        $data->uid = 1000;
        $data->activityType = AssignmentEvent::ACTIVITY;
        $data->module = 5;
        $data->resource = 1;

        $context = AssignmentEvent::trigger(AssignmentEvent::NEWGRADE, $data);
        
        $this->assertNotNull($context);
    }
}
