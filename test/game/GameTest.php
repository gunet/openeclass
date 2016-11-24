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

require_once 'modules/progress/Game.php';

class GameTest extends PHPUnit_Framework_TestCase {
    
    private $game;
    
    public function setUp() {
        $props = new stdClass();
        $props->id = 100;
        $props->type = 'certificate';
        $props->autoassign = 1;
        $props->active = 1;
        $props->criterionIds = array(1, 2, 3);
        
        $this->game = Game::initWithProperties($props);
    }
    
    public function testOne() {
        $context = new Hoa\Ruler\Context();
        $context['uid'] = 200;
        $context['userCriterionIds'] = array(3, 1, 2);
        
        $this->assertTrue($this->game->evaluate($context));
    }
    
    public function testTwo() {
        $context = new Hoa\Ruler\Context();
        $context['uid'] = 200;
        $context['userCriterionIds'] = array(1, 3);
        
        $this->assertFalse($this->game->evaluate($context));
    }
}
