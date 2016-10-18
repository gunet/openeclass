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

require_once 'BasicEvent.php';

class RatingEvent extends BasicEvent {
    
    const FORUM_ACTIVITY = 'forum_post';
    const SOCIALBOOKMARK_ACTIVITY = 'link';
    const RATECAST = 'rate-cast';
    
    public function __construct() {
        parent::__construct();
        
        $handle = function($data) {
            // fetch grade from DB and use it as threshold
            switch($data->activityType) {
                case self::FORUM_ACTIVITY:
                    $subm = Database::get()->querySingle("SELECT SUM(value) AS grade "
                            . " FROM rating r "
                            . " WHERE r.rid IN ( "
                            . "  SELECT fp.id "
                            . "  FROM forum_post fp "
                            . "  JOIN forum_topic ft ON (ft.id = fp.topic_id) "
                            . "  JOIN forum f ON (f.id = ft.forum_id) "
                            . "  WHERE f.course_id = ?d "
                            . " ) "
                            . " AND r.rtype = ?s "
                            . " AND r.user_id = ?d", $data->courseId, self::FORUM_ACTIVITY, $data->uid);
                    $threshold = $this->getThreshold($subm);
                    break;
                case self::SOCIALBOOKMARK_ACTIVITY:
                    $subm = Database::get()->querySingle("SELECT SUM(r.value) AS grade "
                            . " FROM rating r WHERE r.rid in ( "
                            . "  SELECT l.id "
                            . "  FROM link l "
                            . "  WHERE l.course_id = ?d "
                            . " ) "
                            . " AND r.rtype = ?s "
                            . " AND r.user_id = ?d", $data->courseId, self::SOCIALBOOKMARK_ACTIVITY, $data->uid);
                    $threshold = $this->getThreshold($subm);
                    break;
                default:
                    $threshold = 0;
                    break;
            }
            
            $this->setEventData($data);
            $this->context['threshold'] = $threshold;
            $this->emit(parent::PREPARERULES);
        };
        
        $this->on(self::RATECAST, $handle);
    }
    
    private function getThreshold($subm) {
        $threshold = 0;
        if ($subm && floatval($subm->grade) > 0) {
            $threshold = floatval($subm->grade);
        }
        return $threshold;
    }
    
}
