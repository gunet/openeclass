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

class WikiEvent extends BasicEvent {
    
    const ACTIVITY = 'wiki';
    const NEWPAGE = 'page-submitted';
    const DELPAGE = 'page-deleted';
    
    public function __construct() {
        parent::__construct();
        
        $handle = function($data) {
            $threshold = 0;
            
            // fetch wiki pages count from DB and use it as threshold
            $subm = Database::get()->querySingle("SELECT count(wp.id) as count "
                    . " FROM wiki_pages wp "
                    . " JOIN wiki_properties w ON (w.id = wp.wiki_id) "
                    . " WHERE owner_id = ?d "
                    . " AND w.course_id = ?d", $data->uid, $data->courseId);
            if ($subm && floatval($subm->count) > 0) {
                $threshold = floatval($subm->count);
            }
            
            $this->setEventData($data);
            $this->context['threshold'] = $threshold;
            $this->emit(parent::PREPARERULES);
        };
        
        $this->on(self::NEWPAGE, $handle);
        $this->on(self::DELPAGE, $handle);
    }
    
}
