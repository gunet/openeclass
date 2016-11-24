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

require_once 'AbstractEventTest.php';
require_once 'modules/progress/RatingEvent.php';

class RatingEventTest extends AbstractEventTest {
    
    private static $forumData;
    private static $socialBookmarkData;
    
    public static function setUpBeforeClass() {
        self::$hasResource = false;
        self::$hasThreshold = true;
        // forum
        $forumData = new stdClass();
        $forumData->courseId = 1;
        $forumData->uid = 1000;
        $forumData->activityType = RatingEvent::FORUM_ACTIVITY;
        $forumData->module = 39;
        self::$forumData = $forumData;
        // social bookmark
        $scData = new stdClass();
        $scData->courseId = 1;
        $scData->uid = 1000;
        $scData->activityType = RatingEvent::SOCIALBOOKMARK_ACTIVITY;
        $scData->module = 39;
        self::$socialBookmarkData = $scData;
    }
    
    public function setUp() {
        $this->event = new RatingEvent();
    }
    
    public function testForumCastContext() {
        $this->currentdata = self::$forumData;
        $this->event->emit(RatingEvent::RATECAST, [$this->currentdata]);
        $context = $this->event->getContext();
        
        $this->assertNotNull($context);
        $this->assertEquals(RatingEvent::FORUM_ACTIVITY, $context['activityType']);
    }
    
    public function testSocialBookmarkCastContext() {
        $this->currentdata = self::$socialBookmarkData;
        $this->event->emit(RatingEvent::RATECAST, [$this->currentdata]);
        $context = $this->event->getContext();
        
        $this->assertNotNull($context);
        $this->assertEquals(RatingEvent::SOCIALBOOKMARK_ACTIVITY, $context['activityType']);
    }
}
