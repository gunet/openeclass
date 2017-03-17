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

class CommentEvent extends BasicEvent {
    
    const BLOG_ACTIVITY = 'blogpost';
    const COURSE_ACTIVITY = 'course';
    const NEWCOMMENT = 'comment-submitted';
    const DELCOMMENT = 'comment-deleted';
    
    public function __construct() {
        parent::__construct();
        
        $handle = function($data) {
            $threshold = 0;
            
            // fetch comments count from DB and use it as threshold
            $subm = Database::get()->querySingle("SELECT count(id) AS count "
                    . " FROM comments "
                    . " WHERE user_id = ?d "
                    . " AND rid = ?d "
                    . " AND rtype = ?s", $data->uid, $data->resource, $data->activityType);
            if ($subm && floatval($subm->count) > 0) {
                $threshold = floatval($subm->count);
            }
            
            $this->setEventData($data);
            $this->context['threshold'] = $threshold;
            $this->emit(parent::PREPARERULES);
        };
        
        $this->on(self::NEWCOMMENT, $handle);
        $this->on(self::DELCOMMENT, $handle);
    }
    
}
