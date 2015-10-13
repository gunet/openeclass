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
    
    const BLOG_ACTIVITY = 'blog';
    const NEWCOMMENT = 'comment-submitted';
    const DELCOMMENT = 'comment-deleted';
    
    public function __construct() {
        parent::__construct();
        
        $handle = function($data) {
            $this->setEventData($data);
            
            // TODO: fetch data from DB: SELECT COUNT COMMENTS FOR USER $data->uid FOR MODULE $data->module
            $this->context['threshold'] = 18;
            $this->emit(parent::PREPARERULES);
        };
        
        $this->on(self::NEWCOMMENT, $handle);
        $this->on(self::DELCOMMENT, $handle);
    }
    
}
