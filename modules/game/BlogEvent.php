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

class BlogEvent extends BasicEvent {
    
    const ACTIVITY = 'blog';
    const NEWPOST = 'blogpost-submitted';
    const DELPOST = 'blogpost-deleted';
    
    public function __construct() {
        parent::__construct();
        
        $handle = function($data) {
            $this->setEventData($data);
            
            // TODO: fetch data from DB: SELECT COUNT BLOG POSTS FOR USER $data->uid
            $this->context['threshold'] = 12;
            $this->emit(parent::PREPARERULES);
        };
        
        $this->on(self::NEWPOST, $handle);
        $this->on(self::DELPOST, $handle);
    }
    
}
